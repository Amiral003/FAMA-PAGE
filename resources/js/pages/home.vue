<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import Typed from 'typed.js'
import { useHead } from '@vueuse/head'
import SidebarOfficial from '@/components/SidebarOfficial.vue'

useHead({
  title: 'Accueil | FAMa Officiel',
  meta: [
    { name: 'description', content: 'Plateforme officielle des Forces Armées Maliennes.' },
  ],
})

const typedEl = ref(null)
let typedInstance = null
const posts = ref([])
const loading = ref(true)
const router = useRouter()

onMounted(async () => {
  // Animation de texte
  typedInstance = new Typed(typedEl.value, {
    strings: [
      'Le Mali ne plie pas. Le Mali se bat.',
      'Notre terre, notre honneur, notre combat.',
      'Debout pour la Patrie. Forts pour le Mali.'
    ],
    typeSpeed: 40,
    backSpeed: 25,
    loop: true,
    backDelay: 2000,
  })

  // Récupération des posts
  try {
    const res = await axios.get('/api/posts?limit=6')
    posts.value = res.data.data.slice(0, 6)
  } catch (e) {
    console.error("Erreur chargement posts:", e)
  } finally {
    loading.value = false
  }
})

onUnmounted(() => {
  if (typedInstance) typedInstance.destroy()
})

// On passe les 3 derniers PDF à la sidebar en bas
const recentPdfs = computed(() => {
  return posts.value.filter(p => p.pdf_path).slice(0, 3)
})

const goPortfolio = () => router.push('/portfolio')
const goToAbout = () => router.push('/about')
</script>

<template>
  <main class="home-page">
    <section class="hero">
      <div class="container hero-grid">
        <div class="hero-text" data-aos="fade-right">
          <div class="badge-official">PLATEFORME OFFICIELLE</div>
          <h1>Forces Armées <span>Maliennes</span></h1>
          <h2 class="typed-text"><span ref="typedEl"></span></h2>
          
          <p class="hero-desc">
            Garant de l'intégrité territoriale et de la souveraineté nationale, 
            les FAMa veillent sur la paix et la sécurité de tous les Maliens depuis 1960.
          </p>

          <div class="hero-actions">
            <button @click="goPortfolio" class="btn-primary">Communiqués</button>
            <button @click="goToAbout" class="btn-secondary">Notre Histoire</button>
          </div>
        </div>
      </div>
    </section>

    <section class="home-posts">
      <div class="container">
        <div class="section-header">
          <h2>Dernières Publications</h2>
          <div class="red-line"></div>
        </div>

        <div class="grid">
          <div v-if="loading" v-for="i in 3" :key="i" class="skeleton-card">
            <div class="skeleton-img"></div>
            <div class="skeleton-line title"></div>
            <div class="skeleton-line"></div>
          </div>

          <div
            v-else
            v-for="(post, index) in posts"
            :key="post.id"
            class="post-card"
            data-aos="fade-up"
            :data-aos-delay="index * 100"
            @click="router.push(`/posts/${post.slug}`)"
          >
            <div class="img-wrapper">
              <img
                v-if="post.media?.length"
                :src="`/storage/${post.media[0].file_path}`"
                class="card-img"
                alt="Post"
              />
              <div v-else class="img-placeholder">FAMa</div>
            </div>
            <div class="card-body">
              <span class="card-date">{{ new Date(post.published_at).toLocaleDateString() }}</span>
              <h3>{{ post.title }}</h3>
              <p>{{ post.content?.substring(0, 80) }}...</p>
            </div>
          </div>
        </div>

        <div class="center-btn">
          <button class="btn-outline" @click="goPortfolio">Tout voir →</button>
        </div>
      </div>
    </section>

    <footer class="footer-sidebar" data-aos="fade-up">
      <div class="container footer-grid">
        <div class="footer-info">
          <h3>SÉCURITÉ NATIONALE</h3>
          <p>Restez informés via les canaux officiels pour éviter les fausses informations.</p>
          <div class="vigilance-footer">
            <span class="dot"></span> 80 00 11 11 (Appel Gratuit)
          </div>
        </div>
        
        <div class="footer-component">
           <SidebarOfficial :recentDocs="recentPdfs" />
        </div>
      </div>
      <div class="bottom-bar">
        © 2026 Forces Armées Maliennes • Un Peuple, Un But, Une Foi.
      </div>
    </footer>
  </main>
</template>

<style scoped>
.home-page { background: #fff; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

/* HERO */
.hero {
  background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('@/assets/images/hero.jpg');
  background-size: cover;
  background-position: center;
  padding: 120px 0;
  color: white;
  min-height: 80vh;
  display: flex;
  align-items: center;
}

.badge-official {
  background: #ce1126;
  display: inline-block;
  padding: 5px 12px;
  border-radius: 4px;
  font-weight: 800;
  font-size: 0.8rem;
  margin-bottom: 20px;
}

.hero-text h1 { font-size: 3.5rem; font-weight: 900; margin: 0; }
.hero-text h1 span { color: #ce1126; }
.typed-text { height: 40px; color: #cbd5e1; font-size: 1.5rem; margin: 20px 0; }
.hero-desc { max-width: 600px; font-size: 1.1rem; line-height: 1.6; color: #e2e8f0; }

.hero-actions { margin-top: 40px; display: flex; gap: 15px; }
.btn-primary { background: #ce1126; color: white; border: none; padding: 15px 30px; font-weight: bold; border-radius: 6px; cursor: pointer; transition: 0.3s; }
.btn-secondary { background: rgba(255,255,255,0.1); color: white; border: 1px solid white; padding: 15px 30px; font-weight: bold; border-radius: 6px; cursor: pointer; backdrop-filter: blur(5px); }

/* ACTUALITÉS */
.home-posts { padding: 80px 0; background: #f8fafc; }
.section-header { margin-bottom: 40px; }
.red-line { width: 50px; height: 4px; background: #ce1126; margin-top: 10px; }

.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; }

.post-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); cursor: pointer; transition: 0.3s; }
.post-card:hover { transform: translateY(-10px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }

.img-wrapper { height: 200px; overflow: hidden; }
.card-img { width: 100%; height: 100%; object-fit: cover; }
.card-body { padding: 20px; }
.card-date { font-size: 0.8rem; color: #ce1126; font-weight: bold; }
.card-body h3 { margin: 10px 0; font-size: 1.2rem; line-height: 1.3; }

.btn-outline { margin-top: 40px; padding: 12px 30px; border: 2px solid #1a1c1e; background: none; font-weight: bold; cursor: pointer; border-radius: 6px; }

/* FOOTER-SIDEBAR */
.footer-sidebar { background: #1a1c1e; color: white; padding-top: 60px; border-top: 4px solid #ce1126; }
.footer-grid { display: grid; grid-template-columns: 1fr 350px; gap: 50px; padding-bottom: 40px; }

.footer-info h3 { color: #ce1126; margin-bottom: 20px; }
.vigilance-footer { font-size: 1.5rem; font-weight: 900; margin-top: 20px; display: flex; align-items: center; gap: 10px; }
.dot { width: 12px; height: 12px; background: #ce1126; border-radius: 50%; animation: blink 1s infinite; }

.bottom-bar { background: #111; text-align: center; padding: 20px; font-size: 0.8rem; color: #666; }

@keyframes blink { 50% { opacity: 0; } }

/* RESPONSIVE */
@media (max-width: 992px) {
  .hero-text h1 { font-size: 2.5rem; }
  .footer-grid { grid-template-columns: 1fr; }
  .grid { grid-template-columns: 1fr; }
}
</style>