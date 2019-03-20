<template>
  <form class="form" method="post" @submit="signIn">
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
    signIn (event) {
      event.preventDefault()
      this.$emit('logIn', this.credentials)
    }
  }
}
</script>

<style lang="scss" scoped>
.form {
  max-width: 18rem;

  .k-input {
    margin-bottom: 1rem;
  }
}
</style>
