// ------------------------------------------------------
// resources/js/app.js
// ------------------------------------------------------

import './bootstrap'
import '../css/app.css'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { i18n } from './i18n'

// ✅ Head manager (SEO)

import { createHead } from '@unhead/vue/client'

// ✅ AOS
import AOS from 'aos'
import 'aos/dist/aos.css'

// ✅ PrimeVue
import PrimeVue from 'primevue/config'
import Aura from '@primevue/themes/aura'

// ✅ PrimeIcons
import 'primeicons/primeicons.css'

// ✅ Directives perso
import safeLinks from './directives/safeLinks'

// ✅ Composants PrimeVue
// Tu peux garder ça pour l’instant.
// Plus tard, si l’auto-import marche bien partout, on pourra enlever les imports manuels.
import Button from 'primevue/button'
import Select from 'primevue/select'

// ------------------------------------------------------
// Create app
// ------------------------------------------------------
const app = createApp(App)
const head = createHead()

// ------------------------------------------------------
// Directives
// ------------------------------------------------------
app.directive('safe-links', safeLinks)

// ------------------------------------------------------
// Init AOS
// ------------------------------------------------------
AOS.init({
    duration: 900,
    easing: 'ease-out-cubic',
    once: true,
})

// ------------------------------------------------------
// PrimeVue config
// ------------------------------------------------------
app.use(PrimeVue, {
    ripple: true,
    theme: {
        preset: Aura,
        options: {
            prefix: 'p',
            darkModeSelector: '.dark',
            cssLayer: false,
        },
    },
})

// ✅ Enregistrement manuel conservé pour ne rien casser
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