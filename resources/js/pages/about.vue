<script setup>
import { ref, onMounted, computed } from 'vue'
import { useHead } from '@unhead/vue'
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

    <div class="main-layout container">

      <section class="content-column">

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

       <div class="section-head">
            <h2>Missions essentielles</h2>
            <div class="section-line"></div>
          </div>
          <br>
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


      <aside class="sidebar-column">
        <SidebarOfficial />
      </aside>


  </div>
</template>

<style scoped>
.rich-text-content {
  /* Min: 1rem, Idéal: 2.5vw de la largeur d'écran, Max: 1.2rem */
  font-size: clamp(1rem, 0.9rem + 0.5vw, 1.2rem);
  line-height: 1.8;
  color: var(--text-muted, #475569);
  margin-bottom: 50px;
  word-break: break-word; /* Évite que les longs mots dépassent */
}
/* ==========================================
   1. STYLES DE BASE (MODE CLAIR)
   ========================================== */
.about-page {
  min-height: 100vh;
  padding: 36px 0 50px;
  background: linear-gradient(to bottom, #f8fafc 0%, #eef2f7 100%);
  transition: all 0.3s ease;
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

.hero-premium {
  position: relative;
  border-radius: 20px;
  overflow: hidden;
  margin-bottom: 26px;
  background: linear-gradient(120deg, rgba(7,14,23,0.95), rgba(15,23,42,0.92)),
              linear-gradient(90deg, #14B82C 0%, #FFD700 50%, #CE1126 100%);
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at top right, rgba(255,255,255,0.12), transparent 30%),
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

.section-box {
  margin-top: 24px;
  padding: 22px 22px 20px;
  border-radius: 18px;
  border: 1px solid rgba(15,23,42,0.06);
  background: linear-gradient(to bottom right, #ffffff, #f8fafc);
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

.intro-content p, .org-text, .staff-intro {
  color: #334155;
  line-height: 1.85;
  margin: 0 0 14px;
}

.mission-grid {
  display: grid;
  gap: 14px;
}

.mission-item {
  display: grid;
  grid-template-columns: 56px 1fr;
  gap: 14px;
  padding: 14px;
  border-radius: 14px;
  background: #fff;
  border: 1px solid rgba(15,23,42,0.05);
}

.mission-number {
  width: 56px; height: 56px;
  border-radius: 14px;
  display: grid; place-items: center;
  font-weight: 900; color: #14B82C;
  background: rgba(20,184,44,0.08);
}

.mission-body h3 { margin: 0 0 6px; font-size: 1rem; font-weight: 900; color: #0f172a; }
.mission-body p { margin: 0; color: #475569; font-size: 0.94rem; }

.org-tags { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; }

.compact-grid { display: flex; flex-direction: column; gap: 12px; margin-top: 18px; }
.staff-link { display: block; text-decoration: none; width: 100%; }

.premium-card {
  border: 1px solid rgba(15,23,42,0.08);
  background: linear-gradient(to bottom right, #ffffff, #f8fafc);
  transition: all 0.22s ease;
  padding: 12px 20px;
  border-radius: 16px;
}

.premium-card:hover {
  transform: translateY(-3px);
  border-color: rgba(20,184,44,0.22);
}

.staff-row { display: grid; grid-template-columns: 52px 1fr; gap: 12px; align-items: start; }
.staff-thumb {
  width: 52px; height: 52px; border-radius: 14px; overflow: hidden;
  background: #f1f5f9; border: 1px solid rgba(20,184,44,0.18);
}
.staff-thumb img { width: 100%; height: 100%; object-fit: cover; }
.staff-thumb.fallback { display: grid; place-items: center; font-size: 0.78rem; font-weight: 900; color: #14B82C; background: #f0fdf4; }

.staff-name { margin: 0 0 8px; font-size: 0.82rem; font-weight: 900; color: #0f172a; text-transform: uppercase; }
.staff-meta { font-size: 0.76rem; margin-top: 2px; }
.meta-k { color: #64748b; font-weight: 800; margin-right: 4px; }
.meta-v { color: #1e293b; font-weight: 700; }

.sidebar-column { position: sticky; top: 20px; align-self: start; }

/* ==========================================
   3. RESPONSIVE
   ========================================== */
   /* Dans ta section @media (max-width: 768px) */
@media (max-width: 768px) {
  .rich-text-content {
    font-size: 1.05rem; /* On réduit un peu pour le mobile */
    line-height: 1.6;    /* On resserre un peu l'interligne */
  }
}
@media (max-width: 1200px) { .compact-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 1024px) { .main-layout { grid-template-columns: 1fr; } .sidebar-column { display: none; } }
@media (max-width: 700px) { .compact-grid { grid-template-columns: 1fr; } .content-column { padding: 18px; } }
</style>

<style>

/* 1. Fond de page : Vert militaire très profond (au lieu du noir) */
html.dark, html.dark body {
  background-color: #334a36 !important; /* Un vert très sombre */
  color: #334a36!important;
}
/* ON FORCE LE LOOK TACTIQUE SUR LES TROIS BLOCS QUE TU AS CITÉS */
html.dark .intro-box,
html.dark .missions-box,
html.dark .org-box,
html.dark .staffs-box {
    /* On remplace le blanc par ton vert militaire profond */
    background: linear-gradient(135deg, #2d392e 0%, #213024 100%) !important;

    /* On ajoute la bordure Or/Vert pour le côté Institutionnel */
    border: 1px solid rgba(255, 215, 0, 0.2) !important;

    /* On ajoute l'ombre portée pour décoller le bloc du fond */
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4) !important;

    /* On s'assure que le contenu respire */
    padding: 30px !important;
    border-radius: 18px !important;
}

/* ON CORRIGE LES TITRES À L'INTÉRIEUR DE CES BLOCS */
html.dark .intro-box h2,
html.dark .missions-box h2,
html.dark .org-box h2 {
    color: #ffd700 !important; /* Or FAMa */
    text-transform: uppercase;
    font-weight: 900;
}

/* ON CORRIGE LES TEXTES */
html.dark .intro-box p,
html.dark .org-box .org-text,
html.dark .missions-box .mission-body p {
    color: #e2e8f0 !important; /* Blanc cassé lisible */
}

/* LES PETITES LIGNES SOUS LES TITRES */
html.dark .section-line {
    background: linear-gradient(90deg, #14B82C, #FFD700) !important;
    height: 3px !important;
}
html.dark .about-page {
  background: #334a36!important;
  background-image: radial-gradient(circle at top right, rgba(20,184,44,0.1), transparent 25%),
                    linear-gradient(to bottom, #334a36 0%, #334a36 d 100%) !important;
}

/* 2. Colonne de contenu : Un vert légèrement différent pour contraster */
html.dark .content-column {
  background-color: #334a36 !important; /* Un vert encore plus profond */
  border-color: rgba(255, 255, 255, 0.05) !important;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4) !important;
}

/* 3. Blocs de section : Vert militaire moyen */
html.dark .section-box {
  background: linear-gradient(to bottom right, #2d392e  #213024) !important;
  border-color: rgba(20, 184, 44, 0.2) !important; /* Bordure verte pour l'effet tactique */
}

/* 4. Titres et textes : C'est ici qu'on met ton OR FAMa */
html.dark .section-head h2,
html.dark .staff-name,
html.dark h1,
html.dark h3 {
  color: #ffd700 !important; /* Or pur */
}

/* 5. Paragraphes : Blanc cassé pour la lisibilité */
html.dark .intro-content p,
html.dark .org-text,
html.dark .staff-intro,
html.dark .mission-body p {
  color: #e2e8f0 !important;
}

/* 6. Cartes & Missions : Fond vert militaire, bordure or pour le lookpremium */
html.dark .mission-item,
html.dark .premium-card {
  background-color: #2d392e !important;
  border-color: rgba(255, 215, 0, 0.3) !important; /* Bordure or transparente */
}

/* 7. Métadonnées : Labels gris clair, valeurs en VERT FAMa brillant */
html.dark .meta-k { color: #a0aec0 !important; }
html.dark .meta-v { color: #14B82C !important; }

/* 8. Vignettes Staff : Fond vert, bordure verte */
html.dark .staff-thumb {
  background-color: #2d392e important;
  border-color: #14B82C !important;
}

/* Correction PrimeVue & Skeletons */
html.dark .p-card {
  background-color: #2d392e  !important;
  border-color: rgba(255, 215, 0, 0.3) !important;
}

html.dark .p-skeleton {
  background-color: rgba(255, 255, 255, 0.03) !important;
}
/* On force le vert militaire profond sur TOUT ce qui essaie d'être noir ou gris très sombre */
html.dark .bg-black,
html.dark .bg-gray-950,
html.dark .p-card,
html.dark section {
  background-color: #2d392e  !important; /* Ton vert militaire profond exact */
}
</style>
