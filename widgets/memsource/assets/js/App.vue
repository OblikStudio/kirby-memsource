<style lang="scss">
@import 'vars';

.memsource-widget {
    min-height: 12em;
    max-height: 20em;
    overflow: auto;
    position: relative;

    .loading-overlay {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        position: absolute;
            top: 0;
            left: 0;

        font-size: 2em;
        background: #fff;
        user-select: none;

        .loading-content {
            transition: opacity $transition-loader;
        }

        &.fade-enter,
        &.fade-leave-to {
            .loading-content {
                opacity: 0;
            }
        }

        &.fade-enter-active {
            transition: opacity $transition-leave;
        }

        &.fade-leave-active {
            transition: opacity $transition-enter;
        }
    }

    .fade-enter,
    .fade-leave-to {
        opacity: 0;
    }

    .fade-enter-active {
        transition: opacity $transition-enter;
    }

    .fade-leave-active {
        transition: opacity $transition-leave;
    }

    .fade-enter-active + .loading-overlay {
        // If the view is just fading in, there's no reason to animate the
        // loader overlay.
        transition: none;
    }
}
</style>

<template>
    <div class="memsource-widget">
        <transition name="fade" mode="out-in">
        	<component :is="screen"></component>
        </transition>

        <transition name="fade">
            <div v-if="$store.state.loading" class="loading-overlay">
                <div class="loading-content">
                    &hellip;
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
var $button = $('#memsource-widget h2 a')
var $buttonContent = $button.find('span');

module.exports = {
	components: {
		Login: require('./components/Login.vue'),
        Projects: require('./components/Projects.vue')
	},
    data: function () {
        return {
            screen: 'Login'
        };
    },
    methods: {
        openUserScreen: function () {
            console.log('open user');
            this.$store.commit('SET_LOADING', !this.$store.state.loading);
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
        },
        "$store.getters.token": {
            immediate: true,
            handler: function (value) {
                if (value) {
                    this.$store.commit('SET_LOADING', true);
                    this.screen = 'Projects';
                } else {
                    this.screen = 'Login';
                }
            }
        }
    }
};
</script>