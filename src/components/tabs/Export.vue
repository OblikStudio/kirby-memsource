<template>
	<k-grid gutter="medium">
		<k-column width="1/2">
			<k-toggle-field v-model="exportSite" label="Site" />
		</k-column>
		<k-column width="1/2">
			<k-toggle-field v-model="exportFiles" label="Files" />
		</k-column>
		<k-column>
			<k-pages-field
				label="Pages"
				v-model="pages"
				:search="true"
				:multiple="true"
				:endpoints="{
					field: 'memsource/pages'
				}"
			></k-pages-field>
		</k-column>
	</k-grid>
</template>

<script>
export default {
	data: () => {
		return {
			exportSite: false,
			exportFiles: true,
			pages: []
		}
	}
}
</script>
