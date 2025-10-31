/// <reference types="node" />
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Build a UMD library exposing window.FlowstoneUI
export default defineConfig({
  plugins: [react(), tailwindcss()],
  define: {
    'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'production'),
    // Some libs check `global` in UMD environments
    global: 'window',
  },
  css: {
    postcss: {
      plugins: [],
    },
  },
  build: {
    lib: {
      entry: path.resolve(__dirname, 'resources/js/flowstone-ui/init.ts'),
      name: 'FlowstoneUI',
      fileName: () => 'flowstone-ui.js',
      formats: ['umd'],
    },
    outDir: 'dist',
    emptyOutDir: true,
    sourcemap: true,
    rollupOptions: {
      output: {
        assetFileNames: (assetInfo: any) => {
          if (assetInfo.name && assetInfo.name.endsWith('.css')) {
            return 'flowstone-ui.css';
          }
          return assetInfo.name || 'asset-[hash][extname]';
        },
      },
    },
  },
});
