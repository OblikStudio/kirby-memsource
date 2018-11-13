var Vue = require('vue');
var store = require('./store');
var mixin = require('./mixin');

Vue.mixin(mixin);

window.model = new Vue({
	el: '.memsource-widget',
	store: store,
	render: function (create) {
		return create(require('./App.vue'));
	}
});
