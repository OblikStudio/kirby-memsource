<template>
  <div class="ms-crumbs">
    <template v-for="(crumb, index) in crumbs">
      <span v-if="index === 0">{{ crumb.text }}</span>
      <button v-else @click="$emit('click', crumb)">
        {{ crumb.text }}
      </button>
    </template>
  </div>
</template>

<script>
export default {
  props: {
    entries: {
      type: Array
    }
  },
  computed: {
    crumbs () {
      return this.entries.slice().reverse()
    }
  }
}
</script>

<style lang="scss" scoped>
.ms-crumbs {
  button {
    display: inline-flex;
    font-size: 16px;
    line-height: 1;
    outline: none;

    &:before {
      content: '←';
      margin: 0 8px;
      color: #000;
      font-size: 1em;
      user-select: none;
      opacity: 0.5;
    }

    &:hover, &:focus {
      color: #555;
    }
  }
}
</style>
