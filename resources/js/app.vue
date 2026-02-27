<template>

  <header class="fixed-top-section">
    <FlashInfo />
    <Navbar />
  </header>



  <main class="container page-content">

    <router-view :key="$route.fullPath" v-slot="{ Component }">
  <transition name="fade-slide" mode="out-in">
    <component :is="Component" />
  </transition>
</router-view>

  </main>
</template>

<script setup>
import Navbar from './components/Navbar.vue'
import FlashInfo from './pages/FlashInfo.vue'
</script>

<style>
/* Couleurs par défaut (Dark/Military) */
:root, [data-theme='default'] {
  --bg-primary: #0a0f0a;
  --bg-secondary: #1a241b;
  --bg-nav: #1a241b;
  --text: #cbd5e1;
  --accent: #991b1b; /* Rouge FAMa */
  --gold: #FFD700;
}

/* Thème Clair */
[data-theme='light'] {
  --bg-primary: #f1f5f9;
  --bg-secondary: #ffffff;
  --bg-nav: #064e3b; /* On garde un vert foncé pour la nav même en clair */
  --text: #1e293b;
  --accent: #ce1126;
  --gold: #b29400;
}

/* Thème Sombre Profond */
[data-theme='dark'] {
  --bg-primary: #000000;
  --bg-secondary: #111111;
  --bg-nav: #000000;
  --text: #ffffff;
  --accent: #ff0000;
  --gold: #FFD700;
}
body {
  margin: 0;
  background-color: #0d1117; /* Le fond noir/bleu de ta 1ère capture */
}
.container {
  max-width: 1100px;
  margin: auto;
  padding: 40px 20px;
}
.fixed-top-section {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: 3000;
}
.page-content {
  margin-top: 130px !important;
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
