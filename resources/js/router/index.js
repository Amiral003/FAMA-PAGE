import { createRouter, createWebHistory } from 'vue-router'

// -------------------- PAGES / COMPONENTS --------------------
import Home from '../pages/home.vue'
import About from '../pages/about.vue'
import Contact from '../pages/contact.vue'
import Portfolio from '../pages/portfolio.vue'
import Recrutement from '../pages/recrutement.vue'

import SinglePost from '../components/SinglePost.vue' // ou le chemin vers ton composant
import EtatMajor from '../pages/etatmajor.vue'

import EtatmajorAT from '../components/EtatmajorAT.vue'
import EtatmajorAA from '../components/EtatmajorAA.vue'
import Dttia from '../components/Dttia.vue'
import Gendarmerie from '../components/Gendarmerie.vue'
import Dgm from '../components/Dgm.vue'
import EtatmajorGarde from '../components/EtatmajorGarde.vue'
import Police from '../components/police.vue'
import Dmhta from '../components/Dmhta.vue'
import Dcssa from '../components/Dcssa.vue'
import ComOps from '../pages/comOps.vue'

// -------------------- ROUTES --------------------
const routes = [
  // ✅ Je garde ta route + meta.title déjà présent
  { path: '/', component: Home, meta: { title: 'Accueil' } },

  // ✅ J'ajoute meta.title (ton beforeEach s'en sert pour document.title)
  { path: '/about', component: About, meta: { title: 'À propos' } },
  { path: '/contact', component: Contact, meta: { title: 'Contact' } },

  { path: '/etatmajor', component: EtatMajor, meta: { title: 'État-Major' } },

  // ✅ Tes pages Etat-major (je n'ai PAS changé tes paths, juste ajouté les titles)
  { path: '/EtatmajorAT', component: EtatmajorAT, meta: { title: "État-Major - Armée de Terre" } },
  { path: '/EtatmajorAA', component: EtatmajorAA, meta: { title: "État-Major - Armée de l'Air" } },
  { path: '/Dttia', component: Dttia, meta: { title: 'D.T.T.I.A' } },
  { path: '/Dmhta', component: Dmhta, meta: { title: 'D.M.H.T.A' } },
  { path: '/Dcssa', component: Dcssa, meta: { title: 'D.C.S.S.A' } },
  { path: '/Gendarmerie', component: Gendarmerie, meta: { title: 'Gendarmerie Nationale' } },
  { path: '/Dgm', component: Dgm, meta: { title: 'Génie Militaire' } },
  { path: '/EtatmajorGarde', component: EtatmajorGarde, meta: { title: 'État-Major - Garde Nationale' } },
  { path: '/police', component: Police, meta: { title: 'Police Nationale' } },

 {
  path: '/actualites',
  name: 'actualites',
  component: Portfolio,
  meta: { title: 'Actualités & Communiqués' },
},

{
  path: '/communiques',
  redirect: '/actualites',
},

{
  path: '/portfolio',
  redirect: '/actualites',
},

{
  path: '/recrutement',
  name: 'recrutement',
  component: Recrutement,
  meta: { title: 'Recrutement' },
},
  { path: '/com-ops', component: ComOps, meta: { title: 'Com-Ops' } },
  {
  path: '/phototheque',
  name: 'phototheque',
  component: () => import('@/pages/phototheque.vue'),
},
  {
  path: '/videotheque',
  name: 'videotheque',
  component: () => import('@/pages/videotheque.vue'),
},

{
  path: '/etat-major/:slug',
  name: 'staff-show',
  component: () => import('@/pages/staffShow.vue'),
},
  // ✅ Single post : j'ajoute un title dynamique (si slug existe)
  {
    path: '/posts/:slug',
    name: 'post.show',
    component: SinglePost,
    props: true,
    meta: { title: 'Communiqué' }, // titre fallback
  },
]

// -------------------- ROUTER --------------------
const router = createRouter({
  history: createWebHistory(),
  routes,

  // ✅ UX: à chaque navigation on remonte en haut (sauf si back/forward du navigateur)
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition
    return { top: 0 }
  },
})

// -------------------- TITRE ONGLET --------------------
// Optionnel : Changer le titre de l'onglet automatiquement
router.beforeEach((to, from, next) => {
  // ✅ Petite sécurité : si un jour SSR, document peut ne pas exister
  if (typeof document !== 'undefined') {
    // ✅ Si tu veux un titre dynamique pour /posts/:slug
    if (to.name === 'post.show' && to.params?.slug) {
      document.title = `Communiqué - ${to.params.slug} | FAMa`
    } else {
      document.title = (to.meta && to.meta.title ? to.meta.title + ' | FAMa' : 'FAMa')
    }
  }
  next()
})

export default router