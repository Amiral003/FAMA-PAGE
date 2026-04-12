<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useHead } from '@unhead/vue'

const route = useRoute()

const slug = computed(() => String(route.params.slug || '').trim())

const staff = ref(null)
const isLoading = ref(false)
const loadError = ref('')
let controller = null

const cleanText = (value, fallback = '') => {
  return typeof value === 'string' && value.trim() ? value.trim() : fallback
}

const storageUrl = (path) => {
  if (!path || typeof path !== 'string') return ''
  return `/storage/${encodeURI(path.replace(/^\/+/, ''))}`
}

const logoUrl = computed(() => (staff.value?.logo ? storageUrl(staff.value.logo) : ''))
const leaderPhotoUrl = computed(() => (staff.value?.leader_photo ? storageUrl(staff.value.leader_photo) : ''))

const pageTitle = computed(() => {
  return staff.value?.name ? `${staff.value.name} | FAMa` : 'État-major | FAMa'
})

const pageDescription = computed(() => {
  if (!staff.value) return 'Découvrez les informations officielles des états-majors des FAMa.'

  const description = cleanText(staff.value.description)
  const missions = cleanText(staff.value.missions)
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
    cleanText(staff.value.leader_word) ||
    leaderPhotoUrl.value
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
      <nav class="breadcrumb" aria-label="Fil d’Ariane">
        <RouterLink to="/">Accueil</RouterLink>
        <span class="sep">/</span>
        <RouterLink to="/about">À propos</RouterLink>
        <span class="sep">/</span>
        <span class="current">État-major</span>
      </nav>

      <div v-if="isLoading" class="state-box" aria-live="polite">
        Chargement des informations...
      </div>

      <div v-else-if="loadError" class="state-box state-error" role="alert">
        {{ loadError }}
      </div>

      <article v-else-if="staff" class="staff-layout">
        <header class="hero">
          <div class="hero-media">
            <div class="logo-frame">
              <img
                v-if="logoUrl"
                :src="logoUrl"
                :alt="staff.name || 'Logo état-major'"
                loading="eager"
                decoding="async"
                width="120"
                height="120"
              />
              <div v-else class="logo-fallback" aria-hidden="true">
                {{ staff.initials || 'EM' }}
              </div>
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

        <div class="main-grid">
          <main class="content-column">
            <section class="section-block">
              <h2 class="section-title">Présentation</h2>
              <div class="prose-content">
                <p>
                  {{ staff.description || 'Aucune présentation n’est disponible pour le moment.' }}
                </p>
              </div>
            </section>

            <section class="section-block">
              <h2 class="section-title">Missions et attributions</h2>
              <div class="prose-content">
                <p>
                  {{ staff.missions || 'Les missions et attributions ne sont pas encore renseignées.' }}
                </p>
              </div>
            </section>
          </main>

          <aside class="side-column">
            <section v-if="hasCommand" class="side-panel">
              <h2 class="panel-title">Commandement</h2>

              <div class="leader-card">
                <div class="leader-photo">
                  <img
                    v-if="leaderPhotoUrl"
                    :src="leaderPhotoUrl"
                    :alt="staff.leader_name || 'Photo du commandement'"
                    loading="lazy"
                    decoding="async"
                    width="420"
                    height="300"
                  />
                  <div v-else class="leader-fallback">Photo non disponible</div>
                </div>

                <div class="leader-info">
                  <p v-if="staff.leader_rank" class="leader-rank">{{ staff.leader_rank }}</p>
                  <p v-if="staff.leader_name" class="leader-name">{{ staff.leader_name }}</p>
                  <p v-if="staff.leader_word" class="leader-word">“{{ staff.leader_word }}”</p>
                </div>
              </div>
            </section>

            <section v-if="hasContact" class="side-panel">
              <h2 class="panel-title">Contacts officiels</h2>

              <div class="contact-list">
                <div v-if="staff.contact_address" class="contact-row">
                  <span class="contact-label">Adresse</span>
                  <span class="contact-value">{{ staff.contact_address }}</span>
                </div>

                <div v-if="staff.contact_phone" class="contact-row">
                  <span class="contact-label">Téléphone</span>
                  <a class="contact-link" :href="`tel:${staff.contact_phone}`">{{ staff.contact_phone }}</a>
                </div>

                <div v-if="staff.contact_hotline" class="contact-row">
                  <span class="contact-label">Hotline</span>
                  <a class="contact-link" :href="`tel:${staff.contact_hotline}`">{{ staff.contact_hotline }}</a>
                </div>

                <div v-if="staff.contact_email" class="contact-row">
                  <span class="contact-label">Email</span>
                  <a class="contact-link" :href="`mailto:${staff.contact_email}`">{{ staff.contact_email }}</a>
                </div>

                <div v-if="staff.contact_hours" class="contact-row">
                  <span class="contact-label">Horaires</span>
                  <span class="contact-value">{{ staff.contact_hours }}</span>
                </div>

                <div v-if="staff.contact_map_url" class="contact-row">
                  <span class="contact-label">Localisation</span>
                  <a
                    class="contact-link"
                    :href="staff.contact_map_url"
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    Voir la carte
                  </a>
                </div>
              </div>
            </section>
          </aside>
        </div>
      </article>
    </section>
  </div>
</template>

<style scoped>
.staff-page {
  min-height: 100vh;
  padding: 32px 0 48px;
}

.staff-shell {
  max-width: 1180px;
  margin: 0 auto;
  padding: 0 16px;
}

.breadcrumb {
  margin-bottom: 24px;
  font-size: 14px;
  font-weight: 600;
}

.breadcrumb a {
  text-decoration: none;
  color: #14b82c;
}

.sep {
  margin: 0 6px;
  opacity: 0.6;
}

.current {
  opacity: 0.85;
}

.state-box {
  border-radius: 16px;
  padding: 18px 20px;
  font-weight: 600;
  backdrop-filter: blur(8px);
}

.state-error {
  border: 1px solid rgba(220, 38, 38, 0.2);
}

.staff-layout {
  display: flex;
  flex-direction: column;
  gap: 28px;
}

.hero {
  display: grid;
  grid-template-columns: 140px 1fr;
  gap: 24px;
  align-items: center;
  padding: 28px;
  border-radius: 22px;
  border: 1px solid rgba(20, 184, 44, 0.14);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
}

.hero-media {
  display: flex;
  justify-content: center;
}

.logo-frame {
  width: 120px;
  height: 120px;
  border-radius: 24px;
  overflow: hidden;
  display: grid;
  place-items: center;
  border: 1px solid rgba(20, 184, 44, 0.14);
}

.logo-frame img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.logo-fallback {
  font-size: 28px;
  font-weight: 800;
}

.hero-kicker {
  margin: 0 0 8px;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 1.4px;
  font-weight: 800;
  color: #14b82c;
}

.hero-title {
  margin: 0;
  font-size: clamp(28px, 4vw, 44px);
  line-height: 1.08;
  font-weight: 900;
}

.hero-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-items: center;
  margin-top: 14px;
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 34px;
  padding: 0 12px;
  border-radius: 999px;
  background: #14b82c;
  color: white;
  font-size: 13px;
  font-weight: 800;
}

.hero-motto {
  font-style: italic;
  opacity: 0.85;
  font-weight: 500;
}

.main-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.45fr) minmax(300px, 0.8fr);
  gap: 28px;
  align-items: start;
}

.content-column {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.section-block {
  padding: 28px;
  border-radius: 20px;
  border: 1px solid rgba(20, 184, 44, 0.12);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
}

.section-title,
.panel-title {
  margin: 0 0 18px;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 1.3px;
  font-weight: 800;
  color: #14b82c;
}

.prose-content {
  font-size: 16px;
  line-height: 1.9;
  white-space: pre-line;
}

.side-column {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.side-panel {
  padding: 24px;
  border-radius: 20px;
  border: 1px solid rgba(20, 184, 44, 0.12);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
}

.leader-card {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.leader-photo {
  width: 100%;
  min-height: 220px;
  border-radius: 16px;
  overflow: hidden;
  background: rgba(0, 0, 0, 0.05);
}

.leader-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.leader-fallback {
  min-height: 220px;
  display: grid;
  place-items: center;
  font-weight: 700;
  opacity: 0.7;
}

.leader-rank {
  margin: 0 0 6px;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.7px;
  opacity: 0.75;
}

.leader-name {
  margin: 0;
  font-size: 22px;
  font-weight: 850;
  line-height: 1.2;
}

.leader-word {
  margin: 10px 0 0;
  font-style: italic;
  line-height: 1.7;
  opacity: 0.85;
}

.contact-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.contact-row {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding-bottom: 14px;
  border-bottom: 1px solid rgba(20, 184, 44, 0.08);
}

.contact-row:last-child {
  padding-bottom: 0;
  border-bottom: none;
}

.contact-label {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-weight: 800;
  opacity: 0.68;
}

.contact-value,
.contact-link {
  font-size: 15px;
  line-height: 1.6;
  word-break: break-word;
}

.contact-link {
  color: #14b82c;
  text-decoration: none;
  font-weight: 700;
}

.contact-link:hover {
  text-decoration: underline;
}

@media (max-width: 900px) {
  .main-grid {
    grid-template-columns: 1fr;
  }

  .side-column {
    order: 2;
  }

  .content-column {
    order: 1;
  }
}

@media (max-width: 640px) {
  .staff-page {
    padding: 20px 0 36px;
  }

  .hero {
    grid-template-columns: 1fr;
    text-align: center;
    padding: 22px 18px;
  }

  .hero-meta {
    justify-content: center;
  }

  .section-block,
  .side-panel {
    padding: 20px 16px;
  }

  .leader-name {
    font-size: 20px;
  }
}
</style>