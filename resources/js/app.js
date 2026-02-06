import './bootstrap'; // Important pour Laravel
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { createHead } from '@vueuse/head';

// AOS (Animations au scroll)
import AOS from 'aos';
import 'aos/dist/aos.css';

// PrimeVue 4
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import 'primeicons/primeicons.css';

const app = createApp(App);
const head = createHead();

// Initialisation de AOS
AOS.init({
  duration: 900,
  easing: 'ease-out-cubic',
  once: true,
});

// Configuration UNIQUE de PrimeVue
app.use(PrimeVue, {
    ripple: true,
    theme: {
        preset: Aura,
        options: {
            prefix: 'p',
            darkModeSelector: 'system',
            cssLayer: false
        }
    }
});

app.use(router);
app.use(head);

app.mount('#app');