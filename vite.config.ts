import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

// Build a UMD library exposing window.FlowstoneUI
export default defineConfig({
  plugins: [react()],
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
      // Keep React and ReactDOM external to reduce bundle size; provide as UMD globals
      external: ['react', 'react-dom'],
      output: {
        globals: {
          react: 'React',
          'react-dom': 'ReactDOM',
        },
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
