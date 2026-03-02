<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useHead } from '@vueuse/head'
import SidebarOfficial from '@/components/SidebarOfficial.vue'

const route = useRoute()
const slug = computed(() => String(route.params.slug || ''))

const staff = ref(null)
const isLoading = ref(false)
const loadError = ref('')
const notFound = ref(false)

// Form contact ciblé staff
const form = ref({
  name: '',
  email: '',
  subject: '',
  message: '',
})

const isSending = ref(false)
const sent = ref(false)
const errorMsg = ref('')

const socials = computed(() => {
  // contact_socials peut être JSON (objet) ou string JSON selon comment tu le stockes/édites
  const v = staff.value?.contact_socials
  if (!v) return null
  if (typeof v === 'object') return v
  try {
    return JSON.parse(v)
  } catch {
    return null
  }
})

useHead(() => ({
  title: staff.value ? `${staff.value.initials || 'État-Major'} | FAMa` : 'État-Major | FAMa',
  meta: [
    {
      name: 'description',
      content: staff.value?.description
        ? staff.value.description.slice(0, 160)
        : "Présentation d’un État-Major des Forces Armées Maliennes (FAMa) et informations de contact officielles.",
    },
  ],
}))

const fetchStaff = async () => {
  isLoading.value = true
  loadError.value = ''
  notFound.value = false
  staff.value = null

  try {
    const res = await fetch(`/api/public/staffs/${encodeURIComponent(slug.value)}`, {
      headers: { Accept: 'application/json' },
    })

    if (res.status === 404) {
      notFound.value = true
      return
    }

    if (!res.ok) {
      loadError.value = "Impossible de charger cette page pour le moment."
      return
    }

    staff.value = await res.json()
  } catch (e) {
    loadError.value = "Erreur réseau. Vérifie ta connexion."
  } finally {
    isLoading.value = false
  }
}

const handleSubmit = async () => {
  errorMsg.value = ''
  sent.value = false
  isSending.value = true

  try {
    const res = await fetch('/api/public/contact', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify({
        staff_slug: slug.value,
        name: form.value.name,
        email: form.value.email,
        subject: form.value.subject,
        message: form.value.message,
      }),
    })

    if (res.status === 422) {
      const data = await res.json().catch(() => null)
      const firstError = data?.errors ? Object.values(data.errors)?.[0]?.[0] : null
      errorMsg.value = firstError || "Certains champs sont invalides. Vérifie le formulaire."
      return
    }

    if (res.status === 429) {
      errorMsg.value = "Trop de tentatives. Réessaie dans quelques minutes."
      return
    }

    if (!res.ok) {
      errorMsg.value = "Erreur serveur. Réessaie plus tard."
      return
    }

    sent.value = true
    form.value = { name: '', email: '', subject: '', message: '' }
  } catch (e) {
    errorMsg.value = "Impossible de contacter le serveur. Vérifie ta connexion."
  } finally {
    isSending.value = false
  }
}

onMounted(fetchStaff)

watch(
  () => slug.value,
  () => {
    // changement de slug si navigation interne
    fetchStaff()
    // reset form state
    sent.value = false
    errorMsg.value = ''
  }
)
</script>

<template>
  <div class="page-container">
    <div class="main-layout container">
      <section class="content-column">
        <div class="breadcrumb">
          <RouterLink to="/about">À propos</RouterLink>
          <span class="sep">/</span>
          <span>État-major</span>
        </div>

        <div v-if="isLoading" class="notice-box">Chargement...</div>

        <div v-else-if="notFound" class="error-box">
          <h2>Introuvable</h2>
          <p>Cette fiche d’État-Major n’existe pas ou a été retirée.</p>
          <RouterLink class="btn-link" to="/about">Retour à la liste</RouterLink>
        </div>

        <div v-else-if="loadError" class="error-box">
          <h2>Erreur</h2>
          <p>{{ loadError }}</p>
          <button class="btn-link" @click="fetchStaff">Réessayer</button>
        </div>

        <article v-else class="staff-article">
          <header class="staff-header">
            <div class="staff-hero">
              <div class="logo" v-if="staff?.logo">
                <img :src="`/storage/${staff.logo}`" :alt="staff.name" />
              </div>
              <div class="logo placeholder" v-else>
                {{ (staff?.initials || 'EM').slice(0, 4) }}
              </div>

              <div class="hero-text">
                <h1 class="title">{{ staff?.name }}</h1>
                <p class="subtitle">
                  <span class="badge">{{ staff?.initials }}</span>
                  <span v-if="staff?.motto" class="motto">— “{{ staff.motto }}”</span>
                </p>
              </div>
            </div>
          </header>

          <section class="grid-2">
            <div class="card">
              <h3>Présentation</h3>
              <p v-if="staff?.description" class="text">{{ staff.description }}</p>
              <p v-else class="muted">Aucune description n’est disponible pour le moment.</p>
            </div>

            <div class="card">
              <h3>Commandement</h3>

              <div class="leader">
                <div class="leader-photo" v-if="staff?.leader_photo">
                  <img :src="`/storage/${staff.leader_photo}`" :alt="staff.leader_name || 'Chef d’État-Major'" />
                </div>
                <div class="leader-photo placeholder" v-else>—</div>

                <div class="leader-meta">
                  <p class="leader-rank">{{ staff?.leader_rank || '—' }}</p>
                  <p class="leader-name">{{ staff?.leader_name || 'Chef non renseigné' }}</p>
                </div>
              </div>

              <p v-if="staff?.leader_word" class="quote">“{{ staff.leader_word }}”</p>
              <p v-else class="muted">Message du chef non renseigné.</p>
            </div>
          </section>

          <section class="card">
            <h3>Missions et attributions</h3>
            <p v-if="staff?.missions" class="text">{{ staff.missions }}</p>
            <p v-else class="muted">Aucune mission n’est renseignée pour le moment.</p>
          </section>

          <section class="grid-2">
            <!-- Bloc contact officiel -->
            <div class="card">
              <h3>Contacts officiels</h3>

              <div class="contact-list">
                <div class="contact-item" v-if="staff?.contact_address">
                  <span class="icon">📍</span>
                  <div>
                    <div class="label">Adresse</div>
                    <div class="value">{{ staff.contact_address }}</div>
                  </div>
                </div>

                <div class="contact-item" v-if="staff?.contact_phone">
                  <span class="icon">📞</span>
                  <div>
                    <div class="label">Téléphone</div>
                    <div class="value">{{ staff.contact_phone }}</div>
                  </div>
                </div>

                <div class="contact-item" v-if="staff?.contact_hotline">
                  <span class="icon">☎️</span>
                  <div>
                    <div class="label">Numéro vert</div>
                    <div class="value">{{ staff.contact_hotline }}</div>
                  </div>
                </div>

                <div class="contact-item" v-if="staff?.contact_email">
                  <span class="icon">📧</span>
                  <div>
                    <div class="label">Email</div>
                    <div class="value">
                      <a :href="`mailto:${staff.contact_email}`">{{ staff.contact_email }}</a>
                    </div>
                  </div>
                </div>

                <div class="contact-item" v-if="staff?.contact_hours">
                  <span class="icon">🕘</span>
                  <div>
                    <div class="label">Horaires</div>
                    <div class="value">{{ staff.contact_hours }}</div>
                  </div>
                </div>
              </div>

              <div v-if="staff?.contact_map_url" class="map-link">
                <a :href="staff.contact_map_url" target="_blank" rel="noopener">Voir sur la carte →</a>
              </div>

              <div v-if="socials" class="socials">
                <div class="label">Réseaux officiels</div>
                <div class="chips">
                  <a
                    v-for="(url, key) in socials"
                    :key="key"
                    class="chip"
                    :href="url"
                    target="_blank"
                    rel="noopener"
                  >
                    {{ String(key).toUpperCase() }}
                  </a>
                </div>
              </div>

              <p v-if="!staff?.contact_email && !staff?.contact_phone && !staff?.contact_address" class="muted">
                Les informations de contact ne sont pas encore renseignées.
              </p>
            </div>

            <!-- Formulaire contact ciblé -->
            <div class="card">
              <h3>Envoyer un message</h3>
              <p class="muted">
                Votre message sera transmis à l’État-Major concerné. Merci de rester factuel et précis.
              </p>

              <form v-if="!sent" class="contact-form" @submit.prevent="handleSubmit">
                <div class="form-group">
                  <label>Nom complet</label>
                  <input v-model="form.name" type="text" required placeholder="Ex: Leila Diallo" />
                </div>

                <div class="form-group">
                  <label>Email</label>
                  <input v-model="form.email" type="email" required placeholder="ex: didi@gmail.com" />
                </div>

                <div class="form-group">
                  <label>Sujet</label>
                  <select v-model="form.subject" required>
                    <option value="" disabled>Choisissez un sujet</option>
                    <option value="information">Demande d'information</option>
                    <option value="recrutement">Question recrutement</option>
                    <option value="presse">Espace presse</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Message</label>
                  <textarea
                    v-model="form.message"
                    rows="5"
                    required
                    placeholder="Votre message..."
                  ></textarea>
                </div>

                <p v-if="errorMsg" class="error-message">{{ errorMsg }}</p>

                <button class="btn-send" type="submit" :disabled="isSending">
                  {{ isSending ? 'Envoi en cours...' : 'Envoyer le message' }}
                </button>
              </form>

              <div v-else class="success-message">
                <div class="check-icon">✓</div>
                <h4>Message envoyé</h4>
                <p class="muted">Nous reviendrons vers vous dans les plus brefs délais.</p>
                <button class="btn-link" @click="sent = false; errorMsg = ''">
                  Envoyer un autre message
                </button>
              </div>
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
.page-container { background: #f8fafc; min-height: 100vh; padding: 40px 0; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }

/* Layout */
.main-layout { display: grid; grid-template-columns: 1fr 340px; gap: 40px; }
.sidebar-column { position: sticky; top: 20px; height: fit-content; }

.content-column{
  background: white;
  padding: 45px;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
  border-top: 6px solid #14B82C;
}

.breadcrumb{
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 18px;
  color: #64748b;
  font-weight: 700;
}
.breadcrumb a{ color: #14B82C; text-decoration: none; }
.breadcrumb a:hover{ text-decoration: underline; }
.sep{ opacity: 0.6; }

/* Notice/errors */
.notice-box{
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  padding: 12px 14px;
  border-radius: 10px;
  font-weight: 700;
  color: #1a241b;
}
.error-box{
  background: rgba(206, 17, 38, 0.08);
  border: 1px solid rgba(206, 17, 38, 0.25);
  color: #a00d1d;
  padding: 16px;
  border-radius: 12px;
}
.error-box h2{ margin: 0 0 8px; }

/* Header */
.staff-hero{
  display: flex;
  gap: 14px;
  align-items: center;
  margin-bottom: 22px;
}
.logo{
  width: 70px;
  height: 70px;
  border-radius: 14px;
  overflow: hidden;
  background: #f0fdf4;
  display: grid;
  place-items: center;
  font-weight: 900;
  color: #14B82C;
  border: 1px solid rgba(20,184,44,0.25);
}
.logo img{ width: 100%; height: 100%; object-fit: cover; }
.logo.placeholder{ letter-spacing: 0.8px; text-transform: uppercase; }

.title{
  margin: 0;
  font-size: 2rem;
  font-weight: 900;
  color: #1a241b;
}
.subtitle{
  margin: 8px 0 0;
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}
.badge{
  background: #1a241b;
  color: #fff;
  padding: 6px 10px;
  border-radius: 999px;
  font-weight: 900;
  letter-spacing: 0.7px;
}
.motto{ color: #334155; font-weight: 800; }

/* Cards */
.grid-2{
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.card{
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 16px;
}
.card h3{
  margin: 0 0 10px;
  color: #14B82C;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  font-size: 1.05rem;
}
.text{ color: #334155; }
.muted{ color: #64748b; font-weight: 600; }

/* Leader */
.leader{
  display: flex;
  gap: 12px;
  align-items: center;
  margin-bottom: 10px;
}
.leader-photo{
  width: 54px;
  height: 54px;
  border-radius: 12px;
  overflow: hidden;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  display: grid;
  place-items: center;
}
.leader-photo img{ width: 100%; height: 100%; object-fit: cover; }
.leader-meta{ line-height: 1.2; }
.leader-rank{ margin: 0; font-weight: 900; color: #1a241b; }
.leader-name{ margin: 4px 0 0; font-weight: 700; color: #334155; }
.quote{ margin-top: 10px; font-style: italic; color: #334155; }

/* Contact list */
.contact-list{ display: grid; gap: 12px; margin-top: 10px; }
.contact-item{
  display: flex;
  gap: 10px;
  align-items: flex-start;
}
.icon{ font-size: 1.2rem; }
.label{ font-weight: 900; color: #1a241b; }
.value{ color: #334155; font-weight: 700; }
.value a{ color: #ce1126; text-decoration: none; }
.value a:hover{ text-decoration: underline; }

.map-link{ margin-top: 12px; }
.map-link a{ color: #14B82C; font-weight: 900; text-decoration: none; }
.map-link a:hover{ text-decoration: underline; }

.socials{ margin-top: 14px; }
.chips{ display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px; }
.chip{
  display: inline-block;
  padding: 6px 10px;
  border-radius: 999px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  font-weight: 900;
  text-decoration: none;
  color: #1a241b;
}
.chip:hover{ border-color: rgba(20,184,44,0.35); }

/* Form */
.contact-form{ margin-top: 12px; }
.form-group{ margin-bottom: 14px; }
label{ display: block; margin-bottom: 6px; font-weight: 800; color: #444; }
input, select, textarea{
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-family: inherit;
}
.btn-send{
  width: 100%;
  padding: 14px;
  background: #ce1126;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 900;
  cursor: pointer;
  transition: 0.2s;
}
.btn-send:hover{ background: #a00d1d; transform: translateY(-2px); }

.error-message{
  background: rgba(206, 17, 38, 0.08);
  border: 1px solid rgba(206, 17, 38, 0.25);
  color: #a00d1d;
  padding: 10px 12px;
  border-radius: 10px;
  margin: 10px 0 12px;
  font-weight: 800;
}

/* Success */
.success-message{ text-align: center; padding: 18px 0 8px; }
.check-icon{
  width: 56px;
  height: 56px;
  background: #2d5a27;
  color: white;
  font-size: 2rem;
  line-height: 56px;
  border-radius: 50%;
  margin: 0 auto 12px;
}

.btn-link{
  display: inline-block;
  margin-top: 10px;
  background: transparent;
  border: 1px solid #e2e8f0;
  padding: 10px 12px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 900;
  color: #1a241b;
  text-decoration: none;
}
.btn-link:hover{ border-color: rgba(20,184,44,0.35); }

/* Responsive */
@media (max-width: 992px) {
  .main-layout { grid-template-columns: 1fr; }
  .sidebar-column { display: none; }
  .grid-2{ grid-template-columns: 1fr; }
}

@media (max-width: 640px) {
  .content-column { padding: 25px; }
  .title{ font-size: 1.5rem; }
}
</style>