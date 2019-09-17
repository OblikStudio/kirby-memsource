<template>
  <k-list>
    <k-list-item
      v-for="project in projects"
      :key="project.uid"
      :text="project.name"
      :info="info(project)"
      :icon="{ type: 'page', back: 'black' }"
      @click="open(project)"
    />
  </k-list>
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
    },
    info (project) {
      var date = new Date(project.dateCreated)
      return `${ date.toLocaleString() }<strong>#${ project.internalId }</strong>`
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

<style lang="scss" scoped>
.k-list-item {
  cursor: pointer;
}

/deep/ .k-list-item-text strong {
  margin-left: 10px;
}
</style>
