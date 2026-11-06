import { build, context } from 'esbuild';
import { rm } from 'node:fs/promises';

// Chaque module est minifié séparément, sans bundle : ses imports relatifs restent valables dans public/assets/js/.
const SOURCE = 'js';
const OUTPUT = 'public/assets/js';
const watch = process.argv.includes('--watch');

const options = {
    entryPoints: [`${SOURCE}/*.js`],
    outdir: OUTPUT,
    minify: !watch,
    charset: 'utf8',
    logLevel: 'info',
};

await rm(OUTPUT, { recursive: true, force: true });

if (watch) {
    await (await context(options)).watch();
} else {
    await build(options);
}
