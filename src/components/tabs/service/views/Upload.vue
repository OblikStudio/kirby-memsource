<template>
  <div>
    <section class="k-section">
      <k-grid v-if="stats" class="ms-stats">
        <k-column width="1/3" v-for="(value, name) in stats" :key="name">
          {{ name }}: <strong>{{ value }}</strong>
        </k-column>
      </k-grid>
    </section>

    <section class="k-section">
      <k-json-editor
        v-model="$store.state.export"
        label="Data"
      ></k-json-editor>
    </section>

    <k-grid class="k-section" gutter="medium">
      <k-column width="1/3">
        <header>
          <h2 class="k-headline">Job Name</h2>
        </header>

        <NameGen v-model="jobName" />
      </k-column>

      <k-column width="2/3">
        <header>
          <h2 class="k-headline">Target Languages</h2>
        </header>

        <k-input
          type="checkboxes"
          v-model="selectedLangs"
          :options="languageOptions"
          :required="true"
        />
      </k-column>
    </k-grid>

    <div class="ms-actions">
      <k-button
        class="ms-button"
        icon="download"
        @click="downloadExport"
      >
        Save as file
      </k-button>

      <k-button @click="upload" class="ms-button ms-t1" icon="upload">
        Upload
      </k-button>
    </div>
  </div>
</template>

<script>
import cloneDeep from 'lodash/cloneDeep'
import NameGen from '@/components/NameGen.vue'

function countObjectData (data) {
  var stats = {
    strings: 0,
    words: 0,
    chars: 0
  }

  for (let k in data) {
    let value = data[k]

    if (typeof value === 'object' && value !== null) {
      var childStats = countObjectData(value)

      for (let k in childStats) {
        stats[k] += childStats[k]
      }
    } else if (data.hasOwnProperty(k)) {
      value = value + ''

      stats.strings++
      stats.words += value.split(/\s+/).length
      stats.chars += value.length
    }
  }

  return stats
}

export default {
  inject: ['$alert'],
  components: {
    NameGen
  },
  data () {
    return {
      selectedLangs: [],
      jobName: null
    }
  },
  computed: {
    data () {
      return this.$store.state.export
    },
    project () {
      return this.$store.state.project
    },
    displayData () {
      var data = {}

      for (var k in this.data) {
        if (Object.keys(this.data[k]).length) {
          data[k] = this.data[k]
        }
      }

      return data
    },
    stats () {
      if (!this.data) {
        return null
      }

      var pages = this.data.pages && Object.keys(this.data.pages).length
      var files = this.data.files && Object.keys(this.data.files).length
      var stats = countObjectData(this.data)

      return {
        Pages: pages,
        Files: files,
        Variables: countObjectData(this.data.variables).strings,
        Strings: stats.strings,
        Words: stats.words,
        Characters: stats.chars
      }
    },
    languageOptions () {
      var targetLangs =  this.project.targetLangs

      return this.$store.getters.availableLanguages
        .filter(lang => !lang.default)
        .map(lang => {
          // Overwrite due to: https://forum.getkirby.com/t/language-locales-not-available-in-the-panel-front-end/14620
          var target = targetLangs.find(code => code.indexOf(lang.code) === 0)
          if (target) {
            lang.code = target
          }

          return {
            value: lang.code,
            text: `${ lang.name } (${ lang.code })`
          }
        })
    }
  },
  methods: {
    upload () {
      this.getImportSettings().then(settings => {
        this.$store.commit('ALERT', {
          text: `Using import settings: ${ settings.name }`
        })

        var filename = this.jobName + '.json'
        var memsourceHeader = {
          targetLangs: this.selectedLangs,
          importSettings: {
            uid: settings.uid
          }
        }

        return this.$store.dispatch('memsource', {
          url: `/projects/${ this.project.uid }/jobs`,
          method: 'post',
          headers: {
            'Memsource': JSON.stringify(memsourceHeader),
            'Content-Type': 'application/octet-stream',
            'Content-Disposition': `filename*=UTF-8''${ filename }`
          },
          data: this.data
        })
      }).then(response => {
        var jobs = (response.data && response.data.jobs)
        if (jobs && jobs.length) {
          this.$store.commit('ALERT', {
            theme: 'positive',
            text: `Successfully created ${ jobs.length } jobs!`
          })
        }
      }).catch(this.$alert)
    },
    getImportSettings () {
      return this.$store.dispatch('memsource', {
        url: '/importSettings'
      }).then(response => {
        var items = (response.data && response.data.content) || []
        var settings = items.find(item => item.name === 'k3-1')

        if (settings) {
          return this.$store.dispatch('memsource', {
            url: `/importSettings/${ settings.uid }`
          })
        } else {
          return this.$store.dispatch('memsource', {
            url: '/importSettings',
            method: 'post',
            data: {
              name: 'k3-1',
              fileImportSettings: {
                json: {
                  htmlSubFilter: true
                }
              }
            }
          })
        }
      }).then(response => {
        return Promise.resolve(response.data)
      })
    },
    downloadExport () {
      var part = JSON.stringify(this.data, null, 2)
      var blob = new Blob([part], {
        type: 'application/json'
      })

      var url = URL.createObjectURL(blob)
      var anchor = document.createElement('a')
      anchor.download = this.jobName
      anchor.href = url
      anchor.click()
    }
  },
  created () {
    var codes = this.languageOptions.map(lang => lang.value)
    this.selectedLangs = this.project.targetLangs.filter(code => {
      return codes.indexOf(code) >= 0
    })
  }
}
</script>

<style lang="scss" scoped>
/deep/ {
  .ms-stats {
    grid-column-gap: 1.5rem;
    grid-row-gap: 0.5rem;
  }

  .k-checkboxes-input {
    li {
      display: inline-block;
      width: 50%;
      margin-bottom: 0.75rem;
    }
  }
}

header {
  margin-bottom: 0.75rem;
}

.k-list-item {
  &:not(:hover) {
    .k-list-item-options {
      width: 0;
      overflow: hidden;
    }
  }
}
</style>
