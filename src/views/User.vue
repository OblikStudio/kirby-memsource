<template>
  <div class="ms-wrapper">
    <h3>{{ user.role }}</h3>

    <div class="ms-user-info">
      <p>{{ user.firstName }} {{ user.lastName }}</p>
      <p>{{ user.email }}</p>
    </div>

    <k-button
      icon="logout"
      theme="negative"
      @click="$emit('logOut')"
    >
      Log out
    </k-button>
  </div>
</template>

<script>
export default {
  computed: {
    user: function () {
      return this.$store.state.session.user
    }
  }
}
</script>

<style lang="scss" scoped>
.ms-user-info {
  margin-top: 1rem;
  margin-bottom: 2rem;
}
</style>
