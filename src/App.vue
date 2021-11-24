<template>
	<k-view>
		<div class="k-header">
			<div class="k-tabs">
				<nav>
					<k-button
						class="k-tab-button"
						v-for="tab in tabs"
						:key="tab.component"
						:current="screen === tab.component"
						:icon="tab.icon"
						@click="screen = tab.component"
					>
						{{ tab.text }}
					</k-button>
				</nav>
			</div>
		</div>

		<component :is="screen"></component>
	</k-view>
</template>

<script>
import { store } from "./store";
import Export from "./components/tabs/Export.vue";

export default {
	components: {
		Export,
	},
	data() {
		return {
			tabs: [
				{
					icon: "upload",
					text: "Export",
					component: "Export",
				},
			],
		};
	},
	computed: {
		screen: {
			get() {
				return this.$store.state.memsource.screen;
			},
			set(value) {
				return this.$store.commit("memsource/SET_SCREEN", value);
			},
		},
	},
	beforeCreate() {
		this.$store.registerModule("memsource", store);
	},
	created() {
		this.screen = "Export";
	},
};
</script>

<style lang="postcss" scoped>
.k-view {
	max-width: 50rem;
}
</style>
