import freeze from "deep-freeze-node";

export let store = {
	namespaced: true,
	state: {
		/**
		 * Date after which imports are considered "new".
		 */
		historyDate: new Date(),
		export: null,
		screen: null,
	},
	mutations: {
		SET_EXPORT: (state, value) => {
			state.export = freeze(value);
		},
		SET_SCREEN(state, value) {
			if (state.screen === "History") {
				state.historyDate = new Date();
			}

			state.screen = value;
			localStorage.memsourceScreen = value;
		},
	},
};
