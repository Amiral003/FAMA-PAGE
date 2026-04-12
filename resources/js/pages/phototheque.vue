<script setup>
import { useHead } from '@unhead/vue'
import { useRouter } from 'vue-router'
import { ref, onMounted, computed, watch, onBeforeUnmount, nextTick } from 'vue'
import axios from 'axios'

import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'
import Tag from 'primevue/tag'
import Image from 'primevue/image'
import Card from 'primevue/card'

const router = useRouter()
const baseUrl = typeof window !== 'undefined' ? window.location.origin : ''

useHead({
  title: 'Photothèque Officielle | FAMa',
  meta: [
    { name: 'description', content: "Photothèque officielle des Forces Armées Maliennes (FAMa) : cérémonies, opérations, formations, actions civilo-militaires." },
    { property: 'og:type', content: 'website' },
    { property: 'og:title', content: 'Photothèque Officielle | FAMa' },
    { property: 'og:description', content: "Accédez aux images officielles validées par l'État-Major." },
    { property: 'og:image', content: `${baseUrl}/assets/images/hero.jpg` },
    { name: 'twitter:card', content: 'summary_large_image' },
  ],
})

const posts = ref([])
const search = ref('')

const loading = ref(true)
const loadingMore = ref(false)
const page = ref(1)
const perPage = ref(12)
const hasMore = ref(true)

const sentinel = ref(null)
let observer = null

const albums = computed(() => {
  return posts.value
    .map((p, index) => {
      const media = Array.isArray(p.media) ? p.media : []

      const photos = media
        .filter((m) => m?.file_path)
        .map((m, idx) => ({
          id: `${p.id || p.slug || p.title}-${idx}`,
          src: `/storage/${m.file_path}`,
          alt: p.title,
          postSlug: p.slug,
          postTitle: p.title,
        }))

      return {
        id: p.id || p.slug || p.title,
        title: p.title,
        slug: p.slug,
        count: photos.length,
        photos,
        order: index + 1,
      }
    })
    .filter((album) => album.photos.length > 0)
})

const totalPhotos = computed(() =>
  albums.value.reduce((sum, album) => sum + album.count, 0)
)

const fetchPage = async () => {
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
    const items = payload?.data ?? payload ?? []

    if (Array.isArray(payload?.data)) {
      posts.value = page.value === 1 ? items : [...posts.value, ...items]
      hasMore.value = !!payload.next_page_url
    } else {
      posts.value = page.value === 1 ? items : [...posts.value, ...items]
      hasMore.value = false
    }

    page.value += 1
  } catch (e) {
    console.error('Erreur API photos:', e)
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

const resetAndReload = async () => {
  page.value = 1
  hasMore.value = true
  loading.value = true
  posts.value = []
  await fetchPage()
}

const setupObserver = () => {
  if (!sentinel.value) return

  observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting) fetchPage()
    },
    { root: null, threshold: 0.1 }
  )

  observer.observe(sentinel.value)
}

onMounted(async () => {
  await fetchPage()
  await nextTick()
  setupObserver()
})

onBeforeUnmount(() => {
  if (observer && sentinel.value) observer.unobserve(sentinel.value)
  observer = null
})

let tmr = null
watch(search, () => {
  clearTimeout(tmr)
  tmr = setTimeout(() => {
    resetAndReload()
  }, 350)
})

const goToPost = (slug) => router.push(`/posts/${slug}`)
</script>

<template>
  <div class="photo-page">
    <div class="container">
      <header class="hero-banner">
        <div class="hero-content">
          <div class="hero-pill">
            <i class="pi pi-images"></i>
            <span>Galerie officielle FAMa</span>
          </div>

          <h1 class="page-title">Photothèque Officielle</h1>
          <p class="page-subtitle">
            Une présentation plus fluide et moderne des publications visuelles :
            cérémonies, opérations, formations et actions civilo-militaires.
          </p>

          <div class="stats-row">
            <div class="stat-box">
              <strong>{{ albums.length }}</strong>
              <span>Albums</span>
            </div>
            <div class="stat-box">
              <strong>{{ totalPhotos }}</strong>
              <span>Photos</span>
            </div>
          </div>
        </div>

        <div class="hero-tools">
          <div class="search-wrapper">
            <i class="pi pi-search"></i>
            <InputText
              v-model="search"
              placeholder="Rechercher une publication..."
              class="search-input"
            />
          </div>
        </div>
      </header>

      <!-- Loading -->
      <div v-if="loading" class="album-grid loading-grid">
        <Card v-for="i in 6" :key="i" class="album-card skeleton-card">
          <template #content>
            <div class="skeleton-top">
              <div class="skeleton-meta">
                <Skeleton width="70%" height="18px" class="mb-2" />
                <Skeleton width="110px" height="14px" />
              </div>
              <Skeleton width="90px" height="34px" />
            </div>

            <div class="masonry-skeleton">
              <Skeleton v-for="j in 6" :key="j" width="100%" height="180px" class="skeleton-tile" />
            </div>
          </template>
        </Card>
      </div>

      <!-- Content -->
      <div v-else>
        <transition-group name="album-fade" tag="div" class="album-grid">
          <Card
            v-for="album in albums"
            :key="album.id"
            class="album-card"
            :style="{ animationDelay: `${album.order * 40}ms` }"
          >
            <template #content>
              <div class="album-head">
                <div class="album-head-left" @click="goToPost(album.slug)">
                  <h2 class="album-title">{{ album.title }}</h2>
                  <div class="album-meta">
                    <Tag
                      :value="`${album.count} photo${album.count > 1 ? 's' : ''}`"
                      severity="success"
                      rounded
                    />
                  </div>
                </div>

                <Button
                  icon="pi pi-arrow-right"
                  label="Voir"
                  rounded
                  outlined
                  class="album-btn"
                  @click="goToPost(album.slug)"
                />
              </div>

              <!-- TRUE MASONRY -->
              <div class="album-masonry">
                <div
                  v-for="photo in album.photos"
                  :key="photo.id"
                  class="masonry-item"
                >
                  <div class="image-shell">
                    <Image
                      :src="photo.src"
                      :alt="photo.alt"
                      preview
                      imageClass="masonry-img"
                      :pt="{ image: { loading: 'lazy' } }"
                    />
                  </div>
                </div>
              </div>
            </template>
          </Card>
        </transition-group>

        <div v-if="loadingMore" class="loading-more">
          <Card class="album-card skeleton-card">
            <template #content>
              <div class="skeleton-top">
                <div class="skeleton-meta">
                  <Skeleton width="70%" height="18px" class="mb-2" />
                  <Skeleton width="110px" height="14px" />
                </div>
                <Skeleton width="90px" height="34px" />
              </div>

              <div class="masonry-skeleton">
                <Skeleton v-for="j in 6" :key="j" width="100%" height="180px" class="skeleton-tile" />
              </div>
            </template>
          </Card>
        </div>

        <div ref="sentinel" class="sentinel"></div>

        <div v-if="!loading && !loadingMore && !albums.length" class="empty-state">
          <i class="pi pi-images"></i>
          <h3>Aucune photo trouvée</h3>
          <p>Essayez une autre recherche.</p>
        </div>

        <div v-if="!loading && !loadingMore && hasMore === false && albums.length" class="end-feed">
          <i class="pi pi-check-circle"></i>
          <span>Vous avez tout vu</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.photo-page {
  min-height: 100vh;
  padding: 32px 0 60px;
  background:
    radial-gradient(circle at top left, rgba(20, 184, 44, 0.08), transparent 18%),
    radial-gradient(circle at top right, rgba(15, 23, 42, 0.05), transparent 20%),
    linear-gradient(180deg, #f8fafc 0%, #eef3f8 100%);
}

.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 16px;
}

.hero-banner {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 20px;
  align-items: end;
  margin-bottom: 28px;
  padding: 24px;
  border-radius: 28px;
  background: rgba(255, 255, 255, 0.84);
  border: 1px solid rgba(226, 232, 240, 0.9);
  box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
  backdrop-filter: blur(12px);
}

.hero-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 14px;
  padding: 8px 14px;
  border-radius: 999px;
  background: rgba(20, 184, 44, 0.10);
  color: #15803d;
  font-size: 0.85rem;
  font-weight: 900;
}

.page-title {
  margin: 0 0 10px;
  font-size: 2.15rem;
  line-height: 1.1;
  color: #0f172a;
  font-weight: 950;
}

.page-subtitle {
  margin: 0;
  max-width: 760px;
  color: #475569;
  line-height: 1.65;
}

.stats-row {
  display: flex;
  gap: 12px;
  margin-top: 18px;
  flex-wrap: wrap;
}

.stat-box {
  min-width: 110px;
  padding: 12px 14px;
  border-radius: 18px;
  background: rgba(248, 250, 252, 0.95);
  border: 1px solid #e2e8f0;
}

.stat-box strong {
  display: block;
  color: #0f172a;
  font-size: 1.2rem;
  font-weight: 900;
}

.stat-box span {
  color: #64748b;
  font-size: 0.88rem;
  font-weight: 700;
}

.hero-tools {
  display: flex;
  align-items: end;
}

.search-wrapper {
  position: relative;
  width: 100%;
}

.search-wrapper i {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
  z-index: 2;
}

.search-input {
  width: 100%;
  height: 48px;
  padding-left: 42px !important;
  border-radius: 999px !important;
  border: 1px solid #dbe4ee !important;
  box-shadow: none !important;
  background: #fff !important;
}

.album-grid,
.loading-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 18px;
}

.album-card {
  border-radius: 24px;
  border: 1px solid #e2e8f0;
  background: rgba(255, 255, 255, 0.96);
  box-shadow: 0 14px 30px rgba(15, 23, 42, 0.05);
  overflow: hidden;
  opacity: 0;
  transform: translateY(18px);
  animation: albumAppear 0.55s ease forwards;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.album-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
}

:deep(.album-card .p-card-body) {
  padding: 1rem;
}

.album-head,
.skeleton-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 14px;
}

.album-head-left {
  min-width: 0;
  cursor: pointer;
}

.album-title {
  margin: 0 0 8px;
  color: #0f172a;
  font-size: 1.02rem;
  font-weight: 900;
  line-height: 1.35;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.album-meta {
  display: flex;
  align-items: center;
  gap: 10px;
}

.album-btn {
  flex-shrink: 0;
  font-weight: 800;
}

/* VRAIE MASONRY CONTINUE */
.album-masonry,
.masonry-skeleton {
  column-count: 3;
  column-gap: 10px;
}

.masonry-item,
.skeleton-tile {
  break-inside: avoid;
  -webkit-column-break-inside: avoid;
  margin-bottom: 10px;
}

.image-shell {
  position: relative;
  overflow: hidden;
  border-radius: 18px;
  border: 1px solid #edf2f7;
  background: #fff;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
  transition: transform 0.22s ease, box-shadow 0.22s ease;
}

.image-shell:hover {
  transform: translateY(-2px);
  box-shadow: 0 14px 24px rgba(15, 23, 42, 0.08);
}

:deep(.masonry-img) {
  width: 100%;
  height: auto !important;
  display: block;
  object-fit: contain;
  background: #fff;
}

/* animation globale */
@keyframes albumAppear {
  from {
    opacity: 0;
    transform: translateY(18px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.album-fade-enter-active,
.album-fade-leave-active {
  transition: all 0.35s ease;
}

.album-fade-enter-from,
.album-fade-leave-to {
  opacity: 0;
  transform: translateY(12px);
}

.loading-more,
.sentinel,
.end-feed,
.empty-state {
  margin-top: 18px;
}

.sentinel {
  height: 1px;
}

.end-feed {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 0 0;
  color: #64748b;
  font-weight: 800;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  border-radius: 24px;
  border: 1px dashed #cbd5e1;
  background: rgba(255, 255, 255, 0.95);
}

.empty-state i {
  font-size: 2.4rem;
  color: #64748b;
  margin-bottom: 14px;
}

.empty-state h3 {
  margin: 0 0 8px;
  color: #0f172a;
}

.empty-state p {
  margin: 0;
  color: #64748b;
}

@media (max-width: 1100px) {
  .hero-banner {
    grid-template-columns: 1fr;
  }

  .album-grid,
  .loading-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 760px) {
  .page-title {
    font-size: 1.7rem;
  }

  .album-head {
    flex-direction: column;
    align-items: stretch;
  }

  .album-btn {
    width: 100%;
  }

  .album-masonry,
  .masonry-skeleton {
    column-count: 2;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0 12px;
  }

  .hero-banner {
    padding: 18px;
    border-radius: 22px;
  }

  .page-title {
    font-size: 1.45rem;
  }

  .album-masonry,
  .masonry-skeleton {
    column-count: 2;
    column-gap: 8px;
  }

  .masonry-item,
  .skeleton-tile {
    margin-bottom: 8px;
  }

  :deep(.album-card .p-card-body) {
    padding: 0.85rem;
  }
}
</style>