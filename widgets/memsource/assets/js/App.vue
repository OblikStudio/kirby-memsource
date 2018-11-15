<style lang="scss">
@import 'vars';
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
		Login: require('./views/Login.vue'),
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
        logIn: function (data) {
            var self = this;

            this.$store.dispatch('logIn', data).then(function () {
                self.showProjects();
            }).catch(function (error) {
                self.alerts.push({
                    type: 'error',
                    text: self.getErrorMessage(error)
                });
            });
        },
        showProjects: function () {
            var self = this;

            this.screen = null;
            this.$store.dispatch('loadProjects').catch(function (error) {
                self.alerts.push({
                    type: 'error',
                    text: self.getErrorMessage(error)
                });
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
                self.screen = 'Project';
                self.alerts.push({
                    type: 'error',
                    text: self.getErrorMessage(error)
                });
            });
        },
        upload: function (data) {
            var self = this;

            this.$store.dispatch('createJob', {
                data: this.$store.state.pluginApi.exportData,
                projectId: this.$store.state.project.id,
                language: data.language,
                filename: data.filename
            }).then(function (response) {
                var data = response.data,
                    jobs = (data && data.jobs);

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
            });
        },
        listJobs: function () {
            var self = this;

            this.screen = null;
            this.$store.dispatch('listJobs', {
                projectId: this.$store.state.project.id
            }).catch(function (error) {
                self.alerts.push({
                    type: 'error',
                    text: self.getErrorMessage(error)
                });
            }).then(function (response) {
                self.screen = 'Jobs';
            });
        },
        openJob: function (job) {
            this.$store.commit('SET_JOB', job);
            this.screen = 'Import';
        },
        importJob: function () {
            var self = this;

            this.$store.dispatch('importJob', {
                projectId: this.$store.state.project.id,
                jobId: this.$store.state.job.uid,
                language: this.$store.state.job.targetLang
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
            });
        },

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
        "$store.getters.token": function (value, oldval) {
            if (typeof value !== 'string') {
                this.alerts.push({
                    type: 'info',
                    text: 'Your session expired, please log in again.'
                });
                this.screen = 'Login';
            }
        }
    }
};
</script>