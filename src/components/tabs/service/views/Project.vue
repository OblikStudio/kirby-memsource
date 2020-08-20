<template>
	<div>
		<header class="k-section-header">
			<k-headline size="large">{{ project.name }}</k-headline>
			<k-button-group>
				<k-button icon="upload" @click="exportView">{{
					$t('export')
				}}</k-button>
				<k-button icon="download" theme="positive" @click="importView">{{
					$t('import')
				}}</k-button>
			</k-button-group>
		</header>
		<Stats :data="properties" />
	</div>
</template>

<script>
import Stats from '@/components/Stats.vue'

export default {
	components: {
		Stats
	},
	computed: {
		project() {
			return this.$store.state.project
		},
		properties() {
			return [
				{
					title: this.$t('memsource.label.source_langs'),
					content: this.project.sourceLang
				},
				{
					title: this.$t('memsource.label.target_langs'),
					content: this.project.targetLangs.join(', ')
				},
				{
					title: this.$t('jobs'),
					content: `${this.project.progress.finishedCount}/${this.project.progress.totalCount}`
				}
			]
		}
	},
	methods: {
		exportView() {
			this.$store.commit('VIEW', 'Export')
		},
		importView() {
			this.$store.commit('VIEW', 'Import')
		}
	}
}
</script>
