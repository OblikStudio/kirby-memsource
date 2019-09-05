<template>
  <div class="k-list">
    <div
      v-for="project in $store.state.memsource.projects"
      :key="project.uid"
      class="k-list-item"
      @click="openProject(project)"
    >
      <div class="k-list-item-image">
        <span class="k-icon" data-back="black" title="Project ID">
          <strong>{{ project.internalId }}</strong>
        </span>
      </div>
      
      <p class="k-list-item-text">
        {{ project.name }}
      </p>
    </div>
  </div>
</template>

<script>
export default {
  methods: {
    openProject (project) {
      this.$store.commit('SET_PROJECT', project)
      this.$store.commit('VIEW', {
        text: project.name,
        value: 'Project'
      })
    }
  },
  created () {
    this.$store.dispatch('loadProjects').catch(error => {
      this.$store.commit('ALERT', {
        type: 'negative',
        data: error
      })
    })
  }
}
</script>
