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

    if (u.hostname.includes('youtu.be')) return u.pathname.replace('/', '') || null
    if (u.searchParams.get('v')) return u.searchParams.get('v')
    if (u.pathname.includes('/embed/')) return u.pathname.split('/embed/')[1]?.split('/')[0] || null
    if (u.pathname.includes('/shorts/')) return u.pathname.split('/shorts/')[1]?.split('/')[0] || null

    return null
  } catch {
    if (url.includes('youtu.be/')) return url.split('youtu.be/')[1]?.split('?')[0] || null
    if (url.includes('watch?v=')) return url.split('watch?v=')[1]?.split('&')[0] || null
    if (url.includes('/embed/')) return url.split('/embed/')[1]?.split('?')[0] || null
    if (url.includes('/shorts/')) return url.split('/shorts/')[1]?.split('?')[0] || null

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

const allMedia = computed(() => {
  const images = []
  if (post.value?.thumbnail) images.push({ file_path: post.value.thumbnail })
  if (post.value?.media?.length) images.push(...post.value.media)
  return images
})

const coverImage = computed(() => {
  const image = post.value?.thumbnail || post.value?.media?.[0]?.file_path || null

  if (image && siteUrl.value) {
    return `${siteUrl.value}/storage/${image}`
  }

  if (siteUrl.value) {
    return `${siteUrl.value}/images/og-default.jpg`
  }

  return ''
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
        name: 'Actualités',
        item: `${siteUrl.value}/actualites`,
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

      { property: 'og:image', content: coverImage.value },
      { property: 'og:image:secure_url', content: coverImage.value },
      { property: 'og:image:alt', content: post.value?.title || 'Forces Armées Maliennes' },
      { property: 'og:image:width', content: '1200' },
      { property: 'og:image:height', content: '630' },

      { name: 'twitter:card', content: 'summary_large_image' },
      { name: 'twitter:title', content: seoTitle.value },
      { name: 'twitter:description', content: seoDescription.value },
      { name: 'twitter:image', content: coverImage.value },
    ],
    link: [{ rel: 'canonical', href: currentUrl.value }],
    script: scripts,
  }
})

onMounted(async () => {
  try {
    const res = await axios.get(`/api/posts/${route.params.slug}`)
    post.value = res.data.data || res.data
  } catch (e) {
    router.push('/actualites')
  } finally {
    loading.value = false
  }
})

const getRelativeDate = (date) =>
  date
    ? formatDistanceToNow(new Date(date), { addSuffix: true, locale: fr })
    : ''

const openPdf = (path) => {
  if (!path) return
  window.open(`/storage/${path}`, '_blank')
}

const share = (platform) => {
  const fullUrl = typeof window !== 'undefined'
    ? window.location.origin + route.fullPath
    : currentUrl.value

  const rawTitle = post.value?.title || 'Communiqué'
  const shortTitle = rawTitle.length > 70 ? `${rawTitle.substring(0, 70)}...` : rawTitle
  const message = `${shortTitle}\n\n${fullUrl}`

  let shareUrl = ''

  if (platform === 'facebook') {
    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(fullUrl)}`
  } else if (platform === 'whatsapp') {
    shareUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(message)}`
  }

  if (shareUrl) window.open(shareUrl, '_blank')
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
            class="back-btn-modern"
            @click="router.back()"
          />

          <div class="share-actions-minimal" aria-label="Actions de partage">
            <button
              class="minimal-share-btn"
              type="button"
              @click="share('facebook')"
              aria-label="Partager sur Facebook"
            >
              <i class="pi pi-facebook" aria-hidden="true"></i>
            </button>

            <button
              class="minimal-share-btn"
              type="button"
              @click="share('whatsapp')"
              aria-label="Partager sur WhatsApp"
            >
              <i class="pi pi-whatsapp" aria-hidden="true"></i>
            </button>
          </div>
        </nav>

        <header class="post-header">
          <div class="meta-badges">
            <Tag
              :value="post.type === 'video' ? 'VIDÉO OFFICIELLE' : (post.pdf_path ? 'DOCUMENT OFFICIEL' : 'ACTUALITÉ')"
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
          <div v-if="isVideo" class="video-container">
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

          <div v-if="post.pdf_path" class="pdf-action-card-modern">
            <div class="pdf-info-modern">
              <div class="pdf-icon-box">
                <i class="pi pi-file-pdf" aria-hidden="true"></i>
              </div>

              <div>
                <span class="pdf-title-modern">Document officiel joint</span>
                <p class="pdf-sub-modern">
                  Consultez ou téléchargez le fichier PDF publié officiellement par les FAMa.
                </p>
              </div>
            </div>

            <div class="pdf-buttons-modern">
              <Button
                label="Ouvrir le PDF"
                icon="pi pi-eye"
                class="pdf-read-btn"
                @click="openPdf(post.pdf_path)"
              />

              <a :href="`/storage/${post.pdf_path}`" download class="pdf-download-btn">
                <i class="pi pi-download" aria-hidden="true"></i>
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
  grid-template-columns: minmax(0, 1fr) 360px;
  gap: 40px;
  align-items: start;
}

.content-card {
  width: 100%;
  padding: 40px 50px;
  border-radius: 22px;
  background: rgba(255, 255, 255, 0.96);
  box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
  border: 1px solid rgba(148, 163, 184, 0.18);
}

.top-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 14px;
  margin-bottom: 30px;
}

.back-btn-modern {
  border: 1px solid rgba(20, 184, 44, 0.22) !important;
  background: #f8fafc !important;
  color: #1e293b !important;
  border-radius: 999px !important;
  font-weight: 800 !important;
  padding: 0.7rem 1rem !important;
}

.back-btn-modern:hover {
  background: #eef7f0 !important;
  color: #0f7a21 !important;
}

.share-actions-minimal {
  display: flex;
  align-items: center;
  gap: 8px;
}

.minimal-share-btn {
  width: 42px;
  height: 42px;
  border-radius: 999px;
  border: 1px solid rgba(148, 163, 184, 0.28);
  background: #ffffff;
  color: #334155;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.minimal-share-btn:hover {
  background: #f8fafc;
  color: #14b82c;
  transform: translateY(-1px);
  box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
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
  font-size: 0.92rem;
  color: #64748b;
  font-weight: 700;
}

.post-title {
  font-size: clamp(1.8rem, 4vw, 2.7rem);
  font-weight: 950;
  line-height: 1.13;
  color: var(--text-main, #0f172a);
  letter-spacing: -0.03em;
}

.media-section {
  margin-bottom: 40px;
}

.video-container {
  aspect-ratio: 16 / 9;
  border-radius: 18px;
  overflow: hidden;
  background: #0f172a;
  box-shadow: 0 18px 35px rgba(15, 23, 42, 0.16);
}

.video-container iframe,
.video-container video {
  width: 100%;
  height: 100%;
  display: block;
}

.carousel-wrapper {
  border-radius: 18px;
  overflow: hidden;
  background: #ffffff;
  border: 1px solid rgba(148, 163, 184, 0.22);
  box-shadow: 0 18px 35px rgba(15, 23, 42, 0.1);
}

.image-slide {
  min-height: 520px;
  max-height: 680px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ffffff;
  margin: 0;
  padding: 14px;
}

:deep(.main-post-img) {
  width: 100%;
  height: auto;
  max-height: 650px;
  object-fit: contain;
  display: block;
  border-radius: 12px;
  background: #ffffff;
}

.rich-text-content {
  font-size: 1.18rem;
  line-height: 1.85;
  color: var(--text-muted, #475569);
  margin-bottom: 50px;
  word-break: break-word;
}

.rich-text-content :deep(p) {
  margin-bottom: 1.5rem;
}

.rich-text-content :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 14px;
}

.pdf-action-card-modern {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 24px;
  padding: 22px;
  border-radius: 18px;
  margin: 38px 0;
  background: linear-gradient(135deg, #fff7f7 0%, #ffffff 55%, #f8fafc 100%);
  border: 1px solid rgba(239, 68, 68, 0.18);
  box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
}

.pdf-info-modern {
  display: flex;
  align-items: center;
  gap: 18px;
}

.pdf-icon-box {
  width: 58px;
  height: 58px;
  border-radius: 16px;
  background: #fee2e2;
  color: #dc2626;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.pdf-icon-box i {
  font-size: 1.9rem;
}

.pdf-title-modern {
  font-weight: 900;
  font-size: 1.08rem;
  color: #0f172a;
  display: block;
}

.pdf-sub-modern {
  font-size: 0.92rem;
  color: #64748b;
  margin: 4px 0 0;
  line-height: 1.5;
}

.pdf-buttons-modern {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.pdf-read-btn {
  background: #0f172a !important;
  border-color: #0f172a !important;
  color: #fff !important;
  border-radius: 12px !important;
  font-weight: 850 !important;
}

.pdf-download-btn {
  min-height: 42px;
  background: #14b82c;
  color: white;
  padding: 0.7rem 1rem;
  border-radius: 12px;
  text-decoration: none;
  font-weight: 850;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: transform 0.2s ease, filter 0.2s ease;
}

.pdf-download-btn:hover {
  transform: translateY(-1px);
  filter: brightness(0.96);
}

.post-footer {
  margin-top: 58px;
  padding-top: 34px;
  border-top: 1px solid rgba(148, 163, 184, 0.18);
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
  border-radius: 99px;
}

.signature-name {
  font-size: 1.4rem;
  font-weight: 950;
  margin-bottom: 5px;
  color: #0f172a;
}

.signature-rank {
  font-size: 0.9rem;
  opacity: 0.75;
  font-weight: 600;
  color: #475569;
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
  font-weight: 800;
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

  :deep(.back-btn-modern .p-button-label) {
    display: none;
  }

  .minimal-share-btn {
    width: 40px;
    height: 40px;
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

  .post-title {
    font-size: 1.7rem;
    line-height: 1.24;
  }

  .image-slide {
  min-height: 430px;
  max-height: 620px;
  padding: 6px;
}

:deep(.main-post-img) {
  width: 100%;
  max-height: 600px;
  border-radius: 10px;
}

  .rich-text-content {
    font-size: 1.02rem;
    line-height: 1.8;
    margin-bottom: 34px;
  }

  .pdf-action-card-modern {
    flex-direction: column;
    align-items: stretch;
    padding: 16px;
  }

  .pdf-buttons-modern {
    width: 100%;
    flex-direction: column;
    align-items: stretch;
  }

  .pdf-buttons-modern :deep(.p-button),
  .pdf-download-btn {
    width: 100%;
    min-height: 48px;
    justify-content: center;
  }

  .signature-box {
    text-align: left;
  }

  .fama-divider {
    margin-left: 0;
  }
}

@media (max-width: 480px) {
  .content-card {
    padding: 16px 14px 22px;
  }

  .post-title {
    font-size: 1.5rem;
  }

  .image-slide {
    min-height: 240px;
    max-height: 360px;
  }

  :deep(.main-post-img) {
    max-height: 340px;
  }

  .rich-text-content {
    font-size: 0.96rem;
  }
}
</style>