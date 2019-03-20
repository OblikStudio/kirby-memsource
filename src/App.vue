<template>
  <div>
    <k-view>
      <k-header class="ms-header">
        <Crumbs :entries="crumbs" @click="openCrumb"></Crumbs>

        <k-button
          v-if="screen === 'User'"
          icon="parent"
          @click="previousScreen"
        >
          Back
        </k-button>
        <k-button
          v-else-if="$store.getters.user"
          icon="user"
          @click="screen = 'User'"
        >
          {{ $store.getters.user.firstName }}
        </k-button>
      </k-header>

      <component
        :is="screen"
        @logIn="logIn"
        @upload="uploadToProject"
        @export="exportSite"
        @uploadJobs="uploadJobs"
        @listJobs="listJobs"
        @importJobs="importJobs"
        @deleteJobs="deleteJobs"
        @logOut="logOut"
      ></component>
    </k-view>

    <transition name="slide">
      <div v-if="alerts.length" class="ms-alerts-wrapper">
        <div class="ms-alerts-pad">
          <div class="ms-alerts">
            <k-box
              v-for="(alert, index) in alerts"
              :key="index"
              :text="getError(alert.text)"
              :theme="alert.type"
            />

            <k-button @click="alerts = []" icon="check">
              Close
            </k-button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from 'axios'
import whenExpired from 'when-expired'

import mixin from './mixins/main'

import Crumbs from './comps/Crumbs.vue'
import Login from './views/Login.vue'
import Projects from './views/Projects.vue'
import Export from './views/Export.vue'
import Upload from './views/Upload.vue'
import Jobs from './views/Jobs.vue'
import User from './views/User.vue'

export default {
  mixins: [
    mixin
  ],
  components: {
    Crumbs,
    Login,
    Projects,
    Export,
    Upload,
    Jobs,
    User
  },
  data () {
    return {
      screen: null,
      alerts: [],
      crumbs: []
    }
  },
  methods: {
    openCrumb: function (crumb) {
      if (crumb.value !== this.screen) {
        this.crumbs.splice(this.crumbs.indexOf(crumb))

        if (crumb.value) {
          this.screen = crumb.value
        } else if (this.crumbs.length) {
          this.openCrumb(this.crumbs[this.crumbs.length - 1])
        }
      }
    },
    previousScreen: function () {
      var crumb = this.crumbs[this.crumbs.length - 2]
      if (crumb) {
        this.openCrumb(crumb)
      }
    },
    logIn (credentials) {
      this.$store.dispatch('logIn', credentials).then(() => {
        this.showProjects()
      }).catch(error => {
        this.alerts.push({
          type: 'negative',
          text: error
        })
      })
    },
    showProjects () {
      this.screen = 'Projects'
      this.$store.dispatch('loadProjects').catch(function (error) {
        this.alerts.push({
          type: 'negative',
          text: error
        })
      })
    },
    uploadToProject (project) {
      this.$store.commit('SET_PROJECT', project)
      this.screen = 'Export'
    },
    exportSite (options) {
      this.$store.dispatch('exportContent', options).then(response => {
        this.$store.commit('SET_EXPORT_DATA', response.data)
        this.screen = 'Upload'
      }).catch(error => {
        this.alerts.push({
          type: 'negative',
          text: error
        })
      })
    },
    uploadJobs (options) {
      this.$store.dispatch('fetchImportSettings').then(settings => {
        this.alerts.push({
          type: 'info',
          text: `Using import settings: ${ settings.name }`
        })

        return this.$store.dispatch('createJob', {
          data: this.$store.state.exporter.exportData,
          projectId: this.$store.state.project.uid,
          importSettingsId: settings.uid,
          languages: options.languages,
          name: options.jobName
        })
      }).then(response => {
        var jobs = (response.data && response.data.jobs)

        if (jobs && jobs.length) {
          this.alerts.push({
            type: 'positive',
            text: `Successfully created ${ jobs.length } jobs!`
          })
        }
      }).catch(error => {
        this.alerts.push({
          type: 'negative',
          text: error
        })
      })
    },
    listJobs (project) {
      this.$store.commit('SET_PROJECT', project)
      this.$store.dispatch('listJobs', {
        projectId: this.$store.state.project.id
      }).catch(error => {
        this.alerts.push({
          type: 'error',
          text: error
        })
      }).then(response => {
        this.screen = 'Jobs'
      })
    },
    importJobs (jobIds) {
      this.$store.state.memsource.jobs.forEach(job => {
        if (jobIds.indexOf(job.uid) >= 0) {
          this.importJob(job)
        }
      })
    },
    importJob: function (job) {
      var project = this.$store.state.project
      var languages = this.$store.getters.availableLanguages
      var jobLanguage = job.targetLang
      var importLanguage = languages.find(lang => this.isValidLanguage(jobLanguage, lang.code))

      if (!importLanguage) {
        return this.alerts.push({
          type: 'negative',
          text: `Could not import job ${ job.uid }, language ${ jobLanguage } is not valid.`
        })
      }

      console.log('import job', job)
      this.$store.dispatch('downloadJob', {
        projectId: project.id,
        jobId: job.uid
      }).then(response => {
        return this.$store.dispatch('importContent', {
          language: importLanguage.code,
          content: response.data
        })
      }).then(response => {
        console.log('imported', response)
        this.alerts.push({
          type: 'positive',
          text: `Successfully imported job in ${ importLanguage.name }!`
        })
      }).catch(error => {
        this.alerts.push({
          type: 'negative',
          text: error
        })
      })
    },
    deleteJobs: function (jobIds) {
      var project = this.$store.state.project

      this.$store.dispatch('deleteJobs', {
        projectId: project.id,
        jobIds
      }).then(response => {
        return this.$store.dispatch('listJobs', {
          projectId: project.id
        })
      }).then(response => {
        this.alerts.push({
          type: 'positive',
          text: `Deleted ${ jobIds.length } jobs!`
        })
      }).catch(error => {
        this.alerts.push({
          type: 'negative',
          text: error
        })
      })
    },
    logOut: function () {
      this.screen = 'Login'
      this.$store.dispatch('logOut')
    }
  },
  beforeCreate () {
    var Vuex = this.$root.constructor._installedPlugins.find(entry => !!entry.Store)
    this.$store = require('./store')(Vuex, this.$root.$store)
  },
  created: function () {
    if (this.$store.state.session) {
      this.showProjects()
    } else {
      this.screen = 'Login'
    }
  },
  watch: {
    screen (value, oldValue) {
      var text = value

      if (value === 'Login' || oldValue === 'Login') {
        this.crumbs = []
      }

      if (oldValue === 'Projects' && this.$store.state.project) {
        this.crumbs.push({
          value: null,
          text: this.$store.state.project.name
        })
      }

      this.crumbs.push({
        value,
        text
      })
    },
    "$store.state.session.expires": {
      immediate: true,
      handler: value => {
        if (value) {
          whenExpired('session', value).then(() => {
            this.screen = 'Login'
            this.$store.dispatch('logOut').then(() => {
              console.log('session expired!')
            }) 
          })
        }
      }
    }
  }
}
</script>

<style lang="scss" scoped>
$easeOutCubic: cubic-bezier(0.215, 0.61, 0.355, 1);

.ms-header {
  &/deep/ {
    .k-headline {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .k-button {
      margin-bottom: -0.5rem;
    }
  }
}

/deep/ {
  .ms-button {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    border-radius: 2px;

    background: #16171a;
    color: white;

    &.ms-t1 {
      background: #4271ae;
    }

    &.ms-t2 {
      background: #5d800d;
    }
  }

  .ms-actions {
    display: flex;
    align-items: center;
    justify-content: center;

    button {
      min-width: 10em;

      & + button {
        margin-left: 2rem;
      }
    }
  }
}

.k-view {
  max-width: 50rem;
}

.ms-alerts-wrapper {
  width: 100%;
  padding-top: 6px; // for shadow
  position: fixed;
    bottom: 0;
    left: 0; 
  transition: all 0.3s ease;
  overflow: hidden;

  &/deep/ {
    .k-button {
      display: block;
      margin: 1rem auto;
    }
  }
}

  .ms-alerts-pad {
    padding: 0.1px 0;
    background: #f6f6f6;
    box-shadow: 0 0 5px 0 rgba(#000, 0.1);
    transition: transform 0.3s $easeOutCubic;
  }

  .ms-alerts {
    max-width: 25em;
    margin: 2rem auto;

    &/deep/ {
      .k-box {
        margin-bottom: 0.5rem;
      }
    }
  }

.slide-enter,
.slide-leave-to {
  .ms-alerts-pad {
    transform: translateY(100%);
  }
}
</style>
