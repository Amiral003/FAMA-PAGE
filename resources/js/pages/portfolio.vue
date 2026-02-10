<script setup>
import { useHead } from '@vueuse/head'
import { useRouter } from 'vue-router'
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import SidebarOfficial from '@/components/SidebarOfficial.vue'
import Skeleton from 'primevue/skeleton'
import Tag from 'primevue/tag'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'

// SEO OPTIMISÉ POUR LA PAGE ARCHIVES
useHead({
  title: 'Avis & Communiqués | FAMa - Portail Officiel',
  meta: [
    { 
      name: 'description', 
      content: 'Consultez les archives officielles des FAMa : communiqués de presse, avis de recrutement et rapports de situation de l’État-Major Général des Armées du Mali.' 
    },
    { property: 'og:title', content: 'Communiqués & Avis Officiels - FAMa' },
    { property: 'og:description', content: 'Accédez aux informations vérifiées et aux documents officiels des Forces Armées Maliennes.' },
    { property: 'og:type', content: 'website' },
    { name: 'twitter:card', content: 'summary' }
  ],
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
                  <h1 class="page-title">Communiqués & Avis Officiels</h1>
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
                  :value="post.type === 'pdf' ? 'DOCUMENT PDF' : 'COMMUNIQUÉ'"
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
                    label="Lire la suite"
                    icon="pi pi-arrow-right"
                    iconPos="right"
                    text
                    class="p-button-success"
                    @click="router.push(`/posts/${post.slug}`)"
                  />
                  <Button
                    v-if="post.pdf_path"
                    icon="pi pi-download"
                    label="PDF"
                    severity="secondary"
                    outlined
                    size="small"
                    @click="downloadPDF(post.pdf_path)"
                  />
                </div>
                <div class="social-share">
                  <a :href="getShareLink('facebook', post)" target="_blank" class="s-fb"><i class="pi pi-facebook"></i></a>
                  <a :href="getShareLink('whatsapp', post)" target="_blank" class="s-wa"><i class="pi pi-whatsapp"></i></a>
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

.main-layout { display: grid; grid-template-columns: 1fr 350px; gap: 40px; padding-top: 30px; }

.page-title { font-size: 2.2rem; font-weight: 800; color: #1a2421; margin-bottom: 25px; }

.search-hero { margin-bottom: 3rem; }
.search-wrapper { position: relative; width: 100%; }
.search-input { 
    padding: 1rem 1rem 1rem 3rem !important; 
    border-radius: 12px !important; 
    border: 1px solid #d1d5db !important;
}

.news-card {
  background: white;
  padding: 30px;
  border-radius: 16px;
  margin-bottom: 30px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  cursor: pointer;
  transition: all 0.3s ease;
  border: 1px solid rgba(0,0,0,0.03);
}
.news-card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(0,0,0,0.1); }

.card-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.date-text { font-size: 0.85rem; color: #718096; font-weight: 500; }

.card-title { font-size: 1.6rem; font-weight: 800; color: #1a202c; line-height: 1.3; margin-bottom: 20px; }

.card-media { border-radius: 12px; overflow: hidden; margin-bottom: 25px; background: #f8fafc; border: 1px solid #f1f5f9; }
.featured-img { width: 100%; max-height: 400px; object-fit: cover; display: block; }

.pdf-strip {
  padding: 30px; text-align: center; color: #2d3748; font-weight: 700;
  display: flex; flex-direction: column; gap: 10px; background: #fef2f2;
}
.pdf-strip i { font-size: 2rem; color: #dc2626; }

.card-excerpt { font-size: 1rem; color: #4a5568; line-height: 1.7; margin-bottom: 25px; }

.card-footer {
  display: flex; justify-content: space-between; align-items: center;
  padding-top: 20px; border-top: 1px solid #f1f5f9;
}

.action-btns { display: flex; gap: 10px; }

.social-share { display: flex; gap: 12px; }
.social-share a {
  width: 36px; height: 36px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: white; transition: opacity 0.2s;
}
.s-fb { background: #1877f2; }
.s-wa { background: #25d366; }

.skeleton-card { background: white; padding: 30px; border-radius: 16px; margin-bottom: 30px; }

.empty-state { text-align: center; padding: 100px 0; color: #64748b; }
.empty-state i { font-size: 3rem; margin-bottom: 1rem; }
.sidebar-column { position: sticky; top: 20px; height: fit-content; }

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
@media (max-width: 992px) {
  .main-layout { grid-template-columns: 1fr; }
  .sidebar-column { display: none; position:sticky }
}
</style>