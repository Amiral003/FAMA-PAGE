<script setup>
import { ref } from 'vue'
import { useHead } from '@unhead/vue'

useHead({
  title: 'Contact | FAMa',
  meta: [
    {
      name: 'description',
      content: 'Contactez les services officiels des FAMa pour toute demande d’information ou signalement officiel.'
    },
    { name: 'robots', content: 'index,follow' }
  ]
})

const website = ref('') // honeypot
const form = ref({
  name: '',
  telephone: '',
  email: '',
  subject: '',
  message: ''
})

const isSending = ref(false)
const sent = ref(false)
const errorMsg = ref('')

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
        name: form.value.name,
        telephone: form.value.telephone,
        email: form.value.email,
        subject: form.value.subject,
        message: form.value.message,
        website: website.value,
      }),
    })

    if (res.status === 422) {
      const data = await res.json()
      console.log('Erreurs backend:', data.errors)

      const firstErrorKey = Object.keys(data.errors)[0]
      const firstErrorMessage = data.errors[firstErrorKey][0]

      errorMsg.value = firstErrorMessage || 'Certains champs sont invalides.'
      return
    }

    if (res.status === 429) {
      errorMsg.value = 'Trop de tentatives. Réessaie dans quelques minutes.'
      return
    }

    if (!res.ok) {
      errorMsg.value = 'Erreur serveur. Réessaie plus tard.'
      return
    }

    sent.value = true
    form.value = {
      name: '',
      telephone: '',
      email: '',
      subject: '',
      message: '',
    }
    website.value = ''
  } catch (err) {
    console.error('Erreur:', err)
    errorMsg.value = 'Impossible de contacter le serveur. Vérifie ta connexion.'
  } finally {
    isSending.value = false
  }
}
</script>

<template>
  <main class="contact-page">
    <header class="contact-header">
      <div class="container">
        <h1>Contactez-nous</h1>
        <p>Pour toute demande d’information ou signalement officiel.</p>
      </div>
    </header>

    <section class="contact-content container">
      <div class="contact-grid">
        <aside class="contact-info" data-aos="fade-right">
          <h2>Informations officielles</h2>
          <p>Le Ministère de la Défense et des Anciens Combattants est à votre écoute.</p>

          <div class="info-item">
            <span class="icon" aria-hidden="true">📍</span>
            <div>
              <h3>Adresse</h3>
              <p>Sise Commissariat des Armées, Bamako, Mali</p>
            </div>
          </div>

          <div class="info-item">
            <span class="icon" aria-hidden="true">📞</span>
            <div>
              <h3>Téléphone</h3>
              <p><a href="tel:+22320232503">+223 20 23 25 03</a></p>
            </div>
          </div>

          <div class="info-item">
            <span class="icon" aria-hidden="true">📧</span>
            <div>
              <h3>Email</h3>
              <p><a href="mailto:dirpa.fama@gmail.com">dirpa.fama@gmail.com</a></p>
            </div>
          </div>

          <div class="social-links">
            <p>Suivez-nous :</p>
            <div class="icons">
              <span>FB</span>
              <span>TW</span>
              <span>YT</span>
            </div>
          </div>
        </aside>

        <div class="contact-form-container" data-aos="fade-left">
          <form v-if="!sent" @submit.prevent="handleSubmit" class="contact-form" novalidate>
            <div class="form-group">
              <label for="contact-name">Nom complet</label>
              <input
                id="contact-name"
                v-model="form.name"
                type="text"
                placeholder="Ex: Ibrahima DIALLO"
                autocomplete="name"
                maxlength="255"
                required
              />
            </div>

            <div class="form-group">
              <label for="contact-phone">Numéro de téléphone</label>
              <input
                id="contact-phone"
                v-model="form.telephone"
                type="tel"
                placeholder="Ex: 71 24 49 23"
                autocomplete="tel"
                maxlength="50"
                required
              />
            </div>

            <div class="form-group">
              <label for="contact-email">Email <span class="optional">(optionnel)</span></label>
              <input
                id="contact-email"
                v-model="form.email"
                type="email"
                placeholder="exemple@gmail.com"
                autocomplete="email"
                inputmode="email"
                maxlength="255"
              />
            </div>

            <!-- HONEYPOT -->
            <div class="hp-wrapper">
              <label for="website" style="display: none;">Site web</label>
              <input
                id="website"
                v-model="website"
                type="text"
                name="website"
                class="hp"
                autocomplete="off"
                tabindex="-1"
                placeholder="Laissez ce champ vide"
              />
            </div>

            <div class="form-group">
              <label for="contact-subject">Sujet</label>
              <select
                id="contact-subject"
                v-model="form.subject"
                required
              >
                <option value="" disabled>Choisissez un sujet</option>
                <option value="information">Demande d'information</option>
                <option value="recrutement">Question recrutement</option>
                <option value="signalement">Faire un signalement</option>
                <option value="presse">Espace presse</option>
              </select>
            </div>

            <div class="form-group">
              <label for="contact-message">Message</label>
              <textarea
                id="contact-message"
                v-model="form.message"
                rows="6"
                placeholder="Votre message..."
                minlength="10"
                maxlength="5000"
                required
              />
            </div>

            <p v-if="errorMsg" class="error-message" role="alert">
              {{ errorMsg }}
            </p>

            <button type="submit" class="btn-send" :disabled="isSending">
              {{ isSending ? 'Envoi en cours...' : 'Envoyer le message' }}
            </button>
          </form>

          <div v-else class="success-message">
            <div class="check-icon">✓</div>
            <h3>Message envoyé</h3>
            <p>Nous reviendrons vers vous dans les plus brefs délais.</p>
            <button
              @click="sent = false; errorMsg = ''; website = ''"
              class="btn-back"
            >
              Envoyer un autre message
            </button>
          </div>
        </div>
      </div>
    </section>
  </main>
</template>

<style scoped>
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.contact-page {
  padding-bottom: 80px;
  background: #fdfdfd;
}

.contact-header {
  background: linear-gradient(135deg, #1e3d1a 0%, #2d5a27 100%);
  color: white;
  padding: 80px 0;
  text-align: center;
  margin-bottom: 60px;
}

.contact-header h1 {
  font-size: 3rem;
  margin-bottom: 10px;
}

.contact-grid {
  display: grid;
  grid-template-columns: 1fr 1.5fr;
  gap: 60px;
}

.contact-info h2 {
  color: #1e3d1a;
  margin-bottom: 20px;
}

.info-item {
  display: flex;
  gap: 15px;
  margin-bottom: 30px;
}

.info-item .icon {
  font-size: 1.5rem;
  background: #f0f4f0;
  padding: 10px;
  border-radius: 50%;
  height: fit-content;
}

.info-item h3 {
  font-size: 1.1rem;
  margin-bottom: 5px;
  color: #333;
}

.info-item a {
  color: inherit;
  text-decoration: none;
}

.info-item a:hover {
  text-decoration: underline;
}

.contact-form-container {
  background: white;
  padding: 40px;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.05);
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #444;
}

.optional {
  font-weight: normal;
  font-size: 0.8rem;
  color: #888;
}

input,
select,
textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-family: inherit;
  background: #fff;
}

textarea {
  resize: vertical;
}

.btn-send {
  width: 100%;
  padding: 15px;
  background: #ce1126;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.3s;
}

.btn-send:hover:enabled {
  background: #a00d1d;
  transform: translateY(-2px);
}

.btn-send:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.success-message {
  text-align: center;
  padding: 40px 0;
}

.check-icon {
  width: 60px;
  height: 60px;
  background: #2d5a27;
  color: white;
  font-size: 2rem;
  line-height: 60px;
  border-radius: 50%;
  margin: 0 auto 20px;
}

.btn-back {
  margin-top: 16px;
  padding: 12px 18px;
  border: 1px solid #2d5a27;
  background: transparent;
  color: #2d5a27;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 700;
}

/* HONEYPOT */
.hp-wrapper {
  position: absolute;
  left: -9999px;
  top: -9999px;
  opacity: 0;
  height: 0;
  width: 0;
  overflow: hidden;
}

.hp {
  position: absolute;
  left: -9999px;
  width: 1px;
  height: 1px;
  opacity: 0;
}

.error-message {
  background: rgba(206, 17, 38, 0.08);
  border: 1px solid rgba(206, 17, 38, 0.25);
  color: #a00d1d;
  padding: 10px 12px;
  border-radius: 8px;
  margin: 10px 0 15px;
  font-weight: 600;
}

@media (max-width: 768px) {
  .contact-grid {
    grid-template-columns: 1fr;
  }

  .contact-header {
    padding: 56px 0;
  }

  .contact-header h1 {
    font-size: 2.2rem;
  }

  .contact-form-container {
    padding: 24px;
  }
}

input:focus,
select:focus,
textarea:focus {
  outline: none;
  border-color: #2d5a27;
  box-shadow: 0 0 0 3px rgba(45, 90, 39, 0.12);
}
</style>
