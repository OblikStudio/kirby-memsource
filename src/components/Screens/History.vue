<template>
	<k-grid gutter="medium">
		<k-column>
			<k-list :items="importListItems" />

			<k-pagination
				v-bind="pagination"
				align="center"
				:details="true"
				@paginate="fetch($event.page)"
			/>

			<results-dialog
				ref="dialog"
				v-if="importData"
				:data="importData"
				@submit="$refs.dialog.close()"
			/>
		</k-column>
	</k-grid>
</template>

<script>
import freeze from "deep-freeze-node";
import ResultsDialog from "../Util/ResultsDialog.vue";

export default {
	components: {
		ResultsDialog,
	},
	data() {
		return {
			importEntries: [],
			pagination: undefined,
			importData: null,
		};
	},
	computed: {
		importListItems() {
			return this.importEntries.map((e) => {
				let importDate = new Date(e.importDate);
				let isNew =
					this.$store.state.memsource.historyDate < importDate;

				let text = `${e.jobFile} (${e.jobLang})`;
				if (e.isDry) {
					text += " (dry)";
				}

				let info;
				if (isNew) {
					info = "new";
				} else {
					info = importDate.toLocaleString(undefined, {
						dateStyle: "medium",
						timeStyle: "short",
					});
				}

				return {
					text,
					info,
					image: true,
					class: isNew ? "ms-list-item-new" : "",
					icon: {
						type: e.isSuccess ? "check" : "cancel",
						color: e.isSuccess ? "green" : "red",
						back: "white",
					},
					link: () => {
						this.openImportResults(e.importFile);
					},
				};
			});
		},
	},
	methods: {
		fetch(page) {
			this.$api
				.get("memsource/imports", {
					page,
					limit: 15,
				})
				.then((res) => {
					this.importEntries = res.data;
					this.pagination = res.pagination;
				})
				.catch((error) => {
					this.$store.dispatch("notification/error", error);
				});
		},
		openImportResults(importFile) {
			this.$api
				.get(`memsource/imports/${importFile}`)
				.then((data) => {
					this.importData = freeze(data);
					this.$nextTick(() => {
						this.$refs.dialog.open();
					});
				})
				.catch((error) => {
					this.$store.dispatch("notification/error", error);
				});
		},
	},
	mounted() {
		this.fetch(1);
	},
};
</script>

<style>
.ms-list-item-new {
	font-weight: bold;
}
</style>
