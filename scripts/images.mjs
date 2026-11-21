import sharp from 'sharp';
import { mkdir, readdir, stat } from 'node:fs/promises';
import { basename, dirname } from 'node:path';

const PORTRAIT = 'resources/images/leo-renard.jpg';
const PROJECTS = 'resources/images/projects';
const OUTPUT = 'public/assets/img';

// Doit rester aligné sur PROJECT_IMAGE_WIDTHS dans src/helpers.php.
const PROJECT_WIDTHS = [480, 800, 1200, 1600];

// Portrait : zones de recadrage en pixels de l'image source (4912 × 7360), en niveaux de gris.
// Projets : une capture 16:9 par projet, en couleurs, plus son image de partage recadrée par le haut ;
// les captures de sa démarche sont dans le dossier du même nom (<slug>/<nom>.png), en 16:9 aussi.
const entries = await readdir(PROJECTS, {withFileTypes: true});
const pngs = (names) => names.filter((name) => name.endsWith('.png')).map((name) => basename(name, '.png'));
const covers = pngs(entries.filter((entry) => entry.isFile()).map(({name}) => name));
const captures = (await Promise.all(entries.filter((entry) => entry.isDirectory()).map(async ({name: slug}) => pngs(await readdir(`${PROJECTS}/${slug}`)).map((name) => `${slug}/${name}`)))).flat();
const VARIANTS = [
    {source: PORTRAIT, name: 'leo-renard', crop: {left: 442, top: 1030, width: 4048, height: 5060}, greyscale: true, widths: [320, 480, 640, 800, 1040], formats: ['avif', 'webp', 'jpeg']},
    {source: PORTRAIT, name: 'og-image', crop: {left: 0, top: 1398, width: 4912, height: 2578}, greyscale: true, widths: [1200], formats: ['jpeg']},
    ...covers.flatMap((name) => [
        {source: `${PROJECTS}/${name}.png`, name: `projects/${name}`, widths: PROJECT_WIDTHS, formats: ['avif', 'webp', 'jpeg']},
        {source: `${PROJECTS}/${name}.png`, name: `projects/${name}-og`, height: 630, widths: [1200], formats: ['jpeg']},
    ]),
    ...captures.map((name) => ({source: `${PROJECTS}/${name}.png`, name: `projects/${name}`, widths: PROJECT_WIDTHS, formats: ['avif', 'webp', 'jpeg']})),
];

const ENCODERS = {
    avif: {quality: 50, effort: 6},
    webp: {quality: 74, effort: 6},
    jpeg: {quality: 78, mozjpeg: true, progressive: true},
};

const EXTENSIONS = {avif: 'avif', webp: 'webp', jpeg: 'jpg'};

for (const variant of VARIANTS) {
    for (const width of variant.widths) {
        for (const format of variant.formats) {
            const suffix = variant.widths.length > 1 ? `-${width}` : '';
            const file = `${OUTPUT}/${variant.name}${suffix}.${EXTENSIONS[format]}`;
            await mkdir(dirname(file), {recursive: true});
            let image = sharp(variant.source).rotate();
            if (variant.crop) {
                image = image.extract(variant.crop);
            }
            image = image.resize({width, height: variant.height, position: 'top'});
            if (variant.greyscale) {
                image = image.greyscale();
            }
            await image.toFormat(format, ENCODERS[format]).toFile(file);
            const {size} = await stat(file);
            console.log(`${file} ${(size / 1024).toFixed(1)} Ko`);
        }
    }
}
