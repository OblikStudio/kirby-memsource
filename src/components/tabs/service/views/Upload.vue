<template>
	<div>
		<section class="k-section">
			<Stats :data="stats" />
		</section>

		<section class="k-section">
			<k-json-editor
				v-model="$store.state.memsource.export"
				:label="$t('data')"
			></k-json-editor>
		</section>

		<k-grid class="k-section" gutter="medium">
			<k-column width="1/2">
				<NameGen v-model="jobName" :label="$t('memsource.label.job')" />
			</k-column>

			<k-column width="1/2">
				<k-checkboxes-field
					type="checkboxes"
					v-model="selectedLangs"
					:options="languageOptions"
					:label="$t('memsource.label.target_langs')"
				/>
			</k-column>
		</k-grid>

		<k-button-group align="center">
			<k-button icon="download" @click="downloadExport">{{
				$t('file')
			}}</k-button>

			<k-button theme="positive" icon="upload" @click="upload">{{
				$t('upload')
			}}</k-button>
		</k-button-group>
	</div>
</template>

<script>
import Stats from '../../../Stats.vue'
import NameGen from '../../../NameGen.vue'

function countObjectData(data) {
	let stats = {
		strings: 0,
		words: 0,
		chars: 0
	}

	for (let k in data) {
		let value = data[k]

		if (typeof value === 'object' && value !== null) {
			let childStats = countObjectData(value)

			for (let k in childStats) {
				stats[k] += childStats[k]
			}
		} else if (data.hasOwnProperty(k)) {
			value = value + ''

			stats.strings++
			stats.chars += value.length

			if (value.length) {
				stats.words += value.trim().split(/\s+/).length
			}
		}
	}

	return stats
}

export default {
	inject: ['$alert'],
	components: {
		Stats,
		NameGen
	},
	data() {
		return {
			selectedLangs: [],
			jobName: null
		}
	},
	computed: {
		data() {
			return this.$store.state.memsource.export
		},
		project() {
			return this.$store.state.memsource.project
		},
		stats() {
			if (!this.data) {
				return null
			}

			let pages = this.data.pages && Object.keys(this.data.pages).length
			let files = this.data.files && Object.keys(this.data.files).length
			let variableStats = countObjectData(this.data.variables)
			let stats = countObjectData(this.data)

			return [
				{ title: this.$t('pages'), content: pages || 0 },
				{ title: this.$t('files'), content: files || 0 },
				{ title: this.$t('variables'), content: variableStats.strings },
				{ title: this.$t('strings'), content: stats.strings },
				{ title: this.$t('words'), content: stats.words },
				{ title: this.$t('characters'), content: stats.chars }
			]
		},
		languageOptions() {
			let targetLangs = this.project.targetLangs

			return this.$store.getters['memsource/availableLanguages']
				.filter(lang => !lang.default)
				.map(lang => {
					/**
					 *	Overwrite because site locales can't be used
					 *	@see https://github.com/getkirby/kirby/issues/2054
					 */
					let target = targetLangs.find(code => code.indexOf(lang.code) === 0)
					if (target) {
						lang.code = target
					}

					return {
						value: lang.code,
						text: `${lang.name} (${lang.code})`
					}
				})
		}
	},
	methods: {
		upload() {
			this.$alert(this.$t('upload.progress'))

			this.$store
				.dispatch('memsource/memsource', {
					url: `/upload/${this.project.uid}/${this.jobName}.json`,
					method: 'post',
					headers: {
						'Memsource-Langs': JSON.stringify(this.selectedLangs)
					},
					data: this.data
				})
				.then(response => {
					let jobs = response.data && response.data.jobs
					if (jobs && jobs.length) {
						this.$alert(
							this.$t('memsource.info.created_jobs', { count: jobs.length }),
							'positive'
						)
					}
				})
				.catch(this.$alert)
		},
		downloadExport() {
			let part = JSON.stringify(this.data, null, 2)
			let blob = new Blob([part], {
				type: 'application/json'
			})

			let url = URL.createObjectURL(blob)
			let anchor = document.createElement('a')
			anchor.download = this.jobName
			anchor.href = url
			anchor.click()
		}
	},
	created() {
		let codes = this.languageOptions.map(lang => lang.value)
		this.selectedLangs = this.project.targetLangs.filter(code => {
			return codes.indexOf(code) >= 0
		})
	}
}
</script>
