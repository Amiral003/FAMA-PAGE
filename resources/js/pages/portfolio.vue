<script setup>
import { useHead } from '@vueuse/head'
import { useRouter } from 'vue-router'
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import SidebarOfficial from '@/components/SidebarOfficial.vue'

// Import PrimeVue
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'
import Tag from 'primevue/tag'

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
.main-layout { display: grid; grid-template-columns: 1fr 350px; gap: 40px; margin-top: 30px; }

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


.search-large:focus {
outline: none;
  box-shadow: none; }
  /* wrapper PrimeVue */
.p-input-icon-left {
  position: relative;
  width: 100%;
}


.p-input-icon-left > i {
    position: absolute;
   left: 22px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1.2rem;
  color: #6b7280;
  z-index: 2;
}

/* NEWS CARD */
.news-card {
  background: white;
  border-radius: 20px;
  padding: 30px;
  margin-bottom: 35px;
  border: 1px solid #edf2f7;
  transition: all 0.3s ease;
  cursor: pointer;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
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

.action-btns { display: flex; gap: 15px; align-items: center; }

.social-share { display: flex; gap: 15px; }
.social-share a {
  color: #a0aec0; font-size: 1.3rem; transition: color 0.2s;
}
.social-share a:hover { color: #14B82C; }

/* UTILS */
.skeleton-card { background: white; padding: 30px; border-radius: 20px; margin-bottom: 30px; }
.empty-state { text-align: center; padding: 100px 0; color: #a0aec0; }
.empty-state i { font-size: 4rem; margin-bottom: 20px; }

.sidebar-column
  {
    position: sticky;
    top: 10px;
    grid-template-columns: 1fr 340px;
    gap: 20px;
    align-self: start;
}


/* RESPONSIVE */
@media (max-width: 992px) {
  .main-layout { grid-template-columns: 1fr; }
  .sidebar-column { display: none; }
  .page-title { margin: 0;
font-weight: 700;
background: transparent;
padding: 0;
  border-radius: 0;}
}

@media (max-width: 640px) {
  .news-card { padding: 20px; }
  .card-title { font-size: 1.3rem; }
  .card-footer { flex-direction: column; gap: 20px; align-items: flex-start; }
  .social-share { width: 100%; justify-content: flex-end; }
}

.title-wrapper {
  background: transparent;
  padding: 2.5rem;
  border-radius: 20px;
  margin-bottom: 1.5rem;
}
.search-input {
  width: 100%;
  height: 64px;
  padding-left: 64px;
  padding-right: 20px;
  font-size: 1.1rem;
  border-radius: 18px;
 border: none !important;     /* supprime cadre */
  outline: none !important;
  box-shadow: none !important;
  background: #f9fafb;
}

</style>
