<template>
	<ul class="k-system-info-box">
		<li v-for="entry in data" :key="entry.title">
			<dl>
				<dt>{{ entry.title }}</dt>
				<dd v-html="entry.content"></dd>
			</dl>
		</li>
	</ul>
</template>

<script>
export default {
	props: {
		data: Object
	}
}
</script>
