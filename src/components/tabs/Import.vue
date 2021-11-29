<template>
	<k-grid gutter="medium">
		<k-column>
			<k-pages-field
				label="Project"
				empty="No project selected yet"
				v-model="project"
				:search="true"
				:endpoints="{
					field: 'memsource/picker/projects',
				}"
			></k-pages-field>
		</k-column>

		<k-column v-if="selectedProject">
			<k-pages-field
				label="Workflow step"
				empty="No workflow step selected yet"
				v-model="workflowStep"
				:endpoints="{
					field: `memsource/picker/projects/${selectedProject.id}/workflowSteps`,
				}"
			></k-pages-field>
		</k-column>

		<k-column v-if="selectedProject && selectedWorkflowStep">
			<k-pages-field
				label="Jobs"
				empty="No jobs selected yet"
				v-model="jobs"
				:search="true"
				:multiple="true"
				:endpoints="{
					field: `memsource/picker/projects/${selectedProject.id}/workflows/${selectedWorkflowStep.workflowLevel}/jobs`,
				}"
			></k-pages-field>
		</k-column>

		<template v-if="jobs.length">
			<k-column>
				<k-toggle-field
					label="Dry run"
					v-model="dry"
					help="Test import without updating the content?"
				></k-toggle-field>
			</k-column>

			<k-column>
				<k-button-group align="center">
					<k-button
						icon="download"
						theme="positive"
						@click="doImport"
					>
						Import jobs
					</k-button>
				</k-button-group>
			</k-column>
		</template>
	</k-grid>
</template>

<script>
export default {
	data() {
		return {
			project: [],
			workflowStep: [],
			jobs: [],
			dry: false,
		};
	},
	computed: {
		selectedProject() {
			return this.project?.[0];
		},
		selectedWorkflowStep() {
			return this.workflowStep?.[0];
		},
	},
	methods: {
		doImport() {
			this.$api
				.post("memsource/import", {
					project: this.selectedProject.id,
					jobs: this.jobs,
					dry: this.dry,
				})
				.then((data) => {
					this.$store.commit("memsource/SET_RESULTS", data);
					this.$store.commit("memsource/SET_SCREEN", "History");
				})
				.catch((error) => {
					this.$store.dispatch("notification/error", error);
				});
		},
	},
	watch: {
		selectedProject() {
			this.workflowStep = [];
		},
		selectedWorkflowStep() {
			this.jobs = [];
		},
	},
};
</script>
