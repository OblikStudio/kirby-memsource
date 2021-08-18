<template>
	<k-form
		v-if="showForm"
		v-model="params"
		@submit="submit"
		:fields="{
			snapshot: {
				width: '1/2',
				type: 'select',
				options: snapshots,
				label: $t('snapshot'),
				help: $t('memsource.help.snapshot')
			},
			pages: {
				width: '1/2',
				type: 'text',
				label: $t('pages'),
				help: $t('memsource.help.pages')
			}
		}"
	>
		<k-button-group slot="footer" align="center">
			<k-button icon="upload" @click="submit">
				{{ $t('export') }}
			</k-button>
		</k-button-group>
	</k-form>
</template>

<script>
export default {
	inject: ['$alert', '$loading'],
	data() {
		return {
			showForm: false,
			snapshots: [],
			params: {
				snapshot: null,
				pages: null
			}
		}
	},
	methods: {
		submit() {
			let params = { ...this.params }

			if (typeof params.pages === 'string' && params.pages.length) {
				params.pages = `!${params.pages}!` // PHP regex
			} else {
				params.pages = null
			}

			this.$loading(
				this.$store
					.dispatch('memsource/memsource', {
						url: '/export',
						params
					})
					.then(response => {
						this.$store.commit('memsource/SET_EXPORT', response.data)
						this.$store.commit('memsource/VIEW', 'Upload')
					})
					.catch(this.$alert)
			)
		}
	},
	created() {
		this.$loading(
			this.$store
				.dispatch('memsource/memsource', {
					url: '/snapshot',
					method: 'get'
				})
				.then(response => {
					this.snapshots = response.data
						.sort((a, b) => {
							return a.date > b.date ? -1 : 1
						})
						.map(snap => {
							return {
								text: snap.name,
								value: snap.name
							}
						})

					/**
					 * Needed because the select field in the form does not have reactive
					 * options. @see https://github.com/getkirby/kirby/issues/2075
					 */
					this.showForm = true
				})
				.catch(this.$alert)
		)
	}
}
</script>
