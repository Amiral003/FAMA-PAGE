<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useHead } from '@unhead/vue'
import axios from 'axios'

import Carousel from 'primevue/carousel'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Skeleton from 'primevue/skeleton'
import Image from 'primevue/image'
import SidebarOfficial from '@/components/SidebarOfficial.vue'
import { formatDistanceToNow } from 'date-fns'
import { fr } from 'date-fns/locale'

const route = useRoute()
const router = useRouter()

const post = ref(null)
const loading = ref(true)

const siteUrl = computed(() => {
  if (typeof window === 'undefined') return ''
  return window.location.origin
})

const currentUrl = computed(() => {
  if (!siteUrl.value) return ''
  return `${siteUrl.value}${route.fullPath}`
})

// --- VIDÉO HELPERS ---
const isVideo = computed(() => post.value?.type === 'video')

const isMp4Video = computed(() => {
  const url = post.value?.video_url || ''
  const platform = post.value?.video_platform || ''
  return platform === 'mp4' || url.includes('.mp4')
})

const getYoutubeId = (url) => {
  if (!url) return null

  try {
    const u = new URL(url)

    if (u.hostname.includes('youtu.be')) {
      return u.pathname.replace('/', '') || null
    }

    if (u.searchParams.get('v')) {
      return u.searchParams.get('v')
    }

    if (u.pathname.includes('/embed/')) {
      return u.pathname.split('/embed/')[1]?.split('/')[0] || null
    }

    if (u.pathname.includes('/shorts/')) {
      return u.pathname.split('/shorts/')[1]?.split('/')[0] || null
    }

    return null
  } catch {
    if (url.includes('youtu.be/')) {
      return url.split('youtu.be/')[1]?.split('?')[0] || null
    }

    if (url.includes('watch?v=')) {
      return url.split('watch?v=')[1]?.split('&')[0] || null
    }

    if (url.includes('/embed/')) {
      return url.split('/embed/')[1]?.split('?')[0] || null
    }

    if (url.includes('/shorts/')) {
      return url.split('/shorts/')[1]?.split('?')[0] || null
    }

    return null
  }
}

const youtubeEmbedUrl = computed(() => {
  if (!isVideo.value) return null

  const url = post.value?.video_url || ''
  const platform = post.value?.video_platform || ''

  const isYoutubeSource =
    platform === 'youtube' ||
    url.includes('youtube.com') ||
    url.includes('youtu.be')

  if (!isYoutubeSource) return null

  const id = getYoutubeId(url)
  if (!id) return null

  return `https://www.youtube.com/embed/${id}?rel=0&modestbranding=1`
})

// --- MÉDIAS ---
const allMedia = computed(() => {
  const images = []
  if (post.value?.thumbnail) images.push({ file_path: post.value.thumbnail })
  if (post.value?.media?.length) images.push(...post.value.media)
  return images
})

const coverImage = computed(() => {
  const image = post.value?.thumbnail || post.value?.media?.[0]?.file_path || null
  if (!image || !siteUrl.value) return ''
  return `${siteUrl.value}/storage/${image}`
})

const plainTextFromHtml = (html = '') => {
  return html
    .replace(/<[^>]+>/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
}

const seoTitle = computed(() => {
  const base = post.value?.meta_title || post.value?.title || 'Communiqué officiel'
  return `${base} | Forces Armées Maliennes`
})

const seoDescription = computed(() => {
  const raw =
    post.value?.meta_description ||
    post.value?.excerpt ||
    plainTextFromHtml(post.value?.content || '') ||
    'Communiqué officiel des Forces Armées Maliennes.'

  return raw.substring(0, 160)
})

const seoType = computed(() => (isVideo.value ? 'video.other' : 'article'))

const publishedDate = computed(() => post.value?.published_at || post.value?.created_at || null)
const modifiedDate = computed(() => post.value?.updated_at || publishedDate.value || null)
const authorName = computed(() => post.value?.user?.name || 'DIRPA')

const newsArticleSchema = computed(() => {
  if (!post.value || !currentUrl.value) return null

  return {
    '@context': 'https://schema.org',
    '@type': 'NewsArticle',
    headline: post.value.title,
    description: seoDescription.value,
    image: coverImage.value ? [coverImage.value] : [],
    datePublished: publishedDate.value,
    dateModified: modifiedDate.value,
    mainEntityOfPage: {
      '@type': 'WebPage',
      '@id': currentUrl.value,
    },
    author: {
      '@type': 'Organization',
      name: authorName.value,
    },
    publisher: {
      '@type': 'Organization',
      name: 'Forces Armées Maliennes',
      logo: {
        '@type': 'ImageObject',
        url: siteUrl.value ? `${siteUrl.value}/images/logo-fama.png` : '',
      },
    },
  }
})

const videoSchema = computed(() => {
  if (!post.value || !currentUrl.value || !isVideo.value) return null

  return {
    '@context': 'https://schema.org',
    '@type': 'VideoObject',
    name: post.value.title,
    description: seoDescription.value,
    thumbnailUrl: coverImage.value ? [coverImage.value] : [],
    uploadDate: publishedDate.value,
    embedUrl: youtubeEmbedUrl.value || undefined,
    contentUrl: post.value.video_url || undefined,
    publisher: {
      '@type': 'Organization',
      name: 'Forces Armées Maliennes',
      logo: {
        '@type': 'ImageObject',
        url: siteUrl.value ? `${siteUrl.value}/images/logo-fama.png` : '',
      },
    },
    mainEntityOfPage: currentUrl.value,
  }
})

const breadcrumbSchema = computed(() => {
  if (!post.value || !currentUrl.value || !siteUrl.value) return null

  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: [
      {
        '@type': 'ListItem',
        position: 1,
        name: 'Accueil',
        item: siteUrl.value,
      },
      {
        '@type': 'ListItem',
        position: 2,
        name: 'Communiqués',
        item: `${siteUrl.value}/portfolio`,
      },
      {
        '@type': 'ListItem',
        position: 3,
        name: post.value.title,
        item: currentUrl.value,
      },
    ],
  }
})

useHead(() => {
  const scripts = []

  if (isVideo.value && videoSchema.value) {
    scripts.push({
      type: 'application/ld+json',
      children: JSON.stringify(videoSchema.value),
    })
  } else if (newsArticleSchema.value) {
    scripts.push({
      type: 'application/ld+json',
      children: JSON.stringify(newsArticleSchema.value),
    })
  }

  if (breadcrumbSchema.value) {
    scripts.push({
      type: 'application/ld+json',
      children: JSON.stringify(breadcrumbSchema.value),
    })
  }

  return {
    title: seoTitle.value,
    meta: [
      { name: 'description', content: seoDescription.value },
      { name: 'robots', content: 'index, follow, max-image-preview:large' },

      { property: 'og:title', content: seoTitle.value },
      { property: 'og:description', content: seoDescription.value },
      { property: 'og:type', content: seoType.value },
      { property: 'og:url', content: currentUrl.value },
      { property: 'og:site_name', content: 'Forces Armées Maliennes' },
      { property: 'og:locale', content: 'fr_FR' },
      { property: 'og:image', content: coverImage.value || `${siteUrl.value}/images/og-default.jpg` },

      { name: 'twitter:card', content: 'summary_large_image' },
      { name: 'twitter:title', content: seoTitle.value },
      { name: 'twitter:description', content: seoDescription.value },
      { name: 'twitter:image', content: coverImage.value || `${siteUrl.value}/images/og-default.jpg` },
    ],
    link: [
      { rel: 'canonical', href: currentUrl.value },
    ],
    script: scripts,
  }
})

// --- DATA FETCHING ---
onMounted(async () => {
  try {
    const res = await axios.get(`/api/posts/${route.params.slug}`)
    post.value = res.data.data || res.data
  } catch (e) {
    router.push('/portfolio')
  } finally {
    loading.value = false
  }
})

const getRelativeDate = (date) =>
  date
    ? formatDistanceToNow(new Date(date), { addSuffix: true, locale: fr })
    : ''

const openPdf = (path) => {
  window.open(`/storage/${path}`, '_blank')
}

// --- PARTAGE ---
const share = (platform) => {
  const fullUrl = typeof window !== 'undefined'
    ? window.location.origin + route.fullPath
    : currentUrl.value

  const rawTitle = post.value?.title || 'Communiqué'
  const shortTitle = rawTitle.length > 60 ? `${rawTitle.substring(0, 60)}...` : rawTitle
  const message = `${shortTitle}\n\n${fullUrl}`

  let shareUrl = ''

  if (platform === 'facebook') {
    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(fullUrl)}`
  } else if (platform === 'whatsapp') {
    shareUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(message)}`
  }

  if (shareUrl) {
    window.open(shareUrl, '_blank')
  }
}
</script>

<template>
  <div class="page-background staff-page-container">
    <a href="#main-content" class="skip-link">Passer au contenu principal</a>

    <main id="main-content" class="main-layout container" v-if="!loading && post">
      <article class="content-card staff-main-card" aria-labelledby="post-title">
        <nav class="top-nav" aria-label="Navigation de l’article">
          <Button
            icon="pi pi-arrow-left"
            label="Retour"
            link
            class="back-btn"
            @click="router.back()"
          />

          <div class="share-actions" aria-label="Actions de partage">
            <Button
              icon="pi pi-facebook"
              rounded
              text
              severity="secondary"
              @click="share('facebook')"
              aria-label="Partager sur Facebook"
            />
            <Button
              icon="pi pi-whatsapp"
              rounded
              text
              severity="secondary"
              @click="share('whatsapp')"
              aria-label="Partager sur WhatsApp"
            />
          </div>
        </nav>

        <header class="post-header">
          <div class="meta-badges">
            <Tag
              :value="post.type === 'video' ? 'VIDÉO OFFICIELLE' : (post.pdf_path ? 'DOCUMENT OFFICIEL' : 'COMMUNIQUÉ')"
              :severity="post.type === 'video' ? 'info' : (post.pdf_path ? 'danger' : 'success')"
              class="fama-tag"
            />

            <span class="publish-date">
              <i class="pi pi-calendar-plus mr-2" aria-hidden="true"></i>
              <time :datetime="post.published_at || post.created_at">
                {{ getRelativeDate(post.published_at || post.created_at) }}
              </time>
            </span>
          </div>

          <h1 id="post-title" class="post-title">{{ post.title }}</h1>
        </header>

        <section class="media-section" aria-label="Média principal de l’article">
          <div v-if="isVideo" class="video-container shadow-2">
            <iframe
              v-if="youtubeEmbedUrl"
              :src="youtubeEmbedUrl"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              allowfullscreen
              loading="lazy"
              referrerpolicy="strict-origin-when-cross-origin"
              :title="`Vidéo YouTube : ${post.title}`"
            ></iframe>

            <video
              v-else-if="isMp4Video"
              :src="post.video_url"
              controls
              playsinline
              preload="metadata"
              :title="post.title"
            ></video>

            <div v-else class="video-fallback">
              <i class="pi pi-exclamation-triangle" aria-hidden="true"></i>
              <p>Cette vidéo ne peut pas être affichée pour le moment.</p>
              <a
                v-if="post.video_url"
                :href="post.video_url"
                target="_blank"
                rel="noopener noreferrer"
              >
                Ouvrir la vidéo
              </a>
            </div>
          </div>

          <div v-else-if="allMedia.length > 0" class="carousel-wrapper staff-info-block">
            <Carousel
              :value="allMedia"
              :numVisible="1"
              :numScroll="1"
              circular
              :autoplayInterval="5000"
            >
              <template #item="slotProps">
                <figure class="image-slide">
                  <Image
                    :src="`/storage/${slotProps.data.file_path}`"
                    preview
                    imageClass="main-post-img"
                    :alt="slotProps.index === 0
                      ? `${post.title} - Illustration principale`
                      : `${post.title} - Image ${slotProps.index + 1}`"
                    :pt="{
                      image: {
                        loading: slotProps.index === 0 ? 'eager' : 'lazy',
                        decoding: 'async'
                      }
                    }"
                  />
                </figure>
              </template>
            </Carousel>
          </div>
        </section>

        <section class="post-body" aria-label="Contenu de l’article">
          <div class="rich-text-content" v-html="post.content"></div>

          <div v-if="post.pdf_path" class="pdf-action-card staff-info-block">
            <div class="pdf-info">
              <i class="pi pi-file-pdf pdf-icon" aria-hidden="true"></i>
              <div>
                <span class="pdf-title">Consulter le document officiel</span>
                <p class="pdf-sub">Format PDF - Certification DIRPA</p>
              </div>
            </div>

            <div class="pdf-buttons">
              <Button label="Lire" icon="pi pi-eye" text @click="openPdf(post.pdf_path)" />
              <a :href="`/storage/${post.pdf_path}`" download class="btn-download">
                <i class="pi pi-download mr-2" aria-hidden="true"></i>
                Télécharger
              </a>
            </div>
          </div>
        </section>

        <footer class="post-footer">
          <div class="signature-box">
            <div class="fama-divider"></div>
            <p class="signature-name">{{ post.user?.name || 'LA RÉDACTION' }}</p>
            <p class="signature-rank">
              Direction de l'Information et des Relations Publiques des Armées
            </p>
          </div>
        </footer>
      </article>

      <aside class="sidebar-column" aria-label="Informations complémentaires">
        <SidebarOfficial />
      </aside>
    </main>

    <div v-else class="container main-layout py-8" aria-live="polite" aria-busy="true">
      <div class="content-card staff-main-card">
        <Skeleton width="30%" height="2rem" class="mb-4"></Skeleton>
        <Skeleton width="100%" height="4rem" class="mb-6"></Skeleton>
        <Skeleton width="100%" height="400px" class="mb-4"></Skeleton>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page-background {
  min-height: 100vh;
  padding: 40px 0;
}

.skip-link {
  position: absolute;
  left: 16px;
  top: -60px;
  z-index: 2000;
  background: #14b82c;
  color: #fff;
  padding: 10px 14px;
  border-radius: 10px;
  text-decoration: none;
  font-weight: 700;
  transition: top 0.2s ease;
}

.skip-link:focus {
  top: 16px;
}

.container {
  width: 100%;
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 20px;
}

.main-layout {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 40px;
  align-items: start;
}

.content-card {
  width: 100%;
  padding: 40px 50px;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.top-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 14px;
  margin-bottom: 30px;
}

.post-header {
  margin-bottom: 35px;
}

.meta-badges {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.publish-date {
  font-size: 0.9rem;
  color: #8892b0;
  font-weight: 500;
}

.post-title {
  font-size: clamp(1.8rem, 4vw, 2.6rem);
  font-weight: 900;
  line-height: 1.15;
  color: var(--text-main, #1e293b);
  letter-spacing: -0.02em;
}

.media-section {
  margin-bottom: 40px;
}

.video-container {
  aspect-ratio: 16 / 9;
  border-radius: 16px;
  overflow: hidden;
  background: #000;
}

.video-container iframe,
.video-container video {
  width: 100%;
  height: 100%;
  display: block;
}

.carousel-wrapper {
  border-radius: 16px;
  overflow: hidden;
}

.image-slide {
  height: 550px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #000;
  margin: 0;
}

:deep(.main-post-img) {
  width: 100%;
  height: 550px;
  object-fit: contain;
}

.rich-text-content {
  font-size: 1.2rem;
  line-height: 1.8;
  color: var(--text-muted, #475569);
  margin-bottom: 50px;
  word-break: break-word;
}

.rich-text-content :deep(p) {
  margin-bottom: 1.5rem;
}

.pdf-action-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  padding: 20px 30px;
  border-radius: 16px;
  margin: 40px 0;
  border-left: 5px solid #ef4444;
}

.pdf-info {
  display: flex;
  align-items: center;
  gap: 20px;
}

.pdf-icon {
  font-size: 2.2rem;
  color: #ef4444;
}

.pdf-title {
  font-weight: 800;
  font-size: 1.1rem;
  display: block;
}

.pdf-sub {
  font-size: 0.85rem;
  opacity: 0.7;
  margin: 0;
}

.pdf-buttons {
  display: flex;
  align-items: center;
  gap: 15px;
  flex-wrap: wrap;
}

.btn-download {
  background: #14b82c;
  color: white;
  padding: 10px 20px;
  border-radius: 10px;
  text-decoration: none;
  font-weight: 800;
  transition: transform 0.2s;
}

.btn-download:hover {
  transform: translateY(-2px);
}

.post-footer {
  margin-top: 60px;
  padding-top: 40px;
  border-top: 1px solid rgba(148, 163, 184, 0.1);
}

.signature-box {
  text-align: right;
}

.fama-divider {
  width: 60px;
  height: 5px;
  background: #14b82c;
  margin-left: auto;
  margin-bottom: 15px;
}

.signature-name {
  font-size: 1.4rem;
  font-weight: 900;
  margin-bottom: 5px;
}

.signature-rank {
  font-size: 0.9rem;
  opacity: 0.7;
  font-weight: 500;
}

.sidebar-column {
  position: sticky;
  top: 20px;
  height: fit-content;
  align-self: start;
}

.video-fallback {
  width: 100%;
  height: 100%;
  min-height: 320px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 14px;
  background: #0f172a;
  color: #e2e8f0;
  text-align: center;
  padding: 20px;
}

.video-fallback i {
  font-size: 2rem;
  color: #facc15;
}

.video-fallback p {
  margin: 0;
  font-size: 1rem;
}

.video-fallback a {
  color: #ffd700;
  font-weight: 700;
  text-decoration: none;
}

@media (max-width: 1150px) {
  .main-layout {
    grid-template-columns: 1fr;
  }

  .sidebar-column {
    display: none;
  }
}

@media (max-width: 768px) {
  .page-background {
    padding: 0;
  }

  .container {
    max-width: 100%;
    padding: 0;
    margin: 0;
  }

  .main-layout {
    display: block;
    width: 100%;
  }

  .content-card {
    width: 100%;
    margin: 0;
    padding: 18px 16px 26px;
    border-radius: 0;
    box-shadow: none;
    border-left: none;
    border-right: none;
  }

  .top-nav {
    margin-bottom: 20px;
  }

  :deep(.back-btn .p-button-label) {
    display: none;
  }

  .post-header {
    margin-bottom: 24px;
  }

  .meta-badges {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 14px;
  }

  .publish-date {
    font-size: 0.9rem;
  }

  .post-title {
    font-size: 1.7rem;
    line-height: 1.24;
  }

  .media-section {
    margin-bottom: 28px;
  }

  .video-container,
  .carousel-wrapper {
    border-radius: 12px;
  }

  .image-slide {
    height: 300px;
  }

  :deep(.main-post-img) {
    height: 300px;
  }

  .rich-text-content {
    font-size: 1.02rem;
    line-height: 1.8;
    margin-bottom: 34px;
  }

  .pdf-action-card {
    flex-direction: column;
    align-items: stretch;
    padding: 16px;
    gap: 16px;
    margin: 28px 0;
    border-radius: 14px;
  }

  .pdf-info {
    gap: 14px;
    align-items: flex-start;
  }

  .pdf-icon {
    font-size: 2rem;
    margin-top: 2px;
  }

  .pdf-title {
    font-size: 1rem;
  }

  .pdf-sub {
    font-size: 0.84rem;
  }

  .pdf-buttons {
    width: 100%;
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }

  .pdf-buttons :deep(.p-button) {
    width: 100%;
    min-height: 48px;
    justify-content: center;
  }

  .btn-download {
    width: 100%;
    text-align: center;
    padding: 12px 16px;
  }

  .post-footer {
    margin-top: 34px;
    padding-top: 24px;
  }

  .signature-box {
    text-align: left;
  }

  .fama-divider {
    margin-left: 0;
  }

  .signature-name {
    font-size: 1.15rem;
  }

  .signature-rank {
    font-size: 0.88rem;
    line-height: 1.5;
  }
}

@media (max-width: 480px) {
  .content-card {
    padding: 16px 14px 22px;
  }

  .post-title {
    font-size: 1.5rem;
  }

  .publish-date {
    font-size: 0.84rem;
  }

  .image-slide {
    height: 250px;
  }

  :deep(.main-post-img) {
    height: 250px;
  }

  .rich-text-content {
    font-size: 0.96rem;
  }

  .top-nav :deep(.p-button) {
    min-width: 42px;
    min-height: 42px;
  }
}
</style>