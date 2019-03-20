var Vue = require('vue')
var axios = require('axios')

module.exports = {
  state: {
    exportData: null,
  },
  getters: {
    exporterClient: () => {
      return axios.create({
        baseURL: panel.api,
        method: 'get',
        headers: {
          'X-CSRF': panel.csrf
        }
      })
    }
  },
  mutations: {
    SET_EXPORT_DATA: (state, value) => {
      state.exportData = value
    }
  },
  actions: {
    exportContent: (context, payload) => {
      return context.getters.exporterClient({
        url: '/export',
        params: payload
      })
    },
    importContent: (context, { language, content }) => {
      return context.getters.exporterClient({
        url: '/import',
        method: 'post',
        data: JSON.stringify({
          language,
          content
        })
      })
    }
  }
}
