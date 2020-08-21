<template>
	<div>
		<k-list>
			<k-list-item
				v-for="result in results"
				:key="result.id"
				:text="result.job.filename"
				:icon="{ type: statusIcon(result.state), back: result.state }"
				:info="$jobInfo(result.job)"
				@click="open(result)"
			/>
		</k-list>

		<k-dialog ref="dialog" size="large">
			<template v-if="activeResult">
				<k-headline>
					<template v-if="activeResult.state === 'imported'">
						{{
							$t('memsource.info.changed_values', {
								count: activeResult.diff.length,
								language: activeResult.language.name
							})
						}}
					</template>
					<template v-else-if="activeResult.state === 'error'">
						{{
							$t('memsource.info.error', {
								message: activeResult.error.message
							})
						}}
					</template>
					<template v-if="activeResult.state === 'empty'">
						{{ $t('memsource.info.no_changed') }}
					</template>
				</k-headline>

				<Diff v-if="activeResult.diff.length" :entries="activeResult.diff" />
			</template>

			<k-button-group slot="footer">
				<div></div>
				<k-button icon="check" @click="close">
					{{ $t('close') }}
				</k-button>
			</k-button-group>
		</k-dialog>
	</div>
</template>

<script>
import Diff from '../../../Diff.vue'
import { getEntries } from '../../../../modules/diff'

export default {
	inject: ['$jobInfo'],
	components: {
		Diff
	},
	data() {
		return {
			activeResult: null
		}
	},
	computed: {
		results() {
			return this.$store.state.results.map((result) => {
				let diff = getEntries(result.data)
				let state = 'imported'

				if (result.error) {
					state = 'error'
				} else if (diff.length === 0) {
					state = 'empty'
				}

				return {
					id: result.job.uid,
					job: result.job,
					error: result.error,
					language: result.language,
					state,
					diff
				}
			})
		}
	},
	methods: {
		statusIcon(state) {
			switch (state) {
				case 'imported':
					return 'check'
				case 'error':
					return 'cancel'
				case 'empty':
					return 'circle-outline'
			}
		},
		open(result) {
			this.activeResult = result
			this.$refs.dialog.open()
		},
		close() {
			this.$refs.dialog.close()
			this.activeResult = null
		}
	}
}
</script>

<style lang="postcss" scoped>
/deep/ {
	[data-back] {
		color: #fff;
	}
	[data-back='imported'] {
		background: #5d800d;
	}
	[data-back='empty'] {
		background: #000;
	}
	[data-back='error'] {
		background: #800d0d;
	}
}

.k-list-item {
	cursor: pointer;
}

.k-headline {
	text-align: center;
}

.ms-diff {
	margin-top: 1.5rem;
}
</style>
