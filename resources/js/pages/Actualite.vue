<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { formatDistanceToNow } from 'date-fns'
import { fr } from 'date-fns/locale'

import SidebarOfficial from '@/components/SidebarOfficial.vue'
import InputText from 'primevue/inputtext'
import Tag from 'primevue/tag'

const posts = ref([])
const loading = ref(true)
const search = ref('')
const router = useRouter()

onMounted(async () => {
  try {
    const res = await axios.get('/api/posts')
    const allPosts = res.data.data || res.data

    // FILTRE : On prend tout SAUF les flashs
    posts.value = allPosts.filter(p => p.type?.toLowerCase() !== 'flash')

  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})

const filteredPosts = computed(() => {
  const q = search.value.toLowerCase().trim()
  return posts.value.filter(p => p.title?.toLowerCase().includes(q))
})

const getRelativeDate = (date) => date ? formatDistanceToNow(new Date(date), { addSuffix: true, locale: fr }) : ''
</script>

<template>
  <main class="page-beige">
    <div class="container main-layout">
      <section class="feed-column">
        <header class="header-section">
          <h1 class="page-title">Communiqués de Presse</h1>
          <div class="search-wrapper">
            <i class="pi pi-search"></i>
            <InputText v-model="search" placeholder="Rechercher un communiqué..." class="search-input" />
          </div>
        </header>

        <div v-if="loading" class="text-center p-5">Chargement...</div>

        <div v-else class="news-grid">
          <article v-for="post in filteredPosts" :key="post.id" class="news-card" @click="router.push(`/posts/${post.slug}`)">
             <div class="card-meta">
               <Tag value="COMMUNIQUÉ" severity="success" />
               <span class="date-text">{{ getRelativeDate(post.created_at) }}</span>
             </div>
             <h2 class="card-title">{{ post.title }}</h2>
             <p class="card-excerpt">{{ post.content?.substring(0, 150) }}...</p>
             <div class="card-footer">
                <span class="read-more">Lire le communiqué <i class="pi pi-arrow-right"></i></span>
             </div>
          </article>
        </div>
      </section>

      <aside class="sidebar-column">
        <SidebarOfficial :recentDocs="posts.slice(0,3)" />
      </aside>
    </div>
  </main>
</template>

<style scoped>
.page-beige { background: #F5F2ED; min-height: 100vh; padding: 40px 0; font-family: 'Playfair Display', serif; }
.container { max-width: 1200px; margin: 0 auto; display: flex; gap: 40px; }
.feed-column { flex: 1; }
.sidebar-column { width: 320px; }
.page-title { color: #35322F; border-left: 5px solid #A67C52; padding-left: 15px; margin-bottom: 30px; }
.news-card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #E6E1D8; margin-bottom: 20px; cursor: pointer; transition: 0.3s; }
.news-card:hover { transform: translateY(-5px); border-color: #A67C52; box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
.card-title { color: #35322F; font-size: 1.4rem; margin: 15px 0; }
.read-more { color: #A67C52; font-weight: 700; }
</style>
