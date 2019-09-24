// https://codepen.io/z-------------/pen/GgVeXj

var random = function(min, max) {
  return Math.floor(Math.random() * (max - min) + min)
}

function Wordgen (options) {
  options = options || {}

  this.min = options.min || options.length || 6
  this.max = options.max || options.length || 8
  this.vowels = options.vowels || 'aeiouy'
  this.consonants = options.consonants || 'bcdfghjklmnprstvwxz'
} Wordgen.prototype = {
  generate () {
    var length = random(this.min, this.max + 1) // + 1 due to floor()
    var vowel = (Math.random() > 0.5)
    var word = ''

    for (var i = 0; i < length; i++) {
      if (vowel) {
        word += this.vowels[random(0, this.vowels.length)]
      } else {
        word += this.consonants[random(0, this.consonants.length)]
      }
      
      vowel = !vowel
    }

    return word
  }
}

module.exports = Wordgen
