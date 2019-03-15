<template>
  <div>
    <ul v-if="stats" class="stats">
      <li v-for="(value, name) in stats">
        {{ name }}: <strong>{{ value }}</strong>
      </li>
    </ul>

    <section v-for="(group, key) in data" class="k-section k-list">
      <header>
        <h2 class="k-headline">{{ key }}</h2>
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

    <k-input
      type="checkboxes"
      v-model="value"
      :options="[
          { value: 'a', text: 'Option A' },
          { value: 'b', text: 'Option B' },
          { value: 'c', text: 'Option c' },
          { value: 'd', text: 'Option d' }
      ]"
      :required="true"
      :min="2"
      :max="5"
      :columns="5"
    />
  </div>
</template>

<script>
import cloneDeep from 'lodash/cloneDeep'

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
  computed: {
    data () {
      return this.$store.state.exporter.exportData
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
    }
  },
  methods: {
    submit () {
      this.$emit('export', {
        page: this.page || null,
        variables: this.variables
      })
    },
    deletePage (key) {
      this.$delete(this.data.pages, key)
    }
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
  .stats {
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
      margin-bottom: 1rem;
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
