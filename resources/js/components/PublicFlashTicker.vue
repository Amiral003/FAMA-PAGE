<script setup>
import { ref, onMounted, onBeforeUnmount, watchEffect } from 'vue'
import axios from 'axios'

const flashes = ref([])
const TICKER_HEIGHT = 40

const setTickerClass = (enabled) => {
  document.documentElement.classList.toggle('has-ticker', enabled)
}

const getFlashText = (item) => {
  return item?.content?.trim() || item?.title?.trim() || ''
}

onBeforeUnmount(() => setTickerClass(false))

onMounted(async () => {
  try {
    const res = await axios.get('/api/posts/flashes', {
      params: { limit: 25 },
    })

    flashes.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    console.error('Erreur lors de la récupération des flashs:', e)
    flashes.value = []
  }
})

watchEffect(() => {
  setTickerClass(flashes.value.length > 0)
})
</script>

<template>
  <div
    v-if="flashes.length > 0"
    class="ticker-container"
    :style="{ height: TICKER_HEIGHT + 'px' }"
  >
    <div class="ticker-label">FLASH INFO</div>

    <div class="ticker-content" :style="{ paddingLeft: '140px' }">
      <div class="ticker-track">
        <!-- 1ère boucle -->
        <span
          v-for="item in flashes"
          :key="item.id"
          class="ticker-item"
        >
          <router-link :to="`/posts/${item.slug}`" class="flash-link">
            <i class="pi pi-bolt"></i>
            <strong class="flash-title">{{ getFlashText(item) }}</strong>
          </router-link>
        </span>

        <!-- duplication -->
        <span
          v-for="item in flashes"
          :key="'dup-' + item.id"
          class="ticker-item"
        >
          <router-link :to="`/posts/${item.slug}`" class="flash-link">
            <i class="pi pi-bolt"></i>
            <strong class="flash-title">{{ getFlashText(item) }}</strong>
          </router-link>
        </span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.ticker-container {
  display: flex;
  background: #064e3b;
  color: white;
  overflow: hidden;
  width: 100%;
  align-items: center;
  border-bottom: 2px solid #2c2a28;
  position: sticky;
  top: 0;
  z-index: 2001;
}

.ticker-label {
  background: #991b1b;
  padding: 0 18px;
  height: 100%;
  display: flex;
  align-items: center;
  font-weight: 900;
  font-size: 0.8rem;
  text-transform: uppercase;
  position: absolute;
  left: 0;
  z-index: 10;
  box-shadow: 8px 0 15px rgba(0, 0, 0, 0.45);
}

.ticker-content {
  width: 100%;
  display: flex;
  align-items: center;
}

.ticker-track {
  display: flex;
  white-space: nowrap;
  width: max-content;
  animation: scroll-left 28s linear infinite;
}

.ticker-content:hover .ticker-track {
  animation-play-state: paused;
  cursor: pointer;
}

.ticker-item {
  padding: 0 30px;
  display: flex;
  align-items: center;
}

.ticker-item::after {
  content: "///";
  margin-left: 30px;
  color: rgba(255, 255, 255, 0.25);
}

.flash-link {
  text-decoration: none;
  color: inherit;
  display: flex;
  align-items: center;
  gap: 8px;
}

.flash-title {
  color: #ffca28;
  font-size: 0.9rem;
  text-transform: uppercase;
}

@keyframes scroll-left {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}
</style>