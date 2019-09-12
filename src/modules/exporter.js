import axios from 'axios'

export default {
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
    outsource: ({ commit, getters }, payload) => {
      return getters.exporterClient(payload).catch(error => {
        commit('ALERT', {
          theme: 'negative',
          data: error
        })

        return Promise.reject(error)
      })
    }
  }
}
