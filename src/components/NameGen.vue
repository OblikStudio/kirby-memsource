<template>
	<k-field v-bind="$attrs">
		<k-input
			theme="field"
			type="text"
			v-model="value"
			:required="true"
		/>

		<template slot="options">
			<slot name="options" />
		</template>

		<div class="k-list-item-options">
			<k-button icon="refresh" @click="generate"></k-button>
		</div>
	</k-field>
</template>

<script>
import dateFormat from 'dateformat'
import Wordgen from '@/modules/wordgen'

var wordgen = new Wordgen({
	length: 6
})

export default {
	props: {
		value: true
	},
	methods: {
		generate () {
			var string = wordgen.generate()
			var date = dateFormat(new Date(), `-mmm-dd`)
			var name = (string + date).toLowerCase()

			this.$emit('input', name)
		}
	},
	created () {
		this.generate()
	}
}
</script>

<style lang="scss" scoped>
/deep/ {
	position: relative;
}

.k-input {
	padding-right: 40px;
}

.k-list-item-options {
	position: absolute;
	right: 0;
	bottom: 0;
}
</style>
