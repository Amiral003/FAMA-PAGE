<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import Skeleton from 'primevue/skeleton'
import { formatDistanceToNow } from 'date-fns'
import { fr } from 'date-fns/locale'

const router = useRouter()
const flashes = ref([])
const loading = ref(true)

// --- 1. LOGIQUE D'IMAGE DU PORTFOLIO (Celle qui marche chez toi) ---
const getPostImage = (post) => {
  if (!post) return '/placeholder-fama.jpg';

  // On teste les différents champs possibles comme dans ton portfolio
  if (post.thumbnail) return `/storage/${post.thumbnail}`;
  if (post.media?.length > 0) return `/storage/${post.media[0].file_path}`;
  if (post.image_path) return `/storage/${post.image_path}`;

  return '/placeholder-fama.jpg';
}

const getRelativeDate = (date) => {
  if (!date) return ''
  try {
    return formatDistanceToNow(new Date(date), { addSuffix: true, locale: fr })
  } catch (e) {
    return "Récemment"
  }
}

// --- 2. CHARGEMENT DE TOUS LES FLASHS (Sans limite de temps) ---
const loadFlashes = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/posts')
    const fetchedData = res.data.data || res.data

    if (Array.isArray(fetchedData)) {
      // On prend TOUS les flashs publiés, sans la restriction des 24h
      flashes.value = fetchedData
        .filter(p => p.type?.toLowerCase() === 'flash' && p.status === 'publie')
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    }
  } catch (e) {
    console.error("Erreur API Flash:", e)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadFlashes()
})
</script>

<template>
  <div class="flash-page-container">
    <div class="header-banner">
      <h1><i class="pi pi-bolt"></i> TOUS LES FLASH INFOS</h1>
      <p>Retrouvez l'historique complet des alertes des FAMa</p>
    </div>

    <div class="container">
      <div v-if="loading" class="flash-grid">
        <div v-for="i in 3" :key="i" class="skeleton-card">
          <Skeleton height="280px" borderRadius="16px"></Skeleton>
        </div>
      </div>

      <div v-else-if="flashes.length > 0" class="flash-grid">
        <article
          v-for="post in flashes"
          :key="post.id"
          class="flash-item-card"
          @click="router.push(`/posts/${post.slug}`)"
        >
          <div class="image-wrapper">
            <img
              :src="getPostImage(post)"
              class="flash-img"
              loading="lazy"
              @error="(e) => e.target.src = '/placeholder-fama.jpg'"
            />
            <div class="overlay-date">
              <i class="pi pi-clock"></i> {{ getRelativeDate(post.published_at || post.created_at) }}
            </div>
          </div>

          <div class="content-box">
            <h2 class="flash-title">{{ post.title }}</h2>
            <span class="read-more">Consulter le détail <i class="pi pi-arrow-right"></i></span>
          </div>
        </article>
      </div>

      <div v-else class="empty-state">
        <i class="pi pi-info-circle"></i>
        <p>Aucun flash info trouvé dans la base de données.</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Couleurs inspirées de ta charte FAMa */
.flash-page-container { background: #f1f5f9; min-height: 100vh; padding-bottom: 60px; }
.header-banner { background: linear-gradient(135deg, #064e3b 0%, #022c22 100%); padding: 50px 20px; text-align: center; color: white; margin-bottom: 40px; }
.header-banner h1 { color: #ffca28; font-weight: 900; margin-bottom: 10px; }

.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.flash-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; }

.flash-item-card { background: white; border-radius: 16px; overflow: hidden; cursor: pointer; transition: 0.3s ease; border: 1px solid #e2e8f0; }
.flash-item-card:hover { transform: translateY(-8px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); border-color: #064e3b; }

.image-wrapper { position: relative; height: 250px; background: #cbd5e1; }
.flash-img { width: 100%; height: 100%; object-fit: cover; }

.overlay-date { position: absolute; top: 15px; right: 15px; background: rgba(0,0,0,0.75); color: #ffca28; padding: 6px 15px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; }

.content-box { padding: 20px; }
.flash-title { font-size: 1.15rem; font-weight: 800; color: #1e293b; line-height: 1.5; margin-bottom: 15px; }
.read-more { color: #064e3b; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 5px; }

.empty-state { text-align: center; padding: 100px; color: #64748b; }
</style>
