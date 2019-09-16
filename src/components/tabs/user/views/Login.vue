<template>
  <form class="form" method="post" @submit.prevent="logIn">
   <div class="k-input" data-theme="field">
      <span class="k-input-element">
        <input
          v-model="username"
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
          v-model="password"
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
  inject: ['$alert'],
  data () {
    return {
      username: null,
      password: null
    }
  },
  methods: {
    logIn () {
      this.$store.dispatch('memsource', {
        url: '/auth/login',
        method: 'post',
        data: {
          userName: this.username,
          password: this.password
        }
      }).then(response => {
        this.$store.commit('SET_SESSION', response.data)
      }).catch(this.$alert)
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
