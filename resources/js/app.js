import { createApp } from 'vue'
import router from './router'
import App from './App.vue'
import AOS from 'aos'
import 'aos/dist/aos.css'
import { createHead } from '@vueuse/head'

const app = createApp(App)

const head = createHead()

app.use(router)
app.use(head)

app.mount('#app')


AOS.init({
  duration: 900,
  easing: 'ease-out-cubic',
  once: true,
})


