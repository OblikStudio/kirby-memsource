<style lang="scss">
@import 'vars';

.memsource-widget {
    position: relative;

    .ms-view {
        display: flex;
        align-items: center;
        height: 15em; 
        overflow: auto;
    }

        .ms-screen-wrapper {
            width: 100%;
            max-height: 100%;
            padding-bottom: 1.5em;
        }

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
        <Crumbs :entries="crumbs" @click="handleCrumb"></Crumbs>

        <div class="ms-view">
            <div class="ms-screen-wrapper">
                <transition name="fade" mode="out-in">
                    <component :is="screen" @selectProject="selectProject" class="ms-screen"></component>
                </transition>
            </div>
        </div>

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
        Crumbs: require('./components/Crumbs.vue'),
		Login: require('./components/Login.vue'),
        Projects: require('./components/Projects.vue'),
        Project: require('./components/Project.vue')
	},
    data: function () {
        return {
            screen: 'Login',
            crumbs: []
        };
    },
    methods: {
        openUserScreen: function () {
            console.log('open user');
            this.$store.commit('SET_LOADING', !this.$store.state.loading);
        },
        selectProject: function (project) {
            this.$store.commit('SET_PROJECT', project);
            this.screen = 'Project';
        },
        handleCrumb: function (crumb) {
            for (var i = this.crumbs.length - 1; i >= 0; i--) {
                var isClicked = (this.crumbs[i].id === crumb);

                if (!isClicked || this.screen !== crumb) {
                    this.crumbs.splice(i, 1);
                }

                if (isClicked) {
                    break;
                }
            }

            this.screen = crumb;
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
        screen: {
            immediate: true,
            handler: function (value, oldValue) {
                var crumbId = value,
                    crumbText = value;

                if (value === 'Login' || oldValue === 'Login') {
                    this.crumbs = [];
                }

                if (value === 'Project') {
                    crumbText = this.$store.state.project.name;
                }

                this.crumbs.push({
                    id: crumbId,
                    text: crumbText
                });
            }
        },
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
                    // this.$store.commit('SET_LOADING', true);
                    this.screen = 'Projects';
                } else {
                    this.screen = 'Login';
                }
            }
        }
    }
};
</script>