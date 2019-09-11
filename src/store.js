import freeze from 'deep-freeze-node'
import session from './modules/session'
import exporter from './modules/exporter'
import memsource from './modules/memsource'

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
  modules: {
    exporter,
    memsource
  },
  state: {
    alerts: [],
    crumbs: [],
    tab: null,
    session: session.load(),
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
  }
})
