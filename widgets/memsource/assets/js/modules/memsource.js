var axios = require('axios');
var freeze = require('deep-freeze-node');

module.exports = {
    state: {
        projects: [],
        jobs: []
    },
    getters: {
        msClient: function (state, getters, rootState) {
            var token = (rootState.session && rootState.session.token);

            return axios.create({
                baseURL: 'https://cloud.memsource.com/web/api2/v1',
                method: 'get',
                params: {
                    token: token
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
            return context.getters.msClient({
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
            return context.getters.msClient({
                url: '/projects'
            }).then(function (response) {
                var projects = response.data.content;

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

            return context.getters.msClient({
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
            return context.getters.msClient({
                url: '/projects/' + payload.projectId + '/jobs'
            }).then(function (response) {
                var jobs = response.data.content;

                if (jobs) {
                    context.commit('MS_SET_JOBS', jobs);
                }

                return Promise.resolve(response);
            });
        },
        downloadJob: function (context, payload) {
            return context.getters.msClient({
                url: '/projects/' + payload.projectId + '/jobs/' + payload.jobId + '/targetFile'
            });
        }
    }
};