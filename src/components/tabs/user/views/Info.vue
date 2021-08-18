<template>
	<div>
		<header class="k-section-header">
			<k-headline>{{ data.role }}</k-headline>
			<k-button-group>
				<k-button icon="logout" theme="negative" @click="logout">
					{{ this.$t('logout') }}
				</k-button>
			</k-button-group>
		</header>

		<Stats :data="info" />
	</div>
</template>

<script>
import Stats from '../../../Stats.vue'

export default {
	components: {
		Stats
	},
	props: {
		data: Object
	},
	computed: {
		info() {
			return [
				{
					title: this.$t('name'),
					content: `${this.data.firstName} ${this.data.lastName}`
				},
				{
					title: this.$t('email'),
					content: this.data.email
				}
			]
		}
	},
	methods: {
		logout() {
			this.$store.commit('memsource/SET_SESSION', null)
		}
	}
}
</script>
