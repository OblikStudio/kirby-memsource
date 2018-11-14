<style lang="scss">
@import 'vars';

.memsource-widget {
    position: relative;

    .ms-view {
        display: flex;
        align-items: center;
        height: 15em;
        margin-bottom: 0.5em;
        position: relative;
        overflow: auto;
    }

        .ms-screen-wrapper {
            width: 100%;
            max-height: 100%;
            padding-bottom: 1em;
        }

        .ms-error {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-flow: column nowrap;
            position: absolute;
                top: 0;
                left: 0;
            width: 100%;
            height: 100%;
            background: rgba(#fff, 0.95);
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

    button {
        user-select: none;
    }
}
</style>

<template>
    <div class="memsource-widget">
        <Crumbs :entries="crumbs" @click="handleCrumb"></Crumbs>

        <div class="ms-view">
            <div class="ms-screen-wrapper">
                <transition name="fade" mode="out-in">
                    <component
                        :is="screen"
                        class="ms-screen"
                        @logIn="logIn"
                        @selectProject="selectProject"
                        @export="exportContent"
                        @upload="upload"
                    ></component>
                </transition>
            </div>

            <transition name="fade">
                <div v-if="error" class="ms-error">
                    <Info type="error">
                        {{ error }}
                    </Info>
                    <button
                        class="btn btn-rounded"
                        @click="error = null"
                    >
                        Close
                    </button>
                </div>
            </transition>
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
        Info: require('./components/Info.vue'),
		Login: require('./components/Login.vue'),
        Projects: require('./components/Projects.vue'),
        Project: require('./components/Project.vue'),
        Export: require('./components/Export.vue')
	},
    data: function () {
        return {
            crumbs: [],
            screen: null,
            error: null
        };
    },
    methods: {
        openUserScreen: function () {
            console.log('open user');
            this.$store.commit('SET_LOADING', !this.$store.state.loading);
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
        },

        logIn: function (data) {
            var self = this;

            this.$store.dispatch('logIn', data).then(function () {
                self.screen = null;
                self.showProjects();
            }).catch(function (error) {
                self.error = self.getErrorMessage(error);
            });
        },
        showProjects: function () {
            var self = this;

            this.$store.dispatch('loadProjects').catch(function (error) {
                self.error = self.getErrorMessage(error);
            }).then(function () {
                self.screen = 'Projects';
            });
        },
        selectProject: function (project) {
            this.$store.commit('SET_PROJECT', project);
            this.screen = 'Project';
        },
        exportContent: function () {
            var self = this;

            this.screen = null;
            this.$store.dispatch('exportContent').then(function () {
                self.screen = 'Export';
            }).catch(function (error) {
                self.error = self.getErrorMessage(error);
                self.screen = 'Project';
            });
        },
        upload: function (data) {
            this.$store.dispatch('createJob', {
                data: this.$store.state.exportData,
                projectId: this.$store.state.project.id,
                language: data.language,
                filename: data.filename
            });
        }
    },
    created: function () {
        var self = this,
            savedSession = null;

        try {
            if (localStorage.memsourceSession) {
                savedSession = JSON.parse(localStorage.memsourceSession);
            }
        } catch (e) {
            console.warn(e);
        }

        if (savedSession) {
            this.$store.commit('SET_SESSION', savedSession);
            this.showProjects();
        } else {
            this.screen = 'Login';
        }

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

                if (!value) {
                    return; // don't add crumb when screen is set to null
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
                var user = (data && data.user);

                if (user) {
                    $buttonContent.html(data.user.firstName);
                }

                $button.css('display', (user) ? 'block' : 'none');
            }
        },
        "$store.getters.token": {
            immediate: true,
            handler: function (value, oldval) {
                if (value === null) {
                    this.screen = 'Login';
                }
            }
        }
    }
};
</script>