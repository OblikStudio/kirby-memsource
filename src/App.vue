<template>
  <k-view>
    <k-header>Memsource</k-header>
    <component
      :is="screen"
      @loggedIn="showProjects"
      @upload="uploadToProject"
      @export="exportSite"
    ></component>
  </k-view>
</template>

<script>
import axios from 'axios'
import whenExpired from 'when-expired'

import store from './store'
import Login from './views/Login.vue'
import Projects from './views/Projects.vue'
import Export from './views/Export.vue'
import Upload from './views/Upload.vue'

export default {
  store,
  components: {
    Login,
    Projects,
    Export,
    Upload
  },
  data () {
    return {
      screen: 'Export'
    }
  },
  methods: {
    showProjects () {
      this.$store.dispatch('loadProjects').catch(function (error) {
        console.log(error)
      }).then(() => {
        console.log('projects')
        this.screen = 'Export'
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
        console.log(err.response.data)
      })
    }
  },
  created: function () {
    if (this.$store.state.session) {
      // this.showProjects()
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
.k-view {
  max-width: 50rem;
}
</style>
