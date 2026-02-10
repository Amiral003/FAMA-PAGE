import { createRouter, createWebHistory } from 'vue-router'

import Home from '../pages/Home.vue'
import About from '../pages/About.vue'
import Contact from '../pages/Contact.vue'

import SinglePost from '../components/SinglePost.vue'
import EtatMajor from '../pages/EtatMajor.vue'
import EtatmajorAT from '../components/EtatmajorAT.vue'
import EtatmajorAA from '../components/EtatmajorAA.vue'
import Dttia from '../components/Dttia.vue'
import Gendarmerie from '../components/Gendarmerie.vue'
import Dgm from '../components/Dgm.vue'
import EtatmajorGarde from '../components/EtatmajorGarde.vue'
import Police from '../components/Police.vue'
import Dmhta from '../components/Dmhta.vue'
import Dcssa from '../components/Dcssa.vue'
import Portfolio from '../pages/portfolio.vue'

const routes = [
  { path: '/', component: Home, meta: { title: 'Accueil' } },
  { path: '/about', component: About },
  { path: '/contact', component: Contact },
  { path: '/etatmajor', component: EtatMajor },
  {path:'/EtatmajorGarde',component:EtatmajorGarde},
  { path: '/EtatmajorAT', component: EtatmajorAT },
  { path: '/EtatmajorAA',component: EtatmajorAA },
  { path: '/dttia', component: Dttia },
  { path: '/dmhta', component: Dmhta },
  { path: '/dcssa', component: Dcssa },
  { path: '/gendarmerie', component: Gendarmerie },
  { path: '/dgm', component: Dgm },
  { path: '/police', component: Police },

  {
    path: '/portfolio',
    component:Portfolio,
    meta: { title: 'Avis & Communiqués' },
  },

  {
    path: '/posts/:slug',
    name: 'post.show',
    component: SinglePost,
    props: true,
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
