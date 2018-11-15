var Vue = require('vue');
var store = require('./store');
var general = require('./mixins/general');

Vue.mixin(general);

window.model = new Vue({
	el: '.memsource-widget',
	store: store,
	render: function (create) {
		return create(require('./App.vue'));
	}
});
