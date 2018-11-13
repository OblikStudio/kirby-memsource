<template>
	<Login></Login>
</template>

<script>
var Login = require('./components/Login.vue');
var $button = $('#memsource-widget h2 a')
var $buttonContent = $button.find('span');

module.exports = {
	components: {
		Login: Login
	},
    methods: {
        openUserScreen: function () {
            console.log('open user');
        }
    },
    created: function () {
        var self = this;

        $button.on('click', function (event) {
            event.preventDefault();
            self.openUserScreen();
        });
    },
    watch: {
        "$store.state.session": {
            immediate: true,
            handler: function (data) {
                var isLoggedIn = (data && data.user);

                if (isLoggedIn) {
                    $buttonContent.html(data.user.firstName);
                }

                $button.css('display', (isLoggedIn) ? 'block' : 'none');
            }
        }
    }
};
</script>