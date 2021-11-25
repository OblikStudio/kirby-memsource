<template>
	<component :is="$store.getters['memsource/view']"></component>
</template>

<script>
import Projects from "./views/Projects.vue";
import Project from "./views/Project.vue";
import Export from "./views/Export.vue";
import Upload from "../Upload.vue";
import Results from "./views/Results.vue";

export default {
	components: {
		Projects,
		Project,
		Export,
		Upload,
		Results,
	},
	provide() {
		return {
			$jobInfo: this.jobInfo,
		};
	},
	methods: {
		jobInfo(job) {
			return (
				`${new Date(job.dateCreated).toLocaleString()} (${
					job.status
				})` + `<strong>${job.targetLang}</strong>`
			);
		},
	},
	created() {
		this.$store.commit("memsource/VIEW", "Projects");
	},
};
</script>

<style lang="postcss" scoped>
/deep/ {
	.k-list-item-text {
		strong {
			margin-left: 10px;
		}
	}
}
</style>
