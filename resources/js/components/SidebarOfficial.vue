<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import Button from 'primevue/button'

defineProps({
  sticky: {
    type: Boolean,
    default: true,
  },
})

const router = useRouter()

const today = computed(() => {
  const d = new Date()
  const txt = d.toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
  })

  return txt.charAt(0).toUpperCase() + txt.slice(1)
})

const goSignalement = () => {
  router.push({ path: '/contact', query: { type: 'signalement', subject: 'information' } })
}

const goDirpa = () => {
  router.push({ path: '/contact', query: { subject: 'presse' } })
}

const goContact = () => {
  router.push('/contact')
}
</script>

<template>
  <aside :class="['official-sidebar', { 'is-sticky': sticky }]">
    <div class="sidebar-card alert-card">
      <div class="card-header">
        <span class="status-badge" title="Service disponible">
          <span class="status-dot pulse"></span>
          SERVICE DISPONIBLE
        </span>
      </div>

      <div class="info-content">
        <p class="date-text">{{ today }}</p>
        <h3 class="title-gold">Nous contacter</h3>

        <p class="description">
          Adressez un signalement, une demande d’information ou un message destiné au service presse.
        </p>

        <div class="action-stack">
          <Button
            label="Envoyer un signalement"
            icon="pi pi-shield"
            class="w-full signal-btn"
            @click="goSignalement"
          />

          <Button
            label="Contacter un service"
            icon="pi pi-envelope"
            outlined
            class="w-full secondary-btn"
            @click="goContact"
          />

          <Button
            label="DIRPA / Presse"
            icon="pi pi-megaphone"
            link
            class="w-full contact-link"
            @click="goDirpa"
          />
        </div>

        <p class="fine-print">
          Ne partagez pas d’informations sensibles, de mots de passe ou de données bancaires.
        </p>
      </div>
    </div>

    <div class="sidebar-card social-card">
      <div class="social-header">
        <h3 class="title-small">Canaux officiels</h3>
        <p class="social-text">Suivez les publications officielles des FAMa.</p>
      </div>

      <div class="social-flex">
        <a
          class="social-link"
          href="https://www.facebook.com/share/18FFdPGpxG/?mibextid=wwXIfr"
          target="_blank"
          rel="noopener"
          aria-label="Facebook officiel"
          title="Facebook"
        >
          <i class="pi pi-facebook"></i>
        </a>

        <a
          class="social-link"
          href="https://x.com/dirpafa?s=11"
          target="_blank"
          rel="noopener"
          aria-label="X / Twitter officiel"
          title="X"
        >
          <i class="pi pi-twitter"></i>
        </a>

        <a
          class="social-link"
          href="https://youtube.com/@fama-dirpa6233?si=J9QQ3Jpfg3AUZLQx"
          target="_blank"
          rel="noopener"
          aria-label="YouTube officiel"
          title="YouTube"
        >
          <i class="pi pi-youtube"></i>
        </a>

        <a
          class="social-link"
          href="https://www.instagram.com/fama.ml?igsh=Y2ttMjVydm1kMmw1"
          target="_blank"
          rel="noopener"
          aria-label="Instagram officiel"
          title="Instagram"
        >
          <i class="pi pi-instagram"></i>
        </a>
      </div>
    </div>
  </aside>
</template>

<style scoped>
*,
*::before,
*::after {
  box-sizing: border-box;
}

.official-sidebar {
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
  width: 100%;
  min-width: 0;
}

.is-sticky {
  position: sticky;
  top: 120px;
  align-self: start;
  will-change: transform;
}

@media (max-width: 1024px) {
  .is-sticky {
    position: static;
    top: auto;
  }
}

.sidebar-card {
  background: #1a2421;
  border: 1px solid #2a3a35;
  border-radius: 14px;
  padding: 1.1rem;
  color: #f1f5f9;
  box-shadow: 0 8px 22px rgba(0, 0, 0, 0.14);
  overflow: hidden;
}

.alert-card {
  background:
    linear-gradient(180deg, rgba(212, 175, 55, 0.04) 0%, rgba(26, 36, 33, 1) 26%),
    #1a2421;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(20, 184, 44, 0.1);
  padding: 7px 11px;
  border-radius: 999px;
  font-size: 0.68rem;
  font-weight: 900;
  color: #14b82c;
  letter-spacing: 0.08em;
  line-height: 1;
}

.status-dot {
  width: 8px;
  height: 8px;
  background: #14b82c;
  border-radius: 50%;
  flex-shrink: 0;
}

.pulse {
  animation: pulse-green 2s infinite;
}

.info-content {
  margin-top: 0.75rem;
}

.date-text {
  font-size: 0.82rem;
  color: #94a3b8;
  margin: 0 0 0.45rem;
  line-height: 1.4;
}

.title-gold {
  color: #d4af37;
  font-size: 1.12rem;
  font-weight: 900;
  margin: 0 0 0.6rem;
  line-height: 1.25;
}

.title-small {
  font-size: 0.76rem;
  font-weight: 800;
  color: #94a3b8;
  text-transform: uppercase;
  margin: 0;
  letter-spacing: 0.08em;
}

.description {
  font-size: 0.9rem;
  color: #cbd5e1;
  line-height: 1.65;
  margin: 0 0 0.95rem;
}

.action-stack {
  display: flex;
  flex-direction: column;
  gap: 9px;
}

.signal-btn {
  background: #14b82c !important;
  border: none !important;
  font-weight: 800 !important;
  min-height: 44px !important;
  padding: 0.82rem 0.95rem !important;
  border-radius: 12px !important;
}

.secondary-btn {
  border-color: rgba(212, 175, 55, 0.35) !important;
  color: #f1f5f9 !important;
  font-weight: 800 !important;
  min-height: 44px !important;
  border-radius: 12px !important;
}

.contact-link {
  color: #94a3b8 !important;
  font-size: 0.84rem !important;
  text-decoration: none;
  justify-content: flex-start !important;
  padding-left: 0 !important;
}

.fine-print {
  margin-top: 12px;
  font-size: 0.74rem;
  color: #94a3b8;
  line-height: 1.55;
}

.social-card {
  padding-top: 1rem;
  padding-bottom: 1rem;
}

.social-header {
  margin-bottom: 0.85rem;
}

.social-text {
  margin: 0.35rem 0 0;
  font-size: 0.86rem;
  color: #cbd5e1;
  line-height: 1.55;
}

.social-flex {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 9px;
}

.social-link {
  width: 100%;
  min-height: 44px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  border: 1px solid rgba(148, 163, 184, 0.25);
  color: #94a3b8;
  text-decoration: none;
  transition: 0.2s ease;
  font-size: 0.98rem;
}

.social-link:hover {
  color: #d4af37;
  border-color: rgba(212, 175, 55, 0.35);
  background: rgba(212, 175, 55, 0.08);
}

@keyframes pulse-green {
  0% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(20, 184, 44, 0.4);
  }
  70% {
    transform: scale(1);
    box-shadow: 0 0 0 10px rgba(20, 184, 44, 0);
  }
  100% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(20, 184, 44, 0);
  }
}

@media (min-width: 768px) {
  .official-sidebar {
    gap: 1rem;
  }

  .sidebar-card {
    padding: 1.2rem;
  }

  .title-gold {
    font-size: 1.18rem;
  }

  .description {
    font-size: 0.92rem;
  }

  .social-link {
    min-height: 46px;
    font-size: 1rem;
  }
}

@media (max-width: 576px) {
  .official-sidebar {
    gap: 0.85rem;
  }

  .sidebar-card {
    padding: 0.95rem;
    border-radius: 13px;
  }

  .status-badge {
    font-size: 0.63rem;
    padding: 6px 10px;
    letter-spacing: 0.06em;
  }

  .date-text {
    font-size: 0.78rem;
  }

  .title-gold {
    font-size: 1.02rem;
    margin-bottom: 0.5rem;
  }

  .description {
    font-size: 0.87rem;
    line-height: 1.6;
  }

  .signal-btn,
  .secondary-btn {
    min-height: 42px !important;
    font-size: 0.92rem !important;
  }

  .contact-link {
    font-size: 0.8rem !important;
    white-space: normal !important;
    line-height: 1.45 !important;
  }

  .fine-print {
    font-size: 0.71rem;
    margin-top: 10px;
  }

  .social-text {
    font-size: 0.82rem;
  }

  .social-flex {
    gap: 8px;
  }

  .social-link {
    min-height: 42px;
    border-radius: 11px;
    font-size: 0.92rem;
  }
}

@media (max-width: 380px) {
  .sidebar-card {
    padding: 0.88rem;
  }

  .title-gold {
    font-size: 0.96rem;
  }

  .description {
    font-size: 0.84rem;
  }

  .status-badge {
    font-size: 0.6rem;
    padding: 6px 8px;
  }

  .social-flex {
    grid-template-columns: repeat(2, 1fr);
  }

  .social-link {
    min-height: 44px;
  }
}
</style>