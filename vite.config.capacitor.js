/**
 * Vite Configuration for Capacitor Mobile Builds
 * Produces optimized output for Android/iOS apps.
 */

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
  // Use relative paths for Capacitor
  base: './',

  plugins: [
    laravel({
      input: ['resources/js/main-capacitor.js'],
      refresh: false, // No hot reload for production build
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
  ],

  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
      // Use mobile API service
      '@/services/api': path.resolve(__dirname, 'resources/js/services/api-mobile.js'),
    },
  },

  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    manifest: true,
    sourcemap: false,
    // Disable modulePreload to avoid CSS preload issues in Capacitor
    modulePreload: false,
    // Optimize for mobile
    target: 'es2015',
    cssTarget: 'chrome61',
    // Smaller chunks for faster loading
    chunkSizeWarningLimit: 500,
    rollupOptions: {
      output: {
        // Use hashed filenames for cache busting
        entryFileNames: 'assets/[name]-[hash].js',
        chunkFileNames: 'assets/[name]-[hash].js',
        assetFileNames: 'assets/[name]-[hash].[ext]',
        // Manual chunk splitting for better caching
        manualChunks: {
          'vue-vendor': ['vue', 'vue-router', 'pinia'],
          'ui-vendor': ['@heroicons/vue', 'vue3-toastify'],
        },
      },
    },
  },

  // Define globals
  define: {
    '__CAPACITOR_BUILD__': true,
    '__DEV__': false,
  },

  // CSS configuration
  css: {
    devSourcemap: false,
  },

  // Optimize dependencies
  optimizeDeps: {
    include: ['vue', 'vue-router', 'pinia', 'axios'],
  },
});
