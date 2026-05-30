<script setup>
import { ref, computed, onMounted, watch, onBeforeUnmount } from 'vue'
import { useHead } from '@unhead/vue'
import axios from 'axios'

import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Skeleton from 'primevue/skeleton'

useHead({
  title: 'Recrutement & Concours | Forces Armées Maliennes',
  meta: [
    {
      name: 'description',
      content:
        'Consultez les avis de recrutement, concours d’entrée et communiqués officiels des Forces Armées Maliennes.',
    },
    { name: 'robots', content: 'index, follow' },
    { property: 'og:title', content: 'Recrutement & Concours | Forces Armées Maliennes' },
    {
      property: 'og:description',
      content:
        'Avis officiels de recrutement, concours d’entrée et documents PDF publiés par les Forces Armées Maliennes.',
    },
    { property: 'og:type', content: 'website' },
  ],
})

const posts = ref([])
const loading = ref(true)
const errorMsg = ref('')
const q = ref('')
const page = ref(1)
const perPage = 12

const meta = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
})

let searchTimer = null

const normalizeFileUrl = (path) => {
  if (!path) return null
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  if (path.startsWith('/storage/')) return path
  return `/storage/${path}`
}

const stripHtml = (value) => {
  if (!value) return ''
  return value.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim()
}

const fetchRecruitments = async () => {
  loading.value = true
  errorMsg.value = ''

  try {
    const res = await axios.get('/api/posts/recruitment', {
      params: {
        page: page.value,
        per_page: perPage,
        q: q.value || undefined,
      },
    })

    posts.value = res.data.data || []

    meta.value = {
      current_page: res.data.current_page || 1,
      last_page: res.data.last_page || 1,
      total: res.data.total || 0,
    }
  } catch (e) {
    errorMsg.value = 'Impossible de charger les avis de recrutement pour le moment.'
  } finally {
    loading.value = false
  }
}

const goPage = (value) => {
  if (value < 1 || value > meta.value.last_page) return

  page.value = value
  fetchRecruitments()

  window.scrollTo({
    top: 0,
    behavior: 'smooth',
  })
}

watch(q, () => {
  clearTimeout(searchTimer)

  searchTimer = setTimeout(() => {
    page.value = 1
    fetchRecruitments()
  }, 400)
})

const hasResults = computed(() => posts.value.length > 0)

const formatDate = (date) => {
  if (!date) return ''

  return new Date(date).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

onMounted(fetchRecruitments)

onBeforeUnmount(() => {
  clearTimeout(searchTimer)
})
</script>

<template>
  <main class="recruitment-page">
    <section class="recruitment-hero">
      <div class="hero-overlay"></div>

      <div class="hero-content">
        <Tag value="Documents officiels" severity="success" class="hero-tag" />
        <h1>Recrutement & Concours</h1>
        <p>
  Retrouvez ici les avis de recrutement, concours d’entrée dans les écoles militaires
  et communiqués officiels publiés par les Forces Armées Maliennes.
</p>

<div class="hero-actions">
  <a
    href="https://recrutement.mil.ml"
    target="_blank"
    rel="noopener noreferrer"
    class="official-recruitment-btn"
  >
    <i class="pi pi-external-link"></i>
    Accéder à la plateforme de recrutement
  </a>
</div>
      </div>

      <div class="hero-badge" aria-hidden="true">
        <i class="pi pi-file-edit"></i>
      </div>
    </section>

    <section class="toolbar">
      <span class="search-wrap">
        <i class="pi pi-search"></i>
        <InputText
          v-model="q"
          placeholder="Rechercher un avis, un concours, une école..."
          class="search-input"
        />
      </span>

      <div class="count" v-if="!loading">
        {{ meta.total }} document{{ meta.total > 1 ? 's' : '' }}
      </div>
    </section>

   
<!-- LOADING -->
<section v-if="loading" class="docs-list">
  <article v-for="i in 5" :key="i" class="doc-card skeleton-card">
    <div class="doc-preview skeleton-preview">
      <Skeleton width="150px" height="210px" borderRadius="12px" />
    </div>

    <div class="doc-body">
      <Skeleton width="120px" height="24px" />
      <Skeleton width="90%" height="26px" class="mt" />
      <Skeleton width="70%" height="18px" class="mt-sm" />
      <Skeleton width="100%" height="18px" class="mt" />
      <Skeleton width="95%" height="18px" class="mt-sm" />
      <div class="doc-actions mt">
        <Skeleton width="170px" height="42px" borderRadius="12px" />
        <Skeleton width="110px" height="42px" borderRadius="12px" />
      </div>
    </div>
  </article>
</section>

<!-- ERROR -->
<section v-else-if="errorMsg" class="empty-state error-state">
  <i class="pi pi-exclamation-triangle"></i>
  <h2>Chargement impossible</h2>
  <p>{{ errorMsg }}</p>
  <Button label="Réessayer" icon="pi pi-refresh" @click="fetchRecruitments" />
</section>

<!-- EMPTY -->
<section v-else-if="!hasResults" class="empty-state">
  <i class="pi pi-file-pdf"></i>
  <h2>Aucun document trouvé</h2>
  <p>Aucun avis de recrutement ou concours ne correspond à votre recherche.</p>
</section>

<!-- RESULTS -->
<section v-else class="docs-list">
  <article v-for="post in posts" :key="post.id" class="doc-card">
    <div class="doc-preview">
      <div class="doc-sheet">
        <img
          v-if="post.thumbnail"
          :src="normalizeFileUrl(post.thumbnail)"
          :alt="`Couverture du document : ${post.title}`"
          loading="lazy"
          decoding="async"
        />

        <div v-else class="pdf-fallback">
          <i class="pi pi-file-pdf"></i>
          <span>PDF</span>
        </div>
      </div>
    </div>

    <div class="doc-body">
      <div class="doc-meta">
        <Tag value="Recrutement" severity="warning" />
        <span>{{ formatDate(post.published_at || post.created_at) }}</span>
      </div>

      <h2>{{ post.title }}</h2>

      <p v-if="post.content" class="excerpt">
        {{ stripHtml(post.content).slice(0, 220) }}{{ stripHtml(post.content).length > 220 ? '...' : '' }}
      </p>

      <div class="doc-notice">
        <i class="pi pi-info-circle"></i>
        <span>Pour économiser la connexion, le PDF n’est chargé qu’après clic.</span>
      </div>

      <div class="doc-actions">
        <a
          v-if="post.pdf_path"
          :href="normalizeFileUrl(post.pdf_path)"
          class="pdf-btn"
          download
        >
          <i class="pi pi-download"></i>
          Télécharger le PDF
        </a>

        <router-link :to="`/posts/${post.slug}`" class="details-btn">
          Détails
          <i class="pi pi-arrow-right"></i>
        </router-link>
      </div>
    </div>
  </article>
</section>
  </main>
</template>

<style scoped>
.recruitment-page {
  width: 100%;
  max-width: 1240px;
  margin-inline: auto;
  padding: 34px 1rem 64px;
  color: #0f172a;
}

.recruitment-hero {
  position: relative;
  overflow: hidden;
  min-height: 250px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  border-radius: 26px;
  padding: clamp(24px, 4vw, 42px);
  margin-bottom: 26px;
  background:
    radial-gradient(circle at top right, rgba(255, 215, 0, 0.18), transparent 34%),
    linear-gradient(135deg, #172216 0%, #263b28 55%, #1a241b 100%);
  border: 1px solid rgba(255, 215, 0, 0.18);
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.16);
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
  background-size: 34px 34px;
  opacity: 0.45;
}

.hero-content {
  position: relative;
  z-index: 1;
  max-width: 820px;
}

.hero-tag {
  margin-bottom: 16px;
}

.recruitment-hero h1 {
  font-size: clamp(2rem, 4.5vw, 3.25rem);
  line-height: 1.08;
  margin: 0 0 14px;
  color: #ffd700;
  font-weight: 950;
  letter-spacing: -0.03em;
}

.recruitment-hero p {
  margin: 0;
  color: #e4ebe5;
  line-height: 1.75;
  max-width: 760px;
}

.hero-badge {
  position: relative;
  z-index: 1;
  width: 108px;
  height: 108px;
  flex: 0 0 auto;
  display: grid;
  place-items: center;
  border-radius: 999px;
  background: rgba(255, 215, 0, 0.08);
  border: 1px solid rgba(255, 215, 0, 0.22);
  color: #ffd700;
}

.hero-badge i {
  font-size: 2.8rem;
}

.hero-actions {
  margin-top: 24px;
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.official-recruitment-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  min-height: 46px;
  padding: 12px 18px;
  border-radius: 999px;
  background: #ffd700;
  color: #172216;
  font-weight: 950;
  text-decoration: none;
  border: 1px solid rgba(255, 215, 0, 0.35);
  box-shadow: 0 14px 28px rgba(0, 0, 0, 0.18);
}

.official-recruitment-btn:hover {
  background: #ffca28;
  color: #11170f;
  transform: translateY(-1px);
}

.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 14px;
  margin-bottom: 24px;
}

.search-wrap {
  position: relative;
  flex: 1;
  max-width: 620px;
}

.search-wrap i {
  position: absolute;
  left: 15px;
  top: 50%;
  z-index: 1;
  transform: translateY(-50%);
  color: #64748b;
}

.search-input {
  width: 100%;
  min-height: 48px;
  padding-left: 44px;
  border-radius: 16px;
  background: #ffffff;
  border: 1px solid #dbe3ee;
  color: #0f172a;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
}

.count {
  padding: 10px 14px;
  border-radius: 999px;
  background: #eef2f7;
  color: #475569;
  font-size: 0.9rem;
  font-weight: 900;
  white-space: nowrap;
}

.docs-list {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.doc-card {
  display: grid;
  grid-template-columns: minmax(280px, 360px) 1fr;
  gap: 22px;
  align-items: stretch;
  padding: 18px;
  border-radius: 24px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  box-shadow: 0 16px 34px rgba(15, 23, 42, 0.07);
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease,
    border-color 0.2s ease;
}

.doc-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 22px 42px rgba(15, 23, 42, 0.1);
  border-color: rgba(26, 36, 27, 0.22);
}

.doc-preview {
  display: block;
  padding: 0;
  border-radius: 20px;
  background: transparent;
  min-height: auto;
}

.doc-sheet {
  width: 100%;
  height: 390px;
  background: #ffffff;
  border-radius: 18px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow:
    0 12px 30px rgba(15, 23, 42, 0.12),
    0 0 0 1px rgba(15, 23, 42, 0.06);
}

.doc-sheet img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  background: #ffffff;
  display: block;
}

.pdf-fallback {
  width: 100%;
  height: 100%;
  display: grid;
  place-items: center;
  align-content: center;
  gap: 10px;
  color: #991b1b;
  font-weight: 900;
  text-transform: uppercase;
  background:
    linear-gradient(135deg, rgba(153, 27, 27, 0.06), rgba(255, 215, 0, 0.06));
}

.pdf-fallback i {
  font-size: 2.8rem;
}

.doc-body {
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-width: 0;
}

.doc-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  color: #64748b;
  font-size: 0.88rem;
  font-weight: 800;
  margin-bottom: 14px;
}

.doc-body h2 {
  margin: 0 0 12px;
  font-size: clamp(1.15rem, 2vw, 1.55rem);
  line-height: 1.4;
  color: #1e293b;
  font-weight: 900;
}

.excerpt {
  margin: 0 0 18px;
  color: #64748b;
  line-height: 1.7;
  font-size: 0.96rem;
  max-width: 900px;
}

.doc-notice {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  width: fit-content;
  margin: 0 0 16px;
  padding: 8px 12px;
  border-radius: 999px;
  background: #f8fafc;
  color: #64748b;
  font-size: 0.84rem;
  font-weight: 700;
}

.doc-notice i {
  color: #14b82c;
}

.doc-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-top: auto;
}

.pdf-btn,
.details-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  min-height: 44px;
  border-radius: 13px;
  padding: 10px 16px;
  font-weight: 900;
  text-decoration: none;
  font-size: 0.92rem;
  transition:
    transform 0.2s ease,
    opacity 0.2s ease,
    background-color 0.2s ease;
}

.pdf-btn:hover,
.details-btn:hover {
  transform: translateY(-1px);
}

.pdf-btn {
  background: #152015;
  color: #ffd700;
}

.pdf-btn i {
  font-size: 1rem;
}

.details-btn {
  background: #eef2f7;
  color: #1e293b;
}

.empty-state {
  text-align: center;
  padding: 76px 20px;
  border-radius: 22px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  color: #64748b;
}

.empty-state i {
  font-size: 3.2rem;
  color: #94a3b8;
  margin-bottom: 14px;
}

.empty-state h2 {
  margin: 0 0 8px;
  color: #1e293b;
  font-size: 1.35rem;
}

.empty-state p {
  margin: 0 0 18px;
}

.error-state i {
  color: #b91c1c;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 18px;
  margin-top: 36px;
  color: #334155;
  font-weight: 900;
}

.mt {
  margin-top: 14px;
}

.mt-sm {
  margin-top: 10px;
}

/* DARK MODE */
:global(html.dark) .recruitment-page {
  color: #ebede9;
}

:global(html.dark) .recruitment-hero {
  background:
    radial-gradient(circle at top right, rgba(255, 202, 40, 0.18), transparent 34%),
    linear-gradient(135deg, #151d16 0%, #243125 56%, #182119 100%);
  border-color: rgba(255, 202, 40, 0.18);
}

:global(html.dark) .recruitment-hero h1 {
  color: #ffca28;
}

:global(html.dark) .recruitment-hero p {
  color: #d9dfd4;
}

:global(html.dark) .hero-badge {
  background: rgba(255, 202, 40, 0.08);
  border-color: rgba(255, 202, 40, 0.22);
  color: #ffca28;
}

:global(html.dark) .search-input {
  background: #1a241b !important;
  border-color: #3d4a35 !important;
  color: #ebede9 !important;
  box-shadow: none;
}

:global(html.dark) .search-wrap i {
  color: #a1a89c;
}

:global(html.dark) .search-input::placeholder {
  color: #8d9687;
}

:global(html.dark) .count {
  background: #1f261b;
  color: #ffca28;
  border: 1px solid rgba(255, 202, 40, 0.14);
}

:global(html.dark) .doc-card {
  background: #1f261b;
  border-color: rgba(255, 202, 40, 0.14);
  box-shadow: 0 16px 34px rgba(0, 0, 0, 0.22);
}

:global(html.dark) .doc-card:hover {
  border-color: rgba(255, 202, 40, 0.24);
}

:global(html.dark) .doc-preview {
  background: transparent;
}

:global(html.dark) .doc-sheet {
  background: #f8fafc;
  box-shadow:
    0 12px 30px rgba(0, 0, 0, 0.4),
    0 0 0 1px rgba(255, 202, 40, 0.08);
}

:global(html.dark) .doc-notice {
  background: #182119;
  color: #a1a89c;
  border: 1px solid rgba(255, 202, 40, 0.12);
}

:global(html.dark) .doc-notice i {
  color: #ffca28;
}

:global(html.dark) .doc-meta {
  color: #a1a89c;
}

:global(html.dark) .doc-body h2,
:global(html.dark) .empty-state h2 {
  color: #ffca28;
}

:global(html.dark) .excerpt,
:global(html.dark) .empty-state p {
  color: #a1a89c;
}

:global(html.dark) .details-btn {
  background: #2e392a;
  color: #ebede9;
}

:global(html.dark) .pdf-btn {
  background: #11170f;
  color: #ffca28;
  border: 1px solid rgba(255, 202, 40, 0.16);
}

:global(html.dark) .empty-state {
  background: #1f261b;
  border-color: rgba(255, 202, 40, 0.14);
  color: #a1a89c;
}

:global(html.dark) .empty-state i {
  color: #ffca28;
}

:global(html.dark) .pagination {
  color: #d9dfd4;
}

/* RESPONSIVE */
@media (max-width: 900px) {
  .toolbar {
    align-items: stretch;
    flex-direction: column;
  }

  .search-wrap {
    max-width: 100%;
  }

  .count {
    width: fit-content;
  }

  .doc-card {
    grid-template-columns: minmax(240px, 320px) 1fr;
  }

  .doc-sheet {
    height: 340px;
  }
}

@media (max-width: 700px) {
  .recruitment-page {
    padding: 24px 0.85rem 54px;
  }

  .recruitment-hero {
    min-height: auto;
    flex-direction: column;
    align-items: flex-start;
    border-radius: 22px;
    padding: 24px 18px;
  }

  .hero-badge {
    display: none;
  }

  .doc-card {
    grid-template-columns: 1fr;
    gap: 16px;
    padding: 14px;
  }

  .doc-sheet {
    width: 100%;
    height: min(76vh, 540px);
  }

  .doc-meta {
    flex-direction: column;
    align-items: flex-start;
  }

  .doc-notice {
    border-radius: 14px;
    align-items: flex-start;
  }

  .doc-actions {
    flex-direction: column;
  }

  .pdf-btn,
  .details-btn {
    width: 100%;
  }

  .pagination {
    flex-direction: column;
    gap: 12px;
  }
}
</style>