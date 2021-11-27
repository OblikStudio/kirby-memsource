<template>
	<k-grid gutter="medium">
		<k-column>
			<k-list>
				<k-list-item
					ref="jobItems"
					v-for="job in jobResults"
					v-bind="job"
					:key="job.id"
					:link="openJobLog(job)"
				></k-list-item>
			</k-list>
		</k-column>

		<k-dialog
			ref="resultsDialog"
			class="ms-results-dialog"
			size="large"
			:cancelButton="false"
			@submit="$refs.resultsDialog.close()"
		>
			<k-grid gutter="medium" v-if="logData">
				<k-column>
					<ul class="k-system-info-box ms-results-stats">
						<li>
							<dl>
								<dt>Date</dt>
								<dd>{{ statsDateText }}</dd>
							</dl>
						</li>
						<li>
							<dl>
								<dt>Time</dt>
								<dd>{{ statsTimeText }}</dd>
							</dl>
						</li>
						<li>
							<dl>
								<dt>Language</dt>
								<dd>{{ statsLang }}</dd>
							</dl>
						</li>
						<li>
							<dl>
								<dt>Changes</dt>
								<dd>{{ statsChanges }}</dd>
							</dl>
						</li>
						<li>
							<dl>
								<dt>Dry</dt>
								<dd class="ms-results-stats-icon">
									<k-icon :type="statsDryIcon" />
									&zwnj;
								</dd>
							</dl>
						</li>
					</ul>
				</k-column>
				<k-column>
					<k-select-field
						label="Model"
						v-model="logObjectActive"
						:options="logObjectsOptions"
						:empty="false"
					></k-select-field>
				</k-column>
				<k-column>
					<results-table
						:data="logObjects[logObjectActive]"
					></results-table>
				</k-column>
			</k-grid>
		</k-dialog>
	</k-grid>
</template>

<script>
import freeze from "deep-freeze-node";
import ResultsTable from "./ResultsTable.vue";

export default {
	components: {
		ResultsTable,
	},
	data() {
		return {
			logData: null,
			logObjectActive: null,
		};
	},
	computed: {
		jobResults() {
			return this.$store.state.memsource.results;
		},
		logObjects() {
			let res = {};
			let diff = this.logData?.diff;

			if (diff && typeof diff === "object") {
				for (let k of ["site", "pages", "files"]) {
					if (diff[k]) {
						if (k === "site") {
							res[k] = this.flatten(diff[k]);
						} else if (typeof diff[k] === "object") {
							for (let k2 in diff[k]) {
								res[`${k}/${k2}`] = this.flatten(diff[k][k2]);
							}
						}
					}
				}
			}

			return res;
		},
		logObjectsOptions() {
			let res = [];

			for (let k in this.logObjects) {
				res.push({
					value: k,
					text: `${k} (${Object.keys(this.logObjects[k]).length})`,
				});
			}

			return res;
		},
		statsDate() {
			return new Date(this.logData.date);
		},
		statsDateText() {
			return this.statsDate.toLocaleDateString(undefined, {
				dateStyle: "medium",
			});
		},
		statsTimeText() {
			return this.statsDate.toLocaleTimeString(undefined, {
				timeStyle: "short",
			});
		},
		statsLang() {
			return this.logData?.lang.toUpperCase();
		},
		statsChanges() {
			let res = 0;

			for (let k in this.logObjects) {
				res += Object.keys(this.logObjects[k]).length;
			}

			return res.toLocaleString();
		},
		statsDryIcon() {
			return this.logData?.dry === true ? "check" : "cancel";
		},
	},
	methods: {
		openJobLog(job) {
			return () => {
				this.$api
					.get("memsource/import", {
						importFile: job.importFile,
					})
					.then((data) => {
						this.logData = freeze(data);
						this.$refs.resultsDialog.open();
					})
					.catch((error) => {
						this.$store.dispatch("notification/error", error);
					});
			};
		},
		flatten(obj) {
			let res = {};

			for (let k in obj) {
				let val = obj[k];

				if (!val || !obj.hasOwnProperty(k)) {
					continue;
				}

				if (typeof val === "object" && !val.$new && !val.$old) {
					let vres = this.flatten(val);

					for (let vk in vres) {
						res[`${k}/${vk}`] = vres[vk];
					}
				} else {
					res[k] = val;
				}
			}

			return res;
		},
	},
	watch: {
		logObjectsOptions(val) {
			if (val.length > 0) {
				this.logObjectActive = val[0].value;
			}
		},
	},
};
</script>

<style>
@media screen and (min-width: 40rem) {
	.ms-results-dialog[data-size="large"] {
		width: 55rem;
	}
}

.ms-results-stats {
	line-height: 1.35;
}

.ms-results-stats-icon {
	display: flex;
	align-items: center;
	justify-content: center;
}
</style>
