<script setup>
import { ref, onMounted, computed, onUnmounted, nextTick } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useHead } from '@unhead/vue'
import Skeleton from 'primevue/skeleton'
import Tag from 'primevue/tag'
import Button from 'primevue/button'
import Card from 'primevue/card'
import SidebarOfficial from '@/components/SidebarOfficial.vue'

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
    {
      name: 'description',
      content:
        "Portail officiel des FAMa. Retrouvez les communiqués de l'État-Major, l'actualité de la défense et les rapports officiels sur la sécurité du Mali.",
    },
    {
      name: 'keywords',
      content: 'FAMa, Armée Malienne, Défense Mali, Sécurité Mali, Communiqués officiels',
    },
    { property: 'og:type', content: 'website' },
    { property: 'og:url', content: 'https://votre-site-fama.ml/' },
    { property: 'og:title', content: 'FAMa - Engagement Sans Faille pour la Patrie' },
    {
      property: 'og:description',
      content: "Information vérifiée de l'État-Major Général des Armées du Mali.",
    },
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

const truncateTitle = (title, limit = 80) => {
  if (!title) return ''
  return title.length > limit ? `${title.substring(0, limit).trim()}...` : title
}

const currentBgIndex = ref(0)
const backgroundImages = [heroImg, famaImg, maliImg, heroImg1, famaImg1, maliImg1,heroImg2, famaImg2, maliImg2, heroImg12, famaImg12, maliImg12]
let backgroundInterval = null
let observer = null

const posts = ref([])
const loading = ref(true)
const error = ref(null)
const showScrollTop = ref(false)

const handleScroll = () => {
  showScrollTop.value = window.scrollY > 500
}

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const preloadImages = () => {
  backgroundImages.forEach((src) => {
    const img = new Image()
    img.src = src
    img.fetchPriority = 'high'
  })

  ;[photoCover, videoCover].forEach((src) => {
    const img = new Image()
    img.src = src
  })
}

const setupLazyLoading = () => {
  const lazyImages = document.querySelectorAll('img[data-src]')

  if (!lazyImages.length) return

  if ('IntersectionObserver' in window) {
    observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return
          const img = entry.target
          if (img.dataset.src) {
            img.src = img.dataset.src
            img.removeAttribute('data-src')
          }
          observer.unobserve(img)
        })
      },
      { rootMargin: '100px 0px' }
    )

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

const fetchLatestPosts = async () => {
  try {
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
  if (post?.type === 'video') {
    if (post?.video_thumbnail_url) return post.video_thumbnail_url

    if (post?.video_platform === 'youtube' && post?.video_url) {
      const id = getYoutubeId(post.video_url)
      if (id) return `https://img.youtube.com/vi/${id}/hqdefault.jpg`
    }

    return '/assets/images/fama-placeholder.jpg'
  }

  if (post?.type === 'pdf') {
    if (post?.thumbnail) return `/storage/${post.thumbnail}`
    return '/assets/images/fama-placeholder.jpg'
  }

  if (post?.media && post.media.length > 0) {
    return `/storage/${post.media[0].file_path}`
  }

  return '/assets/images/fama-placeholder.jpg'
}

const stripHtml = (html) => {
  if (!html) return ''
  const doc = new DOMParser().parseFromString(html, 'text/html')
  return doc.body.textContent || ''
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

    <section class="news-section" aria-label="Dernières publications">
      <div class="container">
        <div class="section-header-premium">
          <div class="header-line" aria-hidden="true"></div>
          <h2>DERNIÈRES PUBLICATIONS</h2>
          <p>Informations vérifiées de l'État-Major Général des Armées</p>
        </div>

        <div v-if="error" class="error-message" role="alert">
          <i class="pi pi-exclamation-triangle"></i>
          <p>{{ error }}</p>
          <Button
            label="Réessayer"
            icon="pi pi-refresh"
            class="p-button-outlined"
            @click="fetchLatestPosts"
          />
        </div>

        <div class="news-grid">
          <div v-if="loading" v-for="i in 6" :key="i" class="premium-card">
            <Skeleton width="100%" height="200px"></Skeleton>
            <div class="p-4">
              <Skeleton width="40%" class="mb-2"></Skeleton>
              <Skeleton width="100%" height="1.5rem"></Skeleton>
            </div>
          </div>

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
                  {{ new Date(post.created_at).toLocaleDateString('fr-FR') }}
                </span>

                <h3>{{ truncateTitle(post.title, 80) }}</h3>

                <div class="card-footer-link">
                  Consulter <i class="pi pi-arrow-right ml-2" aria-hidden="true"></i>
                </div>
              </div>
            </div>

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
                <Button
                  label="TOUT CONSULTER"
                  icon="pi pi-chevron-right"
                  iconPos="right"
                  class="btn-fama-gold cta-btn"
                />
              </div>
            </div>
          </template>
        </div>

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
                  <img
                    :data-src="photoCover"
                    alt="Photothèque FAMa"
                    loading="lazy"
                    decoding="async"
                  />
                  <div class="media-cover-overlay" aria-hidden="true"></div>
                  <div class="media-badge" aria-hidden="true">
                    <i class="pi pi-images"></i>
                    <span>PHOTOTHÈQUE</span>
                  </div>
                </div>
              </template>

              <template #content>
                <h3 class="media-title">Photothèque Officielle</h3>
                <p class="media-desc">
                  Cérémonies, activités, formations, opérations et images institutionnelles validées.
                </p>
                <div class="media-actions">
                  <Button
                    label="Explorer"
                    icon="pi pi-arrow-right"
                    iconPos="right"
                    class="btn-fama-gold media-btn"
                  />
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
                  <img
                    :data-src="videoCover"
                    alt="Vidéothèque FAMa"
                    loading="lazy"
                    decoding="async"
                  />
                  <div class="media-cover-overlay" aria-hidden="true"></div>
                  <div class="media-badge" aria-hidden="true">
                    <i class="pi pi-video"></i>
                    <span>VIDÉOTHÈQUE</span>
                  </div>
                </div>
              </template>

              <template #content>
                <h3 class="media-title">Vidéothèque Officielle</h3>
                <p class="media-desc">
                  Reportages, déclarations, communiqués vidéo et contenus officiels publiés par les FAMa.
                </p>
                <div class="media-actions">
                  <Button
                    label="Regarder"
                    icon="pi pi-play"
                    iconPos="left"
                    class="btn-fama-gold media-btn"
                  />
                </div>
              </template>
            </Card>
          </div>
        </div>
      </div>
    </section>

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
                Discipline, engagement, sens du devoir et fidélité aux valeurs républicaines fondent l'action
                quotidienne des FAMa au service exclusif du Mali.
              </p>

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

.hero-tag {
  margin-bottom: 18px;
}

.hero-kicker {
  margin: 0 0 18px;
  color: rgba(255, 255, 255, 0.80);
  font-weight: 800;
  font-size: 0.82rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.custom-tag-official {
  background: #14532d !important;
  color: #ffffff !important;
  font-weight: 800 !important;
  border-radius: 999px !important;
  padding: 0.5rem 0.95rem !important;
  font-size: 0.82rem !important;
  letter-spacing: 0.04em;
  border: 1px solid rgba(255, 255, 255, 0.12) !important;
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
   SECTIONS
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

/* =========================
   NEWS
========================= */
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
  background:
    linear-gradient(
      180deg,
      rgba(5, 12, 8, 0.02) 0%,
      rgba(5, 12, 8, 0.18) 100%
    );
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
  box-shadow:
    0 18px 38px rgba(0, 0, 0, 0.28),
    inset 0 0 0 1px rgba(216, 178, 75, 0.22);
  backdrop-filter: blur(10px);
  transition: transform 0.2s ease, background 0.2s ease, border-color 0.2s ease;
}

.premium-card:hover .video-badge i {
  transform: scale(1.08);
  background: rgba(12, 18, 14, 0.9);
  border-color: rgba(216, 178, 75, 0.72);
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

.card-content p {
  color: #64748b;
  font-size: 0.95rem;
  line-height: 1.72;
  margin: 0 0 16px;
  flex-grow: 1;
}

.card-footer-link {
  font-weight: 800;
  color: #152019;
  font-size: 0.92rem;
  display: flex;
  align-items: center;
  margin-top: auto;
}

/* CTA */
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

.cta-btn {
  min-height: 48px !important;
}

/* =========================
   MEDIA
========================= */
.media-section {
  margin-top: 68px;
}

.media-header {
  margin-bottom: 24px;
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
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
  transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}

.media-card:hover {
  transform: translateY(-4px);
  border-color: rgba(216, 178, 75, 0.75);
  box-shadow: 0 16px 30px rgba(15, 23, 42, 0.08);
}

.media-cover {
  position: relative;
  height: 220px;
  overflow: hidden;
}

.media-cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
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
  letter-spacing: 0.06em;
  text-transform: uppercase;
  max-width: calc(100% - 28px);
  border: 1px solid rgba(255, 255, 255, 0.26);
  backdrop-filter: blur(10px);
  box-shadow: 0 12px 26px rgba(0, 0, 0, 0.22);
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

.media-actions {
  display: flex;
}

.media-btn {
  min-height: 46px !important;
}

/* =========================
   FOOTER
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

.soldier-honor-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.04) 0%, rgba(255, 255, 255, 0.015) 100%);
  border: 1px solid rgba(216, 178, 75, 0.24);
  border-left: 4px solid #d8b24b;
  padding: 22px;
  border-radius: 14px;
  margin-top: 28px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  gap: 15px;
  align-items: flex-start;
  transition: transform 0.2s ease, border-color 0.2s ease;
}

.soldier-honor-card:hover {
  border-color: rgba(216, 178, 75, 0.52);
  transform: translateY(-2px);
}

.soldier-icon {
  font-size: 2.15rem;
  color: #d8b24b;
  opacity: 0.95;
  flex-shrink: 0;
}

.honor-content h3 {
  margin: 0 0 12px;
  color: #ffffff;
  font-size: 1.2rem;
  font-weight: 900;
}

.honor-text {
  font-size: 0.98rem;
  line-height: 1.84;
  color: #e2e8f0;
  font-weight: 400;
  margin: 0 0 14px;
}

.honor-footer {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 10px;
}

.honor-rank {
  color: #d8b24b;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1.2px;
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

.footer-docs {
  min-width: 0;
}

.copyright {
  background: #1b281d;
  text-align: center;
  padding: 18px 14px;
  font-size: 0.78rem;
  color: #aeb8c5;
  width: 100%;
  line-height: 1.6;
}

/* Bouton retour haut */
.scroll-top-button {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  z-index: 100;
  opacity: 0.92;
  transition: all 0.3s ease;
  background: #d8b24b !important;
  border: none !important;
  color: #152019 !important;
}

.scroll-top-button:hover {
  opacity: 1;
  transform: translateY(-4px);
  background: #caa33a !important;
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
  flex-wrap: wrap;
}

.error-message i {
  color: #ef4444;
  font-size: 1.25rem;
}

.error-message p {
  margin: 0;
  color: #991b1b;
  flex: 1;
}

/* Accessibilité */
@media (prefers-reduced-motion: reduce) {
  .hero-bg-layer,
  .premium-card,
  .media-card,
  .scroll-top-button {
    transition: none !important;
    transform: none !important;
  }
}

/* =========================
   TABLETTE / DESKTOP
========================= */
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
    align-items: start;
  }
}

@media (max-width: 992px) {
  .news-grid {
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 260px), 1fr));
  }

  .hero-premium {
    min-height: 720px;
  }

  .hero-content {
    padding-block: 84px 64px;
  }
}

/* =========================
   MOBILE
========================= */
@media (max-width: 768px) {
  .container {
    padding-inline: 18px;
  }

  .hero-premium {
    min-height: 660px;
    align-items: center;
  }

  .hero-overlay {
    background:
      linear-gradient(
        to right,
        rgba(10, 15, 11, 0.92) 0%,
        rgba(16, 24, 18, 0.82) 45%,
        rgba(18, 28, 22, 0.56) 78%,
        rgba(18, 28, 22, 0.40) 100%
      );
  }

  .hero-content {
    padding-block: 68px 44px;
  }

  .hero-text-box {
    width: 100%;
    max-width: 100%;
  }

  .custom-tag-official {
    font-size: 0.88rem !important;
    padding: 0.58rem 0.95rem !important;
  }

  .hero-kicker {
    font-size: 0.74rem;
    margin-bottom: 16px;
  }

  .hero-text-box h1 {
    font-size: 2.55rem;
    line-height: 1.02;
    letter-spacing: -0.03em;
  }

  .hero-subtext {
    max-width: 100%;
    font-size: 1rem;
    line-height: 1.74;
    margin: 22px 0 28px;
    padding-left: 16px;
  }

  .hero-btns {
    flex-direction: column;
    align-items: stretch;
    width: 100%;
    gap: 12px;
  }

  .hero-action-btn {
    width: 100%;
    min-height: 56px !important;
    font-size: 1rem !important;
  }

  .news-section {
    padding: 64px 0;
  }

  .section-header-premium {
    margin-bottom: 28px;
  }

  .header-line {
    width: 72px;
    height: 5px;
    margin-bottom: 16px;
  }

  .section-header-premium h2 {
    font-size: 1.95rem;
    line-height: 1.12;
    margin-bottom: 10px;
  }

  .section-header-premium p {
    font-size: 1.01rem;
    line-height: 1.7;
  }

  .news-grid {
    grid-template-columns: 1fr;
    gap: 22px;
  }

  .premium-card,
  .media-card {
    border-radius: 16px;
  }

  .card-media {
    height: 250px;
  }

  .card-content {
    padding: 20px 20px 22px;
  }

  .date {
    font-size: 0.9rem;
    gap: 8px;
  }

  .card-content h3 {
    font-size: 1.24rem;
    line-height: 1.45;
    margin: 14px 0 12px;
    min-height: auto;
  }

  .card-content p {
    font-size: 1rem;
    line-height: 1.72;
    margin-bottom: 18px;
  }

  .card-footer-link {
    font-size: 1rem;
  }

  .video-badge i {
  width: 56px;
  height: 56px;
  font-size: 1.3rem;
}

  .cta-content {
    padding: 28px 20px;
  }

  .cta-content h3 {
    font-size: 1.38rem;
  }

  .cta-content p {
    font-size: 1rem;
  }

  .cta-btn {
    width: 100%;
    min-height: 54px !important;
    font-size: 1rem !important;
  }

  .media-section {
    margin-top: 64px;
  }

  .media-header {
    margin-bottom: 26px;
  }

  .media-grid {
    gap: 22px;
  }

  .media-cover {
    height: 235px;
  }

  .media-badge {
    font-size: 0.84rem;
    padding: 9px 14px;
    gap: 9px;
  }

  .media-title {
    font-size: 1.28rem;
    margin-bottom: 12px;
  }

  .media-desc {
    font-size: 1rem;
    line-height: 1.72;
    margin-bottom: 18px;
  }

  .media-btn {
    width: 100%;
    min-height: 52px !important;
    font-size: 1rem !important;
  }

  .fama-footer {
    padding-top: 64px;
  }

  .footer-grid {
    gap: 32px;
    padding-bottom: 48px;
  }

  .footer-title {
    font-size: 1.68rem;
    margin-bottom: 16px;
  }

  .footer-intro {
    font-size: 1.02rem;
    line-height: 1.8;
  }

  .soldier-honor-card {
    padding: 22px 18px;
    gap: 16px;
  }

  .soldier-icon {
    font-size: 2.1rem;
  }

  .honor-content h3 {
    font-size: 1.28rem;
    margin-bottom: 14px;
  }

  .honor-text {
    font-size: 1rem;
    line-height: 1.88;
  }

  .honor-rank,
  .honor-motto {
    font-size: 0.82rem;
  }

  .copyright {
    font-size: 0.84rem;
    padding: 18px 16px;
  }

  .scroll-top-button {
    right: 1rem;
    bottom: 1rem;
  }
}

/* =========================
   PETITS TÉLÉPHONES
========================= */
@media (max-width: 576px) {
  .container {
    padding-inline: 16px;
  }

  .hero-premium {
    min-height: 600px;
  }

  .hero-content {
    padding-block: 58px 34px;
  }

  .custom-tag-official {
    font-size: 0.82rem !important;
    padding: 0.52rem 0.88rem !important;
  }

  .hero-kicker {
    font-size: 0.7rem;
    letter-spacing: 0.12em;
  }

  .hero-text-box h1 {
    font-size: 2.05rem;
  }

  .hero-subtext {
    font-size: 0.97rem;
    line-height: 1.68;
    margin: 18px 0 24px;
    padding-left: 14px;
  }

  .hero-action-btn {
    min-height: 52px !important;
    font-size: 0.95rem !important;
  }

  .section-header-premium h2 {
    font-size: 1.68rem;
  }

  .section-header-premium p {
    font-size: 0.98rem;
  }

  .news-grid {
    gap: 18px;
  }

  .premium-card {
    border-radius: 14px;
  }

  .card-media {
    height: 215px;
  }

  .card-content {
    padding: 18px 16px 20px;
  }

  .date {
    font-size: 0.84rem;
  }

  .card-content h3 {
    font-size: 1.12rem;
  }

  .card-content p {
    font-size: 0.95rem;
  }

  .card-footer-link {
    font-size: 0.94rem;
  }

  .cta-content {
    padding: 24px 16px;
  }

  .cta-content h3 {
    font-size: 1.22rem;
  }

  .cta-content p {
    font-size: 0.95rem;
  }

  .cta-btn,
  .media-btn {
    width: 100%;
  }

  .media-cover {
    height: 205px;
  }

  .media-badge {
    font-size: 0.75rem;
    padding: 8px 12px;
  }

  .media-title {
    font-size: 1.12rem;
  }

  .media-desc {
    font-size: 0.95rem;
  }

  .fama-footer {
    padding-top: 56px;
  }

  .footer-title {
    font-size: 1.42rem;
  }

  .footer-intro {
    font-size: 0.95rem;
  }

  .soldier-honor-card {
    padding: 18px 16px;
  }

  .soldier-icon {
    font-size: 1.95rem;
  }

  .honor-content h3 {
    font-size: 1.14rem;
  }

  .honor-text {
    font-size: 0.94rem;
    line-height: 1.8;
  }

  .honor-footer {
    gap: 10px;
  }

  .honor-separator {
    width: 24px;
  }

  .copyright {
    font-size: 0.78rem;
  }
}

@media (max-width: 380px) {
  .container {
    padding-inline: 14px;
  }

  .hero-premium {
    min-height: 560px;
  }

  .hero-text-box h1 {
    font-size: 1.88rem;
  }

  .hero-subtext {
    font-size: 0.9rem;
  }

  .custom-tag-official {
    font-size: 0.76rem !important;
    padding: 0.44rem 0.74rem !important;
  }

  .section-header-premium h2 {
    font-size: 1.46rem;
  }

  .card-media {
    height: 190px;
  }

  .card-content h3 {
    font-size: 1.04rem;
  }

  .card-content p {
    font-size: 0.9rem;
  }

  .media-cover {
    height: 185px;
  }

  .cta-content h3,
  .media-title {
    font-size: 1.04rem;
  }

  .footer-title {
    font-size: 1.24rem;
  }
}
</style>