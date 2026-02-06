<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useHead } from '@vueuse/head'
import Skeleton from 'primevue/skeleton'
import Tag from 'primevue/tag'
import Button from 'primevue/button'
import SidebarOfficial from '@/components/SidebarOfficial.vue'

useHead({ 
  title: 'Accueil | FAMa Officiel',
  meta: [{ name: 'description', content: 'Portail officiel des Forces Armées Maliennes.' }]
})

const posts = ref([])
const loading = ref(true)
const router = useRouter()

onMounted(async () => {
  try {
    const res = await axios.get('/api/posts')
    // On récupère les données brutes, le template gérera le slice(0, 5)
    posts.value = res.data.data
  } catch (e) {
    console.error("Erreur chargement posts:", e)
  } finally {
    loading.value = false
  }
})

// Gestion intelligente des images (Thumbnail pour PDF, Media[0] pour Articles)
const getPostImage = (post) => {
  if (post.thumbnail) return `/storage/${post.thumbnail}`;
  if (post.media && post.media.length > 0) return `/storage/${post.media[0].file_path}`;
  return '/assets/images/fama-placeholder.jpg';
}

const recentPdfs = computed(() => posts.value.filter(p => p.pdf_path).slice(0, 3))
</script>

<template>
  <main class="home-page">
    <section class="hero-premium">
      <div class="hero-overlay"></div>
      <div class="container hero-content">
        <div class="hero-text-box" data-aos="fade-up">
          <Tag value="PORTAIL OFFICIEL" class="mb-4 custom-tag-official" />
          <h1>Défense de la Patrie <br/>
            <span class="text-gold">Engagement Sans Faille</span>
          </h1>
          <p class="hero-subtext">
            Depuis 1960, les FAMa assurent la protection de la population malienne 
            et la souveraineté de la République sur l'ensemble du territoire.
          </p>
          <div class="hero-btns">
            <Button label="COMMUNIQUÉS" icon="pi pi-file" class="btn-fama-gold" @click="router.push('/portfolio')" />
            <Button label="NOTRE HISTOIRE" icon="pi pi-shield" variant="text" class="p-button-text text-white" @click="router.push('/about')" />
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
                <img :src="getPostImage(post)" :alt="post.title" class="zoom-effect">
                <div class="card-type-tag">
                   <Tag 
                      :value="post.type === 'pdf' ? 'DOCUMENT' : 'ACTUALITÉ'" 
                      :severity="post.type === 'pdf' ? 'danger' : 'success'" 
                   />
                </div>
              </div>
              <div class="card-content">
                <span class="date"><i class="pi pi-calendar"></i> {{ new Date(post.created_at).toLocaleDateString() }}</span>
                <h3>{{ post.title }}</h3>
                <p>{{ post.content?.substring(0, 85) }}...</p>
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
                    <Button label="TOUT VOIR" icon="pi pi-chevron-right" iconPos="right" class="btn-fama-gold p-button-sm" />
                </div>
            </div>
          </template>
        </div>
      </div>
    </section>

    <footer class="fama-footer">
      <div class="container footer-grid">
        <div class="footer-brand">
          <h3 class="footer-title">FORCES ARMÉES MALIENNES</h3>
          <p>La défense de la patrie est un devoir sacré. Restez connectés aux sources officielles pour des informations vérifiées.</p>
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
/* BASES & COULEURS */
.home-page { background: #fdfdfd; }
.text-gold { color: #FFD700; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

/* HERO */
.hero-premium {
  position: relative;
  min-height: 80vh;
  background: url('@/assets/images/hero.jpg') center/cover no-repeat;
  display: flex;
  align-items: center;
  color: white;
}
.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to right, #1a241b 40%, rgba(26, 36, 27, 0.4));
}
.hero-content { position: relative; z-index: 10; }
.hero-text-box h1 { font-size: 3.5rem; font-weight: 900; line-height: 1.1; text-transform: uppercase; }
.hero-subtext { max-width: 600px; font-size: 1.2rem; color: #cbd5e1; margin: 20px 0 40px; border-left: 4px solid #FFD700; padding-left: 20px; }
.custom-tag-official { background: #14B82C !important; color: white; font-weight: 800; border-radius: 4px; }
.btn-fama-gold { background: #FFD700 !important; color: #1a241b !important; border: none !important; font-weight: 800 !important; }

/* ACTUALITÉS GRID */
.news-section { padding: 80px 0; background: #f8fafc; }
.section-header-premium { margin-bottom: 50px; }
.header-line { width: 70px; height: 6px; background: #14B82C; margin-bottom: 15px; }
.section-header-premium h2 { font-size: 1.8rem; font-weight: 900; color: #1a241b; }

.news-grid { 
  display: grid; 
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); 
  gap: 30px; 
}

/* CARTE STANDARD */
.premium-card {
  background: white;
  border-radius: 4px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
  transition: 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  cursor: pointer;
  border: 1px solid #f1f5f9;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.premium-card:hover { transform: translateY(-10px); border-color: #FFD700; box-shadow: 0 15px 30px rgba(0,0,0,0.1); }

.card-media { position: relative; height: 210px; overflow: hidden; }
.zoom-effect { width: 100%; height: 100%; object-fit: cover; transition: 0.8s; }
.premium-card:hover .zoom-effect { transform: scale(1.1); }
.card-type-tag { position: absolute; top: 12px; right: 12px; }

.card-content { padding: 25px; flex-grow: 1; }
.date { font-size: 0.8rem; font-weight: 700; color: #14B82C; }
.card-content h3 { font-size: 1.2rem; font-weight: 800; color: #1a241b; margin: 12px 0; line-height: 1.4; height: 3.4rem; overflow: hidden; }
.card-content p { color: #64748b; font-size: 0.9rem; line-height: 1.5; margin-bottom: 15px; }
.card-footer-link { font-weight: 800; color: #1a241b; font-size: 0.85rem; display: flex; align-items: center; }

/* CARTE 6 (APPEL À L'ACTION) */
.cta-card {
  background: #1a241b !important;
  border: 2px dashed #FFD700 !important;
  justify-content: center;
  align-items: center;
  text-align: center;
}
.cta-content { padding: 30px; }
.cta-icon { 
    width: 70px; height: 70px; background: rgba(255, 215, 0, 0.1); 
    border-radius: 50%; display: flex; align-items: center; 
    justify-content: center; margin: 0 auto 20px; 
}
.cta-icon i { font-size: 2.5rem; color: #FFD700; }
.cta-card h3 { color: #FFD700 !important; height: auto !important; margin-bottom: 10px !important; }
.cta-card p { color: #cbd5e1 !important; margin-bottom: 25px !important; }

/* FOOTER */
.fama-footer { background: #1a241b; color: white; padding-top: 80px; }
.footer-grid { display: grid; grid-template-columns: 1fr 400px; gap: 60px; padding-bottom: 60px; }
.footer-title { color: #FFD700; font-weight: 900; margin-bottom: 25px; }
.copyright { background: #0e140f; text-align: center; padding: 25px; font-size: 0.8rem; color: #64748b; }

@media (max-width: 992px) {
  .hero-text-box h1 { font-size: 2.8rem; }
  .footer-grid { grid-template-columns: 1fr; }
  .news-grid { grid-template-columns: 1fr; }
}
</style>