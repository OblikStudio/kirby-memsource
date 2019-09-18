<template>
  <div class="ms-crumbs">
    <div
      v-for="(crumb, index) in crumbs"
      :key="crumb.value"
      class="ms-crumbs__crumb"
    >
      <span v-if="index === value.length - 1">{{ $t(crumb.text) }}</span>
      <button v-else @click="open(crumb)">{{ $t(crumb.text) }}</button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    value: {
      type: Array
    }
  },
  data () {
    return {
      crumbs: []
    }
  },
  methods: {
    open (crumb) {
      this.crumbs.splice(this.crumbs.indexOf(crumb) + 1)
      this.$emit('input', this.crumbs)
    }
  },
  watch: {
    value: {
      immediate: true,
      handler (value) {
        this.crumbs = [...value]
      }
    }
  }
}
</script>

<style lang="scss">
.ms-crumbs {
  padding: 0.625rem;
  font-size: 0.75rem;
  font-weight: 500;
  text-align: center;
  text-transform: uppercase;
  border: 1px solid #ccc;
}

  .ms-crumbs__crumb {
    display: inline-block;
    line-height: 1;

    button {
      font-size: inherit;
      font-weight: inherit;
      text-transform: inherit;
      outline: none;
      opacity: 0.75;

      &:after {
        content: '→';
        margin: 0 8px;
        user-select: none;
        opacity: 0.5;
      }

      &:hover, &:focus {
        opacity: 1;
      }
    }
  }
</style>
