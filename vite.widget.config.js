import { defineConfig } from 'vite';

// The embed widget is built on its own into public/widget.js, so it has a stable url
// that other sites can load.
export default defineConfig({
    publicDir: false,
    build: {
        outDir: 'public',
        emptyOutDir: false,
        lib: {
            entry: 'resources/widget/widget.ts',
            name: 'Praisewall',
            formats: ['iife'],
            fileName: () => 'widget.js',
        },
    },
});
