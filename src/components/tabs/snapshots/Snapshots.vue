<template>
  <div class="k-fieldset">
    <k-grid>
      <k-column class="ms-add">
        <NameGen v-model="name" />

        <k-button icon="add" @click="create">
          {{ $t('add') }}
        </k-button>
      </k-column>

      <k-column>
        <k-list>
          <k-list-item
            v-for="snapshot in snapshots"
            :key="snapshot.name"
            :icon="{
              type: 'page',
              back: 'black'
            }"
            :text="snapshot.name"
            :info="time(snapshot.date)"
            :options="[
              {icon: 'trash', text: $t('delete')}
            ]"
            @action="remove(snapshot.name)"
          ></k-list-item>
        </k-list>
      </k-column>
    </k-grid>
  </div>
</template>

<script>
import NameGen from '@/components/NameGen.vue'

export default {
  components: {
    NameGen
  },
  data() {
    return {
      name: null,
      snapshots: []
    };
  },
  methods: {
    time (seconds) {
      return new Date(seconds * 1000).toLocaleString()
    },
    fetch () {
      this.$store.dispatch('outsource', {
        url: '/snapshot',
        method: 'get'
      }).then(response => {
        this.snapshots = response.data.sort((a, b) => {
          return (a.date > b.date) ? -1 : 1
        })
      })
    },
    create () {
      this.$store.dispatch('outsource', {
        url: '/snapshot',
        method: 'post',
        params: {
          name: this.name
        }
      }).then(response => {
        this.fetch()
      })
    },
    remove (name) {
      this.$store.dispatch('outsource', {
        url: '/snapshot',
        method: 'delete',
        params: {
          name
        }
      }).then(response => {
        this.fetch()
      })
    }
  },
  created() {
    this.fetch()
  }
};
</script>

<style lang="scss" scoped>
.ms-add {
  display: flex;
  grid-column: 4 / span 6;

  .k-button {
    flex: 1 0 auto;
    margin-left: 1rem;
  }
}
</style>
