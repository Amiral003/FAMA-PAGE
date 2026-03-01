<script setup>
import { useHead } from '@vueuse/head'
import { useRouter } from 'vue-router'
import { ref, onMounted, computed, watch, onBeforeUnmount, nextTick } from 'vue'
import axios from 'axios'

// date relative
import { formatDistanceToNow } from 'date-fns'
import { fr } from 'date-fns/locale'

// PrimeVue
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'
import Tag from 'primevue/tag'

const router = useRouter()
const baseUrl = typeof window !== 'undefined' ? window.location.origin : ''

useHead({
  title: 'Vidéothèque Officielle | FAMa',
  meta: [
    { name: 'description', content: "Vidéothèque officielle des Forces Armées Maliennes (FAMa) : reportages, communiqués vidéo, interviews." },
    { property: 'og:type', content: 'website' },
    { property: 'og:title', content: 'Vidéothèque Officielle | FAMa' },
    { property: 'og:description', content: "Accédez aux vidéos officielles validées par l'État-Major." },
    { property: 'og:image', content: `${baseUrl}/assets/images/hero.jpg` },
    { name: 'twitter:card', content: 'summary_large_image' },
  ],
})

// -------------------- STATE --------------------
const videos = ref([])
const search = ref('')

const loading = ref(true)
const loadingMore = ref(false)
const page = ref(1)
const perPage = ref(12)
const hasMore = ref(true)

const sentinel = ref(null)
let observer = null

// -------------------- HELPERS --------------------
const getRelativeDate = (date) => {
  if (!date) return ''
  try {
    return formatDistanceToNow(new Date(date), { addSuffix: true, locale: fr })
  } catch (e) {
    return 'Date inconnue'
  }
}

const stripHtml = (html) => {
  if (!html) return ''
  const doc = new DOMParser().parseFromString(html, 'text/html')
  return doc.body.textContent || ''
}

const getVideoCover = (v) => {
  if (v.video_thumbnail_url) return v.video_thumbnail_url
  // fallback local (mets une image dans public/assets/images/)
  return '/assets/images/video-cover.jpg'
}

const getPlatformLabel = (platform) => {
  if (!platform) return 'VIDÉO'
  if (platform === 'youtube') return 'YOUTUBE'
  if (platform === 'facebook') return 'FACEBOOK'
  if (platform === 'mp4') return 'MP4'
  return 'VIDÉO'
}

// -------------------- API --------------------
const fetchPage = async () => {
  if (!hasMore.value) return
  if (loadingMore.value) return

  loadingMore.value = true

  try {
    const res = await axios.get('/api/posts/videos', {
      params: {
        page: page.value,
        per_page: perPage.value,
        q: search.value.trim() || undefined,
      },
    })

    const payload = res.data
    const items = payload?.data ?? payload ?? []

    if (Array.isArray(payload?.data)) {
      if (page.value === 1) videos.value = items
      else videos.value = [...videos.value, ...items]
      hasMore.value = !!payload.next_page_url
    } else {
      if (page.value === 1) videos.value = items
      else videos.value = [...videos.value, ...items]
      hasMore.value = false
    }

    page.value += 1
  } catch (e) {
    console.error('Erreur API vidéos:', e)
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

const resetAndReload = async () => {
  page.value = 1
  hasMore.value = true
  loading.value = true
  videos.value = []
  await fetchPage()
}

// -------------------- INFINITE SCROLL --------------------
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

// -------------------- SEARCH debounce --------------------
let tmr = null
watch(search, () => {
  clearTimeout(tmr)
  tmr = setTimeout(() => {
    resetAndReload()
  }, 350)
})

// pas besoin de filtrage local car API gère q
const filteredVideos = computed(() => videos.value)
</script>

<template>
  <div class="video-page">
    <div class="container">
      <header class="header">
        <div class="title-wrap">
          <h1 class="page-title">Vidéothèque Officielle</h1>
          <p class="page-subtitle">
            Reportages, communiqués vidéo, interviews et contenus validés par l’État-Major.
          </p>
        </div>

        <div class="search-wrapper">
          <i class="pi pi-search"></i>
          <InputText v-model="search" placeholder="Rechercher une vidéo..." class="search-input" />
        </div>
      </header>

      <div v-if="loading" class="loading-grid">
        <div v-for="i in 8" :key="i" class="video-card skeleton">
          <Skeleton width="100%" height="180px" class="mb-3"></Skeleton>
          <Skeleton width="70%" height="1rem" class="mb-2"></Skeleton>
          <Skeleton width="45%" height="0.9rem"></Skeleton>
        </div>
      </div>

      <div v-else>
        <div v-if="filteredVideos.length > 0" class="grid">
          <article
            v-for="v in filteredVideos"
            :key="v.id"
            class="video-card"
            @click="router.push(`/posts/${v.slug}`)"
          >
            <div class="thumb">
              <img :src="getVideoCover(v)" :alt="v.title" loading="lazy" />
              <div class="thumb-overlay">
                <i class="pi pi-play"></i>
              </div>

              <div class="badges">
                <Tag :value="getPlatformLabel(v.video_platform)" severity="info" />
              </div>
            </div>

            <div class="meta">
              <h2 class="video-title">{{ v.title }}</h2>
              <div class="video-sub">
                <span class="date">
                  <i class="pi pi-clock mr-1"></i>
                  {{ getRelativeDate(v.published_at || v.created_at) }}
                </span>
              </div>

              <p v-if="v.content" class="excerpt">
                {{ stripHtml(v.content).substring(0, 120) }}...
              </p>

              <div class="actions" @click.stop>
                <Button
                  label="Regarder"
                  icon="pi pi-play"
                  class="btn-fama"
                  size="small"
                  @click="router.push(`/posts/${v.slug}`)"
                />
              </div>
            </div>
          </article>

          <div v-if="loadingMore" class="loading-more">
            <div class="video-card skeleton">
              <Skeleton width="100%" height="180px" class="mb-3"></Skeleton>
              <Skeleton width="70%" height="1rem" class="mb-2"></Skeleton>
              <Skeleton width="45%" height="0.9rem"></Skeleton>
            </div>
          </div>

          <div ref="sentinel" class="sentinel"></div>

          <div v-if="!loading && !loadingMore && !hasMore" class="end-feed">
            <p>Vous avez tout vu ✅</p>
          </div>
        </div>

        <div v-else class="empty">
          <i class="pi pi-info-circle"></i>
          <p>Aucune vidéo trouvée.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.video-page { background: #f0f2f5; min-height: 100vh; padding: 40px 0; }
.container { max-width: 1100px; margin: 0 auto; padding: 0 15px; }

.header { display: grid; grid-template-columns: 1fr 360px; gap: 20px; align-items: end; margin-bottom: 30px; }
.page-title { font-size: 1.8rem; font-weight: 900; color: #1c1e21; margin-bottom: 6px; }
.page-subtitle { color: #65676b; margin: 0; }

.search-wrapper { position: relative; }
.search-input { width: 100%; border-radius: 25px !important; padding-left: 45px !important; border: none !important; box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important; }
.search-wrapper i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); z-index: 2; color: #65676b; }

.grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
.video-card {
  background: white;
  border-radius: 14px;
  overflow: hidden;
  border: 1px solid #dddfe2;
  box-shadow: 0 1px 2px rgba(0,0,0,0.08);
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.video-card:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.10); }

.thumb { position: relative; }
.thumb img { width: 100%; height: 190px; object-fit: cover; display: block; }
.thumb-overlay{
  position:absolute; inset:0;
  display:flex; align-items:center; justify-content:center;
  background: rgba(0,0,0,0.22);
}
.thumb-overlay i{
  font-size: 2.6rem;
  color: white;
  background: rgba(0,0,0,0.45);
  border-radius: 999px;
  padding: 12px 16px;
}
.badges{ position:absolute; left: 10px; bottom: 10px; display:flex; gap:8px; }

.meta { padding: 14px 14px 16px; }
.video-title { font-size: 1.05rem; font-weight: 800; color: #050505; line-height: 1.35; margin: 0 0 8px; }
.video-sub { display:flex; justify-content: space-between; align-items:center; color:#65676b; font-size: 0.88rem; margin-bottom: 10px; }
.excerpt { color: #1c1e21; font-size: 0.92rem; margin: 0 0 12px; }

.actions { display:flex; justify-content: flex-start; }
.btn-fama { font-weight: 800; }

.loading-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
.loading-more { margin-top: 10px; grid-column: 1 / -1; }
.sentinel { height: 1px; width: 100%; grid-column: 1 / -1; }
.end-feed { grid-column: 1 / -1; text-align: center; color: #65676b; padding: 25px 0; font-weight: 800; }

.empty { background: white; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 30px; text-align: center; color: #334155; }
.empty i { font-size: 2rem; margin-bottom: 10px; color: #64748b; }

@media (max-width: 980px) {
  .header { grid-template-columns: 1fr; }
  .grid { grid-template-columns: repeat(2, 1fr); }
  .loading-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 560px) {
  .grid { grid-template-columns: 1fr; }
  .loading-grid { grid-template-columns: 1fr; }
  .thumb img { height: 210px; }
}
</style>