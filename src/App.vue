<template>
	<k-view :class="{ 'ms-loading': $store.state.memsource.loading }">
		<div class="k-header">
			<div class="k-tabs">
				<nav>
					<k-button
						v-for="tab in tabs"
						:key="tab.component"
						:current="currentTab === tab.component"
						:icon="tab.icon"
						class="k-tab-button"
						@click="currentTab = tab.component"
					>
						{{ tab.text }}
					</k-button>
				</nav>
			</div>
			<Crumbs v-if="crumbs.length" v-model="crumbs"></Crumbs>
		</div>

		<component
			:is="currentTab"
			v-show="!$store.state.memsource.loading"
		></component>

		<k-dialog ref="alerts" size="medium">
			<k-text>
				<k-box
					v-for="(alert, index) in alerts"
					:key="index"
					:text="alert.text"
					:theme="alert.theme"
				/>
			</k-text>

			<k-button-group slot="footer">
				<div></div>
				<k-button icon="check" @click="closeAlerts">
					{{ $t('close') }}
				</k-button>
			</k-button-group>
		</k-dialog>
	</k-view>
</template>

<script>
import whenExpired from 'when-expired'
import { store } from './store'
import Crumbs from './components/Crumbs.vue'
import User from './components/tabs/user/User.vue'
import Service from './components/tabs/service/Service.vue'
import Snapshots from './components/tabs/snapshots/Snapshots.vue'

export default {
	components: {
		Crumbs,
		User,
		Service,
		Snapshots
	},
	provide() {
		return {
			$alert: this.$alert,
			$loading: this.$loading
		}
	},
	data() {
		return {
			tabs: [
				{
					icon: 'user',
					text: this.$t('user'),
					component: 'User'
				},
				{
					icon: 'globe',
					text: this.$t('service'),
					component: 'Service'
				},
				{
					icon: 'clock',
					text: this.$t('snapshots'),
					component: 'Snapshots'
				}
			]
		}
	},
	computed: {
		currentTab: {
			get() {
				return this.$store.state.memsource.tab
			},
			set(value) {
				return this.$store.commit('memsource/TAB', value)
			}
		},
		crumbs: {
			get() {
				return this.$store.state.memsource.crumbs
			},
			set(value) {
				return this.$store.commit('memsource/CRUMBS', value)
			}
		},
		alerts() {
			return this.$store.state.memsource.alerts
		}
	},
	methods: {
		$alert(data, theme) {
			let conf = {
				theme: 'info',
				text: null,
				error: null
			}

			if (typeof data === 'string') {
				conf.text = data
			} else if (data instanceof Error) {
				conf.theme = 'negative'
				conf.error = data
			}

			if (typeof theme === 'string') {
				conf.theme = theme
			}

			this.$store.commit('memsource/ALERT', conf)
		},
		$loading(promise) {
			this.$store.commit('memsource/LOADING', true)
			return promise.then(() => {
				this.$store.commit('memsource/LOADING', false)
			})
		},
		closeAlerts() {
			this.$store.commit('memsource/CLEAR_ALERTS')
			this.$refs.alerts.close()
		}
	},
	beforeCreate() {
		this.$store.registerModule('memsource', store)
	},
	created() {
		if (this.$store.state.memsource.session) {
			this.currentTab = 'Service'
		} else {
			this.currentTab = 'User'
		}
	},
	watch: {
		'$store.state.memsource.session.expires': {
			immediate: true,
			handler(value) {
				if (value) {
					whenExpired('session', value).then(() => {
						this.$alert(this.$t('memsource.info.session_expired'))
						this.$store.dispatch('memsource/logOut')
					})
				}
			}
		},
		alerts(value) {
			if (value.length) {
				this.$refs.alerts.open()
			}
		}
	}
}
</script>

<style lang="postcss" scoped>
.k-view {
	max-width: 50rem;

	&.ms-loading {
		.k-header {
			pointer-events: none;
			opacity: 0.6;
		}
	}
}

.k-header {
	transition: opacity 0.1s ease-out;
}

.k-tab-button {
	width: 100%;
}

.k-box + .k-box {
	margin-top: 5px;
}

/deep/ {
	.ms-crumbs {
		border-bottom: none;
	}

	.k-form {
		.k-button-group {
			margin-top: 1rem;
		}
	}
}
</style>
