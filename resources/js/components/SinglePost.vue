<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useHead } from '@vueuse/head'
import axios from 'axios'

// --- COMPOSANTS & LOGIQUE ---
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

// --- VIDÉO HELPERS ---
const isVideo = computed(() => post.value?.type === 'video')
const isMp4Video = computed(() => post.value?.video_platform === 'mp4' || post.value?.video_url?.includes('.mp4'))

const youtubeEmbedUrl = computed(() => {
  if (!isVideo.value || !post.value?.video_url?.includes('youtube')) return null
  const id = post.value.video_url.split('v=')[1]?.split('&')[0] || post.value.video_url.split('/').pop()
  return `https://www.youtube.com/embed/${id}`
})

// --- MÉDIAS ---
const allMedia = computed(() => {
  const images = []
  if (post.value?.thumbnail) images.push({ file_path: post.value.thumbnail })
  if (post.value?.media?.length) images.push(...post.value.media)
  return images
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

const getRelativeDate = (date) => date ? formatDistanceToNow(new Date(date), { addSuffix: true, locale: fr }) : ''
const openPdf = (path) => window.open(`/storage/${path}`, '_blank')
</script>

<template>
  <div class="page-background staff-page-container">
    <main class="main-layout container" v-if="!loading && post">

      <article class="content-card staff-main-card">
        <nav class="top-nav">
          <Button icon="pi pi-arrow-left" label="Retour" link class="back-btn" @click="router.back()" />
          <div class="share-actions">
             <Button icon="pi pi-facebook" rounded text severity="secondary" />
             <Button icon="pi pi-whatsapp" rounded text severity="secondary" />
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
              <i class="pi pi-calendar-plus mr-2"></i>
              {{ getRelativeDate(post.published_at || post.created_at) }}
            </span>
          </div>
          <h1 class="post-title">{{ post.title }}</h1>
        </header>

        <section class="media-section">
          <div v-if="isVideo" class="video-container shadow-2">
            <iframe v-if="youtubeEmbedUrl" :src="youtubeEmbedUrl" frameborder="0" allowfullscreen></iframe>
            <video v-else-if="isMp4Video" :src="post.video_url" controls></video>
          </div>

          <div v-else-if="allMedia.length > 0" class="carousel-wrapper staff-info-block">
            <Carousel :value="allMedia" :numVisible="1" :numScroll="1" circular :autoplayInterval="5000">
              <template #item="slotProps">
                <div class="image-slide">
                  <Image :src="`/storage/${slotProps.data.file_path}`" preview imageClass="main-post-img" />
                </div>
              </template>
            </Carousel>
          </div>
        </section>

        <section class="post-body">
          <div class="rich-text-content" v-html="post.content"></div>

          <div v-if="post.pdf_path" class="pdf-action-card staff-info-block">
            <div class="pdf-info">
              <i class="pi pi-file-pdf pdf-icon"></i>
              <div>
                <span class="pdf-title">Consulter le document officiel</span>
                <p class="pdf-sub">Format PDF - Certification DIRPA</p>
              </div>
            </div>
            <div class="pdf-buttons">
              <Button label="Lire" icon="pi pi-eye" text @click="openPdf(post.pdf_path)" />
              <a :href="`/storage/${post.pdf_path}`" download class="btn-download">
                <i class="pi pi-download mr-2"></i> Télécharger
              </a>
            </div>
          </div>
        </section>

        <footer class="post-footer">
          <div class="signature-box">
            <div class="fama-divider"></div>
            <p class="signature-name">{{ post.user?.name || 'LA RÉDACTION' }}</p>
            <p class="signature-rank">Direction de l'Information et des Relations Publiques des Armées</p>
          </div>
        </footer>
      </article>

      <aside class="sidebar-column">
        <SidebarOfficial />
      </aside>
    </main>

    <div v-else class="container main-layout py-8">
      <div class="content-card staff-main-card">
        <Skeleton width="30%" height="2rem" class="mb-4"></Skeleton>
        <Skeleton width="100%" height="4rem" class="mb-6"></Skeleton>
        <Skeleton width="100%" height="400px" class="mb-4"></Skeleton>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* --- MISE EN PAGE GLOBALE --- */
.page-background { min-height: 100vh; padding: 40px 0; }
.main-layout { display: grid; grid-template-columns: 1fr 360px; gap: 40px; align-items: start; }

.content-card {
  padding: 40px 50px;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.08);
  border: 1px solid rgba(255, 255, 255, 0.05);
}

/* --- HEADER --- */
.top-nav { display: flex; justify-content: space-between; margin-bottom: 30px; }
.post-header { margin-bottom: 35px; }
.meta-badges { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; }
.publish-date { font-size: 0.9rem; color: #8892b0; font-weight: 500; }
.post-title {
  font-size: clamp(1.8rem, 4vw, 2.6rem);
  font-weight: 900;
  line-height: 1.15;
  color: var(--text-main, #1e293b);
  letter-spacing: -0.02em;
}

/* --- MÉDIA --- */
.media-section { margin-bottom: 40px; }
.video-container {
  aspect-ratio: 16/9;
  border-radius: 16px;
  overflow: hidden;
  background: #000;
}
.video-container iframe, .video-container video { width: 100%; height: 100%; }

.carousel-wrapper { border-radius: 16px; overflow: hidden; }
.image-slide { height: 550px; display: flex; align-items: center; justify-content: center; background: #000; }
:deep(.main-post-img) { width: 100%; height: 550px; object-fit: contain; }

/* --- CONTENU --- */
.rich-text-content {
  font-size: 1.2rem;
  line-height: 1.8;
  color: var(--text-muted, #475569);
  margin-bottom: 50px;
}
.rich-text-content :deep(p) { margin-bottom: 1.5rem; }

/* --- PDF CARD --- */
.pdf-action-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 30px;
  border-radius: 16px;
  margin: 40px 0;
  border-left: 5px solid #ef4444;
}
.pdf-info { display: flex; align-items: center; gap: 20px; }
.pdf-icon { font-size: 2.2rem; color: #ef4444; }
.pdf-title { font-weight: 800; font-size: 1.1rem; display: block; }
.pdf-sub { font-size: 0.85rem; opacity: 0.7; margin: 0; }
.pdf-buttons { display: flex; align-items: center; gap: 15px; }
.btn-download {
  background: #14B82C;
  color: white;
  padding: 10px 20px;
  border-radius: 10px;
  text-decoration: none;
  font-weight: 800;
  transition: transform 0.2s;
}
.btn-download:hover { transform: translateY(-2px); }

/* --- FOOTER & SIGNATURE --- */
.post-footer { margin-top: 60px; padding-top: 40px; border-top: 1px solid rgba(148, 163, 184, 0.1); }
.signature-box { text-align: right; }
.fama-divider { width: 60px; height: 5px; background: #14B82C; margin-left: auto; margin-bottom: 15px; }
.signature-name { font-size: 1.4rem; font-weight: 900; margin-bottom: 5px; }
.signature-rank { font-size: 0.9rem; opacity: 0.7; font-weight: 500; }
.sidebar-column {
  position: -webkit-sticky; /* Pour la compatibilité Safari */
  position: sticky;
  top: 20px; /* Distance par rapport au haut du navigateur */
  height: fit-content; /* Important : le bloc ne doit pas faire toute la hauteur */
  align-self: start; /* Empêche le sidebar de s'étirer verticalement */
}

/* --- RESPONSIVE --- */
@media (max-width: 1150px) {
  .main-layout { grid-template-columns: 1fr; }
  .sidebar-column { display: none; }
}
@media (max-width: 768px) {
  .content-card { padding: 30px 20px; border-radius: 0; }
  .image-slide { height: 400px; }
  :deep(.main-post-img) { height: 400px; }
  .pdf-action-card { flex-direction: column; gap: 20px; align-items: flex-start; }
  .pdf-buttons { width: 100%; justify-content: space-between; }
}
</style>
