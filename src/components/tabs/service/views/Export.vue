<template>
  <div class="k-fieldset">
    <k-grid>
      <k-column width="1/2">
        <label for="pages" class="k-field-label">Pages</label>
        <k-input v-model="pages" type="text" theme="field" icon="page" name="pages" placeholder="Export all pages" />
        <div data-theme="help" class="k-text k-field-help">When set, only pages containing the given string will be exported.</div>
      </k-column>

      <k-column width="1/2">
        <label class="k-field-label">Variables</label>
        <k-input v-model="variables" name="toggle" type="toggle" theme="field" />
        <div data-theme="help" class="k-text k-field-help">Whether to export language variables.</div>
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
  data () {
    return {
      pages: null,
      variables: true
    }
  },
  methods: {
    submit () {
      var data = {
        pages: this.pages,
        variables: this.variables
      }

      if (typeof data.pages === 'string' && data.pages.length) {
        data.pages = `!${ data.pages }!`
      } else {
        data.pages = null
      }

      this.$store.dispatch('exportContent', data).then(response => {
        this.$store.commit('SET_EXPORT_DATA', response.data)
        this.$store.commit('VIEW', 'Upload')
      }).catch(error => {
        this.$store.commit('ALERT', {
          type: 'negative',
          data: error
        })
      })
    }
  }
}
</script>

