<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useHead } from '@vueuse/head'
import axios from 'axios'
import Carousel from 'primevue/carousel';
// --- LOGIQUE DATE & COMPOSANTS ---
import { formatDistanceToNow } from 'date-fns'
import { fr } from 'date-fns/locale'
import SidebarOfficial from '@/components/SidebarOfficial.vue'

import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Skeleton from 'primevue/skeleton'
import Image from 'primevue/image'

const route = useRoute()
const router = useRouter()
const post = ref(null)
// -------------------- VIDEO HELPERS --------------------
const isVideo = computed(() => {
  return post.value?.type === 'video'
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
      return u.pathname.split('/embed/')[1] || null
    }

    return null
  } catch (e) {
    if (url.includes('youtu.be/'))
      return url.split('youtu.be/')[1]?.split('?')[0] || null

    if (url.includes('watch?v='))
      return url.split('watch?v=')[1]?.split('&')[0] || null

    return null
  }
}

const youtubeEmbedUrl = computed(() => {
  if (!isVideo.value) return null
  if (post.value?.video_platform !== 'youtube') return null

  const id = getYoutubeId(post.value?.video_url)
  return id ? `https://www.youtube.com/embed/${id}` : null
})

const isMp4Video = computed(() => {
  if (!isVideo.value) return false
  if (post.value?.video_platform === 'mp4') return true

  const url = post.value?.video_url || ''
  return url.toLowerCase().includes('.mp4')
})

const isFacebookVideo = computed(() => {
  if (!isVideo.value) return false
  return post.value?.video_platform === 'facebook'
})

const openExternalVideo = () => {
  if (!post.value?.video_url) return
  window.open(post.value.video_url, '_blank')
}
const loading = ref(true)
const baseUrl = window.location.origin
const allMedia = computed(() => {
  const images = [];
  if (post.value?.thumbnail) {
    images.push({ file_path: post.value.thumbnail, isThumbnail: true });
  }
  if (post.value?.media?.length) {
    images.push(...post.value.media);
  }
  return images;
});

// --- SEO DYNAMIQUE ---
useHead({
  title: computed(() => post.value ? `${post.value.title} | FAMa` : 'Chargement...'),
  meta: [
    { name: 'description', content: computed(() => post.value?.content?.substring(0, 160)) },
    { property: 'og:title', content: computed(() => post.value?.title) },
    { property: 'og:image', content: computed(() => post.value?.thumbnail ? `${baseUrl}/storage/${post.value.thumbnail}` : `${baseUrl}/assets/images/hero.jpg`) },
    { property: 'og:type', content: 'article' },
    { name: 'twitter:card', content: 'summary_large_image' }
  ]
})

onMounted(async () => {
  try {
    const res = await axios.get(`/api/posts/${route.params.slug}`)
    post.value = res.data.data || res.data
  } catch (e) {
    console.error("Post introuvable", e)
    router.push('/portfolio')
  } finally {
    loading.value = false
  }
})

// --- FONCTIONS ---
const getRelativeDate = (date) => {
  if (!date) return ''
  try {
    return formatDistanceToNow(new Date(date), { addSuffix: true, locale: fr })
  } catch (e) { return "Date inconnue" }
}

const getShareLink = (platform) => {
  const shareUrl = encodeURIComponent(window.location.href)
  const shareTitle = encodeURIComponent(`FAMa : ${post.value?.title}`)
  return platform === 'facebook'
    ? `https://www.facebook.com/sharer/sharer.php?u=${shareUrl}`
    : `https://api.whatsapp.com/send?text=${shareTitle}%20${shareUrl}`
}

const openPdf = (path) => {
  window.open(`/storage/${path}`, '_blank')


}

const responsiveOptions = ref([
    {
        breakpoint: '1024px',
        numVisible: 1,
        numScroll: 1
    },
    {
        breakpoint: '768px',
        numVisible: 1,
        numScroll: 1
    }
]);
</script>

<template>
  <div class="page-background">
    <main class="main-layout container" v-if="!loading && post">
      <div class="content-card">
        <Button
          icon="pi pi-arrow-left"
          label="Retour aux communiqués"
          link
          class="back-btn"
          @click="router.back()"
        />

        <header class="post-header">
          <div class="post-meta">
            <Tag
  :value="post.type === 'video' ? 'VIDÉO OFFICIELLE' : (post.pdf_path ? 'DOCUMENT OFFICIEL' : 'COMMUNIQUÉ')"
  :severity="post.type === 'video' ? 'info' : (post.pdf_path ? 'danger' : 'success')"
  class="fama-tag"
/>
            <span class="date">
              <i class="pi pi-clock mr-2"></i>
              {{ getRelativeDate(post.published_at || post.created_at) }}
            </span>
          </div>
          <h3 class="post-title">{{ post.title }}</h3>
        </header>
        <!-- ✅ VIDEO PLAYER (type=video) -->
<section v-if="isVideo" class="video-player-wrapper">
  <!-- YouTube -->
  <div v-if="youtubeEmbedUrl" class="video-embed">
    <iframe
      :src="youtubeEmbedUrl"
      title="Vidéo officielle"
      frameborder="0"
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
      allowfullscreen
    ></iframe>
  </div>

  <!-- MP4 direct -->
  <div v-else-if="isMp4Video" class="video-embed">
    <video :src="post.video_url" controls playsinline preload="metadata"></video>
  </div>

  <!-- Facebook / autres -->
  <div v-else class="video-fallback">
    <div class="video-fallback-card">
      <i class="pi pi-video"></i>
      <div class="video-fallback-text">
        <h4>Vidéo disponible</h4>
        <p>Cette vidéo est hébergée sur une plateforme externe. Cliquez sur “Ouvrir la vidéo”.</p>
      </div>
      <Button
        label="Ouvrir la vidéo"
        icon="pi pi-external-link"
        class="btn-open-video"
        @click="openExternalVideo"
      />
    </div>
  </div>
</section>

        <!-- <div class="post-main-image" v-if="post.thumbnail">
          <Image :src="`/storage/${post.thumbnail}`" preview imageClass="main-img-fluid" />
        </div> -->

        <section class="post-body">
          <div class="text-content rich-text" v-safe-links v-html="post.content"></div>

          <!-- <div v-if="post.media?.length" class="post-gallery">
            <div v-for="(item, index) in post.media" :key="index" class="gallery-item">
              <Image :src="`/storage/${item.file_path}`" preview imageClass="gallery-img" />
            </div>
          </div> -->
<div class="instagram-carousel-wrapper" v-if="!isVideo && allMedia.length > 0">  <Carousel :value="allMedia" :numVisible="1" :numScroll="1" :circular="false" :responsiveOptions="responsiveOptions">
    <template #item="slotProps">
      <div class="carousel-image-container">
        <div class="carousel-counter">
           {{ allMedia.findIndex(m => m.file_path === slotProps.data.file_path) + 1 }} / {{ allMedia.length }}
        </div>

        <Image
          :src="`/storage/${slotProps.data.file_path}`"
          preview
          imageClass="instagram-img"
        />
      </div>
    </template>
  </Carousel>
</div>
          <div class="post-signature-minimal" v-if="post.user">
            <div class="signature-line"></div>
            <p class="author-name-bottom">{{ post.user.name }}</p>
            <p class="author-sub">Direction de l'Information et des Relations Publiques des Armées</p>
                    <div class="share-wrapper">
  <Button icon="pi pi-share-alt" class="share-trigger-btn" rounded severity="secondary" />
  <div class="share-floating-menu">
    <a :href="getShareLink('facebook')" target="_blank" class="s-btn fb"><i class="pi pi-facebook"></i></a>
    <a :href="getShareLink('whatsapp')" target="_blank" class="s-btn wa"><i class="pi pi-whatsapp"></i></a>
  </div>
</div>
          </div>

          <div v-if="post.pdf_path" class="pdf-download-wrapper">
            <div class="pdf-minimal-bar">
              <div class="pdf-label">
                <i class="pi pi-file-pdf"></i>
                <span>Communiqué officiel (PDF)</span>
              </div>
              <div class="pdf-buttons">
                <Button
                  label="Consulter"
                  icon="pi pi-eye"
                  text
                  class="p-button-sm btn-view"
                  @click="openPdf(post.pdf_path)"
                />
                <a :href="`/storage/${post.pdf_path}`" download class="btn-download-fama-mini">
                  <i class="pi pi-download"></i>
                  <span>Télécharger</span>
                </a>
              </div>
            </div>
          </div>
        </section>
      </div>

      <aside class="sidebar-column">
        <SidebarOfficial />
      </aside>
    </main>

    <div v-else-if="loading" class="container main-layout mt-5">
      <div class="content-card">
        <Skeleton width="150px" height="2rem" class="mb-5"></Skeleton>
        <Skeleton width="100%" height="400px" class="mb-5"></Skeleton>
        <Skeleton width="80%" height="2rem" class="mb-3"></Skeleton>
        <Skeleton width="60%" height="2rem"></Skeleton>
      </div>
    </div>
  </div>
</template>

<style scoped>

.carousel-counter {
  position: absolute;
  top: 15px;
  right: 15px;
  background: transparent; /* Changé de #000 à transparent */
  border: none;
  color: white;
  padding: 4px 12px;
  border-radius: 16px;
  font-size: 0.8rem;
  z-index: 10;
  pointer-events: none;
}
.carousel-image-container { position: relative; } /* Important pour le positionnement du badge */
/* Container principal du carrousel */
.instagram-carousel-wrapper {
 margin: 30px 0;
  border-radius: 16px;
  overflow: hidden;
  background: transparent; /* Changé de #000 à transparent */
  border: none;
}

/* Fixer la hauteur pour que toutes les images s'alignent */
.carousel-image-container {
 height: 500px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
}

:deep(.instagram-img) {
  width: 100%;
  height: 500px;
  object-fit: contain; /* L'image entière est visible sans être rognée */
  display: block;
}

/* Personnalisation des indicateurs (petits points) */
:deep(.p-carousel-indicators) {
  padding: 1rem;
}

:deep(.p-carousel-indicator button) {
  width: 8px !important;
  height: 8px !important;
  border-radius: 50%;
  background-color: #cbd5e1 !important;
}

:deep(.p-carousel-indicator.p-highlight button) {
  background-color: #14B82C !important; /* Ta couleur verte FAMa */
}
.page-background { background: #f8fafc; min-height: 100vh; padding: 40px 0; }
.container { max-width: 1240px; margin: 0 auto; padding: 0 20px; }
.main-layout { display: grid; grid-template-columns: 1fr 340px; gap: 40px; }
.content-card { background: white; padding: 50px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); border: 1px solid #f1f5f9; }

/* Boutons & Meta */
.back-btn { color: #14B82C !important; padding: 0 !important; font-weight: 700; transition: 0.2s; margin-bottom: 20px; }
.back-btn:hover { transform: translateX(-5px); }
.post-title {
    font-size: clamp(1.5rem, 3vw, 2.2rem);
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2; margin: 15px 0;
     letter-spacing: -0.5px; }
.post-meta { display: flex; gap: 20px; align-items: center; color: #64748b; font-size: 0.95rem; }

.share-wrapper {
     position: relative;
     display:flex;
     margin-bottom: 30px;
    padding: 10px 0; }
.share-floating-menu {
  position: absolute;
  top: 0;
  left: 50px;
  display: flex;
  gap: 10px;
  opacity: 0;
  transform: translateX(-10px);
  transition: 0.3s;
  pointer-events: none;
}

/* Apparaît quand on survole le bouton ou le wrapper */
.share-wrapper:hover .share-floating-menu {
  opacity: 1;
  transform: translateX(0);
  pointer-events: auto;
}

.image-container-fixed {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}
/* Media */

/* ---------------- VIDEO PLAYER ---------------- */
.video-player-wrapper{
  margin: 25px 0 35px;
}

.video-embed{
  width: 100%;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid #e2e8f0;
  box-shadow: 0 8px 22px rgba(0,0,0,0.06);
  background: #000;
  position: relative;
  aspect-ratio: 16 / 9;
}

.video-embed iframe,
.video-embed video{
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
}

.video-fallback-card{
  display:flex;
  gap: 14px;
  align-items: center;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  padding: 16px 18px;
  border-radius: 14px;
}

.video-fallback-card i{
  font-size: 1.8rem;
  color: #0f172a;
}

.video-fallback-text h4{
  margin: 0 0 3px;
  font-weight: 900;
  color:#0f172a;
}

.video-fallback-text p{
  margin: 0;
  color:#475569;
  font-size: 0.95rem;
}

.btn-open-video{
  margin-left: auto;
  font-weight: 800;
}

:deep(.main-img-fluid) {
    height: auto;
     width: 100%; max-height: 700px;
      object-fit: contain !important; }
/* .post-gallery { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 40px 0; } */
.gallery-item { border-radius: 12px; overflow: hidden; height: 200px; }
:deep(.gallery-img) { width: 100%; height: 100%; object-fit: contain; }

/* Partage */
.share-bar { display: flex; align-items: center; gap: 15px; background: #f1f5f9; padding: 10px 20px; border-radius: 50px; width: fit-content; margin-bottom: 40px; }
.s-btn { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; transition: 0.3s; }
.fb { background: #1877f2; } .wa { background: #22c55e; }

/* Texte & Signature */
.text-content { font-size: 1.25rem; line-height: 1.9; color: #334155; white-space: pre-wrap; margin-bottom: 50px; }
.post-signature-minimal { border-top: 2px solid #f1f5f9; padding-top: 30px; margin-top: 60px; text-align: right; }
.signature-line { width: 60px; height: 4px; background: #14B82C; margin-left: auto; margin-bottom: 15px; }
.author-name-bottom { font-size: 1.3rem; font-weight: 900; color: #1e293b; text-transform: uppercase; margin: 0; }


/* STYLE PDF MINIMALISTE */
.pdf-download-wrapper {
  margin-top: 40px;
  padding-top: 30px;
  border-top: 1px dashed #e2e8f0;
}
.pdf-minimal-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #f8fafc;
  padding: 12px 20px;
  border-radius: 12px;
  border: 1px solid #edf2f7;
}
.pdf-label { display: flex; align-items: center; gap: 12px; color: #475569; font-weight: 600; font-size: 0.95rem; }
.pdf-label i { color: #ef4444; font-size: 1.2rem; }
.pdf-buttons { display: flex; align-items: center; gap: 10px; }
.btn-view { color: #64748b !important; }
.btn-download-fama-mini {
  background: #14B82C;
  color: white;
  padding: 7px 15px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 700;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
}
.btn-download-fama-mini:hover { background: #119e25; transform: translateY(-1px); }

/* Sidebar & Mobile */
.sidebar-column { position: sticky; top: 100px; height: fit-content; }

@media (max-width: 1100px) {
  .main-layout { grid-template-columns: 1fr; }
  .sidebar-column { display: none; }
}
@media (max-width: 768px) {
  .content-card { padding: 30px 20px; }
  .post-main-image { margin: 30px -20px; }
  .pdf-minimal-bar { flex-direction: column; gap: 15px; align-items: flex-start; }
  .pdf-buttons { width: 100%; justify-content: space-between; }
  .carousel-image-container, :deep(.instagram-img) {
    height: 400px;
  }
  :deep(.p-carousel-prev), :deep(.p-carousel-next) {
    display: none;
  }
}
/* AJOUTE CECI DANS TON <style scoped> */

.rich-text :deep(b), .rich-text :deep(strong) {
  font-weight: 800;
  color: #0f172a;
}

.rich-text :deep(ul) {
  list-style-type: disc !important;
  margin-left: 1.5rem !important;
  margin-bottom: 1.5rem;
}

.rich-text :deep(ol) {
  list-style-type: decimal !important;
  margin-left: 1.5rem !important;
  margin-bottom: 1.5rem;
}

.rich-text :deep(li) {
  margin-bottom: 0.5rem;
}

.rich-text :deep(h2), .rich-text :deep(h3) {
  font-weight: 800;
  margin-top: 2rem;
  margin-bottom: 1rem;
  color: #1a241b;
}

.rich-text :deep(a) {
  color: #14B82C;
  text-decoration: underline;
  font-weight: 600;
}

.rich-text :deep(blockquote) {
  border-left: 4px solid #14B82C;
  padding-left: 1.5rem;
  font-style: italic;
  color: #475569;
  margin: 1.5rem 0;
}
</style>
