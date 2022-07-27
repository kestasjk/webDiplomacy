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
    },
    fontFamily: {
      roboto: ["Roboto", "sans-serif"],
    },
  },
  plugins: [],
};
