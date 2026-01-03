import { readFileSync, writeFileSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = join(__dirname, '..');

// Read the Vite manifest
const manifestPath = join(rootDir, 'public/build/manifest.json');
const manifest = JSON.parse(readFileSync(manifestPath, 'utf-8'));

// Find the main entry point
const mainEntry = manifest['resources/js/main.js'];
if (!mainEntry) {
  console.error('Could not find main.js entry in manifest');
  process.exit(1);
}

const mainJs = mainEntry.file;
const mainCss = mainEntry.css?.[0] || '';

// Generate the index.html
const indexHtml = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <title>Lentlo Ads</title>
    <meta name="description" content="Buy and sell anything locally. Find great deals on new and used items near you.">

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#667eea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Lentlo Ads">

    <!-- Capacitor -->
    <meta name="format-detection" content="telephone=no">
    <meta name="msapplication-tap-highlight" content="no">

    <!-- Favicon -->
    <link rel="icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    ${mainCss ? `<link rel="stylesheet" href="${mainCss}">` : ''}

    <style>
        /* Initial loading state */
        body {
            margin: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        #app:empty {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        #app:empty::after {
            content: '';
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div id="app"></div>

    <!-- Main App Script -->
    <script type="module" src="${mainJs}"></script>
</body>
</html>`;

// Write the index.html
const outputPath = join(rootDir, 'public/build/index.html');
writeFileSync(outputPath, indexHtml);

console.log(`Generated Capacitor index.html with:`);
console.log(`  - JS: ${mainJs}`);
console.log(`  - CSS: ${mainCss}`);
