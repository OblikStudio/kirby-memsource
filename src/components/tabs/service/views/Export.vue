<template>
  <div class="k-fieldset">
    <k-grid>
      <k-column width="1/2">
        <label class="k-field-label">Snapshot</label>

        <select v-model="snapshot">
          <option :value="null">None</option>
          <option
            v-for="option in snapshots"
            :key="option.name"
            :value="option.name"
          >
            {{ option.name }}
          </option>
        </select>

        <div data-theme="help" class="k-text k-field-help">
          Compare current site data with a snapshot to export only the differences.
        </div>
      </k-column>

      <k-column width="1/2">
        <k-text-field
          v-model="pages"
          label="Pages"
          placeholder="Export all pages"
          help="When set, only pages containing the given string will be exported."
        />
      </k-column>

      <k-column width="1/1" class="ms-actions">
        <k-button
          class="ms-button"
          icon="upload"
          @click="submit"
        >
          Export
        </k-button>
      </k-column>
    </k-grid>
  </div>
</template>

<script>
export default {
  inject: ['$alert'],
  data () {
    return {
      pages: null,
      snapshot: null,
      snapshots: []
    }
  },
  methods: {
    submit () {
      var params = {
        pages: this.pages,
        snapshot: this.snapshot
      }

      if (typeof params.pages === 'string' && params.pages.length) {
        params.pages = `!${ params.pages }!` // PHP regex
      } else {
        params.pages = null
      }

      this.$store.dispatch('outsource', {
        url: '/export',
        params
      }).then(response => {
        this.$store.commit('SET_EXPORT', response.data)
        this.$store.commit('VIEW', 'Upload')
      }).catch(this.$alert)
    }
  },
  created () {
    this.$store.dispatch('outsource', {
      url: '/snapshot',
      method: 'get'
    }).then(response => {
      this.snapshots = response.data.sort((a, b) => {
        return (a.date > b.date) ? -1 : 1
      })
    }).catch(this.$alert)
  }
}
</script>

