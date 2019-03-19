<template>
  <div>
    <k-view>
      <k-header>
        <Crumbs :entries="crumbs" @click="openCrumb"></Crumbs>
      </k-header>

      <component
        :is="screen"
        @loggedIn="showProjects"
        @upload="uploadToProject"
        @export="exportSite"
        @uploadJobs="uploadJobs"
        @download="listJobs"
        @importJobs="importJobs"
      ></component>
    </k-view>

    <transition name="slide">
      <div v-if="alerts.length" class="ms-alerts-wrapper">
        <div class="ms-alerts-pad">
          <div class="ms-alerts">
            <k-box
              v-for="alert in alerts"
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

const CRUMB_RESET_VIEWS = [
  'Login'
]

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
    Jobs
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
        this.screen = crumb.value
      }
    },
    showProjects () {
      this.$store.dispatch('loadProjects').catch(function (error) {
        console.log(error)
      }).then(() => {
        this.screen = 'Projects'
      })
    },
    uploadToProject (data) {
      this.$store.commit('SET_PROJECT', data)
      this.screen = 'Export'
    },
    exportSite (options) {
      this.$store.dispatch('exportContent', options).then(data => {
        console.log('exported', data)
        this.screen = 'Upload'
      }).catch(err => {
        console.log(err.response || err)
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
      console.log('Listing jobs')

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
      console.log('import jobs', jobIds)
    }
  },
  beforeCreate () {
    var Vuex = this.$root.constructor._installedPlugins.find(entry => !!entry.Store)
    this.$store = require('./store')(Vuex)
  },
  created: function () {
    console.log('root', this.$root)
    if (this.$store.state.session) {
      this.showProjects()
    } else {
      this.screen = 'Login'
    }

    axios({
      url: panel.api + '/memsource/langs',
      headers: {
        'X-CSRF': panel.csrf
      }
    }).then(response => {
      this.$store.commit('SET_LANGUAGES', response.data)
    })
  },
  watch: {
    screen: {
      immediate: true,
      handler: function (value, oldValue) {
        var crumbId = value
        var crumbText = value

        if (
          CRUMB_RESET_VIEWS.indexOf(value) >= 0 ||
          CRUMB_RESET_VIEWS.indexOf(oldValue) >= 0
        ) {
          this.crumbs = []
        }

        if (!value) {
          return // don't add crumb when screen is set to null
        }

        if (value === 'Project') {
          crumbText = this.$store.state.project.name
        }

        this.crumbs.push({
          value: crumbId,
          text: crumbText
        })
      }
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

/deep/ {
  .ms-button {
    display: flex;
    align-items: center;
    margin: 2rem auto 0;
    padding: 0.75rem 1.5rem;
    border-radius: 2px;
    color: white;

    &.ms-t1 {
      background: #4271ae;
    }

    &.ms-t2 {
      background: #5d800d;
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
