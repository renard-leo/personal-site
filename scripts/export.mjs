import { execFileSync } from 'node:child_process';
import { existsSync } from 'node:fs';
import { copyFile, cp, mkdir, readdir, readFile, realpath, rm, writeFile } from 'node:fs/promises';
import { basename, dirname, extname, resolve } from 'node:path';

// Dossier site/ du portfolio 3D : contenu du site, complété par OVERLAY (ce qui est propre au portfolio,
// même arborescence, prioritaire). Non versionné ; peut être une jonction vers le dossier site/ du portfolio.
const MODES = ['prod', 'preprod'];
const OVERLAY = 'resources/export';
const OUTPUT = 'export';
const LANG = 'fr';
const PHOTO = 'resources/images/leo-renard.jpg';
const LOGO = 'resources/images/logo.svg';
const SHARE_IMAGE = 'public/assets/img/og-image.jpg';
// Page HTML de chaque projet ({{clé}} remplacées), exclue de la copie de OVERLAY.
const PAGE_TEMPLATE = `${OVERLAY}/projects/_page.html`;
const projectImage = (slug) => `public/assets/img/projects/${slug}-1600.webp`;
// Places sur les étagères du portfolio.
const SHELF_MAX = 8;

// Seule cette clé de la configuration sort de PHP : aucun secret dans l'export.
const READ_SITE = `<?php
$config = require 'config/' . (is_file('config/config.php') ? 'config.php' : 'config.example.php');
echo json_encode([
    'content' => require 'src/content.php',
    'openToWork' => ($config['openToWork'] ?? false) === true,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
`;

const fail = (message) => {
    console.error(`✗ ${message}`);
    process.exit(1);
};

const isObject = (value) => value !== null && typeof value === 'object' && !Array.isArray(value);

// Objets fusionnés clé par clé ; toute autre valeur de OVERLAY remplace celle du site.
const merge = (base, extra) => (isObject(base) && isObject(extra)
    ? Object.fromEntries([...new Set([...Object.keys(base), ...Object.keys(extra)])].map((key) => [key, key in extra ? merge(base[key], extra[key]) : base[key]]))
    : extra);

const ascii = (text) => text.normalize('NFD').replace(/\p{Diacritic}/gu, '');

const html = (text) => String(text).replace(/[&<>"]/g, (char) => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;'})[char]);

// Adresse publique lue dans l'environnement ; vide : aucun lien vers cette adresse.
function readUrl(name, role) {
    const url = process.env[name];
    if (url === undefined) {
        fail(`variable d’environnement ${name} manquante (à définir dans .env.${mode}) : ${role}.`);
    }
    if (url !== '' && !URL.canParse(url)) {
        fail(`${name} n’est pas une URL valide : ${url}`);
    }
    return url.replace(/\/+$/, '');
}

function readSite() {
    try {
        return JSON.parse(execFileSync(process.env.PHP || 'php', ['-d', 'display_errors=stderr'], {input: READ_SITE, encoding: 'utf8'}));
    } catch (error) {
        fail(error.code === 'ENOENT' ? 'PHP introuvable : l’ajouter au PATH ou indiquer son chemin dans la variable d’environnement PHP.' : `lecture du contenu impossible : ${error.message}`);
    }
}

async function readOverlay(path) {
    const file = `${OVERLAY}/${path}`;
    if (!existsSync(file)) {
        return {};
    }
    try {
        return JSON.parse(await readFile(file, 'utf8'));
    } catch (error) {
        fail(`JSON invalide dans ${file} : ${error.message}`);
    }
}

// Fichiers copiés tels quels : chemin dans OUTPUT → fichier source.
const copies = {};

function copy(source, target) {
    if (!existsSync(source)) {
        fail(`fichier introuvable : ${source}${source.startsWith('public/assets/img/') ? ' (créé par npm run build:images)' : ''}`);
    }
    copies[target] = source;
    return target;
}

// Fichier de assets/ : renvoie son chemin dans ce dossier, tel que l'attend le portfolio.
function asset(source, target = basename(source)) {
    copy(source, `assets/${target}`);
    return target;
}

// prod par défaut, ou preprod : adresses publiques lues dans .env.<mode>, où une variable déjà présente
// dans l'environnement l'emporte ; les projets pas encore en ligne ne sont exportés qu'en préprod.
const [mode = 'prod'] = process.argv.slice(2);
if (!MODES.includes(mode)) {
    fail(`mode inconnu : ${mode} (${MODES.join(' ou ')}).`);
}
if (existsSync(`.env.${mode}`)) {
    process.loadEnvFile(`.env.${mode}`);
}

const siteUrl = readUrl('PUBLIC_SITE_URL', 'adresse publique du site, cible des liens « Version simple » et du formulaire de contact');
const projectsUrl = readUrl('PUBLIC_PROJECTS_URL', 'adresse où sont publiés les projets, préfixe de leurs liens relatifs');
const {content, openToWork} = readSite();
const {identity} = content;
const work = openToWork ? content.openToWork : null;
const name = `${identity.firstName} ${identity.lastName}`;
const city = identity.location.match(/^(.+?)\s*\((.+)\)$/)?.[1] ?? identity.location;
const cv = asset(`public/${content.cv.file}`);

// Même règle que project_url() dans src/helpers.php.
const projectUrl = (url) => {
    if (!url || /^https?:\/\//.test(url)) {
        return url || '';
    }
    return projectsUrl ? `${projectsUrl}/${url.replace(/^\/+/, '')}` : '';
};

// Même règle que projects() dans src/helpers.php.
const projects = (content.projects ?? []).filter((project) => mode === 'preprod' || project.prod !== false);
if (projects.length > SHELF_MAX) {
    console.warn(`⚠ ${projects.length} projets sur le site : seuls les ${SHELF_MAX} premiers tiennent sur les étagères.`);
}
const shelf = projects.slice(0, SHELF_MAX);

const generated = {
    'site.json': {
        name,
        careerStart: identity.careerStart,
        languages: [LANG],
        defaultLanguage: LANG,
        simpleUrl: siteUrl,
        files: {
            logo: asset(LOGO),
            photo: {file: asset(PHOTO, 'profile.jpg')},
            cv: {file: cv, downloadName: `CV-${ascii(name).replaceAll(' ', '-')}${extname(cv)}`},
            shareImage: asset(SHARE_IMAGE),
        },
        projects: shelf.map(({slug}) => slug),
    },
    'contact.json': {
        email: '',
        form: siteUrl ? `${siteUrl}/#contact` : '',
        links: Object.entries(content.links).map(([id, {label, url}]) => ({id, label, url})),
    },
    // Mêmes titre et description que l'accueil du site (public/index.php).
    [`i18n/${LANG}/site.json`]: {
        title: `{name} · ${identity.role}`,
        description: `{name}, ${identity.role.toLowerCase()} à ${city}, {experience} d’expérience. ${content.hero.lead}`,
    },
    [`i18n/${LANG}/profile.json`]: {
        role: identity.role,
        location: identity.location,
        status: work?.status ?? '',
        details: work ? [work.workModes, work.drivingLicence].filter(Boolean) : [],
        pitch: content.hero.lead,
        about: content.about.paragraphs,
        projectTypes: content.about.projectTypes,
        qualities: content.about.beyondCode,
        skills: content.skills.map(({title, items}) => ({group: title, items})),
        photoAlt: 'Portrait de {name}',
    },
    [`i18n/${LANG}/career.json`]: {
        experiences: content.experiences.map((experience) => ({
            ...experience,
            description: experience.description ?? '',
            logo: experience.logo ? asset(`public/${experience.logo}`, `logos/${basename(experience.logo)}`) : '',
        })),
        education: content.education,
    },
    [`i18n/${LANG}/contact.json`]: {
        intro: work?.contactIntro ?? content.contact.intro,
    },
    [`i18n/${LANG}/projects.json`]: Object.fromEntries(shelf.map(({slug, title}) => [slug, {title}])),
    // L'objet posé sur l'étagère (prop) est choisi par le portfolio 3D d'après le slug.
    ...Object.fromEntries(shelf.map(({slug, year}) => [`projects/${slug}/project.json`, {year: Number(year) || year}])),
};

const template = await readFile(PAGE_TEMPLATE, 'utf8').catch(() => fail(`gabarit introuvable : ${PAGE_TEMPLATE}`));

// Liens [libellé](cible) des textes, comme rich() dans src/helpers.php ; une cible relative part de l'adresse du site.
const richHtml = (text) => text.split(/(\[[^\]]+\]\([^)\s]+\))/).map((part) => {
    const [, label, target] = part.match(/^\[([^\]]+)\]\(([^)\s]+)\)$/) ?? [];
    if (!label) {
        return html(part);
    }
    const href = /^https?:\/\//.test(target) ? target : siteUrl && `${siteUrl}/${target.replace(/^\/+/, '')}`;
    return href ? `<a href="${html(href)}">${html(label)}</a>` : html(label);
}).join('');

// Capture d'une étape de la démarche, copiée à côté de la page.
function figure(slug, {file, alt, caption}) {
    const image = `images/${file}.webp`;
    copy(projectImage(`${slug}/${file}`), `projects/${slug}/${image}`);
    return [
        '    <figure>',
        `        <img src="${image}" alt="${html(alt)}" width="1600" height="900">`,
        ...(caption ? [`        <figcaption>${html(caption)}</figcaption>`] : []),
        '    </figure>',
    ].join('\n');
}

function renderPage(project) {
    const links = [[projectUrl(project.url), 'Voir le projet'], [project.code, 'Voir le code']].filter(([href]) => href);
    const list = (items, className) => [`    <ul${className ? ` class="${className}"` : ''}>`, ...items.map((item) => `        <li>${html(item)}</li>`), '    </ul>'].join('\n');
    const process = (project.process ?? []).flatMap(({title, text, images = []}) => [
        `    <h3>${html(title)}</h3>`,
        ...[text].flat().map((paragraph) => `    <p>${richHtml(paragraph)}</p>`),
        ...images.map((image) => figure(project.slug, image)),
    ]);
    const values = {
        lang: LANG,
        title: html(project.title),
        eyebrow: html(`${project.year} · ${project.role}`),
        summary: html(project.summary),
        links: links.length ? `    <p class="links">${links.map(([href, label]) => `<a href="${html(href)}">${label}</a>`).join(' ')}</p>` : '',
        image: 'images/cover.webp',
        alt: html(project.imageAlt),
        description: project.description.map((paragraph) => `    <p>${html(paragraph)}</p>`).join('\n'),
        process: process.length ? ['    <h2>Démarche</h2>', ...process].join('\n') : '',
        highlights: project.highlights.length ? `    <h2>Points forts</h2>\n${list(project.highlights)}` : '',
        stack: project.stack.length ? `    <h2>Technologies</h2>\n${list(project.stack, 'tags')}` : '',
    };
    // Les clés vides laissent une ligne blanche, retirée ensuite.
    return template.replace(/\{\{(\w+)\}\}/g, (match, key) => values[key] ?? match).replace(/\n[ \t]*(?=\n)/g, '');
}

const pages = Object.fromEntries(shelf.map((project) => {
    copy(projectImage(project.slug), `projects/${project.slug}/images/cover.webp`);
    return [`projects/${project.slug}/${LANG}.html`, renderPage(project)];
}));

// Vide OUTPUT sans le supprimer : il peut être un lien vers le dossier site/ du portfolio 3D.
await mkdir(OUTPUT, {recursive: true});
for (const entry of await readdir(OUTPUT)) {
    await rm(`${OUTPUT}/${entry}`, {recursive: true, force: true});
}
await cp(OVERLAY, await realpath(OUTPUT), {recursive: true, filter: (source) => resolve(source) !== resolve(PAGE_TEMPLATE)});

for (const [path, data] of Object.entries(generated)) {
    const file = `${OUTPUT}/${path}`;
    await mkdir(dirname(file), {recursive: true});
    await writeFile(file, `${JSON.stringify(merge(data, await readOverlay(path)), null, 4)}\n`);
}

for (const [path, page] of Object.entries(pages)) {
    await writeFile(`${OUTPUT}/${path}`, page);
}

for (const [target, source] of Object.entries(copies)) {
    const file = `${OUTPUT}/${target}`;
    await mkdir(dirname(file), {recursive: true});
    await copyFile(source, file);
}

console.log(`${OUTPUT} (${mode}) : contenu du site et ${shelf.length} projet(s), complétés par ${OVERLAY}.`);
