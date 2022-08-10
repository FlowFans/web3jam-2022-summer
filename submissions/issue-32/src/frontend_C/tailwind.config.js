module.exports = {
    // mode: 'jit',
    // jit document: https://tailwindcss.com/docs/just-in-time-mode
    purge: ['./src/**/*.html', './src/**/*.tsx', './src/**/*.ts'],
    darkMode: false, // or 'media' or 'class'
    theme: {
      colors:{
        'card-true':'#001F9E',
        'card-false':'#D9D9D9',
        'white':"#ffffff"
      }
    },
    variants: {
      extend: {},
    },
    plugins: [],
};
