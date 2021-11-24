<template>
	<div class="ms-namegen">
		<k-text-field v-bind="$attrs" v-model="value">
			<template slot="options">
				<slot name="options"></slot>
			</template>
		</k-text-field>

		<k-button icon="refresh" @click="generate"></k-button>
	</div>
</template>

<script>
import dateFormat from "dateformat";
import Wordgen from "../modules/wordgen";

let wordgen = new Wordgen({
	length: 6,
});

export default {
	props: {
		value: true,
	},
	methods: {
		generate() {
			let string = wordgen.generate();
			let date = dateFormat(new Date(), `-mmm-dd`);
			let name = (string + date).toLowerCase();

			this.$emit("input", name);
		},
	},
	created() {
		this.generate();
	},
};
</script>

<style lang="postcss" scoped>
.ms-namegen {
	position: relative;
}

.k-input {
	padding-right: 40px;
}

/deep/ {
	.k-counter {
		display: none !important;
	}
}

.k-button {
	position: absolute;
	bottom: 1px;
	right: 1px;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 36px;
	height: 36px;
}
</style>
