<template>
	<table class="k-structure-table ms-results-table">
		<thead>
			<tr>
				<th class="k-structure-table-index">#</th>
				<th class="k-structure-table-column">Field</th>
				<th class="k-structure-table-column">Change</th>
			</tr>
		</thead>

		<template v-for="(change, key, index) in data">
			<tr :key="key">
				<td class="k-structure-table-index" rowspan="2">
					<span class="k-structure-table-index-number">
						{{ index + 1 }}
					</span>
				</td>
				<td class="k-structure-table-column" rowspan="2">
					<p class="k-structure-table-text">
						{{ key }}
					</p>
				</td>
				<td class="k-structure-table-column ms-results-table-old">
					<p class="k-structure-table-text">
						{{ change.$old }}
					</p>
				</td>
			</tr>

			<tr :key="key">
				<td class="k-structure-table-column ms-results-table-new">
					<p class="k-structure-table-text">
						{{ change.$new }}
					</p>
				</td>
			</tr>
		</template>
	</table>
</template>

<script>
export default {
	props: {
		data: Object,
	},
};
</script>

<style>
.ms-results-table {
	box-shadow: none;
	border: 1px solid #ccc;
}

.ms-results-table th:nth-child(2) {
	width: 30%;
}

.ms-results-table th:nth-child(3) {
	width: 70%;
}

.ms-results-table .k-structure-table-column {
	padding-top: 9.75px;
	padding-bottom: 9.75px;
}

.ms-results-table-old {
	background: #fff5f5;
}

.ms-results-table-new {
	background: #fbfff3;
}

.ms-results-table .k-structure-table-text {
	white-space: normal;
}

.ms-results-table tr:nth-last-child(2) td:first-child,
.ms-results-table tr:nth-last-child(2) td:nth-child(2) {
	border-bottom: none;
}
</style>
