<template>
 <div class="header-wrapper">
    <PublicFlashTicker />
    <Navbar />
  </div>
  <main class="container">
    <router-view v-slot="{ Component }">
  <transition name="fade-slide" mode="out-in">
    <component :is="Component" />
  </transition>
</router-view>

  </main>
</template>

<script setup>
import PublicFlashTicker from './components/PublicFlashTicker.vue'
import Navbar from './components/Navbar.vue'
</script>

<style>
/* On fixe le wrapper global, pas les composants individuellement */
.header-wrapper {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
}
.container {
  max-width: 1100px;
  margin: 0 auto;
  /* Augmente cette valeur si ça touche encore.
     Elle doit être supérieure à la hauteur cumulée du Ticker + Navbar */
  margin-top: 140px;
  padding: 20px;
}
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.35s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

</style>
