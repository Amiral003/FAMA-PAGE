<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useHead } from '@unhead/vue'

const route = useRoute()
const slug = computed(() => String(route.params.slug || '').trim())

const staff = ref(null)
const isLoading = ref(false)
const loadError = ref('')
const activeLeaderPhotoIndex = ref(0)
let controller = null

const cleanText = (value, fallback = '') => {
  return typeof value === 'string' && value.trim() ? value.trim() : fallback
}

const stripHtml = (value, fallback = '') => {
  if (typeof value !== 'string' || !value.trim()) return fallback
  return value
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/&amp;/g, '&')
    .replace(/&quot;/g, '"')
    .replace(/&#039;/g, "'")
    .replace(/\s+/g, ' ')
    .trim()
}

const isMissionsTextLong = computed(() => {
  const descriptionText = staff.value?.description ? stripHtml(staff.value.description) : ''
  const missionsText = staff.value?.missions ? stripHtml(staff.value.missions) : ''
  const fullText = `${descriptionText} ${missionsText}`.trim()
  if (!fullText) return false
  const wordCount = fullText.split(/\s+/).filter(word => word.length > 0).length
  return wordCount > 150
})

const storageUrl = (path) => {
  if (!path || typeof path !== 'string') return ''
  return `/storage/${encodeURI(path.replace(/^\/+/, ''))}`
}

const normalizePhotoPaths = (photos, legacyPhoto = '') => {
  const paths = []
  const addPath = (photo) => {
    if (typeof photo !== 'string') return
    const cleanPhoto = photo.trim()
    if (cleanPhoto && !paths.includes(cleanPhoto)) paths.push(cleanPhoto)
  }

  if (Array.isArray(photos)) {
    photos.forEach(addPath)
  } else if (typeof photos === 'string') {
    try {
      const parsedPhotos = JSON.parse(photos)
      if (Array.isArray(parsedPhotos)) parsedPhotos.forEach(addPath)
      else addPath(photos)
    } catch {
      addPath(photos)
    }
  }

  addPath(legacyPhoto)
  return paths.slice(0, 3)
}

const logoUrl = computed(() => (staff.value?.logo ? storageUrl(staff.value.logo) : ''))
const leaderPhotoUrls = computed(() => {
  if (!staff.value) return []
  return normalizePhotoPaths(staff.value.leader_photos, staff.value.leader_photo).map(storageUrl)
})
const activeLeaderPhotoUrl = computed(() => leaderPhotoUrls.value[activeLeaderPhotoIndex.value] || '')
const hasMultipleLeaderPhotos = computed(() => leaderPhotoUrls.value.length > 1)
const secondLeaderPhotoUrl = computed(() => (staff.value?.second_leader_photo ? storageUrl(staff.value.second_leader_photo) : ''))

const missionsHtml = computed(() => {
  if (!staff.value) return ''
  const descriptionHtml = staff.value.description ? `<p class="staff-description-lead">${staff.value.description}</p>` : ''
  const missionsHtmlContent = staff.value.missions || '<p>Les missions et attributions ne sont pas encore renseignées.</p>'
  return descriptionHtml + missionsHtmlContent
})

const pageTitle = computed(() => {
  return staff.value?.name ? `${staff.value.name} | FAMa` : 'État-major | FAMa'
})

const pageDescription = computed(() => {
  if (!staff.value) return 'Découvrez les informations officielles des états-majors des FAMa.'
  const description = stripHtml(staff.value.description)
  const missions = stripHtml(staff.value.missions)
  const text = description || missions || `Présentation officielle de ${cleanText(staff.value.name, 'cet état-major')}.`
  return text.length > 160 ? `${text.slice(0, 157)}...` : text
})

const canonicalUrl = computed(() => {
  if (typeof window === 'undefined') return route.path
  return `${window.location.origin}${route.path}`
})

const hasCommand = computed(() => {
  if (!staff.value) return false
  return Boolean(
    cleanText(staff.value.leader_name) ||
    cleanText(staff.value.leader_rank) ||
    leaderPhotoUrls.value.length > 0
  )
})

const showPreviousLeaderPhoto = () => {
  if (!leaderPhotoUrls.value.length) return
  activeLeaderPhotoIndex.value =
    (activeLeaderPhotoIndex.value - 1 + leaderPhotoUrls.value.length) % leaderPhotoUrls.value.length
}

const showNextLeaderPhoto = () => {
  if (!leaderPhotoUrls.value.length) return
  activeLeaderPhotoIndex.value = (activeLeaderPhotoIndex.value + 1) % leaderPhotoUrls.value.length
}

const selectLeaderPhoto = (index) => {
  if (index < 0 || index >= leaderPhotoUrls.value.length) return
  activeLeaderPhotoIndex.value = index
}

const hasSecondCommand = computed(() => {
  if (!staff.value) return false
  return Boolean(
    cleanText(staff.value.second_leader_name) ||
    cleanText(staff.value.second_leader_rank) ||
    secondLeaderPhotoUrl.value
  )
})

const hasContact = computed(() => {
  if (!staff.value) return false
  return Boolean(
    cleanText(staff.value.contact_address) ||
    cleanText(staff.value.contact_phone) ||
    cleanText(staff.value.contact_hotline) ||
    cleanText(staff.value.contact_email) ||
    cleanText(staff.value.contact_hours)
  )
})

const fetchStaff = async () => {
  if (!slug.value) {
    staff.value = null
    loadError.value = 'État-major introuvable.'
    return
  }

  if (controller) controller.abort()
  controller = new AbortController()

  isLoading.value = true
  loadError.value = ''

  try {
    const res = await fetch(`/api/public/staffs/${encodeURIComponent(slug.value)}`, {
      headers: { Accept: 'application/json' },
      signal: controller.signal,
    })

    if (res.status === 404) {
      staff.value = null
      loadError.value = 'État-major introuvable.'
      return
    }

    if (!res.ok) {
      staff.value = null
      loadError.value = 'Erreur de chargement.'
      return
    }

    staff.value = await res.json()
  } catch (error) {
    if (error?.name !== 'AbortError') {
      staff.value = null
      loadError.value = 'Erreur réseau.'
    }
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchStaff)
watch(slug, fetchStaff)
watch(leaderPhotoUrls, () => {
  activeLeaderPhotoIndex.value = 0
})

onBeforeUnmount(() => {
  if (controller) controller.abort()
})

useHead(() => {
  const meta = [
    { name: 'description', content: pageDescription.value },
    { name: 'robots', content: staff.value ? 'index,follow' : 'noindex,follow' },
    { property: 'og:type', content: 'article' },
    { property: 'og:title', content: pageTitle.value },
    { property: 'og:description', content: pageDescription.value },
    { property: 'og:url', content: canonicalUrl.value },
    { name: 'twitter:card', content: logoUrl.value ? 'summary_large_image' : 'summary' },
    { name: 'twitter:title', content: pageTitle.value },
    { name: 'twitter:description', content: pageDescription.value },
  ]

  if (logoUrl.value) {
    meta.push({ property: 'og:image', content: logoUrl.value })
    meta.push({ name: 'twitter:image', content: logoUrl.value })
  }

  return {
    title: pageTitle.value,
    meta,
    link: [{ rel: 'canonical', href: canonicalUrl.value }],
  }
})
</script>

<template>
  <div class="staff-page">
    <section class="staff-shell">
      <nav class="breadcrumb">
        <RouterLink to="/">Accueil</RouterLink>
        <span class="sep">/</span>
        <RouterLink to="/about">À propos</RouterLink>
        <span class="sep">/</span>
        <span class="current">État-major</span>
      </nav>

      <div v-if="isLoading" class="state-box">
        <div class="loading-spinner"></div>
        <p>Chargement des informations...</p>
      </div>

      <div v-else-if="loadError" class="state-box state-error" role="alert">
        {{ loadError }}
      </div>

      <article v-else-if="staff" class="staff-layout">
        <!-- Header avec logo et titre -->
        <header class="hero">
          <div class="hero-media">
            <div class="logo-frame" style="display: flex; align-items: center; justify-content: center; background: transparent;">
  <img v-if="logoUrl" :src="logoUrl" :alt="staff.name" style="max-width: 120px; max-height: 120px; width: auto; height: auto; object-fit: contain;" loading="eager" />
  <div v-else class="logo-fallback" style="width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #40a15e, #1a4629); color: white; font-size: 1.5rem; font-weight: bold;">{{ staff.initials || 'EM' }}</div>
</div>
          </div>
          <div class="hero-body">
            <p class="hero-kicker">État-major</p>
            <h1 class="hero-title">{{ staff.name }}</h1>
            <div class="hero-meta">
              <span v-if="staff.initials" class="hero-badge">{{ staff.initials }}</span>
              <span v-if="staff.motto" class="hero-motto">“{{ staff.motto }}”</span>
            </div>
          </div>
        </header>

        <!-- Section unique: Description, Missions, Commandement (tout dans le meme bloc) -->
        <section class="missions-block-full">
          <div
            class="journal-style-container"
            :class="isMissionsTextLong ? 'missions-layout-multi' : 'missions-layout-single'"
          >
            <!-- Bloc Commandement (photo carrée + badge coloré) à l'intérieur des colonnes -->
            <div v-if="hasCommand" class="chef-journal-header">
              <h2 class="section-title">Commandement</h2>

              <!-- Commandant principal -->
              <div class="leader-photo-wrapper">
                <div class="leader-gallery" :class="{ 'has-controls': hasMultipleLeaderPhotos }">
                  <button
                    v-if="hasMultipleLeaderPhotos"
                    type="button"
                    class="gallery-control gallery-control-prev"
                    aria-label="Photo precedente"
                    @click="showPreviousLeaderPhoto"
                  >
                    ‹
                  </button>

                  <!-- PHOTO EN CARRE -->
                  <div class="photo-border-square">
                    <img
                      v-if="activeLeaderPhotoUrl"
                      :src="activeLeaderPhotoUrl"
                      :alt="staff.leader_name || 'Photo du commandement'"
                      class="command-photo-square"
                    />
                    <div v-else class="command-photo-fallback-square">
                      <span class="fallback-icon">★</span>
                    </div>
                  </div>

                  <button
                    v-if="hasMultipleLeaderPhotos"
                    type="button"
                    class="gallery-control gallery-control-next"
                    aria-label="Photo suivante"
                    @click="showNextLeaderPhoto"
                  >
                    ›
                  </button>
                </div>

                <div v-if="hasMultipleLeaderPhotos" class="gallery-dots" aria-label="Choisir une photo">
                  <button
                    v-for="(_, index) in leaderPhotoUrls"
                    :key="index"
                    type="button"
                    class="gallery-dot"
                    :class="{ active: index === activeLeaderPhotoIndex }"
                    :aria-label="`Afficher la photo ${index + 1}`"
                    :aria-current="index === activeLeaderPhotoIndex ? 'true' : 'false'"
                    @click="selectLeaderPhoto(index)"
                  ></button>
                </div>

                <!-- Infos du leader dans un petit cadre coloré (tag/badge) -->
                <div class="leader-info-tag">
                  <p v-if="staff.leader_rank" class="command-rank">{{ staff.leader_rank }}</p>
                  <p v-if="staff.leader_name" class="command-name">{{ staff.leader_name }}</p>
                  <p v-if="staff.leader_function" class="command-function">{{ staff.leader_function }}</p>
                  <p v-if="staff.leader_word" class="command-word">“{{ staff.leader_word }}”</p>
                </div>
              </div>

              <!-- Commandant adjoint -->
              <div v-if="hasSecondCommand" class="leader-photo-wrapper second mt-6">
                <div class="photo-border-square">
                  <img
                    v-if="secondLeaderPhotoUrl"
                    :src="secondLeaderPhotoUrl"
                    :alt="staff.second_leader_name || 'Photo du commandement adjoint'"
                    class="command-photo-square"
                  />
                  <div v-else class="command-photo-fallback-square">
                    <span class="fallback-icon">★</span>
                  </div>
                </div>
                <div class="leader-info-tag">
                  <p v-if="staff.second_leader_rank" class="command-rank">{{ staff.second_leader_rank }}</p>
                  <p v-if="staff.second_leader_name" class="command-name">{{ staff.second_leader_name }}</p>
                  <p v-if="staff.second_leader_function" class="command-function">{{ staff.second_leader_function }}</p>
                  <p v-if="staff.second_leader_word" class="command-word">“{{ staff.second_leader_word }}”</p>
                </div>
              </div>
            </div>

            <!-- Description et Missions (texte) -->
            <div
              class="missions-text-content"
              v-html="missionsHtml"
            ></div>
          </div>
        </section>

        <!-- Section Contacts -->
        <aside v-if="hasContact" class="contacts-block-full">
          <h2 class="section-title">Contacts officiels</h2>
          <div class="contact-grid">
            <div v-if="staff.contact_address" class="contact-item">
              <span class="contact-label">Adresse</span>
              <span class="contact-value">{{ staff.contact_address }}</span>
            </div>
            <div v-if="staff.contact_phone" class="contact-item">
              <span class="contact-label">Téléphone</span>
              <a class="contact-link" :href="`tel:${staff.contact_phone}`">{{ staff.contact_phone }}</a>
            </div>
            <div v-if="staff.contact_hotline" class="contact-item">
              <span class="contact-label">Hotline</span>
              <a class="contact-link" :href="`tel:${staff.contact_hotline}`">{{ staff.contact_hotline }}</a>
            </div>
            <div v-if="staff.contact_email" class="contact-item">
              <span class="contact-label">Email</span>
              <a class="contact-link" :href="`mailto:${staff.contact_email}`">{{ staff.contact_email }}</a>
            </div>
            <div v-if="staff.contact_hours" class="contact-item">
              <span class="contact-label">Horaires</span>
              <span class="contact-value">{{ staff.contact_hours }}</span>
            </div>
            <div v-if="staff.contact_map_url" class="contact-item">
              <span class="contact-label">Localisation</span>
              <a class="contact-link" :href="staff.contact_map_url" target="_blank" rel="noopener noreferrer">Voir la carte</a>
            </div>
          </div>
        </aside>
      </article>
    </section>
  </div>
</template>



<style scoped>
.staff-page {
  min-height: 100vh;
  padding: 32px 0 48px;
  background: linear-gradient(135deg, #f5f7fc 0%, #eef2f8 100%);
  font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, sans-serif;
}

.staff-shell {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 16px;
}

.breadcrumb {
  margin-bottom: 24px;
  font-size: 14px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.breadcrumb a {
  text-decoration: none;
  color: #14b82c;
  transition: color 0.2s;
}

.breadcrumb a:hover {
  color: #0e8a22;
}

.sep {
  opacity: 0.5;
}

.current {
  opacity: 0.85;
}

.state-box {
  border-radius: 16px;
  padding: 18px 20px;
  font-weight: 500;
  background: white;
  text-align: center;
}

.state-error {
  border-left: 4px solid #dc2626;
  color: #991b1b;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e2e8f0;
  border-top-color: #14b82c;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Hero Section */
.hero {
  display: grid;
  grid-template-columns: 140px 1fr;
  gap: 24px;
  align-items: center;
  padding: 32px;
  border-radius: 24px;
  background: white;
  margin-bottom: 32px;
  border: 1px solid #eef2ff;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.02);
}

.logo-frame {
  width: 120px;
  height: 120px;
  border-radius: 24px;
  overflow: hidden;
  display: grid;
  place-items: center;
  /* border: 1px solid #eef2ff; */
  background: transparent;
}

.logo-frame img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.logo-fallback {
  font-size: 28px;
  font-weight: 800;
  color: #14b82c;
}

.hero-kicker {
  margin: 0 0 8px;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  font-weight: 800;
  color: #14b82c;
}

.hero-title {
  margin: 0;
  font-size: clamp(1.8rem, 4vw, 2.4rem);
  font-weight: 800;
  color: #0f172a;
  line-height: 1.2;
}

.hero-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-items: center;
  margin-top: 12px;
}

.hero-badge {
  background: #14b82c;
  color: white;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 700;
}

.hero-motto {
  font-style: italic;
  color: #64748b;
}

/* Section Missions (contenant tout) */
.missions-block-full {
  background: white;
  border-radius: 20px;
  padding: 28px;
  margin-bottom: 32px;
  border: 1px solid #eef2ff;
}

.journal-style-container {
  font-size: 15px;
  line-height: 1.85;
  color: #1e293b;
}

.missions-layout-multi {
  column-count: 3;
  column-gap: 40px;
  column-rule: 1px solid rgba(20, 184, 44, 0.15);
  text-align: justify;
}

.missions-layout-single {
  column-count: 1;
  text-align: justify;
}

/* Bloc Commandement (chef) */
.chef-journal-header {
  break-inside: avoid-column;
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px dashed rgba(20, 184, 44, 0.15);
}

.leader-photo-wrapper {
  text-align: center;
  margin-bottom: 20px;
}

.leader-gallery {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.gallery-control {
  position: absolute;
  z-index: 2;
  top: 50%;
  width: 34px;
  height: 34px;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.72);
  color: #ffffff;
  font-size: 26px;
  line-height: 1;
  display: grid;
  place-items: center;
  transform: translateY(-50%);
  transition: background 0.2s, transform 0.2s;
}

.gallery-control:hover {
  background: rgba(20, 184, 44, 0.92);
  transform: translateY(-50%) scale(1.04);
}

.gallery-control-prev {
  left: 8px;
}

.gallery-control-next {
  right: 8px;
}

.gallery-dots {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin: 10px 0 8px;
}

.gallery-dot {
  width: 9px;
  height: 9px;
  border-radius: 999px;
  background: #cbd5e1;
  transition: background 0.2s, transform 0.2s;
}

.gallery-dot.active {
  background: #14b82c;
  transform: scale(1.25);
}

/* PHOTO EN CARRE */
.photo-border-square {
  width: 230px;
  height: 230px;
  margin: 0 auto;
  border-radius: 12px;
  background: linear-gradient(135deg, #14b82c, #0e8a22);
  padding: 1px;
  box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.2);
  overflow: hidden;
}

.command-photo-square {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
  background: #f3f5f8;
}

.command-photo-fallback-square {
  width: 100%;
  height: 100%;
  border-radius: 8px;
  background: linear-gradient(135deg, #e2f0e4, #cfe3d3);
  display: flex;
  align-items: center;
  justify-content: center;
}

.fallback-icon {
  font-size: 3rem;
  color: #14b82c;
  font-weight: 800;
}

/* PETIT CADRE COLORE (TAG/BADGE) POUR LES INFOS DU LEADER */
.leader-info-tag {
  background: linear-gradient(135deg, #e4cfaf, #e3d6a1);
  padding: 3px 6px;
  border-radius: 16px;
  margin-top: 0%;
  margin-bottom: 12px;
  box-shadow: 0 4px 12px rgba(20, 184, 44, 0.3);
  transition: transform 0.2s, box-shadow 0.2s;
  display: inline-block;
  width: auto;
  min-width: 230px;
}

.leader-info-tag:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(20, 184, 44, 0.4);
}

.command-rank {
  font-size: 0.7rem;
  letter-spacing: 2px;
  text-transform: uppercase;
  font-weight: 700;
  color: #000903;
  background: rgba(255, 255, 255, 0.2);
  display: inline-block;
  padding: 0.2rem 0.8rem;
  border-radius: 30px;
  margin-bottom: 0.5rem;
}

.command-name {
  font-size: 1.2rem;
  font-weight: 800;
  color: #000903;
  margin: 0 0 0.2rem 0;
  letter-spacing: -0.3px;
}

.command-function {
  font-size: 0.8rem;
  font-weight: 500;
  color: #000903;
  margin: 0;
  padding: 0;
}

.command-word {
  font-style: italic;
  font-size: 0.75rem;
  color: #0a0000;
  margin: 0.3rem 0 0;
  padding-top: 0.3rem;
  border-top: 1px solid rgba(255, 255, 255, 0.3);
  display: inline-block;
}

.section-title {
  margin: 0 0 20px;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 1.3px;
  font-weight: 800;
  color: #14b82c;
  border-left: 3px solid #14b82c;
  padding-left: 12px;
}

.mt-6 {
  margin-top: 1.5rem;
}

/* Texte des missions */
.missions-text-content :deep(p.staff-description-lead) {
  margin: 0 0 16px 0;
  font-size: 16px;
  font-weight: 600;
  color: #0f172a;
  line-height: 1.7;
  break-inside: avoid-column;
}

.missions-text-content :deep(p:first-of-type::first-letter) {
  float: left;
  font-size: 3.4em;
  line-height: 0.8;
  padding-top: 4px;
  padding-right: 10px;
  font-weight: 900;
  color: #14b82c;
}

.missions-text-content {
  display: block;
  width: 100%;
}

.missions-text-content :deep(p) {
  margin: 0 0 16px 0;
  break-inside: auto;
}

.missions-layout-multi .missions-text-content :deep(p) {
  text-indent: 24px;
}

.missions-text-content :deep(p:first-of-type) {
  text-indent: 0 !important;
}

.missions-text-content :deep(h3),
.missions-text-content :deep(h4) {
  margin: 24px 0 12px;
  font-size: 15px;
  font-weight: 800;
  color: #14b82c;
  text-transform: uppercase;
  break-inside: avoid;
}

.missions-text-content :deep(ul),
.missions-text-content :deep(ol) {
  margin: 0 0 16px;
  padding-left: 20px;
  break-inside: avoid;
}

/* Contacts Section */
.contacts-block-full {
  background: white;
  border-radius: 20px;
  padding: 28px;
  border: 1px solid #eef2ff;
}

.contact-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.contact-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 16px;
  background: #f8fafc;
  border-radius: 12px;
  transition: transform 0.2s, box-shadow 0.2s;
}

.contact-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.contact-label {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-weight: 800;
  color: #14b82c;
}

.contact-value,
.contact-link {
  font-size: 14px;
  color: #1e293b;
  font-weight: 500;
}

.contact-link {
  color: #14b82c;
  text-decoration: none;
  font-weight: 600;
}

.contact-link:hover {
  text-decoration: underline;
}
/* Responsive */
@media (max-width: 1024px) {
  .missions-layout-multi {
    column-count: 2;
    column-gap: 32px;
  }
}

@media (max-width: 768px) {
  .hero {
    grid-template-columns: 1fr;
    text-align: center;
  }

  /* AJOUTEZ CES LIGNES POUR CENTRER LE LOGO SUR MOBILE */
  .hero-media {
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .logo-frame {
    margin: 0 auto;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  /* FIN DES AJOUTS */

  .hero-meta {
    justify-content: center;
  }

  .missions-layout-multi {
    column-count: 1;
    column-rule: none;
  }

  .missions-layout-multi .missions-text-content :deep(p) {
    text-indent: 0;
  }

  .contact-grid {
    grid-template-columns: 1fr;
  }

  .photo-border-square {
    width: 150px;
    height: 150px;
  }

  .leader-info-tag {
    padding: 10px 16px;
  }

  .command-name {
    font-size: 1rem;
  }
}

@media (max-width: 640px) {
  .staff-page {
    padding: 20px 0;
  }

  .hero,
  .missions-block-full,
  .contacts-block-full {
    padding: 20px;
  }

  .photo-border-square {
    width: 130px;
    height: 130px;
  }
}
</style>
