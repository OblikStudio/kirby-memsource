var Vue = require('vue');
var Vuex = require('vuex');
var session = null;

Vue.use(Vuex);

if (localStorage.memsourceSession) {
    try {
        session = JSON.parse(localStorage.memsourceSession);
    } catch (e) {
        console.warn(e);
    }
}

module.exports = new Vuex.Store({
    state: {
        kirby: window.Memsource,
        session: session,
        loading: false,
        project: null
    },
    getters: {
        token: function (state) {
            if (state.session) {
                var expireDate = new Date(state.session.expires);

                if (Date.now() < expireDate) {
                    return state.session.token;
                }
            }

            return null;
        },
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
            localStorage.memsourceSession = JSON.stringify(payload);
        },
        SET_LOADING: function (state, value) {
            state.loading = value;
        },
        SET_PROJECT: function (state, value) {
            state.project = value;
        }
    }
});