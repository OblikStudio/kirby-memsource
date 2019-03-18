export default {
  methods: {
    getError (input) {
      if (typeof input === 'string') {
        return input
      }

      var response = (input.response && input.response.data)
      if (response) {
        return `${ response.errorCode }: ${ response.errorDescription }`
      }

      if (typeof input.toString === 'function') {
        return input.toString()
      }

      return 'An error occurred.'
    }
  }
}
