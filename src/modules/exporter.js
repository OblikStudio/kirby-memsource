var axios = require('axios')
var freeze = require('deep-freeze-node')
var Vue = require('vue')
var cloneDeep = require('lodash/cloneDeep')

module.exports = {
  state: {
    exportData: null,
  },
  getters: {
    exporterClient: function (state, getters, rootState) {
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
    SET_EXPORT_DATA: function (state, value) {
      state.exportData = cloneDeep(value)
    }
  },
  actions: {
    exportContent: function (context, payload) {
      return context.getters.exporterClient({
        url: '/export',
        params: payload
      }).then(function (response) {
        context.commit('SET_EXPORT_DATA', response.data)
        return Promise.resolve(response.data)
      })
    },
    importJob: function (context, payload) {      
      return context.dispatch('downloadJob', {
        projectId: payload.projectId,
        jobId: payload.jobId
      }).then(function (response) {
        return context.getters.exporterClient({
          url: '/import',
          method: 'put',
          data: JSON.stringify({
            data: response.data,
            language: payload.language
          })
        })
      })
    }
  }
}
