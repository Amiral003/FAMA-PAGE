<script setup>
// Page Com-Ops : affiche UNIQUEMENT les posts type "flash" (status=publie)
// + effet feed infini (IntersectionObserver)
// + recherche server-side via ?q=... (optionnel)

import { useHead } from '@vueuse/head'
import { useRouter } from 'vue-router'
import { ref, onMounted, computed, watch, onBeforeUnmount } from 'vue'
import axios from 'axios'

// Date relative
import { formatDistanceToNow } from 'date-fns'
import { fr } from 'date-fns/locale'

// PrimeVue
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'

// SEO
const baseUrl = typeof window !== 'undefined' ? window.location.origin : ''
useHead({
  title: 'Com-Ops | FAMa',
  meta: [
    { name: 'description', content: 'Communications opérationnelles (Com-Ops) — Flash infos officiels.' },
    { property: 'og:type', content: 'website' },
    { property: 'og:title', content: 'Com-Ops | FAMa' },
    { property: 'og:description', content: 'Derniers Flash infos et communications opérationnelles.' },
    { property: 'og:image', content: `${baseUrl}/assets/images/hero.jpg` },
  ],
})

const router = useRouter()

// -------------------- FEED STATE --------------------
const flashes = ref([])

const search = ref('')
const loading = ref(true)
const loadingMore = ref(false)

const page = ref(1)
const perPage = ref(9)
const hasMore = ref(true)

const sentinel = ref(null)
let observer = null

// -------------------- HELPERS --------------------
const stripHtml = (html) => {
  if (!html) return ''
  const doc = new DOMParser().parseFromString(html, 'text/html')
  return doc.body.textContent || ''
}

const getRelativeDate = (date) => {
  if (!date) return ''
  try {
    return formatDistanceToNow(new Date(date), { addSuffix: true, locale: fr })
  } catch {
    return 'Date inconnue'
  }
}

// ✅ Pour Com-Ops: on veut afficher une image "format communiqué"
// On prend en priorité thumbnail (si tu l’utilises),
// sinon premier media si dispo.
const getPostImage = (post) => {
  if (post.thumbnail) return `/storage/${post.thumbnail}`
  if (post.media?.length > 0) return `/storage/${post.media[0].file_path}`
  return null
}

// -------------------- API : charger une page (type=flash) --------------------
const fetchPage = async () => {
  if (!hasMore.value) return
  if (loadingMore.value) return

  loadingMore.value = true

  try {
    const res = await axios.get('/api/posts', {
      params: {
        page: page.value,
        per_page: perPage.value,
        type: 'flash',                // ✅ IMPORTANT : filtre serveur
        q: search.value.trim() || undefined,
      },
    })

    const payload = res.data
    const items = payload?.data ?? []

    // pagination Laravel
    if (page.value === 1) flashes.value = items
    else flashes.value = [...flashes.value, ...items]

    hasMore.value = !!payload.next_page_url
    page.value += 1
  } catch (e) {
    console.error('Erreur API Com-Ops:', e)
    hasMore.value = false
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

const resetAndReload = async () => {
  page.value = 1
  hasMore.value = true
  loading.value = true
  flashes.value = []
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
  setupObserver()
})

onBeforeUnmount(() => {
  if (observer && sentinel.value) observer.unobserve(sentinel.value)
  observer = null
})

// -------------------- RECHERCHE (debounce) --------------------
let tmr = null
watch(search, () => {
  clearTimeout(tmr)
  tmr = setTimeout(() => resetAndReload(), 350)
})

// -------------------- COMPUTED --------------------
const items = computed(() => flashes.value)
</script>

<template>
  <div class="comops-page">
    <div class="container">
      <header class="header">
        <div class="title-wrap">
          <h1 class="title">Com-Ops</h1>
          <p class="subtitle">Flash infos & communications opérationnelles</p>
        </div>

        <div class="search-wrapper">
          <i class="pi pi-search"></i>
          <InputText v-model="search" placeholder="Rechercher un flash..." class="search-input" />
        </div>
      </header>

      <!-- Loading initial -->
      <div v-if="loading" class="loading-grid">
        <div v-for="i in 3" :key="i" class="flash-card skeleton">
          <Skeleton width="40%" height="1.2rem" class="mb-3"></Skeleton>
          <Skeleton width="100%" height="280px" class="mb-3"></Skeleton>
          <Skeleton width="90%" class="mb-2"></Skeleton>
        </div>
      </div>

      <div v-else>
        <div v-if="items.length > 0" class="grid">
          <article
            v-for="post in items"
            :key="post.id"
            class="flash-card"
            @click="router.push(`/posts/${post.slug}`)"
          >
            <div class="badge">FLASH</div>

            <div class="media" v-if="getPostImage(post)">
              <img :src="getPostImage(post)" alt="" loading="lazy" />
            </div>

            <div class="body">
              <div class="meta">
                <i class="pi pi-clock"></i>
                <span>{{ getRelativeDate(post.published_at || post.created_at) }}</span>
              </div>

              <h2 class="card-title">{{ post.title }}</h2>

              <!-- Petit extrait si tu veux (mais tes flash sont souvent juste titre + image) -->
              <p v-if="post.content" class="excerpt">
                {{ stripHtml(post.content).substring(0, 120) }}...
              </p>

              <div class="cta">
                <Button
                  label="Ouvrir"
                  icon="pi pi-arrow-right"
                  iconPos="right"
                  text
                  class="open-btn"
                  @click.stop="router.push(`/posts/${post.slug}`)"
                />
              </div>
            </div>
          </article>

          <!-- ✅ Sentinel en bas de page -->
          <div ref="sentinel" style="height: 1px;"></div>

          <!-- Loader pages suivantes -->
          <div v-if="loadingMore" class="loading-more">
            <div class="flash-card skeleton">
              <Skeleton width="40%" height="1.2rem" class="mb-3"></Skeleton>
              <Skeleton width="100%" height="280px" class="mb-3"></Skeleton>
              <Skeleton width="90%" class="mb-2"></Skeleton>
            </div>
          </div>

          <div v-if="!loadingMore && !hasMore" class="end">
            Vous avez tout vu ✅
          </div>
        </div>

        <div v-else class="empty">
          <i class="pi pi-info-circle"></i>
          <p>Aucun flash trouvé.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.comops-page { background: #f4f6f8; min-height: 100vh; padding: 30px 0; }
.container { max-width: 1100px; margin: 0 auto; padding: 0 15px; }

.header { display: flex; justify-content: space-between; gap: 18px; align-items: flex-end; margin-bottom: 18px; }
.title { font-size: 1.9rem; font-weight: 900; color: #0f172a; margin: 0; }
.subtitle { margin: 6px 0 0; color: #64748b; font-weight: 700; }

.search-wrapper { position: relative; width: 420px; max-width: 100%; }
.search-input { width: 100%; border-radius: 999px !important; padding-left: 45px !important; border: none !important; box-shadow: 0 2px 6px rgba(0,0,0,0.06) !important; }
.search-wrapper i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #64748b; }

.grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px; }
@media (max-width: 900px) { .grid { grid-template-columns: 1fr; } .header { flex-direction: column; align-items: stretch; } }

.flash-card {
  background: white;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
  cursor: pointer;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
  position: relative;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.flash-card:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.08); }

.badge {
  position: absolute;
  top: 12px;
  left: 12px;
  background: #991b1b;
  color: white;
  padding: 6px 10px;
  border-radius: 999px;
  font-weight: 900;
  letter-spacing: 1px;
  font-size: 0.75rem;
  z-index: 2;
}

.media { background: #0b1220; }
.media img { width: 100%; height: auto; display: block; }

.body { padding: 16px; }
.meta { display: inline-flex; gap: 8px; align-items: center; color: #64748b; font-weight: 800; font-size: 0.85rem; }
.card-title { margin: 10px 0 8px; font-weight: 900; color: #0f172a; line-height: 1.25; font-size: 1.1rem; }
.excerpt { margin: 0 0 8px; color: #334155; line-height: 1.5; }
.cta { display: flex; justify-content: flex-end; }
.open-btn { font-weight: 900 !important; color: #14b82c !important; }

.loading-more { margin-top: 10px; }
.end { text-align: center; color: #64748b; padding: 18px 0; font-weight: 900; }
.empty { text-align: center; padding: 40px 0; color: #64748b; font-weight: 800; }
.empty i { font-size: 1.6rem; margin-bottom: 10px; display: inline-block; }
</style>