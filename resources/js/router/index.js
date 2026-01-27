import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import About from '../pages/About.vue'
import Contact from '../pages/Contact.vue'
import Portfolio from '../pages/Portfolio.vue'
import SinglePost from '../components/SinglePost.vue' // ou le chemin vers ton composant



const routes = [
    { path: '/', component: Home,meta: { title: 'Accueil' },},
    { path: '/about', component: About },
    { path: '/contact', component: Contact },
    { path: '/portfolio', component: Portfolio,
    meta: { title: 'Avis & Communiqués' }, },

     {
    path: '/posts/:slug', // Le ":slug" est la partie variable
    name: 'post.show',
    component: SinglePost,
    props: true // Permet de recevoir le slug comme une prop si besoin
  },
 



  
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})



export default createRouter({
    history: createWebHistory(),
    routes,
})
