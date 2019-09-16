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
          <span class="k-icon" data-back="black" title="Target language">
            <strong>{{ result.job.targetLang }}</strong>
          </span>
        </div>
        
        <p class="k-list-item-text">
          <em>{{ result.job.filename }}</em>
        </p>
      </div>
    </div>

    <template v-if="activeResult">
      <Diff v-if="!activeResult.isEmpty" :entries="activeResult.diff" />
      <p v-else>Nothing was changed.</p>
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

        return {
          id: result.job.uid,
          job: result.job,
          isEmpty: diff.length === 0,
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
  box-shadow: 0 0 0 2px rgba(66,113,174,.25);
}
</style>