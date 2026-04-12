<template>
  <div class="app-shell">
    <div class="header-wrapper">
      <PublicFlashTicker />
      <Navbar />
    </div>

    <main class="site-main">
      <router-view v-slot="{ Component, route }">
        <transition name="fade-slide" mode="out-in">
          <component :is="Component" :key="route.fullPath" />
        </transition>
      </router-view>
    </main>
  </div>
</template>

<script setup>
import PublicFlashTicker from './components/PublicFlashTicker.vue'
import Navbar from './components/navbar.vue'
</script>

<style>
:root {
  --header-offset-desktop: 120px;
  --header-offset-tablet: 110px;
  --header-offset-mobile: 100px;
}

/* Reset global propre */
*,
*::before,
*::after {
  box-sizing: border-box;
}

html,
body,
#app {
  margin: 0;
  padding: 0;
  width: 100%;
  min-height: 100%;
  overflow-x: hidden;
}

body {
  overflow-x: hidden;
}

img,
video,
iframe {
  max-width: 100%;
  height: auto;
  display: block;
}

a,
button,
input,
textarea,
select {
  max-width: 100%;
}

.app-shell {
  width: 100%;
  min-height: 100vh;
  overflow-x: hidden;
}

/* Header fixe */
.header-wrapper {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  width: 100%;
  z-index: 1000;
}

/* Contenu global */
.site-main {
  width: 100%;
  min-height: 100vh;
  padding-top: var(--header-offset-desktop);
  overflow-x: hidden;
}

/* Animation plus douce et simple */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(8px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

/* Tablette */
@media (max-width: 1024px) {
  .site-main {
    padding-top: var(--header-offset-tablet);
  }
}

/* Mobile */
@media (max-width: 768px) {
  .site-main {
    padding-top: var(--header-offset-mobile);
  }
}

/* Petit mobile */
@media (max-width: 480px) {
  .site-main {
    padding-top: 92px;
  }
}
</style>