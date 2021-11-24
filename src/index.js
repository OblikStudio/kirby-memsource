import App from "./App.vue";

panel.plugin("oblik/memsource", {
	views: {
		memsource: {
			icon: "globe",
			label: "Memsource",
			component: App,
		},
	},
});
