// ------------------------------------------------------
// resources/js/app.js
// ------------------------------------------------------

import { i18n } from './i18n'
// ✅ Laravel bootstrap (axios/csrf etc.)
import './bootstrap'

// ✅ CSS global (tailwind + tes styles)
import '../css/app.css'

// ✅ Vue
import { createApp } from 'vue'
import App from './App.vue'

// ✅ Router
import router from './router'

// ✅ Head manager (SEO)
import { createHead } from '@vueuse/head'

// ✅ AOS (Animations au scroll)
import AOS from 'aos'
import 'aos/dist/aos.css'

// ✅ PrimeVue v4
import PrimeVue from 'primevue/config'
import Aura from '@primevue/themes/aura'

// ✅ PrimeIcons (pour pi pi-moon / pi pi-sun / pi pi-bars etc.)
import 'primeicons/primeicons.css'

// ✅ Composants PrimeVue utilisés (tu peux en ajouter d'autres ici)
import Button from 'primevue/button'
import Select from 'primevue/select' // (remplace Dropdown déprécié)

// ------------------------------------------------------
// Create app
// ------------------------------------------------------
const app = createApp(App)

// ✅ vueuse/head
const head = createHead()


import safeLinks from './directives/safeLinks'

app.directive('safe-links', safeLinks)

// ------------------------------------------------------
// Init AOS (si tu veux seulement côté client)
// ------------------------------------------------------
AOS.init({
  duration: 900,
  easing: 'ease-out-cubic',
  once: true,
})

// ------------------------------------------------------
// PrimeVue config (UNE SEULE FOIS)
// ------------------------------------------------------
app.use(PrimeVue, {
  ripple: true,
  theme: {
    preset: Aura,
    options: {
      prefix: 'p',
      // IMPORTANT:
      // - 'system' = se base sur le thème OS
      // - si tu veux contrôler via html.dark, tu peux mettre 'class'
      // MAIS: ton toggle thème dans la navbar fonctionne via html.dark + ton CSS,
      // donc tu peux laisser system ici si tu veux juste PrimeVue auto.
      darkModeSelector: '.dark',
      cssLayer: false,
    },
  },
})

// ✅ Enregistrement global des composants PrimeVue (facultatif mais pratique)
app.component('Button', Button)
app.component('Select', Select)

// ------------------------------------------------------
// Plugins
// ------------------------------------------------------
app.use(router)
app.use(head)
app.use(i18n)

// ------------------------------------------------------
// Mount
// ------------------------------------------------------
app.mount('#app')