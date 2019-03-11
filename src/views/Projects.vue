<template>
  <div class="k-list">
    <ProjectItem
      v-for="project in projects"
      :data="project"
      @upload="$emit('upload', project)"
      @download="$emit('download', project)"
    ></ProjectItem>
  </div>
</template>

<script>
import ProjectItem from '../comps/ProjectItem.vue'

export default {
  components: {
    ProjectItem
  },
  computed: {
    projects () {
      return this.$store.state.memsource.projects
    }
  }
}
</script>
