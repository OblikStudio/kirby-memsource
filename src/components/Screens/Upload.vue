<template>
	<k-grid gutter="medium">
		<k-column v-if="stats">
			<ul class="k-system-info-box">
				<li>
					<dl>
						<dt>{{ $t("strings") }}</dt>
						<dd>{{ stats.strings.toLocaleString() }}</dd>
					</dl>
				</li>
				<li>
					<dl>
						<dt>{{ $t("words") }}</dt>
						<dd>{{ stats.words.toLocaleString() }}</dd>
					</dl>
				</li>
				<li>
					<k-button-group>
						<k-button icon="edit" @click="editorOpen">
							Edit
						</k-button>

						<k-dialog
							ref="editDialog"
							class="ms-edit-dialog"
							@submit="editorSubmit"
						>
							<k-textarea-field
								label="JSON"
								font="monospace"
								v-model="dataString"
								:buttons="false"
								:counter="false"
							></k-textarea-field>
						</k-dialog>
					</k-button-group>
				</li>
			</ul>
		</k-column>

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

		<template v-if="selectedProject">
			<k-column v-if="missingProjectLangs && missingProjectLangs.length">
				<k-box theme="notice">
					The selected project does not target the following site
					languages:
					<strong>
						{{ missingProjectLangs.join(", ").toUpperCase() }}
					</strong>
				</k-box>
			</k-column>

			<k-column v-if="missingSiteLangs && missingSiteLangs.length">
				<k-box theme="notice">
					The selected project targets languages not yet added to the
					site:
					<strong>
						{{ missingSiteLangs.join(", ").toUpperCase() }}
					</strong>
				</k-box>
			</k-column>

			<k-column>
				<k-checkboxes-field
					v-model="selectedLangs"
					:label="$t('memsource.label.target_langs')"
					:options="availableLangs"
					:columns="6"
				/>
			</k-column>

			<k-column>
				<k-text-field
					v-model="jobName"
					:label="$t('memsource.label.job')"
				/>
			</k-column>

			<k-column>
				<k-button-group align="center">
					<k-button icon="download" @click="downloadExport">
						{{ $t("file") }}
					</k-button>

					<k-button theme="positive" icon="upload" @click="upload">
						{{ $t("upload") }}
					</k-button>
				</k-button-group>
			</k-column>
		</template>
	</k-grid>
</template>

<script>
function countObjectData(data) {
	let stats = {
		strings: 0,
		words: 0,
	};

	for (let k in data) {
		let value = data[k];

		if (typeof value === "object" && value !== null) {
			let childStats = countObjectData(value);
			stats.strings += childStats.strings;
			stats.words += childStats.words;
		} else if (data.hasOwnProperty(k) && k !== "id") {
			stats.strings++;

			if (typeof value === "string") {
				stats.words += value.trim().split(/\s+/).length;
			}
		}
	}

	return stats;
}

export default {
	data() {
		return {
			dataString: null,
			project: [],
			selectedLangs: [],
			jobName: null,
		};
	},
	computed: {
		data() {
			return this.$store.state.memsource.export;
		},
		stats() {
			return countObjectData(this.data);
		},
		selectedProject() {
			return this.project?.[0];
		},
		siteLangs() {
			return this.$store.state.languages.all;
		},
		projectLangs() {
			return this.selectedProject?.targetLangs;
		},
		missingSiteLangs() {
			return (
				this.selectedProject &&
				this.projectLangs?.filter((code) => {
					return !this.siteLangs.find((lang) => lang.code === code);
				})
			);
		},
		missingProjectLangs() {
			return (
				this.selectedProject &&
				this.siteLangs
					?.filter((lang) => {
						return !this.projectLangs?.find(
							(code) => code === lang.code
						);
					})
					.map((lang) => lang.code)
			);
		},
		availableLangs() {
			return this.projectLangs?.map((code) => ({
				text: code.toUpperCase(),
				value: code,
			}));
		},
	},
	methods: {
		editorOpen() {
			this.dataString = JSON.stringify(this.data, undefined, 2);
			this.$refs.editDialog.open();
		},
		editorSubmit() {
			let data = null;

			try {
				data = JSON.parse(this.dataString);
			} catch (e) {
				this.$refs.editDialog.error("Invalid JSON.");
			}

			if (data !== null) {
				this.$store.commit("memsource/SET_EXPORT", data);
				this.$refs.editDialog.close();
			}
		},
		upload() {
			this.$alert(this.$t("upload.progress"));

			this.$store
				.dispatch("memsource/memsource", {
					url: `/upload/${this.project.uid}/${this.jobName}.json`,
					method: "post",
					headers: {
						"Memsource-Langs": JSON.stringify(this.selectedLangs),
					},
					data: this.data,
				})
				.then((response) => {
					let jobs = response.data && response.data.jobs;
					if (jobs && jobs.length) {
						this.$alert(
							this.$t("memsource.info.created_jobs", {
								count: jobs.length,
							}),
							"positive"
						);
					}
				})
				.catch(this.$alert);
		},
		downloadExport() {
			let part = JSON.stringify(this.data, null, 2);
			let blob = new Blob([part], {
				type: "application/json",
			});

			let url = URL.createObjectURL(blob);
			let anchor = document.createElement("a");
			anchor.download = this.jobName;
			anchor.href = url;
			anchor.click();
		},
	},
	watch: {
		selectedProject(value) {
			if (value) {
				this.selectedLangs = [...this.projectLangs];
			}
		},
	},
};
</script>

<style>
.k-system-info-box {
	text-align: center;
}

.ms-edit-dialog {
	width: 100% !important;
	max-width: 75vw;
}
</style>