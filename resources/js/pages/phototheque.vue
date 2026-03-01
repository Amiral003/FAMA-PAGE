<script setup>
import { useHead } from '@vueuse/head'
import { useRouter } from 'vue-router'
import { ref, onMounted, computed, watch, onBeforeUnmount, nextTick } from 'vue'
import axios from 'axios'

import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'
import Tag from 'primevue/tag'
import Image from 'primevue/image'

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

// -------------------- STATE --------------------
const posts = ref([])
const search = ref('')

const loading = ref(true)
const loadingMore = ref(false)
const page = ref(1)
const perPage = ref(12)
const hasMore = ref(true)

const sentinel = ref(null)
let observer = null

// -------------------- TRANSFORM POSTS -> PHOTOS --------------------
const photos = computed(() => {
  const out = []

  for (const p of posts.value) {
    // ✅ uniquement les images de la galerie media
    if (Array.isArray(p.media)) {
      for (const m of p.media) {
        if (!m?.file_path) continue
        out.push({
          src: `/storage/${m.file_path}`,
          postSlug: p.slug,
          postTitle: p.title,
        })
      }
    }
  }

  return out
})

// -------------------- API --------------------
const fetchPage = async () => {
  if (!hasMore.value) return
  if (loadingMore.value) return

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
      if (page.value === 1) posts.value = items
      else posts.value = [...posts.value, ...items]
      hasMore.value = !!payload.next_page_url
    } else {
      if (page.value === 1) posts.value = items
      else posts.value = [...posts.value, ...items]
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

const goToPost = (slug) => router.push(`/posts/${slug}`)
</script>

<template>
  <div class="photo-page">
    <div class="container">
      <header class="header">
        <div class="title-wrap">
          <h1 class="page-title">Photothèque Officielle</h1>
          <p class="page-subtitle">
            Cérémonies, opérations, formations, actions civilo-militaires.
          </p>
        </div>

        <div class="search-wrapper">
          <i class="pi pi-search"></i>
          <InputText v-model="search" placeholder="Rechercher..." class="search-input" />
        </div>
      </header>

      <div v-if="loading" class="loading-grid">
        <div v-for="i in 12" :key="i" class="photo-card skeleton">
          <Skeleton width="100%" height="190px"></Skeleton>
        </div>
      </div>

      <div v-else>
        <div v-if="photos.length > 0" class="grid">
          <article v-for="(ph, idx) in photos" :key="ph.src + idx" class="photo-card">
            <div class="badge">
              <Tag :value="ph.badge" severity="success" />
            </div>

            <Image
              :src="ph.src"
              preview
              imageClass="photo-img"
              :pt="{ image: { loading: 'lazy' } }"
            />

            <div class="footer" @click="goToPost(ph.postSlug)">
              <span class="post-title">{{ ph.postTitle }}</span>
              <Button label="Voir le post" icon="pi pi-arrow-right" text class="btn-link" />
            </div>
          </article>

          <div v-if="loadingMore" class="loading-more">
            <div class="photo-card skeleton">
              <Skeleton width="100%" height="190px"></Skeleton>
            </div>
          </div>

          <div ref="sentinel" class="sentinel"></div>

          <div v-if="!loading && !loadingMore && !hasMore" class="end-feed">
            <p>Vous avez tout vu ✅</p>
          </div>
        </div>

        <div v-else class="empty">
          <i class="pi pi-info-circle"></i>
          <p>Aucune photo trouvée.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.photo-page { background: #f0f2f5; min-height: 100vh; padding: 40px 0; }
.container { max-width: 1100px; margin: 0 auto; padding: 0 15px; }

.header { display: grid; grid-template-columns: 1fr 360px; gap: 20px; align-items: end; margin-bottom: 30px; }
.page-title { font-size: 1.8rem; font-weight: 900; color: #1c1e21; margin-bottom: 6px; }
.page-subtitle { color: #65676b; margin: 0; }

.search-wrapper { position: relative; }
.search-input { width: 100%; border-radius: 25px !important; padding-left: 45px !important; border: none !important; box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important; }
.search-wrapper i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); z-index: 2; color: #65676b; }

.grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }

.photo-card{
  position: relative;
  background: white;
  border-radius: 14px;
  overflow: hidden;
  border: 1px solid #dddfe2;
  box-shadow: 0 1px 2px rgba(0,0,0,0.08);
}

.badge{
  position:absolute;
  top: 10px;
  left: 10px;
  z-index: 3;
}

:deep(.photo-img){
  width: 100%;
  height: 190px;
  object-fit: cover;
  display: block;
}

.footer{
  display:flex;
  align-items:center;
  justify-content: space-between;
  gap: 10px;
  padding: 10px 12px;
  border-top: 1px solid #f1f5f9;
  cursor: pointer;
}
.post-title{
  font-size: 0.88rem;
  font-weight: 800;
  color:#0f172a;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.btn-link{ font-weight: 800; color:#14B82C !important; }

.loading-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
.loading-more { margin-top: 10px; grid-column: 1 / -1; }
.sentinel { height: 1px; width: 100%; grid-column: 1 / -1; }
.end-feed { grid-column: 1 / -1; text-align: center; color: #65676b; padding: 25px 0; font-weight: 800; }

.empty { background: white; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 30px; text-align: center; color: #334155; }
.empty i { font-size: 2rem; margin-bottom: 10px; color: #64748b; }

@media (max-width: 980px) {
  .header { grid-template-columns: 1fr; }
  .grid { grid-template-columns: repeat(3, 1fr); }
  .loading-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 680px) {
  .grid { grid-template-columns: repeat(2, 1fr); }
  .loading-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>