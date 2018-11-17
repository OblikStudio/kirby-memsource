var axios = require('axios');
var freeze = require('deep-freeze-node');

module.exports = {
    state: {
        exportData: null
    },
    getters: {
        pluginClient: function (state, getters, rootState) {
            return axios.create({
                baseURL: rootState.kirby.endpoint,
                method: 'get'
            });
        }
    },
    mutations: {
        SET_EXPORT_DATA: function (state, value) {
            state.exportData = freeze(value);
        }
    },
    actions: {
        exportContent: function (context) {
            return context.getters.pluginClient({
                url: '/export'
            }).then(function (response) {
                context.commit('SET_EXPORT_DATA', response.data);
                return Promise.resolve(response);
            });
        },
        importJob: function (context, payload) {            
            return context.dispatch('downloadJob', {
                projectId: payload.projectId,
                jobId: payload.jobId
            }).then(function (response) {
                return context.getters.pluginClient({
                    url: '/import',
                    method: 'put',
                    data: JSON.stringify({
                        data: response.data,
                        language: payload.language
                    })
                });
            });
        }
    }
};