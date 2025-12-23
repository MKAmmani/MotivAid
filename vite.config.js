import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import { VitePWA } from "vite-plugin-pwa"; // <-- Import the plugin

export default defineConfig({
    plugins: [
        laravel({
            input: "resources/js/app.js",
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        // Add the PWA Plugin configuration here
        VitePWA({
            registerType: "autoUpdate", // Automatically update the Service Worker
            devOptions: {
                enabled: true, // Recommended for testing during development
            },
            manifest: {
                name: "MotivAid",
                short_name: "MotivAid",
                description: "Install MotivAid as a Progressive Web App",
                theme_color: "#FF69B4", // Primary color of your app's UI
                background_color: "#FF69B4", // Splash screen background
                display: "standalone", // Makes it run in a separate window without browser controls
                icons: [
                    // Define all necessary icon sizes here (e.g., 192x192, 512x512)
                    {
                        src: "/images/pwa/pwa-192x192.png",
                        sizes: "192x192",
                        type: "image/png",
                    },
                    {
                        src: "/images/pwa/pwa-512x512.png",
                        sizes: "512x512",
                        type: "image/png",
                    },
                ],
            },
            // Workbox configuration for Caching (Crucial for offline Inertia/Vue)
            workbox: {
                globPatterns: ["**/*.{js,css,html,ico,png,svg}"],
                // Optional: Define a specific handler for your Laravel entry blade file (app.blade.php)
                runtimeCaching: [
                    {
                        urlPattern: ({ request }) =>
                            request.mode === "navigate",
                        handler: "NetworkFirst", // Prioritize network, but fall back to cache
                        options: {
                            cacheName: "html-cache",
                        },
                    },
                ],
            },
        }),
    ],
});
