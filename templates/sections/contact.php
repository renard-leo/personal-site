<?php

declare(strict_types=1);

/** @var array $nav */

$status = $_GET['contact'] ?? null;
$messages = [
    'sent' => ['type' => 'success', 'icon' => 'check', 'text' => 'Merci, votre message a bien été envoyé. Je vous réponds dès que possible.'],
    'error' => ['type' => 'error', 'icon' => 'alert', 'text' => 'Votre message n’a pas pu être envoyé. Vérifiez les champs puis réessayez, ou écrivez-moi sur LinkedIn.'],
    'antispam' => ['type' => 'error', 'icon' => 'alert', 'text' => 'Votre message n’a pas été envoyé : ce formulaire nécessite JavaScript et votre accord pour la protection anti-spam Google reCAPTCHA. Vous pouvez aussi m’écrire sur LinkedIn.'],
];
$message = is_string($status) ? ($messages[$status] ?? null) : null;
// reCAPTCHA obligatoire : bouton désactivé tant que le consentement manque (contact-form.js le réactive).
$antispamGate = recaptcha_required();
$linkedin = content('links.linkedin.url');
$cv = cv();

$fields = [
    ['name' => 'name', 'label' => 'Nom', 'type' => 'text', 'autocomplete' => 'name', 'maxlength' => 100],
    ['name' => 'email', 'label' => 'Adresse e-mail', 'hint' => 'Format attendu : nom@entreprise.fr', 'type' => 'email', 'autocomplete' => 'email', 'maxlength' => 254],
    ['name' => 'subject', 'label' => 'Objet', 'type' => 'text', 'autocomplete' => 'off', 'maxlength' => 150],
    ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'maxlength' => 5000],
];
?>
        <section class="section section-contact" id="contact" aria-labelledby="contact-title">
            <div class="container contact-grid">
                <div class="contact-intro">
<?= render('partials/section-head', ['id' => 'contact', 'title' => $nav['contact']]) ?>
                    <p class="contact-lead" data-reveal="paint"><?= t(content(open_to_work() ? 'openToWork.contactIntro' : 'contact.intro')) ?></p>
                    <ul class="contact-links" role="list" data-reveal="list">
<?php if ($cv): ?>
                        <li><a class="contact-link contact-link-download" href="<?= e($cv['url']) ?>" type="application/pdf" download><?= icon('document') ?><span><?= t($cv['label']) ?></span><span class="contact-link-meta"><?= t('PDF, ' . $cv['size']) ?></span><?= icon('download', 'icon icon-trailing') ?></a></li>
<?php endif; ?>
<?php foreach (content('links', []) as $network => $link): ?>
                        <li><a class="contact-link" href="<?= e($link['url']) ?>" rel="me noopener"><?= icon($network) ?><span><?= t($link['label']) ?></span><?= icon('external', 'icon icon-trailing') ?></a></li>
<?php endforeach; ?>
                    </ul>
                </div>
                <div class="contact-card" data-reveal="frame">
                    <form class="contact-form" method="post" action="contact.php" data-contact-form>
                        <div class="form-status<?= $message ? ' is-' . e($message['type']) : '' ?>" role="status" data-form-status>
                            <?= $message ? icon($message['icon']) : '' ?><p class="form-status-text"><?= $message ? t($message['text']) : '' ?></p>
                        </div>
                        <p class="form-required" id="contact-required">Tous les champs sont obligatoires.</p>
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="recaptcha_token" value="">
                        <div class="form-trap" aria-hidden="true" inert>
                            <label for="contact-website">Site web (ne pas remplir)</label>
                            <input type="text" id="contact-website" name="website" tabindex="-1" autocomplete="off">
                        </div>
<?php foreach ($fields as $field):
    $id = 'contact-' . $field['name'];
    $describedBy = isset($field['hint']) ? $id . '-hint contact-required' : 'contact-required';
?>
                        <div class="field" data-field="<?= e($field['name']) ?>" data-reveal="field">
                            <label class="field-label" for="<?= e($id) ?>"><?= t($field['label']) ?></label>
<?php if (isset($field['hint'])): ?>
                            <p class="field-hint" id="<?= e($id) ?>-hint"><?= t($field['hint']) ?></p>
<?php endif; ?>
<?php if ($field['type'] === 'textarea'): ?>
                            <textarea class="field-input" id="<?= e($id) ?>" name="<?= e($field['name']) ?>" rows="6" maxlength="<?= e($field['maxlength']) ?>" required aria-describedby="<?= e($describedBy) ?>"></textarea>
<?php else: ?>
                            <input class="field-input" type="<?= e($field['type']) ?>" id="<?= e($id) ?>" name="<?= e($field['name']) ?>" autocomplete="<?= e($field['autocomplete']) ?>" maxlength="<?= e($field['maxlength']) ?>" required aria-describedby="<?= e($describedBy) ?>"<?= $field['type'] === 'email' ? ' spellcheck="false"' : '' ?>>
<?php endif; ?>
                            <p class="field-error" id="<?= e($id) ?>-error" hidden><?= icon('alert') ?><span></span></p>
                        </div>
<?php endforeach; ?>
<?php if ($antispamGate): ?>
                        <div class="form-antispam" id="contact-antispam" tabindex="-1" data-antispam-gate>
                            <p class="form-antispam-text" id="contact-antispam-consent" data-antispam-message="consent"><?= icon('lock') ?><span><?= t('Pour envoyer votre message, autorisez la protection anti-spam Google reCAPTCHA.') ?><span class="no-js-only"> <?= t('Elle nécessite JavaScript, désactivé dans votre navigateur.') ?></span> <?= t('Vous préférez ne pas l’autoriser ?') ?> <a href="<?= e($linkedin) ?>" rel="me noopener"><?= t('Écrivez-moi sur LinkedIn') ?></a>.</span></p>
                            <p class="form-antispam-text" id="contact-antispam-error" data-antispam-message="error" hidden><?= icon('alert') ?><span><?= t('La protection anti-spam Google reCAPTCHA n’a pas pu se charger, peut-être à cause d’un bloqueur de publicités ou d’un réseau filtrant. Désactivez le blocage pour ce site puis rechargez la page, ou') ?> <a href="<?= e($linkedin) ?>" rel="me noopener"><?= t('écrivez-moi sur LinkedIn') ?></a>.</span></p>
                            <div class="form-antispam-actions js-only" data-antispam-actions>
                                <button class="button button-secondary" type="button" aria-describedby="contact-antispam-consent" data-antispam-accept>Autoriser reCAPTCHA</button>
                                <?= render('partials/consent-opener', ['class' => 'form-antispam-link']) ?>
                            </div>
                        </div>
<?php endif; ?>
                        <div class="form-actions" data-reveal="field">
                            <button class="button button-primary form-submit" type="submit" data-label-busy="Envoi en cours…"<?= $antispamGate ? ' aria-describedby="contact-antispam-consent" disabled' : '' ?>>
                                <span class="spinner" aria-hidden="true"></span><span class="form-submit-label">Envoyer le message</span>
                            </button>
                        </div>
                        <div class="form-notes">
                            <p class="form-legal"><?= icon('lock') ?><span><?= t('Vos données servent uniquement à vous répondre.') ?> <a href="<?= e(url('mentions-legales#donnees')) ?>">En savoir plus<span class="visually-hidden"> sur vos données personnelles</span></a></span></p>
<?php if (config('recaptcha.siteKey')): ?>
                            <p class="form-legal form-recaptcha" data-recaptcha-notice hidden><span><?= rich('Ce site est protégé par reCAPTCHA ; les [règles de confidentialité](https://policies.google.com/privacy) et [conditions d’utilisation](https://policies.google.com/terms) de Google s’appliquent.') ?></span></p>
<?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </section>
