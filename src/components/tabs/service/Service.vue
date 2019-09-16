<template>
  <component :is="$store.getters.view"></component>
</template>

<script>
import Projects from './views/Projects.vue'
import Project from './views/Project.vue'
import Export from './views/Export.vue'
import Upload from './views/Upload.vue'
import Import from './views/Import.vue'
import Results from './views/Results.vue'

export default {
  components: {
    Projects,
    Project,
    Export,
    Upload,
    Import,
    Results
  },
  created () {
    this.$store.commit('VIEW', 'Projects')
  }
}
</script>

