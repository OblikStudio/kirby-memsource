<template>
  <div class="k-fieldset" v-if="jobs.length">
    <k-grid>
      <k-column>
        <label class="k-field-label">
          Filter Jobs
        </label>

        <k-input
          theme="field"
          type="text"
          v-model="query"
          placeholder="Query"
        />
      </k-column>

    <k-column>
      <template v-if="filteredJobs.length">
        <k-button
          class="ms-select-all"
          icon="check"
          @click="toggleSelected"
        ></k-button>

        <div class="k-list">
          <div v-for="job in filteredJobs" class="k-list-item" :key="job.uid">
            <div class="k-list-item-image">
              <span class="k-icon" data-back="black" title="Target language">
                <strong>{{ job.targetLang }}</strong>
              </span>
            </div>
            
            <p class="k-list-item-text" :title="(new Date(job.dateCreated)).toLocaleString()">
              <em>{{ job.filename }}</em>
              <small>{{ job.status }}</small>
            </p>

            <div class="k-list-item-options">
              <label :for="job.uid" class="k-button">
                <input :id="job.uid" v-model="selectedJobs" :value="job.uid" type="checkbox"/>
              </label>
            </div>
          </div>
        </div>
      </template>

      <p v-if="filteredJobs.length < jobs.length" class="ms-info">
        <strong>{{ jobs.length - filteredJobs.length }} hidden jobs</strong>
      </p>
    </k-column>

    <k-column class="ms-actions" v-if="selectedJobs.length">
      <k-button
        icon="trash"
        :theme="confirmDelete ? 'negative' : null"
        @click="deleteHandler"
      >
        {{ confirmDelete ? 'Delete?!' : 'Delete' }}
      </k-button>

      <k-button
        class="ms-button ms-t2"
        icon="download"
        @click="importHandler"
      >
        Import {{ selectedJobs.length }} jobs
      </k-button>
    </k-column>

  </k-grid>
  </div>
  <p v-else class="ms-info">
    <strong>No jobs in this project</strong>
  </p>
</template>

<script>
export default {
  data () {
    return {
      query: null,
      selectedJobs: [],
      confirmDelete: false
    }
  },
  computed: {
    jobs () {
      this.selectedJobs = []
      return this.$store.state.memsource.jobs
    },
    filteredJobs () {
      return this.jobs.filter(job => {
        var matches = (!this.query || job.filename.indexOf(this.query) >= 0)
        var selectedIndex = this.selectedJobs.indexOf(job.uid)
        if (selectedIndex >= 0 && !matches) {
          this.selectedJobs.splice(selectedIndex, 1)
        }

        return matches
      })
    }
  },
  methods: {
    isValidLanguage (input, target) {
      input = input.toLowerCase()
      target = target.toLowerCase()

      // allow imports of en in en_us and vice-versa
      return (target.indexOf(input) === 0 || input.indexOf(target) === 0)
    },
    toggleSelected () {
      if (this.selectedJobs.length !== this.filteredJobs.length) {
        this.selectedJobs = this.filteredJobs.map(job => job.uid)
      } else {
        this.selectedJobs = []
      }
    },
    importHandler () {
      this.jobs.forEach(job => {
        if (this.selectedJobs.indexOf(job.uid) >= 0) {
          this.importJob(job)
        }
      })
    },
    deleteHandler () {
      if (this.confirmDelete) {
        this.deleteJobs()
      } else {
        this.confirmDelete = true
        setTimeout(() => {
          this.confirmDelete = false
        }, 1500)
      }
    },
    importJob (job) {
      var project = this.$store.state.project
      var languages = this.$store.getters.availableLanguages
      var jobLanguage = job.targetLang
      var importLanguage = languages.find(lang => this.isValidLanguage(jobLanguage, lang.code))

      if (!importLanguage) {
        return this.$store.commit('ALERT', {
          type: 'negative',
          text: `Could not import job ${ job.uid }, language ${ jobLanguage } is not valid.`
        })
      }

      this.$store.dispatch('downloadJob', {
        projectId: project.id,
        jobId: job.uid
      }).then(response => {
        return this.$store.dispatch('importContent', {
          language: importLanguage.code,
          content: response.data
        })
      }).then(response => {
        this.$store.commit('ALERT', {
          type: 'positive',
          text: `Successfully imported job in ${ importLanguage.name }!`
        })
      }).catch(error => {
        this.$store.commit('ALERT', {
          type: 'negative',
          data: error
        })
      })
    },
    deleteJobs (jobIds) {
      var jobs = this.selectedJobs
      var project = this.$store.state.project

      this.$store.dispatch('deleteJobs', {
        projectId: project.id,
        jobIds: jobs
      }).then(response => {
        return this.$store.dispatch('listJobs', {
          projectId: project.id
        })
      }).then(response => {
        this.$store.commit('ALERT', {
          type: 'positive',
          text: `Deleted ${ jobs.length } jobs!`
        })
      }).catch(error => {
        this.$store.commit('ALERT', {
          type: 'negative',
          data: error
        })
      })
    }
  }
}
</script>

<style lang="scss" scoped>
label {
  display: flex;
  align-items: center;

  &.k-button,
  & input {
    cursor: pointer;
  }
}

.ms-select-all {
  display: block;
  margin-top: -20px;
  margin-left: auto;
  padding: 11px;
}

.ms-info {
  margin: 1rem 0;
  text-align: center;
}

.k-list-item-text small {
  margin-right: -10px;
}

.k-list-item-image {
  width: 64px;

  .k-icon {
    width: auto;
  }
}

.ms-delete {
  display: block;
  margin: 0.75rem auto;
  opacity: 0.6;
}
</style>
