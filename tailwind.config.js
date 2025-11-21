import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
// import typography from '@tailwindcss/typography' // opsional

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/livewire/**/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.{js,ts,vue}", // kalau ada
    ],

    theme: {
        container: {
            center: true,
            padding: {
                DEFAULT: "24px", // samakan dengan padding frame Figma
                md: "24px",
            },
            // max-width container per breakpoint (samakan dengan frame Figma)
            screens: {
                xs: "375px", // mobile Figma
                sm: "640px",
                md: "1024px",
                lg: "1280px",
                xl: "1440px", // desktop Figma 1440
                "2xl": "1536px",
            },
        },

        extend: {
            // Pakai font yang sama dengan Figma (ganti sesuai desain)
            fontFamily: {
                sans: ["Inter", "Figtree", ...defaultTheme.fontFamily.sans],
            },

            // Token warna (contoh – ganti sesuai Figma)
            colors: {
                brand: {
                    red: "#E11D48",
                },
                gray: {
                    25: "#FCFCFD",
                    50: "#F9FAFB",
                    100: "#F2F4F7",
                    200: "#EAECF0",
                    300: "#D0D5DD",
                    500: "#667085",
                    700: "#344054",
                    900: "#101828",
                },
            },

            // Spacing/size khusus yang sering muncul di Figma
            spacing: {
                18: "4.5rem", // kalau suka skala 72px, dst.
            },

            // Radius sesuai Figma
            borderRadius: {
                14: "14px",
                16: "16px",
                20: "20px",
                "2xl": "16px", // override jika perlu
            },

            // Shadow kartu sesuai Figma
            boxShadow: {
                card: "0 1px 2px rgba(16,24,40,.05), 0 8px 24px rgba(16,24,40,.06)",
            },

            // Line-height & tracking custom (sering dibutuhkan)
            lineHeight: {
                22: "22px",
                36: "36px",
            },
            letterSpacing: {
                "tight-02": "0.2px",
            },
        },
    },

    plugins: [
        forms,
        // typography,
    ],
};
