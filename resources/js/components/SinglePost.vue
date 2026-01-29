<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import SidebarOfficial from '@/components/SidebarOfficial.vue'

const route = useRoute()
const router = useRouter()
const post = ref(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await axios.get(`/api/posts/${route.params.slug}`)
    post.value = res.data.data || res.data
  } catch (e) {
    console.error("Post introuvable", e)
    router.push('/portfolio')
  } finally {
    loading.value = false
  }
})

// Logique de partage
const shareUrl = window.location.href
const shareTitle = computed(() => post.value ? `FAMa : ${post.value.title}` : '')

const share = (platform) => {
  const links = {
    facebook: 'https://www.facebook.com/',
    whastapp:'https://web.whatsapp.com/',
    twitter: 'https://x.com',
   // facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`,
   // twitter: `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareTitle.value)}&url=${encodeURIComponent(shareUrl)}`,
    //whatsapp: `https://api.whatsapp.com/send?text=${encodeURIComponent(shareTitle.value + ' ' + shareUrl)}`
  }
  window.open(links[platform], '_blank')
}
</script>

<template>
  <div class="page-background">
    <main class="main-layout container" v-if="!loading && post">

      <div class="content-card">
        <button @click="router.back()" class="btn-back">← Retour aux communiqués</button>

        <header class="post-header">
          <div class="post-meta">
            <span class="type-badge" v-if="post.type">{{ post.type }}</span>
            <span class="date">📅 {{ new Date(post.published_at || post.created_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) }}</span>
          </div>
          <h1>{{ post.title }}</h1>

          <div class="author-info" v-if="post.user">
            <div class="author-avatar">{{ post.user.name.charAt(0) }}</div>
            <span>Publié par <strong>{{ post.user.name }}</strong> (FAMa)</span>
          </div>
        </header>

        <div class="share-bar">
          <span>Partager ce communiqué :</span>
          <button @click="share('facebook')" class="s-btn fb" title="Facebook">F</button>
          <button @click="share('twitter')" class="s-btn x" title="Twitter/X">X</button>
          <button @click="share('whatsapp')" class="s-btn wa" title="WhatsApp">W</button>
        </div>

        <section class="post-main-image" v-if="post.thumbnail">
          <img
            :src="`/storage/${post.thumbnail}`"
            class="main-img"
            :alt="post.title"
          />
        </section>

        <section class="post-gallery" v-if="post.media && post.media.length">
          <div v-for="(item, index) in post.media" :key="index" class="gallery-item">
            <img
              :src="`/storage/${item.file_path}`"
              :alt="post.title"
              class="gallery-img"
            />
          </div>
        </section>

        <section class="post-content">
          <div class="text-body">
            {{ post.content }}
          </div>

          <div v-if="post.pdf_path" class="pdf-section">
            <div class="pdf-card">
              <div class="pdf-icon">📄</div>
              <div class="pdf-details">
                <h3>Document Officiel (PDF)</h3>
                <p>Consulter ou enregistrer le document original</p>
              </div>
              <div class="pdf-actions">
                <a :href="`/storage/${post.pdf_path}`" target="_blank" class="btn-view">
                  Voir le PDF
                </a>
                <a :href="`/storage/${post.pdf_path}`" download class="btn-download">
                  Enregistrer
                </a>
              </div>
            </div>
          </div>
        </section>
      </div>

      <aside class="sidebar-aside">
        <SidebarOfficial />
      </aside>

    </main>

    <div v-else-if="loading" class="loader-container">
      <div class="spinner"></div>
      <p>Récupération du communiqué officiel...</p>
    </div>
  </div>
</template>

<style scoped>
.page-background { background: #f3f4f6; min-height: 100vh; padding: 30px 0; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }

/* LAYOUT */
.main-layout { display: grid; grid-template-columns: 1fr 340px; gap: 30px; align-items: flex-start; }

@media (max-width: 992px) {
  .main-layout { grid-template-columns: 1fr; }
  .sidebar-aside { display: none; }
}

/* CARTE DE CONTENU */
.content-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }

.btn-back { background: none; border: none; color: #ce1126; cursor: pointer; font-weight: 700; margin-bottom: 25px; padding: 0; }

.post-header h1 { font-size: 2.2rem; line-height: 1.2; margin: 15px 0; color: #1a1c1e; }
.post-meta { display: flex; gap: 15px; align-items: center; }
.type-badge { background: #ce1126; color: white; padding: 4px 12px; border-radius: 4px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; }
.date { color: #6b7280; font-size: 0.9rem; }

/* AUTEUR */
.author-info { display: flex; align-items: center; gap: 12px; margin-top: 20px; padding: 15px 0; border-top: 1px solid #f3f4f6; font-size: 0.95rem; }
.author-avatar { width: 35px; height: 35px; background: #1a1c1e; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }

/* PARTAGE */
.share-bar { display: flex; align-items: center; gap: 12px; margin: 25px 0; padding: 15px; background: #f9fafb; border-radius: 8px; font-size: 0.9rem; font-weight: bold; }
.s-btn { width: 32px; height: 32px; border-radius: 50%; border: none; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; font-weight: bold; transition: opacity 0.2s; }
.s-btn:hover { opacity: 0.8; }
.fb { background: #1877f2; }
.x { background: #000; }
.wa { background: #25d366; }

/* IMAGES */
.post-main-image { margin: 30px 0; }
.main-img { width: 100%; max-height: 600px; object-fit: contain; border-radius: 10px; background: #f3f4f6; }

.post-gallery { margin: 20px 0; }
.gallery-img { width: 100%; border-radius: 10px; object-fit: cover; margin-bottom: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

/* TEXTE */
.post-content { font-size: 1.15rem; line-height: 1.8; color: #374151; }
.text-body { white-space: pre-wrap; margin-bottom: 40px; }

/* SECTION PDF */
.pdf-section { background: #1a1c1e; color: white; padding: 25px; border-radius: 10px; border-left: 5px solid #ce1126; }
.pdf-card { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
.pdf-icon { font-size: 2.5rem; }
.pdf-details h3 { margin: 0; font-size: 1.1rem; }
.pdf-details p { margin: 5px 0 0; font-size: 0.85rem; opacity: 0.8; }

.pdf-actions { display: flex; gap: 10px; margin-left: auto; }
.btn-view { border: 1px solid white; color: white; text-decoration: none; padding: 10px 18px; border-radius: 6px; font-size: 0.9rem; font-weight: bold; transition: 0.3s; }
.btn-view:hover { background: white; color: #1a1c1e; }
.btn-download { background: #ce1126; color: white; text-decoration: none; padding: 10px 18px; border-radius: 6px; font-size: 0.9rem; font-weight: bold; }

/* LOADER */
.loader-container { text-align: center; padding: 100px 0; color: #6b7280; }
.spinner { width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #ce1126; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

@media (max-width: 640px) {
  .content-card { padding: 20px; }
  .post-header h1 { font-size: 1.6rem; }
  .pdf-actions { width: 100%; flex-direction: column; margin-top: 10px; }
  .btn-view, .btn-download { text-align: center; }
}
</style>
