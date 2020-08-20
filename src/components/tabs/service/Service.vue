<template>
	<component :is="$store.getters.view"></component>
</template>

<script>
import Projects from './views/Projects.vue'
import Project from './views/Project.vue'
import Export from './views/Export.vue'
import Upload from './views/Upload.vue'
import Import from './views/Import.vue'
import Results from './views/Results.vue'

export default {
	components: {
		Projects,
		Project,
		Export,
		Upload,
		Import,
		Results
	},
	provide() {
		return {
			$jobInfo: this.jobInfo
		}
	},
	methods: {
		jobInfo(job) {
			return (
				`${new Date(job.dateCreated).toLocaleString()} (${job.status})` +
				`<strong>${job.targetLang}</strong>`
			)
		}
	},
	created() {
		this.$store.commit('VIEW', 'Projects')
	}
}
</script>

<style lang="scss" scoped>
/deep/ {
	.k-list-item-text {
		strong {
			margin-left: 10px;
		}
	}
}
</style>
