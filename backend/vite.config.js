import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
  plugins: [
    laravel({
      input: [
        // CSS
        "resources/css/app.css",
        "resources/css/dashboard.css",
        "resources/css/ckeditor.css",
        // JS
        "resources/js/app.js",
        "resources/js/dashboard.js",
        "resources/js/ckeditor.js",
        "resources/js/file-input.js",
        "resources/js/chart.js",
        "resources/js/analytics.js",
      ],
      refresh: true,
    }),
    tailwindcss(),
  ],
});
