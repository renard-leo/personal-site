import { rm, writeFile } from 'node:fs/promises';

// Mode lu par le site (src/bootstrap.php) : en prod, les projets marqués 'prod' => false sont masqués.
const MODES = ['prod', 'preprod'];
const FILE = 'config/build.php';
// Le sitemap liste les pages des projets : supprimé, PHP le régénère pour ce mode au prochain appel.
const SITEMAP = 'public/sitemap.xml';

const [mode = 'prod'] = process.argv.slice(2);
if (!MODES.includes(mode)) {
    console.error(`✗ mode inconnu : ${mode} (${MODES.join(' ou ')}).`);
    process.exit(1);
}

await writeFile(FILE, `<?php

declare(strict_types=1);

// Généré par npm run build (prod) ou npm run build:preprod : ne pas modifier.
return ['mode' => '${mode}'];
`);
await rm(SITEMAP, {force: true});

console.log(`${FILE} : mode ${mode}.`);
