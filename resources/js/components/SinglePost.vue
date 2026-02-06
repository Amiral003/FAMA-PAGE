<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import SidebarOfficial from '@/components/SidebarOfficial.vue'

// Import PrimeVue 4
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Skeleton from 'primevue/skeleton'
import Image from 'primevue/image' // Ajout pour le zoom

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

const getShareLink = (platform) => {
  if (typeof window === 'undefined' || !post.value) return '#'
  const shareUrl = encodeURIComponent(window.location.href)
  const shareTitle = encodeURIComponent(`FAMa : ${post.value.title}`)
  const links = {
    facebook: `https://www.facebook.com/sharer/sharer.php?u=${shareUrl}`,
    whatsapp: `https://api.whatsapp.com/send?text=${shareTitle}%20${shareUrl}`
  }
  return links[platform]
}

const openPdf = (path) => {
  window.open(`/storage/${path}`, '_blank')
}
</script>

<template>
  <div class="page-background">
    <main class="main-layout container" v-if="!loading && post">

      <div class="content-card">
        <Button
          icon="pi pi-arrow-left"
          label="Retour aux communiqués"
          link
          class="p-0 mb-5 text-fama"
          @click="router.back()"
        />

        <header class="post-header">
          <div class="post-meta">
            <Tag
              :value="post.type || 'COMMUNIQUÉ'"
              :severity="post.type === 'pdf' ? 'danger' : 'success'"
            />
            <span class="date">
              <i class="pi pi-calendar mr-2"></i>
              {{ new Date(post.published_at || post.created_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) }}
            </span>
          </div>
          <h1 class="post-title">{{ post.title }}</h1>
        </header>

        <section class="post-main-image" v-if="post.thumbnail">
          <Image
            :src="`/storage/${post.thumbnail}`"
            alt="Image à la une"
            preview
            imageClass="main-img-fluid"
          />
        </section>

        <div class="share-bar">
          <span class="share-label">Partager :</span>
          <div class="share-buttons">
            <a :href="getShareLink('facebook')" target="_blank" class="s-btn fb"><i class="pi pi-facebook"></i></a>
            <a :href="getShareLink('whatsapp')" target="_blank" class="s-btn wa"><i class="pi pi-whatsapp"></i></a>
          </div>
        </div>

        <section class="post-body">
          <div class="text-content">
            {{ post.content }}
          </div>

          <div v-if="post.media && post.media.length" class="post-gallery">
            <div v-for="(item, index) in post.media" :key="index" class="gallery-item">
                <Image
                    :src="`/storage/${item.file_path}`"
                    preview
                    alt="Galerie FAMa"
                    imageClass="gallery-img-styled"
                />
            </div>
          </div>

          <div class="post-signature-minimal" v-if="post.user">
            <div class="signature-line"></div>
            <p class="author-name-bottom">{{ post.user.name }}</p>
            <p class="author-sub">Forces Armées Maliennes</p>
          </div>

          <div v-if="post.pdf_path" class="pdf-section">
            <div class="pdf-card">
              <div class="pdf-icon"><i class="pi pi-file-pdf"></i></div>
              <div class="pdf-info">
                <h3>Document source certifié</h3>
                <p>Consultez la version officielle signée au format PDF.</p>
              </div>
              <div class="pdf-actions">
                <Button label="Consulter" icon="pi pi-eye" outlined class="p-button-white" @click="openPdf(post.pdf_path)" />
                <a :href="`/storage/${post.pdf_path}`" download class="btn-download-fama">
                  <i class="pi pi-download mr-2"></i> Télécharger
                </a>
              </div>
            </div>
          </div>
        </section>
      </div>

      <aside class="sidebar-column">
        <SidebarOfficial />
      </aside>
    </main>

    <div v-else-if="loading" class="container main-layout mt-5">
      <div class="content-card">
        <Skeleton width="20%" height="2rem" class="mb-4"></Skeleton>
        <Skeleton width="100%" height="30rem" class="mb-4"></Skeleton>
        <Skeleton width="80%" class="mb-2"></Skeleton>
        <Skeleton width="60%"></Skeleton>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Fond et Container */
.page-background { background: #f1f5f9; min-height: 100vh; padding: 20px 0; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }

/* Layout Grid */
.main-layout { display: grid; grid-template-columns: 1fr 320px; gap: 30px; align-items: start; }

/* Content Card */
.content-card {
  background: white;
  padding: 40px;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.text-fama { color: #14B82C !important; font-weight: 700; }
.post-title { font-size: clamp(1.8rem, 5vw, 2.8rem); font-weight: 900; color: #1a202c; line-height: 1.2; margin-top: 15px; }
.post-meta { display: flex; gap: 15px; flex-wrap: wrap; align-items: center; color: #64748b; font-weight: 600; }

/* SHARE BAR */
.share-bar {
  display: flex; align-items: center; gap: 15px;
  margin: 30px 0; padding: 12px 20px;
  background: #f8fafc; border-radius: 50px; width: fit-content;
}

/* IMAGES OPTIMISÉES */
.post-main-image { margin: 30px -40px; } /* Sort un peu du padding sur PC */
:deep(.main-img-fluid) {
    width: 100%;
    max-height: 600px;
    object-fit: cover;
    display: block;
}

/* GALERIE RESPONSIVE */
.post-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
    margin-top: 40px;
}
.gallery-item { overflow: hidden; border-radius: 12px; height: 200px; }
:deep(.gallery-img-styled) {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}
:deep(.gallery-img-styled:hover) { transform: scale(1.05); }

/* TEXTE */
.text-content { font-size: 1.15rem; line-height: 1.8; color: #334155; white-space: pre-wrap; margin-bottom: 40px; }

/* SIGNATURE */
.post-signature-minimal { margin: 40px 0; text-align: right; }
.signature-line { width: 100px; height: 4px; background: #14B82C; margin-left: auto; margin-bottom: 10px; }
.author-name-bottom { font-size: 1.3rem; font-weight: 800; text-transform: uppercase; margin: 0; }

/* PDF SECTION */
.pdf-section { background: #0f172a; color: white; padding: 25px; border-radius: 16px; }
.pdf-card { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
.pdf-icon { font-size: 2.5rem; color: #ef4444; }
.pdf-actions { display: flex; gap: 10px; margin-left: auto; }

.sidebar-column
  {
    position: sticky;
    top: 10px;
    grid-template-columns: 1fr 340px;
    gap: 20px;
    align-self: start;
}
/* RESPONSIVE BREAKPOINTS */
@media (max-width: 1024px) {
    .main-layout { grid-template-columns: 1fr; }
    .sidebar-column { display: none; }
    .post-main-image { margin: 30px 0; }
}

@media (max-width: 768px) {
    .content-card { padding: 25px; }
    .post-main-image { margin: 20px -25px; }
    .pdf-actions { width: 100%; flex-direction: column; }
    .btn-download-fama { text-align: center; }
}
</style>
