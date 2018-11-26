var Vue = require('vue');
var Vuex = require('vuex');
var axios = require('axios');
var freeze = require('deep-freeze-node');

var session = require('./modules/session');
var pluginApi = require('./modules/plugin-api');
var memsource = require('./modules/memsource');

Vue.use(Vuex);

module.exports = new Vuex.Store({
    modules: {
        pluginApi: pluginApi,
        memsource: memsource
    },
    state: {
        kirby: freeze(window.Memsource),
        session: session.load(),
        loaders: 0,
        loadingStatus: null,
        project: null,
        job: null
    },
    getters: {
        isLoading: function (state) {
            return state.loaders > 0;
        },
        availableLanguages: function (state) {
            return state.kirby.languages || [];
        },
        siteLanguage: function (state, getters) {
            return getters.availableLanguages.find(function (lang) {
                return lang.isDefault;
            });
        },
        sourceLanguage: function (state, getters) {
            return getters.availableLanguages.find(function (lang) {
                return lang.isActive;
            });
        },
        sourceLanguageMatching: function (state, getters) {
            var projectLang = (state.project && state.project.sourceLang);

            return getters.sourceLanguage.locale === projectLang;
        },
        targetLanguages: function (state, getters) {
            return getters.availableLanguages.filter(function (lang) {
                return lang !== getters.sourceLanguage;
            });
        },
        targetLanguagesMatching: function (state, getters) {
            var projectLangs = (state.project && state.project.targetLangs) || [];

            return getters.targetLanguages.filter(function (lang) {
                return projectLangs.indexOf(lang.locale) >= 0;
            });
        }
    },
    mutations: {
        SET_LOADING: function (state, value) {
            state.loading = value;
        },
        SET_SESSION: function (state, data) {
            state.session = freeze(data);
            session.save(data);
        },
        SET_PROJECT: function (state, value) {
            state.project = freeze(value);
        },
        SET_JOB: function (state, value) {
            state.job = freeze(value);
        },
        MODIFY_LOADERS: function (state, value) {
            state.loaders += (value === 'add') ? 1 : -1;

            if (state.loaders === 0) {
                state.loadingStatus = null;
            }
        },
        SET_LOADING_STATUS: function (state, value) {
            state.loadingStatus = value;
        }
    },
    actions: {
        logOut: function (context) {
            context.commit('SET_SESSION', null);
            context.commit('SET_PROJECT', []);
            context.commit('SET_JOB', []);
            context.commit('MS_SET_PROJECTS', []);
            context.commit('MS_SET_JOBS', []);
        }
    }
});