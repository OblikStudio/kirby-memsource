<template>
  <div>
    <header class="k-section-header">
      <k-headline>{{ data.role }}</k-headline>
      <k-button-group>
        <k-button icon="logout" theme="negative" @click="logout">
          Log out
        </k-button>
      </k-button-group>
    </header>

    <Stats :data="info" />
  </div>
</template>

<script>
import Stats from '@/components/Stats.vue'

export default {
  components: {
    Stats
  },
  props: {
    data: Object
  },
  computed: {
    info () {
      return [
        {
          title: 'Name',
          content: `${ this.data.firstName } ${ this.data.lastName }`
        },
        {
          title: 'Email',
          content: this.data.email
        }
      ]
    }
  },
  methods: {
    logout () {
      this.$store.commit('SET_SESSION', null)
    }
  }
}
</script>
