/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./views/**/*.{html,php}",
    "./includes/**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#e91e63', // Your pink color
      },
    },
  },
  plugins: [],
}

