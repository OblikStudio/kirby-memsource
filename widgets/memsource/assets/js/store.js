var Vue = require('vue');
var Vuex = require('vuex');
var axios = require('axios');
var freeze = require('deep-freeze-node');

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
        session: null,
        loading: false,
        project: null,
        job: null
    },
    getters: {
        availableLanguages: function (state) {
            var values = [],
                projectLangs = state.project.targetLangs;

            state.kirby.languages.forEach(function (lang) {
                if (!lang.isActive && projectLangs.indexOf(lang.locale) >= 0) {
                    values.push(lang);
                }
            });

            return values;
        },
        siteLanguage: function (state) {
            return state.kirby.languages.reduce(function (search, item) {
                return search || (item.isDefault ? item : null);
            }, null);
        },
        activeLanguage: function (state) {
            return state.kirby.languages.reduce(function (search, item) {
                return search || (item.isActive ? item : null);
            }, null);
        }
    },
    mutations: {
        SET_SESSION: function (state, data) {
            state.session = freeze(data);

            try {
                localStorage.memsourceSession = JSON.stringify(data);
            } catch (e) {
                console.warn(e);
            }
        },
        SET_LOADING: function (state, value) {
            state.loading = value;
        },
        SET_PROJECT: function (state, value) {
            state.project = freeze(value);
        },
        SET_JOB: function (state, value) {
            state.job = freeze(value);
        }
    }
});