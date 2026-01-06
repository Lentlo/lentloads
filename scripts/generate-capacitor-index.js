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

// Generate the index.html for Capacitor (no leading slashes for local file loading)
const indexHtml = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1, user-scalable=no">

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

    <!-- Fonts - Load early -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS - Critical for layout -->
    ${mainCss ? `<link rel="stylesheet" href="${mainCss}">` : ''}

    <style>
        /* Critical inline styles for initial render */
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background: #f8fafc;
        }
        #app {
            min-height: 100vh;
        }
        /* Loading state - show spinner while Vue mounts */
        .app-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .app-loading .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        .app-loading .text {
            margin-top: 16px;
            font-size: 14px;
            opacity: 0.9;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        /* Error state */
        .app-error {
            padding: 20px;
            background: #fee2e2;
            color: #dc2626;
            margin: 20px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <!-- Initial loading state - Vue will replace this when it mounts -->
    <div id="app">
        <div class="app-loading">
            <div class="spinner"></div>
            <div class="text">Loading Lentlo Ads...</div>
        </div>
    </div>

    <!-- Fallback content if JS fails -->
    <noscript>
      <div style="padding:20px;text-align:center;">JavaScript is required to run this app.</div>
    </noscript>

    <!-- Main App Script -->
    <script type="module" src="${mainJs}"></script>

    <!-- Catch module loading errors -->
    <script type="module">
      window.addEventListener('error', function(e) {
        var app = document.getElementById('app');
        if (app && e.message) {
          app.innerHTML = '<div class="app-error"><h3>Error Loading App</h3><p>' + e.message + '</p></div>';
        }
      });
    </script>

    <!-- Debug: Show if module script fails to load -->
    <script nomodule>
      document.body.innerHTML = '<div style="padding:20px;color:red;">Your browser does not support ES modules.</div>';
    </script>
</body>
</html>`;

// Write the index.html
const outputPath = join(rootDir, 'public/build/index.html');
writeFileSync(outputPath, indexHtml);

console.log(`Generated Capacitor index.html with:`);
console.log(`  - JS: ${mainJs}`);
console.log(`  - CSS: ${mainCss}`);
