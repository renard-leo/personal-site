import sharp from 'sharp';
import { mkdir, stat } from 'node:fs/promises';

const SOURCE = 'resources/images/leo-renard.jpg';
const OUTPUT = 'public/assets/img';

// Zones de recadrage exprimées en pixels de l'image source (4912 × 7360).
const VARIANTS = [
    {name: 'leo-renard', crop: {left: 442, top: 1030, width: 4048, height: 5060}, widths: [320, 480, 640, 800, 1040], formats: ['avif', 'webp', 'jpeg']},
    {name: 'og-image', crop: {left: 0, top: 1398, width: 4912, height: 2578}, widths: [1200], formats: ['jpeg']},
];

const ENCODERS = {
    avif: {quality: 50, effort: 6},
    webp: {quality: 74, effort: 6},
    jpeg: {quality: 78, mozjpeg: true, progressive: true},
};

const EXTENSIONS = {avif: 'avif', webp: 'webp', jpeg: 'jpg'};

await mkdir(OUTPUT, {recursive: true});

for (const variant of VARIANTS) {
    for (const width of variant.widths) {
        for (const format of variant.formats) {
            const suffix = variant.widths.length > 1 ? `-${width}` : '';
            const file = `${OUTPUT}/${variant.name}${suffix}.${EXTENSIONS[format]}`;
            await sharp(SOURCE).rotate().extract(variant.crop).resize({width}).greyscale().toFormat(format, ENCODERS[format]).toFile(file);
            const {size} = await stat(file);
            console.log(`${file} ${(size / 1024).toFixed(1)} Ko`);
        }
    }
}
