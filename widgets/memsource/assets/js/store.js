var Vue = require('vue');
var Vuex = require('vuex');
var memsource = require('./modules/memsource');

Vue.use(Vuex);

module.exports = new Vuex.Store({
    modules: {
        memsource: memsource
    },
    state: {
        kirby: window.Memsource,
        session: null,
        loading: false,
        project: null
    },
    getters: {
        siteLanguage: function (state) {
            var lang = null;

            state.kirby.languages.forEach(function (item) {
                if (item.isDefault) {
                    lang = item;
                }
            });

            return lang;
        }
    },
    mutations: {
        SET_SESSION: function (state, payload) {
            state.session = payload;

            try {
                localStorage.memsourceSession = JSON.stringify(payload);
            } catch (e) {
                console.warn(e);
            }
        },
        SET_LOADING: function (state, value) {
            state.loading = value;
        },
        SET_PROJECT: function (state, value) {
            state.project = value;
        }
    }
});