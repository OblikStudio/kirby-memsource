var Vue = require('vue');
var Vuex = require('vuex');

Vue.use(Vuex);

module.exports = new Vuex.Store({
    state: {
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
        SET_SESSION: function (state, payload) {
            console.log('set session', payload);
            state.session = payload;
        }
    }
});