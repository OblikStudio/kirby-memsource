export default {
  methods: {
    getError (input) {
      if (typeof input === 'string') {
        return input
      }

      var response = (input.response && input.response.data)
      if (response) {
        return `${ response.errorCode || response.exception }: ${ response.errorDescription || response.message }`
      }

      if (typeof input.toString === 'function') {
        return input.toString()
      }

      return 'An error occurred.'
    },
    isValidLanguage (input, target) {
      input = input.toLowerCase()
      target = target.toLowerCase()

      // allow imports of en in en_us and vice-versa
      return (target.indexOf(input) === 0 || input.indexOf(target) === 0)
    }
  }
}
