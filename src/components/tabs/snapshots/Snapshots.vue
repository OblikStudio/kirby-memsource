<template>
	<div>
		<div class="k-section">
			<NameGen :label="$t('name')" v-model="name">
				<k-button slot="options" icon="add" @click="create">
					{{ $t('add') }}
				</k-button>
			</NameGen>
		</div>

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
				:flag="{
					icon: 'trash',
					click: removeClosure(snapshot.name)
				}"
			></k-list-item>
		</k-list>
	</div>
</template>

<script>
import NameGen from '@/components/NameGen.vue'

export default {
	inject: ['$alert'],
	components: {
		NameGen
	},
	data() {
		return {
			name: null,
			snapshots: []
		}
	},
	methods: {
		time(seconds) {
			return new Date(seconds * 1000).toLocaleString()
		},
		fetch() {
			this.$store
				.dispatch('memsource', {
					url: '/snapshot',
					method: 'get'
				})
				.then((response) => {
					this.snapshots = response.data.sort((a, b) => {
						return a.date > b.date ? -1 : 1
					})
				})
				.catch(this.$alert)
		},
		create() {
			this.$store
				.dispatch('memsource', {
					url: '/snapshot',
					method: 'post',
					params: {
						name: this.name
					}
				})
				.then((response) => {
					this.fetch()
				})
				.catch(this.$alert)
		},
		remove(name) {
			this.$store
				.dispatch('memsource', {
					url: '/snapshot',
					method: 'delete',
					params: {
						name
					}
				})
				.then((response) => {
					this.fetch()
				})
				.catch(this.$alert)
		},
		removeClosure(name) {
			return () => {
				this.remove(name)
			}
		}
	},
	created() {
		this.fetch()
	}
}
</script>
