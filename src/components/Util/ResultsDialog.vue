<template>
	<k-dialog
		ref="dialog"
		class="ms-results-dialog"
		size="large"
		:cancelButton="false"
		v-on="$listeners"
	>
		<k-grid gutter="medium">
			<k-column>
				<ul class="k-system-info-box">
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
							<dt>Models</dt>
							<dd>{{ statsModels }}</dd>
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
							<dd class="ms-results-dialog-stats-icon">
								<k-icon :type="statsDryIcon" />
								&zwnj;
							</dd>
						</dl>
					</li>
				</ul>
			</k-column>
			<template v-if="modelsOptions.length > 0">
				<k-column>
					<k-select-field
						label="Model"
						v-model="selectedModel"
						:options="modelsOptions"
						:empty="false"
					></k-select-field>
				</k-column>
				<k-column>
					<results-table
						:data="models[selectedModel]"
					></results-table>
				</k-column>
			</template>
			<k-column v-else>
				<k-box v-if="data.importChanges === 0" theme="info">
					No differences in the content.
				</k-box>
				<k-box v-else-if="data.importChanges > 0" theme="notice">
					Couldn't load import changes.
				</k-box>
				<k-box
					v-else
					theme="negative"
					:text="error || 'Unknown error.'"
				></k-box>
			</k-column>
		</k-grid>
	</k-dialog>
</template>

<script>
import ResultsTable from "./ResultsTable.vue";

export default {
	components: {
		ResultsTable,
	},
	props: {
		data: Object,
	},
	data() {
		return {
			selectedModel: null,
		};
	},
	computed: {
		error() {
			let err = this.data.importError;
			return err?.errorDescription || err;
		},
		models() {
			let res = {};
			let diff = this.data.importDiff;

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
		modelsOptions() {
			let res = [];

			for (let k in this.models) {
				let changes = Object.keys(this.models[k]).length;
				if (changes > 0) {
					res.push({
						value: k,
						text: `${k} (${changes})`,
					});
				}
			}

			return res;
		},
		statsDate() {
			return new Date(this.data.importDate);
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
			return this.data.jobLang?.toUpperCase() || "?";
		},
		statsModels() {
			return this.modelsOptions.length;
		},
		statsChanges() {
			let c = this.data.importChanges;
			return typeof c === "number" ? c.toLocaleString() : "?";
		},
		statsDryIcon() {
			return this.data.isDry === true ? "check" : "cancel";
		},
	},
	methods: {
		open() {
			this.$refs.dialog.open();
		},
		close() {
			this.$refs.dialog.close();
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
				} else if (val.$new && val.$old) {
					res[k] = val;
				}
			}

			return res;
		},
	},
	watch: {
		modelsOptions: {
			immediate: true,
			handler(val) {
				if (val.length > 0) {
					this.selectedModel = val[0].value;
				}
			},
		},
	},
};
</script>

<style>
.ms-results-dialog .k-box .k-text {
	white-space: pre-line; /* format long PHP error messages */
}

.ms-results-dialog .k-structure-table th {
	top: -1.5rem; /* sticky offset to negate dialog padding */
}

@media screen and (min-width: 40rem) {
	.ms-results-dialog[data-size="large"] {
		width: 55rem;
	}
}

.ms-results-dialog .k-system-info-box {
	line-height: 1.35;
}

.ms-results-dialog-stats-icon {
	display: flex;
	align-items: center;
}
</style>
