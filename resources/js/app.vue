<template>
  <Navbar />

  <main class="container">
    <router-view v-slot="{ Component }">
  <transition name="fade-slide" mode="out-in">
    <component :is="Component" />
  </transition>
</router-view>

  </main>
</template>

<script setup>
import Navbar from './components/Navbar.vue'
</script>

<style>
.container {
  max-width: 1100px;
  margin: auto;
  padding: 40px 20px;
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
