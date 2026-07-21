// Generates favicons, touch icons, PWA manifest icons and the Open Graph image
// from resources/brand/mark.svg. Adapted from the nexo-links canonical generator
// (CATALOG). Rule: fill/stroke live on the SVG's paths, never the root <svg>.
// Run after changing the mark: node scripts/generate-brand-assets.mjs
import sharp from 'sharp';
import pngToIco from 'png-to-ico';
import { readFileSync, writeFileSync, copyFileSync } from 'node:fs';

const mark = readFileSync('resources/brand/mark.svg');

// Favicon + app icons (transparent background).
const sizes = {
    'public/favicon-16.png': 16,
    'public/favicon-32.png': 32,
    'public/apple-touch-icon.png': 180,
    'public/icon-192.png': 192,
    'public/icon-512.png': 512,
};

for (const [file, size] of Object.entries(sizes)) {
    await sharp(mark).resize(size, size).png().toFile(file);
    console.log(`✓ ${file}`);
}

// Explicit sizes keep the ico small (the default embeds a 256px layer).
writeFileSync('public/favicon.ico', await pngToIco(['public/favicon-16.png', 'public/favicon-32.png']));
console.log('✓ public/favicon.ico');

copyFileSync('resources/brand/mark.svg', 'public/favicon.svg');
console.log('✓ public/favicon.svg');

// Open Graph card (1200x630) — dark background with the centered mark.
const og = `<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630">
    <defs>
        <linearGradient id="g" x1="0" y1="0" x2="64" y2="64" gradientUnits="userSpaceOnUse">
            <stop stop-color="#6366f1"/>
            <stop offset="1" stop-color="#d946ef"/>
        </linearGradient>
    </defs>
    <rect width="1200" height="630" fill="#0a0a0a"/>
    <g transform="translate(524 227) scale(2.5) rotate(-45 32 32)" fill="none" stroke="url(#g)" stroke-width="7" stroke-linecap="round">
        <path d="M27 20 h-5 a12 12 0 0 0 0 24 h5"/>
        <path d="M37 20 h5 a12 12 0 0 1 0 24 h-5"/>
        <path d="M24 32 h16"/>
    </g>
    <text x="600" y="470" fill="#fafafa" font-family="system-ui, sans-serif" font-size="52" font-weight="700" text-anchor="middle">Nexo Short</text>
</svg>`;

await sharp(Buffer.from(og)).png().toFile('public/og.png');
console.log('✓ public/og.png');
