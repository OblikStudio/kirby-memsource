<template>
  <div class="k-fieldset">
    <k-grid>
      <k-column>
        <h2>
          {{ project.name }}
          <span>#{{ project.internalId}}</span>
        </h2>
      </k-column>

      <k-column>
        <ul>
          <li v-for="property in properties" :key="property.text">
            <strong>{{ property.text }}:</strong>
            {{ property.value }}
          </li>
        </ul>
      </k-column>

      <k-column class="ms-actions">
        <k-button
          class="ms-button"
          icon="upload"
          @click="exportData"
        >
          Export
        </k-button>

        <k-button
          class="ms-button ms-t2"
          icon="download"
          @click="listJobs"
        >
          Import
        </k-button>
      </k-column>
    </k-grid>
  </div>
</template>

<script>
export default {
  computed: {
    project () {
      return this.$store.state.project
    },
    properties () {
      return [
        {
          text: 'Source language',
          value: this.project.sourceLang
        },
        {
          text: 'Target languages',
          value: this.project.targetLangs.join(', ')
        },
        {
          text: 'Jobs total',
          value: this.project.progress.totalCount
        },
        {
          text: 'Jobs finished',
          value: this.project.progress.finishedCount
        }
      ]
    }
  },
  methods: {
    exportData () {
      this.$store.commit('VIEW', 'Export')
    },
    listJobs () {
      this.$store.dispatch('listJobs', {
        projectId: this.project.id
      }).catch(error => {
        this.$store.commit('ALERT', {
          type: 'error',
          data: error
        })
      }).then(response => {
        this.$store.commit('VIEW', 'Jobs')
      })
    }
  }
}
</script>

<style lang="scss" scoped>
h2 {
  text-align: center;

  span {
    font-size: 18px;
    font-weight: normal;
  }
}

ul {
  display: flex;
  flex-flow: row wrap;
  margin-bottom: -0.5rem;

  li {
    margin-bottom: 0.5rem;
    flex: 1 1 50%;
  }
}
</style>
