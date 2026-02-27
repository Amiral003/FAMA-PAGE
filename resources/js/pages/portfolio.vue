<script setup>
import { useHead } from '@vueuse/head'
import { useRouter } from 'vue-router'
import { ref, onMounted, onUnmounted, computed } from 'vue'
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
    { name: 'twitter:title', content: 'Avis & Communiqués | FAMa' }
  ],
})

const posts = ref([])
const search = ref('')
const loading = ref(true)
const loadingMore = ref(false)
const page = ref(1)
const lastPage = ref(false)
const router = useRouter()

// --- CHARGEMENT INITIAL & PAGINATION AVEC FILTRE ANTI-FLASH ---
const loadPosts = async (isFirstLoad = false) => {
  if ((!isFirstLoad && loadingMore.value) || lastPage.value) return;

  if (isFirstLoad) loading.value = true;
  else loadingMore.value = true;

  try {
    const res = await axios.get(`/api/posts?page=${page.value}`)
    const fetchedData = res.data.data || res.data;

    if (fetchedData && fetchedData.length > 0) {
      // 💡 INTÉGRATION DU FILTRE : On exclut les types 'flash'
      const filtered = fetchedData.filter(p => p.type?.toLowerCase() !== 'flash');

      posts.value = [...posts.value, ...filtered];
      page.value++;
    }

    // Vérification de la fin de pagination
    if (res.data.current_page >= res.data.last_page) {
      lastPage.value = true;
    }
  } catch (e) {
    console.error("Erreur API:", e)
  } finally {
    loading.value = false;
    loadingMore.value = false;
  }
}

// --- LOGIQUE DU SCROLL ---
const handleScroll = () => {
  const scrollHeight = document.documentElement.scrollHeight
  const scrollTop = document.documentElement.scrollTop
  const clientHeight = document.documentElement.clientHeight

  if (scrollTop + clientHeight >= scrollHeight - 200) {
    loadPosts();
  }
}

onMounted(() => {
  loadPosts(true)
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})

// --- FONCTIONS UTILITAIRES ---
const getRelativeDate = (date) => {
  if (!date) return ''
  try {
    return formatDistanceToNow(new Date(date), { addSuffix: true, locale: fr })
  } catch (e) {
    return "Date inconnue"
  }
}

const getPostImage = (post) => {
  if (post.thumbnail) return `/storage/${post.thumbnail}`;
  if (post.media?.length > 0) return `/storage/${post.media[0].file_path}`;
  return null;
}

const downloadPDF = (path) => {
  if (!path) return;
  const link = document.createElement('a');
  link.href = `/storage/${path}`;
  link.setAttribute('download', 'communique-fama.pdf');
  document.body.appendChild(link);
  link.click();
  link.remove();
};

const getShareLink = (platform, post) => {
  const shareUrl = encodeURIComponent(`${baseUrl}/posts/${post.slug}`)
  const shareTitle = encodeURIComponent(`FAMa : ${post.title}`)
  return platform === 'facebook'
    ? `https://www.facebook.com/sharer/sharer.php?u=${shareUrl}`
    : `https://api.whatsapp.com/send?text=${shareTitle}%20${shareUrl}`
}

const filteredPosts = computed(() => {
  const q = search.value.toLowerCase().trim();
  if (!q) return posts.value;
  return posts.value.filter(post =>
    post.title?.toLowerCase().includes(q) || post.content?.toLowerCase().includes(q)
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
            <InputText
              v-model="search"
              placeholder="Rechercher un communiqué ou un mot-clé..."
              class="search-input"
            />
          </div>
        </header>

        <div v-if="loading" class="loading-grid">
          <div v-for="i in 3" :key="i" class="news-card skeleton">
            <Skeleton width="40%" height="1.2rem" class="mb-4"></Skeleton>
            <Skeleton width="100%" height="200px" class="mb-4"></Skeleton>
            <Skeleton width="90%" class="mb-2"></Skeleton>
            <Skeleton width="70%"></Skeleton>
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
                <Tag :value="post.type ? post.type.toUpperCase() : (post.pdf_path ? 'PDF OFFICIEL' : 'INFO')"
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
                  <span>DOCUMENT PDF DISPONIBLE</span>
                </div>
              </div>

              <p class="card-excerpt">{{ post.content?.substring(0, 180) }}...</p>

              <div class="card-footer" @click.stop>
                <div class="action-btns">
                  <Button label="Consulter" icon="pi pi-chevron-right" iconPos="right" text @click="router.push(`/posts/${post.slug}`)" />
                  <Button v-if="post.pdf_path" icon="pi pi-download" severity="secondary" outlined size="small" @click="downloadPDF(post.pdf_path)" />
                </div>

                <div class="share-wrapper">
                  <Button icon="pi pi-share-alt" class="share-trigger-btn" rounded severity="secondary" size="small" />
                  <div class="share-floating-menu">
                    <a :href="getShareLink('facebook', post)" target="_blank" class="s-btn fb"><i class="pi pi-facebook"></i></a>
                    <a :href="getShareLink('whatsapp', post)" target="_blank" class="s-btn wa"><i class="pi pi-whatsapp"></i></a>
                  </div>
                </div>
              </div>
            </article>
          </TransitionGroup>

          <div v-else class="empty-state">
            <i class="pi pi-search-minus"></i>
            <p>Aucun communiqué trouvé pour "{{ search }}".</p>
          </div>

          <div v-if="loadingMore" class="text-center p-4">
             <i class="pi pi-spin pi-spinner" style="font-size: 2rem; color: #14b82c"></i>
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
/* Tes styles restent inchangés */
.share-wrapper { position: relative; display: flex; align-items: center; }
.share-floating-menu {
  position: absolute; top: 50%; right: 45px; transform: translateY(-50%) translateX(10px);
  display: flex; gap: 8px; opacity: 0; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); pointer-events: none;
}
.share-wrapper:hover .share-floating-menu { opacity: 1; transform: translateY(-50%) translateX(0); pointer-events: auto; }
.s-btn { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; font-size: 0.9rem; transition: 0.2s; }
.fb { background: #1877f2; }
.wa { background: #22c55e; }
.s-btn:hover { transform: scale(1.15); filter: brightness(1.1); }
.portfolio-container { background: #f8fafc; min-height: 100vh; padding: 40px 0; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.main-layout { display: flex; gap: 40px; align-items: flex-start; }
.feed-column { flex: 1; min-width: 0; }
.sidebar-column { width: 320px; position: sticky; top: 100px; }
.header-section { margin-bottom: 40px; }
.page-title { font-size: 2.2rem; font-weight: 900; color: #1e293b; margin-bottom: 25px; }
.search-wrapper { position: relative; max-width: 600px; }
.search-wrapper i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; z-index: 1; }
.search-input { width: 100%; padding: 12px 12px 12px 45px !important; border-radius: 12px !important; border: 1px solid #e2e8f0 !important; }
.news-card { background: white; border-radius: 16px; padding: 25px; margin-bottom: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: all 0.3s ease; border: 1px solid #f1f5f9; cursor: pointer; }
.news-card:hover { transform: translateY(-4px); box-shadow: 0 12px 20px rgba(0,0,0,0.1); border-color: #14b82c; }
.card-meta { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 0.85rem; }
.date-text { color: #64748b; font-weight: 600; }
.card-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 15px; line-height: 1.3; }
.card-media { border-radius: 12px; overflow: hidden; margin-bottom: 20px; border: 1px solid #f1f5f9; }
.featured-img { width: 100%; height: 350px; object-fit: cover; }
.pdf-strip { padding: 40px; background: #fef2f2; color: #991b1b; text-align: center; display: flex; flex-direction: column; gap: 10px; font-weight: 700; }
.pdf-strip i { font-size: 2rem; }
.card-excerpt { color: #475569; line-height: 1.7; margin-bottom: 25px; }
.card-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 20px; border-top: 1px solid #f1f5f9; }
.action-btns { display: flex; gap: 10px; }
@media (max-width: 992px) { .main-layout { flex-direction: column; } .sidebar-column { display: none; } }
</style>
