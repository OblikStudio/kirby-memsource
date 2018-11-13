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
        loading: false
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
        }
    },
    mutations: {
        SET_SESSION: function (state, payload) {
            state.session = payload;
            localStorage.memsourceSession = JSON.stringify(payload);
        },
        SET_LOADING: function (state, value) {
            state.loading = value;
        }
    }
});