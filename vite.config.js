import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';



// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/css/app.css', 'resources/js/app.js'],
//             refresh: true,
//         }),
//         vue(),
//     ],
// });

export default defineConfig({
    server: {
        host: '0.0.0.0', // Permet l'accès externe
        hmr: {
            host: '192.168.7.74', // Ton IP que tu as trouvée avec ipconfig
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
});
