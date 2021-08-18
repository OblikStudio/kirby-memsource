<template>
	<k-list>
		<k-list-item
			v-for="project in projects"
			:key="project.uid"
			:text="project.name"
			:info="info(project)"
			:icon="{ type: 'page', back: 'black' }"
			@click="open(project)"
		/>
	</k-list>
</template>

<script>
export default {
	inject: ['$alert', '$loading'],
	data() {
		return {
			projects: []
		}
	},
	methods: {
		open(project) {
			this.$store.commit('memsource/SET_PROJECT', project)
			this.$store.commit('memsource/VIEW', {
				text: project.name,
				value: 'Project'
			})
		},
		info(project) {
			let date = new Date(project.dateCreated)
			return `${date.toLocaleString()}<strong>#${project.internalId}</strong>`
		}
	},
	created() {
		this.$store.commit('memsource/SET_PROJECT', null)
		this.$loading(
			this.$store
				.dispatch('memsource/memsource', {
					url: '/projects',
					method: 'get'
				})
				.then(response => {
					this.projects = response.data.content
				})
				.catch(this.$alert)
		)
	}
}
</script>

<style lang="postcss" scoped>
.k-list-item {
	cursor: pointer;
}
</style>
