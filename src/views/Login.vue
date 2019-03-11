<template>
  <div class="form">
    <k-input v-model="credentials.username" type="text" name="username" theme="field" icon="user" />
    <k-input v-model="credentials.password" type="password" name="password" theme="field" icon="key" />
    <k-button icon="check" @click="signIn">Login</k-button>
  </div>
</template>

<script>
export default {
  data () {
    return {
      credentials: {
        username: null,
        password: null
      }
    }
  },
  methods: {
    signIn () {
      this.$store.dispatch('logIn', this.credentials).then(() => {
        console.log('loggedIn')
        this.$emit('loggedIn')
      }).catch(error => {
         console.log(error)
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.form {
  max-width: 20rem;
  margin: 0 auto;

  .k-input {
    margin-bottom: 1rem;
  }
}
</style>
