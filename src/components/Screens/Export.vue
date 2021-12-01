<template>
	<k-grid gutter="medium">
		<k-column>
			<k-pages-field
				label="Pages"
				v-model="exportPages"
				:search="true"
				:multiple="true"
				:endpoints="{
					field: 'memsource/pages',
				}"
			></k-pages-field>
		</k-column>
		<k-column width="1/2">
			<k-radio-field
				label="Site"
				v-model="exportSite"
				:options="[
					{ value: false, text: 'Off' },
					{ value: true, text: 'On' },
				]"
				:columns="2"
				:help="exportSiteHelp"
			/>
		</k-column>
		<k-column width="1/2">
			<k-radio-field
				label="Files"
				v-model="exportFiles"
				:options="filesOptions"
				:columns="filesOptions.length"
				:help="activeFilesOption.help"
				@input="input"
			/>
		</k-column>
		<k-column align="center">
			<k-button-group>
				<k-button
					icon="upload"
					:disabled="!isCanExport"
					@click="doExport"
				>
					Export
				</k-button>
			</k-button-group>
		</k-column>
	</k-grid>
</template>

<script>
export default {
	data: () => {
		return {
			exportSite: false,
			exportFiles: "off",
			exportPages: [],
		};
	},
	computed: {
		exportSiteHelp() {
			if (this.exportSite === true) {
				return "Export global site content.";
			} else {
				return "Do not export global site content.";
			}
		},
		filesOptions() {
			return [
				{
					value: "off",
					text: "Off",
					help: "Do not export file fields, such as alt texts.",
				},
				{
					value: "include",
					text: "Include",
					help: "Include all translatable content in files.",
				},
				{
					value: "only",
					text: "Only",
					help: "Export only site and page files, without their content.",
				},
			];
		},
		activeFilesOption() {
			return this.filesOptions.find((e) => e.value === this.exportFiles);
		},
		isCanExport() {
			return this.exportSite || this.exportPages.length;
		},
	},
	methods: {
		doExport() {
			this.$api
				.get("memsource/export", {
					site: this.exportSite ? "1" : null,
					files: this.exportFiles,
					pages: this.exportPages.map((p) => p.id),
				})
				.then((data) => {
					this.$store.commit("memsource/SET_EXPORT", data);
					this.$store.commit("memsource/SET_SCREEN", "Upload");
				})
				.catch((error) => {
					this.$store.dispatch("notification/error", error);
				});
		},
	},
};
</script>
