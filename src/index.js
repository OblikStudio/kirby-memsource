import App from "./components/App.vue";

panel.plugin("oblik/memsource", {
	views: {
		memsource: {
			icon: "globe",
			label: "Memsource",
			component: App,
		},
	},
});
