import axios from 'axios'
import App from './App.vue'
import Vue from 'vue'

// panel.plugin('oblik/memsource', {
//   views: {
//     memsource: {
//       label: 'Memsource',
//       icon: 'globe',
//       component: App
//     }
//   }
// })

var el = document.createElement('div')
document.body.appendChild(el)

new Vue({
  el,
  render: h => h(App)
})
