var axios = require('axios');
var freeze = require('deep-freeze-node');

function apiRequest (context, options) {
    context.commit('SET_LOADING', true);

    return context.getters.pluginApiClient(options).then(function (value) {
        context.commit('SET_LOADING', false);
        return Promise.resolve(value);
    }).catch(function (reason) {
        context.commit('SET_LOADING', false);
        return Promise.reject(reason);
    });
}

module.exports = {
    state: {
        exportData: null
    },
    getters: {
        pluginApiClient: function (state, getters, rootState) {
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
            return apiRequest(context, {
                url: '/export'
            }).then(function (response) {
                context.commit('SET_EXPORT_DATA', response.data);
                return Promise.resolve(response);
            });
        },
        importJob: function (context, payload) {
            console.log('payload', payload);
            
            return context.dispatch('downloadJob', {
                projectId: payload.projectId,
                jobId: payload.jobId
            }).then(function (response) {
                return apiRequest(context, {
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