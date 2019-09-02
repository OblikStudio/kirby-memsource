<template>
  <div>
    <component
      :is="$store.getters.view"
      @upload="uploadToProject"
      @export="exportSite"
      @uploadJobs="uploadJobs"
      @listJobs="listJobs"
      @importJobs="importJobs"
      @deleteJobs="deleteJobs"
      @logOut="logOut"
    ></component>
  </div>
</template>

<script>
import axios from 'axios'
import Projects from './views/Projects.vue'
import Export from './views/Export.vue'
import Upload from './views/Upload.vue'
import Jobs from './views/Jobs.vue'

export default {
  components: {
    Projects,
    Export,
    Upload,
    Jobs
  },
  methods: {
    isValidLanguage (input, target) {
      input = input.toLowerCase()
      target = target.toLowerCase()

      // allow imports of en in en_us and vice-versa
      return (target.indexOf(input) === 0 || input.indexOf(target) === 0)
    },
    alert (data) {
      this.$store.commit('ALERT', data)
    },
    showProjects () {
      this.$store.commit('VIEW', 'Projects')
      this.$store.dispatch('loadProjects').catch(error => {
        this.alert({
          type: 'negative',
          data: error
        })
      })
    },
    uploadToProject (project) {
      this.$store.commit('SET_PROJECT', project)
      this.$store.commit('VIEW', 'Export')
    },
    exportSite (options) {
      this.$store.dispatch('exportContent', options).then(response => {
        this.$store.commit('SET_EXPORT_DATA', response.data)
        this.$store.commit('VIEW', 'Upload')
      }).catch(error => {
        this.alert({
          type: 'negative',
          data: error
        })
      })
    },
    uploadJobs (options) {
      this.$store.dispatch('fetchImportSettings').then(settings => {
        this.alert({
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
          this.alert({
            type: 'positive',
            text: `Successfully created ${ jobs.length } jobs!`
          })
        }
      }).catch(error => {
        this.alert({
          type: 'negative',
          data: error
        })
      })
    },
    listJobs (project) {
      this.$store.commit('SET_PROJECT', project)
      this.$store.dispatch('listJobs', {
        projectId: this.$store.state.project.id
      }).catch(error => {
        this.alert({
          type: 'error',
          data: error
        })
      }).then(response => {
        this.$store.commit('VIEW', 'Jobs')
      })
    },
    importJobs (jobIds) {
      this.$store.state.memsource.jobs.forEach(job => {
        if (jobIds.indexOf(job.uid) >= 0) {
          this.importJob(job)
        }
      })
    },
    importJob (job) {
      var project = this.$store.state.project
      var languages = this.$store.getters.availableLanguages
      var jobLanguage = job.targetLang
      var importLanguage = languages.find(lang => this.isValidLanguage(jobLanguage, lang.code))

      if (!importLanguage) {
        return this.alert({
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
        this.alert({
          type: 'positive',
          text: `Successfully imported job in ${ importLanguage.name }!`
        })
      }).catch(error => {
        this.alert({
          type: 'negative',
          data: error
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
        this.alert({
          type: 'positive',
          text: `Deleted ${ jobIds.length } jobs!`
        })
      }).catch(error => {
        this.alert({
          type: 'negative',
          data: error
        })
      })
    }
  },
  created: function () {
    this.showProjects()
  }
}
</script>

