var axios = require('axios')

module.exports = {
  getters: {
    msClient: function (state, getters, rootState) {
      var token = (rootState.session && rootState.session.token)

      return axios.create({
        baseURL: 'https://cloud.memsource.com/web/api2/v1',
        method: 'get',
        params: {
          token: token
        }
      })
    }
  },
  actions: {
    memsource: ({ commit, getters }, payload) => {
      return getters.msClient(payload).catch(error => {
        commit('ALERT', {
          theme: 'negative',
          data: error
        })

        return Promise.reject(error)
      })
    },
    logIn: function (context, data) {
      return context.getters.msClient({
        url: '/auth/login',
        method: 'post',
        data: {
          userName: data.username,
          password: data.password
        }
      }).then(function (response) {
        context.commit('SET_SESSION', response.data)
        return Promise.resolve()
      })
    },
    logOut (context) {
      context.commit('SET_SESSION', null)
      context.commit('SET_PROJECT', [])
    }
  }
}
