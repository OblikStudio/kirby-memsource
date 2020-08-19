<template>
	<div v-if="jobs.length">
		<k-text-field
			class="k-section"
			v-model="query"
			:counter="false"
			:label="$t('memsource.label.filter_jobs')"
			:placeholder="$t('search')"
		>
			<k-button slot="options" icon="check" @click="toggle">
				{{ $t('select') }}
			</k-button>
		</k-text-field>

		<k-list>
			<k-list-item
				v-for="job in filteredJobs"
				:key="job.uid"
				:text="job.filename"
				:info="$jobInfo(job)"
				:icon="{ type: 'text', back: 'black' }"
			>
				<label slot="options" class="k-button" :for="job.uid">
					<input type="checkbox" :id="job.uid" v-model="selectedJobs" :value="job.uid" />
				</label>
			</k-list-item>
			<k-empty v-if="filteredJobs.length < jobs.length" icon="text">
				{{ $t('memsource.info.hidden_jobs', { count: jobs.length - filteredJobs.length }) }}
			</k-empty>
		</k-list>

		<k-button-group v-if="selectedJobs.length" align="center">
			<k-button icon="trash" theme="negative" @click="$refs.dialog.open()">
				{{ $t('delete') }}
			</k-button>

			<k-button icon="download" theme="positive" @click="importHandler">
				{{ $t('import') }}
			</k-button>
		</k-button-group>

		<k-dialog
			ref="dialog"
			theme="negative"
			icon="trash"
			:button="$t('delete')"
			@submit="deleteJobs"
		>
			<k-text>
				{{ $t('memsource.info.jobs_deletion', { count: selectedJobs.length }) }}
			</k-text>
		</k-dialog>
	</div>
	<k-empty v-else icon="text">
		{{ $t('memsource.info.jobs_empty') }}
	</k-empty>
</template>

<script>
import freeze from 'deep-freeze-node'

export default {
	inject: ['$alert', '$jobInfo', '$loading'],
	data () {
		return {
			jobs: [],
			query: null,
			selectedJobs: []
		}
	},
	computed: {
		projectId () {
			return this.$store.state.project.id
		},
		filteredJobs () {
			return this.jobs.filter(job => {
				var matches = (!this.query || job.filename.indexOf(this.query) >= 0)
				var selectedIndex = this.selectedJobs.indexOf(job.uid)
				if (selectedIndex >= 0 && !matches) {
					this.selectedJobs.splice(selectedIndex, 1)
				}

				return matches
			})
		}
	},
	methods: {
		toggle () {
			if (this.selectedJobs.length !== this.filteredJobs.length) {
				this.selectedJobs = this.filteredJobs.map(job => job.uid)
			} else {
				this.selectedJobs = []
			}
		},
		importHandler () {
			var jobs = this.selectedJobs
				.map(id => this.jobs.find(job => job.uid === id))
				.filter(job => !!job)

			this.$loading(
				Promise.all(jobs.map(this.importJob, this)).then(results => {
					this.$store.commit('SET_RESULTS', results)
					this.$store.commit('VIEW', 'Results')
				})
			)
		},
		loadJobs () {
			return this.$store.dispatch('memsource', {
				url: `/projects/${ this.projectId }/jobs`
			}).then(response => {
				this.jobs = freeze(response.data.content)
				this.selectedJobs = []
			}).catch(this.$alert)
		},
		importJob (job) {
			var promise
			var language = this.$store.getters.availableLanguages.find(lang => {
				return job.targetLang.indexOf(lang.code) === 0
			})

			if (language) {
				promise = this.$store.dispatch('memsource', {
					url: `/projects/${ this.projectId }/jobs/${ job.uid }/targetFile`,
					responseType: 'blob'
				}).then(response => {
					var blob = response.data
					var config = {
						language: language.code
					}

					return this.$store.dispatch('memsource', {
						url: '/import',
						method: 'post',
						headers: {
							'Memsource': JSON.stringify(config)
						},
						data: blob
					})
				}).then(response => {
					return Promise.resolve(response.data)
				})
			} else {
				promise = Promise.reject(new Error(this.$t('memsource.info.invalid_language')))
			}

			return promise.then(data => {
				return Promise.resolve({ job, language, data })
			}).catch(error => {
				return Promise.resolve({ job, language, error })
			})
		},
		deleteJobs () {
			var jobs = this.selectedJobs

			this.$refs.dialog.close()
			this.$loading(
				this.$store.dispatch('memsource', {
					url: `/projects/${ this.projectId }/jobs/batch`,
					method: 'delete',
					data: {
						jobs: jobs.map(id => ({ uid: id }))
					}
				}).then(response => {
					return this.loadJobs()
				}).then(response => {
					this.$alert(this.$t('memsource.info.deleted_jobs', {
						count: jobs.length
					}))
				}).catch(this.$alert)
			)
		}
	},
	created () {
		this.$loading(this.loadJobs())
	}
}
</script>

<style lang="scss" scoped>
/deep/ {
	.k-list-item {
		.k-button {
			display: flex;
			align-items: center;
			margin-left: -10px;
			top: 2px;

			&, input {
				cursor: pointer;
			}
		}
	}
}
</style>
