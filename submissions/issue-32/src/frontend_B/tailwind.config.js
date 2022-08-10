module.exports = {
    // mode: 'jit',
    // jit document: https://tailwindcss.com/docs/just-in-time-mode
    purge: ['./src/**/*.html', './src/**/*.tsx', './src/**/*.ts'],
    darkMode: false, // or 'media' or 'class'
    theme: {
      fontFamily: {
        'EBRIMA': ['EBRIMA']
      },
      extend: {
        spacing: {
          'main': '1280px',
        },
        colors: {
          'blue': '#0012BF',
        },
      },
    },
    variants: {
      extend: {},
    },
    plugins: [],
};
