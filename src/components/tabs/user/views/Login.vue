<template>
  <k-form v-model="credentials" @submit="submit" :fields="{
    userName: {
      width: '1/2',
      label: $t('username'),
      type: 'text'
    },
    password: {
      width: '1/2',
      label: $t('password'),
      type: 'password',
      minlength: 0
    }
  }">
    <k-button-group slot="footer" align="center">
      <k-button icon="check" @click="submit">{{ $t('login') }}</k-button>
    </k-button-group>
  </k-form>
</template>

<script>
export default {
  inject: ['$alert', '$loading'],
  data () {
    return {
      credentials: {
        userName: null,
        password: null
      }
    }
  },
  methods: {
    submit () {
      this.$loading(
        this.$store.dispatch('memsource', {
          url: '/auth/login',
          method: 'post',
          data: this.credentials
        }).then(response => {
          this.$store.commit('SET_SESSION', response.data)
        }).catch(this.$alert)
      )
    }
  }
}
</script>
