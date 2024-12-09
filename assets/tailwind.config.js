/** @type {import('tailwindcss').Config} */
module.exports = {
    prefix: 'tb_',
    content: [
      "./src/js/popup/**/*.{js,jsx,ts,tsx}",
      "./src/js/popup/blocks/**/*.{js,jsx,ts,tsx}"
    ],
    theme: {
      extend: {
        colors: {
          primary: {
            DEFAULT: "#e63f51",    // default for 'bg-primary'
            50: "#fdedef",
            100: "#fbdadd",
            200: "#f7b5bb",
            300: "#f39099",
            400: "#ef6b77",
            500: "#e63f51",        // same as default
            600: "#bf3443",
            700: "#992a36",
            800: "#731f28",
            900: "#4c141b",
          },
          secondary: {
            DEFAULT: "#FFC52F",    // default for 'bg-secondary'
            50: "#fff9e6", 
            100: "#fff3cc",
            200: "#ffe799",
            300: "#ffdb66",
            400: "#ffcf33",
            500: "#FFC52F",        // same as default
            600: "#cca026",
            700: "#997a1d",
            800: "#665213",
            900: "#33290a",
          }
        }
      },
    },
    plugins: [],
}