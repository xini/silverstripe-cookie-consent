import { defineConfig } from 'vite';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import {
    JS_ENTRIES,
    CSS_ENTRIES,
} from './vite.paths.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

// Custom plugin to clean dist folder once at startup
function cleanDistOnce() {
    let cleaned = false;
    return {
        name: 'clean-dist-once',
        apply: 'build',
        enforce: 'pre',
        buildStart() {
            if (!cleaned) {
                const distPath = path.resolve(__dirname, 'dist');
                if (fs.existsSync(distPath)) {
                    console.log('Cleaning dist folder (initial build)...');
                    fs.rmSync(distPath, { recursive: true, force: true });
                }
                cleaned = true;
            }
        }
    };
}

export default defineConfig(({ command, mode }) => {
    const isProduction = mode === 'production';
    const isDebug = mode === 'debug';
    const stripConsole = !isDebug;

    return {
        plugins: [
            cleanDistOnce(), // Clean once at start

            // Suppress Vite warnings about SilverStripe runtime asset paths
            {
                name: 'suppress-silverstripe-asset-warnings',
                apply: 'build',
                enforce: 'pre',
                configResolved(config) {
                    const originalWarn = config.logger.warn;
                    const originalWarnOnce = config.logger.warnOnce;

                    config.logger.warn = (msg, options) => {
                        if (typeof msg === 'string' &&
                            msg.includes('/_resources/') &&
                            msg.includes("didn't resolve at build time")) {
                            return;
                        }
                        originalWarn(msg, options);
                    };

                    config.logger.warnOnce = (msg, options) => {
                        if (typeof msg === 'string' &&
                            msg.includes('/_resources/') &&
                            msg.includes("didn't resolve at build time")) {
                            return;
                        }
                        originalWarnOnce(msg, options);
                    };
                },
            },
        ],

        css: {
            postcss: './postcss.config.js',
            sourcemap: true,
            devSourcemap: true,
        },

        root: '.',

        build: {
            outDir: 'dist',
            assetsDir: '',
            emptyOutDir: false, // Disable auto-clean, managed by cleanDistOnce
            sourcemap: true,
            manifest: false,

            minify: isDebug ? false : 'terser',
            cssMinify: isDebug ? false : true,
            terserOptions: {
                compress: {
                    drop_console: stripConsole,
                    drop_debugger: true,
                },
                mangle: false,
            },

            rollupOptions: {
                onwarn(warning, warn) {
                    if (warning.message?.includes('/_resources/') &&
                        warning.message?.includes("didn't resolve at build time")) {
                        return;
                    }
                    warn(warning);
                },

                input: {
                    ...Object.fromEntries(
                        Object.entries(JS_ENTRIES).map(([key, path]) => [`js:${key}`, path])
                    ),
                    ...Object.fromEntries(
                        Object.entries(CSS_ENTRIES).map(([key, path]) => [`css:${key}`, path])
                    ),
                },
                output: {
                    entryFileNames: (chunkInfo) => {
                        const name = chunkInfo.name.replace(/^js[_:]/, '');
                        return `js/${name}.js`;
                    },
                    chunkFileNames: 'js/chunks/[name]-[hash].js',
                    assetFileNames: (assetInfo) => {
                        if (assetInfo.name?.endsWith('.css')) {
                            const name = assetInfo.name.replace(/^css[_:]/, '').replace(/\.css$/, '');
                            return `css/${name}.css`;
                        }
                        if (assetInfo.name?.match(/\.(png|jpg|jpeg|gif|webp|avif)$/)) {
                            return 'images/[name][extname]';
                        }
                        if (assetInfo.name?.match(/\.(woff2|woff|ttf|otf|eot)$/)) {
                            return 'fonts/[name][extname]';
                        }
                        return 'assets/[name][extname]';
                    },
                },
            }
        }
    };
});
