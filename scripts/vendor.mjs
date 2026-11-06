import { copyFile, mkdir, rm } from 'node:fs/promises';

// Bibliothèques servies depuis le site (aucun CDN) : copiées de node_modules, version fixée dans package.json.
const VENDORS = [
    {
        source: 'node_modules/vanilla-cookieconsent',
        output: 'public/assets/vendor/cookieconsent',
        files: {'dist/cookieconsent.esm.js': 'cookieconsent.esm.js', 'dist/cookieconsent.css': 'cookieconsent.css', LICENSE: 'LICENSE'},
    },
];

for (const {source, output, files} of VENDORS) {
    await rm(output, {recursive: true, force: true});
    await mkdir(output, {recursive: true});
    for (const [from, to] of Object.entries(files)) {
        await copyFile(`${source}/${from}`, `${output}/${to}`);
    }
    console.log(`${source} → ${output}`);
}
