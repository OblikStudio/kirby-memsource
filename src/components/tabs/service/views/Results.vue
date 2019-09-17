<template>
  <div>
    <div class="k-list">
      <div
        v-for="result in results"
        class="k-list-item"
        :class="{ 'ms-active': result === activeResult }"
        :key="result.id"
        @click="activeId = result.id"
      >
        <div class="k-list-item-image">
          <span class="k-icon" :data-ms-state="result.state">
            <strong>{{ result.job.targetLang }}</strong>
          </span>
        </div>
        
        <p class="k-list-item-text">
          <em>{{ result.job.filename }}</em>
        </p>
      </div>
    </div>

    <template v-if="activeResult">
      <template v-if="activeResult.state === 'imported'">
        <p class="ms-title">Changed {{ activeResult.diff.length }} values in {{ activeResult.language.name }}</p>
        <Diff :entries="activeResult.diff" />
      </template>
      <template v-else-if="activeResult.state === 'error'">
        <p class="ms-title">Error: {{ activeResult.error.message }}</p>
      </template>
      <template v-if="activeResult.state === 'empty'">
        <p class="ms-title">Nothing was changed</p>
      </template>
    </template>
  </div>
</template>

<script>
import { getEntries } from '@/modules/diff'
import Diff from '@/components/Diff.vue'

export default {
  components: {
    Diff
  },
  data () {
    return {
      activeId: null
    }
  },
  computed: {
    results () {
      return this.$store.state.results.map(result => {
        var diff = getEntries(result.data)
        var state = 'imported'

        if (result.error) {
          state = 'error'
        } else if (diff.length === 0) {
          state = 'empty'
        }

        return {
          id: result.job.uid,
          job: result.job,
          error: result.error,
          language: result.language,
          state,
          diff
        }
      })
    },
    activeResult () {
      return this.results.find(result => result.id === this.activeId)
    }
  },
  created () {
    if (this.results.length) {
      this.activeId = this.results[0].id
    }
  }
}
</script>

<style lang="scss" scoped>
.ms-active {
  background: #f6f6f6;
}

.ms-title {
  margin-top: 2.5rem;
  margin-bottom: 1rem;
  text-align: center;
}

.k-list-item {
  cursor: pointer;
}

  .k-list-item-image {
    width: 64px;

    .k-icon {
      width: auto;
      color: #fff;
    }
  }

[data-ms-state="imported"] { background: #5d800d; }
[data-ms-state="empty"] { background: #000; }
[data-ms-state="error"] { background: #800d0d; }
</style>