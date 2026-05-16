<script setup>
import { useHead } from '@unhead/vue'
import { useRouter } from 'vue-router'
import { ref, onMounted, computed, watch, onBeforeUnmount, nextTick } from 'vue'
import axios from 'axios'

import { formatDistanceToNow } from 'date-fns'
import { fr } from 'date-fns/locale'

import SidebarOfficial from '@/components/SidebarOfficial.vue'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Skeleton from 'primevue/skeleton'

const baseUrl = typeof window !== 'undefined' ? window.location.origin : ''

useHead({  meta: [
    { name: 'description', content: 'Fil d\'actualité officiel des Forces Armées Maliennes (FAMa).' },
    { property: 'og:type', content: 'website' },
    { property: 'og:title', content: 'Avis & Communiqués Officiels | FAMa' },
    { property: 'og:description', content: 'Accédez aux derniers communiqués de presse et documents certifiés des FAMa.' },
    { property: 'og:image', content: `${baseUrl}/assets/images/hero.jpg` },
    { property: 'og:image:alt', content: 'Forces Armées Maliennes' },
    { name: 'twitter:card', content: 'summary_large_image' },
    { name: 'twitter:title', content: 'Avis & Communiqués | FAMa' },
  ],
})

const router = useRouter()

// -------------------- FEED STATE --------------------
const posts = ref([])
const search = ref('')
const loading = ref(true)
const loadingMore = ref(false)
const page = ref(1)
const perPage = ref(9)
const hasMore = ref(true)
const showScrollTop = ref(false)
const sentinel = ref(null)
let observer = null
let searchTimeout = null

// -------------------- STICKY SIDEBAR (JS robuste) --------------------
// Refs pour le sticky sidebar
const sidebarColumn = ref(null)
const sidebarInner = ref(null)
const mainLayout = ref(null)

const STICKY_TOP_OFFSET = 20 // px depuis le haut du viewport

const updateStickysidebar = () => {
  const sidebar = sidebarColumn.value
  const inner = sidebarInner.value
  const layout = mainLayout.value
  if (!sidebar || !inner || !layout || window.innerWidth <= 850) return

  const layoutRect = layout.getBoundingClientRect()
  const innerHeight = inner.offsetHeight
  const layoutBottom = layoutRect.bottom
  const viewportHeight = window.innerHeight

  // Position naturelle du top de la colonne sidebar dans le document
  const sidebarColTop = sidebar.getBoundingClientRect().top + window.scrollY
  const scrollY = window.scrollY

  // Calcul : jusqu'où peut descendre la sidebar (ne dépasse pas le bas du layout)
  const maxTop = layout.offsetTop + layout.offsetHeight - innerHeight
  const desiredTop = scrollY + STICKY_TOP_OFFSET

  if (desiredTop <= layout.offsetTop) {
    // Pas encore scrollé jusqu'au layout : position naturelle
    inner.style.position = 'relative'
    inner.style.top = '0'
    inner.style.width = ''
  } else if (desiredTop >= maxTop) {
    // Bas du feed atteint : colle la sidebar au bas du layout
    inner.style.position = 'absolute'
    inner.style.top = `${layout.offsetHeight - innerHeight}px`
    inner.style.width = `${sidebar.offsetWidth}px`
  } else {
    // En cours de scroll : fixed dans la colonne
    inner.style.position = 'fixed'
    inner.style.top = `${STICKY_TOP_OFFSET}px`
    inner.style.width = `${sidebar.offsetWidth}px`
  }
}

const resetStickyOnResize = () => {
  const inner = sidebarInner.value
  if (!inner) return
  if (window.innerWidth <= 850) {
    inner.style.position = ''
    inner.style.top = ''
    inner.style.width = ''
  } else {
    updateStickysidebar()
  }
}

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
  } catch (e) {
    return 'Date inconnue'
  }
}

const getPostImage = (post) => {
  if (post.type === 'video') {
    if (post.video_thumbnail_url) return post.video_thumbnail_url
    if (post.thumbnail) return `/storage/${post.thumbnail}`
    if (post.media?.length > 0) return `/storage/${post.media[0].file_path}`
    return null
  }
  if (post.thumbnail) return `/storage/${post.thumbnail}`
  if (post.media?.length > 0) return `/storage/${post.media[0].file_path}`
  return null
}

const downloadPDF = (path, event) => {
  event.stopPropagation()
  if (!path) return
  window.open(`/storage/${path}`, '_blank')
}

const getShareLink = (platform, post) => {
  const shareUrl = encodeURIComponent(`${window.location.origin}/posts/${post.slug}`)
  const shareTitle = encodeURIComponent(`FAMa : ${post.title}`)
  return platform === 'facebook'
    ? `https://www.facebook.com/sharer/sharer.php?u=${shareUrl}`
    : `https://api.whatsapp.com/send?text=${shareTitle}%20${shareUrl}`
}

const scrollToTop = () => window.scrollTo({ top: 0, behavior: 'smooth' })

const handleScroll = () => {
  showScrollTop.value = window.scrollY > 500
  updateStickysidebar()
}

// -------------------- API --------------------
const fetchPage = async () => {
  if (!hasMore.value || loadingMore.value) return
  loadingMore.value = true
  try {
    const res = await axios.get('/api/posts', {
      params: { page: page.value, per_page: perPage.value, q: search.value.trim() || undefined },
      timeout: 10000,
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
    console.error('Erreur API:', e)
  } finally {
    loading.value = false
    loadingMore.value = false
    // Recalcule la sidebar après chargement (hauteur du feed change)
    await nextTick()
    updateStickysidebar()
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
      if (entries[0].isIntersecting && !loadingMore.value && hasMore.value) fetchPage()
    },
    { root: null, threshold: 0.1, rootMargin: '100px' }
  )
  observer.observe(sentinel.value)
}

// -------------------- RECHERCHE --------------------
watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => resetAndReload(), 400)
})

// -------------------- COMPUTED --------------------
const filteredPosts = computed(() =>
  posts.value.filter((post) =>
    ['article', 'video'].includes(post.type)
  )
)
const recentPdfs = ref([])

const fetchRecentPdfs = async () => {
  try {
    const res = await axios.get('/api/posts/latest-pdfs', { params: { limit: 3 }, timeout: 10000 })
    recentPdfs.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    console.error('Erreur chargement PDF récents:', e)
    recentPdfs.value = []
  }
}

// -------------------- LIFECYCLE --------------------
onMounted(async () => {
  await Promise.all([fetchPage(), fetchRecentPdfs()])
  await nextTick()
  setupObserver()
  window.addEventListener('scroll', handleScroll, { passive: true })
  window.addEventListener('resize', resetStickyOnResize, { passive: true })
  updateStickysidebar()
})

onBeforeUnmount(() => {
  if (observer && sentinel.value) observer.unobserve(sentinel.value)
  if (observer) observer.disconnect()
  if (searchTimeout) clearTimeout(searchTimeout)
  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('resize', resetStickyOnResize)
})

const handleCardKeyPress = (event, post) => {
  if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault()
    router.push(`/posts/${post.slug}`)
  }
}
</script>

<template>
  <div class="portfolio-container">
    <!--
      ref="mainLayout" est CRUCIAL : il sert de référence de hauteur
      pour calculer jusqu'où la sidebar peut descendre
    -->
    <div class="container main-layout" ref="mainLayout">

      <section class="feed-column">
        <header class="header-section">
          <h1 class="page-title">Communiqués & Avis Officiels</h1>
          <p class="header-subtitle">Fil d'actualité officiel des Forces Armées Maliennes</p>
          <div class="search-wrapper">
            <i class="pi pi-search" aria-hidden="true"></i>
            <InputText
              v-model="search"
              placeholder="Rechercher un communiqué..."
              class="search-input"
              aria-label="Rechercher des publications"
            />
          </div>
        </header>

        <div v-if="loading" class="loading-grid">
          <div v-for="i in 3" :key="i" class="news-card skeleton-card">
            <div class="skeleton-header">
              <Skeleton width="30%" height="1.2rem" />
              <Skeleton width="25%" height="0.9rem" />
            </div>
            <Skeleton width="100%" height="280px" class="mt-3 mb-3" />
            <Skeleton width="80%" height="1.2rem" class="mb-2" />
            <Skeleton width="60%" height="1.2rem" />
          </div>
        </div>

        <div v-else>
          <div v-if="filteredPosts.length > 0">
            <div class="posts-list">
              <article
                v-for="post in filteredPosts"
                :key="post.id"
                class="news-card"
                @click="router.push(`/posts/${post.slug}`)"
                @keypress="handleCardKeyPress($event, post)"
                tabindex="0"
                role="article"
                :aria-label="`Publication: ${post.title}`"
              >
                <div class="card-header">
                  <div class="card-meta">
                    <Tag
                      :value="post.type === 'video' ? 'VIDEO OFFICIELLE' : (post.pdf_path ? 'DOCUMENT OFFICIEL' : 'COMMUNIQUE')"
                      :severity="post.type === 'video' ? 'info' : (post.pdf_path ? 'danger' : 'success')"
                      class="card-tag"
                    />
                    <span class="date-text">
                      <i class="pi pi-clock" aria-hidden="true"></i>
                      {{ getRelativeDate(post.published_at || post.created_at) }}
                    </span>
                  </div>
                  <h2 class="card-title">{{ post.title }}</h2>
                </div>

                <div class="card-media" v-if="getPostImage(post) || post.pdf_path">
                  <div
                    class="media-container"
                    :class="{ 'pdf-media-container': post.pdf_path && post.type !== 'video' }"
                  >
                    <img
                      v-if="getPostImage(post)"
                      :src="getPostImage(post)"
                      :alt="post.title"
                      class="featured-img"
                      :class="{ 'pdf-featured-img': post.pdf_path && post.type !== 'video' }"
                      loading="lazy"
                      decoding="async"
                    />
                    <div v-if="post.type === 'video'" class="video-overlay" aria-label="Contenu video">
                      <i class="pi pi-play" aria-hidden="true"></i>
                    </div>
                    <div v-else-if="post.pdf_path" class="pdf-sticker" @click.stop>
                      <i class="pi pi-file-pdf" aria-hidden="true"></i>
                      <span>DOCUMENT OFFICIEL</span>
                    </div>
                  </div>
                </div>

                <p class="card-excerpt">{{ stripHtml(post.content).substring(0, 180) }}...</p>

                <div class="card-footer" @click.stop>
                  <div class="action-btns">
                    <Button
                      label="Lire la suite"
                      icon="pi pi-arrow-right"
                      iconPos="right"
                      text
                      @click="router.push(`/posts/${post.slug}`)"
                      class="read-more-btn"
                      aria-label="Lire la suite"
                    />
                    <Button
                      v-if="post.pdf_path"
                      icon="pi pi-download"
                      severity="danger"
                      text
                      @click="downloadPDF(post.pdf_path, $event)"
                      label="PDF"
                      aria-label="Telecharger le PDF"
                    />
                  </div>
                  <div class="share-wrapper">
                    <div class="share-floating-menu">
                      <a :href="getShareLink('facebook', post)" target="_blank" class="s-btn fb" rel="noopener noreferrer" aria-label="Partager sur Facebook" @click.stop>
                        <i class="pi pi-facebook" aria-hidden="true"></i>
                      </a>
                      <a :href="getShareLink('whatsapp', post)" target="_blank" class="s-btn wa" rel="noopener noreferrer" aria-label="Partager sur WhatsApp" @click.stop>
                        <i class="pi pi-whatsapp" aria-hidden="true"></i>
                      </a>
                    </div>
                    <Button icon="pi pi-share-alt" rounded severity="secondary" size="small" class="share-trigger-btn" aria-label="Partager" />
                  </div>
                </div>
              </article>
            </div>

            <div v-if="loadingMore" class="loading-more">
              <div class="news-card skeleton-card">
                <div class="skeleton-header">
                  <Skeleton width="30%" height="1.2rem" />
                  <Skeleton width="25%" height="0.9rem" />
                </div>
                <Skeleton width="100%" height="280px" class="mt-3 mb-3" />
                <Skeleton width="80%" height="1.2rem" />
              </div>
            </div>

            <div ref="sentinel" class="sentinel"></div>

            <div v-if="!loading && !loadingMore && !hasMore" class="end-feed">
              <i class="pi pi-check-circle" aria-hidden="true"></i>
              <span>Vous avez consulte toutes les publications</span>
            </div>
          </div>

          <div v-else class="empty-state">
            <i class="pi pi-info-circle" aria-hidden="true"></i>
            <h3>Aucun resultat trouve</h3>
            <p>Aucune publication ne correspond a votre recherche "{{ search }}"</p>
            <Button label="Effacer la recherche" icon="pi pi-times" class="empty-state-btn" @click="search = ''" />
          </div>
        </div>
      </section>

      <!--
        STRUCTURE CLÉ DU STICKY JS :
        - .sidebar-column  => colonne grille, position: relative, hauteur naturelle
        - .sidebar-inner   => l'élément qu'on déplace via JS (position change dynamiquement)
      -->
      <aside class="sidebar-column" ref="sidebarColumn">
        <div class="sidebar-inner" ref="sidebarInner">
          <SidebarOfficial :recentDocs="recentPdfs" />
        </div>
      </aside>

    </div>

    <Transition name="fade">
      <button
        v-if="showScrollTop"
        @click="scrollToTop"
        class="scroll-top-button"
        aria-label="Retour en haut de page"
      >
        <i class="pi pi-arrow-up" aria-hidden="true"></i>
      </button>
    </Transition>
  </div>
</template>

<style scoped>
* {
  box-sizing: border-box;
}

.portfolio-container {
  background: #f0f2f5;
  min-height: 100vh;
  padding: 40px 0;
}

.container {
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 16px;
}

.main-layout {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 30px;
  align-items: start;       /* indispensable */
  /* PAS de min-height ici : la hauteur doit être naturelle
     pour que le calcul JS du bas du layout soit exact */
}

/* -------------------------------------------------------
   SIDEBAR : position: relative sur la COLONNE
   Le sticky est géré par .sidebar-inner via JS
   ------------------------------------------------------- */
.sidebar-column {
  width: 320px;
  position: relative;  /* ancre absolue pour le mode "bas du feed" */
  align-self: stretch; /* la colonne s'étire sur toute la hauteur du feed */
}

.sidebar-inner {
  /* JS va injecter : position fixed/absolute/relative + top + width */
  /* Valeur par défaut (avant JS ou sur mobile) */
  position: relative;
  top: 0;
  width: 100%;
}

/* HEADER */
.header-section { margin-bottom: 24px; }

.page-title {
  font-size: 1.95rem;
  font-weight: 900;
  color: #1c1e21;
  margin: 0 0 8px;
  line-height: 1.2;
}

.header-subtitle {
  color: #65676b;
  font-size: 0.9rem;
  margin: 0 0 20px;
}

.search-wrapper {
  margin-bottom: 30px;
  position: relative;
}

.search-wrapper i {
  position: absolute;
  left: 18px;
  top: 50%;
  transform: translateY(-50%);
  z-index: 2;
  color: #65676b;
  font-size: 0.95rem;
  pointer-events: none;
}

.search-input {
  width: 100%;
  min-height: 52px;
  border-radius: 26px !important;
  padding-left: 46px !important;
  background: white !important;
  border: 1px solid #dddfe2 !important;
  color: #1c1e21 !important;
  font-size: 0.98rem !important;
  transition: all 0.2s ease !important;
}

.search-input:focus {
  border-color: #14b82c !important;
  box-shadow: 0 0 0 2px rgba(20, 184, 44, 0.1) !important;
  outline: none;
}

.search-input::placeholder { color: #9ca3af; }

.posts-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.news-card {
  background: white;
  border-radius: 16px;
  padding: 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border: 1px solid #dddfe2;
  transition: all 0.2s ease;
  cursor: pointer;
}

.news-card:hover {
  transform: translateY(-2px);
  border-color: #14b82c;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.skeleton-card { cursor: default; padding: 20px; }
.skeleton-card:hover { transform: none; border-color: #dddfe2; }

.skeleton-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.card-header { padding: 20px 20px 0 20px; }

.card-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
  flex-wrap: wrap;
}

.card-tag {
  font-weight: 700 !important;
  padding: 0.3rem 0.8rem !important;
  border-radius: 6px !important;
  font-size: 0.7rem !important;
}

.date-text {
  font-size: 0.85rem;
  color: #65676b;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.card-title {
  font-size: 1.35rem;
  font-weight: 800;
  color: #1c1e21;
  line-height: 1.4;
  margin: 0 0 16px;
}

.card-media { margin: 0; background: #f8fafc; overflow: hidden; }

.media-container {
  position: relative;
  width: 100%;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
}

.featured-img {
  display: block;
  width: 100%;
  height: auto;
  object-fit: contain;
  background: #f8fafc;
}

.pdf-media-container {
  height: 320px;
  padding: 18px;
  background: #eef2f7;
  overflow: hidden;
}

.pdf-featured-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  object-position: center;
  display: block;
  background: white;
  border-radius: 10px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
}

.pdf-sticker {
  position: absolute;
  top: 14px;
  left: 14px;
  z-index: 3;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border-radius: 999px;
  background: rgba(190, 18, 60, 0.94);
  color: #fff;
  font-weight: 800;
  font-size: 0.72rem;
  line-height: 1;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
  backdrop-filter: blur(4px);
}

.pdf-sticker i { font-size: 0.95rem; }

.video-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.18);
  pointer-events: none;
}

.video-overlay i {
  font-size: 3rem;
  color: white;
  background: rgba(0, 0, 0, 0.45);
  border-radius: 50%;
  padding: 14px 18px;
}

.card-excerpt {
  color: #1c1e21;
  font-size: 0.95rem;
  line-height: 1.7;
  margin: 0;
  padding: 0 20px;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  padding: 16px 20px 20px 20px;
  border-top: 1px solid #eef2ee;
  margin-top: 16px;
}

.action-btns { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }

.read-more-btn { font-weight: 800 !important; color: #14b82c !important; }
.read-more-btn:hover { color: #0f9e24 !important; }

.share-wrapper { position: relative; display: flex; align-items: center; }

.share-floating-menu {
  display: flex;
  gap: 8px;
  margin-right: 10px;
  opacity: 0;
  transform: translateX(10px);
  transition: 0.2s ease;
  pointer-events: none;
}

.share-wrapper:hover .share-floating-menu {
  opacity: 1;
  transform: translateX(0);
  pointer-events: auto;
}

.s-btn {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  text-decoration: none;
  transition: opacity 0.2s ease;
}

.s-btn:hover { opacity: 0.9; }
.fb { background: #1877f2; }
.wa { background: #22c55e; }

.share-trigger-btn {
  background: #e4e6eb !important;
  border: none !important;
  color: #1c1e21 !important;
}

.loading-grid { display: flex; flex-direction: column; gap: 24px; }
.loading-more { margin-top: 8px; }

.sentinel { height: 1px; width: 100%; opacity: 0; }

.end-feed {
  text-align: center;
  padding: 32px 0 16px;
  color: #65676b;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.end-feed i { font-size: 1rem; }
.end-feed span { font-size: 0.85rem; }

.empty-state {
  background: white;
  border: 1px solid #dddfe2;
  border-radius: 16px;
  padding: 48px 32px;
  text-align: center;
}

.empty-state i { font-size: 2.5rem; color: #65676b; margin-bottom: 16px; display: block; }
.empty-state h3 { font-size: 1.2rem; font-weight: 700; color: #1c1e21; margin: 0 0 8px; }
.empty-state p { color: #65676b; margin: 0 0 24px; }

.empty-state-btn {
  background: #14b82c !important;
  border: none !important;
  color: white !important;
  font-weight: 600 !important;
}

.empty-state-btn:hover { background: #0f9e24 !important; }

.scroll-top-button {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  z-index: 100;
  background: white;
  border: 1px solid #dddfe2;
  color: #14b82c;
  width: 44px;
  height: 44px;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
}

.scroll-top-button:hover {
  background: #f0f2f5;
  transform: translateY(-2px);
  border-color: #14b82c;
}

.scroll-top-button i { font-size: 1.1rem; }

.fade-enter-active,
.fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from,
.fade-leave-to { opacity: 0; }

@media (prefers-reduced-motion: reduce) {
  .news-card,
  .share-floating-menu,
  .scroll-top-button { transition: none !important; }
  .news-card:hover { transform: none; }
}

/* RESPONSIVE */
@media (max-width: 850px) {
  .main-layout { grid-template-columns: 1fr; }
  .sidebar-column { display: none; }
}

@media (max-width: 768px) {
  .portfolio-container { padding: 24px 0 32px; }
  .container { padding: 0 16px; }
  .page-title { font-size: 1.6rem; }
  .search-input { min-height: 48px; font-size: 0.95rem !important; }
  .card-header { padding: 16px 16px 0 16px; }
  .card-title { font-size: 1.2rem; }
  .card-excerpt { padding: 0 16px; font-size: 0.9rem; }
  .card-footer { padding: 12px 16px 16px 16px; flex-direction: column; align-items: stretch; }
  .action-btns { width: 100%; }
  .action-btns :deep(.p-button) { flex: 1; }
  .share-wrapper { justify-content: flex-end; }
  .share-floating-menu { opacity: 1; transform: none; pointer-events: auto; }
}

@media (max-width: 576px) {
  .portfolio-container { padding: 20px 0 28px; }
  .container { padding: 0 14px; }
  .page-title { font-size: 1.4rem; }
  .header-subtitle { font-size: 0.85rem; }
  .card-header { padding: 14px 14px 0 14px; }
  .card-meta { margin-bottom: 10px; }
  .card-tag :deep(.p-tag-value) { font-size: 0.65rem; }
  .date-text { font-size: 0.75rem; }
  .card-title { font-size: 1rem; margin-bottom: 12px; }
  .card-excerpt { padding: 0 14px; font-size: 0.85rem; }
  .card-footer { padding: 12px 14px 14px 14px; }
  .video-overlay i { font-size: 2rem; padding: 10px 14px; }
  .action-btns { flex-direction: column; }
  .action-btns :deep(.p-button) { width: 100%; }
  .empty-state { padding: 32px 20px; }
  .empty-state h3 { font-size: 1rem; }
  .scroll-top-button { bottom: 1rem; right: 1rem; width: 40px; height: 40px; }
}

@media (max-width: 380px) {
  .container { padding: 0 12px; }
  .page-title { font-size: 1.2rem; }
  .card-header { padding: 12px 12px 0 12px; }
  .card-title { font-size: 0.9rem; }
  .card-excerpt { padding: 0 12px; font-size: 0.8rem; }
  .card-footer { padding: 10px 12px 12px 12px; }
}
</style>
