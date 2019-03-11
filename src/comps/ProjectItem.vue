<template>
  <div class="k-list-item">
    <div class="k-list-item-image">
      <span class="k-icon" data-back="black" title="Project ID">
        <strong>{{ data.internalId }}</strong>
      </span>
    </div>
    
    <p class="k-list-item-text">
      {{ data.name }}
    </p>

    <k-dropdown class="k-list-item-options">
      <k-button @click="$refs.dropdown.toggle()" icon="dots" alt="Actions"></k-button>
      <k-dropdown-content ref="dropdown" align="right">
        <k-dropdown-item icon="upload" @click="$emit('upload')">Upload</k-dropdown-item>
        <k-dropdown-item icon="import" @click="$emit('download')">Download</k-dropdown-item>
      </k-dropdown-content>
    </k-dropdown>
  </div>
</template>

<script>
export default {
  props: {
    data: {
      type: Object
    }
  }
}
</script>
