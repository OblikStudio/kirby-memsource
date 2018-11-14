var axios = require('axios');
var get = require('lodash.get');

const ENDPOINT = 'https://cloud.memsource.com/web/api2/v1/';

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
    },
    mutations: {
        MS_SET_PROJECTS: function (state, data) {
            state.projects = data;
        }
    },
    actions: {
        logIn: function (context, data) {
            context.commit('SET_LOADING', true);

            return axios.post(ENDPOINT + 'auth/login', {
                userName: data.username,
                password: data.password
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
            if (context.state.projects.length) {
                return Promise.resolve();
            }

            context.commit('SET_LOADING', true);

            return axios.get(ENDPOINT + 'projects', {
                params: {
                    token: context.getters.token
                }
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
            // https://cloud.memsource.com/web/api2/v1/projects/{projectUid}/jobs
            console.log('create job', payload);
        }
    }
};