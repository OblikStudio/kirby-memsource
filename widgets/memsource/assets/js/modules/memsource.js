var axios = require('axios');
var get = require('lodash.get');

module.exports = {
    state: {
        projects: [],
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
        api: function (state, getters) {
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
            state.projects = data;
        }
    },
    actions: {
        logIn: function (context, data) {
            context.commit('SET_LOADING', true);

            return context.getters.api({
                url: '/auth/login',
                method: 'post',
                data: {
                    userName: data.username,
                    password: data.password
                }
            }).then(function (response) {
                context.commit('SET_SESSION', response.data);
                context.commit('SET_LOADING', false);
                return Promise.resolve();
            }).catch(function (error) {
                context.commit('SET_LOADING', false);
                return Promise.reject(error);
            });
        },
        loadProjects: function (context) {
            context.commit('SET_LOADING', true);

            return context.getters.api({
                url: '/projects'
            }).then(function (response) {
                var projects = get(response, 'data.content');

                if (projects) {
                    context.commit('MS_SET_PROJECTS', projects);
                }

                context.commit('SET_LOADING', false);
                return Promise.resolve();
            }).catch(function (error) {
                context.commit('SET_LOADING', false);
                return Promise.reject(error);
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

            context.commit('SET_LOADING', true);

            return context.getters.api({
                url: '/projects/' + payload.projectId + '/jobs',
                method: 'post',
                headers: {
                    'Memsource': JSON.stringify(memsourceHeader),
                    'Content-Type': 'application/octet-stream',
                    'Content-Disposition': 'filename*=UTF-8\'\'' + filename
                },
                data: payload.data
            }).then(function (response) {
                context.commit('SET_LOADING', false);
                return Promise.resolve(response);
            }).catch(function (error) {
                context.commit('SET_LOADING', false);
                return Promise.reject(error);
            });
        }
    }
};