<template>
  <component :is="$store.getters.view"></component>
</template>

<script>
import Projects from './views/Projects.vue'
import Project from './views/Project.vue'
import Export from './views/Export.vue'
import Upload from './views/Upload.vue'
import Jobs from './views/Jobs.vue'

export default {
  components: {
    Projects,
    Project,
    Export,
    Upload,
    Jobs
  },
  created () {
    this.$store.commit('VIEW', 'Projects')
  }
}
</script>

