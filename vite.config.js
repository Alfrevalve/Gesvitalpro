import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/core.js'
            ],
            refresh: true,
        }),
    ],
    build: {
        chunkSizeWarningLimit: 2000,
        cssCodeSplit: true,
        minify: 'terser',
        sourcemap: true,
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: [
                        'jquery',
                        '@popperjs/core',
                        'bootstrap',
                        'perfect-scrollbar'
                    ],
                },
            },
        },
        terserOptions: {
            compress: {
                drop_console: true,
                drop_debugger: true,
            },
            format: {
                comments: false,
            },
        },
    },
    optimizeDeps: {
        include: [
            'jquery',
            '@popperjs/core',
            'bootstrap',
            'perfect-scrollbar',
            'sass',
            'postcss'
        ],
        exclude: [
            'jsdom',
            'velocity-animate'
        ]
    },
    server: {
        hmr: {
            overlay: false
        },
        watch: {
            usePolling: true,
            interval: 1000
        }
    },
    css: {
        preprocessorOptions: {
            scss: {
                additionalData: `@import "resources/scss/variables";`
            }
        }
    }
});
