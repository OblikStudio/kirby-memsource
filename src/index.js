import './scss/main.scss'
import App from './App.vue'

panel.plugin('oblik/memsource', {
  views: {
    memsource: {
      label: 'Memsource',
      icon: 'globe',
      component: App
    }
  }
})
