<template>
  <div>
    <ul v-if="stats" class="ms-stats">
      <li v-for="(value, name) in stats">
        {{ name }}: <strong>{{ value }}</strong>
      </li>
    </ul>

    <div class="ms-export-data">
      <section v-for="(group, key) in displayData" class="k-section k-list">
        <header>
          <h2 class="k-headline">
            {{ key }}
          </h2>
        </header>

        <k-grid>
          <k-column width="1/3" v-for="(item, key) in group" class="k-list-item">
            <p class="k-list-item-text">{{ key }}</p>

            <div class="k-list-item-options">
              <k-button @click="$delete(group, key)" icon="trash" alt="Delete"></k-button>
            </div>
          </k-column>
        </k-grid>
      </section>
    </div>

    <section class="k-section">
      <header>
        <h2 class="k-headline">Target Languages</h2>
      </header>

      <k-input
        type="checkboxes"
        v-model="selectedLangs"
        :options="languageOptions"
        :required="true"
      />

      <k-button icon="check" @click="toggleLanguages">
        Toggle all
      </k-button>
    </section>

    <section class="k-section">
      <header>
        <h2 class="k-headline">Job Name</h2>
      </header>

      <k-input
        theme="field"
        type="text"
        v-model="jobName"
        :required="true"
      />

      <k-button class="k-field-help" icon="refresh" @click="generateName">
        Generate
      </k-button>
    </section>

    <k-button @click="upload" class="ms-button ms-t1" icon="upload">
      Upload
    </k-button>
  </div>
</template>

<script>
import dateFormat from 'dateformat'
import cloneDeep from 'lodash/cloneDeep'

import Wordgen from '../modules/wordgen'

var wordgen = new Wordgen({
  length: 6
})

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
  data () {
    return {
      selectedLangs: [],
      jobName: null
    }
  },
  computed: {
    data () {
      return this.$store.state.exporter.exportData
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
      if (!this.data || !this.data.pages) {
        return null
      }

      var pages = Object.keys(this.data.pages).length
      var files = Object.keys(this.data.files).length
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
      return this.$store.state.languages
        .filter(lang => !lang.isDefault)
        .map(lang => {
          return {
            value: lang.locale,
            text: `${ lang.name } (${ lang.locale })`
          }
        })
    }
  },
  methods: {
    toggleLanguages () {
      var hasUntoggled = !!this.languageOptions.find(
        option => this.selectedLangs.indexOf(option.value) < 0
      )

      if (hasUntoggled) {
        this.selectedLangs = this.languageOptions.map(lang => lang.value)
      } else {
        this.selectedLangs = []
      }
    },
    generateName () {
      this.jobName = (wordgen.generate() + dateFormat(new Date(), `-mmm-dd`)).toLowerCase()
    },
    upload () {
      this.$emit('uploadJobs', {
        languages: this.selectedLangs,
        jobName: this.jobName
      })
    }
  },
  created () {
    this.toggleLanguages()
    this.generateName()
  }
}
</script>

<style lang="scss" scoped>
.k-section/deep/ {
  .k-grid {
    margin-bottom: -2px;
    margin-right: -2px;
  }

    .k-list-item {
      margin-bottom: 2px;
      margin-right: 2px;
    }
}

/deep/ {
  .ms-stats {
    margin-bottom: 1rem;

    li {
      display: inline-block;
      width: 33.33%;
      margin-bottom: 0.5rem;
    }
  }

  .k-checkboxes-input {
    li {
      display: inline-block;
      width: 33.33%;
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

.ms-export-data {
  margin-bottom: 2rem;
}
</style>
