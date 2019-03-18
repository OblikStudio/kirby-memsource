var axios = require('axios')
var freeze = require('deep-freeze-node')
var session = require('./modules/session')
var exporter = require('./modules/exporter')
var memsource = require('./modules/memsource')

module.exports = Vuex => new Vuex.Store({
  modules: {
    exporter,
    memsource
  },
  state: {
    languages: [],
    session: session.load(),
    loaders: 0,
    loadingStatus: null,
    project: null,
    job: null
  },
  getters: {
    isLoading: function (state) {
      return state.loaders > 0
    },
    siteLanguage: function (state, getters) {
      return state.languages.find(lang => lang.isDefault)
    },
    sourceLanguage: function (state, getters) {
      return state.languages.find(lang => lang.isActive)
    }
  },
  mutations: {
    SET_LANGUAGES: (state, value) => {
      state.languages = freeze(value)
    },
    SET_LOADING: function (state, value) {
      state.loading = value
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
    MODIFY_LOADERS: function (state, value) {
      state.loaders += (value === 'add') ? 1 : -1

      if (state.loaders === 0) {
        state.loadingStatus = null
      }
    },
    SET_LOADING_STATUS: function (state, value) {
      state.loadingStatus = value
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