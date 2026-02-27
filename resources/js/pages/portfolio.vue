<script setup>
import { useHead } from '@vueuse/head'
import { useRouter } from 'vue-router'
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

// --- IMPORT POUR LA DATE RELATIVE ---
import { formatDistanceToNow } from 'date-fns'
import { fr } from 'date-fns/locale'

// --- IMPORTS DES COMPOSANTS ---
import SidebarOfficial from '@/components/SidebarOfficial.vue'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Skeleton from 'primevue/skeleton'

// --- CONFIGURATION SEO ---
const baseUrl = typeof window !== 'undefined' ? window.location.origin : ''

useHead({
  title: 'Avis & Communiqués | FAMa',
  meta: [
    { name: 'description', content: 'Fil d’actualité officiel des Forces Armées Maliennes (FAMa).' },
    { property: 'og:type', content: 'website' },
    { property: 'og:title', content: 'Avis & Communiqués Officiels | FAMa' },
    { property: 'og:description', content: 'Accédez aux derniers communiqués de presse et documents certifiés des FAMa.' },
    { property: 'og:image', content: `${baseUrl}/assets/images/hero.jpg` },
    { name: 'twitter:card', content: 'summary_large_image' },
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

// --- NETTOYAGE DU HTML POUR L'EXTRAIT ---
// Cette fonction enlève les balises HTML pour ne garder que le texte pur dans la liste
const stripHtml = (html) => {
  if (!html) return '';
  const doc = new DOMParser().parseFromString(html, 'text/html');
  return doc.body.textContent || "";
}

const getRelativeDate = (date) => {
  if (!date) return ''
  try {
    return formatDistanceToNow(new Date(date), { addSuffix: true, locale: fr })
  } catch (e) { return "Date inconnue" }
}

const getPostImage = (post) => {
  if (post.thumbnail) return `/storage/${post.thumbnail}`;
  if (post.media?.length > 0) return `/storage/${post.media[0].file_path}`;
  return null;
}

const downloadPDF = (path) => {
  if (!path) return;
  window.open(`/storage/${path}`, '_blank');
};

const getShareLink = (platform, post) => {
  const shareUrl = encodeURIComponent(`${window.location.origin}/posts/${post.slug}`)
  const shareTitle = encodeURIComponent(`FAMa : ${post.title}`)
  return platform === 'facebook'
    ? `https://www.facebook.com/sharer/sharer.php?u=${shareUrl}`
    : `https://api.whatsapp.com/send?text=${shareTitle}%20${shareUrl}`
}

const filteredPosts = computed(() => {
  const q = search.value.toLowerCase().trim();
  if (!q) return posts.value;
  return posts.value.filter(post =>
    post.title?.toLowerCase().includes(q) || stripHtml(post.content).toLowerCase().includes(q)
  );
})

const recentPdfs = computed(() => posts.value.filter(p => p.pdf_path).slice(0, 3))
</script>

<template>
  <div class="portfolio-container">
    <div class="container main-layout">

      <section class="feed-column">
        <header class="header-section">
          <h1 class="page-title">Communiqués & Avis Officiels</h1>
          <div class="search-wrapper">
            <i class="pi pi-search"></i>
            <InputText v-model="search" placeholder="Rechercher un communiqué..." class="search-input" />
          </div>
        </header>

        <div v-if="loading" class="loading-grid">
          <div v-for="i in 3" :key="i" class="news-card skeleton">
            <Skeleton width="40%" height="1.2rem" class="mb-4"></Skeleton>
            <Skeleton width="100%" height="250px" class="mb-4"></Skeleton>
            <Skeleton width="90%" class="mb-2"></Skeleton>
          </div>
        </div>

        <div v-else>
          <TransitionGroup name="list" tag="div" v-if="filteredPosts.length > 0">
            <article
              v-for="post in filteredPosts"
              :key="post.id"
              class="news-card"
              @click="router.push(`/posts/${post.slug}`)"
            >
              <div class="card-meta">
                <Tag :value="post.pdf_path ? 'DOCUMENT OFFICIEL' : 'COMMUNIQUÉ'"
                     :severity="post.pdf_path ? 'danger' : 'success'" />

                <span class="date-text">
                  <i class="pi pi-clock mr-1"></i>
                  {{ getRelativeDate(post.published_at || post.created_at) }}
                </span>
              </div>

              <h2 class="card-title">{{ post.title }}</h2>

              <div class="card-media" v-if="getPostImage(post) || post.pdf_path">
                <img v-if="getPostImage(post)" :src="getPostImage(post)" class="featured-img" loading="lazy" />
                <div v-else-if="post.pdf_path" class="pdf-strip">
                  <i class="pi pi-file-pdf"></i>
                  <span>COMMUNIQUÉ OFFICIEL EN PDF</span>
                </div>
              </div>

              <p class="card-excerpt">{{ stripHtml(post.content).substring(0, 180) }}...</p>

              <div class="card-footer" @click.stop>
                <div class="action-btns">
                  <Button label="Lire la suite" icon="pi pi-arrow-right" iconPos="right" text @click="router.push(`/posts/${post.slug}`)" class="read-more-btn" />
                  <Button v-if="post.pdf_path" icon="pi pi-download" severity="danger" text @click="downloadPDF(post.pdf_path)" label="PDF" />
                </div>

                <div class="share-wrapper">
                  <div class="share-floating-menu">
                    <a :href="getShareLink('facebook', post)" target="_blank" class="s-btn fb"><i class="pi pi-facebook"></i></a>
                    <a :href="getShareLink('whatsapp', post)" target="_blank" class="s-btn wa"><i class="pi pi-whatsapp"></i></a>
                  </div>
                  <Button icon="pi pi-share-alt" rounded severity="secondary" size="small" class="share-trigger-btn" />
                </div>
              </div>
            </article>
          </TransitionGroup>

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
.portfolio-container { background: #f0f2f5; min-height: 100vh; padding: 40px 0; }
.container { max-width: 1100px; margin: 0 auto; padding: 0 15px; }
.main-layout { display: grid; grid-template-columns: 1fr 320px; gap: 30px; }

.page-title { font-size: 1.8rem; font-weight: 800; color: #1c1e21; margin-bottom: 20px; }

.search-wrapper { margin-bottom: 30px; position: relative; }
.search-input { width: 100%; border-radius: 25px !important; padding-left: 45px !important; border: none !important; box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important; }
.search-wrapper i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); z-index: 2; color: #65676b; }

/* News Card Style Instagram/Facebook */
.news-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 25px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  border: 1px solid #dddfe2;
  transition: background 0.2s;
  cursor: pointer;
}
.news-card:hover { background: #fcfcfc; }

.card-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.date-text { font-size: 0.85rem; color: #65676b; }

.card-title { font-size: 1.35rem; font-weight: 700; color: #050505; line-height: 1.4; margin-bottom: 12px; }

.card-media { margin: 0 -20px 15px -20px; border-top: 1px solid #f0f2f5; border-bottom: 1px solid #f0f2f5; }
.featured-img { width: 100%; height: auto; max-height: 450px; object-fit: cover; }

.pdf-strip { padding: 50px; background: #fff1f2; color: #be123c; display: flex; flex-direction: column; align-items: center; gap: 10px; font-weight: 700; }
.pdf-strip i { font-size: 2.5rem; }

.card-excerpt { color: #1c1e21; font-size: 0.95rem; line-height: 1.5; margin-bottom: 15px; }

.card-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 10px; }

/* Share logic */
.share-wrapper { position: relative; display: flex; align-items: center; }
.share-floating-menu {
  display: flex; gap: 8px; margin-right: 10px;
  opacity: 0; transform: translateX(10px);
  transition: 0.3s ease; pointer-events: none;
}
.share-wrapper:hover .share-floating-menu { opacity: 1; transform: translateX(0); pointer-events: auto; }
.s-btn { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; }
.fb { background: #1877f2; } .wa { background: #22c55e; }

.read-more-btn { font-weight: 700 !important; color: #14b82c !important; }

@media (max-width: 850px) {
  .main-layout { grid-template-columns: 1fr; }
  .sidebar-column { display: none; }
}
</style>