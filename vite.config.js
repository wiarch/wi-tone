import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/chord-visual-editor.js',
                'resources/js/chordpro-viewer.js',
                'resources/js/musician-tools.js',
                'resources/js/song-reader-sidebar.js',
                'resources/js/song-performance.js',
                'resources/js/song-export.js',
            ],
            refresh: true,
        }),
    ],
});
