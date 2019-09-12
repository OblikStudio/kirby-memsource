var axios = require('axios')
var freeze = require('deep-freeze-node')

var IMPORT_SETTINGS = {
  name: 'k3-1',
  fileImportSettings: {
    json: {
      htmlSubFilter: true
    }
  }
}

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
    },
    listImportSettings: function (context) {
      return context.getters.msClient({
        url: '/importSettings'
      }).then(function (response) {
        var items = (response.data && response.data.content),
          settings = null

        if (Array.isArray(items)) {
          items.forEach(function (item) {
            if (item.name === IMPORT_SETTINGS.name) {
              settings = item
            }
          })
        }

        return Promise.resolve(settings)
      })
    },
    getImportSettings: function (context, uid) {
      return context.getters.msClient({
        url: '/importSettings/' + uid
      }).then(function (response) {
        return Promise.resolve(response.data)
      })
    },
    createImportSettings: function (context) {
      return context.getters.msClient({
        url: '/importSettings',
        method: 'post',
        data: IMPORT_SETTINGS
      }).then(function (response) {
        return Promise.resolve(response.data)
      })
    },
    fetchImportSettings: function (context) {
      return context.dispatch('listImportSettings').then(settings => {
        if (settings) {
          return context.dispatch('getImportSettings', settings.uid)
        } else {
          return context.dispatch('createImportSettings')
        }
      })
    },
    createJob: function (context, payload) {
      var filename = payload.name + '.json'
      var memsourceHeader = {
        targetLangs: payload.languages,
        importSettings: {
          uid: payload.importSettingsId
        }
      }

      return context.getters.msClient({
        url: '/projects/' + payload.projectId + '/jobs',
        method: 'post',
        headers: {
          'Memsource': JSON.stringify(memsourceHeader),
          'Content-Type': 'application/octet-stream',
          'Content-Disposition': 'filename*=UTF-8\'\'' + filename
        },
        data: payload.data
      })
    },
    downloadJob: function (context, payload) {
      return context.getters.msClient({
        url: `/projects/${ payload.projectId }/jobs/${ payload.jobId }/targetFile`
      })
    },
    deleteJobs: (context, payload) => {
      return context.getters.msClient({
        url: `/projects/${ payload.projectId }/jobs/batch`,
        method: 'delete',
        data: {
          jobs: payload.jobIds.map(id => {
            return {
              uid: id
            }
          })
        }
      })
    }
  }
}
