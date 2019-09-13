<template>
  <div>
    <div class="ms-diff" v-for="entry in entries" :key="entry.name">
      <p>{{ entry.name }}</p>
      <CodeDiff
        :old-string="entry.oldValue"
        :new-string="entry.newValue"
        outputFormat="side-by-side"
      />
    </div>
  </div>
</template>

<script>
import CodeDiff from 'vue-code-diff'

function sanitize (value) {
  if (value === null || value === undefined) {
    value = ''
  } else if (typeof value === 'object') {
    value = JSON.stringify(value, null, 4)
  } else if (typeof value !== 'string') {
    value += ''
  }

  return value
}

function getDiffEntries (data, prefix = null) {
  var entries = []

  for (let k in data) {
    let entry = data[k]
    let name = prefix ? `${ prefix }/${ k }` : k

    if (entry) {
      let oldValue = entry.$old
      let newValue = entry.$new

      if (typeof oldValue !== 'undefined' && typeof newValue !== 'undefined') {
        entries.push({
          name,
          oldValue: sanitize(oldValue),
          newValue: sanitize(newValue)
        })
      } else if (typeof entry === 'object') {
        entries = entries.concat(getDiffEntries(entry, name))
      }
    }
  }

  return entries
}

export default {
  props: {
    data: Object
  },
  components: {
    CodeDiff
  },
  computed: {
    entries () {
      return getDiffEntries(this.data)
    }
  }
}
</script>

<style lang="scss" scoped>
.ms-diff {
  margin-bottom: 1rem;

  p {
    font-family: monospace;
    margin-left: 11px;
    margin-bottom: 0.3rem;
  }
}

/deep/ {
  .d2h-file-wrapper {
    margin: 0;
    overflow: hidden;
    background: white;
    border-radius: 0;
    border-color: #ccc;
  }

  .d2h-files-diff {
    display: flex;
  }

    .d2h-file-diff,
    .d2h-file-side-diff {
      margin: 0;
      overflow-x: auto;

      & + .d2h-file-side-diff {
        border-left: 1px solid #ccc;
      }
    }

      .d2h-diff-tbody {
        tr {
          &:first-child {
            display: none;
          }

          .d2h-code-line-prefix {
            display: none;
          }

          .d2h-code-side-linenumber {
            display: none;
          }

          .d2h-code-side-line {
            margin: 0;
          }
        }
      }
}
</style>