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
				label="Jobs"
				empty="No jobs selected yet"
				v-model="jobs"
				:search="true"
				:multiple="true"
				:endpoints="{
					field: `memsource/picker/projects/${selectedProject.id}/jobs`,
				}"
			></k-pages-field>
		</k-column>

		<k-column>
			<k-button-group v-if="selectedJobs.length" align="center">
				<k-button
					icon="trash"
					theme="negative"
					@click="$refs.dialog.open()"
				>
					{{ $t("delete") }}
				</k-button>

				<k-button
					icon="download"
					theme="positive"
					@click="importHandler"
				>
					{{ $t("import") }}
				</k-button>
			</k-button-group>

			<k-dialog
				ref="dialog"
				theme="negative"
				icon="trash"
				:button="$t('delete')"
				@submit="deleteJobs"
			>
				<k-text>
					{{
						$t("memsource.info.jobs_deletion", {
							count: selectedJobs.length,
						})
					}}
				</k-text>
			</k-dialog>
		</k-column>
	</k-grid>
</template>

<script>
import freeze from "deep-freeze-node";

export default {
	data() {
		return {
			project: [],
			jobs: [],
			query: null,
			selectedJobs: [],
		};
	},
	computed: {
		selectedProject() {
			return this.project?.[0];
		},
	},
	methods: {
		toggle() {
			if (this.selectedJobs.length !== this.filteredJobs.length) {
				this.selectedJobs = this.filteredJobs.map((job) => job.uid);
			} else {
				this.selectedJobs = [];
			}
		},
		importHandler() {
			let jobs = this.selectedJobs
				.map((id) => this.jobs.find((job) => job.uid === id))
				.filter((job) => !!job);

			this.$loading(
				Promise.all(jobs.map(this.importJob, this)).then((results) => {
					this.$store.commit("memsource/SET_RESULTS", results);
					this.$store.commit("memsource/VIEW", "Results");
				})
			);
		},
		loadJobs() {
			return this.$store
				.dispatch("memsource/memsource", {
					url: `/projects/${this.projectId}/jobs`,
				})
				.then((response) => {
					this.jobs = freeze(response.data.content);
					this.selectedJobs = [];
				})
				.catch(this.$alert);
		},
		importJob(job) {
			let promise;
			let language = this.$store.getters[
				"memsource/availableLanguages"
			].find((lang) => {
				return job.targetLang.indexOf(lang.code) === 0;
			});

			if (language) {
				promise = this.$store
					.dispatch("memsource/memsource", {
						url: `/projects/${this.projectId}/jobs/${job.uid}/targetFile`,
						responseType: "blob",
					})
					.then((response) => {
						let blob = response.data;
						let config = {
							language: language.code,
						};

						return this.$store.dispatch("memsource/memsource", {
							url: "/import",
							method: "post",
							headers: {
								Memsource: JSON.stringify(config),
							},
							data: blob,
						});
					})
					.then((response) => {
						return Promise.resolve(response.data);
					});
			} else {
				promise = Promise.reject(
					new Error(this.$t("memsource.info.invalid_language"))
				);
			}

			return promise
				.then((data) => {
					return Promise.resolve({ job, language, data });
				})
				.catch((error) => {
					return Promise.resolve({ job, language, error });
				});
		},
		deleteJobs() {
			let jobs = this.selectedJobs;

			this.$refs.dialog.close();
			this.$loading(
				this.$store
					.dispatch("memsource/memsource", {
						url: `/projects/${this.projectId}/jobs/batch`,
						method: "delete",
						data: {
							jobs: jobs.map((id) => ({ uid: id })),
						},
					})
					.then((response) => {
						return this.loadJobs();
					})
					.then((response) => {
						this.$alert(
							this.$t("memsource.info.deleted_jobs", {
								count: jobs.length,
							})
						);
					})
					.catch(this.$alert)
			);
		},
	},
	watch: {
		selectedProject() {
			this.jobs = [];
		},
	},
};
</script>
