<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isMenuOpen = ref(false)

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

const closeMenu = () => {
  isMenuOpen.value = false
}
</script>

<template>
  <nav class="navbar-global">
    <div class="vigilance-bar">
      <div class="nav-container">
        <div class="vigilance-content">
          <span class="pulse-dot"></span>
          <span class="vigilance-text">
            <strong class="label">VIGILANCE :</strong>
            <a href="tel:80001111" class="phone-link">80 00 11 11</a>
          </span>
        </div>
      </div>
    </div>

    <div class="nav-main">
      <div class="nav-container main-flex">
        <div class="logo" @click="router.push('/'); closeMenu()">
          FAM<span class="red">A</span> <small class="logo-sub">OFFICIEL</small>
        </div>

        <button class="menu-toggle" @click="toggleMenu" :aria-expanded="isMenuOpen" aria-label="Menu">
          <div class="hamburger-icon" :class="{ 'is-active': isMenuOpen }">
            <span></span>
            <span></span>
            <span></span>
          </div>
        </button>

        <ul class="nav-links" :class="{ 'is-open': isMenuOpen }">
          <li><router-link to="/" @click="closeMenu">Accueil</router-link></li>
          <li><router-link to="/portfolio" @click="closeMenu">Communiqués</router-link></li>
          <li><router-Link to ="/etatmajor" @click="closeMenu">Etat Major</router-link></li>
          <li><router-link to="/contact" @click="closeMenu">Contact</router-link></li>
        <li><router-link to="/about" @click="closeMenu">À propos</router-link></li>
        </ul>
      </div>
    </div>

    <div v-if="isMenuOpen" class="menu-overlay" @click="closeMenu"></div>
  </nav>
</template>

<style scoped>
.navbar-global {
  width: 100%;
  position: sticky;
  top: 0;
  z-index: 3000;
  background: white;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

/* Bandeau Vigilance */
.vigilance-bar {
  background: #1a1c1e;
  color: white;
  padding: 10px 0;
  border-bottom: 2px solid #ce1126;
}

.vigilance-content {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  font-size: 0.9rem;
}

.phone-link {
  color: #ff3e3e;
  text-decoration: none;
  font-weight: 900;
  font-size: 1.1rem;
}

.pulse-dot {
  width: 10px;
  height: 10px;
  background: #ff3e3e;
  border-radius: 50%;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(255, 62, 62, 0.7); }
  70% { box-shadow: 0 0 0 10px rgba(255, 62, 62, 0); }
  100% { box-shadow: 0 0 0 0 rgba(255, 62, 62, 0); }
}

/* Navigation */
.main-flex {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 80px;
}

.logo {
  font-size: 1.8rem;
  font-weight: 900;
  cursor: pointer;
  display: flex;
  align-items: baseline;
  gap: 5px;
}
.logo-sub { font-size: 0.7rem; color: #666; font-weight: 400; }
.red { color: #ce1126; }

.nav-links {
  display: flex;
  gap: 35px;
  list-style: none;
  margin: 0;
  padding: 0;
}

.nav-links a {
  text-decoration: none;
  color: #1a1c1e;
  font-weight: 700;
  font-size: 1rem;
  transition: 0.3s;
  text-transform: uppercase;
display: block;
width: 100%;
}

.nav-links a:hover, .nav-links a.router-link-active {
  color: #ce1126;
}

/* BOUTON HAMBURGER (VISIBILITÉ CORRIGÉE) */
.menu-toggle {
  display: none;
  position: relative;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 10px;
  z-index: 3100;
}

.hamburger-icon {
  width: 30px;
  height: 20px;
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.hamburger-icon span {
  display: block;
  width: 100%;
  height: 3px;
  background-color: #1a1c1e;
  border-radius: 3px;
  transition: all 0.3s ease;
}

/* Animation vers "X" */
.hamburger-icon.is-active span:nth-child(1) { transform: translateY(8.5px) rotate(45deg); }
.hamburger-icon.is-active span:nth-child(2) { opacity: 0; }
.hamburger-icon.is-active span:nth-child(3) { transform: translateY(-8.5px) rotate(-45deg); }

/* MOBILE RESPONSIVE */
@media (max-width: 850px) {
  .menu-toggle { display:block;
background: transparent;
  border: none;
  cursor: pointer;
  padding: 10px;
  z-index: 3100;}

  .vigilance-text { font-size: 0.8rem; }
  .label { display: none; } /* Cache "VIGILANCE NATIONALE" sur très petit écran */

  .nav-links {
    position: fixed;
    top: 0;
    right: -100%; /* Caché à droite */
    width: 280px;
    height: 100vh;
    background: white;
    flex-direction: column;
    padding: 100px 40px;
    box-shadow: -5px 0 15px rgba(0,0,0,0.1);
    transition: 0.4s ease-in-out;
    z-index: 3100;
  }

  .nav-links.is-open {
    right: 0; /* Affiche le menu */
    visibility:visible /*Activation de visibilté @t*/
  }

  .nav-links li {
    width: 100%;
    border-bottom: 1px solid #eee;
    padding: 15px 0;
  }

  .menu-overlay {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 2500;
  }
}
</style>
