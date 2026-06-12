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
  script: [
    {
      type: 'application/ld+json',
      children: JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'GovernmentOrganization',
        name: 'Forces Armées Maliennes',
        alternateName: 'FAMa',
        url: `${siteUrl}/about`,
        description: pageDescription,
      }),
    },
  ],
})

const staffs = ref([])
const isLoading = ref(false)
const loadError = ref('')

const isFixed = ref(false)
const isBottomed = ref(false)

const mainLayout = ref(null)
const contentColumn = ref(null)
const sidebarContainer = ref(null)

const sortByOrder = (items) => {
  return [...items].sort((a, b) => {
    const orderA = Number(a?.order ?? 9999)
    const orderB = Number(b?.order ?? 9999)

    if (orderA !== orderB) return orderA - orderB

    return String(a?.name ?? '').localeCompare(String(b?.name ?? ''), 'fr')
  })
}

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

  isFixed.value = layoutRect.top <= topSpacing

  const maxTopAllowed = contentRect.bottom - sidebarHeight

  if (topSpacing >= maxTopAllowed) {
    isFixed.value = false
    isBottomed.value = true
  } else {
    isBottomed.value = false
  }
}

const getStaffTypeLabel = (s) => {
  const name = `${s?.name || ''} ${s?.initials || ''}`.toLowerCase()

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
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
    })

    if (!res.ok) {
      loadError.value = 'Impossible de charger les structures.'
      return
    }

    const data = await res.json()
    staffs.value = Array.isArray(data) ? data : []
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

const presidencyStaff = computed(() => {
  if (!Array.isArray(staffs.value)) return []

  return sortByOrder(
    staffs.value.filter((s) => s && s.initials === 'PR')
  )
})

const mdacParent = computed(() => {
  if (!Array.isArray(staffs.value)) return null

  return staffs.value.find((s) => s && s.initials === 'MDAC') || null
})

const emgaParent = computed(() => {
  if (!Array.isArray(staffs.value)) return null

  return staffs.value.find((s) => s && s.initials === 'EMGA') || null
})

const mdacChildren = computed(() => {
  if (!Array.isArray(staffs.value)) return []

  const mdac = mdacParent.value
  if (!mdac?.id) return []

  return sortByOrder(
    staffs.value.filter((s) => {
      return (
        s &&
        Number(s.parent_staff_id) === Number(mdac.id) &&
        s.initials !== 'EMGA'
      )
    })
  )
})

const emgaChildren = computed(() => {
  if (!Array.isArray(staffs.value)) return []

  const emga = emgaParent.value
  if (!emga?.id) return []

  return sortByOrder(
    staffs.value.filter((s) => {
      return s && Number(s.parent_staff_id) === Number(emga.id)
    })
  )
})

const otherStaffs = computed(() => {
  if (!Array.isArray(staffs.value)) return []

  const mdacId = mdacParent.value?.id
  const emgaId = emgaParent.value?.id

  return sortByOrder(
    staffs.value.filter((s) => {
      return (
        s &&
        s.initials !== 'PR' &&
        s.initials !== 'MDAC' &&
        s.initials !== 'EMGA' &&
        Number(s.parent_staff_id) !== Number(mdacId) &&
        Number(s.parent_staff_id) !== Number(emgaId)
      )
    })
  )
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
              <img
                :src="aboutImg1"
                alt="Forces Armées Maliennes"
                loading="eager"
                decoding="async"
              />
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
                spécialisés et des entités d'appui chargés de la planification,
                de la coordination, de l'administration, du soutien et de la
                conduite des opérations.
              </p>

              <p>
                Leur organisation repose sur la discipline, la continuité du
                commandement, l'efficacité opérationnelle et le respect des
                principes républicains.
              </p>
            </div>

            <div class="intro-image">
              <img
                :src="aboutImg2"
                alt="Organisation institutionnelle des FAMa"
                loading="lazy"
                decoding="async"
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
                  Assurer l'intégrité du territoire national et contribuer à la
                  protection de la souveraineté de l'État.
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
            L'organisation institutionnelle de la défense comprend plusieurs
            niveaux : ministère, état-major général, états-majors, directions,
            services techniques, structures d'appui et organismes spécialisés.
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
            Organisation hiérarchique de la Défense nationale et des Forces Armées.
          </p>

          <div v-if="loadError" class="error-box">
            {{ loadError }}
          </div>

          <div v-else-if="isLoading" class="compact-grid">
            <Card v-for="i in 4" :key="i" class="staff-card compact-card">
              <template #content>
                <div class="staff-row">
                  <Skeleton shape="circle" size="3rem" />
                  <div class="staff-col">
                    <Skeleton width="85%" height="0.9rem" class="mb-2" />
                    <Skeleton width="50%" height="0.8rem" />
                  </div>
                </div>
              </template>
            </Card>
          </div>

          <div v-else class="hierarchy-container">
            <div v-if="presidencyStaff.length" class="hierarchy-group">


              <div class="compact-grid">
                <RouterLink
                  v-for="s in presidencyStaff"
                  :key="s.id"
                  :to="`/etat-major/${s.slug}`"
                  class="staff-link"
                >
                  <Card class="staff-card compact-card premium-card parent-card presidency-card">
                    <template #content>
                      <div class="staff-row">
                        <div class="staff-thumb" v-if="s.logo">
                          <img
                            :src="`/storage/${s.logo}`"
                            :alt="s.name"
                            loading="lazy"
                            decoding="async"
                          />
                        </div>

                        <div class="staff-thumb fallback" v-else>
                          {{ (s.initials || 'FAMa') }}
                        </div>

                        <div class="staff-col">
                          <Tag :value="s.initials" severity="success" rounded class="mini-tag" />

                          <h3 class="staff-name">{{ s.name }}</h3>



                          <div class="staff-meta" v-if="s.leader_name">
                            <span class="meta-k">
                              {{ s.leader_name }}
                              <span v-if="s.leader_rank" class="leader-rank">
                                ({{ s.leader_rank }})
                              </span>
                            </span>
                          </div>
                        </div>
                      </div>
                    </template>
                  </Card>
                </RouterLink>
              </div>
            </div>

            <div v-if="mdacParent" class="org-tree-container">


              <div class="parent-node">
                <RouterLink :to="`/etat-major/${mdacParent.slug}`" class="staff-link">
                  <Card class="staff-card compact-card premium-card parent-card">
                    <template #content>
                      <div class="staff-row">
                        <div class="staff-thumb" v-if="mdacParent.logo">
                          <img
                            :src="`/storage/${mdacParent.logo}`"
                            :alt="mdacParent.name"
                            loading="lazy"
                            decoding="async"
                          />
                        </div>

                        <div class="staff-thumb fallback" v-else>
                          {{ mdacParent.initials }}
                        </div>

                        <div class="staff-col">
                          <Tag :value="mdacParent.initials" severity="success" rounded class="mini-tag" />

                          <h3 class="staff-name">{{ mdacParent.name }}</h3>



                          <div class="staff-meta" v-if="mdacParent.leader_name">
                            <span class="meta-k">
                              {{ mdacParent.leader_name }}
                              <span v-if="mdacParent.leader_rank" class="leader-rank">
                                ({{ mdacParent.leader_rank }})
                              </span>
                            </span>
                          </div>
                        </div>
                      </div>
                    </template>
                  </Card>
                </RouterLink>
              </div>

              <div v-if="mdacChildren.length" class="children-tree">
                <div
                  v-for="s in mdacChildren"
                  :key="s.id"
                  class="child-node"
                >
                  <RouterLink :to="`/etat-major/${s.slug}`" class="staff-link">
                    <Card class="staff-card compact-card child-card">
                      <template #content>
                        <div class="staff-row">
                          <div class="staff-thumb" v-if="s.logo">
                            <img
                              :src="`/storage/${s.logo}`"
                              :alt="s.name"
                              loading="lazy"
                              decoding="async"
                            />
                          </div>

                          <div class="staff-thumb fallback" v-else>
                            {{ (s.initials || 'FAMa') }}
                          </div>

                          <div class="staff-col">
                            <Tag :value="s.initials" severity="success" rounded class="mini-tag" />
                            <h3 class="staff-name">{{ s.name }}</h3>
                          </div>
                        </div>
                      </template>
                    </Card>
                  </RouterLink>
                </div>
              </div>
            </div>

            <div v-if="emgaParent" class="org-tree-container">


              <div class="parent-node">
                <RouterLink :to="`/etat-major/${emgaParent.slug}`" class="staff-link">
                  <Card class="staff-card compact-card premium-card parent-card">
                    <template #content>
                      <div class="staff-row">
                        <div class="staff-thumb" v-if="emgaParent.logo">
                          <img
                            :src="`/storage/${emgaParent.logo}`"
                            :alt="emgaParent.name"
                            loading="lazy"
                            decoding="async"
                          />
                        </div>

                        <div class="staff-thumb fallback" v-else>
                          {{ emgaParent.initials }}
                        </div>

                        <div class="staff-col">
                          <Tag :value="emgaParent.initials" severity="success" rounded class="mini-tag" />

                          <h3 class="staff-name">{{ emgaParent.name }}</h3>



                          <div class="staff-meta" v-if="emgaParent.leader_name">
                            <span class="meta-k">
                              {{ emgaParent.leader_name }}
                              <span v-if="emgaParent.leader_rank" class="leader-rank">
                                ({{ emgaParent.leader_rank }})
                              </span>
                            </span>
                          </div>
                        </div>
                      </div>
                    </template>
                  </Card>
                </RouterLink>
              </div>

              <div v-if="emgaChildren.length" class="children-tree">
                <div
                  v-for="s in emgaChildren"
                  :key="s.id"
                  class="child-node"
                >
                  <RouterLink :to="`/etat-major/${s.slug}`" class="staff-link">
                    <Card class="staff-card compact-card child-card">
                      <template #content>
                        <div class="staff-row">
                          <div class="staff-thumb" v-if="s.logo">
                            <img
                              :src="`/storage/${s.logo}`"
                              :alt="s.name"
                              loading="lazy"
                              decoding="async"
                            />
                          </div>

                          <div class="staff-thumb fallback" v-else>
                            {{ (s.initials || 'FAMa') }}
                          </div>

                          <div class="staff-col">
                            <Tag :value="s.initials" severity="success" rounded class="mini-tag" />
                            <h3 class="staff-name">{{ s.name }}</h3>
                          </div>
                        </div>
                      </template>
                    </Card>
                  </RouterLink>
                </div>
              </div>
            </div>

            <div v-if="otherStaffs.length" class="hierarchy-group">
              <div class="hierarchy-title-border">
                Autres organismes
              </div>

              <div class="compact-grid">
                <RouterLink
                  v-for="s in otherStaffs"
                  :key="s.id"
                  :to="`/etat-major/${s.slug}`"
                  class="staff-link"
                >
                  <Card class="staff-card compact-card child-card">
                    <template #content>
                      <div class="staff-row">
                        <div class="staff-thumb" v-if="s.logo">
                          <img
                            :src="`/storage/${s.logo}`"
                            :alt="s.name"
                            loading="lazy"
                            decoding="async"
                          />
                        </div>

                        <div class="staff-thumb fallback" v-else>
                          {{ (s.initials || 'FAMa') }}
                        </div>

                        <div class="staff-col">
                          <Tag :value="s.initials" severity="success" rounded class="mini-tag" />
                          <h3 class="staff-name">{{ s.name }}</h3>
                        </div>
                      </div>
                    </template>
                  </Card>
                </RouterLink>
              </div>
            </div>
          </div>
        </section>
      </section>

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
  position: relative;
}

.content-column {
  min-width: 0;
  overflow: hidden;
  background: #ffffff;
  border-radius: 22px;
  padding: 28px;
  border: 1px solid rgba(15, 23, 42, 0.06);
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
}

.sidebar-wrapper-column {
  width: 320px;
  height: 100%;
}

.js-dynamic-sidebar {
  width: 320px;
  height: fit-content;
}

.js-dynamic-sidebar.is-fixed {
  position: fixed;
  top: 24px;
  width: 320px;
  z-index: 90;
}

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
  background: linear-gradient(135deg, rgba(31, 78, 44, 0.98), rgba(57, 27, 64, 0.96));
}

.hero-content {
  display: grid;
  grid-template-columns: minmax(0, 1.2fr) minmax(240px, 0.8fr);
  gap: 24px;
  align-items: center;
  padding: 34px;
}

.hero-text-block {
  min-width: 0;
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
  overflow-wrap: anywhere;
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

.hero-image-card img,
.intro-image img,
.staff-thumb img {
  width: 100%;
  height: 100%;
}

.hero-image-card img,
.intro-image img {
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
  grid-template-columns: minmax(0, 1fr) 280px;
  gap: 24px;
  align-items: center;
  margin-top: 18px;
}

.intro-content {
  min-width: 0;
}

.intro-content p,
.org-text,
.staff-intro {
  color: #334155;
  line-height: 1.85;
  margin: 0 0 14px;
}

.staff-intro {
  margin-top: 14px;
}

.intro-image {
  height: 220px;
  border-radius: 16px;
  overflow: hidden;
  background: #f1f5f9;
}

.mission-grid {
  display: grid;
  gap: 14px;
  margin-top: 18px;
}

.mission-item {
  display: grid;
  grid-template-columns: 56px minmax(0, 1fr);
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

.mission-body {
  min-width: 0;
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

.hierarchy-container {
  display: flex;
  flex-direction: column;
  gap: 34px;
  margin-top: 24px;
  max-width: 100%;
  overflow: hidden;
}

.hierarchy-title-border {
  margin-bottom: 12px;
  color: #64748b;
  font-size: 0.74rem;
  font-weight: 950;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.compact-grid {
  display: flex;
  flex-direction: column;
  gap: 14px;
  max-width: 100%;
}

.staff-link {
  display: block;
  width: 100%;
  max-width: 100%;
  text-decoration: none;
}

.staff-card {
  width: 100%;
  max-width: 100%;
  overflow: hidden;
}

.compact-card {
  border-radius: 16px;
}

.compact-card :deep(.p-card-body) {
  padding: 0;
}

.compact-card :deep(.p-card-content) {
  padding: 0;
}

.staff-row {
  display: flex;
  gap: 16px;
  align-items: flex-start;
  width: 100%;
  min-width: 0;
}

.staff-thumb {
  width: 90px;
  height: 90px;
  flex: 0 0 90px;
  border-radius: 0;
  overflow: hidden;
  background: transparent;
  border: transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 6px;
}

.staff-thumb img {
  width: auto;
  height: auto;
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.staff-thumb.fallback {
  font-size: 0.9rem;
  font-weight: 900;
  color: #40a15e;
}

.staff-col {
  flex: 1;
  min-width: 0;
  max-width: 100%;
}

.staff-name {
  margin: 4px 0 4px;
  font-size: 0.9rem;
  font-weight: 950;
  color: #ebedf2;
  text-transform: uppercase;
  line-height: 1.35;
  overflow-wrap: anywhere;
  word-break: normal;
  hyphens: auto;
}

.staff-type {
  display: inline-block;
  margin-top: 2px;
  color: #64748b;
  font-size: 0.68rem;
  font-weight: 900;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.staff-meta {
  font-size: 0.78rem;
  margin-top: 6px;
  line-height: 1.45;
  overflow-wrap: anywhere;
}

.meta-k {
  color: #e3c6a9 !important;
  font-weight: 850;
  margin-right: 4px;
}

.leader-rank {
  color: #e2e8f0;
  font-size: 0.72rem;
  font-weight: 600;
}

.mini-tag {
  max-width: 100%;
  font-size: 0.7rem !important;
  font-weight: 800 !important;
  padding: 4px 10px !important;
  background: rgba(20, 184, 44, 0.15) !important;
  color: #14b82c !important;
  border: none !important;
  display: inline-flex !important;
  width: fit-content !important;
}

.premium-card {
  border: 1px solid rgba(15, 23, 42, 0.08);
  background: linear-gradient(135deg, #40a15e, #1a4629);
  transition: all 0.22s ease;
  padding: 14px 20px;
  border-radius: 16px;
}

.premium-card:hover {
  transform: translateY(-2px);
  border-color: rgba(20, 184, 44, 0.28);
  box-shadow: 0 12px 25px rgba(15, 23, 42, 0.08);
}

.parent-card {
  background: linear-gradient(135deg, #40a15e, #1a4629);
  margin-bottom: 14px;
}

.parent-card .staff-name {
  color: #ffffff !important;
}

.parent-card .staff-type {
  color: rgba(255, 255, 255, 0.74);
}

.parent-card .meta-k {
  color: #e3c6a9 !important;
}

.parent-card .mini-tag {
  background: rgba(255, 255, 255, 0.2) !important;
  color: #ffffff !important;
}

.presidency-card {
  border-left: 4px solid #f59e0b !important;
}

.child-card {
  background: #ffffff !important;
  border: 1px solid #e2e8f0 !important;
  padding: 12px 16px;
  transition: all 0.2s ease;
}

.child-card:hover {
  border-color: rgba(20, 184, 44, 0.35) !important;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.07);
  transform: translateY(-1px);
}

.child-card .staff-name {
  color: #0f172a !important;
}

.child-card .mini-tag {
  background: rgba(20, 184, 44, 0.15) !important;
  color: #14b82c !important;
}

.org-tree-container {
  position: relative;
  max-width: 100%;
  overflow: hidden;
}

.parent-node {
  position: relative;
  max-width: 100%;
}

.children-tree {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-top: 12px;
  padding-left: 28px;
  max-width: 100%;
}

.child-node {
  position: relative;
  width: 100%;
  max-width: 100%;
}

.child-node::before {
  content: "";
  position: absolute;
  top: 50%;
  left: -28px;
  width: 28px;
  height: 3px;
  background: #40a15e;
  border-radius: 3px;
  transform: translateY(-50%);
}

.child-node::after {
  content: "";
  position: absolute;
  left: -28px;
  top: -14px;
  width: 3px;
  height: calc(100% + 14px);
  background: #40a15e;
  border-radius: 3px;
}

.child-node:first-child::after {
  top: -26px;
  height: calc(100% + 26px);
}

.child-node:last-child::after {
  height: calc(50% + 14px);
}

.child-node:first-child:last-child::after {
  top: -26px;
  height: calc(50% + 26px);
}

.error-box {
  margin-top: 18px;
  padding: 14px 16px;
  border-radius: 14px;
  background: #fef2f2;
  color: #991b1b;
  font-weight: 700;
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

@media (max-width: 900px) {
  .children-tree {
    gap: 16px;
    padding-left: 22px;
    margin-top: 16px;
  }

  .child-node::before {
    left: -22px;
    width: 22px;
  }

  .child-node::after {
    left: -22px;
  }

  .staff-row {
    flex-direction: row;
    align-items: center;
    gap: 16px;
  }

  .staff-thumb {
    width: 70px;
    height: 70px;
    flex-basis: 70px;
  }

  .staff-name {
    font-size: 0.85rem;
  }

  .mini-tag {
    font-size: 0.65rem !important;
    padding: 3px 8px !important;
  }
}

@media (max-width: 768px) {
  .about-page {
    padding: 20px 0;
  }

  .content-column {
    padding: 16px;
  }

  .section-box {
    padding: 16px;
    margin-top: 16px;
  }

  .section-head h2 {
    font-size: 1.2rem;
  }

  .section-line {
    width: 60px;
    height: 3px;
  }

  .children-tree {
    padding-left: 16px;
    gap: 12px;
  }

  .child-node::before {
    left: -16px;
    width: 16px;
    height: 2px;
  }

  .child-node::after {
    left: -16px;
    width: 2px;
  }

  .staff-row {
    gap: 9px;
    align-items: center;
  }

  .staff-thumb {
    width: 55px;
    height: 55px;
    flex-basis: 55px;
  }

  .staff-name {
    font-size: 0.75rem;
    margin-bottom: 2px;
  }

  .staff-type {
    font-size: 0.58rem;
  }

  .staff-meta {
    font-size: 0.7rem;
  }

  .mini-tag {
    font-size: 0.6rem !important;
    padding: 2px 6px !important;
    margin-bottom: 4px !important;
  }

  .parent-card {
    padding: 12px !important;
  }

  .parent-card .staff-name {
    font-size: 0.8rem;
  }

  .child-card {
    padding: 10px !important;
  }

  .compact-grid {
    gap: 8px;
  }
}

@media (max-width: 480px) {
  .staff-row {
    gap: 10px;
  }

  .staff-thumb {
    width: 50px;
    height: 50px;
    flex-basis: 50px;
  }

  .staff-name {
    font-size: 0.7rem;
  }

  .staff-meta {
    font-size: 0.65rem;
  }

  .mini-tag {
    font-size: 0.55rem !important;
    padding: 2px 5px !important;
  }

  .children-tree {
    padding-left: 12px;
  }

  .child-node::before {
    left: -12px;
    width: 12px;
  }

  .child-node::after {
    left: -12px;
  }
}
</style>
