import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    define: {
        'process.env': {
            GOOGLE_API_KEY: `'${process.env.GOOGLE_MAPS_API_KEY}'`,
        },
    },
});
