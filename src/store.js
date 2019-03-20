var axios = require('axios')
var freeze = require('deep-freeze-node')
var session = require('./modules/session')
var exporter = require('./modules/exporter')
var memsource = require('./modules/memsource')

module.exports = (Vuex, rootStore) => new Vuex.Store({
  modules: {
    exporter,
    memsource
  },
  state: {
    session: session.load(),
    project: null,
    job: null
  },
  getters: {
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
    SET_SESSION: function (state, data) {
      state.session = freeze(data)
      session.save(data)
    },
    SET_PROJECT: function (state, value) {
      state.project = freeze(value)
    },
    SET_JOB: function (state, value) {
      state.job = freeze(value)
    }
  },
  actions: {
    logOut: function (context) {
      context.commit('SET_SESSION', null)
      context.commit('SET_PROJECT', [])
      context.commit('SET_JOB', [])
      context.commit('MS_SET_PROJECTS', [])
      context.commit('MS_SET_JOBS', [])
    }
  }
})