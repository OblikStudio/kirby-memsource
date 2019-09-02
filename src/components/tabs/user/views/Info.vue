<template>
  <div class="ms-screen">
    <h3>{{ data.role }}</h3>

    <div class="ms-user-info">
      <p>{{ data.firstName }} {{ data.lastName }}</p>
      <p>{{ data.email }}</p>
    </div>

    <k-button
      icon="logout"
      theme="negative"
      @click="$store.dispatch('logOut')"
    >
      Log out
    </k-button>
  </div>
</template>

<script>
export default {
  props: {
    data: Object
  }
}
</script>

<style lang="scss" scoped>
.ms-screen {
  text-align: center;
}

  .ms-user-info {
    margin-top: 1rem;
    margin-bottom: 2rem;
  }
</style>
