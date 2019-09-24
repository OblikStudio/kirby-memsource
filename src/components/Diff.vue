<template>
  <div class="ms-diff">
    <div
      v-for="entry in entries"
      :key="entry.name"
      class="ms-diff-entry" 
    >
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

export default {
  props: {
    entries: Array
  },
  components: {
    CodeDiff
  }
}
</script>

<style lang="scss" scoped>
.ms-diff-entry {
  & + .ms-diff-entry {
    margin-top: 1rem;
  }

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