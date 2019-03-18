<template>
  <div>
    <k-view>
      <k-header>Memsource
        <span @click="alerts.push({type: 'info', text: 'foobar'})">test</span>
      </k-header>
      <component
        :is="screen"
        @loggedIn="showProjects"
        @upload="uploadToProject"
        @export="exportSite"
        @uploadJobs="uploadJobs"
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

import Login from './views/Login.vue'
import Projects from './views/Projects.vue'
import Export from './views/Export.vue'
import Upload from './views/Upload.vue'

export default {
  mixins: [
    mixin
  ],
  components: {
    Login,
    Projects,
    Export,
    Upload
  },
  data () {
    return {
      screen: null,
      alerts: [{
        text: 'foo',
        type: 'warning'
      },{
        text: 'foofa',
        type: 'positive'
      }]
    }
  },
  methods: {
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
