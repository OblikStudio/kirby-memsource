export default class Wordgen {
	constructor(options) {
		options = options || {}

		this.min = options.min || options.length || 6
		this.max = options.max || options.length || 8
		this.vowels = options.vowels || 'aeiouy'
		this.consonants = options.consonants || 'bcdfghjklmnprstvwxz'
	}

	random(min, max) {
		return Math.floor(Math.random() * (max - min) + min)
	}

	generate() {
		let length = this.random(this.min, this.max + 1) // + 1 due to floor()
		let vowel = Math.random() > 0.5
		let word = ''

		for (let i = 0; i < length; i++) {
			if (vowel) {
				word += this.vowels[this.random(0, this.vowels.length)]
			} else {
				word += this.consonants[this.random(0, this.consonants.length)]
			}

			vowel = !vowel
		}

		return word
	}
}
