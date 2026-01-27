<script setup>
import { ref } from 'vue'
import { useHead } from '@vueuse/head'

useHead({
  title: 'Contact | FAMa',
  meta: [{ name: 'description', content: 'Contactez les services officiels des FAMa.' }]
})

const form = ref({
  name: '',
  email: '',
  subject: '',
  message: ''
})

const isSending = ref(false)
const sent = ref(false)

const handleSubmit = async () => {
  isSending.value = true
  // Simulez un envoi (remplacez par votre appel API axios si besoin)
  setTimeout(() => {
    isSending.value = false
    sent.value = true
    form.value = { name: '', email: '', subject: '', message: '' }
  }, 1500)
}
</script>

<template>
  <main class="contact-page">
    <header class="contact-header">
      <div class="container">
        <h1>Contactez-nous</h1>
        <p>Pour toute demande d'information ou signalement officiel.</p>
      </div>
    </header>

    <section class="contact-content container">
      <div class="contact-grid">
        
        <div class="contact-info" data-aos="fade-right">
          <h2>Informations Officielles</h2>
          <p>Le Ministère de la Défense et des Anciens Combattants est à votre écoute.</p>
          
          <div class="info-item">
            <span class="icon">📍</span>
            <div>
              <h3>Adresse</h3>
              <p>Place de la Liberté, Bamako, Mali</p>
            </div>
          </div>

          <div class="info-item">
            <span class="icon">📞</span>
            <div>
              <h3>Numéros Verts</h3>
              <p>80 00 11 11 / 80 00 22 22</p>
            </div>
          </div>

          <div class="info-item">
            <span class="icon">📧</span>
            <div>
              <h3>Email</h3>
              <p>contact@fama.ml</p>
            </div>
          </div>

          <div class="social-links">
            <p>Suivez-nous :</p>
            <div class="icons">
              <span>FB</span> <span>TW</span> <span>YT</span>
            </div>
          </div>
        </div>

        <div class="contact-form-container" data-aos="fade-left">
          <form @submit.prevent="handleSubmit" class="contact-form" v-if="!sent">
            <div class="form-group">
              <label>Nom Complet</label>
              <input v-model="form.name" type="text" placeholder="Ex: Moussa Traoré" required />
            </div>

            <div class="form-group">
              <label>Email</label>
              <input v-model="form.email" type="email" placeholder="votre@email.com" required />
            </div>

            <div class="form-group">
              <label>Sujet</label>
              <select v-model="form.subject" required>
                <option value="" disabled>Choisissez un sujet</option>
                <option value="information">Demande d'information</option>
                <option value="recrutement">Question Recrutement</option>
                <option value="presse">Espace Presse</option>
              </select>
            </div>

            <div class="form-group">
              <label>Message</label>
              <textarea v-model="form.message" rows="5" placeholder="Votre message..." required></textarea>
            </div>

            <button type="submit" class="btn-send" :disabled="isSending">
              {{ isSending ? 'Envoi en cours...' : 'Envoyer le message' }}
            </button>
          </form>

          <div v-else class="success-message">
            <div class="check-icon">✓</div>
            <h3>Message envoyé !</h3>
            <p>Nous reviendrons vers vous dans les plus brefs délais.</p>
            <button @click="sent = false" class="btn-back">Envoyer un autre message</button>
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

input, select, textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-family: inherit;
}

.btn-send {
  width: 100%;
  padding: 15px;
  background: #ce1126; /* Rouge drapeau Mali */
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.3s;
}

.btn-send:hover {
  background: #a00d1d;
  transform: translateY(-2px);
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

@media (max-width: 768px) {
  .contact-grid {
    grid-template-columns: 1fr;
  }
}
</style>