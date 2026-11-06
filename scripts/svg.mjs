import { readFile, writeFile } from 'node:fs/promises';

const SOURCE = 'resources/images/logo.svg';
const OUTPUTS = ['public/assets/img/logo.svg', 'public/favicon.svg'];

const source = await readFile(SOURCE, 'utf8');

const fills = Object.fromEntries([...source.matchAll(/\.(cls-\d+)\{fill:(#[0-9a-f]{3,6});\}/gi)].map(([, name, color]) => [name, color.toLowerCase()]));

const round = (value) => String(Math.round(parseFloat(value) * 10) / 10).replace(/^(-?)0\./, '$1.');

// Arrondit au dixième (viewBox de 1000) en gardant un séparateur seulement là où il est indispensable.
const compactPath = (d) => {
    let output = '';
    let previous = null;
    for (const [token] of d.matchAll(/[a-z]|-?(?:\d+\.?\d*|\.\d+)(?:e-?\d+)?/gi)) {
        if (/^[a-z]$/i.test(token)) {
            output += token;
            previous = null;
            continue;
        }
        const number = round(token);
        const needsSeparator = previous !== null && !number.startsWith('-') && !(number.startsWith('.') && previous.includes('.'));
        output += (needsSeparator ? ' ' : '') + number;
        previous = number;
    }
    return output;
};

const layers = [...source.matchAll(/<path\b([^>]*)\/>/g)].reduce((list, [, attributes]) => {
    const fill = fills[attributes.match(/class="([^"]+)"/)[1]];
    const d = compactPath(attributes.match(/\sd="([^"]+)"/)[1]);
    const last = list.at(-1);
    if (last?.fill === fill) {
        last.d += d;
    } else {
        list.push({fill, d});
    }
    return list;
}, []);

const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000.6">${layers.map(({fill, d}) => `<path fill="${fill}" d="${d}"/>`).join('')}</svg>\n`;

for (const file of OUTPUTS) {
    await writeFile(file, svg);
}

console.log(`${OUTPUTS.join(', ')} : ${(Buffer.byteLength(svg) / 1024).toFixed(1)} Ko (source ${(Buffer.byteLength(source) / 1024).toFixed(1)} Ko)`);
