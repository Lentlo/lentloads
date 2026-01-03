/**
 * Capacitor Build Script
 * Builds the mobile app assets and generates the index.html for Capacitor.
 */

import { execSync } from 'child_process';
import { readFileSync, writeFileSync, existsSync, mkdirSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = join(__dirname, '..');
const buildDir = join(rootDir, 'public/build');

console.log('🚀 Building Capacitor app...\n');

// Step 1: Run Vite build with Capacitor config
console.log('📦 Building with Vite...');
try {
  execSync('npx vite build --config vite.config.capacitor.js', {
    cwd: rootDir,
    stdio: 'inherit',
  });
} catch (error) {
  console.error('❌ Vite build failed');
  process.exit(1);
}

// Step 2: Read the manifest to get the built file names
console.log('\n📄 Generating index.html...');
const manifestPath = join(buildDir, '.vite/manifest.json');

if (!existsSync(manifestPath)) {
  console.error('❌ Manifest not found at:', manifestPath);
  process.exit(1);
}

const manifest = JSON.parse(readFileSync(manifestPath, 'utf-8'));

// Find the main entry point
const mainEntry = manifest['resources/js/main-capacitor.js'];
if (!mainEntry) {
  console.error('❌ Could not find main-capacitor.js in manifest');
  console.log('Available entries:', Object.keys(manifest));
  process.exit(1);
}

const mainJs = mainEntry.file;
const mainCss = mainEntry.css?.[0] || '';

console.log(`  - JS: ${mainJs}`);
console.log(`  - CSS: ${mainCss}`);

// Step 3: Generate the index.html for Capacitor
const indexHtml = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1, user-scalable=no">

    <title>Lentlo Ads</title>
    <meta name="description" content="Buy and sell anything locally.">

    <!-- Mobile App Meta Tags -->
    <meta name="theme-color" content="#667eea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="format-detection" content="telephone=no">
    <meta name="msapplication-tap-highlight" content="no">

    <!-- Preload fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Main CSS -->
    ${mainCss ? `<link rel="stylesheet" href="${mainCss}">` : ''}

    <style>
        /* Critical CSS for initial render */
        *, *::before, *::after {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow-x: hidden;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background: #f8fafc;
            color: #1e293b;
        }

        #app {
            min-height: 100vh;
            min-height: 100dvh;
        }

        /* Loading state */
        .app-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            min-height: 100dvh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }

        .app-loading .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .app-loading .text {
            margin-top: 20px;
            font-size: 16px;
            font-weight: 500;
            opacity: 0.9;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Error state */
        .app-error {
            padding: 24px;
            margin: 20px;
            background: #fee2e2;
            border-radius: 12px;
            text-align: center;
        }

        .app-error h2 {
            color: #dc2626;
            margin: 0 0 12px 0;
            font-size: 20px;
        }

        .app-error p {
            color: #7f1d1d;
            margin: 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="app-loading">
            <div class="spinner"></div>
            <div class="text">Loading...</div>
        </div>
    </div>

    <script type="module" src="${mainJs}"></script>

    <script>
        // Global error handler for script loading failures
        window.onerror = function(msg, url, line, col, error) {
            var app = document.getElementById('app');
            if (app) {
                app.innerHTML = '<div class="app-error"><h2>Loading Error</h2><p>' +
                    (error ? error.message : msg) + '</p></div>';
            }
            return false;
        };
    </script>
</body>
</html>`;

writeFileSync(join(buildDir, 'index.html'), indexHtml);
console.log('  - index.html generated');

// Step 4: Sync with Capacitor
console.log('\n📱 Syncing with Capacitor...');
try {
  execSync('npx cap sync', {
    cwd: rootDir,
    stdio: 'inherit',
  });
} catch (error) {
  console.error('❌ Capacitor sync failed');
  process.exit(1);
}

console.log('\n✅ Build complete! Ready for Android Studio.');
console.log('\nNext steps:');
console.log('  1. Run: npx cap open android');
console.log('  2. Or build APK: cd android && ./gradlew assembleDebug');
