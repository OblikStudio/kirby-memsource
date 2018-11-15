var Vue = require('vue');
var Vuex = require('vuex');
var axios = require('axios');
var freeze = require('deep-freeze-node');
var memsource = require('./modules/memsource');

Vue.use(Vuex);

module.exports = new Vuex.Store({
    modules: {
        memsource: memsource
    },
    state: {
        kirby: window.Memsource,
        session: null,
        loading: false,
        project: null,
        exportData: null,
        job: null
    },
    getters: {
        availableLanguages: function (state) {
            var values = [],
                projectLangs = state.project.targetLangs;

            state.kirby.languages.forEach(function (lang) {
                if (!lang.isDefault && projectLangs.indexOf(lang.locale) >= 0) {
                    values.push(lang);
                }
            });

            return values;
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
        SET_SESSION: function (state, data) {
            state.session = freeze(data);

            try {
                localStorage.memsourceSession = JSON.stringify(data);
            } catch (e) {
                console.warn(e);
            }
        },
        SET_LOADING: function (state, value) {
            state.loading = value;
        },
        SET_PROJECT: function (state, value) {
            state.project = freeze(value);
        },
        SET_EXPORT_DATA: function (state, value) {
            state.exportData = freeze(value);
        },
        SET_JOB: function (state, value) {
            state.job = freeze(value);
        }
    },
    actions: {
        exportContent: function (context) {
            context.commit('SET_LOADING', true);

            return axios({
                url: context.state.kirby.endpoint + '/export',
                method: 'get'
            }).then(function (response) {
                context.commit('SET_EXPORT_DATA', response.data);
                context.commit('SET_LOADING', false);
                return Promise.resolve(response);
            }).catch(function (error) {
                context.commit('SET_LOADING', false);
                return Promise.reject(error);
            });
        },
        importJob: function (context, payload) {
            return context.dispatch('downloadJob', {
                projectId: payload.projectId,
                jobId: payload.jobId
            }).then(function (jobContent) {
                var postData = {
                    data: jobContent,
                    language: payload.language
                };

                context.commit('SET_LOADING', true);

                return axios({
                    url: context.state.kirby.endpoint + '/import',
                    method: 'put',
                    data: JSON.stringify(postData)
                });
            })
            .then(function (response) {
                context.commit('SET_LOADING', false);
                return Promise.resolve(response);
            }).catch(function (error) {
                context.commit('SET_LOADING', false);
                return Promise.reject(error);
            });
        }
    }
});