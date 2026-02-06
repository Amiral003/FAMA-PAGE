
<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import Button from 'primevue/button'

const router = useRouter()
const isMenuOpen = ref(false)
const isDropdownOpen = ref(false) // Pour le menu Catégories

const navItems = [
  { label: 'Accueil', to: '/' },
  { label: 'Communiqués', to: '/portfolio' },

 // { label: 'À propos', to: '/about' },
]

const categories = [
   { label: "Armée de Terre", to: '/EtatmajorAT' },
     { label: "Armée de l'Air", to: '/EtatmajorAA' },
    { label: "Garde Nationale", to: '/EtatmajorGarde' },
      { label: "D.T.T.I.A", to: '/Dttia' },
      { label: "D.M.H.T.A", to: '/Dmhta' },
      {label:"D.C.S.S.A",to:'/Dcssa'},
      { label: "Génie Militaire", to: '/Dgm' },
      { label: "Gendarmerie Nationale", to: '/Gendarmerie' },
      {label:"Police Nationale",to:'/police'},
]
const contactItem=  { label: 'Contact', to: '/contact' };
const aboutItem = { label: 'À propos', to: '/about' }
</script>

<template>
  <nav class="navbar-tactical">
    <div class="mali-stripe">
      <div class="s-green"></div><div class="s-yellow"></div><div class="s-red"></div>
    </div>

    <div class="nav-container">
      <div class="nav-content">
        <div class="logo-box" @click="router.push('/')" style="cursor: pointer;">
          <span class="fama">FAM<span class="gold">a</span></span>
          <span class="sub">ÉTAT-MAJOR GÉNÉRAL</span>
        </div>

        <ul class="nav-links">
          <li v-for="item in navItems" :key="item.to">
            <router-link :to="item.to">{{ item.label }}</router-link>
          </li>

          <li class="dropdown-pc" @mouseenter="isDropdownOpen = true" @mouseleave="isDropdownOpen = false">
            <a href="#" class="dropdown-trigger">
              ÉTAT-MAJOR <i class="pi pi-chevron-down"></i>
            </a>
            <transition name="fade-slide">
              <ul v-if="isDropdownOpen" class="dropdown-menu">
                <li v-for="cat in categories" :key="cat.to">
                  <router-link :to="cat.to">{{ cat.label }}</router-link>
                </li>
              </ul>
            </transition>
          </li>
          <li><router-link :to="contactItem.to">{{ contactItem.label}}</router-link></li>
           <li>
    <router-link :to="aboutItem.to">{{ aboutItem.label }}</router-link>
  </li>

        </ul>

        <Button icon="pi pi-bars" class="menu-mobile-btn" text @click="isMenuOpen = true" />
      </div>
    </div>

    <div class="mobile-wrapper" :class="{ 'is-active': isMenuOpen }" @click="isMenuOpen = false">
      <div class="side-menu" @click.stop>
        <div class="menu-header">
           <div class="logo-box"><span class="fama">FAM<span class="gold">A</span></span></div>
           <Button icon="pi pi-times" class="close-btn" text @click="isMenuOpen = false" />
        </div>

        <div class="menu-items">
          <router-link v-for="item in navItems" :to="item.to" class="mobile-link" @click="isMenuOpen = false">
            {{ item.label }}
          </router-link>

          <div class="mobile-category-section">

            <div class="mobile-link" @click="isDropdownOpen = !isDropdownOpen">
              État-Major
              <i :class="isDropdownOpen ? 'pi pi-chevron-up' : 'pi pi-chevron-down'"></i>
            </div>
            <div v-if="isDropdownOpen" class="mobile-sub-menu">
              <router-link v-for="cat in categories" :to="cat.to" class="sub-link" @click="isMenuOpen = false">
                {{ cat.label }}
              </router-link>
            </div>
          </div>
               <!-- À PROPOS -->
      <router-link
        :to="aboutItem.to"
        class="mobile-link"
        @click="isMenuOpen = false"
      >
        {{ aboutItem.label }}
      </router-link>

      <!-- CONTACT -->
      <router-link
        :to="contactItem.to"
        class="mobile-link"
        @click="isMenuOpen = false"
      >
        {{ contactItem.label }}
      </router-link>
        </div>

        <div class="menu-footer">
          <p>SÉCURITÉ - UNITÉ - SOUVERAINETÉ</p>
        </div>
      </div>
    </div>
  </nav>
</template>

<style scoped>
/* --- Styles de base PC --- */
.navbar-tactical { width: 100%; position: sticky; top: 0; z-index: 1000; background: #1a241b; border-bottom: 1px solid rgba(255, 215, 0, 0.2); }
.mali-stripe { display: flex; height: 4px; }
.s-green { background: #14B82C; flex: 1; }
.s-yellow { background: #FFD700; flex: 1; }
.s-red { background: #CE1126; flex: 1; }
.nav-container { max-width: 1300px; margin: 0 auto; padding: 0 2rem; }
.nav-content { display: flex; justify-content: space-between; align-items: center; height: 80px; }
.fama { font-size: 1.8rem; font-weight: 900; color: #FFD700; letter-spacing: 2px; }
.gold { color: #FFD700; }
.sub { display: block; font-size: 0.6rem; color: #cbd5e1; letter-spacing: 3px; margin-top: -8px; }

.nav-links { display: flex; gap: 35px; list-style: none; align-items: center; }
.nav-links a { color: #cbd5e1; text-decoration: none; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; transition: 0.3s; }
.nav-links a:hover, .nav-links a.router-link-active { color: #FFD700; }

/* --- STYLE DROPDOWN PC --- */
.dropdown-pc { position: relative; cursor: pointer; }
.dropdown-trigger i { font-size: 0.7rem; margin-left: 5px; transition: 0.3s; }
.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  background: #243125;
  min-width: 180px;
  list-style: none;
  padding: 10px 0;
  border: 1px solid rgba(255, 215, 0, 0.2);
  box-shadow: 0 10px 15px rgba(0,0,0,0.3);
}
.dropdown-menu li a {
  display: block;
  padding: 10px 20px;
  font-size: 0.8rem;
  color: #fff;
}
.dropdown-menu li a:hover { background: rgba(255, 215, 0, 0.1); color: #FFD700; }

/* Animation dropdown */
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.3s ease; }
.fade-slide-enter-from, .fade-slide-leave-to { opacity: 0; transform: translateY(10px); }

/* --- STYLE MOBILE --- */
.mobile-wrapper { display: none; }
.menu-mobile-btn { display: none; color: #FFD700 !important; }

@media (max-width: 992px) {
  .nav-links { display: none !important; }
  .menu-mobile-btn { display: block; }
  .mobile-wrapper { display: block; position: fixed; top: 0; left: 0; width: 100%; height: 100vh; z-index: 2000; visibility: hidden; opacity: 0; transition: 0.4s; background: rgba(0, 0, 0, 0.8); }
  .mobile-wrapper.is-active { visibility: visible; opacity: 1; }
  .side-menu { position: absolute; right: -300px; top: 0; width: 280px; height: 100%; background: #1a241b; transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; }
  .mobile-wrapper.is-active .side-menu { right: 0; }
  .menu-header { padding: 2rem; display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255, 215, 0, 0.1); }
  .menu-items { padding: 1rem 0; overflow-y: auto; }
  .mobile-link { padding: 1.2rem 2rem; color: #fff; text-decoration: none; font-size: 1.1rem; font-weight: 700; display: flex; justify-content: space-between; cursor: pointer; }

  /* Sous-menu mobile */
  .mobile-sub-menu { background: rgba(0, 0, 0, 0.2); padding-left: 1rem; }
  .sub-link { display: block; padding: 1rem 2rem; color: #cbd5e1; text-decoration: none; font-size: 0.95rem; border-left: 2px solid rgba(255, 215, 0, 0.3); }
  .sub-link:hover { color: #FFD700; }

  .menu-footer { margin-top: auto; padding: 2rem; text-align: center; color: #666; font-size: 0.6rem; border-top: 1px solid rgba(255, 215, 0, 0.1); }
}
</style>
