import axios from 'axios'
import freeze from 'deep-freeze-node'
import session from './modules/session'

function getMessage (input) {
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

  return null
}

export default (Vuex, rootStore) => new Vuex.Store({
  state: {
    alerts: [],
    crumbs: [],
    tab: null,
    session: session.load(),
    export: null,
    project: null,
    job: null
  },
  getters: {
    view: (state) => {
      var last = state.crumbs[state.crumbs.length - 1]
      return last ? last.value : null
    },
    user: (state) => {
      return (state.session && state.session.user)
    },
    languages: function () {
      return rootStore.state.languages
    },
    availableLanguages: (state, getters) => {
      return getters.languages.all
    },
    siteLanguage: function (state, getters) {
      return getters.languages.default.code
    },
    sourceLanguage: function (state, getters) {
      return getters.languages.current.code
    },
    msClient: function (state, getters, rootState) {
      var token = (rootState.session && rootState.session.token)

      return axios.create({
        baseURL: 'https://cloud.memsource.com/web/api2/v1',
        method: 'get',
        params: {
          token: token
        }
      })
    },
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
    TAB (state, value) {
      state.crumbs = []
      state.tab = value
    },
    VIEW (state, value) {
      if (typeof value === 'string') {
        value = {
          text: value,
          value
        }
      }

      if (value !== null) {
        state.crumbs.push(value)
      } else {
        state.crumbs = []
      }
    },
    CRUMBS (state, value) {
      state.crumbs = value
    },
    SET_SESSION: function (state, data) {
      state.session = freeze(data)
      session.save(data)
    },
    SET_PROJECT: function (state, value) {
      state.project = freeze(value)
    },
    SET_EXPORT: (state, value) => {
      state.export = value
    },
    SET_JOB: function (state, value) {
      state.job = freeze(value)
    },
    ALERT (state, alert) {
      if (!alert.theme) {
        alert.theme = 'info'
      }

      if (!alert.text && alert.data) {
        alert.text = getMessage(alert.data)
      }

      state.alerts.push(alert)
    },
    CLEAR_ALERTS (state) {
      state.alerts = []
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
})
