<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const post = ref(null)
const loading = ref(true)

onMounted(async () => {
  try {
    // On récupère le post via son slug
    const res = await axios.get(`/api/posts/${route.params.slug}`)
    post.value = res.data
  } catch (e) {
    console.error("Post introuvable", e)
    // Redirection si le post n'existe pas
    // router.push('/404')
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <main class="single-post-container" v-if="!loading && post">
    <button @click="router.back()" class="btn-back">← Retour</button>

    <header class="post-header">
      <div class="post-meta">
        <span class="badge">{{ post.type }}</span>
        <span class="date">Publié le {{ new Date(post.published_at).toLocaleDateString() }}</span>
      </div>
      <h1>{{ post.title }}</h1>
      
      <div class="author-info" v-if="post.user">
        <div class="author-avatar">{{ post.user.name.charAt(0) }}</div>
        <span>Par <strong>{{ post.user.name }}</strong></span>
      </div>
    </header>

    <section class="post-gallery" v-if="post.media && post.media.length">
      <div v-for="(item, index) in post.media" :key="index" class="gallery-item">
        <img 
          :src="`/storage/${item.file_path}`" 
          :alt="`Image ${index + 1} - ${post.title}`"
          class="gallery-img"
        />
      </div>
    </section>

    <section class="post-content">
      <div class="text-content">
        {{ post.content }}
      </div>
      
      <div v-if="post.type === 'recrutement' && post.pdf_path" class="download-section">
        <a :href="`/storage/${post.pdf_path}`" target="_blank" class="btn-pdf">
          📄 Télécharger le document officiel (PDF)
        </a>
      </div>
    </section>
  </main>

  <div v-else-if="loading" class="loader">
    Chargement du communiqué...
  </div>
</template>

<style scoped>
.single-post-container {
  max-width: 900px;
  margin: 40px auto;
  padding: 0 20px;
}

.btn-back {
  background: none;
  border: none;
  color: #0d6efd;
  cursor: pointer;
  font-weight: 600;
  margin-bottom: 20px;
}

.post-header h1 {
  font-size: 2.5rem;
  margin: 15px 0;
  color: #1a1a1a;
}

.post-meta {
  display: flex;
  gap: 15px;
  align-items: center;
  color: #666;
}

.badge {
  background: #e9ecef;
  padding: 4px 12px;
  border-radius: 20px;
  text-transform: uppercase;
  font-size: 0.8rem;
  font-weight: bold;
}

.author-info {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 20px;
  padding: 10px 0;
  border-top: 1px solid #eee;
}

.author-avatar {
  width: 35px;
  height: 35px;
  background: #0d6efd;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

/* Style de la Galerie */
.post-gallery {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin: 30px 0;
}

.gallery-img {
  width: 100%;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.post-content {
  font-size: 1.2rem;
  line-height: 1.8;
  color: #333;
  white-space: pre-line; /* Garde les retours à la ligne du textarea */
}

.download-section {
  margin-top: 40px;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 8px;
  text-align: center;
}

.btn-pdf {
  display: inline-block;
  padding: 12px 24px;
  background: #dc3545;
  color: white;
  text-decoration: none;
  border-radius: 6px;
  font-weight: bold;
}
</style>