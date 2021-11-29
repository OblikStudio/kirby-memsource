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
				:data="importData"
				@submit="$refs.dialog.close()"
			/>
		</k-column>
	</k-grid>
</template>

<script>
import freeze from "deep-freeze-node";
import ResultsDialog from "./ResultsDialog.vue";

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
				let text = `${e.jobFile} (${e.jobLang})`;

				if (e.isDry) {
					text += " (dry)";
				}

				return {
					text,
					info: new Date(e.importDate).toLocaleString(undefined, {
						dateStyle: "medium",
						timeStyle: "short",
					}),
					image: true,
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
					limit: 20,
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
					this.$refs.dialog.open();
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
