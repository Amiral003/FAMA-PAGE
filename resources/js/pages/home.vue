<script setup>
import { ref, onMounted, computed, onUnmounted, nextTick } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useHead } from '@unhead/vue'
import Skeleton from 'primevue/skeleton'
import Button from 'primevue/button'
import Card from 'primevue/card'
import SidebarOfficial from '@/components/SidebarOfficial.vue'

// Importations des images groupées
import heroImg from '@/assets/images/FAMA-IMAGE/37.jpg'
import famaImg from '@/assets/images/FAMA-IMAGE/6.jpg'
import maliImg from '@/assets/images/FAMA-IMAGE/33.jpg'
import heroImg1 from '@/assets/images/FAMA-IMAGE/30.jpg'
import famaImg1 from '@/assets/images/FAMA-IMAGE/29.jpg'
import maliImg1 from '@/assets/images/FAMA-IMAGE/46.jpg'
import heroImg2 from '@/assets/images/FAMA-IMAGE/32.jpg'
import famaImg2 from '@/assets/images/FAMA-IMAGE/26.jpg'
import maliImg2 from '@/assets/images/FAMA-IMAGE/9.jpg'
import heroImg12 from '@/assets/images/FAMA-IMAGE/34.jpg'
import famaImg12 from '@/assets/images/FAMA-IMAGE/43.jpg'
import maliImg12 from '@/assets/images/FAMA-IMAGE/44.jpg'
import photoCover from '@/assets/images/FAMA-IMAGE/39.jpg'
import videoCover from '@/assets/images/FAMA-IMAGE/7.jpg'

const router = useRouter()

useHead({
  title: 'Accueil | FAMa - Portail Officiel des Forces Armées Maliennes',
  meta: [
    { name: 'description', content: "Portail officiel des FAMa. Retrouvez les communiqués de l'État-Major, l'actualité de la défense et les rapports officiels sur la sécurité du Mali." },
    { name: 'keywords', content: 'FAMa, Armée Malienne, Défense Mali, Sécurité Mali, Communiqués officiels' },
    { property: 'og:type', content: 'website' },
    { property: 'og:url', content: 'https://votre-site-fama.ml/' },
    { property: 'og:title', content: 'FAMa - Engagement Sans Faille pour la Patrie' },
    { property: 'og:description', content: "Information vérifiée de l'État-Major Général des Armées du Mali." },
    { property: 'og:image', content: '/assets/images/hero.jpg' },
    { property: 'og:image:alt', content: 'Forces Armées Maliennes' },
    { name: 'twitter:card', content: 'summary_large_image' },
    { name: 'twitter:title', content: 'FAMa Officiel | Forces Armées Maliennes' },
    { name: 'twitter:image', content: '/assets/images/hero.jpg' },
    { name: 'twitter:image:alt', content: 'Forces Armées Maliennes' }
  ],
  link: [
    { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
    { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' }
  ]
})

// Variables d'état
const currentBgIndex = ref(0)
const backgroundImages = [heroImg, famaImg, maliImg, heroImg1, famaImg1, maliImg1, heroImg2, famaImg2, maliImg2, heroImg12, famaImg12, maliImg12]
const posts = ref([])
const loading = ref(true)
const error = ref(null)
const showScrollTop = ref(false)

let backgroundInterval = null
let observer = null

// Utilitaires de traitement de chaînes
const truncateTitle = (title, limit = 80) => {
  if (!title) return ''
  return title.length > limit ? `${title.substring(0, limit).trim()}...` : title
}

const stripHtml = (html) => {
  if (!html) return ''
  return html.replace(/<\/?[^>]+(>|$)/g, "") // Optimisé: Évite l'instanciation lourde de DOMParser
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return isNaN(d.getTime()) ? '' : d.toLocaleDateString('fr-FR')
}

// Gestionnaires d'événements défilés
const handleScroll = () => {
  showScrollTop.value = window.scrollY > 500
}

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Préchargement et Lazy Loading
const preloadImages = () => {

    const img = new Image()
   img.src = backgroundImages[0]
    img.fetchPriority = 'high'

  ;[photoCover, videoCover].forEach((src) => {
    const img = new Image()
    img.src = src
  })
}

const setupLazyLoading = () => {
  const lazyImages = document.querySelectorAll('img[data-src]')
  if (!lazyImages.length) return

  if ('IntersectionObserver' in window) {
    observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return
        const img = entry.target
        if (img.dataset.src) {
          img.src = img.dataset.src
          img.removeAttribute('data-src')
        }
        observer.unobserve(img)
      })
    }, { rootMargin: '100px 0px' })

    lazyImages.forEach((img) => observer.observe(img))
  } else {
    lazyImages.forEach((img) => {
      if (img.dataset.src) {
        img.src = img.dataset.src
        img.removeAttribute('data-src')
      }
    })
  }
}

// Récupération des données API
const fetchLatestPosts = async () => {
  try {
    loading.value = true
    error.value = null
    const res = await axios.get('/api/posts/latest', { timeout: 10000 })
    posts.value = Array.isArray(res.data) ? res.data : (res.data?.data ?? [])
  } catch (e) {
    console.error('Erreur chargement posts:', e)
    error.value = 'Impossible de charger les dernières publications'
  } finally {
    loading.value = false
    await nextTick()
    setupLazyLoading()
  }
}

onMounted(async () => {
  preloadImages()
  backgroundInterval = setInterval(() => {
    currentBgIndex.value = (currentBgIndex.value + 1) % backgroundImages.length
  }, 5500)

  await fetchLatestPosts()
  window.addEventListener('scroll', handleScroll, { passive: true })
})

onUnmounted(() => {
  if (backgroundInterval) clearInterval(backgroundInterval)
  if (observer) observer.disconnect()
  window.removeEventListener('scroll', handleScroll)
})

// Logique de récupération des miniatures médias
const getYoutubeId = (url) => {
  if (!url) return null
  try {
    const u = new URL(url)
    if (u.hostname.includes('youtu.be')) return u.pathname.replace('/', '') || null
    if (u.searchParams.get('v')) return u.searchParams.get('v')
    if (u.pathname.includes('/embed/')) return u.pathname.split('/embed/')[1] || null
    return null
  } catch {
    if (url.includes('youtu.be/')) return url.split('youtu.be/')[1]?.split('?')[0] || null
    if (url.includes('watch?v=')) return url.split('watch?v=')[1]?.split('&')[0] || null
    return null
  }
}

const getPostImage = (post) => {
  if (!post) return '/assets/images/fama-placeholder.jpg'
  if (post.type === 'video') {
    if (post.video_thumbnail_url) return post.video_thumbnail_url
    if (post.video_platform === 'youtube' && post.video_url) {
      const id = getYoutubeId(post.video_url)
      if (id) return `https://img.youtube.com/vi/${id}/hqdefault.jpg`
    }
  }
  if (post.type === 'pdf') {
    return post.thumbnail ? `/storage/${post.thumbnail}` : '/assets/images/fama-placeholder.jpg'
  }
  if (post.media && post.media.length > 0) {
    return `/storage/${post.media[0].file_path}`
  }
  return '/assets/images/fama-placeholder.jpg'
}

const recentPdfs = computed(() => posts.value.filter((p) => p.pdf_path).slice(0, 3))

const handleCardKeyPress = (event, post) => {
  if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault()
    router.push(`/posts/${post.slug}`)
  }
}

</script>

<template>
  <div class="home-page">
    <!-- HERO SECTION -->
    <section class="hero-premium" aria-label="Bannière d'accueil">
      <div
        v-for="(img, index) in backgroundImages"
        :key="index"
        class="hero-bg-layer"
        :class="{ active: currentBgIndex === index }"
        :style="{ backgroundImage: `url(${img})` }"
        aria-hidden="true"
      ></div>

      <div class="hero-overlay" aria-hidden="true"></div>

      <div class="container hero-content">
        <div class="hero-text-box">
          <p class="hero-kicker">RÉPUBLIQUE DU MALI • FORCES ARMÉES MALIENNES</p>
          <h1>
            Défense de la Patrie
            <br />
            <span class="text-gold">Engagement Sans Faille</span>
          </h1>

          <p class="hero-subtext">
            Les Forces Armées Maliennes incarnent le devoir, la discipline et la fidélité à la Nation.
            Ce portail officiel centralise les informations institutionnelles, les communiqués,
            les documents validés et les contenus multimédias publiés au nom des FAMa.
          </p>

          <div class="hero-btns">
            <Button
              label="RECRUTEMENTS & CONCOURS"
              icon="pi pi-file"
              class="btn-fama-gold hero-action-btn"
              @click="router.push('/recrutement')"
              aria-label="Voir les recrutements et concours"
            />
            <Button
              label="DÉCOUVRIR L'INSTITUTION"
              icon="pi pi-shield"
              variant="text"
              class="hero-action-btn hero-action-outline"
              @click="router.push('/about')"
              aria-label="Découvrir l'institution"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- MAIN NEWS & MEDIA SECTION -->
    <section class="news-section" aria-label="Dernières publications">
      <div class="container">
        <div class="section-header-premium">
          <div class="header-line" aria-hidden="true"></div>
          <h2>DERNIÈRES PUBLICATIONS</h2>
          <p>Informations vérifiées de l'État-Major Général des Armées</p>
        </div>

        <!-- Error Alert -->
        <div v-if="error" class="error-message" role="alert">
          <i class="pi pi-exclamation-triangle"></i>
          <p>{{ error }}</p>
          <Button label="Réessayer" icon="pi pi-refresh" class="p-button-outlined" @click="fetchLatestPosts" />
        </div>

        <!-- Publications Grid -->
        <div class="news-grid">
          <template v-if="loading">
            <div v-for="i in 6" :key="i" class="premium-card">
              <Skeleton width="100%" height="200px"></Skeleton>
              <div class="p-4">
                <Skeleton width="40%" class="mb-2"></Skeleton>
                <Skeleton width="100%" height="1.5rem"></Skeleton>
              </div>
            </div>
          </template>

          <template v-else>
            <div
              v-for="post in posts.slice(0, 5)"
              :key="post.id"
              class="premium-card"
              @click="router.push(`/posts/${post.slug}`)"
              @keypress="handleCardKeyPress($event, post)"
              tabindex="0"
              role="article"
              :aria-label="`Article: ${post.title}`"
            >
              <div class="card-media">
                <img
                  :data-src="getPostImage(post)"
                  :alt="post.title"
                  class="card-image"
                  loading="lazy"
                  decoding="async"
                />
                <div v-if="post.type === 'video'" class="video-badge" aria-label="Contenu vidéo">
                  <i class="pi pi-play" aria-hidden="true"></i>
                </div>
              </div>

              <div class="card-content">
                <span class="date">
                  <i class="pi pi-calendar" aria-hidden="true"></i>
                  {{ formatDate(post.created_at) }}
                </span>
                <h3>{{ truncateTitle(post.title, 80) }}</h3>
                <div class="card-footer-link">
                  Consulter <i class="pi pi-arrow-right ml-2" aria-hidden="true"></i>
                </div>
              </div>
            </div>

            <!-- CTA Card Archive -->
            <div
              class="premium-card cta-card"
              @click="router.push('/portfolio')"
              @keypress="($event) => { if ($event.key === 'Enter' || $event.key === ' ') router.push('/portfolio') }"
              tabindex="0"
              role="button"
              aria-label="Accéder aux archives et communiqués"
            >
              <div class="cta-content">
                <div class="cta-icon" aria-hidden="true">
                  <i class="pi pi-folder-open"></i>
                </div>
                <h3>Archives & Communiqués</h3>
                <p>Accédez à l'intégralité des documents, rapports et publications officiels.</p>
                <Button label="TOUT CONSULTER" icon="pi pi-chevron-right" iconPos="right" class="btn-fama-gold cta-btn" />
              </div>
            </div>
          </template>
        </div>

        <!-- Médiathèque Row -->
        <div class="media-section">
          <div class="section-header-premium media-header">
            <div class="header-line" aria-hidden="true"></div>
            <h2>MÉDIATHÈQUE</h2>
            <p>Photos et vidéos officielles des activités et opérations des FAMa</p>
          </div>

          <div class="media-grid">
            <Card
              class="media-card"
              @click="router.push('/phototheque')"
              @keypress="($event) => { if ($event.key === 'Enter' || $event.key === ' ') router.push('/phototheque') }"
              tabindex="0"
              role="button"
              aria-label="Explorer la photothèque"
            >
              <template #header>
                <div class="media-cover">
                  <img :data-src="photoCover" alt="Photothèque FAMa" loading="lazy" decoding="async" />
                  <div class="media-cover-overlay" aria-hidden="true"></div>
                  <div class="media-badge" aria-hidden="true">
                    <i class="pi pi-images"></i>
                    <span>PHOTOTHÈQUE</span>
                  </div>
                </div>
              </template>
              <template #content>
                <h3 class="media-title">Photothèque Officielle</h3>
                <p class="media-desc">Cérémonies, activités, formations, opérations et images institutionnelles validées.</p>
                <div class="media-actions">
                  <Button label="Explorer" icon="pi pi-arrow-right" iconPos="right" class="btn-fama-gold media-btn" />
                </div>
              </template>
            </Card>

            <Card
              class="media-card"
              @click="router.push('/videotheque')"
              @keypress="($event) => { if ($event.key === 'Enter' || $event.key === ' ') router.push('/videotheque') }"
              tabindex="0"
              role="button"
              aria-label="Explorer la vidéothèque"
            >
              <template #header>
                <div class="media-cover">
                  <img :data-src="videoCover" alt="Vidéothèque FAMa" loading="lazy" decoding="async" />
                  <div class="media-cover-overlay" aria-hidden="true"></div>
                  <div class="media-badge" aria-hidden="true">
                    <i class="pi pi-video"></i>
                    <span>VIDÉOTHÈQUE</span>
                  </div>
                </div>
              </template>
              <template #content>
                <h3 class="media-title">Vidéothèque Officielle</h3>
                <p class="media-desc">Reportages, déclarations, communiqués vidéo et contenus officiels publiés par les FAMa.</p>
                <div class="media-actions">
                  <Button label="Regarder" icon="pi pi-play" iconPos="left" class="btn-fama-gold media-btn" />
                </div>
              </template>
            </Card>
          </div>
        </div>
      </div>
    </section>

    <!-- ACCESSIBLE & UNIFORM FOOTER -->
    <footer class="fama-footer" aria-label="Pied de page">
      <div class="container footer-grid">
        <div class="footer-brand">
          <h3 class="footer-title">FORCES ARMÉES MALIENNES</h3>
          <p class="footer-intro">
            Portail officiel d'information institutionnelle. Consultez des contenus fiables, vérifiés et publiés
            au nom des Forces Armées Maliennes.
          </p>

          <div class="soldier-honor-card">
            <i class="pi pi-shield soldier-icon" aria-hidden="true"></i>

            <div class="honor-content">
              <h3>Servir la Patrie</h3>

              <p class="honor-text">
                Défendre l'intégrité territoriale, protéger les populations et préserver la souveraineté nationale
                constituent le cœur de la mission des Forces Armées Maliennes.
              </p>

              <p class="honor-text">
                Le cœur de notre nation bat au rythme de l'engagement de ses fils et filles sous les drapeaux.
                Aujourd'hui plus que jamais, nous devons faire bloc.
              </p>

              <h4 class="honor-subtitle">A nos vaillants soldats (FAMa) qui œuvrent, chaque jour pour :</h4>
              <p class="honor-text honor-list-text">
                • Défendre l'intégrité territoriale de notre Grand Mali <br>
                • Protéger les populations avec courage et abnégation <br>
                • Préserver la souveraineté nationale, socle de notre dignité.
              </p>

              <p class="honor-text">
                Votre force réside dans votre discipline, votre sens du devoir et votre fidélité aux valeurs républicaines.
                Sachez que chaque Malien est fier de son armée, qui agit pour le service exclusif du Mali.
              </p>

              <h4 class="honor-subtitle">A la population malienne :</h4>
              <p class="honor-text">
                Avoir l'amour du Mali, c'est croire en notre résilience. La défense de la patrie n'est pas que l'affaire des armes,
                c'est aussi celle des cœurs unis.
              </p>

              <p class="honor-text honor-italic">
                Le Mali est un et indivisible. Derrière nos soldats, nous formons une forteresse inébranlable.
              </p>

              <p class="honor-text">
                Restons debout, restons fiers. Pour l'honneur de nos ancêtres et l'avenir de nos enfants.
              </p>

              <h2 class="honor-slogan">Vive les FAMa ! Vive le Mali !</h2>

              <div class="honor-footer">
                <span class="honor-rank">Valeurs</span>
                <span class="honor-separator" aria-hidden="true"></span>
                <span class="honor-motto">Honneur • Patrie • Discipline</span>
              </div>
            </div>
          </div>
        </div>

        <div class="footer-docs">
          <SidebarOfficial :recentDocs="recentPdfs" />
        </div>
      </div>

      <div class="copyright">
        © 2026 RÉPUBLIQUE DU MALI • UN PEUPLE • UN BUT • UNE FOI
      </div>
    </footer>

    <!-- Back To Top Trigger -->
    <Transition name="fade">
      <Button
        v-if="showScrollTop"
        @click="scrollToTop"
        icon="pi pi-arrow-up"
        class="scroll-top-button"
        rounded
        severity="secondary"
        aria-label="Retour en haut de page"
      />
    </Transition>
  </div>
</template>

<style scoped>
*,
*::before,
*::after {
  box-sizing: border-box;
  min-width: 0;
}

.home-page {
  background: #fdfdfd;
  width: 100%;
  overflow-x: clip;
}

.text-gold {
  color: #d8b24b;
}

.container {
  width: 100%;
  max-width: 1240px;
  margin: 0 auto;
  padding-inline: 22px;
}

@media (max-width: 576px) {
  .container {
    padding-inline: 16px;
  }
}

/* =========================
   HERO
========================= */
.hero-premium {
  position: relative;
  min-height: min(100svh, 860px);
  overflow: hidden;
  background: #1d261e;
  display: flex;
  align-items: center;
  width: 100%;
}

.hero-bg-layer {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center center;
  background-repeat: no-repeat;
  opacity: 0;
  transition: opacity 0.9s ease-in-out;
  z-index: 0;
}

.hero-bg-layer.active {
  opacity: 1;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  z-index: 1;
  background:
    linear-gradient(
      90deg,
      rgba(10, 15, 11, 0.90) 0%,
      rgba(18, 26, 20, 0.78) 35%,
      rgba(20, 30, 24, 0.52) 65%,
      rgba(20, 30, 24, 0.38) 100%
    );
}

.hero-content {
  position: relative;
  z-index: 2;
  width: 100%;
  padding-block: 96px 72px;
  display: flex;
  align-items: center;
}

.hero-text-box {
  width: min(100%, 780px);
}

.hero-kicker {
  margin: 0 0 18px;
  color: rgba(255, 255, 255, 0.80);
  font-weight: 800;
  font-size: 0.82rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.hero-text-box h1 {
  font-size: clamp(2.2rem, 5.8vw, 4.5rem);
  font-weight: 900;
  line-height: 1.02;
  text-transform: uppercase;
  color: #f8fafc;
  margin: 0;
  letter-spacing: -0.03em;
  max-width: 900px;
}

.hero-subtext {
  max-width: 760px;
  font-size: clamp(1rem, 2.2vw, 1.12rem);
  line-height: 1.8;
  color: #dbe4ee;
  margin: 24px 0 34px;
  border-left: 4px solid #d8b24b;
  padding-left: 18px;
}

.hero-btns {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
}

.hero-action-btn {
  min-height: 52px !important;
}

.btn-fama-gold {
  background: #d8b24b !important;
  color: #182118 !important;
  border: 1px solid #d8b24b !important;
  font-weight: 800 !important;
  box-shadow: none !important;
}

.btn-fama-gold:hover {
  background: #caa33a !important;
  border-color: #caa33a !important;
}

.hero-action-outline {
  color: #ffffff !important;
  border: 1px solid rgba(255, 255, 255, 0.22) !important;
  background: rgba(255, 255, 255, 0.06) !important;
  font-weight: 700 !important;
}

.hero-action-outline:hover {
  background: rgba(255, 255, 255, 0.11) !important;
}

/* =========================
   SECTIONS & NEWS
========================= */
.section-header-premium {
  margin-bottom: 34px;
}

.header-line {
  width: 70px;
  height: 4px;
  border-radius: 999px;
  background: #14532d;
  margin-bottom: 14px;
}

.section-header-premium h2 {
  font-size: clamp(1.55rem, 4vw, 2.15rem);
  font-weight: 900;
  color: #152019;
  margin: 0 0 8px;
  line-height: 1.18;
  letter-spacing: -0.02em;
}

.section-header-premium p {
  color: #64748b;
  font-size: clamp(0.96rem, 2vw, 1.02rem);
  margin: 0;
  line-height: 1.7;
}

.news-section {
  padding: 76px 0 72px;
  background: #f8fafc;
}

.news-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 22px;
}

.premium-card {
  background: #ffffff;
  border-radius: 14px;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
  transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
  cursor: pointer;
  border: 1px solid #e9eef4;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  min-height: 100%;
}

.premium-card:hover {
  transform: translateY(-4px);
  border-color: rgba(216, 178, 75, 0.75);
  box-shadow: 0 18px 34px rgba(15, 23, 42, 0.09);
}

.card-media {
  position: relative;
  height: 220px;
  overflow: hidden;
  background: #e2e8f0;
}

.card-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.video-badge {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(180deg, rgba(5, 12, 8, 0.02) 0%, rgba(5, 12, 8, 0.18) 100%);
}

.video-badge i {
  width: 58px;
  height: 58px;
  border-radius: 999px;
  background: rgba(15, 23, 20, 0.78);
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.38);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
  padding-left: 3px;
  box-shadow: 0 18px 38px rgba(0, 0, 0, 0.28);
  backdrop-filter: blur(10px);
  transition: transform 0.2s ease, background 0.2s ease;
}

.premium-card:hover .video-badge i {
  transform: scale(1.08);
  background: rgba(12, 18, 14, 0.9);
}

.card-content {
  padding: 18px 18px 20px;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.date {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-size: 0.83rem;
  font-weight: 700;
  color: #14532d;
}

.card-content h3 {
  font-size: 1.1rem;
  font-weight: 800;
  color: #152019;
  margin: 12px 0 10px;
  line-height: 1.45;
  min-height: 3.2rem;
}

.card-footer-link {
  font-weight: 800;
  color: #152019;
  font-size: 0.92rem;
  display: flex;
  align-items: center;
  margin-top: auto;
}

/* CTA Card */
.cta-card {
  background: #152019 !important;
  border: 1px solid rgba(216, 178, 75, 0.45) !important;
  justify-content: center;
  align-items: center;
  text-align: center;
}

.cta-content {
  padding: 30px 22px;
  width: 100%;
}

.cta-content h3 {
  font-size: 1.28rem;
  font-weight: 900;
  color: #ffffff;
  margin: 0 0 10px;
}

.cta-content p {
  color: #d3d9e2;
  line-height: 1.72;
  margin: 0 0 20px;
}

.cta-icon {
  width: 70px;
  height: 70px;
  background: rgba(216, 178, 75, 0.10);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 18px;
}

.cta-icon i {
  font-size: 1.85rem;
  color: #d8b24b;
}

/* =========================
   MEDIA
========================= */
.media-section {
  margin-top: 68px;
}

.media-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
}

.media-card {
  cursor: pointer;
  overflow: hidden;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
}

.media-cover {
  position: relative;
  height: 220px;
}

.media-cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.media-cover-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.42), rgba(0, 0, 0, 0.08));
}

.media-badge {
  position: absolute;
  left: 14px;
  bottom: 14px;
  display: inline-flex;
  gap: 8px;
  align-items: center;
  background: rgba(12, 18, 14, 0.78);
  color: #ffffff;
  padding: 8px 12px;
  border-radius: 999px;
  font-weight: 800;
  font-size: 0.74rem;
  border: 1px solid rgba(255, 255, 255, 0.26);
}

.media-title {
  font-size: 1.16rem;
  font-weight: 900;
  color: #152019;
  margin: 0 0 10px;
}

.media-desc {
  color: #64748b;
  font-size: 0.96rem;
  line-height: 1.7;
  margin: 0 0 18px;
}

/* =========================
   OPTIMIZED FOOTER DIGNIFIÉ
========================= */
.fama-footer {
  background: #152019;
  color: white;
  padding-top: 74px;
}

.footer-grid {
  display: flex;
  flex-direction: column;
  gap: 36px;
  padding-bottom: 55px;
}

.footer-brand {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.footer-title {
  color: #d8b24b;
  font-weight: 900;
  margin: 0 0 18px;
  font-size: clamp(1.35rem, 3vw, 1.8rem);
}

.footer-intro {
  color: #d3d9e2;
  line-height: 1.78;
  margin: 0;
  font-size: 1rem;
  max-width: 760px;
}

/* Alignements de la boite d'honneur */
.soldier-honor-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.04) 0%, rgba(255, 255, 255, 0.015) 100%);
  border: 1px solid rgba(216, 178, 75, 0.24);
  border-left: 4px solid #d8b24b;
  padding: 22px;
  border-radius: 14px;
  margin-top: 28px;
  display: flex;
  flex-direction: column;
  gap: 15px;
  align-items: flex-start;
  flex-grow: 1; /* S'étire pour remplir toute la hauteur */
}

.soldier-icon {
  font-size: 2.15rem;
  color: #d8b24b;
  opacity: 0.95;
  flex-shrink: 0;
}

.honor-content {
  width: 100%;
}

.honor-content h3 {
  margin: 0 0 25px;
  color: #ffffff;
  font-size: 1.8rem;
  text-transform: uppercase;
  letter-spacing: 2px;
  border-bottom: 1px solid rgba(216, 178, 75, 0.2);
  padding-bottom: 10px;
  display: inline-block;
}

/* Réglage d'aération des écritures demandé */
.honor-content .honor-text {
  font-size: 1rem !important;
  line-height: 1.75 !important; /* Enlève le côté entassé */
  letter-spacing: 0.02em;       /* Lisibilité accrue */
  color: #e2e8f0;
  margin-bottom: 1.25rem;
  text-align: justify;
}

.honor-subtitle {
  font-size: 1.12rem;
  font-weight: 700;
  color: #ffffff;
  margin: 1.5rem 0 0.5rem 0;
}

.honor-list-text {
  line-height: 1.85 !important;
  padding-left: 0.5rem;
}

.honor-italic {
  font-style: italic;
  background: rgba(216, 178, 75, 0.04);
  border-left: 3px solid #d8b24b;
  padding: 12px 16px !important;
  margin: 1.5rem 0;
}

.honor-slogan {
  font-size: 1.55rem;
  font-weight: 700;
  text-align: center;
  color: #ffffff;
  margin: 2rem 0;
  letter-spacing: 0.05em;
}

.honor-footer {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 9px;
}

.honor-rank {
  color: #d8b24b;
  font-weight: 800;
  text-transform: uppercase;
  font-size: 0.76rem;
}

.honor-separator {
  width: 32px;
  height: 1px;
  background: rgba(216, 178, 75, 0.45);
}

.honor-motto {
  color: #aeb8c5;
  font-weight: 700;
  text-transform: uppercase;
  font-size: 0.76rem;
}

.copyright {
  background: #1b281d;
  text-align: center;
  padding: 18px 14px;
  font-size: 0.78rem;
  color: #aeb8c5;
  width: 100%;
}

/* Bouton Scroll Top */
.scroll-top-button {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  z-index: 100;
  background: #d8b24b !important;
  border: none !important;
  color: #152019 !important;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.error-message {
  background: #fee2e2;
  border-left: 4px solid #ef4444;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 2rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

/* Responsive Tablet & Desktop */
@media (min-width: 768px) {
  .media-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .soldier-honor-card {
    padding: 30px;
    flex-direction: row;
    gap: 22px;
  }
}

@media (min-width: 1024px) {
  .footer-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 390px;
    gap: 54px;
    align-items: stretch; /* Aligne le bloc de texte et la sidebar à la même hauteur exacte */
  }
}

@media (max-width: 992px) {
  .news-grid {
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 260px), 1fr));
  }
}

@media (max-width: 768px) {
  .hero-premium {
    min-height: 660px;
  }
  .hero-text-box h1 {
    font-size: 2.55rem;
  }
  .hero-btns {
    flex-direction: column;
  }
  .news-section {
    padding: 64px 0; /* Correction de la coupure de syntaxe d'origine */
  }
}
</style>
