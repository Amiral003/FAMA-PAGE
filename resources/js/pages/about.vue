<script setup>
import { ref, onMounted, computed } from 'vue'
import { useHead } from '@vueuse/head'
import { RouterLink } from 'vue-router'
import SidebarOfficial from '@/components/SidebarOfficial.vue'

import Card from 'primevue/card'
import Tag from 'primevue/tag'
import Skeleton from 'primevue/skeleton'
import Button from 'primevue/button'

useHead({
  title: 'À propos | FAMa - Portail Officiel des Forces Armées Maliennes',
  meta: [
    {
      name: 'description',
      content:
        'Présentation officielle des Forces Armées Maliennes, de leurs missions, de leur organisation institutionnelle et des différentes structures de commandement.'
    }
  ]
})

const staffs = ref([])
const isLoading = ref(false)
const loadError = ref('')

const sortedStaffs = computed(() =>
  [...staffs.value].sort((a, b) => (a.order ?? 0) - (b.order ?? 0))
)

const fetchStaffs = async () => {
  isLoading.value = true
  loadError.value = ''

  try {
    const res = await fetch('/api/public/staffs', {
      headers: { Accept: 'application/json' }
    })

    if (!res.ok) {
      loadError.value = "Impossible de charger les structures."
      return
    }

    staffs.value = await res.json()
  } catch (e) {
    loadError.value = "Erreur réseau. Veuillez réessayer plus tard."
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchStaffs)
</script>

<template>
  <div class="about-page">
    <div class="main-layout container">

      <!-- CONTENU -->
      <section class="content-column">

        <!-- HERO PREMIUM -->
        <section class="hero-premium">
          <div class="hero-overlay"></div>

          <div class="hero-content">
            <div class="hero-topline">République du Mali • Forces Armées Maliennes</div>

            <h1 class="hero-title">
              Institution militaire au service de la souveraineté nationale
            </h1>

            <p class="hero-text">
              Les Forces Armées Maliennes assurent la défense de l’intégrité territoriale,
              la protection des institutions républicaines, la sécurisation des populations
              et l’exécution des missions militaires décidées par l’État.
            </p>

            <div class="hero-chips">
              <span>Défense</span>
              <span>Commandement</span>
              <span>Organisation</span>
              <span>Souveraineté</span>
            </div>
          </div>
        </section>

        <!-- INTRO -->
        <section class="section-box intro-box">
          <div class="section-head">
            <h2>Présentation générale</h2>
            <div class="section-line"></div>
          </div>

          <div class="intro-content">
            <p>
              Les Forces Armées Maliennes constituent l’outil principal de défense de l’État.
              Elles regroupent des structures de commandement, des directions, des services,
              ainsi que des entités spécialisées chargées de la planification, de la coordination,
              de l’administration, du soutien et de la conduite des opérations.
            </p>

            <p>
              Leur organisation répond à une logique de discipline, d’efficacité et de continuité
              du commandement, afin de garantir l’exécution des missions militaires dans le respect
              des principes républicains et des intérêts supérieurs de la Nation.
            </p>
          </div>
        </section>

        <!-- MISSIONS -->
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
                <p>Assurer l’intégrité du territoire national et la protection de la souveraineté de l’État.</p>
              </div>
            </div>

            <div class="mission-item">
              <div class="mission-number">02</div>
              <div class="mission-body">
                <h3>Protection des populations</h3>
                <p>Contribuer à la sécurisation des populations, des institutions et des espaces sensibles.</p>
              </div>
            </div>

            <div class="mission-item">
              <div class="mission-number">03</div>
              <div class="mission-body">
                <h3>Organisation du commandement</h3>
                <p>Structurer, coordonner et appuyer les opérations à travers les états-majors, directions et services.</p>
              </div>
            </div>
          </div>
        </section>

        <!-- ORGANISATION -->
        <section class="section-box org-box">
          <div class="section-head">
            <h2>Organisation institutionnelle</h2>
            <div class="section-line"></div>
          </div>

          <p class="org-text">
            L’organisation militaire s’appuie sur plusieurs structures hiérarchisées :
            états-majors, directions, services techniques, structures d’appui, organismes spécialisés
            et entités relevant du ministère en charge de la Défense.
          </p>

          <div class="org-tags">
            <Tag value="États-majors" severity="success" rounded />
            <Tag value="Directions" severity="warning" rounded />
            <Tag value="Services militaires" severity="danger" rounded />
            <Tag value="Structures rattachées" severity="info" rounded />
          </div>
        </section>

        <!-- STAFFS -->
        <section class="section-box staffs-box">
          <div class="section-head">
            <h2>États-majors, directions et services</h2>
            <div class="section-line"></div>
          </div>

          <p class="staff-intro">
            Les structures ci-dessous sont alimentées dynamiquement depuis l’administration du portail.
          </p>

          <div v-if="loadError" class="error-box">
            {{ loadError }}
          </div>

          <div v-else-if="isLoading" class="staff-grid compact-grid">
            <Card v-for="i in 12" :key="i" class="staff-card compact-card">
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
                      {{ (s.initials || 'EM').slice(0, 4) }}
                    </div>

                    <div class="staff-col">
                      <div class="staff-top">
                        <Tag
                          :value="s.initials || 'STRUCTURE'"
                          severity="success"
                          rounded
                          class="mini-tag"
                        />
                      </div>

                      <h3 class="staff-name" :title="s.name">
                        {{ s.name }}
                      </h3>

                      <div class="staff-meta">
                        <span class="meta-k">Chef :</span>
                        <span class="meta-v">{{ s.leader_name || 'Non renseigné' }}</span>
                      </div>

                      <div class="staff-meta">
                        <span class="meta-k">Grade :</span>
                        <span class="meta-v">{{ s.leader_rank || '—' }}</span>
                      </div>
                    </div>

                  </div>
                </template>
              </Card>
            </RouterLink>
          </div>
        </section>

      </section>

      <!-- SIDEBAR -->
      <aside class="sidebar-column">
        <SidebarOfficial />
      </aside>

    </div>
  </div>
</template>

<style scoped>
.about-page {
  min-height: 100vh;
  padding: 36px 0 50px;
  background:
    radial-gradient(circle at top right, rgba(20,184,44,0.06), transparent 20%),
    linear-gradient(to bottom, #f8fafc 0%, #eef2f7 100%);
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
}

.content-column {
  background: #fff;
  border-radius: 20px;
  padding: 28px;
  border: 1px solid rgba(15, 23, 42, 0.06);
  box-shadow: 0 16px 38px rgba(15, 23, 42, 0.08);
}

/* HERO PREMIUM */
.hero-premium {
  position: relative;
  border-radius: 20px;
  overflow: hidden;
  margin-bottom: 26px;
  background:
    linear-gradient(120deg, rgba(7,14,23,0.95), rgba(15,23,42,0.92)),
    linear-gradient(90deg, #14B82C 0%, #FFD700 50%, #CE1126 100%);
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at top right, rgba(255,255,255,0.12), transparent 30%),
    linear-gradient(to right, rgba(20,184,44,0.08), rgba(255,215,0,0.05), rgba(206,17,38,0.08));
}

.hero-content {
  position: relative;
  z-index: 2;
  padding: 34px 30px;
}

.hero-topline {
  color: rgba(255,255,255,0.75);
  text-transform: uppercase;
  letter-spacing: 1.2px;
  font-size: 0.76rem;
  font-weight: 700;
  margin-bottom: 14px;
}

.hero-title {
  margin: 0 0 16px;
  color: #fff;
  font-size: clamp(2rem, 4vw, 3rem);
  line-height: 1.08;
  font-weight: 900;
  max-width: 900px;
}

.hero-text {
  max-width: 850px;
  margin: 0;
  color: rgba(255,255,255,0.92);
  line-height: 1.9;
  font-size: 1rem;
}

.hero-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 20px;
}

.hero-chips span {
  background: rgba(255,255,255,0.1);
  color: #fff;
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 999px;
  padding: 8px 14px;
  font-size: 0.78rem;
  font-weight: 700;
}

/* BLOCS */
.section-box {
  margin-top: 24px;
  padding: 22px 22px 20px;
  border-radius: 18px;
  border: 1px solid rgba(15,23,42,0.06);
  background: linear-gradient(to bottom right, #ffffff, #f8fafc);
}

.section-head {
  margin-bottom: 16px;
}

.section-head h2 {
  margin: 0;
  font-size: 1.35rem;
  color: #0f172a;
  font-weight: 900;
}

.section-line {
  width: 90px;
  height: 4px;
  border-radius: 999px;
  margin-top: 10px;
  background: linear-gradient(90deg, #14B82C, #FFD700, #CE1126);
}

.intro-content p,
.org-text,
.staff-intro {
  color: #334155;
  line-height: 1.85;
  margin: 0 0 14px;
}

.intro-content p:last-child {
  margin-bottom: 0;
}

/* MISSIONS */
.mission-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 14px;
}

.mission-item {
  display: grid;
  grid-template-columns: 56px 1fr;
  gap: 14px;
  align-items: start;
  padding: 14px;
  border-radius: 14px;
  background: #fff;
  border: 1px solid rgba(15,23,42,0.05);
}

.mission-number {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  font-weight: 900;
  font-size: 0.95rem;
  background: rgba(20,184,44,0.08);
  color: #14B82C;
}

.mission-body h3 {
  margin: 0 0 6px;
  font-size: 1rem;
  font-weight: 900;
  color: #0f172a;
}

.mission-body p {
  margin: 0;
  color: #475569;
  line-height: 1.7;
  font-size: 0.94rem;
}

/* ORG */
.org-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 16px;
}

/* STAFFS COMPACTS */
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

.compact-card {
  border-radius: 16px;
}

.premium-card {
    width: 100%;
  border: 1px solid rgba(15,23,42,0.08);
  background: linear-gradient(to bottom right, #ffffff, #f8fafc);
  transition: all 0.22s ease;
    padding: 12px 20px;
}

.premium-card:hover {
  transform: translateY(-3px);
  border-color: rgba(20,184,44,0.22);
  box-shadow: 0 14px 24px rgba(15,23,42,0.07);
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
  border: 1px solid rgba(20,184,44,0.18);
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
  font-weight: 900;
  color: #14B82C;
  background: #f0fdf4;
}

.staff-col {
  min-width: 0;
}

.staff-top {
  margin-bottom: 8px;
}

.mini-tag {
  font-size: 0.68rem;
  font-weight: 800;
}

.staff-name {
  margin: 0 0 8px;
  font-size: 0.82rem;
  font-weight: 900;
  color: #0f172a;
  text-transform: uppercase;
  line-height: 1.45;
  letter-spacing: 0.25px;

  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.staff-meta {
  font-size: 0.76rem;
  line-height: 1.55;
  margin-top: 2px;
}

.meta-k {
  color: #64748b;
  font-weight: 800;
  margin-right: 4px;
}

.meta-v {
  color: #1e293b;
  font-weight: 700;
}

/* ERROR */
.error-box {
  margin-top: 16px;
  padding: 12px 14px;
  border-radius: 12px;
  background: rgba(206,17,38,0.08);
  color: #991b1b;
  border: 1px solid rgba(206,17,38,0.25);
  font-weight: 800;
}

/* SIDEBAR */
.sidebar-column {
  position: sticky;
  top: 20px;
  align-self: start;
}

/* RESPONSIVE */
@media (max-width: 1200px) {
  .compact-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 1024px) {
  .main-layout {
    grid-template-columns: 1fr;
  }

  .sidebar-column {
    display: none;
  }
}

@media (max-width: 700px) {
  .compact-grid {
    grid-template-columns: 1fr;
  }

  .content-column {
    padding: 18px;
  }

  .hero-content {
    padding: 24px 20px;
  }
}
</style>
