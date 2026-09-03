import path from 'node:path';
import { fileURLToPath } from 'node:url';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        react(),
    ],
    build: {
        cssMinify: 'esbuild',
        rolldownOptions: {
            checks: {
                pluginTimings: false,
            },
            output: {
                strictExecutionOrder: true,
                codeSplitting: {
                    groups: [
                        {
                            name: 'atlaskit',
                            test: /node_modules[\\/]@atlaskit[\\/]/,
                            maxSize: 450_000,
                        },
                    ],
                },
            },
        },
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
});
