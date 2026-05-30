<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useHead } from '@unhead/vue'
import { RouterLink } from 'vue-router'
import SidebarOfficial from '@/components/SidebarOfficial.vue'

import Card from 'primevue/card'
import Tag from 'primevue/tag'
import Skeleton from 'primevue/skeleton'

import aboutImg1 from '@/assets/images/FAMA-IMAGE/14.jpg'
import aboutImg2 from '@/assets/images/FAMA-IMAGE/23.jpg'

const siteUrl = typeof window !== 'undefined' ? window.location.origin : ''

const pageTitle = 'À propos | Forces Armées Maliennes'
const pageDescription =
  'Présentation officielle des Forces Armées Maliennes : missions, organisation institutionnelle, ministère, état-major général, directions, services et structures de défense nationale.'

useHead({
  title: pageTitle,
  meta: [
    { name: 'description', content: pageDescription },
    {
      name: 'keywords',
      content:
        'Forces Armées Maliennes, FAMa, Ministère de la Défense Mali, État-Major Général des Armées, Défense nationale, Armée malienne, structures militaires Mali',
    },
    { name: 'robots', content: 'index, follow, max-image-preview:large' },

    { property: 'og:type', content: 'website' },
    { property: 'og:title', content: 'À propos des Forces Armées Maliennes' },
    { property: 'og:description', content: pageDescription },
    { property: 'og:url', content: `${siteUrl}/about` },
    { property: 'og:site_name', content: 'Forces Armées Maliennes' },
    { property: 'og:locale', content: 'fr_FR' },
    { property: 'og:image', content: `${siteUrl}/images/og-default.jpg` },
    { property: 'og:image:alt', content: 'Forces Armées Maliennes' },

    { name: 'twitter:card', content: 'summary_large_image' },
    { name: 'twitter:title', content: 'À propos des Forces Armées Maliennes' },
    { name: 'twitter:description', content: pageDescription },
    { name: 'twitter:image', content: `${siteUrl}/images/og-default.jpg` },
  ],
  link: [{ rel: 'canonical', href: `${siteUrl}/about` }],
})

const staffs = ref([])
const isLoading = ref(false)
const loadError = ref('')

// États de gestion de positionnement dynamique
const isFixed = ref(false)
const isBottomed = ref(false)

const mainLayout = ref(null)
const contentColumn = ref(null)
const sidebarContainer = ref(null)

const handleScroll = () => {
  if (!mainLayout.value || !contentColumn.value || !sidebarContainer.value) return
  if (window.innerWidth <= 1024) {
    isFixed.value = false
    isBottomed.value = false
    return
  }

  const layoutRect = mainLayout.value.getBoundingClientRect()
  const contentRect = contentColumn.value.getBoundingClientRect()
  const sidebarHeight = sidebarContainer.value.offsetHeight

  const topSpacing = 24

  // 1. Gestion du déclenchement du mode fixe (Haut de l'écran)
  if (layoutRect.top <= topSpacing) {
    isFixed.value = true
  } else {
    isFixed.value = false
  }

  // 2. Alignement et blocage au bas de la colonne de contenu (Empêche le dépassement)
  const maxTopAllowed = contentRect.bottom - sidebarHeight
  if (topSpacing >= maxTopAllowed) {
    isFixed.value = false
    isBottomed.value = true
  } else {
    isBottomed.value = false
  }
}

const sortedStaffs = computed(() =>
  [...staffs.value].sort((a, b) => {
    const orderA = a.order ?? 999999
    const orderB = b.order ?? 999999

    if (orderA !== orderB) return orderA - orderB

    return (a.name || '').localeCompare(b.name || '', 'fr')
  })
)

const getStaffTypeLabel = (s) => {
  const name = `${s.name || ''} ${s.initials || ''}`.toLowerCase()

  if (name.includes('minist')) return 'MINISTÈRE'
  if (
    name.includes('état-major général') ||
    name.includes('etat-major general') ||
    name.includes('emga')
  ) {
    return 'ÉTAT-MAJOR GÉNÉRAL'
  }

  if (name.includes('état-major') || name.includes('etat-major')) {
    return 'ÉTAT-MAJOR'
  }

  if (name.includes('direction') || name.startsWith('d')) {
    return 'DIRECTION'
  }

  if (name.includes('service')) return 'SERVICE'

  return 'STRUCTURE'
}

const fetchStaffs = async () => {
  isLoading.value = true
  loadError.value = ''

  try {
    const res = await fetch('/api/public/staffs', {
      headers: { Accept: 'application/json' },
    })

    if (!res.ok) {
      loadError.value = 'Impossible de charger les structures.'
      return
    }

    staffs.value = await res.json()
  } catch (e) {
    loadError.value = 'Erreur réseau. Veuillez réessayer plus tard.'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchStaffs()
  window.addEventListener('scroll', handleScroll, { passive: true })
  window.addEventListener('resize', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('resize', handleScroll)
})
</script>

<template>
  <div class="about-page">
    <div ref="mainLayout" class="main-layout container">
      <section ref="contentColumn" class="content-column">
        <section class="hero-premium">
          <div class="hero-content">
            <div class="hero-text-block">
              <div class="hero-topline">
                République du Mali • Défense nationale
              </div>

              <h1 class="hero-title">
                Forces Armées Maliennes
              </h1>

              <p class="hero-text">
                Les Forces Armées Maliennes constituent l’outil de défense de
                l’État. Elles participent à la protection de la souveraineté
                nationale, à la défense du territoire, à la sécurisation des
                populations et à l’exécution des missions militaires décidées
                par les autorités compétentes.
              </p>

              <div class="hero-chips">
                <span>Défense nationale</span>
                <span>Souveraineté</span>
                <span>Commandement</span>
                <span>Service de la Nation</span>
              </div>
            </div>

            <div class="hero-image-card">
              <img :src="aboutImg1" alt="Forces Armées Maliennes" />
            </div>
          </div>
        </section>

        <section class="section-box intro-box">
          <div class="section-head">
            <h2>Présentation générale</h2>
            <div class="section-line"></div>
          </div>

          <div class="intro-grid">
            <div class="intro-content">
              <p>
                Les Forces Armées Maliennes regroupent des structures de
                commandement, des directions, des services, des organismes
                spécialisés et des entités d’appui chargés de la planification,
                de la coordination, de l’administration, du soutien et de la
                conduite des opérations.
              </p>

              <p>
                Leur organisation repose sur la discipline, la continuité du
                commandement, l’efficacité opérationnelle et le respect des
                principes républicains.
              </p>
            </div>

            <div class="intro-image">
              <img
                :src="aboutImg2"
                alt="Organisation institutionnelle des FAMa"
              />
            </div>
          </div>
        </section>

        <section class="section-box missions-box">
          <div class="section-head">
            <h2>Missions essentielles</h2>
            <div class="section-line"></div>
          </div>

          <div class="mission-grid">
            <div class="mission-item">
              <div class="mission-number">01</div>

              <div class="mission-body">
                <h3>Défense du territoire</h3>

                <p>
                  Assurer l’intégrité du territoire national et contribuer à la
                  protection de la souveraineté de l’État.
                </p>
              </div>
            </div>

            <div class="mission-item">
              <div class="mission-number">02</div>

              <div class="mission-body">
                <h3>Protection des populations</h3>

                <p>
                  Participer à la sécurisation des populations, des institutions
                  et des espaces stratégiques.
                </p>
              </div>
            </div>

            <div class="mission-item">
              <div class="mission-number">03</div>

              <div class="mission-body">
                <h3>Appui au commandement</h3>

                <p>
                  Structurer, coordonner et soutenir les opérations à travers
                  les structures de commandement, directions et services.
                </p>
              </div>
            </div>
          </div>
        </section>

        <section class="section-box org-box">
          <div class="section-head">
            <h2>Organisation institutionnelle</h2>
            <div class="section-line"></div>
          </div>

          <p class="org-text">
            L’organisation institutionnelle de la défense comprend plusieurs
            niveaux : ministère, état-major général, états-majors, directions,
            services techniques, structures d’appui et organismes spécialisés.
          </p>

          <div class="org-tags">
            <Tag value="Ministère" severity="success" rounded />
            <Tag value="État-major général" severity="success" rounded />
            <Tag value="États-majors" severity="info" rounded />
            <Tag value="Directions" severity="warning" rounded />
            <Tag value="Services" severity="secondary" rounded />
          </div>
        </section>

        <section class="section-box structures-box">
          <div class="section-head">
            <h2>Structures institutionnelles</h2>
            <div class="section-line"></div>
          </div>

          <p class="staff-intro">
            Cette section présente les structures institutionnelles de la
            défense : ministère, état-major général, états-majors, directions,
            services et structures spécialisées.
          </p>

          <div v-if="loadError" class="error-box">
            {{ loadError }}
          </div>

          <div v-else-if="isLoading" class="staff-grid compact-grid">
            <Card
              v-for="i in 12"
              :key="i"
              class="staff-card compact-card"
            >
              <template #content>
                <div class="staff-row">
                  <Skeleton shape="circle" size="3rem" />

                  <div class="staff-col">
                    <Skeleton width="85%" height="0.9rem" class="mb-2" />
                    <Skeleton width="50%" height="0.8rem" class="mb-2" />
                    <Skeleton width="60%" height="0.8rem" />
                  </div>
                </div>
              </template>
            </Card>
          </div>

          <div v-else class="staff-grid compact-grid">
            <RouterLink
              v-for="s in sortedStaffs"
              :key="s.id"
              :to="`/etat-major/${s.slug}`"
              class="staff-link"
            >
              <Card class="staff-card compact-card premium-card">
                <template #content>
                  <div class="staff-row">
                    <div class="staff-thumb" v-if="s.logo">
                      <img :src="`/storage/${s.logo}`" :alt="s.name" />
                    </div>

                    <div class="staff-thumb fallback" v-else>
                      {{ (s.initials || 'FAMa').slice(0, 4) }}
                    </div>

                    <div class="staff-col">
                      <div class="staff-top">
                        <Tag
                          :value="getStaffTypeLabel(s)"
                          severity="success"
                          rounded
                          class="mini-tag"
                        />
                      </div>

                      <h3 class="staff-name" :title="s.name">
                        {{ s.name }}
                      </h3>

                      <div class="staff-subline">
                        {{ s.initials || 'Structure institutionnelle' }}
                      </div>

                      <div class="staff-meta">
                        <span class="meta-k">Responsable :</span>

                        <span class="meta-v">
                          {{ s.leader_name || 'Non renseigné' }}
                        </span>
                      </div>

                      <div class="staff-meta">
                        <span class="meta-k">Grade / Fonction :   <span class="meta-v">
                          {{ s.leader_rank || 'Non renseigné' }}
                        </span></span>


                      </div>
                    </div>
                  </div>
                </template>
              </Card>
            </RouterLink>
          </div>
        </section>
      </section>

      <!-- Colonne réservoir latérale équilibrée -->
      <aside class="sidebar-wrapper-column">
        <div
          ref="sidebarContainer"
          :class="[
            'js-dynamic-sidebar',
            { 'is-fixed': isFixed, 'is-bottomed': isBottomed }
          ]"
        >
          <SidebarOfficial />
        </div>
      </aside>
    </div>
  </div>
</template>

<style scoped>
.about-page {
  min-height: 100vh;
  padding: 36px 0 50px;
  background: #f4f7f5;
}

.container {
  max-width: 1320px;
  margin: 0 auto;
  padding: 0 16px;
}

.main-layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 320px;
  gap: 28px;
  align-items: start;
  position: relative; /* Point de repère absolu pour le blocage au bas */
}

.content-column {
  background: #ffffff;
  border-radius: 22px;
  padding: 28px;
  border: 1px solid rgba(15, 23, 42, 0.06);
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
}

/* Base de la colonne de droite */
.sidebar-wrapper-column {
  width: 320px;
  height: 100%;
}

/* Comportement initial standard */
.js-dynamic-sidebar {
  width: 320px;
  height: fit-content;
}

/* Étape 1 : Mode fixe au scroll global */
.js-dynamic-sidebar.is-fixed {
  position: fixed;
  top: 24px;
  width: 320px;
  z-index: 90;
}

/* Étape 2 : Fige l'alignement tout en bas pour ne jamais dépasser le conteneur principal */
.js-dynamic-sidebar.is-bottomed {
  position: absolute;
  bottom: 0;
  top: auto;
  width: 320px;
}

.hero-premium {
  position: relative;
  border-radius: 22px;
  overflow: hidden;
  margin-bottom: 26px;
  background:
    linear-gradient(
      135deg,
      rgba(18, 43, 25, 0.98),
      rgba(15, 23, 42, 0.96)
    );
}

.hero-content {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 24px;
  align-items: center;
  padding: 34px;
}

.hero-topline {
  color: rgba(255, 215, 0, 0.86);
  text-transform: uppercase;
  letter-spacing: 1.2px;
  font-size: 0.76rem;
  font-weight: 800;
  margin-bottom: 14px;
}

.hero-title {
  margin: 0 0 16px;
  color: #ffffff;
  font-size: clamp(2rem, 4vw, 3.1rem);
  line-height: 1.08;
  font-weight: 950;
}

.hero-text {
  max-width: 850px;
  margin: 0;
  color: rgba(255, 255, 255, 0.9);
  line-height: 1.85;
  font-size: 1rem;
}

.hero-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 22px;
}

.hero-chips span {
  background: rgba(255, 255, 255, 0.09);
  color: #ffffff;
  border: 1px solid rgba(255, 215, 0, 0.18);
  border-radius: 999px;
  padding: 8px 14px;
  font-size: 0.78rem;
  font-weight: 800;
}

.hero-image-card {
  height: 280px;
  border-radius: 18px;
  overflow: hidden;
  border: 1px solid rgba(255, 215, 0, 0.24);
  box-shadow: 0 18px 35px rgba(0, 0, 0, 0.22);
}

.hero-image-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.section-box {
  margin-top: 24px;
  padding: 24px;
  border-radius: 20px;
  border: 1px solid rgba(15, 23, 42, 0.07);
  background: #ffffff;
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04);
}

.section-head h2 {
  margin: 0;
  font-size: 1.35rem;
  color: #0f172a;
  font-weight: 950;
}

.section-line {
  width: 80px;
  height: 4px;
  border-radius: 999px;
  margin-top: 10px;
  background: #14b82c;
}

.intro-grid {
  display: grid;
  grid-template-columns: 1fr 280px;
  gap: 24px;
  align-items: center;
  margin-top: 18px;
}

.intro-content p,
.org-text,
.staff-intro {
  color: #334155;
  line-height: 1.85;
  margin: 0 0 14px;
}

.intro-image {
  height: 220px;
  border-radius: 16px;
  overflow: hidden;
  background: #f1f5f9;
}

.intro-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.mission-grid {
  display: grid;
  gap: 14px;
  margin-top: 18px;
}

.mission-item {
  display: grid;
  grid-template-columns: 56px 1fr;
  gap: 14px;
  padding: 16px;
  border-radius: 16px;
  background: #f8fafc;
  border: 1px solid rgba(15, 23, 42, 0.06);
}

.mission-number {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  font-weight: 950;
  color: #0f7a21;
  background: rgba(20, 184, 44, 0.1);
}

.mission-body h3 {
  margin: 0 0 6px;
  font-size: 1rem;
  font-weight: 950;
  color: #0f172a;
}

.mission-body p {
  margin: 0;
  color: #475569;
  font-size: 0.94rem;
  line-height: 1.65;
}

.org-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 16px;
}

.compact-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 18px;
}

.staff-link {
  display: block;
  text-decoration: none;
  width: 100%;
}

.premium-card {
  border: 1px solid rgba(15, 23, 42, 0.08);
  background: #ffffff;
  transition: all 0.22s ease;
  padding: 12px 20px;
  border-radius: 16px;
}

.premium-card:hover {
  transform: translateY(-2px);
  border-color: rgba(20, 184, 44, 0.28);
  box-shadow: 0 12px 25px rgba(15, 23, 42, 0.08);
}

.staff-row {
  display: grid;
  grid-template-columns: 52px 1fr;
  gap: 12px;
  align-items: start;
}

.staff-thumb {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  overflow: hidden;
  background: #f1f5f9;
  border: 1px solid rgba(20, 184, 44, 0.18);
  flex-shrink: 0;
}

.staff-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.staff-thumb.fallback {
  display: grid;
  place-items: center;
  font-size: 0.78rem;
  font-weight: 950;
  color: #14b82c;
  background: #f0fdf4;
}

.staff-top {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  flex-wrap: wrap;
}

.staff-name {
  margin: 0 0 4px;
  font-size: 0.9rem;
  font-weight: 950;
  color: #0f172a;
  text-transform: uppercase;
  line-height: 1.35;
}

.staff-subline {
  margin-bottom: 7px;
  color: #64748b;
  font-size: 0.76rem;
  font-weight: 850;
  letter-spacing: 0.03em;
  text-transform: uppercase;
}

.staff-meta {
  font-size: 0.78rem;
  margin-top: 2px;
  line-height: 1.45;
}

.meta-k {
  color: #64748b;
  font-weight: 850;
  margin-right: 4px;
}

.meta-v {
  color: #1e293b;
  font-weight: 750;
}

.error-box {
  margin-top: 18px;
  padding: 14px 16px;
  border-radius: 14px;
  background: #fef2f2;
  color: #991b1b;
  font-weight: 700;
}

/* ================= DARK MODE ================= */

:global(html.dark) .about-page {
  background: #263827;
}

:global(html.dark) .content-column {
  background: #2d392e;
  border-color: rgba(255, 215, 0, 0.08);
  box-shadow: none;
}

:global(html.dark) .section-box {
  background: #243125;
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow: none;
}

:global(html.dark) .section-head h2 {
  color: #f8fafc;
}

:global(html.dark) .section-line {
  background: #14b82c;
}

:global(html.dark) .intro-content p,
:global(html.dark) .org-text,
:global(html.dark) .staff-intro,
:global(html.dark) .mission-body p {
  color: #dbe4dc;
}

:global(html.dark) .mission-item {
  background: #2d392e;
  border-color: rgba(255, 255, 255, 0.08);
}

:global(html.dark) .mission-body h3 {
  color: #ffffff;
}

:global(html.dark) .mission-number {
  color: #22c55e;
  background: rgba(34, 197, 94, 0.12);
}

:global(html.dark) .premium-card {
  background: #2d392e !important;
  border: 1px solid rgba(255, 255, 255, 0.08) !important;
  box-shadow: none !important;
}

:global(html.dark) .premium-card:hover {
  border-color: rgba(34, 197, 94, 0.35) !important;
  box-shadow: 0 12px 25px rgba(0, 0, 0, 0.18) !important;
}

:global(html.dark) .premium-card :deep(.p-card) {
  background: transparent !important;
  box-shadow: none !important;
}

:global(html.dark) .premium-card :deep(.p-card-body),
:global(html.dark) .premium-card :deep(.p-card-content) {
  background: transparent !important;
  padding: 0 !important;
}

:global(html.dark) .staff-name {
  color: #ffffff;
}

:global(html.dark) .staff-subline {
  color: #cbd5e1;
}

:global(html.dark) .meta-k {
  color: #a8b5aa;
}

:global(html.dark) .meta-v {
  color: #f8fafc;
}

:global(html.dark) .staff-thumb {
  background: #1f2c21;
  border-color: rgba(255, 255, 255, 0.12);
}

:global(html.dark) .staff-thumb.fallback {
  color: #22c55e;
  background: rgba(34, 197, 94, 0.12);
}

:global(html.dark) .mini-tag,
:global(html.dark) .org-tags :deep(.p-tag) {
  background: rgba(34, 197, 94, 0.14) !important;
  color: #bbf7d0 !important;
  border: 1px solid rgba(34, 197, 94, 0.22) !important;
  box-shadow: none !important;
}

:global(html.dark) :deep(.p-tag) {
  box-shadow: none !important;
}

:global(html.dark) :deep(.p-card) {
  background: transparent !important;
  box-shadow: none !important;
}

@media (max-width: 1024px) {
  .main-layout {
    grid-template-columns: 1fr;
  }

  .sidebar-wrapper-column,
  .js-dynamic-sidebar {
    width: 100% !important;
  }

  .hero-content,
  .intro-grid {
    grid-template-columns: 1fr;
  }

  .hero-image-card {
    height: 240px;
  }

  .js-dynamic-sidebar.is-fixed,
  .js-dynamic-sidebar.is-bottomed {
    position: static !important;
    width: 100% !important;
  }
}

@media (max-width: 700px) {
  .about-page {
    padding: 0;
  }

  .container {
    padding: 0;
  }

  .content-column {
    border-radius: 0;
    padding: 16px;
  }

  .hero-content {
    padding: 24px 18px;
  }

  .hero-image-card {
    height: 210px;
  }

  .section-box {
    padding: 18px;
  }

  .mission-item {
    grid-template-columns: 1fr;
  }

  .mission-number {
    width: 48px;
    height: 48px;
  }

  .premium-card {
    padding: 12px 14px;
  }

  .staff-row {
    grid-template-columns: 46px 1fr;
    gap: 10px;
  }

  .staff-thumb {
    width: 46px;
    height: 46px;
    border-radius: 12px;
  }

  .staff-name {
    font-size: 0.82rem;
  }

  .staff-subline,
  .staff-meta {
    font-size: 0.73rem;
  }
}
</style>
