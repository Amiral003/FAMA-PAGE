<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const flashes = ref([])

onMounted(async () => {
  try {
    const res = await axios.get('/api/posts')
    const allPosts = res.data.data || res.data

    const vingtQuatreHeures = 24 * 60 * 60 * 1000
    const maintenant = Date.now()

    // 1. Filtrer uniquement les posts de type 'flash' et ayant le statut 'publie'
    const onlyFlashes = allPosts.filter(p =>
      p.type?.toLowerCase() === 'flash' &&
      p.status === 'publie'
    )

    // 2. Filtre STRICT : on ne garde que ceux de moins de 24h
    const filtered = onlyFlashes.filter(p => {
      const datePost = new Date(p.created_at).getTime()
      return (maintenant - datePost) < vingtQuatreHeures
    })

    // 3. Tri du plus récent au plus ancien
    filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))

    // 4. Mise à jour de la variable (si vide, le v-if cachera la barre)
    flashes.value = filtered

  } catch (e) {
    console.error("Erreur lors de la récupération des flashs:", e)
  }
})
</script>

<template>
  <div v-if="flashes.length > 0" class="ticker-container">
    <div class="ticker-label">FLASH INFO</div>

    <div class="ticker-content">
      <div class="ticker-track">
        <span v-for="item in flashes" :key="item.id" class="ticker-item">
          <router-link :to="`/posts/${item.slug}`" class="flash-link">
            <i class="pi pi-bolt"></i>
            <strong class="flash-title">{{ item.title }}</strong>
          </router-link>
        </span>
         <span v-for="item in flashes" :key="'dup-'+item.id" class="ticker-item">
          <router-link :to="`/posts/${item.slug}`" class="flash-link">
            <i class="pi pi-bolt"></i>
            <strong class="flash-title">{{ item.title }}</strong>
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
  height: 40px;
  width: 100vw;
  align-items: center;
  border-bottom: 2px solid #2c2a28;
  position: sticky;
  top: 0;
  z-index: 2001;
}

.ticker-label {
  background: #991b1b;
  padding: 0 25px;
  height: 100%;
  display: flex;
  align-items: center;
  font-weight: 900;
  font-size: 0.85rem;
  text-transform: uppercase;
  position: absolute;
  left: 0;
  z-index: 10;
  box-shadow: 8px 0 15px rgba(0,0,0,0.5);
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
  /* padding-left: 100%; */
  animation: scroll-left 30s linear infinite;
}

.ticker-content:hover .ticker-track {
  animation-play-state: paused;
  cursor: pointer;
}

.ticker-item {
  padding: 0 50px;
  display: flex;
  align-items: center;
}

.ticker-item::after {
  content: "///";
  margin-left: 60px;
  color: rgba(255, 255, 255, 0.3);
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
  font-size: 0.95rem;
  text-transform: uppercase;
}

@keyframes scroll-left {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}
</style>
