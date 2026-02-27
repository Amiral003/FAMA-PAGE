import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/home.vue'
import About from '../pages/About.vue'
import Contact from '../pages/Contact.vue'
import Portfolio from '../pages/portfolio.vue'
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
import Actualite from '../pages/Actualite.vue'
import Compos from '../pages/compos.vue'
import Photo from '../pages/photo.vue'




const routes = [
    { path: '/', component: Home,meta: { title: 'Accueil' },},
    { path: '/about', component: About },
    { path: '/contact', component: Contact },
    {path:'/etatmajor', component: EtatMajor},
    {path:'/EtatmajorAT', component: EtatmajorAT},
    {path:'/EtatmajorAA', component: EtatmajorAA},
    {path:'/Dttia', component: Dttia},
    {path:'/Dmhta', component: Dmhta},
     {path:'/Dcssa',component: Dcssa},
    {path:'/Gendarmerie', component:Gendarmerie},
    {path:'/Dgm',component:Dgm},
    {path:'/EtatmajorGarde',component:EtatmajorGarde},
     {path:'/police',component:Police},
    { path: '/portfolio', component: Portfolio,
   },
//    {path:'/portfolio',component:Portfolio},

//
// Change '/Phototheque' en '/phototheque' (tout en minuscule)
{ path: '/phototheque', component: Photo, name: 'phototheque' },
   //{path:'/flahinfo',component:FlashInfo},


   {path:'/Compos',component:Compos},

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
// Optionnel : Changer le titre de l'onglet automatiquement
router.beforeEach((to, from, next) => {
    document.title = to.meta.title || 'FAMali'
    next()
})


export default router;
