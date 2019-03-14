<template>
  <div class="k-fieldset">
    <k-grid>
      <k-column width="1/1">
        <label for="pages" class="k-field-label">Pages</label>
        <k-input v-model="page" type="text" theme="field" icon="page" name="pages" placeholder="Export all pages" />
        <div data-theme="help" class="k-text k-field-help">When set, only pages containing the given string will be exported.</div>
      </k-column>

      <k-column width="1/1">
        <label class="k-field-label">Variables</label>
        <k-input v-model="variables" name="toggle" type="toggle" theme="field" />
        <div data-theme="help" class="k-text k-field-help">Whether to export language variables.</div>
      </k-column>

      <k-column width="1/1">
        <k-button icon="check" @click="submit">Export</k-button>
      </k-column>
    </k-grid>
  </div>
</template>

<script>
export default {
  data () {
    return {
      page: null,
      variables: true
    }
  },
  methods: {
    submit () {
      this.$emit('export', {
        page: this.page || null,
        variables: this.variables
      })
    }
  }
}
</script>

