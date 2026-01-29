<script setup>
import { useHead } from '@vueuse/head'
import { useRouter } from 'vue-router'
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import SidebarOfficial from '@/components/SidebarOfficial.vue'

useHead({
  title: 'Avis & Communiqués | FAMA',
  meta: [{ name: 'description', content: 'Fil d’actualité officiel des FAMa.' }],
})

const posts = ref([])
const search = ref('')
const loading = ref(true)
const router = useRouter()

onMounted(async () => {
  try {
    const res = await axios.get('/api/posts')
    posts.value = res.data.data || res.data
  } catch (e) {
    console.error("Erreur API:", e)
  } finally {
    loading.value = false
  }
})

const recentPdfs = computed(() => {
  return posts.value.filter(p => p.pdf_path).slice(0, 3)
})

const filteredPosts = computed(() => {
  if (!search.value) return posts.value
  const q = search.value.toLowerCase()
  return posts.value.filter(post =>
    (post.title?.toLowerCase().includes(q) || post.content?.toLowerCase().includes(q))
  )
})

const getShareLink = (platform, post) => {
  const url = window.location.origin + '/posts/' + post.slug
  const text = encodeURIComponent(`FAMa : ${post.title}`)
  const links = {
    facebook: `https://www.facebook.com`,
    twitter: 'https://twitter.com',
    whatsapp: 'https://wa.me'
   // twitter: `https://twitter.com/intent/tweet?url=${url}&text=${text}`,
    //whatsapp: `https://api.whatsapp.com/send?text=${text}%20${url}`
  }
  return links[platform]
}
</script>

<template>
  <div class="portfolio-container">
    <div class="main-layout container">

      <section class="feed-column">
        <header class="header-section">
          <h1>Communiqués & Avis Officiels</h1>
          <input
            v-model="search"
            type="text"
            placeholder="Rechercher un communiqué..."
            class="search-bar"
          />
        </header>

        <div v-if="loading" class="center-msg">
          <div class="spinner"></div>
          <p>Chargement des informations...</p>
        </div>

        <div v-else>
          <div v-if="filteredPosts.length > 0">
            <article
              v-for="post in filteredPosts"
              :key="post.id"
              class="post-item"
              @click="router.push(`/posts/${post.slug}`)"
            >
              <span class="type-badge" v-if="post.type">{{ post.type }}</span>
              <h2>{{ post.title }}</h2>
              <p class="post-meta">📅 {{ new Date(post.published_at || post.created_at).toLocaleDateString('fr-FR') }}</p>


              <div class="media-box">
  <img
    v-if="post.thumbnail"
    :src="`/storage/${post.thumbnail}`"
    class="post-img"
    alt="Couverture du post"
  />

  <img
    v-else-if="post.media?.length"
    :src="`/storage/${post.media[0].file_path}`"
    class="post-img"
  />

  <div v-else-if="post.pdf_path" class="pdf-box">
    📄 Document Officiel
  </div>
</div>

              <p class="excerpt">{{ post.content?.substring(0, 160) }}...</p>

              <div class="footer-actions">
                <div class="btns">
                  <span class="read-btn">Lire la suite</span>
                  <a v-if="post.pdf_path" :href="`/storage/${post.pdf_path}`" download @click.stop class="pdf-btn">PDF</a>
                </div>
                <div class="socials" @click.stop>
                  <a href=https://www.facebook.com/ target="_blank" class="s-fb">F</a>

                  <a href=https://web.whatsapp.com/ target="_blank" class="s-wa">W</a>
                  <a href=https://x.com target="_blank" class="s-tw">T</a>
                </div>
              </div>
            </article>
          </div>
          <div v-else class="center-msg">Aucun résultat.</div>
        </div>
      </section>

      <aside class="sidebar-column">
        <SidebarOfficial :recentDocs="recentPdfs" />
      </aside>

    </div>
  </div>
</template>

<style scoped>
/* Conteneur principal avec fond gris léger pour faire ressortir les cartes */
.portfolio-container {
  background: #f0f2f5;
  min-height: 100vh;
  /* AJOUTE CECI : */
  padding-top: 40px; /* Espace entre la navbar et le titre */
  padding-bottom: 40px;
  font-family: 'Inter', sans-serif;
}

/* Si la barre de recherche est encore trop proche sur mobile */
@media (max-width: 768px) {
  .portfolio-container {
    padding-top: 20px;
  }
}

.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

/* Layout Grid */
.main-layout { display: grid; grid-template-columns: 1fr 340px; gap: 30px; }

@media (max-width: 992px) {
  .main-layout { grid-template-columns: 1fr; }
  .sidebar-column { display: none; }
}

/* En-tête de section */
.header-section { margin-bottom: 30px; }
h1 { font-size: 2rem; font-weight: 800; color: #1a1c1e; margin-bottom: 20px; }

.search-bar {
  width: 100%;
  max-width: 90%;
  padding: 12px 18px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 0.95rem;
  box-shadow: 0 2px 5px rgba(0,0,0,0.02);
  transition: all 0.3s;
  display: block;
}
.search-bar:focus { outline: none;
     border-color: #ce1126;
    box-shadow: 0 0 0 3px rgba(206, 17, 38, 0.1); /* Halo rouge au focus */
  max-width: 400px;  }

/* --- DESIGN DES POSTS (CARTES) --- */
.post-item {
  background: white;
  padding: 30px;
  border-radius: 16px;
  margin-bottom: 30px; /* C'est ici qu'on crée l'espace entre les posts */
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  cursor: pointer;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border: 1px solid rgba(0,0,0,0.03);
}

.post-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 24px rgba(0,0,0,0.1);
}

/* Badge et Meta */
.type-badge {
  background: #ce1126;
  color: white;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 800;
  text-transform: uppercase;
  display: inline-block;
  margin-bottom: 12px;
}

h2 {
  margin: 10px 0;
  font-size: 1.6rem;
  line-height: 1.3;
  color: #111;
  font-weight: 700;
}

.post-meta {
  font-size: 0.9rem;
  color: #6b7280;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 5px;
}

/* Media Box */
.media-box {
  margin: 20px 0;
  border-radius: 12px;
  overflow: hidden;
  background: #f3f4f6;
}

.post-img {
  width: 100%;
  max-height: 450px;
  object-fit: cover;
  display: block;
}

.pdf-box {
  padding: 50px;
  text-align: center;
  color: #4b5563;
  border: 2px dashed #d1d5db;
  border-radius: 12px;
  font-weight: 600;
  background: #f9fafb;
}

/* Texte */
.excerpt {
  color: #374151;
  line-height: 1.7;
  margin-bottom: 25px;
  font-size: 1.05rem;
}

/* Actions Footer */
.footer-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 20px;
  border-top: 1px solid #f3f4f6;
}

.btns { display: flex; gap: 20px; align-items: center; }

.read-btn {
  color: #ce1126;
  font-weight: 700;
  font-size: 0.95rem;
  position: relative;
}
.read-btn::after {
  content: ' →';
  transition: margin-left 0.2s;
}
.post-item:hover .read-btn::after { margin-left: 5px; }

.pdf-btn {
  background: #065f46;
  color: white;
  padding: 6px 14px;
  border-radius: 8px;
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 600;
  transition: opacity 0.2s;
}
.pdf-btn:hover { opacity: 0.9; }

/* Socials */
.socials { display: flex; gap: 10px; }
.socials a {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  font-size: 0.8rem;
  font-weight: bold;
  transition: transform 0.2s;
}
.socials a:hover { transform: scale(1.1); }
.s-fb { background: #1877f2; }
.s-wa { background: #25d366; }
.s-tw { background: #000000}

/* Utils */
.center-msg { text-align: center; padding: 60px; color: #6b7280; font-size: 1.1rem; }
.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #ce1126;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

/* Mobile Adjustments */
@media (max-width: 640px) {
  .post-item { padding: 20px; margin-bottom: 20px; }
  h2 { font-size: 1.3rem; }
  .footer-actions { flex-direction: column; gap: 15px; align-items: flex-start; }
  .socials { width: 100%; justify-content: flex-end; }
}
</style>
