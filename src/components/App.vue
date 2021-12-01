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
import { store } from "../store";
import Export from "./Screens/Export.vue";
import History from "./Screens/History.vue";
import Import from "./Screens/Import.vue";
import Upload from "./Screens/Upload.vue";

export default {
	components: {
		Export,
		History,
		Import,
		Upload,
	},
	data() {
		return {
			tabs: [
				{
					icon: "share",
					text: "Export",
					component: "Export",
				},
				{
					icon: "upload",
					text: "Upload",
					component: "Upload",
				},
				{
					icon: "download",
					text: "Import",
					component: "Import",
				},
				{
					icon: "clock",
					text: "History",
					component: "History",
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
		this.screen = localStorage.memsourceScreen || "Export";
	},
};
</script>

<style lang="postcss" scoped>
.k-view {
	max-width: 50rem;
}
</style>
