var Vue = require('vue');
var Vuex = require('vuex');
var axios = require('axios');
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
        project: null,
        exportData: null
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
        },
        SET_EXPORT_DATA: function (state, value) {
            state.exportData = value;
        }
    },
    actions: {
        exportContent: function (context) {
            context.commit('SET_LOADING', true);

            return axios.get(context.state.kirby.endpoint + '/export').then(function (response) {
                var data = Object.freeze(response.data); // large object, good idea to freeze

                context.commit('SET_EXPORT_DATA', data);
                context.commit('SET_LOADING', false);
                return Promise.resolve();
            }).catch(function (error) {
                context.commit('SET_LOADING', false);
                return Promise.reject(error);
            });
        }
    }
});