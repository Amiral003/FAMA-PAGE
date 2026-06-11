<script setup>
import { useHead } from '@unhead/vue'
import { useRouter } from 'vue-router'
import { ref, onMounted, onBeforeUnmount, nextTick, watch } from 'vue'
import axios from 'axios'

import InputText from 'primevue/inputtext'
import Skeleton from 'primevue/skeleton'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'

const router = useRouter()

useHead({
  title: 'Galerie Photo | FAMa',
  meta: [
    { name: 'description', content: "Galerie photo officielle des Forces Armées Maliennes (FAMa)" },
    { property: 'og:type', content: 'website' },
    { property: 'og:title', content: 'Galerie Photo | FAMa' },
    { property: 'og:description', content: "Découvrez les images officielles des FAMa" },
    { name: 'twitter:card', content: 'summary_large_image' },
  ],
})

const photos = ref([])
const search = ref('')
const loading = ref(true)
const loadingMore = ref(false)
const page = ref(1)
const perPage = ref(24)
const hasMore = ref(true)
const totalPhotosCount = ref(0)

const showModal = ref(false)
const currentPhoto = ref(null)

const sentinel = ref(null)
let observer = null
let tmr = null

const downloadPhoto = async (photo) => {
  try {
    const response = await fetch(photo.src)
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)

    const link = document.createElement('a')
    link.href = url
    link.download = photo.filename || `fama_photo_${photo.id}.jpg`

    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)

    window.URL.revokeObjectURL(url)
  } catch (error) {
    window.open(photo.src, '_blank')
  }
}

const openPhoto = (photo) => {
  if (photo.failed) return

  currentPhoto.value = photo
  showModal.value = true
}

const goToPost = (slug) => {
  router.push(`/posts/${slug}`)
}

const fetchPhotos = async () => {
  if (!hasMore.value || loadingMore.value) return

  loadingMore.value = true

  try {
    const res = await axios.get('/api/posts/photos', {
      params: {
        page: page.value,
        per_page: perPage.value,
        q: search.value.trim() || undefined,
      },
    })

    const payload = res.data
    const posts = payload?.data ?? payload ?? []
    const newPhotos = []
    const sizes = ['portrait', 'landscape', 'square', 'portrait', 'landscape']

    for (const post of posts) {
      const media = Array.isArray(post.media) ? post.media : []

      const postPhotos = media
        .filter((m) => m?.file_path)
        .map((m, idx) => {
          const filePath = m.file_path
          const fileName = filePath.split('/').pop()

          return {
            id: `${post.id}-${m.id ?? idx}`,
            src: `/storage/${filePath}`,
            alt: post.title,
            postSlug: post.slug,
            postTitle: post.title,
            caption: m.caption || post.excerpt || null,
            date: post.published_at || post.created_at,
            filename: fileName,
            failed: false,
            size: sizes[(Number(post.id) + idx) % sizes.length],
          }
        })

      newPhotos.push(...postPhotos)
    }

    if (page.value === 1) {
      photos.value = newPhotos
    } else {
      photos.value = [...photos.value, ...newPhotos]
    }

    totalPhotosCount.value = photos.value.length
    hasMore.value = !!payload.next_page_url
    page.value += 1
  } catch (e) {
    console.error('Erreur chargement photos:', e)
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

const resetAndReload = async () => {
  page.value = 1
  hasMore.value = true
  loading.value = true
  photos.value = []
  totalPhotosCount.value = 0

  await fetchPhotos()
}

const setupObserver = () => {
  if (!sentinel.value) return

  observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting) {
        fetchPhotos()
      }
    },
    {
      root: null,
      threshold: 0.1,
      rootMargin: '200px',
    }
  )

  observer.observe(sentinel.value)
}

onMounted(async () => {
  await fetchPhotos()
  await nextTick()
  setupObserver()
})

onBeforeUnmount(() => {
  if (observer && sentinel.value) {
    observer.unobserve(sentinel.value)
  }

  if (tmr) {
    clearTimeout(tmr)
  }

  observer = null
})

watch(search, () => {
  clearTimeout(tmr)

  tmr = setTimeout(() => {
    resetAndReload()
  }, 350)
})
</script>

<template>
  <div class="gallery-page">
    <div class="container">
      <header class="hero-banner">
        <div class="hero-content">
          <div class="hero-pill">
            <i class="pi pi-images"></i>
            <span>Galerie officielle FAMa</span>
          </div>

          <h1 class="page-title">Galerie Photo</h1>

          <p class="page-subtitle">
            Explorez les moments forts des Forces Armées Maliennes
          </p>

          <div class="stats-row">
            <div class="stat-box">
              <strong>{{ totalPhotosCount }}</strong>
              <span>Photos chargées</span>
            </div>
          </div>
        </div>

        <div class="hero-tools">
          <div class="search-wrapper">
            <i class="pi pi-search"></i>

            <InputText
              v-model="search"
              placeholder="Rechercher..."
              class="search-input"
            />
          </div>
        </div>
      </header>

      <div v-if="loading" class="photo-grid-skeleton">
        <div v-for="i in 12" :key="i" class="photo-skeleton">
          <Skeleton width="100%" height="100%" class="skeleton-img" />
        </div>
      </div>

      <div v-else>
        <div class="photo-grid-masonry">
          <div
            v-for="photo in photos"
            :key="photo.id"
            :class="['photo-item', `photo-${photo.size}`]"
          >
            <div class="photo-container">
              <img
                v-if="!photo.failed"
                :src="photo.src"
                :alt="photo.alt"
                class="photo-img"
                loading="lazy"
                @click="openPhoto(photo)"
                @error="photo.failed = true"
              />

              <div v-else class="image-error">
                <i class="pi pi-image"></i>
                <span>Image indisponible</span>
              </div>

              <div v-if="!photo.failed" class="photo-overlay">
                <div class="overlay-buttons">
                  <button
                    class="overlay-btn download-btn"
                    @click.stop="downloadPhoto(photo)"
                    title="Télécharger"
                  >
                    <i class="pi pi-download"></i>
                  </button>

                  <button
                    class="overlay-btn view-btn"
                    @click.stop="openPhoto(photo)"
                    title="Agrandir"
                  >
                    <i class="pi pi-search-plus"></i>
                  </button>
                </div>

                <div class="overlay-caption" v-if="photo.caption">
                  <span>{{ photo.caption }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="loadingMore" class="loading-more">
          <div class="spinner">
            <i class="pi pi-spin pi-spinner"></i>
            <span>Chargement...</span>
          </div>
        </div>

        <div ref="sentinel" class="sentinel"></div>

        <div v-if="!loading && !loadingMore && !photos.length" class="empty-state">
          <i class="pi pi-images"></i>
          <h3>Aucune photo trouvée</h3>
          <p>Essayez une autre recherche.</p>
        </div>

        <div v-if="!loading && !loadingMore && !hasMore && photos.length" class="end-feed">
          <i class="pi pi-check-circle"></i>
          <span>Fin de la galerie — {{ totalPhotosCount }} photos chargées</span>
        </div>
      </div>
    </div>

    <Dialog
      v-model:visible="showModal"
      :modal="true"
      :closable="true"
      :closeOnEscape="true"
      class="photo-modal"
      :style="{ width: '90vw', maxWidth: '1200px' }"
      :header="currentPhoto?.postTitle"
    >
      <div v-if="currentPhoto" class="modal-content">
        <div class="modal-image">
          <img :src="currentPhoto.src" :alt="currentPhoto.alt" />
        </div>

        <div class="modal-info">
          <div v-if="currentPhoto.caption" class="modal-caption">
            <i class="pi pi-comment"></i>
            <p>{{ currentPhoto.caption }}</p>
          </div>

          <div class="modal-actions">
            <Button
              label="Voir l'article"
              icon="pi pi-arrow-right"
              class="p-button-success"
              @click="goToPost(currentPhoto.postSlug)"
            />

            <Button
              label="Télécharger"
              icon="pi pi-download"
              class="p-button-outlined"
              @click="downloadPhoto(currentPhoto)"
            />
          </div>
        </div>
      </div>
    </Dialog>
  </div>
</template>

<style scoped>
.gallery-page {
  min-height: 100vh;
  padding: 32px 0 60px;
  background: linear-gradient(180deg, #f8fafc 0%, #eef3f8 100%);
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 16px;
}

.hero-banner {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  flex-wrap: wrap;
  gap: 20px;
  margin-bottom: 32px;
  padding: 28px;
  border-radius: 28px;
  background: rgba(255, 255, 255, 0.92);
  border: 1px solid rgba(226, 232, 240, 0.9);
  backdrop-filter: blur(12px);
}

.hero-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 14px;
  padding: 6px 14px;
  border-radius: 999px;
  background: rgba(20, 184, 44, 0.12);
  color: #15803d;
  font-size: 0.85rem;
  font-weight: 900;
}

.page-title {
  margin: 0 0 10px;
  font-size: 2.3rem;
  color: #0f172a;
  font-weight: 950;
}

.page-subtitle {
  margin: 0 0 16px;
  color: #475569;
}

.stat-box {
  display: inline-flex;
  flex-direction: column;
  padding: 8px 16px;
  border-radius: 18px;
  background: rgba(248, 250, 252, 0.95);
  border: 1px solid #e2e8f0;
}

.stat-box strong {
  font-size: 1.3rem;
  font-weight: 900;
  color: #0f172a;
}

.stat-box span {
  font-size: 0.8rem;
  color: #64748b;
}

.search-wrapper {
  position: relative;
  width: 280px;
}

.search-wrapper i {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
}

.search-input {
  width: 100%;
  height: 44px;
  padding-left: 38px !important;
  border-radius: 999px !important;
  border: 1px solid #dbe4ee !important;
  background: #fff !important;
}

/* Skeleton stable */
.photo-grid-skeleton {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 20px;
}

.photo-skeleton {
  border-radius: 16px;
  overflow: hidden;
  background: #e2e8f0;
  aspect-ratio: 4 / 3;
}

.skeleton-img {
  width: 100%;
  height: 100%;
}

/* Masonry style Pinterest */
.photo-grid-masonry {
  column-count: 4;
  column-gap: 20px;
}

.photo-item {
  position: relative;
  display: inline-block;
  width: 100%;
  margin-bottom: 20px;
  break-inside: avoid;
  page-break-inside: avoid;
  border-radius: 16px;
  overflow: hidden;
  background: #f1f5f9;
  cursor: pointer;
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
}

.photo-container {
  position: relative;
  width: 100%;
  overflow: hidden;
  background: #e2e8f0;
}

.photo-square .photo-container {
  aspect-ratio: 1 / 1;
}

.photo-portrait .photo-container {
  aspect-ratio: 3 / 4;
}

.photo-landscape .photo-container {
  aspect-ratio: 4 / 3;
}

.photo-img {
  width: 100%;
  height: 100%;
  display: block;
  object-fit: cover;
  transition: transform 0.4s ease;
}

.photo-item:hover .photo-img {
  transform: scale(1.02);
}

.image-error {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: #e2e8f0;
  color: #64748b;
  font-weight: 700;
}

.image-error i {
  font-size: 2rem;
}

.photo-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.2));
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 12px;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.photo-item:hover .photo-overlay {
  opacity: 1;
}

.overlay-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.overlay-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.95);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  color: #0f172a;
}

.overlay-btn:hover {
  background: #14b82c;
  color: white;
  transform: scale(1.05);
}

.overlay-caption {
  background: rgba(0, 0, 0, 0.7);
  border-radius: 8px;
  padding: 8px 12px;
  color: white;
  font-size: 0.8rem;
  line-height: 1.4;
  backdrop-filter: blur(4px);
}

.overlay-caption span {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.loading-more {
  display: flex;
  justify-content: center;
  padding: 40px 0;
}

.spinner {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 24px;
  background: white;
  border-radius: 40px;
  color: #14b82c;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.sentinel {
  height: 1px;
}

.end-feed {
  text-align: center;
  padding: 40px 0 20px;
  color: #64748b;
  font-weight: 600;
}

.empty-state {
  text-align: center;
  padding: 80px 20px;
  background: white;
  border-radius: 28px;
  border: 1px dashed #cbd5e1;
}

.empty-state i {
  font-size: 3rem;
  color: #cbd5e1;
  margin-bottom: 16px;
}

.photo-modal :deep(.p-dialog-header) {
  background: #0f172a;
  color: white;
  border-bottom: 1px solid #1e293b;
}

.photo-modal :deep(.p-dialog-content) {
  background: #0f172a;
  padding: 0;
}

.modal-content {
  display: flex;
  flex-direction: column;
}

.modal-image {
  background: #0f172a;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
}

.modal-image img {
  max-width: 100%;
  max-height: 70vh;
  object-fit: contain;
  border-radius: 12px;
}

.modal-info {
  padding: 20px;
  background: white;
  border-top: 1px solid #e2e8f0;
}

.modal-caption {
  display: flex;
  gap: 10px;
  align-items: flex-start;
  background: #f8fafc;
  padding: 14px;
  border-radius: 12px;
  margin-bottom: 20px;
  border-left: 3px solid #14b82c;
}

.modal-caption i {
  color: #14b82c;
  font-size: 1.1rem;
  margin-top: 2px;
}

.modal-caption p {
  margin: 0;
  line-height: 1.5;
  color: #334155;
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

@media (max-width: 1200px) {
  .photo-grid-masonry {
    column-count: 3;
    column-gap: 16px;
  }

  .photo-grid-skeleton {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
  }

  .photo-item {
    margin-bottom: 16px;
  }
}

@media (max-width: 900px) {
  .photo-grid-masonry {
    column-count: 2;
  }

  .photo-grid-skeleton {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 768px) {
  .hero-banner {
    flex-direction: column;
    align-items: stretch;
  }

  .search-wrapper {
    width: 100%;
  }

  .overlay-caption {
    font-size: 0.7rem;
    padding: 6px 10px;
  }

  .overlay-btn {
    width: 32px;
    height: 32px;
  }

  .modal-actions {
    flex-direction: column;
  }

  .modal-actions button {
    width: 100%;
  }
}

@media (max-width: 640px) {
  .page-title {
    font-size: 1.7rem;
  }

  .hero-banner {
    padding: 20px;
  }
}

@media (max-width: 480px) {
  .photo-grid-masonry {
    column-count: 1;
    column-gap: 12px;
  }

  .photo-grid-skeleton {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .photo-item {
    margin-bottom: 12px;
  }
}
</style>