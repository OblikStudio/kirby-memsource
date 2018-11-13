var Vue = require('vue');
var Vuex = require('vuex');

Vue.use(Vuex);

module.exports = new Vuex.Store({
    state: {
        screen: 'Login',
        session: null,
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
        SET_SCREEN: function (state, value) {
            state.screen = value;
        },
        SET_SESSION: function (state, payload) {
            state.session = payload;
            localStorage.memsourceSession = JSON.stringify(payload);
        }
    }
});