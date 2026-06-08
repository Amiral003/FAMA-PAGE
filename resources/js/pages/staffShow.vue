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

/**
 * Détermination du mode d'affichage selon le volume de mots réels
 */
const isMissionsTextLong = computed(() => {
  if (!staff.value?.missions) return false
  const cleanStr = stripHtml(staff.value.missions)
  if (!cleanStr) return false

  const wordCount = cleanStr.split(/\s+/).filter(word => word.length > 0).length
  return wordCount > 150
})

const storageUrl = (path) => {
  if (!path || typeof path !== 'string') return ''
  return `/storage/${encodeURI(path.replace(/^\/+/, ''))}`
}

const logoUrl = computed(() => (staff.value?.logo ? storageUrl(staff.value.logo) : ''))
const leaderPhotoUrl = computed(() => (staff.value?.leader_photo ? storageUrl(staff.value.leader_photo) : ''))
const secondLeaderPhotoUrl = computed(() => (staff.value?.second_leader_photo ? storageUrl(staff.value.second_leader_photo) : ''))

const pageTitle = computed(() => {
  return staff.value?.name ? `${staff.value.name} | FAMa` : 'État-major | FAMa'
})

const pageDescription = computed(() => {
  if (!staff.value) return 'Découvrez les informations officielles des états-majors des FAMa.'

  const description = stripHtml(staff.value.description)
  const missions = stripHtml(staff.value.missions)

  const text =
    description ||
    missions ||
    `Présentation officielle de ${cleanText(staff.value.name, 'cet état-major')}.`

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

const hasSecondCommand = computed(() => {
  if (!staff.value) return false
  return Boolean(
    cleanText(staff.value.second_leader_name) ||
    cleanText(staff.value.second_leader_rank) ||
    cleanText(staff.value.second_leader_word) ||
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

        <section class="presentation-command-section">
          <div class="presentation-col">
            <h2 class="section-title">Présentation</h2>
            <div class="prose-content" v-html="staff.description || '<p>Aucune présentation n’est disponible pour le moment.</p>'">
            </div>
          </div>

          <div v-if="hasCommand" class="commandement-col">
            <h2 class="section-title">Commandement</h2>

            <div class="leader-card">
              <div class="leader-photo">
                <img
                  v-if="leaderPhotoUrl"
                  :src="leaderPhotoUrl"
                  :alt="staff.leader_name || 'Photo du commandement'"
                  loading="lazy"
                  decoding="async"
                />
                <div v-else class="leader-fallback">Photo non disponible</div>
              </div>
              <div class="leader-info">
                <p class="leader-role">Chef</p>
                <p v-if="staff.leader_rank" class="leader-rank">{{ staff.leader_rank }}</p>
                <p v-if="staff.leader_name" class="leader-name">{{ staff.leader_name }}</p>
                <p v-if="staff.leader_word" class="leader-word">“{{ staff.leader_word }}”</p>
              </div>
            </div>

            <div v-if="hasSecondCommand" class="leader-card">
              <div class="leader-photo">
                <img
                  v-if="secondLeaderPhotoUrl"
                  :src="secondLeaderPhotoUrl"
                  :alt="staff.second_leader_name || 'Photo du commandement adjoint'"
                  loading="lazy"
                  decoding="async"
                />
                <div v-else class="leader-fallback">Photo non disponible</div>
              </div>
              <div class="leader-info">
                <p class="leader-role">Chef Adjoint</p>
                <p v-if="staff.second_leader_rank" class="leader-rank">{{ staff.second_leader_rank }}</p>
                <p v-if="staff.second_leader_name" class="leader-name">{{ staff.second_leader_name }}</p>
                <p v-if="staff.second_leader_word" class="leader-word">“{{ staff.second_leader_word }}”</p>
              </div>
            </div>
          </div>
        </section>

        <section class="missions-block-full">
          <h2 class="section-title">Missions et attributions</h2>
          <div
            class="journal-style-container"
            :class="isMissionsTextLong ? 'missions-layout-multi' : 'missions-layout-single'"
            v-html="staff.missions || '<p>Les missions et attributions ne sont pas encore renseignées.</p>'"
          >
          </div>
        </section>

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
        </aside>
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
  max-width: 1280px;
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
  gap: 32px;
}

.hero {
  display: grid;
  grid-template-columns: 140px 1fr;
  gap: 24px;
  align-items: center;
  padding: 32px;
  border-radius: 24px;
  border: 1px solid rgba(20, 184, 44, 0.14);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
  background: white;
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
  background: #f8fafc;
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

/* Section Présentation + Commandement côte à côte */
.presentation-command-section {
  display: grid;
  grid-template-columns: 60% 40%;
  gap: 32px;
  padding: 28px;
  border-radius: 20px;
  border: 1px solid rgba(20, 184, 44, 0.12);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
  background: white;
}

.presentation-col,
.commandement-col {
  display: flex;
  flex-direction: column;
}

.section-title {
  margin: 0 0 20px;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 1.3px;
  font-weight: 800;
  color: #14b82c;
}

/* JUSTIFICATION DU TEXTE DE LA PRESENTATION */
.prose-content {
  font-size: 16px;
  line-height: 1.9;
  color: #334155;
  text-align: justify;
  text-justify: inter-word;
}

.prose-content :deep(p) {
  margin: 0 0 14px;
}

/* Cartes commandement */
.leader-card {
  display: flex;
  gap: 20px;
  align-items: flex-start;
  margin-bottom: 28px;
  padding: 20px;
  background: transparent;
  border-radius: 16px;
  transition: transform 0.2s ease;
}

.leader-card:last-child {
  margin-bottom: 0;
}

.leader-photo {
  width: 120px;
  height: 120px;
  flex-shrink: 0;
  border-radius: 16px;
  overflow: hidden;
  background: #e2e8f0;
}

.leader-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.leader-fallback {
  width: 100%;
  height: 100%;
  display: grid;
  place-items: center;
  font-weight: 700;
  font-size: 12px;
  opacity: 0.6;
  text-align: center;
}

.leader-info {
  flex: 1;
}

.leader-role {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  font-weight: 800;
  color: #14b82c;
  margin-bottom: 8px;
}

.leader-rank {
  margin: 0 0 6px;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.7px;
  color: #64748b;
}

.leader-name {
  margin: 0;
  font-size: 18px;
  font-weight: 800;
  color: #0f172a;
}

.leader-word {
  margin: 8px 0 0;
  font-style: italic;
  font-size: 13px;
  color: #475569;
}

/* Missions block */
.missions-block-full {
  padding: 28px;
  border-radius: 20px;
  border: 1px solid rgba(20, 184, 44, 0.12);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
  background: white;
}

/* Styles de base de la typographie journal */
.journal-style-container {
  font-size: 15px;
  line-height: 1.85;
  color: #1e293b;
}

/* ==========================================================================
   JUSTIFICATION STRICTE SUR LES DEUX MODES DE MISSIONS
   ========================================================================== */

/* TEXTE COURT : Devient un bloc unifié JUSTIFIÉ sur toute la largeur */
.missions-layout-single {
  display: block;
  width: 100%;
  text-align: justify;
  text-justify: inter-word;
}

/* TEXTE LONG : 3 colonnes ajustées et JUSTIFIÉES */
.missions-layout-multi {
  display: block;
  column-count: 3;
  column-gap: 48px;
  column-rule: 1px solid rgba(20, 184, 44, 0.15);
  text-align: justify;
  text-justify: inter-word;
}

/* Gestion des paragraphes injectés */
.journal-style-container :deep(p) {
  margin: 0 0 16px 0;
}

/* Indentation et cassures de colonnes réservées au mode long */
.missions-layout-multi :deep(p) {
  text-indent: 24px;
  break-inside: avoid-column;
}

/* Lettrine sur le premier mot (uniquement en mode long) */
.missions-layout-multi :deep(p:first-of-type::first-letter) {
  float: left;
  font-size: 3.4em;
  line-height: 0.8;
  padding-top: 4px;
  padding-right: 10px;
  font-weight: 900;
  color: #0f172a;
}

.missions-layout-multi :deep(p:first-of-type) {
  text-indent: 0;
}

/* Configuration des en-têtes et listes */
.journal-style-container :deep(h3),
.journal-style-container :deep(h4) {
  margin: 24px 0 12px 0;
  font-size: 15px;
  font-weight: 800;
  color: #14b82c;
  text-transform: uppercase;
}

.missions-layout-multi :deep(h3),
.missions-layout-multi :deep(h4) {
  break-inside: avoid-column;
}

.journal-style-container :deep(ul),
.journal-style-container :deep(ol) {
  margin: 0 0 16px;
  padding-left: 20px;
}

.missions-layout-multi :deep(ul),
.missions-layout-multi :deep(ol) {
  break-inside: avoid-column;
}

.journal-style-container :deep(li) {
  margin-bottom: 8px;
}

/* Contacts - Grille */
.contacts-block-full {
  padding: 28px;
  border-radius: 20px;
  border: 1px solid rgba(20, 184, 44, 0.12);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
  background: white;
}

.contact-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
}

.contact-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 16px;
  background: #f8fafc;
  border-radius: 12px;
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
  font-size: 15px;
  color: #1e293b;
  font-weight: 500;
}

.contact-link {
  color: #14b82c;
  text-decoration: none;
  font-weight: 700;
}

/* ==========================================================================
   Media Queries Responsive
   ========================================================================== */
@media (max-width: 1024px) {
  .presentation-command-section {
    grid-template-columns: 1fr;
    gap: 28px;
  }

  .contact-grid {
    grid-template-columns: 1fr;
  }

  .missions-layout-multi {
    column-count: 2;
    column-gap: 32px;
  }
}

@media (max-width: 640px) {
  .hero {
    grid-template-columns: 1fr;
    text-align: center;
  }

  .hero-meta {
    justify-content: center;
  }

  .leader-card {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  /* Sur smartphone, l'alignement à gauche redevient la norme pour le confort de lecture */
  .missions-layout-multi,
  .missions-layout-single,
  .prose-content {
    column-count: 1 !important;
    column-gap: 0;
    column-rule: none;
    text-align: left !important;
  }

  .missions-layout-multi :deep(p) {
    text-indent: 0;
  }
}
</style>
