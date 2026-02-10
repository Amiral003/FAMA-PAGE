<script setup>
import { useHead } from '@vueuse/head'
import { useRouter } from 'vue-router'
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import SidebarOfficial from '@/components/SidebarOfficial.vue'
 import InputText from 'primevue/inputtext'

useHead({
  title: 'Avis & Communiqués | FAMa',
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

// Fonctions utilitaires
const getPostImage = (post) => {
  if (post.thumbnail) return `/storage/${post.thumbnail}`;
  if (post.media && post.media.length > 0) return `/storage/${post.media[0].file_path}`;
  return null;
}

const downloadPDF = (path) => {
  if (!path) return;
  const link = document.createElement('a');
  link.href = `/storage/${path}`;
  link.download = '';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const getShareLink = (platform, post) => {
  if (typeof window === 'undefined') return '#';
  const url = window.location.origin + '/posts/' + post.slug;
  const text = encodeURIComponent(`FAMa : ${post.title}`);
  const links = {
    facebook: `https://www.facebook.com/sharer/sharer.php?u=${url}`,
    whatsapp: `https://api.whatsapp.com/send?text=${text}%20${url}`
  };
  return links[platform];
}

const filteredPosts = computed(() => {
  if (!search.value) return posts.value
  const q = search.value.toLowerCase()
  return posts.value.filter(post =>
    (post.title?.toLowerCase().includes(q) || post.content?.toLowerCase().includes(q))
  )
})

const recentPdfs = computed(() => posts.value.filter(p => p.pdf_path).slice(0, 3))
</script>

<template>
  <div class="portfolio-container">
    <div class="main-layout container">

      <section class="feed-column">
        <header class="header-section">
            <div class="title-wrapper">
                  <h1 class="page-title">Communiqués & Avis Officiels

                  </h1>
            </div>

          <div class="search-hero">
            <div class="p-input-icon-left search-wrapper">
              <i class="pi pi-search"></i>
              <InputText
                v-model="search"
                placeholder="Rechercher dans les archives officielles..."
                class="w-full search-input"
              />
            </div>
          </div>
        </header>

        <div v-if="loading">
          <div v-for="i in 2" :key="i" class="skeleton-card">
            <Skeleton width="30%" height="1.5rem" class="mb-4"></Skeleton>
            <Skeleton width="100%" height="250px" class="mb-4"></Skeleton>
            <Skeleton width="80%" height="1rem" class="mb-2"></Skeleton>
            <Skeleton width="60%" height="1rem"></Skeleton>
          </div>
        </div>

        <div v-else>
          <div v-if="filteredPosts.length > 0">
            <article
              v-for="post in filteredPosts"
              :key="post.id"
              class="news-card"
              @click="router.push(`/posts/${post.slug}`)"
            >
              <div class="card-meta">
                <Tag
                  :value="post.type || 'OFFICIEL'"
                  :severity="post.type === 'pdf' ? 'danger' : 'info'"
                />
                <span class="date-text">
                  <i class="pi pi-calendar mr-1"></i>
                  {{ new Date(post.published_at || post.created_at).toLocaleDateString('fr-FR') }}
                </span>
              </div>

              <h2 class="card-title">{{ post.title }}</h2>

              <div class="card-media" v-if="getPostImage(post) || post.pdf_path">
                <img
                  v-if="getPostImage(post)"
                  :src="getPostImage(post)"
                  class="featured-img"
                  alt="Actualité FAMa"
                />
                <div v-else-if="post.pdf_path" class="pdf-strip">
                  <i class="pi pi-file-pdf"></i>
                  <span>DOCUMENT OFFICIEL DISPONIBLE</span>
                </div>
              </div>

              <p class="card-excerpt">{{ post.content?.substring(0, 200) }}...</p>

              <div class="card-footer" @click.stop>
                <div class="action-btns">
                  <Button
                    label="Consulter l'article"
                    icon="pi pi-arrow-right"
                    iconPos="right"
                    text
                    class="p-button-success"
                    @click="router.push(`/posts/${post.slug}`)"
                  />
                  <Button
                    v-if="post.pdf_path"
                    icon="pi pi-download"
                    label="Télécharger PDF"
                    severity="secondary"
                    outlined
                    size="small"
                    @click="downloadPDF(post.pdf_path)"
                  />
                </div>
                <div class="social-share">
                  <a :href="getShareLink('facebook', post)" target="_blank" title="Partager sur Facebook"><i class="pi pi-facebook"></i></a>
                  <a :href="getShareLink('whatsapp', post)" target="_blank" title="Partager sur WhatsApp"><i class="pi pi-whatsapp"></i></a>
                </div>
              </div>
            </article>
          </div>

          <div v-else class="empty-state">
            <i class="pi pi-info-circle"></i>
            <p>Aucun résultat pour cette recherche.</p>
          </div>
        </div>
      </section>

      <aside class="sidebar-column">
        <SidebarOfficial :recentDocs="recentPdfs" />
      </aside>

    </div>
  </div>
</template>

<style scoped>
.portfolio-container {
  background: #f4f7f6;
  min-height: 100vh;
  padding-bottom: 50px;
}
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

/* LAYOUT */
.main-layout {
    display: flex;
     gap: 40px;
      margin-top: 30px;
    align-items: flex-start;
}

    .feed-column{
        flex: 1  auto;
        min-width: 0;
    }

/* HEADER & SEARCH */
.header-section { margin-bottom: 40px; }
.page-title { font-size: 2.2rem; font-weight: 800; color: #1a2421; margin-bottom: 25px; }
.search-hero {
   background: transparent;
  padding: 0px;
  border-radius: 20px;
  box-shadow:none ;
  max-width: 900px;
  margin: 0 auto 3rem;}

/* En-tête de section */
.header-section { margin-bottom: 30px; }
h1 { font-size: 2rem; font-weight: 800; color: #1a1c1e; margin-bottom: 20px; }

.search-bar {
  width: 100%;
  padding: 14px 20px;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  font-size: 1rem;
  box-shadow: 0 2px 5px rgba(0,0,0,0.02);
  transition: border-color 0.3s;
}
.search-bar:focus { outline: none; border-color: #ce1126; }

/* ====== FIX BARRE DE RECHERCHE PRIMEVUE ====== */
.search-wrapper {
  position: relative;
  width: 100%;
}

.search-wrapper .p-inputtext {
  width: 100%;
  height: 52px;
  padding-left: 42px;   /* espace pour l’icône */
  padding-right: 16px;
  border-radius: 30px;
  border: 1px solid #e5e7eb;
  font-size: 1rem;
  background: #ffffff;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
  display: block;
}

/* Icône recherche */
.search-wrapper .pi-search {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #6b7280;
  z-index: 2;
}

/* Focus propre */
.search-wrapper .p-inputtext:focus {
  outline: none;
  border-color: #14b82c;
  box-shadow: 0 0 0 3px rgba(20, 184, 44, 0.15);
}

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
.news-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.08); }

.card-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.date-text { font-size: 0.85rem; color: #718096; font-weight: 500; }

.card-title { font-size: 1.7rem; font-weight: 900; color: #1a202c; line-height: 1.3; margin-bottom: 20px; }

.card-media { border-radius: 15px; overflow: hidden; margin-bottom: 25px; background: #f8fafc; border: 1px solid #f1f5f9; }
.featured-img { width: 100%; max-height: 480px; object-fit: cover; display: block; }

.pdf-strip {
  padding: 40px; text-align: center; color: #2d3748; font-weight: 700;
  display: flex; flex-direction: column; gap: 10px; background: #ebf8ff;
}
.pdf-strip i { font-size: 2.5rem; color: #e53e3e; }

.card-excerpt { font-size: 1.05rem; color: #4a5568; line-height: 1.8; margin-bottom: 30px; }

.card-footer {
  display: flex; justify-content: space-between; align-items: center;
  padding-top: 20px; border-top: 1px solid #f1f5f9;
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
/* DESKTOP */
.sidebar-column {
  position: sticky;
  top: 10px;
  align-self: start;
}
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

/* Mobile Adjustments */
@media (max-width: 640px) {
    .main-layout{
        flex-direction: column;
        display: flex;
    }

    .sidebar-column {
    position: static;
    bottom: 0;
    left: 0;
    right: 0;

    top: auto;
    width: 100%;
    max-width: 100%;
    z-index: 999;

    padding: 0.8rem;
    background: transparent;
  }
  .post-item { padding: 20px; margin-bottom: 20px; }
  h2 { font-size: 1.3rem; }
  .footer-actions { flex-direction: column; gap: 15px; align-items: flex-start; }
  .socials { width: 100%; justify-content: flex-end; }
}


</style>
