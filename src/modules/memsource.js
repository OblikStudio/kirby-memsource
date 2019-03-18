var axios = require('axios')
var freeze = require('deep-freeze-node')

var IMPORT_SETTINGS = {
  name: 'kirby_1',
  fileImportSettings: {
    json: {
      htmlSubFilter: false,
      tagRegexp: '<[^>]+>|%[a-zA-Z]'
    }
  }
}

module.exports = {
  state: {
    projects: [],
    jobs: []
  },
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
  mutations: {
    MS_SET_PROJECTS: function (state, data) {
      state.projects = freeze(data)
    },
    MS_SET_JOBS: function (state, data) {
      state.jobs = freeze(data)
    }
  },
  actions: {
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
    loadProjects: function (context) {
      return context.getters.msClient({
        url: '/projects'
      }).then(function (response) {
        var projects = response.data.content

        if (projects) {
          context.commit('MS_SET_PROJECTS', projects)
        }

        console.log('prjs', projects)
        return Promise.resolve(response)
      })
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
    listJobs: function (context, payload) {
      return context.getters.msClient({
        url: '/projects/' + payload.projectId + '/jobs'
      }).then(function (response) {
        var jobs = response.data.content

        if (jobs) {
          context.commit('MS_SET_JOBS', jobs)
        }

        return Promise.resolve(response)
      })
    },
    downloadJob: function (context, payload) {
      return context.getters.msClient({
        url: '/projects/' + payload.projectId + '/jobs/' + payload.jobId + '/targetFile'
      })
    }
  }
}
