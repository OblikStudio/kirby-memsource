import axios from 'axios'
import freeze from 'deep-freeze-node'
import session from './modules/session'

class MemsourceError extends Error {
	constructor(response) {
		super(response.data.errorDescription)
		this.name = 'MemsourceError'
		this.type = response.data.errorCode
		this.response = response
	}
}

function formatRejection(Error) {
	return function(error) {
		if (error.response && error.response.data) {
			error = new Error(error.response)
		}

		return Promise.reject(error)
	}
}

export default (Vuex, rootStore) =>
	new Vuex.Store({
		state: {
			alerts: [],
			crumbs: [],
			tab: null,
			session: session.load(),
			export: null,
			project: null,
			results: null,
			loading: false
		},
		getters: {
			view: state => {
				let last = state.crumbs[state.crumbs.length - 1]
				return last ? last.value : null
			},
			user: state => {
				return state.session && state.session.user
			},
			languages: function() {
				return rootStore.state.languages
			},
			availableLanguages: (state, getters) => {
				return getters.languages.all
			},
			siteLanguage: function(state, getters) {
				return getters.languages.default.code
			},
			sourceLanguage: function(state, getters) {
				return getters.languages.current.code
			},
			msClient: function() {
				return axios.create({
					baseURL: panel.api + '/memsource',
					method: 'get',
					headers: {
						'X-CSRF': panel.csrf
					}
				})
			}
		},
		mutations: {
			TAB(state, value) {
				state.crumbs = []
				state.tab = value
			},
			LOADING(state, value) {
				state.loading = !!value
			},
			VIEW(state, value) {
				if (typeof value === 'string') {
					value = {
						text: value.toLowerCase(), // for translation key
						value
					}
				}

				if (value !== null) {
					state.crumbs.push(value)
				} else {
					state.crumbs = []
				}
			},
			CRUMBS(state, value) {
				state.crumbs = value
			},
			SET_SESSION: function(state, data) {
				state.session = freeze(data)
				session.save(data)
			},
			SET_PROJECT: function(state, value) {
				state.project = freeze(value)
			},
			SET_EXPORT: (state, value) => {
				state.export = value
			},
			SET_RESULTS: function(state, value) {
				state.results = freeze(value)
			},
			ALERT(state, alert) {
				if (!alert.theme) {
					alert.theme = 'info'
				}

				if (!alert.text && alert.error) {
					alert.text = `${alert.error.name}: ${alert.error.message}`
				}

				state.alerts.push(alert)
			},
			CLEAR_ALERTS(state) {
				state.alerts = []
			}
		},
		actions: {
			memsource: ({ getters }, payload) => {
				return getters.msClient(payload).catch(formatRejection(MemsourceError))
			}
		}
	})
