<template>
  <div class="k-list">
    <div
      v-for="project in $store.state.memsource.projects"
      :key="project.uid"
      class="k-list-item"
    >
      <div class="k-list-item-image">
        <span class="k-icon" data-back="black" title="Project ID">
          <strong>{{ project.internalId }}</strong>
        </span>
      </div>
      
      <p class="k-list-item-text">
        {{ project.name }}
      </p>

      <k-dropdown class="k-list-item-options">
        <k-button @click="openDropdown(project.uid)" icon="dots" alt="Actions"></k-button>
        <k-dropdown-content :id="project.uid" ref="dropdown" align="right">
          <k-dropdown-item icon="upload" @click="$emit('upload', project)">Upload</k-dropdown-item>
          <k-dropdown-item icon="page" @click="$emit('listJobs', project)">Jobs</k-dropdown-item>
        </k-dropdown-content>
      </k-dropdown>
    </div>
  </div>
</template>

<script>
export default {
  methods: {
    openDropdown (projectId) {
      var dropdown = this.$refs.dropdown.find(vm => vm.$attrs.id === projectId)
      if (dropdown) {
        dropdown.toggle()
      }
    }
  }
}
</script>
