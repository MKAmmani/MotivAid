import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                "motivaid-pink": "#FF69B4",
                "hotpink-800": "#FF69B4", // Using the same pink color for hotpink-800
                primary: "#FF69B4",
                "background-light": "#fef5f8",
                "background-dark": "#1f1116",
                "primary-light": "#ffeaf4",
                "primary-dark": "#59243f",
                "surface-light": "#ffffff",
                "surface-dark": "#2a161f",
            },
            fontFamily: {
                display: ["Public Sans", "sans-serif"],
            },
            borderRadius: {
                DEFAULT: "0.5rem",
                lg: "0.75rem",
                xl: "1rem",
                full: "9999px",
            },
        },
    },

    darkMode: "class",

    plugins: [forms],
};
