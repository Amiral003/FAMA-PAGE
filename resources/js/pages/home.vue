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

// ✅ (Option) images pour tes cartes — remplace si tu veux
import photoCover from '@/assets/images/hero.jpg'
import videoCover from '@/assets/images/fam.png'

// --- BLOC SEO AJOUTÉ ---
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

// --- LOGIQUE DIAPORAMA ---
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
  // ✅ VIDEO : thumbnail youtube auto (ou fallback)
  if (post?.type === 'video') {
    if (post?.video_thumbnail_url) return post.video_thumbnail_url
    // fallback: si youtube et pas de thumbnail, on peut tenter avec l'id
    if (post?.video_platform === 'youtube' && post?.video_url) {
      const id = getYoutubeId(post.video_url)
      if (id) return `https://img.youtube.com/vi/${id}/hqdefault.jpg`
    }
    return '/assets/images/fama-placeholder.jpg'
  }

  // ✅ PDF : miniature dédiée
  if (post?.type === 'pdf') {
    if (post?.thumbnail) return `/storage/${post.thumbnail}`
    return '/assets/images/fama-placeholder.jpg'
  }

  // ✅ ARTICLE : image media[0]
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
          <Tag value="PORTAIL OFFICIEL" class="mb-4 custom-tag-official" />
          <h1>
            Défense de la Patrie <br />
            <span class="text-gold">Engagement Sans Faille</span>
          </h1>
          <p class="hero-subtext">
            Les Forces Armées Maliennes sont l’expression vivante du devoir, du courage et de la fidélité à la Patrie.
            Servir dans les FAMA, c’est protéger le Mali, défendre son peuple et garantir sa souveraineté.
          </p>
          <div class="hero-btns">
            <Button label="COMMUNIQUÉS" icon="pi pi-file" class="btn-fama-gold" @click="router.push('/portfolio')" />
            <Button
              label="NOTRE HISTOIRE"
              icon="pi pi-shield"
              variant="text"
              class="p-button-text text-white"
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
                <span class="date"><i class="pi pi-calendar"></i> {{ new Date(post.created_at).toLocaleDateString() }}</span>
                <h3>{{ post.title }}</h3>
                <p>{{ stripHtml(post.content).substring(0, 85) }}...</p>
                <div class="card-footer-link">Consulter <i class="pi pi-arrow-right ml-2"></i></div>
              </div>
            </div>

            <div class="premium-card cta-card" @click="router.push('/portfolio')">
              <div class="cta-content">
                <div class="cta-icon">
                  <i class="pi pi-folder-open"></i>
                </div>
                <h3>Archives & Communiqués</h3>
                <p>Accédez à l'intégralité des documents et rapports officiels.</p>
                <Button label="TOUT VOIR" icon="pi pi-chevron-right" iconPos="right" class="btn-fama-gold p-button-sm" />
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
                <p class="media-desc">Accédez aux images officielles : cérémonies, opérations, formations, actions civilo-militaires.</p>
                <div class="media-actions">
                  <Button label="Explorer" icon="pi pi-arrow-right" iconPos="right" class="btn-fama-gold p-button-sm" />
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
                <p class="media-desc">Reportages, communiqués vidéo, interviews, et contenus officiels validés par l'État-Major.</p>
                <div class="media-actions">
                  <Button label="Regarder" icon="pi pi-play" iconPos="left" class="btn-fama-gold p-button-sm" />
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
          <p>La défense de la patrie est un devoir sacré. Restez connectés aux sources officielles pour des informations vérifiées.</p>

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
      <div class="copyright">© 2026 RÉPUBLIQUE DU MALI • UN PEUPLE • UN BUT • UNE FOI</div>
    </footer>
  </main>
</template>

<style scoped>
/* --- BLOC HONORABLE DU SOLDAT --- */
.soldier-honor-card {
  position: relative;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.01) 100%);
  border: 1px solid rgba(212, 175, 55, 0.3);
  border-left: 5px solid #d4af37;
  padding: 20px;
  border-radius: 12px;
  margin-top: 30px;
  overflow: hidden;
  backdrop-filter: blur(10px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
  display: flex;
  flex-direction: column;
  gap: 15px;
  align-items: flex-start;
}

@media (min-width: 768px) {
  .soldier-honor-card {
    padding: 40px;
    flex-direction: row;
    gap: 25px;
  }
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
  font-size: 2.5rem;
  color: #d4af37;
  opacity: 0.8;
  filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.5));
}

.honor-text {
  font-size: 1.15rem;
  line-height: 1.8;
  color: #e2e8f0;
  font-style: italic;
  font-weight: 400;
  margin-bottom: 20px;
  position: relative;
}

.honor-footer {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-top: 15px;
}

.honor-rank {
  color: #d4af37;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 2px;
  font-size: 0.8rem;
}

.honor-separator {
  width: 40px;
  height: 1px;
  background: rgba(212, 175, 55, 0.5);
}

.honor-motto {
  color: #94a3b8;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.8rem;
}

.soldier-honor-card:hover {
  border-color: rgba(212, 175, 55, 0.8);
  transform: translateY(-5px);
  transition: all 0.4s ease;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4), 0 0 15px rgba(212, 175, 55, 0.1);
}

/* BASES & COULEURS */
.home-page { background: #fdfdfd; }
.text-gold { color: #ffd700; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; width: 100%; }

/* HERO */
.hero-premium {
  position: relative;
  height: 80vh;
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
.hero-bg-layer.active { opacity: 1; }
.hero-overlay {
  position: absolute;
  z-index: 1;
  inset: 0;
  background: linear-gradient(to right, #2e362f 15%, rgba(30, 37, 31, 0.4));
}
.hero-content { position: relative; z-index: 10; }
.hero-text-box h1 {
  font-size: 3.5rem;
  font-weight: 900;
  line-height: 1.1;
  text-transform: uppercase;
  color: #cbd5e1;
}
@media (max-width: 768px) {
  .hero-text-box h1 { font-size: 2.2rem; }
}
.hero-subtext {
  max-width: 600px;
  font-size: 1.2rem;
  color: #cbd5e1;
  margin: 20px 0 40px;
  border-left: 4px solid #ffd700;
  padding-left: 20px;
}
.custom-tag-official { background: #14b82c !important; color: white; font-weight: 800; border-radius: 4px; }
.btn-fama-gold { background: #ffd700 !important; color: #1a241b !important; border: none !important; font-weight: bold !important; }

/* ACTUALITÉS GRID */
.news-section { padding: 80px 0; background: #f8fafc; }
.section-header-premium { margin-bottom: 50px; }
.header-line { width: 70px; height: 6px; background: #14b82c; margin-bottom: 15px; }
.section-header-premium h2 { font-size: 1.8rem; font-weight: 900; color: #1a241b; }

.video-badge {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: radial-gradient(circle, rgba(0,0,0,0.35) 0%, rgba(0,0,0,0.15) 60%, rgba(0,0,0,0.05) 100%);
}
.video-badge i {
  width: 64px; height: 64px; border-radius: 50%;
  background: rgba(255,215,0,0.92); color: #1a241b;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.6rem; box-shadow: 0 10px 22px rgba(0,0,0,0.25);
}

.news-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 30px;
}

/* CARTE STANDARD */
.premium-card {
  background: white; border-radius: 4px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  transition: 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  cursor: pointer; border: 1px solid #f1f5f9;
  display: flex; flex-direction: column; overflow: hidden;
}
.premium-card:hover { transform: translateY(-10px); border-color: #ffd700; box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1); }

.card-media { position: relative; height: 210px; overflow: hidden; }
.zoom-effect { width: 100%; height: 100%; object-fit: cover; transition: 0.8s; }
.premium-card:hover .zoom-effect { transform: scale(1.1); }

.card-content { padding: 25px; flex-grow: 1; }
.date { font-size: 0.8rem; font-weight: 700; color: #14b82c; }
.card-content h3 { font-size: 1.2rem; font-weight: 800; color: #1a241b; margin: 12px 0; line-height: 1.4; height: 3.4rem; overflow: hidden; }
.card-content p { color: #64748b; font-size: 0.9rem; line-height: 1.5; margin-bottom: 15px; }
.card-footer-link { font-weight: 800; color: #1a241b; font-size: 0.85rem; display: flex; align-items: center; }

/* CARTE CTA */
.cta-card { background: #1a241b !important; border: 2px dashed #ffd700 !important; justify-content: center; align-items: center; text-align: center; }
.cta-content { padding: 30px; }
.cta-icon { width: 70px; height: 70px; background: rgba(255, 215, 0, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
.cta-icon i { font-size: 2.5rem; color: #ffd700; }

/* MÉDIATHÈQUE */
.media-section { margin-top: 55px; }
.media-header { margin-bottom: 25px; }
.media-grid { display: grid; grid-template-columns: 1fr; gap: 20px; }
@media (min-width: 768px) { .media-grid { grid-template-columns: repeat(2, 1fr); } }

.media-card { cursor: pointer; overflow: hidden; border-radius: 10px; border: 1px solid #e2e8f0; box-shadow: 0 8px 18px rgba(0, 0, 0, 0.06); transition: 0.35s ease; }
.media-card:hover { transform: translateY(-8px); border-color: #ffd700; }
.media-cover { position: relative; height: 180px; overflow: hidden; }
.media-cover img { width: 100%; height: 100%; object-fit: cover; transition: 0.7s; }
.media-badge { position: absolute; left: 16px; bottom: 14px; display: flex; gap: 10px; align-items: center; background: rgba(255, 215, 0, 0.9); color: #1a241b; padding: 8px 12px; border-radius: 999px; font-weight: 900; font-size: 0.8rem; }

/* FOOTER - LE FIX DE LA SIDEBAR */
.fama-footer { background: #1a241b; color: white; padding-top: 80px; }
.footer-grid {
  display: flex;
  flex-direction: column;
  gap: 40px;
  padding-bottom: 60px;
}
@media (min-width: 1024px) {
  .footer-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 60px;
  }
}
.footer-title { color: #ffd700; font-weight: 900; margin-bottom: 25px; }
.copyright { background: #202d22; text-align: center; padding: 25px; font-size: 0.8rem; color: #64748b; width: 100%; }

@media (max-width: 992px) {
  .hero-text-box h1 { font-size: 2.8rem; }
  .news-grid { grid-template-columns: 1fr; }
}
</style>
