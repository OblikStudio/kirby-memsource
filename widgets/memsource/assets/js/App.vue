<template>
	<component :is="$store.state.screen"></component>
</template>

<script>
var $button = $('#memsource-widget h2 a')
var $buttonContent = $button.find('span');

module.exports = {
	components: {
		Login: require('./components/Login.vue'),
        Projects: require('./components/Projects.vue')
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

        if (localStorage.memsourceSession) {
            this.$store.commit('SET_SESSION', JSON.parse(localStorage.memsourceSession));
        }
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
        },
        "$store.getters.token": function (value) {
            if (value) {
                this.$store.commit('SET_SCREEN', 'Projects');
            } else {
                this.$store.commit('SET_SCREEN', 'Login');
            }
        }
    }
};
</script>