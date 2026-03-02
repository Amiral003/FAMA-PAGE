<script setup>
import { ref, onMounted, computed } from 'vue'
import { useHead } from '@vueuse/head'
import { RouterLink } from 'vue-router'
import SidebarOfficial from '@/components/SidebarOfficial.vue'

import Card from 'primevue/card'
import Tag from 'primevue/tag'
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'

useHead({
  title: 'À propos | FAMa - Portail Officiel des Forces Armées Maliennes',
  meta: [
    {
      name: 'description',
      content:
        'Découvrez l’histoire, les missions et l’organisation des Forces Armées Maliennes (FAMa), ainsi que les différents États-Majors et leurs attributions.'
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
      loadError.value = "Impossible de charger la liste des États-Majors."
      return
    }
    staffs.value = await res.json()
  } catch (e) {
    loadError.value = "Erreur réseau. Vérifie ta connexion."
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchStaffs)
</script>

<template>
  <div class="about-container">
    <div class="main-layout container">

      <section class="content-column">
        <header class="about-header">
          <h1>À propos des Forces Armées Maliennes</h1>
          <div class="header-line"></div>
        </header>

        <article class="about-text">
          <p class="lead">
            Les Forces Armées Maliennes constituent le rempart de la souveraineté nationale.
            Elles regroupent des structures de commandement chargées de la planification,
            de la coordination et de la conduite des opérations.
          </p>

          <section class="text-block command-section">
            <h3>États-Majors et structures de commandement</h3>
            <p>
              Cliquez sur un État-Major pour consulter sa présentation détaillée
              ainsi que ses informations officielles.
            </p>

            <div v-if="loadError" class="error-box">
              {{ loadError }}
            </div>

            <div v-else class="staff-grid">

              <RouterLink
                v-for="s in sortedStaffs"
                :key="s.id"
                :to="`/etat-major/${s.slug}`"
                class="staff-card-link"
              >
                <Card class="staff-card">
                  <template #content>
                    <div class="staff-row">

                      <!-- Logo -->
                      <div class="staff-logo" v-if="s.logo">
                        <img :src="`/storage/${s.logo}`" :alt="s.name" />
                      </div>
                      <div class="staff-logo placeholder" v-else>
                        {{ (s.initials || 'EM').slice(0, 4) }}
                      </div>

                      <!-- Infos -->
                      <div class="staff-main">

                        <!-- TITRE MAÎTRISÉ -->
                        <div class="staff-name" :title="s.name">
                          {{ s.name }}
                        </div>

                    

                        <div class="staff-meta">
                          <span class="meta-label">Chef :</span>
                          <span class="meta-value">
                            {{ s.leader_name || 'Non renseigné' }}
                          </span>
                        </div>

                        <div class="staff-meta">
                          <span class="meta-label">Grade :</span>
                          <span class="meta-value">
                            {{ s.leader_rank || '—' }}
                          </span>
                        </div>

                      </div>

                    </div>
                  </template>
                </Card>
              </RouterLink>

            </div>
          </section>
        </article>
      </section>

      <aside class="sidebar-column">
        <SidebarOfficial />
      </aside>

    </div>
  </div>
</template>

<style scoped>
.about-container { background: #f8fafc; padding: 40px 0; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }

.main-layout { display: grid; grid-template-columns: 1fr 340px; gap: 40px; }

.content-column {
  background: white;
  padding: 45px;
  border-radius: 12px;
  border-top: 6px solid #14B82C;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

/* TITRES */
.about-header h1 {
  font-size: 2.2rem;
  font-weight: 900;
  color: #1a241b;
}

.header-line {
  width: 80px;
  height: 6px;
  background: #FFD700;
  margin: 20px 0 30px;
  border-radius: 3px;
}

.lead {
  font-size: 1.05rem;
  background: #f0fdf4;
  padding: 12px 18px;
  border-left: 5px solid #14B82C;
  font-weight: 600;
}

/* GRID */
.staff-grid {
  margin-top: 20px;
  display: grid;
  gap: 14px;
}

/* LIEN CARTE */
.staff-card-link {
  text-decoration: none;
}

/* CARTE */
.staff-card {
  border: 1px solid rgba(0,0,0,0.08);
  border-radius: 14px;
  transition: all 0.2s ease;
  position: relative;
}

.staff-card::before {
  content: "";
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: rgba(20,184,44,0.6);
  border-radius: 14px 0 0 14px;
}

.staff-card:hover {
  box-shadow: 0 12px 28px rgba(0,0,0,0.08);
  border-color: rgba(20,184,44,0.3);
  transform: translateY(-2px);
}

/* LAYOUT INTERNE */
.staff-row {
  display: grid;
  grid-template-columns: 70px 1fr;
  gap: 16px;
  align-items: center;
}

/* LOGO */
.staff-logo {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  overflow: hidden;
  background: #f0fdf4;
  display: grid;
  place-items: center;
  font-weight: 900;
  color: #14B82C;
  border: 1px solid rgba(20,184,44,0.3);
}

.staff-logo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* TITRE LONG MAÎTRISÉ */
.staff-name {
  font-size: 0.95rem;
  font-weight: 900;
  color: #0f172a;
  text-transform: uppercase;
  letter-spacing: 0.5px;

  display: -webkit-box;
  -webkit-line-clamp: 2;        /* max 2 lignes */
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* TAG */
.staff-tag {
  margin-top: 6px;
  font-weight: 900;
  border-radius: 999px;
}

/* META */
.staff-meta {
  font-size: 0.85rem;
  margin-top: 6px;
}

.meta-label {
  font-weight: 900;
  text-transform: uppercase;
  font-size: 0.7rem;
  color: #64748b;
  margin-right: 6px;
}

.meta-value {
  font-weight: 700;
}

.error-box {
  margin-top: 15px;
  padding: 12px;
  border-radius: 10px;
  background: rgba(206,17,38,0.08);
  border: 1px solid rgba(206,17,38,0.25);
  font-weight: 800;
  color: #a00d1d;
}

.sidebar-column { position: sticky; top: 20px; }

@media (max-width: 992px) {
  .main-layout { grid-template-columns: 1fr; }
  .sidebar-column { display: none; }
}
</style>