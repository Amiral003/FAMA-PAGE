<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Skeleton from 'primevue/skeleton'

// Import des images de l'accueil pour les inclure dans la galerie
import heroImg from '@/assets/images/hero.jpg'
import famaImg from '@/assets/images/fam.png'
import maliImg from '@/assets/images/fa.jpg'

const allPhotos = ref([])
const loading = ref(true)

// LOGIQUE D'IMAGE SÉCURISÉE
const getImageUrl = (path) => {
  if (!path) return '/placeholder-fama.jpg';
  // Si c'est une image importée via Vite (accueil), elle est déjà traitée
  if (path.startsWith('data:') || path.startsWith('/@fs') || path.startsWith('/src/assets')) return path;
  if (path.startsWith('http')) return path;

  return path.startsWith('storage/') ? `/${path}` : `/storage/${path}`;
}

const loadPhotos = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/posts')
    const posts = res.data.data || res.data

    // 1. On commence par mettre les photos de l'accueil
    const tempPhotos = [
      { id: 'hero', url: heroImg },
      { id: 'fama', url: famaImg },
      { id: 'mali', url: maliImg }
    ]

    // 2. On ajoute toutes les photos issues des posts (Flash, Actu, etc.)
    posts.forEach(post => {
      if (post.thumbnail) {
        tempPhotos.push({ id: `thumb-${post.id}`, url: post.thumbnail })
      }
      if (post.media && Array.isArray(post.media)) {
        post.media.forEach((m, idx) => {
          tempPhotos.push({ id: `media-${m.id || idx}-${post.id}`, url: m.file_path || m.path })
        })
      }
      if (post.image_path && post.image_path !== post.thumbnail) {
        tempPhotos.push({ id: `path-${post.id}`, url: post.image_path })
      }
    })

    // 3. Suppression des doublons
    const uniqueUrls = new Set()
    allPhotos.value = tempPhotos.filter(photo => {
      if (!photo.url || uniqueUrls.has(photo.url)) return false
      uniqueUrls.add(photo.url)
      return true
    })

  } catch (e) {
    console.error("Erreur Photothèque:", e)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadPhotos()
})
</script>

<template>
  <div class="photo-page">
    <div class="header-section">
      <h1><i class="pi pi-camera"></i>  PHOTOTHEQUE OFFICIELLE</h1>
      <div class="header-line"></div>
    </div>

    <div class="container">
      <div v-if="loading" class="photo-grid">
        <Skeleton v-for="i in 12" :key="i" height="280px" borderRadius="4px"></Skeleton>
      </div>

      <div v-else-if="allPhotos.length > 0" class="photo-grid">
        <div v-for="photo in allPhotos" :key="photo.id" class="photo-item">
          <img
            :src="getImageUrl(photo.url)"
            class="gallery-img"
            loading="lazy"
            @error="(e) => e.target.src = '/placeholder-fama.jpg'"
          />
          <div class="img-overlay">
            <i class="pi pi-search-plus"></i>
          </div>
        </div>
      </div>

      <div v-else class="empty-state">
        <p>Aucune image disponible.</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.photo-page {
  background-color: #162417; /* Fond vert sombre comme ton accueil */
  min-height: 100vh;
  padding-bottom: 50px;
}

.header-section {
  padding: 60px 20px;
  text-align: center;
  color: #ffca28;
}

.header-section h1 {
  font-size: 2.5rem;
  font-weight: 900;
  letter-spacing: 2px;
  margin-bottom: 15px;
}

.header-line {
  width: 80px;
  height: 4px;
  background: #ffca28;
  margin: 0 auto;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 20px;
}

/* Grille style Masonry/Instagram */
.photo-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 15px;
}

.photo-item {
  position: relative;
  height: 300px;
  overflow: hidden;
  border-radius: 4px;
  cursor: pointer;
  background: #1e2f1f;
  border: 1px solid rgba(255, 202, 40, 0.1);
}

.gallery-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.photo-item:hover .gallery-img {
  transform: scale(1.1);
}

/* Effet au survol (optionnel, juste une loupe) */
.img-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s;
}

.photo-item:hover .img-overlay {
  opacity: 1;
}

.img-overlay i {
  color: #ffca28;
  font-size: 2rem;
}

@media (max-width: 768px) {
  .photo-grid {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 8px;
  }
  .photo-item { height: 180px; }
}
</style>
