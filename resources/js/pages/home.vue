<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useHead } from '@vueuse/head'
import Skeleton from 'primevue/skeleton'
import Tag from 'primevue/tag'
import Button from 'primevue/button'
import Card from 'primevue/card'
import SidebarOfficial from '@/components/SidebarOfficial.vue'

import heroImg from '@/assets/images/hero.jpg'
import famaImg from '@/assets/images/fam.png'
import maliImg from '@/assets/images/fa.jpg'

import photoCover from '@/assets/images/hero.jpg'
import videoCover from '@/assets/images/fam.png'

useHead({
  title: 'Accueil | FAMa - Portail Officiel des Forces Armées Maliennes',
  meta: [
    {
      name: 'description',
      content:
        "Portail officiel des FAMa. Retrouvez les communiqués de l'État-Major, l'actualité de la défense et les rapports officiels sur la sécurité du Mali.",
    },
    { name: 'keywords', content: 'FAMa, Armée Malienne, Défense Mali, Sécurité Mali, Communiqués officiels' },
    { property: 'og:type', content: 'website' },
    { property: 'og:url', content: 'https://votre-site-fama.ml/' },
    { property: 'og:title', content: 'FAMa - Engagement Sans Faille pour la Patrie' },
    { property: 'og:description', content: "Information vérifiée de l’État-Major Général des Armées du Mali." },
    { property: 'og:image', content: '/assets/images/hero.jpg' },
    { name: 'twitter:card', content: 'summary_large_image' },
    { name: 'twitter:title', content: 'FAMa Officiel | Forces Armées Maliennes' },
    { name: 'twitter:image', content: '/assets/images/hero.jpg' },
  ],
})

const currentBgIndex = ref(0)
const backgroundImages = [heroImg, famaImg, maliImg]
let backgroundInterval = null

const posts = ref([])
const loading = ref(true)
const router = useRouter()

onMounted(async () => {
  backgroundImages.forEach((src) => {
    const img = new Image()
    img.src = src
  })

  backgroundInterval = setInterval(() => {
    currentBgIndex.value = (currentBgIndex.value + 1) % backgroundImages.length
  }, 5000)

  try {
    const res = await axios.get('/api/posts/latest')
    posts.value = Array.isArray(res.data) ? res.data : (res.data?.data ?? [])
  } catch (e) {
    console.error('Erreur chargement posts:', e)
  } finally {
    loading.value = false
  }
})

onUnmounted(() => {
  if (backgroundInterval) clearInterval(backgroundInterval)
})

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

  if (post?.media && post.media.length > 0) return `/storage/${post.media[0].file_path}`

  return '/assets/images/fama-placeholder.jpg'
}

const getYoutubeId = (url) => {
  if (!url) return null

  try {
    const u = new URL(url)
    if (u.hostname.includes('youtu.be')) return u.pathname.replace('/', '') || null
    if (u.searchParams.get('v')) return u.searchParams.get('v')
    if (u.pathname.includes('/embed/')) return u.pathname.split('/embed/')[1] || null
    return null
  } catch (e) {
    if (url.includes('youtu.be/')) return url.split('youtu.be/')[1]?.split('?')[0] || null
    if (url.includes('watch?v=')) return url.split('watch?v=')[1]?.split('&')[0] || null
    return null
  }
}

const recentPdfs = computed(() => posts.value.filter((p) => p.pdf_path).slice(0, 3))

const stripHtml = (html) => {
  if (!html) return ''
  const doc = new DOMParser().parseFromString(html, 'text/html')
  return doc.body.textContent || ''
}
</script>

<template>
  <main class="home-page">
    <section class="hero-premium">
      <div
        v-for="(img, index) in backgroundImages"
        :key="index"
        class="hero-bg-layer"
        :class="{ active: currentBgIndex === index }"
        :style="{ backgroundImage: `url(${img})` }"
      ></div>

      <div class="hero-overlay"></div>

      <div class="container hero-content">
        <div class="hero-text-box" data-aos="fade-up">
          <Tag value="PORTAIL OFFICIEL" class="custom-tag-official hero-tag" />

          <h1>
            Défense de la Patrie <br />
            <span class="text-gold">Engagement Sans Faille</span>
          </h1>

          <p class="hero-subtext">
            Les Forces Armées Maliennes sont l’expression vivante du devoir, du courage et de la fidélité à la Patrie.
            Servir dans les FAMA, c’est protéger le Mali, défendre son peuple et garantir sa souveraineté.
          </p>

          <div class="hero-btns">
            <Button
              label="COMMUNIQUÉS"
              icon="pi pi-file"
              class="btn-fama-gold hero-action-btn"
              @click="router.push('/portfolio')"
            />
            <Button
              label="NOTRE HISTOIRE"
              icon="pi pi-shield"
              variant="text"
              class="hero-action-btn hero-action-outline"
              @click="router.push('/about')"
            />
          </div>
        </div>
      </div>
    </section>

    <section class="news-section">
      <div class="container">
        <div class="section-header-premium">
          <div class="header-line"></div>
          <h2>DERNIÈRES PUBLICATIONS</h2>
          <p>Informations vérifiées de l'État-Major Général des Armées</p>
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
            >
              <div class="card-media">
                <img :src="getPostImage(post)" :alt="post.title" class="zoom-effect" />
                <div v-if="post.type === 'video'" class="video-badge">
                  <i class="pi pi-play"></i>
                </div>
              </div>

              <div class="card-content">
                <span class="date">
                  <i class="pi pi-calendar"></i>
                  {{ new Date(post.created_at).toLocaleDateString() }}
                </span>

                <h3>{{ post.title }}</h3>
                <p>{{ stripHtml(post.content).substring(0, 85) }}...</p>

                <div class="card-footer-link">
                  Consulter <i class="pi pi-arrow-right ml-2"></i>
                </div>
              </div>
            </div>

            <div class="premium-card cta-card" @click="router.push('/portfolio')">
              <div class="cta-content">
                <div class="cta-icon">
                  <i class="pi pi-folder-open"></i>
                </div>
                <h3>Archives & Communiqués</h3>
                <p>Accédez à l'intégralité des documents et rapports officiels.</p>
                <Button
                  label="TOUT VOIR"
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
            <div class="header-line"></div>
            <h2>MÉDIATHÈQUE</h2>
            <p>Photos et vidéos officielles des activités et opérations des FAMa</p>
          </div>

          <div class="media-grid">
            <Card class="media-card" @click="router.push('/phototheque')">
              <template #header>
                <div class="media-cover">
                  <img :src="photoCover" alt="Photothèque FAMa" />
                  <div class="media-cover-overlay"></div>
                  <div class="media-badge">
                    <i class="pi pi-images"></i>
                    <span>PHOTOTHÈQUE</span>
                  </div>
                </div>
              </template>

              <template #content>
                <h3 class="media-title">Photothèque Officielle</h3>
                <p class="media-desc">
                  Accédez aux images officielles : cérémonies, opérations, formations, actions civilo-militaires.
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

            <Card class="media-card" @click="router.push('/videotheque')">
              <template #header>
                <div class="media-cover">
                  <img :src="videoCover" alt="Vidéothèque FAMa" />
                  <div class="media-cover-overlay"></div>
                  <div class="media-badge">
                    <i class="pi pi-video"></i>
                    <span>VIDÉOTHÈQUE</span>
                  </div>
                </div>
              </template>

              <template #content>
                <h3 class="media-title">Vidéothèque Officielle</h3>
                <p class="media-desc">
                  Reportages, communiqués vidéo, interviews, et contenus officiels validés par l'État-Major.
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

    <footer class="fama-footer">
      <div class="container footer-grid">
        <div class="footer-brand">
          <h3 class="footer-title">FORCES ARMÉES MALIENNES</h3>
          <p class="footer-intro">
            La défense de la patrie est un devoir sacré. Restez connectés aux sources officielles pour des informations vérifiées.
          </p>

          <div class="soldier-honor-card">
            <div class="card-glow"></div>
            <i class="pi pi-shield soldier-icon"></i>

            <div class="honor-content">
              <h3>Etre FAMa!!!</h3>

              <p class="honor-text">
                « C’est répondre à l’appel de la Patrie avec loyauté et sens du devoir. c’est accepter la noble mission de défendre
                l’intégrité territoriale, la souveraineté nationale et la sécurité des populations, dans le respect des valeurs
                républicaines et de l’éthique militaire.»
              </p>

              <p class="honor-text">
                « Etre un pilier de la stabilité nationale. Agir avec discipline, courage et professionnalisme face aux menaces qui
                pèsent sur la Nation. Chaque mission, qu’elle soit de défense, de sécurisation ou d’assistance aux populations, est
                accomplie avec détermination et abnégation, au service exclusif du Mali. »
              </p>

              <div class="honor-footer">
                <span class="honor-rank">Valeurs Fondamentales</span>
                <span class="honor-separator"></span>
                <span class="honor-motto">Honneur - Patrie</span>
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
  </main>
</template>

<style scoped>
* {
  box-sizing: border-box;
}

.home-page {
  background: #fdfdfd;
}

.text-gold {
  color: #ffd700;
}

.container {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding-inline: 20px;
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
  min-height: clamp(560px, 88vh, 760px);
  width: 100%;
  overflow: hidden;
  background: #1d261e;
  display: flex;
  align-items: center;
}

.hero-bg-layer {
  position: absolute;
  inset: 0;
  background-size: cover !important;
  background-position: center !important;
  background-repeat: no-repeat !important;
  opacity: 0;
  transition: opacity 1.5s ease-in-out;
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
      to right,
      rgba(26, 36, 27, 0.92) 0%,
      rgba(32, 45, 34, 0.8) 38%,
      rgba(26, 36, 27, 0.45) 70%,
      rgba(26, 36, 27, 0.28) 100%
    );
}

.hero-content {
  position: relative;
  z-index: 10;
  width: 100%;
  padding-block: 80px 50px;
}

.hero-text-box {
  width: min(100%, 780px);
}

.hero-tag {
  margin-bottom: 18px;
}

.custom-tag-official {
  background: #14b82c !important;
  color: white !important;
  font-weight: 800 !important;
  border-radius: 999px !important;
  padding: 0.5rem 0.95rem !important;
  font-size: 0.82rem !important;
  letter-spacing: 0.04em;
}

.hero-text-box h1 {
  font-size: clamp(2rem, 6vw, 3.8rem);
  font-weight: 900;
  line-height: 1.08;
  text-transform: uppercase;
  color: #e2e8f0;
  margin: 0;
  letter-spacing: -0.02em;
}

.hero-subtext {
  max-width: 700px;
  font-size: clamp(1rem, 2.5vw, 1.18rem);
  line-height: 1.75;
  color: #dbe4ee;
  margin: 22px 0 34px;
  border-left: 4px solid #ffd700;
  padding-left: 18px;
}

.hero-btns {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
}

.hero-action-btn {
  min-height: 50px !important;
}

.btn-fama-gold {
  background: #ffd700 !important;
  color: #1a241b !important;
  border: none !important;
  font-weight: 800 !important;
}

.hero-action-outline {
  color: #ffffff !important;
  border: 1px solid rgba(255, 255, 255, 0.28) !important;
  background: rgba(255, 255, 255, 0.08) !important;
  font-weight: 700 !important;
}

/* =========================
   SECTIONS
========================= */
.section-header-premium {
  margin-bottom: 34px;
}

.header-line {
  width: 72px;
  height: 6px;
  border-radius: 999px;
  background: #14b82c;
  margin-bottom: 14px;
}

.section-header-premium h2 {
  font-size: clamp(1.45rem, 4vw, 2rem);
  font-weight: 900;
  color: #1a241b;
  margin: 0 0 8px;
  line-height: 1.2;
}

.section-header-premium p {
  color: #64748b;
  font-size: clamp(0.96rem, 2vw, 1.02rem);
  margin: 0;
  line-height: 1.6;
}

/* =========================
   NEWS
========================= */
.news-section {
  padding: 70px 0;
  background: #f8fafc;
}

.news-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
  gap: 22px;
}

.premium-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 5px 18px rgba(0, 0, 0, 0.05);
  transition: 0.35s ease;
  cursor: pointer;
  border: 1px solid #eef2f7;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  min-height: 100%;
}

.premium-card:hover {
  transform: translateY(-8px);
  border-color: #ffd700;
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.card-media {
  position: relative;
  height: 220px;
  overflow: hidden;
  background: #e2e8f0;
}

.zoom-effect {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.8s ease;
}

.premium-card:hover .zoom-effect {
  transform: scale(1.08);
}

.video-badge {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: radial-gradient(circle, rgba(0, 0, 0, 0.35) 0%, rgba(0, 0, 0, 0.12) 65%, rgba(0, 0, 0, 0.04) 100%);
}

.video-badge i {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: rgba(255, 215, 0, 0.92);
  color: #1a241b;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.55rem;
  box-shadow: 0 10px 22px rgba(0, 0, 0, 0.25);
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
  color: #14b82c;
}

.card-content h3 {
  font-size: 1.12rem;
  font-weight: 800;
  color: #1a241b;
  margin: 12px 0 10px;
  line-height: 1.45;
  min-height: 3.2rem;
}

.card-content p {
  color: #64748b;
  font-size: 0.95rem;
  line-height: 1.65;
  margin: 0 0 16px;
  flex-grow: 1;
}

.card-footer-link {
  font-weight: 800;
  color: #1a241b;
  font-size: 0.92rem;
  display: flex;
  align-items: center;
  margin-top: auto;
}

/* CTA */
.cta-card {
  background: #1a241b !important;
  border: 2px dashed #ffd700 !important;
  justify-content: center;
  align-items: center;
  text-align: center;
}

.cta-content {
  padding: 28px 20px;
  width: 100%;
}

.cta-content h3 {
  font-size: 1.3rem;
  font-weight: 900;
  color: #ffffff;
  margin: 0 0 10px;
}

.cta-content p {
  color: #cbd5e1;
  line-height: 1.7;
  margin: 0 0 20px;
}

.cta-icon {
  width: 72px;
  height: 72px;
  background: rgba(255, 215, 0, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 18px;
}

.cta-icon i {
  font-size: 2rem;
  color: #ffd700;
}

.cta-btn {
  min-height: 48px !important;
}

/* =========================
   MEDIA
========================= */
.media-section {
  margin-top: 60px;
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
  border-radius: 18px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 8px 18px rgba(0, 0, 0, 0.06);
  transition: 0.35s ease;
}

.media-card:hover {
  transform: translateY(-8px);
  border-color: #ffd700;
}

.media-cover {
  position: relative;
  height: 210px;
  overflow: hidden;
}

.media-cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.7s ease;
}

.media-card:hover .media-cover img {
  transform: scale(1.06);
}

.media-cover-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.08));
}

.media-badge {
  position: absolute;
  left: 14px;
  bottom: 14px;
  display: inline-flex;
  gap: 8px;
  align-items: center;
  background: rgba(255, 215, 0, 0.94);
  color: #1a241b;
  padding: 8px 12px;
  border-radius: 999px;
  font-weight: 900;
  font-size: 0.78rem;
  max-width: calc(100% - 28px);
}

.media-title {
  font-size: 1.18rem;
  font-weight: 900;
  color: #1a241b;
  margin: 0 0 10px;
}

.media-desc {
  color: #64748b;
  font-size: 0.96rem;
  line-height: 1.65;
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
  background: #1a241b;
  color: white;
  padding-top: 70px;
}

.footer-grid {
  display: flex;
  flex-direction: column;
  gap: 36px;
  padding-bottom: 55px;
}

.footer-title {
  color: #ffd700;
  font-weight: 900;
  margin: 0 0 18px;
  font-size: clamp(1.35rem, 3vw, 1.8rem);
}

.footer-intro {
  color: #cbd5e1;
  line-height: 1.75;
  margin: 0;
  font-size: 1rem;
}

.soldier-honor-card {
  position: relative;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.01) 100%);
  border: 1px solid rgba(212, 175, 55, 0.3);
  border-left: 5px solid #d4af37;
  padding: 20px;
  border-radius: 16px;
  margin-top: 28px;
  overflow: hidden;
  backdrop-filter: blur(10px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
  display: flex;
  flex-direction: column;
  gap: 15px;
  align-items: flex-start;
  transition: all 0.35s ease;
}

.soldier-honor-card:hover {
  border-color: rgba(212, 175, 55, 0.8);
  transform: translateY(-5px);
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4), 0 0 15px rgba(212, 175, 55, 0.1);
}

.card-glow {
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(212, 175, 55, 0.08) 0%, transparent 60%);
  pointer-events: none;
}

.soldier-icon {
  font-size: 2.25rem;
  color: #d4af37;
  opacity: 0.9;
  filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.5));
  flex-shrink: 0;
}

.honor-content {
  position: relative;
  z-index: 2;
}

.honor-content h3 {
  margin: 0 0 12px;
  color: #ffffff;
  font-size: 1.25rem;
  font-weight: 900;
}

.honor-text {
  font-size: 1rem;
  line-height: 1.85;
  color: #e2e8f0;
  font-style: italic;
  font-weight: 400;
  margin: 0 0 16px;
  position: relative;
}

.honor-footer {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 10px;
}

.honor-rank {
  color: #d4af37;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  font-size: 0.78rem;
}

.honor-separator {
  width: 36px;
  height: 1px;
  background: rgba(212, 175, 55, 0.5);
}

.honor-motto {
  color: #94a3b8;
  font-weight: 700;
  text-transform: uppercase;
  font-size: 0.78rem;
}

.footer-docs {
  min-width: 0;
}

.copyright {
  background: #202d22;
  text-align: center;
  padding: 18px 14px;
  font-size: 0.78rem;
  color: #94a3b8;
  width: 100%;
  line-height: 1.6;
}

/* =========================
   PC / TABLETTE
========================= */
@media (min-width: 768px) {
  .media-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .soldier-honor-card {
    padding: 34px;
    flex-direction: row;
    gap: 24px;
  }
}

@media (min-width: 1024px) {
  .footer-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 390px;
    gap: 56px;
    align-items: start;
  }
}

@media (max-width: 992px) {
  .news-grid {
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  }
}

/* =========================
   MOBILE ZOOM
========================= */
@media (max-width: 768px) {
  .container {
    padding-inline: 18px;
  }

  .hero-premium {
    min-height: 720px;
    align-items: flex-end;
  }

  .hero-overlay {
    background:
      linear-gradient(
        to right,
        rgba(26, 36, 27, 0.94) 0%,
        rgba(26, 36, 27, 0.84) 46%,
        rgba(26, 36, 27, 0.52) 76%,
        rgba(26, 36, 27, 0.34) 100%
      );
  }

  .hero-content {
    padding-block: 118px 42px;
  }

  .hero-text-box {
    width: 100%;
    max-width: 100%;
  }

  .custom-tag-official {
    font-size: 0.92rem !important;
    padding: 0.6rem 1rem !important;
  }

  .hero-text-box h1 {
    font-size: 2.7rem;
    line-height: 1.04;
    letter-spacing: -0.03em;
  }

  .hero-subtext {
    max-width: 100%;
    font-size: 1.08rem;
    line-height: 1.78;
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
    min-height: 58px !important;
    font-size: 1rem !important;
  }

  .news-section {
    padding: 64px 0;
  }

  .section-header-premium {
    margin-bottom: 28px;
  }

  .header-line {
    width: 78px;
    height: 7px;
    margin-bottom: 16px;
  }

  .section-header-premium h2 {
    font-size: 2rem;
    line-height: 1.12;
    margin-bottom: 10px;
  }

  .section-header-premium p {
    font-size: 1.02rem;
    line-height: 1.7;
  }

  .news-grid {
    grid-template-columns: 1fr;
    gap: 22px;
  }

  .premium-card {
    border-radius: 18px;
  }

  .card-media {
    height: 250px;
  }

  .card-content {
    padding: 20px 20px 22px;
  }

  .date {
    font-size: 0.92rem;
    gap: 8px;
  }

  .card-content h3 {
    font-size: 1.28rem;
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
    width: 64px;
    height: 64px;
    font-size: 1.55rem;
  }

  .cta-content {
    padding: 30px 20px;
  }

  .cta-content h3 {
    font-size: 1.45rem;
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
    margin-top: 68px;
  }

  .media-header {
    margin-bottom: 26px;
  }

  .media-grid {
    gap: 22px;
  }

  .media-card {
    border-radius: 18px;
  }

  .media-cover {
    height: 235px;
  }

  .media-badge {
    font-size: 0.86rem;
    padding: 9px 14px;
    gap: 9px;
  }

  .media-title {
    font-size: 1.32rem;
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
    font-size: 1.7rem;
    margin-bottom: 16px;
  }

  .footer-intro {
    font-size: 1.02rem;
    line-height: 1.8;
  }

  .soldier-honor-card {
    padding: 22px 18px;
    border-left-width: 4px;
    gap: 16px;
  }

  .soldier-icon {
    font-size: 2.2rem;
  }

  .honor-content h3 {
    font-size: 1.35rem;
    margin-bottom: 14px;
  }

  .honor-text {
    font-size: 1rem;
    line-height: 1.9;
  }

  .honor-rank,
  .honor-motto {
    font-size: 0.84rem;
  }

  .copyright {
    font-size: 0.85rem;
    padding: 18px 16px;
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
    min-height: 675px;
  }

  .hero-content {
    padding-block: 104px 34px;
  }

  .custom-tag-official {
    font-size: 0.86rem !important;
    padding: 0.55rem 0.9rem !important;
  }

  .hero-text-box h1 {
    font-size: 2.2rem;
  }

  .hero-subtext {
    font-size: 1rem;
    line-height: 1.72;
    margin: 18px 0 24px;
    padding-left: 14px;
  }

  .hero-action-btn {
    min-height: 54px !important;
    font-size: 0.96rem !important;
  }

  .section-header-premium h2 {
    font-size: 1.72rem;
  }

  .section-header-premium p {
    font-size: 0.98rem;
  }

  .news-grid {
    gap: 18px;
  }

  .premium-card {
    border-radius: 16px;
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
    font-size: 1.14rem;
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
    font-size: 1.25rem;
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
    font-size: 0.76rem;
    padding: 8px 12px;
  }

  .media-title {
    font-size: 1.14rem;
  }

  .media-desc {
    font-size: 0.95rem;
  }

  .fama-footer {
    padding-top: 56px;
  }

  .footer-title {
    font-size: 1.45rem;
  }

  .footer-intro {
    font-size: 0.95rem;
  }

  .soldier-honor-card {
    padding: 18px 16px;
  }

  .soldier-icon {
    font-size: 2rem;
  }

  .honor-content h3 {
    font-size: 1.18rem;
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

/* =========================
   TRÈS PETITS ÉCRANS
========================= */
@media (max-width: 380px) {
  .container {
    padding-inline: 14px;
  }

  .hero-premium {
    min-height: 630px;
  }

  .hero-text-box h1 {
    font-size: 1.95rem;
  }

  .hero-subtext {
    font-size: 0.94rem;
  }

  .custom-tag-official {
    font-size: 0.78rem !important;
    padding: 0.46rem 0.78rem !important;
  }

  .section-header-premium h2 {
    font-size: 1.5rem;
  }

  .card-media {
    height: 190px;
  }

  .card-content h3 {
    font-size: 1.06rem;
  }

  .card-content p {
    font-size: 0.9rem;
  }

  .media-cover {
    height: 185px;
  }

  .cta-content h3,
  .media-title {
    font-size: 1.06rem;
  }

  .footer-title {
    font-size: 1.28rem;
  }
}
</style>