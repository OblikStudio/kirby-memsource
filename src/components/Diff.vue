<template>
	<div class="ms-diff">
		<div v-for="entry in entries" :key="entry.name" class="ms-diff-entry">
			<p>{{ entry.name }}</p>
			<div>{{ entry.oldValue }}</div>
			<div>{{ entry.newValue }}</div>
		</div>
	</div>
</template>

<script>
export default {
	props: {
		entries: Array,
	},
};
</script>

<style lang="postcss" scoped>
.ms-diff-entry {
	& + .ms-diff-entry {
		margin-top: 1rem;
	}

	p {
		font-family: monospace;
		margin-left: 11px;
		margin-bottom: 0.3rem;
	}
}
</style>
