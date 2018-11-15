var axios = require('axios');
var get = require('lodash.get');
var freeze = require('deep-freeze-node');

function apiRequest (context, options) {
    context.commit('SET_LOADING', true);

    return context.getters.memsourceApiClient(options).then(function (value) {
        context.commit('SET_LOADING', false);
        return Promise.resolve(value);
    }).catch(function (reason) {
        context.commit('SET_LOADING', false);
        return Promise.reject(reason);
    });
}

module.exports = {
    state: {
        projects: [],
        jobs: []
    },
    getters: {
        token: function (state, getters, rootState) {
            var session = rootState.session;

            if (session) {
                var expireDate = new Date(session.expires);

                if (Date.now() < expireDate) {
                    return session.token;
                }
            }
        },
        memsourceApiClient: function (state, getters) {
            return axios.create({
                baseURL: 'https://cloud.memsource.com/web/api2/v1',
                method: 'get',
                params: {
                    token: getters.token
                }
            });
        }
    },
    mutations: {
        MS_SET_PROJECTS: function (state, data) {
            state.projects = freeze(data);
        },
        MS_SET_JOBS: function (state, data) {
            state.jobs = freeze(data);
        }
    },
    actions: {
        logIn: function (context, data) {
            return apiRequest(context, {
                url: '/auth/login',
                method: 'post',
                data: {
                    userName: data.username,
                    password: data.password
                }
            }).then(function (response) {
                context.commit('SET_SESSION', response.data);
                return Promise.resolve();
            });
        },
        loadProjects: function (context) {
            return apiRequest(context, {
                url: '/projects'
            }).then(function (response) {
                var projects = get(response, 'data.content');

                if (projects) {
                    context.commit('MS_SET_PROJECTS', projects);
                }

                return Promise.resolve(response);
            });
        },
        createJob: function (context, payload) {
            var filename = payload.filename + '.json';
            var targetLanguages = (Array.isArray(payload.language))
                ? payload.language
                : [payload.language];

            var memsourceHeader = {
                targetLangs: targetLanguages
            };

            return apiRequest(context, {
                url: '/projects/' + payload.projectId + '/jobs',
                method: 'post',
                headers: {
                    'Memsource': JSON.stringify(memsourceHeader),
                    'Content-Type': 'application/octet-stream',
                    'Content-Disposition': 'filename*=UTF-8\'\'' + filename
                },
                data: payload.data
            });
        },
        listJobs: function (context, payload) {
            return apiRequest(context, {
                url: '/projects/' + payload.projectId + '/jobs'
            }).then(function (response) {
                var jobs = get(response, 'data.content');

                if (jobs) {
                    context.commit('MS_SET_JOBS', jobs);
                }

                return Promise.resolve(response);
            });
        },
        downloadJob: function (context, payload) {
            return apiRequest(context, {
                url: '/projects/' + payload.projectId + '/jobs/' + payload.jobId + '/targetFile'
            });
        }
    }
};