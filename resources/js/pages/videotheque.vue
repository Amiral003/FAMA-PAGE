<script setup>
import { useHead } from '@unhead/vue'
import { useRouter } from 'vue-router'
import { ref, onMounted, computed, watch, onBeforeUnmount, nextTick } from 'vue'
import axios from 'axios'

import { formatDistanceToNow } from 'date-fns'
import { fr } from 'date-fns/locale'

import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'
import Tag from 'primevue/tag'

const router = useRouter()
const baseUrl = typeof window !== 'undefined' ? window.location.origin : ''

const pageTitle = 'Vidéothèque officielle | Forces Armées Maliennes'
const pageDescription =
  'Consultez la vidéothèque officielle des Forces Armées Maliennes : reportages, communiqués vidéo, interviews, opérations, cérémonies et contenus institutionnels validés.'

useHead({
  title: pageTitle,
  meta: [
    { name: 'description', content: pageDescription },
    {
      name: 'keywords',
      content:
        'FAMa, Forces Armées Maliennes, Vidéothèque FAMa, vidéos militaires Mali, communiqués vidéo, reportages FAMa, armée malienne, défense nationale Mali',
    },
    { name: 'robots', content: 'index, follow, max-image-preview:large' },

    { property: 'og:type', content: 'website' },
    { property: 'og:title', content: pageTitle },
    { property: 'og:description', content: pageDescription },
    { property: 'og:url', content: `${baseUrl}/videotheque` },
    { property: 'og:site_name', content: 'Forces Armées Maliennes' },
    { property: 'og:locale', content: 'fr_FR' },
    { property: 'og:image', content: `${baseUrl}/images/og-default.jpg` },
    { property: 'og:image:alt', content: 'Vidéothèque officielle des Forces Armées Maliennes' },

    { name: 'twitter:card', content: 'summary_large_image' },
    { name: 'twitter:title', content: pageTitle },
    { name: 'twitter:description', content: pageDescription },
    { name: 'twitter:image', content: `${baseUrl}/images/og-default.jpg` },
  ],
  link: [{ rel: 'canonical', href: `${baseUrl}/videotheque` }],
  script: [
    {
      type: 'application/ld+json',
      children: JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'CollectionPage',
        name: pageTitle,
        description: pageDescription,
        url: `${baseUrl}/videotheque`,
        isPartOf: {
          '@type': 'WebSite',
          name: 'Forces Armées Maliennes',
          url: baseUrl,
        },
        publisher: {
          '@type': 'GovernmentOrganization',
          name: 'Forces Armées Maliennes',
          alternateName: 'FAMa',
          url: baseUrl,
        },
      }),
    },
    {
      type: 'application/ld+json',
      children: JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: [
          {
            '@type': 'ListItem',
            position: 1,
            name: 'Accueil',
            item: baseUrl,
          },
          {
            '@type': 'ListItem',
            position: 2,
            name: 'Vidéothèque',
            item: `${baseUrl}/videotheque`,
          },
        ],
      }),
    },
  ],
})

const videos = ref([])
const search = ref('')

const loading = ref(true)
const loadingMore = ref(false)
const page = ref(1)
const perPage = ref(12)
const hasMore = ref(true)

const sentinel = ref(null)
let observer = null

const getRelativeDate = (date) => {
  if (!date) return ''

  try {
    return formatDistanceToNow(new Date(date), {
      addSuffix: true,
      locale: fr,
    })
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

  return '/assets/images/video-cover.jpg'
}

const getPlatformLabel = (platform) => {
  if (!platform) return 'VIDÉO'
  if (platform === 'youtube') return 'YOUTUBE'
  if (platform === 'facebook') return 'FACEBOOK'
  if (platform === 'mp4') return 'MP4'

  return 'VIDÉO'
}

const fetchPage = async () => {
  if (!hasMore.value || loadingMore.value) return

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
      videos.value = page.value === 1 ? items : [...videos.value, ...items]
      hasMore.value = !!payload.next_page_url
    } else {
      videos.value = page.value === 1 ? items : [...videos.value, ...items]
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

const setupObserver = () => {
  if (!sentinel.value) return

  observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting) fetchPage()
    },
    {
      root: null,
      threshold: 0.1,
    }
  )

  observer.observe(sentinel.value)
}

onMounted(async () => {
  await fetchPage()
  await nextTick()
  setupObserver()
})

onBeforeUnmount(() => {
  if (observer && sentinel.value) {
    observer.unobserve(sentinel.value)
  }

  observer = null
})

let tmr = null

watch(search, () => {
  clearTimeout(tmr)

  tmr = setTimeout(() => {
    resetAndReload()
  }, 350)
})

const filteredVideos = computed(() => videos.value)
</script>

<template>
  <div class="video-library-page">
    <div class="video-container">
      <header class="video-header">
        <div class="video-title-wrap">
          <div class="video-eyebrow">
            Forces Armées Maliennes
          </div>

          <h1 class="video-page-title">
            Vidéothèque officielle
          </h1>

          <p class="video-page-subtitle">
            Reportages, communiqués vidéo, interviews et contenus officiels
            validés par les structures compétentes.
          </p>
        </div>

        <div class="video-search-wrapper">
          <i class="pi pi-search"></i>

          <InputText
            v-model="search"
            placeholder="Rechercher une vidéo..."
            class="video-search-input"
          />
        </div>
      </header>

      <div v-if="loading" class="video-loading-grid">
        <div
          v-for="i in 8"
          :key="i"
          class="video-item-card video-skeleton-card"
        >
          <Skeleton width="100%" height="190px" class="mb-3" />
          <Skeleton width="70%" height="1rem" class="mb-2" />
          <Skeleton width="45%" height="0.9rem" />
        </div>
      </div>

      <div v-else>
        <div v-if="filteredVideos.length > 0" class="video-grid">
          <article
            v-for="v in filteredVideos"
            :key="v.id"
            class="video-item-card"
            @click="router.push(`/posts/${v.slug}`)"
          >
            <div class="video-thumb">
              <img
                :src="getVideoCover(v)"
                :alt="v.title"
                loading="lazy"
              />

              <div class="video-thumb-overlay">
                <span class="video-play-button">
                  <i class="pi pi-play"></i>
                </span>
              </div>

              <div class="video-badges">
                <Tag
                  :value="getPlatformLabel(v.video_platform)"
                  severity="info"
                  class="video-platform-tag"
                />
              </div>
            </div>

            <div class="video-meta">
              <h2 class="video-title">
                {{ v.title }}
              </h2>

              <div class="video-sub">
                <span class="video-date">
                  <i class="pi pi-clock mr-1"></i>
                  {{ getRelativeDate(v.published_at || v.created_at) }}
                </span>
              </div>

              <p v-if="v.content" class="video-excerpt">
                {{ stripHtml(v.content).substring(0, 120) }}...
              </p>

              <div class="video-actions" @click.stop>
                <Button
                  label="Regarder"
                  icon="pi pi-play"
                  class="video-watch-button"
                  size="small"
                  @click="router.push(`/posts/${v.slug}`)"
                />
              </div>
            </div>
          </article>

          <div v-if="loadingMore" class="video-loading-more">
            <div class="video-item-card video-skeleton-card">
              <Skeleton width="100%" height="190px" class="mb-3" />
              <Skeleton width="70%" height="1rem" class="mb-2" />
              <Skeleton width="45%" height="0.9rem" />
            </div>
          </div>

          <div ref="sentinel" class="video-sentinel"></div>

          <div
            v-if="!loading && !loadingMore && !hasMore"
            class="video-end-feed"
          >
            <p>Vous avez tout vu ✅</p>
          </div>
        </div>

        <div v-else class="video-empty">
          <i class="pi pi-info-circle"></i>
          <p>Aucune vidéo trouvée.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.video-library-page {
  min-height: 100vh;
  padding: 42px 0 56px;
  background: #f4f7f5;
}

.video-container {
  max-width: 1160px;
  margin: 0 auto;
  padding: 0 16px;
}

.video-header {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 360px;
  gap: 24px;
  align-items: end;
  margin-bottom: 30px;
  padding: 26px;
  border-radius: 22px;
  background: #ffffff;
  border: 1px solid rgba(15, 23, 42, 0.08);
  box-shadow: 0 14px 32px rgba(15, 23, 42, 0.06);
}

.video-eyebrow {
  display: inline-flex;
  width: fit-content;
  margin-bottom: 10px;
  padding: 6px 12px;
  border-radius: 999px;
  background: rgba(20, 184, 44, 0.1);
  color: #0f7a21;
  font-size: 0.76rem;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.video-page-title {
  margin: 0 0 8px;
  font-size: clamp(1.8rem, 3vw, 2.4rem);
  line-height: 1.1;
  font-weight: 950;
  color: #0f172a;
}

.video-page-subtitle {
  max-width: 720px;
  margin: 0;
  color: #475569;
  line-height: 1.75;
  font-size: 0.98rem;
}

.video-search-wrapper {
  position: relative;
}

.video-search-wrapper i {
  position: absolute;
  left: 18px;
  top: 50%;
  z-index: 2;
  transform: translateY(-50%);
  color: #64748b;
}

.video-search-input {
  width: 100%;
  border-radius: 999px !important;
  padding-left: 45px !important;
  height: 44px;
  border: 1px solid rgba(15, 23, 42, 0.1) !important;
  background: #f8fafc !important;
  color: #0f172a !important;
  box-shadow: none !important;
}

.video-search-input:focus {
  border-color: rgba(20, 184, 44, 0.5) !important;
  box-shadow: 0 0 0 3px rgba(20, 184, 44, 0.12) !important;
}

.video-grid,
.video-loading-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
}

.video-item-card {
  overflow: hidden;
  border-radius: 18px;
  background: #ffffff;
  border: 1px solid rgba(15, 23, 42, 0.08);
  box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
  cursor: pointer;
  transition:
    transform 0.18s ease,
    box-shadow 0.18s ease,
    border-color 0.18s ease;
}

.video-item-card:hover {
  transform: translateY(-3px);
  border-color: rgba(20, 184, 44, 0.28);
  box-shadow: 0 14px 28px rgba(15, 23, 42, 0.1);
}

.video-thumb {
  position: relative;
  background: #0f172a;
}

.video-thumb img {
  display: block;
  width: 100%;
  height: 190px;
  object-fit: cover;
}

.video-thumb-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background:
    linear-gradient(
      180deg,
      rgba(15, 23, 42, 0.05),
      rgba(15, 23, 42, 0.4)
    );
}

.video-play-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 58px;
  height: 58px;
  border-radius: 999px;
  background: rgba(0, 0, 0, 0.48);
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.22);
  backdrop-filter: blur(3px);
}

.video-play-button i {
  font-size: 1.7rem;
  margin-left: 3px;
}

.video-badges {
  position: absolute;
  left: 12px;
  bottom: 12px;
  display: flex;
  gap: 8px;
}

.video-meta {
  padding: 15px 15px 17px;
}

.video-title {
  display: -webkit-box;
  min-height: 46px;
  margin: 0 0 8px;
  overflow: hidden;
  color: #0f172a;
  font-size: 1rem;
  font-weight: 900;
  line-height: 1.45;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.video-sub {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
  color: #64748b;
  font-size: 0.84rem;
}

.video-date {
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.video-excerpt {
  display: -webkit-box;
  min-height: 44px;
  margin: 0 0 14px;
  overflow: hidden;
  color: #475569;
  font-size: 0.9rem;
  line-height: 1.55;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.video-actions {
  display: flex;
  justify-content: flex-start;
}

.video-watch-button {
  border: none !important;
  background: #0f7a21 !important;
  color: #ffffff !important;
  font-weight: 900 !important;
  border-radius: 999px !important;
}

.video-watch-button:hover {
  background: #0b6519 !important;
}

.video-loading-more,
.video-sentinel,
.video-end-feed {
  grid-column: 1 / -1;
}

.video-loading-more {
  margin-top: 10px;
}

.video-sentinel {
  width: 100%;
  height: 1px;
}

.video-end-feed {
  padding: 24px 0 8px;
  text-align: center;
}

.video-end-feed p {
  margin: 0;
  color: #64748b;
  font-weight: 900;
}

.video-empty {
  padding: 36px 20px;
  border-radius: 18px;
  background: #ffffff;
 border: 1px solid rgba(15, 23, 42, 0.12);
  color: #334155;
  text-align: center;
}

.video-empty i {
  display: block;
  margin-bottom: 12px;
  color: #64748b;
  font-size: 2rem;
}

.video-empty p {
  margin: 0;
  font-weight: 800;
}

.video-skeleton-card {
  cursor: default;
}

/* ================= DARK MODE ROBUSTE ================= */

:global(html.dark) .video-library-page {
  background: #243125 !important;
}

:global(html.dark) .video-library-page *,
:global(html.dark) .video-library-page *::before,
:global(html.dark) .video-library-page *::after {
  box-shadow: none;
}

:global(html.dark) .video-header {
  background: #1f261b !important;
  border-color: rgba(255, 255, 255, 0.08) !important;
}

:global(html.dark) .video-eyebrow {
  background: rgba(34, 197, 94, 0.13) !important;
  color: #bbf7d0 !important;
  border: 1px solid rgba(34, 197, 94, 0.22) !important;
}

:global(html.dark) .video-page-title,
:global(html.dark) .video-title {
  color: #ffffff !important;
}

:global(html.dark) .video-page-subtitle,
:global(html.dark) .video-sub,
:global(html.dark) .video-date,
:global(html.dark) .video-excerpt,
:global(html.dark) .video-end-feed p {
  color: #dbe4dc !important;
}

:global(html.dark) .video-search-input {
  background: #151c14 !important;
  color: #ffffff !important;
  border-color: rgba(255, 255, 255, 0.1) !important;
}

:global(html.dark) .video-search-input::placeholder {
  color: #9ca89c !important;
}

:global(html.dark) .video-search-wrapper i {
  color: #a8b5aa !important;
}

:global(html.dark) .video-item-card,
:global(html.dark) .video-empty,
:global(html.dark) .video-skeleton-card {
  background: #1f261b !important;
  color: #ebede9 !important;
  border-color: rgba(255, 255, 255, 0.08) !important;
}

:global(html.dark) .video-item-card:hover {
  border-color: rgba(34, 197, 94, 0.32) !important;
  box-shadow: 0 14px 28px rgba(0, 0, 0, 0.24) !important;
}

:global(html.dark) .video-thumb {
  background: #0f172a !important;
  border: 0 !important;
}

:global(html.dark) .video-thumb img {
  background: transparent !important;
  border: 0 !important;
}

:global(html.dark) .video-thumb-overlay,
:global(html.dark) .video-play-button,
:global(html.dark) .video-badges {
  border-color: transparent !important;
}

:global(html.dark) .video-play-button {
  background: rgba(0, 0, 0, 0.55) !important;
  color: #ffffff !important;
  border: 1px solid rgba(255, 255, 255, 0.18) !important;
}

:global(html.dark) .video-platform-tag,
:global(html.dark) .video-library-page .p-tag {
  background: rgba(34, 197, 94, 0.14) !important;
  color: #bbf7d0 !important;
  border: 1px solid rgba(34, 197, 94, 0.25) !important;
  box-shadow: none !important;
}

:global(html.dark) .video-watch-button {
  background: #15803d !important;
  color: #ffffff !important;
  border: none !important;
}

:global(html.dark) .video-watch-button:hover {
  background: #166534 !important;
}

:global(html.dark) .video-empty {
  border-style: dashed !important;
}

:global(html.dark) .video-empty i,
:global(html.dark) .video-empty p {
  color: #dbe4dc !important;
}

:global(html.dark) .video-library-page .p-skeleton {
  background: #2f3a2b !important;
}

@media (max-width: 980px) {
  .video-header {
    grid-template-columns: 1fr;
  }

  .video-grid,
  .video-loading-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 560px) {
  .video-library-page {
    padding: 18px 0 36px;
  }

  .video-header {
    padding: 20px;
    border-radius: 18px;
  }

  .video-grid,
  .video-loading-grid {
    grid-template-columns: 1fr;
  }

  .video-thumb img {
    height: 215px;
  }
}
</style>