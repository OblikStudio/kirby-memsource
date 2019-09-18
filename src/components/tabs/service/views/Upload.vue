<template>
  <div>
    <section class="k-section">
      <Stats :data="stats" />
    </section>

    <section class="k-section">
      <k-json-editor
        v-model="$store.state.export"
        :label="$t('data')"
      ></k-json-editor>
    </section>

    <k-grid class="k-section" gutter="medium">
      <k-column width="1/2">
        <NameGen
          v-model="jobName"
          :label="$t('memsource.label.job')"
        />
      </k-column>

      <k-column width="1/2">
        <k-checkboxes-field
          type="checkboxes"
          v-model="selectedLangs"
          :options="languageOptions"
          :label="$t('memsource.label.target_langs')"
        />
      </k-column>
    </k-grid>

    <k-button-group align="center">
      <k-button icon="download" @click="downloadExport">
        {{ $t('file') }}
      </k-button>

      <k-button theme="positive" icon="upload" @click="upload">
        {{ $t('upload') }}
      </k-button>
    </k-button-group>
  </div>
</template>

<script>
import Stats from '@/components/Stats.vue'
import NameGen from '@/components/NameGen.vue'

const IMPORT_SETTINGS = {
  name: 'kirby-2.0.0',
  fileImportSettings: {
    inputCharset: 'UTF-8',
    outputCharset: 'UTF-8',
    json: {
      htmlSubFilter: true,
      includeKeyRegexp: '.*(?<!/id)$'
    }
  }
}

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
      stats.chars += value.length

      if (value.length) {
        stats.words += value.trim().split(/\s+/).length
      }
    }
  }

  return stats
}

export default {
  inject: ['$alert'],
  components: {
    Stats,
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
    stats () {
      if (!this.data) {
        return null
      }

      var pages = this.data.pages && Object.keys(this.data.pages).length
      var files = this.data.files && Object.keys(this.data.files).length
      var variableStats = countObjectData(this.data.variables)
      var stats = countObjectData(this.data)

      return [
        { title: this.$t('pages'), content: pages || 0 },
        { title: this.$t('files'), content: files || 0 },
        { title: this.$t('variables'), content: variableStats.strings },
        { title: this.$t('strings'), content: stats.strings },
        { title: this.$t('words'), content: stats.words },
        { title: this.$t('characters'), content: stats.chars }
      ]
    },
    languageOptions () {
      var targetLangs =  this.project.targetLangs

      return this.$store.getters.availableLanguages
        .filter(lang => !lang.default)
        .map(lang => {
          /**
           *  Overwrite because site locales can't be used
           *  @see https://github.com/getkirby/kirby/issues/2054
          */
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
          this.$alert(this.$t('memsource.info.created_jobs', { count: jobs.length }), 'positive')
        }
      }).catch(this.$alert)
    },
    getImportSettings () {
      return this.$store.dispatch('memsource', {
        url: '/importSettings'
      }).then(response => {
        var items = (response.data && response.data.content) || []
        var settings = items.find(item => item.name === IMPORT_SETTINGS.name)

        if (settings) {
          return this.$store.dispatch('memsource', {
            url: `/importSettings/${ settings.uid }`
          }).then(response => {
            return Promise.resolve(response.data)
          })
        } else {
          return this.$store.dispatch('memsource', {
            url: '/importSettings',
            method: 'post',
            data: IMPORT_SETTINGS
          }).then(response => {
            this.$alert(this.$t('memsource.info.created_settings', { name: IMPORT_SETTINGS.name }))
            return Promise.resolve(response.data)
          })
        }
      }).then(settings => {
        var name = settings.name
        var date = settings.dateCreated

        if (name && date) {
          this.$alert(this.$t('memsource.info.using_settings', {
            name,
            date: (new Date(date)).toLocaleString()
          }))

          return Promise.resolve(settings)
        } else {
          return Promise.reject(new Error(this.$t('memsource.info.invalid_settings')))
        }
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
