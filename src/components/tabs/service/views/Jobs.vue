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
      jobs: [],
      selectedJobs: [],
      confirmDelete: false
    }
  },
  computed: {
    projectId () {
      return this.$store.state.project.id
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
    loadJobs () {
      return this.$store.dispatch('memsource', {
        url: `/projects/${ this.projectId }/jobs`
      }).then(response => {
        this.jobs = response.data.content
        this.selectedJobs = []
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
      var importLanguage = this.$store.getters.availableLanguages.find(lang => {
        return job.targetLang.indexOf(lang.code) === 0
      })

      if (importLanguage) {
        this.$store.dispatch('memsource', {
          url: `/projects/${ this.projectId }/jobs/${ job.uid }/targetFile`
        }).then(response => {
          return this.$store.dispatch('outsource', {
            url: '/import',
            method: 'post',
            data: {
              language: importLanguage.code,
              content: response.data
            }
          })
        }).then(response => {
          this.$store.commit('ALERT', {
            theme: 'positive',
            text: `Successfully imported job in ${ importLanguage.name }!`
          })
        })
      } else {
        this.$store.commit('ALERT', {
          theme: 'negative',
          text: `Invalid site language: ${ job.targetLang }`
        })
      }
    },
    deleteJobs () {
      var jobs = this.selectedJobs

      this.$store.dispatch('memsource', {
        url: `/projects/${ this.projectId }/jobs/batch`,
        method: 'delete',
        data: {
          jobs: jobs.map(id => ({ uid: id }))
        }
      }).then(response => {
        return this.loadJobs()
      }).then(response => {
        this.$store.commit('ALERT', {
          theme: 'positive',
          text: `Deleted ${ jobs.length } jobs!`
        })
      })
    }
  },
  created () {
    this.loadJobs()
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
