<template>
	<k-grid gutter="medium">
		<k-column>
			<k-text-field
				v-model="jobName"
				after=".json"
				:label="$t('memsource.label.job')"
			/>
		</k-column>

		<k-column v-if="stats">
			<ul class="k-system-info-box">
				<li>
					<dl>
						<dt>Models</dt>
						<dd>{{ modelsCount.toLocaleString() }}</dd>
					</dl>
				</li>
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

						<k-button icon="download" @click="downloadExport">
							{{ $t("file") }}
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

		<k-column v-if="checkDataEmpty(data)">
			<k-box theme="notice">
				The upload data is currently empty. You should export something
				first.
			</k-box>
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
			<k-column v-if="missingProjectLangs.length">
				<k-box theme="notice">
					The selected project does not target the following site
					languages:
					<strong>
						{{ missingProjectLangs.join(", ").toUpperCase() }}
					</strong>
				</k-box>
			</k-column>

			<k-column v-if="missingSiteLangs.length">
				<k-box theme="notice">
					The selected project targets languages not yet added to the
					site:
					<strong>
						{{ missingSiteLangs.join(", ").toUpperCase() }}
					</strong>
				</k-box>
			</k-column>

			<template v-if="validTargetLangsOptions.length">
				<k-column>
					<k-checkboxes-field
						v-model="selectedTargetLangs"
						:label="$t('memsource.label.target_langs')"
						:options="validTargetLangsOptions"
						:columns="Math.min(validTargetLangsOptions.length, 7)"
					/>
				</k-column>

				<k-column>
					<k-button-group align="center">
						<k-button
							theme="positive"
							icon="upload"
							:disabled="!selectedTargetLangs.length"
							@click="upload"
						>
							{{ $t("upload") }}
						</k-button>
					</k-button-group>
				</k-column>
			</template>
			<k-column v-else-if="!isLoadingLangs">
				<k-box theme="negative">No valid target langs.</k-box>
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
		let domain = window.location.hostname.replace(".", "-");
		let date = new Date().toLocaleDateString("en", {
			day: "2-digit",
			month: "short",
			year: "2-digit",
		});
		let jobName = `${domain}-${date}`
			.toLowerCase()
			.replace(/[^a-z0-9]+/g, "-");

		return {
			isLoadingLangs: false,
			dataString: null,
			project: [],
			validTargetLangs: [],
			missingSiteLangs: [],
			missingProjectLangs: [],
			selectedTargetLangs: [],
			jobName,
		};
	},
	computed: {
		data() {
			return this.$store.state.memsource.export;
		},
		stats() {
			return countObjectData(this.data);
		},
		modelsCount() {
			let result = 0;
			let pages = this.data?.pages;
			let files = this.data?.files;

			if (this.data?.site) result++;
			if (pages) result += Object.keys(pages).length;
			if (files) result += Object.keys(files).length;

			return result;
		},
		selectedProject() {
			return this.project?.[0];
		},
		validTargetLangsOptions() {
			return this.validTargetLangs.map((code) => ({
				text: code.toUpperCase(),
				value: code,
			}));
		},
	},
	methods: {
		editorOpen() {
			this.dataString = !this.checkDataEmpty(this.data)
				? JSON.stringify(this.data, undefined, 2)
				: "";
			this.$refs.editDialog.open();
		},
		editorSubmit() {
			let data = null;

			try {
				data = JSON.parse(this.dataString);
			} catch (e) {
				this.$refs.editDialog.error("Invalid JSON.");
				return;
			}

			this.$store.commit("memsource/SET_EXPORT", data);
			this.$refs.editDialog.close();
		},
		checkDataEmpty(data) {
			return !data || Object.keys(data).length === 0;
		},
		upload() {
			this.$api
				.post("memsource/upload", {
					projectId: this.selectedProject.id,
					targetLangs: this.selectedTargetLangs,
					jobName: this.jobName,
					jobData: this.data,
				})
				.then((res) => {
					this.$store.dispatch(
						"notification/success",
						`Successfully created ${res.jobs.length} jobs!`
					);
				})
				.catch((error) => {
					this.$store.dispatch("notification/error", error);
				});
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
			this.validTargetLangs = [];
			this.missingSiteLangs = [];
			this.missingProjectLangs = [];

			if (!value) {
				return;
			}

			this.isLoadingLangs = true;
			this.$api
				.get("memsource/verify-languages", {
					targetLangs: value.targetLangs,
				})
				.then((data) => {
					this.validTargetLangs = data.validTargetLangs;
					this.missingSiteLangs = data.missingSiteLangs;
					this.missingProjectLangs = data.missingProjectLangs;
				})
				.catch((error) => {
					this.$store.dispatch("notification/error", error);
				})
				.then(() => {
					this.isLoadingLangs = false;
				});
		},
		validTargetLangs(value) {
			this.selectedTargetLangs = [...value];
		},
	},
};
</script>

<style>
@media screen and (min-width: 22rem) {
	.ms-edit-dialog[data-size="default"] {
		width: 55rem;
	}
}
</style>
