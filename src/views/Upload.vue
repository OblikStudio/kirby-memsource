<template>
  <div>
    <ul v-if="stats" class="stats">
      <li v-for="(value, name) in stats">
        {{ name }}: <strong>{{ value }}</strong>
      </li>
    </ul>

    <div v-if="data && data.pages" class="k-list">
      <div v-for="(page, key) in data.pages" class="k-list-item">
        <p class="k-list-item-text">
          {{ key }}
        </p>

        <k-button @click="deletePage(key)" icon="trash" alt="Delete"></k-button>
      </div>
    </div>

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
  data () {
    return {
      page: null,
      variables: true,
      value: 'a'
    }
  },
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
      // var clone = cloneDeep(this.data)
      // delete clone.pages[key]
      // this.$store.commit('SET_EXPORT_DATA', clone)
    }
  }
}
</script>

<style lang="scss" scoped>
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
</style>
