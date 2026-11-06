<?php

declare(strict_types=1);

const RATE_LIMIT_MAX = 5;
const RATE_LIMIT_WINDOW = 3600;
const RATE_LIMIT_FILE = 'rate-limit.json';

/**
 * Compte un envoi pour cette adresse IP, conservée seulement sous forme d'empreinte HMAC.
 * Renvoie 0 si l'envoi est accepté, sinon le nombre de secondes à attendre.
 */
function rate_limit_hit(string $ip): int
{
    return rate_limit_update(static function (array &$entries, int $now) use ($ip): int {
        $key = sign('rate-limit', ip_scope($ip));
        $hits = $entries[$key] ?? [];
        if (count($hits) >= RATE_LIMIT_MAX) {
            return max(1, min($hits) + RATE_LIMIT_WINDOW - $now);
        }
        $entries[$key] = [...$hits, $now];

        return 0;
    });
}

/**
 * Efface les entrées expirées. Appelée à chaque requête : la date de modification du fichier
 * étant celle de l'entrée la plus ancienne, un simple stat suffit dans le cas courant.
 */
function rate_limit_purge(): void
{
    $file = storage_path(RATE_LIMIT_FILE);
    if (is_file($file) && filesize($file) > 0 && filemtime($file) <= time() - RATE_LIMIT_WINDOW) {
        rate_limit_update(static fn (): int => 0);
    }
}

/** IPv6 : un abonné dispose en général d'un /64 entier, on regroupe donc par préfixe. */
function ip_scope(string $ip): string
{
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
        return $ip;
    }

    return inet_ntop(substr((string) inet_pton($ip), 0, 8) . str_repeat("\0", 8)) . '/64';
}

/**
 * Lit les entrées non expirées sous verrou exclusif, applique $change puis réécrit le fichier.
 * Stockage indisponible : l'envoi n'est pas bloqué (les autres protections restent actives), l'erreur est journalisée.
 */
function rate_limit_update(callable $change): int
{
    $file = storage_path(RATE_LIMIT_FILE);
    $handle = storage_ready() ? @fopen($file, 'c+') : false;
    if ($handle === false) {
        error_log('Limitation des envois : impossible d\'ouvrir ' . $file);

        return 0;
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            error_log('Limitation des envois : verrouillage impossible de ' . $file);

            return 0;
        }

        $now = time();
        $stored = json_decode((string) stream_get_contents($handle), true);
        $entries = [];
        foreach (is_array($stored) ? $stored : [] as $key => $hits) {
            $hits = array_values(array_filter((array) $hits, static fn (mixed $time): bool => is_int($time) && $time > $now - RATE_LIMIT_WINDOW));
            if ($hits !== []) {
                $entries[$key] = $hits;
            }
        }

        $result = $change($entries, $now);

        ftruncate($handle, 0);
        rewind($handle);
        if ($entries !== []) {
            fwrite($handle, json_encode($entries, JSON_THROW_ON_ERROR));
        }
        fflush($handle);
        flock($handle, LOCK_UN);
    } finally {
        fclose($handle);
    }

    if ($entries !== []) {
        touch($file, min(array_merge(...array_values($entries))));
    }

    return $result;
}
