<template>
  <form class="form" method="post" @submit.prevent="logIn">
   <div class="k-input" data-theme="field">
      <span class="k-input-element">
        <input
          v-model="credentials.username"
          type="username"
          id="ms-username"
          name="ms-username"
          class="k-text-input"
          autocomplete="username"
          placeholder="Username"
        >
      </span>
      <label for="ms-username" class="k-input-icon">
        <k-icon type="user"/>
      </label>
    </div>

    <div class="k-input" data-theme="field">
      <span class="k-input-element">
        <input
          v-model="credentials.password"
          type="password"
          id="ms-password"
          name="ms-password"
          class="k-text-input"
          autocomplete="password"
          placeholder="Password"
        >
      </span>
      <label for="ms-password" class="k-input-icon">
        <k-icon type="key"/>
      </label>
    </div>

    <k-button
      type="submit"
      icon="check"
    >
      Login
    </k-button>
  </form>
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
    logIn () {
      this.$store.dispatch('logIn', this.credentials).catch(error => {
        this.$store.commit('ALERT', {
          type: 'negative',
          data: error
        })
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.form {
  max-width: 18rem;
  margin: 0 auto;
  text-align: center;

  .k-input {
    margin-bottom: 1rem;
  }
}
</style>
