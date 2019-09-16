<template>
  <div class="k-list">
    <div
      v-for="project in projects"
      :key="project.uid"
      class="k-list-item"
      @click="open(project)"
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
  inject: ['$alert'],
  data () {
    return {
      projects: []
    }
  },
  methods: {
    open (project) {
      this.$store.commit('SET_PROJECT', project)
      this.$store.commit('VIEW', {
        text: project.name,
        value: 'Project'
      })
    }
  },
  created () {
    this.$store.commit('SET_PROJECT', null)
    this.$store.dispatch('memsource', {
      url: '/projects',
      method: 'get'
    }).then(response => {
      this.projects = response.data.content
    }).catch(this.$alert)
  }
}
</script>
