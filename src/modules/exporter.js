import axios from 'axios'
import freeze from 'deep-freeze-node'

export default {
  state: {
    exportData: {}
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
      state.exportData = freeze(value)
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
