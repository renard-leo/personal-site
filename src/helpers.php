<?php

declare(strict_types=1);

// Doit rester aligné sur --color-bg dans scss/_variables.scss (barre du navigateur mobile).
const THEME_COLORS = ['light' => '#fbf9f6', 'dark' => '#020a12'];

const MONTHS_SHORT = [1 => 'janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
const MONTHS_LONG = [1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

/**
 * Typographie française : espace fine insécable avant ; ! ? et à l'intérieur des guillemets,
 * espace insécable avant les deux-points.
 */
function typo(string $text): string
{
    $thin = "\u{202F}";
    $nbsp = "\u{00A0}";

    $text = preg_replace('/(?<=[^\s;!?])[ \x{00A0}\x{202F}]*([;!?])/u', $thin . '$1', $text);
    $text = preg_replace('/[ \x{00A0}\x{202F}]*»/u', $thin . '»', $text);
    $text = preg_replace('/«[ \x{00A0}\x{202F}]*/u', '«' . $thin, $text);
    $text = preg_replace('/(?<=[\p{L}\d)»])[ \x{00A0}\x{202F}]*:(?=\s|$)/u', $nbsp . ':', $text);

    return $text;
}

/** Échappe un texte après lui avoir appliqué la typographie française. */
function t(string|int|null $text): string
{
    return e(typo((string) $text));
}

function render(string $template, array $data = []): string
{
    extract($data, EXTR_SKIP);
    ob_start();
    require ROOT . '/templates/' . $template . '.php';

    return (string) ob_get_clean();
}

function base_path(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    $script = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $uri = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

    // En local, la racine .htaccess réécrit vers public/ : l'URL publique ne contient pas ce segment.
    if (str_ends_with($script, '/public') && !str_starts_with($uri, $script . '/')) {
        $script = substr($script, 0, -strlen('/public'));
    }

    return $base = $script . '/';
}

function url(string $path = ''): string
{
    return base_path() . ltrim($path, '/');
}

/** URL d'un fichier de public/ avec empreinte de version pour un cache long. */
function asset(string $path): string
{
    $file = ROOT . '/public/' . ltrim($path, '/');
    $version = is_file($file) ? '?v=' . base_convert((string) filemtime($file), 10, 36) : '';

    return url($path) . $version;
}

function absolute_url(string $path = ''): string
{
    return rtrim((string) config('siteUrl'), '/') . '/' . ltrim($path, '/');
}

function is_https(): bool
{
    $https = strtolower((string) ($_SERVER['HTTPS'] ?? ''));

    return ($https !== '' && $https !== 'off')
        || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443'
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
}

/** Hôte de la requête en minuscules, port compris s'il n'est pas celui par défaut (« localhost », « renardleo.fr:8080 »). */
function request_host(): string
{
    return preg_replace('/:(80|443)$/', '', strtolower((string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '')));
}

/** Nom d'hôte sans le port. */
function request_hostname(): string
{
    return (string) parse_url('http://' . request_host(), PHP_URL_HOST);
}

/** Adresse IP du visiteur ; X-Forwarded-For n'est lu que derrière un relais déclaré dans trustedProxies. */
function client_ip(): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $forwarded = (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
    if ($forwarded !== '' && in_array($ip, (array) config('trustedProxies', []), true)) {
        $hops = array_map('trim', explode(',', $forwarded));
        $ip = (string) end($hops);
    }

    return filter_var($ip, FILTER_VALIDATE_IP) !== false ? $ip : '';
}

/** Chemin d'un fichier de var/ (données d'exécution, hors de la racine web). */
function storage_path(string $file): string
{
    return ROOT . '/var/' . $file;
}

/**
 * Écrit un fichier de public/ qu'Apache servira ensuite sans PHP (robots.txt, sitemap.xml).
 * Écriture atomique pour ne jamais servir un fichier à moitié écrit ; un échec est journalisé sans bloquer la réponse.
 */
function write_public_file(string $name, string $content): void
{
    $file = ROOT . '/public/' . $name;
    $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (@file_put_contents($tmp, $content) === false || !@rename($tmp, $file)) {
        @unlink($tmp);
        error_log('Impossible d’écrire ' . $file);
    }
}

/** Crée var/ au besoin ; false (journalisé) si le dossier n'est pas accessible en écriture. */
function storage_ready(): bool
{
    $dir = ROOT . '/var';
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        error_log('Impossible de créer le dossier ' . $dir);

        return false;
    }

    return true;
}

function content(string $key, mixed $default = null): mixed
{
    return array_get($GLOBALS['content'] ?? [], $key, $default);
}

function config(string $key, mixed $default = null): mixed
{
    return array_get($GLOBALS['config'] ?? [], $key, $default);
}

function open_to_work(): bool
{
    return config('openToWork', false) === true;
}

function hero_variant(): string
{
    $configured = config('hero', 'studio');

    return in_array($configured, ['studio', 'classic'], true) ? $configured : 'studio';
}

function array_get(array $array, string $key, mixed $default = null): mixed
{
    foreach (explode('.', $key) as $segment) {
        if (!is_array($array) || !array_key_exists($segment, $array)) {
            return $default;
        }
        $array = $array[$segment];
    }

    return $array;
}

function month_from(string $yearMonth): DateTimeImmutable
{
    return DateTimeImmutable::createFromFormat('!Y-m', $yearMonth) ?: new DateTimeImmutable('first day of this month midnight');
}

/** HTML échappé : mois abrégé à l'écran, en toutes lettres pour les lecteurs d'écran (« sept. » serait lu « sept »). */
function format_month(string $yearMonth): string
{
    $date = month_from($yearMonth);
    $short = MONTHS_SHORT[(int) $date->format('n')];
    $long = MONTHS_LONG[(int) $date->format('n')];
    $month = $short === $long ? e($short) : '<span aria-hidden="true">' . e($short) . '</span><span class="visually-hidden">' . e($long) . '</span>';

    return $month . ' ' . $date->format('Y');
}

/** Durée entre deux mois (fin exclue), au mois près : « 5 ans 2 mois », « 1 an », « 4 mois ». */
function format_duration(string $start, ?string $end = null): string
{
    $to = $end !== null ? month_from($end) : new DateTimeImmutable('first day of this month midnight');
    $diff = month_from($start)->diff($to);
    $months = max(1, $diff->y * 12 + $diff->m);
    $years = intdiv($months, 12);
    $rest = $months % 12;

    $parts = [];
    if ($years > 0) {
        $parts[] = $years . ' ' . ($years > 1 ? 'ans' : 'an');
    }
    if ($rest > 0) {
        $parts[] = $rest . ' mois';
    }

    return implode(' ', $parts);
}

/** Poids d'un fichier : « 85 Ko », « 1,2 Mo ». */
function format_file_size(int $bytes): string
{
    if ($bytes < 1024 ** 2) {
        return max(1, (int) round($bytes / 1024)) . "\u{00A0}Ko";
    }

    return str_replace('.', ',', (string) round($bytes / 1024 ** 2, 1)) . "\u{00A0}Mo";
}

/** CV en téléchargement (URL versionnée et poids), null tant que le PDF n'est pas déposé. */
function cv(): ?array
{
    $path = (string) content('cv.file');
    $file = ROOT . '/public/' . $path;
    if ($path === '' || !is_file($file)) {
        return null;
    }

    return ['label' => content('cv.label'), 'url' => asset($path), 'size' => format_file_size((int) filesize($file))];
}

function plural(int $count, string $singular, string $plural): string
{
    return $count . "\u{00A0}" . ($count > 1 ? $plural : $singular);
}

function icon(string $name, string $class = 'icon'): string
{
    return '<svg class="' . e($class) . '" aria-hidden="true" focusable="false"><use href="#i-' . e($name) . '"></use></svg>';
}

function full_name(): string
{
    return content('identity.firstName') . ' ' . content('identity.lastName');
}

/** Texte échappé dont les liens [libellé](cible) deviennent des balises <a>. */
function rich(string $text): string
{
    $parts = preg_split('/(\[[^\]]+\]\([^)\s]+\))/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

    return implode('', array_map(static function (string $part): string {
        if (!preg_match('/^\[([^\]]+)\]\(([^)\s]+)\)$/u', $part, $match)) {
            return t($part);
        }
        $external = str_starts_with($match[2], 'http');
        $href = $external ? $match[2] : url($match[2]);

        return '<a href="' . e($href) . '"' . ($external ? ' rel="noopener"' : '') . '>' . t($match[1]) . '</a>';
    }, $parts));
}

/** Configuration exposée au JavaScript : jamais de clé secrète ici. */
function public_config(): array
{
    return array_filter([
        'analytics' => config('googleAnalytics.measurementId') ? ['measurementId' => config('googleAnalytics.measurementId')] : null,
        'antispam' => config('recaptcha.siteKey') ? ['siteKey' => config('recaptcha.siteKey'), 'required' => recaptcha_required()] : null,
    ]);
}

function is_email(mixed $value): bool
{
    return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

function json_for_script(mixed $data): string
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR);
}

/** Découpe « Épinal (Vosges) » en ['Épinal', 'Vosges']. */
function split_location(string $location): array
{
    return preg_match('/^(.+?)\s*\((.+)\)$/u', $location, $match) ? [$match[1], $match[2]] : [$location, null];
}

function current_employer(): ?string
{
    foreach (content('experiences', []) as $experience) {
        foreach ($experience['positions'] as $position) {
            if ($position['end'] === null) {
                return $experience['company'];
            }
        }
    }

    return null;
}
