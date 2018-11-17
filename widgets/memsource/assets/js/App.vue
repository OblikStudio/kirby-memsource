<style lang="scss">
@import 'main';

.memsource-widget {
    position: relative;

    .ms-view {
        display: flex;
        align-items: center;
        height: 15em;
        margin-bottom: 0.5em;
        overflow: auto;
    }

        .ms-view-wrapper {
            width: 100%;
            max-height: 100%;
        }

    .ms-alert {
        display: flex;
        align-items: center;
        width: 100%;
        height: 100%;
        position: absolute;
            top: 0;
            left: 0;

        text-align: center;
        background: rgba(#fff, 0.95);
        overflow: auto;
    }

        .ms-alert-wrapper {
            width: 100%;
            max-height: 100%;
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
}
</style>

<template>
    <div class="memsource-widget">
        <Crumbs :entries="crumbs" @click="handleCrumb"></Crumbs>

        <div class="ms-view">
            <div class="ms-view-wrapper">
                <transition name="fade" mode="out-in">
                    <component
                        :is="screen"
                        class="ms-screen"
                        @logIn="logIn"
                        @logOut="logOut"
                        @selectProject="selectProject"
                        @export="exportContent"
                        @upload="upload"
                        @listJobs="listJobs"
                        @openJob="openJob"
                        @import="importJob"
                    ></component>
                </transition>
            </div>
        </div>

        <transition name="fade">
            <div v-if="alerts.length" class="ms-alert">
                <div class="ms-alert-wrapper ms-wrapper">
                    <Info v-for="alert in alerts" :type="alert.type">
                        {{ alert.text }}
                    </Info>
                    <button
                        class="btn btn-rounded"
                        @click="alerts = []"
                    >
                        Close
                    </button>
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div v-if="$store.getters.isLoading" class="loading-overlay">
                <div class="loading-content">
                    &hellip;
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
var whenExpires = require('./modules/expires');
var $button = $('#memsource-widget h2 a')
var $buttonContent = $button.find('span');

const CRUMB_RESET_VIEWS = [
    'Login',
    'User'
];

module.exports = {
	components: {
        Login: require('./views/Login.vue'),
		User: require('./views/User.vue'),
        Projects: require('./views/Projects.vue'),
        Project: require('./views/Project.vue'),
        Export: require('./views/Export.vue'),
        Jobs: require('./views/Jobs.vue'),
        Import: require('./views/Import.vue'),

        Crumbs: require('./components/Crumbs.vue'),
        Info: require('./components/Info.vue')
	},
    data: function () {
        return {
            alerts: [],
            crumbs: [],
            screen: null
        };
    },
    methods: {
        loaderPromise: function (promise) {
            var self = this;
            this.$store.commit('MODIFY_LOADERS', 'add');

            promise.then(function (value) {
                self.$store.commit('MODIFY_LOADERS', 'remove');
                return Promise.resolve(value);
            }).catch(function (reason) {
                self.$store.commit('MODIFY_LOADERS', 'remove');
                return Promise.reject(reason);
            });
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

            this.loaderPromise(
                this.$store.dispatch('logIn', data).then(function () {
                    self.showProjects();
                }).catch(function (error) {
                    self.alerts.push({
                        type: 'error',
                        text: self.getErrorMessage(error)
                    });
                })
            );
        },
        logOut: function () {
            this.screen = 'Login';
            this.$store.dispatch('logOut');
        },
        showProjects: function () {
            var self = this;

            this.screen = null;
            this.loaderPromise(
                this.$store.dispatch('loadProjects').catch(function (error) {
                    self.alerts.push({
                        type: 'error',
                        text: self.getErrorMessage(error)
                    });
                }).then(function () {
                    self.screen = 'Projects';
                })
            ); 
        },
        selectProject: function (project) {
            this.$store.commit('SET_PROJECT', project);
            this.screen = 'Project';
        },
        exportContent: function () {
            var self = this;

            this.screen = null;
            this.loaderPromise(
                this.$store.dispatch('exportContent').then(function () {
                    self.screen = 'Export';
                }).catch(function (error) {
                    self.screen = 'Project';
                    self.alerts.push({
                        type: 'error',
                        text: self.getErrorMessage(error)
                    });
                })
            );
        },
        upload: function (data) {
            var self = this;

            this.loaderPromise(
                this.$store.dispatch('createJob', {
                    data: this.$store.state.pluginApi.exportData,
                    projectId: this.$store.state.project.id,
                    language: data.language,
                    filename: data.filename
                }).then(function (response) {
                    var jobs = (response.data && response.data.jobs);

                    if (jobs && jobs.length) {
                        self.alerts.push({
                            type: 'success',
                            text: 'Successfully created ' + self.plural(jobs.length, 'job') + '!'
                        });
                    }
                }).catch(function (error) {
                    self.alerts.push({
                        type: 'error',
                        text: self.getErrorMessage(error)
                    });
                })
            );
        },
        listJobs: function () {
            var self = this;

            this.screen = null;
            this.loaderPromise(
                this.$store.dispatch('listJobs', {
                    projectId: this.$store.state.project.id
                }).catch(function (error) {
                    self.alerts.push({
                        type: 'error',
                        text: self.getErrorMessage(error)
                    });
                }).then(function (response) {
                    self.screen = 'Jobs';
                })
            );
        },
        openJob: function (job) {
            this.$store.commit('SET_JOB', job);
            this.screen = 'Import';
        },
        importJob: function () {
            var self = this,
                job = this.$store.state.job,
                project = this.$store.state.project,
                availableLanguages = this.$store.getters.availableLanguages,
                jobLanguage = job.targetLang;

            var importLanguage = availableLanguages.find(function (lang) {
                return lang.locale === jobLanguage;
            });

            if (importLanguage) {
                // Convert language locale back to language code because
                // that's what Kirby uses internally in its API.
                importLanguage = importLanguage.code;
            } else {
                return self.alerts.push({
                    type: 'error',
                    text: 'Language "' + jobLanguage + '" not found!'
                });
            }

            this.loaderPromise(
                this.$store.dispatch('importJob', {
                    projectId: project.id,
                    jobId: job.uid,
                    language: importLanguage
                }).then(function (response) {
                    self.alerts.push({
                        type: 'success',
                        text: 'Successfully imported job!'
                    });
                }).catch(function (error) {
                    self.alerts.push({
                        type: 'error',
                        text: self.getErrorMessage(error)
                    });
                })
            );
        }
    },
    created: function () {
        if (this.$store.state.session) {
            this.showProjects();
        } else {
            this.screen = 'Login';
        }

        var self = this;
        $button.on('click', function (event) {
            event.preventDefault();

            if (self.screen !== 'User') {
                self.screen = 'User';
            } else {
                self.screen = 'Projects';
            }
        });
    },
    watch: {
        screen: {
            immediate: true,
            handler: function (value, oldValue) {
                var crumbId = value,
                    crumbText = value;

                if (
                    CRUMB_RESET_VIEWS.indexOf(value) >= 0 ||
                    CRUMB_RESET_VIEWS.indexOf(oldValue) >= 0
                ) {
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
        "$store.state.session.expires": {
            immediate: true,
            handler: function (value) {
                var self = this;

                if (value) {
                    whenExpires('session', value).then(function () {
                        self.screen = 'Login';

                        self.$store.dispatch('logOut').then(function () {
                            self.alerts.push({
                                type: 'info',
                                text: 'Your session expired, please log in again.'
                            });
                        }); 
                    });
                }
            }
        }
    }
};
</script>