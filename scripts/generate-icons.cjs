/**
 * Generate App Icons and Splash Screens
 * Creates icons with "L" letter on purple gradient background
 */

const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

// Colors from the app
const PRIMARY_COLOR = '#667eea';
const SECONDARY_COLOR = '#764ba2';

// Create SVG for app icon
function createIconSvg(size) {
  const fontSize = Math.round(size * 0.6);
  const padding = Math.round(size * 0.15);

  return `
    <svg width="${size}" height="${size}" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" style="stop-color:${PRIMARY_COLOR};stop-opacity:1" />
          <stop offset="100%" style="stop-color:${SECONDARY_COLOR};stop-opacity:1" />
        </linearGradient>
      </defs>
      <rect width="${size}" height="${size}" fill="url(#grad)" rx="${Math.round(size * 0.2)}" ry="${Math.round(size * 0.2)}"/>
      <text
        x="50%"
        y="55%"
        font-family="Arial, sans-serif"
        font-size="${fontSize}"
        font-weight="bold"
        fill="white"
        text-anchor="middle"
        dominant-baseline="middle"
      >L</text>
    </svg>
  `;
}

// Create SVG for foreground (adaptive icon)
function createForegroundSvg(size) {
  const fontSize = Math.round(size * 0.4);
  const iconSize = Math.round(size * 0.66);
  const offset = Math.round((size - iconSize) / 2);

  return `
    <svg width="${size}" height="${size}" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" style="stop-color:${PRIMARY_COLOR};stop-opacity:1" />
          <stop offset="100%" style="stop-color:${SECONDARY_COLOR};stop-opacity:1" />
        </linearGradient>
      </defs>
      <circle cx="${size/2}" cy="${size/2}" r="${iconSize/2}" fill="url(#grad)"/>
      <text
        x="50%"
        y="53%"
        font-family="Arial, sans-serif"
        font-size="${fontSize}"
        font-weight="bold"
        fill="white"
        text-anchor="middle"
        dominant-baseline="middle"
      >L</text>
    </svg>
  `;
}

// Create splash screen SVG
function createSplashSvg(width, height) {
  const iconSize = Math.min(width, height) * 0.25;
  const fontSize = iconSize * 0.6;

  return `
    <svg width="${width}" height="${height}" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" style="stop-color:${PRIMARY_COLOR};stop-opacity:1" />
          <stop offset="100%" style="stop-color:${SECONDARY_COLOR};stop-opacity:1" />
        </linearGradient>
      </defs>
      <rect width="${width}" height="${height}" fill="url(#grad)"/>
      <circle cx="${width/2}" cy="${height/2}" r="${iconSize/2}" fill="rgba(255,255,255,0.15)"/>
      <text
        x="50%"
        y="51%"
        font-family="Arial, sans-serif"
        font-size="${fontSize}"
        font-weight="bold"
        fill="white"
        text-anchor="middle"
        dominant-baseline="middle"
      >L</text>
      <text
        x="50%"
        y="${height/2 + iconSize/2 + 40}"
        font-family="Arial, sans-serif"
        font-size="${Math.round(fontSize * 0.4)}"
        font-weight="500"
        fill="rgba(255,255,255,0.9)"
        text-anchor="middle"
      >Lentlo Ads</text>
    </svg>
  `;
}

async function generateIcons() {
  const androidResDir = path.join(__dirname, '../android/app/src/main/res');

  // Icon sizes for Android
  const iconSizes = {
    'mipmap-mdpi': 48,
    'mipmap-hdpi': 72,
    'mipmap-xhdpi': 96,
    'mipmap-xxhdpi': 144,
    'mipmap-xxxhdpi': 192,
  };

  // Foreground sizes for adaptive icons
  const foregroundSizes = {
    'mipmap-mdpi': 108,
    'mipmap-hdpi': 162,
    'mipmap-xhdpi': 216,
    'mipmap-xxhdpi': 324,
    'mipmap-xxxhdpi': 432,
  };

  // Splash screen sizes
  const splashSizes = {
    'drawable-port-mdpi': { w: 320, h: 480 },
    'drawable-port-hdpi': { w: 480, h: 800 },
    'drawable-port-xhdpi': { w: 720, h: 1280 },
    'drawable-port-xxhdpi': { w: 960, h: 1600 },
    'drawable-port-xxxhdpi': { w: 1280, h: 1920 },
    'drawable-land-mdpi': { w: 480, h: 320 },
    'drawable-land-hdpi': { w: 800, h: 480 },
    'drawable-land-xhdpi': { w: 1280, h: 720 },
    'drawable-land-xxhdpi': { w: 1600, h: 960 },
    'drawable-land-xxxhdpi': { w: 1920, h: 1280 },
  };

  console.log('🎨 Generating app icons...\n');

  // Generate launcher icons
  for (const [folder, size] of Object.entries(iconSizes)) {
    const dir = path.join(androidResDir, folder);
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
    }

    const svg = createIconSvg(size);

    await sharp(Buffer.from(svg))
      .png()
      .toFile(path.join(dir, 'ic_launcher.png'));

    await sharp(Buffer.from(svg))
      .png()
      .toFile(path.join(dir, 'ic_launcher_round.png'));

    console.log(`  ✓ ${folder}/ic_launcher.png (${size}x${size})`);
  }

  console.log('\n🎨 Generating adaptive icon foregrounds...\n');

  // Generate foreground icons for adaptive icons
  for (const [folder, size] of Object.entries(foregroundSizes)) {
    const dir = path.join(androidResDir, folder);
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
    }

    const svg = createForegroundSvg(size);

    await sharp(Buffer.from(svg))
      .png()
      .toFile(path.join(dir, 'ic_launcher_foreground.png'));

    console.log(`  ✓ ${folder}/ic_launcher_foreground.png (${size}x${size})`);
  }

  console.log('\n🎨 Generating splash screens...\n');

  // Generate splash screens
  for (const [folder, dims] of Object.entries(splashSizes)) {
    const dir = path.join(androidResDir, folder);
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
    }

    const svg = createSplashSvg(dims.w, dims.h);

    await sharp(Buffer.from(svg))
      .png()
      .toFile(path.join(dir, 'splash.png'));

    console.log(`  ✓ ${folder}/splash.png (${dims.w}x${dims.h})`);
  }

  // Create drawable folder with default splash
  const drawableDir = path.join(androidResDir, 'drawable');
  if (!fs.existsSync(drawableDir)) {
    fs.mkdirSync(drawableDir, { recursive: true });
  }

  const defaultSplash = createSplashSvg(480, 800);
  await sharp(Buffer.from(defaultSplash))
    .png()
    .toFile(path.join(drawableDir, 'splash.png'));

  console.log(`  ✓ drawable/splash.png (default)`);

  console.log('\n✅ All icons and splash screens generated!');
}

generateIcons().catch(console.error);
