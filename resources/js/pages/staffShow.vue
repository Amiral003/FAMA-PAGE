<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useHead } from '@vueuse/head'

const route = useRoute()
const slug = computed(() => String(route.params.slug || ''))

const staff = ref(null)
const isLoading = ref(false)
const loadError = ref('')

// Formulaire de contact
const form = ref({ name: '', email: '', subject: 'information', message: '' })
const isSending = ref(false)
const sent = ref(false)
const errorMsg = ref('')

const fetchStaff = async () => {
  isLoading.value = true
  try {
    const res = await fetch(`/api/public/staffs/${encodeURIComponent(slug.value)}`)
    if (res.ok) staff.value = await res.json()
    else loadError.value = "Erreur de chargement"
  } catch (e) {
    loadError.value = "Erreur réseau"
  } finally {
    isLoading.value = false
  }
}

const handleSubmit = async () => {
  errorMsg.value = ''; isSending.value = true
  try {
    const res = await fetch('/api/public/contact', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ staff_slug: slug.value, ...form.value }),
    })
    if (res.ok) { sent.value = true; form.value = { name: '', email: '', subject: 'information', message: '' } }
    else { errorMsg.value = "Erreur lors de l'envoi." }
  } catch (e) { errorMsg.value = "Serveur injoignable." }
  finally { isSending.value = false }
}

onMounted(fetchStaff)
watch(() => slug.value, fetchStaff)

useHead(() => ({
  title: staff.value ? `${staff.value.initials} | FAMa` : 'État-Major',
}))
</script>

<template>
  <div class="page-container staff-page-container">
      <section class="content-card staff-main-card">

        <nav class="breadcrumb">
          <RouterLink to="/about">À propos</RouterLink>
          <span class="sep">/</span>
          <span class="current">État-major</span>
        </nav>

        <div v-if="isLoading" class="loading">Chargement...</div>

        <article v-else-if="staff" class="staff-content">
          <header class="header-main">
            <div class="logo-wrapper staff-info-block">
              <img v-if="staff.logo" :src="`/storage/${staff.logo}`" :alt="staff.name" />
              <div v-else class="logo-placeholder">{{ staff.initials }}</div>
            </div>

            <div class="header-text">
              <h1 class="staff-title">{{ staff.name }}</h1>
              <div class="meta-line">
                <span class="badge-em">{{ staff.initials }}</span>
                <span v-if="staff.motto" class="motto">“{{ staff.motto }}”</span>
              </div>
            </div>
          </header>

          <div class="grid-layout">
            <section class="info-block staff-info-block">
              <h3 class="section-title staff-accent-title">Présentation</h3>
              <p class="description-text">{{ staff.description }}</p>
            </section>

            <section class="info-block staff-info-block">
              <h3 class="section-title staff-accent-title">Commandement</h3>
              <div class="leader-card">
                <div class="leader-img">
                  <img v-if="staff.leader_photo" :src="`/storage/${staff.leader_photo}`" />
                  <div v-else class="img-none">Photo DIRPA</div>
                </div>
                <div class="leader-info">
                  <p class="rank">{{ staff.leader_rank }}</p>
                  <p class="name">{{ staff.leader_name }}</p>
                  <p v-if="staff.leader_word" class="word">“{{ staff.leader_word }}”</p>
                </div>
              </div>
            </section>

            <section class="info-block staff-info-block">
              <h3 class="section-title staff-accent-title">Contacts officiels</h3>
              <div class="contact-details">
                <div v-if="staff.contact_address" class="contact-item">
                  <strong>📍 Adresse:</strong> <span>{{ staff.contact_address }}</span>
                </div>
                <div v-if="staff.contact_phone" class="contact-item">
                  <strong>📞 Téléphone:</strong> <span>{{ staff.contact_phone }}</span>
                </div>
                <div v-if="staff.contact_email" class="contact-item">
                  <strong>📧 Email:</strong> <a :href="`mailto:${staff.contact_email}`">{{ staff.contact_email }}</a>
                </div>
              </div>
            </section>

            <section class="info-block staff-info-block">
              <h3 class="section-title staff-accent-title">Envoyer un message</h3>
              <div v-if="sent" class="success-msg">
                ✓ Votre message a été transmis avec succès.
                <button @click="sent = false" class="btn-reset">Envoyer un autre message</button>
              </div>
              <form v-else @submit.prevent="handleSubmit" class="mini-form">
                <input v-model="form.name" type="text" placeholder="Votre nom complet" class="staff-input" required />
                <input v-model="form.email" type="email" placeholder="Votre adresse email" class="staff-input" required />
                <textarea v-model="form.message" placeholder="Votre message..." rows="3" class="staff-input" required></textarea>
                <p v-if="errorMsg" class="error-txt">{{ errorMsg }}</p>
                <button type="submit" :disabled="isSending" class="btn-submit">
                  {{ isSending ? 'Envoi en cours...' : 'Envoyer le message' }}
                </button>
              </form>
            </section>
          </div>

          <section class="info-block staff-info-block full-width">
            <h3 class="section-title staff-accent-title">Missions et attributions</h3>
            <p class="description-text">{{ staff.missions }}</p>
          </section>

        </article>
      </section>
    </div>
</template>

<style scoped>
/* STRUCTURE INCHANGÉE - COULEURS LIÉES À APP.CSS */
.page-container {
  min-height: 100vh;
  padding: 40px 0;
  transition: all 0.3s ease;
}

.content-card {
  max-width: 1000px;
  margin: 0 auto;
  padding: 40px;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  border-top: 4px solid #14B82C;
}

/* Breadcrumb */
.breadcrumb { margin-bottom: 25px; font-size: 14px; font-weight: 600; }
.breadcrumb .sep { margin: 0 5px; }
.breadcrumb a { color: #14B82C; text-decoration: none; }

/* Header */
.header-main { display: flex; gap: 20px; align-items: center; margin-bottom: 35px; }
.logo-wrapper { width: 80px; height: 80px; border: 1px solid transparent; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; }
.logo-wrapper img { width: 100%; height: 100%; object-fit: contain; }

.staff-title { font-size: 28px; font-weight: 800; margin: 0; }
.badge-em { background: #14B82C; color: white; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 13px; }
.motto { font-style: italic; font-weight: 500; margin-left: 10px; opacity: 0.8; }

/* Grid Layout */
.grid-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px; }
.info-block { padding: 25px; border-radius: 8px; border: 1px solid; }
.full-width { grid-column: 1 / -1; }

.section-title {
  text-transform: uppercase;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 1px;
  margin-bottom: 20px;
  padding-bottom: 8px;
  border-bottom: 2px solid rgba(20, 184, 44, 0.1);
}

.description-text { line-height: 1.7; font-size: 15px; }

/* Contacts & Form */
.contact-details { display: flex; flex-direction: column; gap: 12px; }
.contact-item a { color: #14B82C; text-decoration: none; font-weight: bold; }

.mini-form { display: flex; flex-direction: column; gap: 10px; }
.mini-form input, .mini-form textarea {
  width: 100%;
  padding: 10px;
  border-radius: 6px;
  border: 1px solid rgba(0,0,0,0.1);
  font-family: inherit;
}

.btn-submit {
  background: #14B82C;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 6px;
  font-weight: 700;
  cursor: pointer;
  transition: opacity 0.2s;
}
.btn-submit:disabled { opacity: 0.5; }

.success-msg { color: #14B82C; font-weight: bold; text-align: center; }
.btn-reset { display: block; margin: 10px auto; background: none; border: 1px solid #14B82C; color: #14B82C; cursor: pointer; padding: 5px 10px; border-radius: 4px; }

/* Leader */
.leader-card { display: flex; flex-direction: column; gap: 10px; }
.leader-img { width: 100%; height: 180px; border-radius: 6px; overflow: hidden; background: rgba(0,0,0,0.05); }
.leader-img img { width: 100%; height: 100%; object-fit: cover; }
.name { font-weight: 800; font-size: 17px; margin-top: 5px; }
.rank { font-size: 14px; text-transform: capitalize; opacity: 0.8; }

@media (max-width: 768px) {
  .grid-layout { grid-template-columns: 1fr; }
  .header-main { flex-direction: column; text-align: center; }
}
</style>
