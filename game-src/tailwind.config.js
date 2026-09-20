/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./src/**/*.{js,jsx,ts,tsx}"],
  theme: {
    extend: {
      backgroundImage: {
        loading: "url('../../../src/assets/png/spacebg.jpg')",
      },
      fontSize: {
        xsss: "0.55rem",
        xss: "0.68rem",
      },
      colors: {
        "france-main": "#2D5EE8",
        "france-light": "#B9C9F7",
        "austria-main": "#FC4343",
        "austria-light": "#FEC0C0",
        "england-main": "#F146FA",
        "england-light": "#F9C0FC",
        "germany-main": "#916620",
        "germany-light": "#E3C5A7",
        "russia-main": "#4B1BA2",
        "russia-light": "#C5B3E5",
        "italy-main": "#47D290",
        "italy-light": "#C2F0CF",
        "turkey-main": "#F6C903",
        "turkey-light": "#FDEFAC",
      },
    },
    fontFamily: {
      roboto: ["Roboto", "sans-serif"],
    },
  },
  plugins: [require("@tailwindcss/forms")],
};
