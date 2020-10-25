module.exports = {
  future: {
    removeDeprecatedGapUtilities: true,
    purgeLayersByDefault: true,
  },
  purge: [
    './pages/**/*.tsx',
    './components/**/*.tsx'
  ],
  theme: {
    extend: {
      colors: {
        yt_orange: '#E6A340',
        yt_red: '#D16764',
      }
    },
    fontFamily: {
      'sans': ['Roboto', 'Helvetica', 'Arial', 'sans-serif'],
    },
  },
  variants: {},
  plugins: [],
}
