<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import Button from 'primevue/button'
import Divider from 'primevue/divider'

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

const HOTLINE_1 = '80 00 11 11'
const HOTLINE_2 = '80 00 22 22'

const goSignalement = () => {
  router.push({ path: '/contact', query: { type: 'signalement', subject: 'information' } })
}

const goDirpa = () => {
  router.push({ path: '/contact', query: { subject: 'presse' } })
}

const goContact = () => {
  router.push('/contact')
}

const copyHotline = async () => {
  try {
    await navigator.clipboard.writeText(`${HOTLINE_1} / ${HOTLINE_2}`)
  } catch (e) {
    // clipboard bloqué : on ignore silencieusement
  }
}
</script>

<template>
  <aside class="official-sidebar">
    <!-- Carte action principale -->
    <div class="sidebar-card alert-card">
      <div class="card-header">
        <span class="status-badge" title="Service opérationnel">
          <span class="status-dot pulse"></span>
          SYSTÈME ACTIF
        </span>

        <!--
        <Button
          icon="pi pi-copy"
          text
          rounded
          class="copy-btn"
          aria-label="Copier les numéros verts"
          title="Copier les numéros verts"
          @click="copyHotline"
        />
        -->
      </div>

      <div class="info-content">
        <p class="date-text">{{ today }}</p>
        <h3 class="title-gold">Signalement citoyen</h3>

        <p class="description">
          Transmettez toute information utile de manière responsable. En cas d’urgence immédiate, privilégiez l’appel.
        </p>

        <!--
        <div class="hotline">
          <span class="hotline-label">Numéros verts :</span>
          <span class="hotline-value">{{ HOTLINE_1 }} / {{ HOTLINE_2 }}</span>
        </div>
        -->

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
            label="Contacter la DIRPA (Presse)"
            icon="pi pi-info-circle"
            link
            class="w-full contact-link"
            @click="goDirpa"
          />
        </div>

        <p class="fine-print">
          ⚠️ Ne transmettez pas de mots de passe, données bancaires, ou informations personnelles sensibles.
        </p>
      </div>
    </div>

    <!-- Devise -->
    <div class="sidebar-card values-card">
      <div class="motto-container">
        <p class="motto-label">République du Mali</p>
        <p class="motto-main">Un Peuple • Un But • Une Foi</p>
        <Divider class="gold-divider" />

        <div class="values-row">
          <div class="value-item">HONNEUR</div>
          <div class="value-item">PATRIE</div>
          <div class="value-item">FIDÉLITÉ</div>
        </div>
      </div>
    </div>

    <!-- Réseaux -->
    <div class="sidebar-card social-card">
      <h3 class="title-small">Canaux officiels</h3>

      <div class="social-flex">
        <a
          class="social-link"
          href="https://www.facebook.com/share/18FFdPGpxG/?mibextid=wwXIfr"
          target="_blank"
          rel="noopener"
          aria-label="Facebook officiel"
        >
          <i class="pi pi-facebook"></i>
        </a>

        <a
          class="social-link"
          href="https://x.com/dirpafa?s=11"
          target="_blank"
          rel="noopener"
          aria-label="X / Twitter officiel"
        >
          <i class="pi pi-twitter"></i>
        </a>

        <a
          class="social-link"
          href="https://youtube.com/@fama-dirpa6233?si=J9QQ3Jpfg3AUZLQx"
          target="_blank"
          rel="noopener"
          aria-label="YouTube officiel"
        >
          <i class="pi pi-youtube"></i>
        </a>

        <a
          class="social-link"
          href="https://www.instagram.com/fama.ml?igsh=Y2ttMjVydm1kMmw1"
          target="_blank"
          rel="noopener"
          aria-label="Instagram officiel"
        >
          <i class="pi pi-instagram"></i>
        </a>
      </div>
    </div>
  </aside>
</template>

<style scoped>
* {
  box-sizing: border-box;
}

.official-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.1rem;
  width: 100%;
  min-width: 0;
}

/* CARTES */
.sidebar-card {
  background: #1a2421;
  border: 1px solid #2a3a35;
  border-radius: 16px;
  padding: 1.35rem;
  color: #f1f5f9;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.16);
  overflow: hidden;
}

.alert-card {
  background:
    linear-gradient(180deg, rgba(212, 175, 55, 0.04) 0%, rgba(26, 36, 33, 1) 28%),
    #1a2421;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

/* BADGE */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(20, 184, 44, 0.1);
  padding: 7px 12px;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 900;
  color: #14b82c;
  letter-spacing: 0.08em;
  line-height: 1;
  flex-wrap: nowrap;
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

.copy-btn {
  color: #94a3b8 !important;
}

.copy-btn:hover {
  color: #d4af37 !important;
  background: rgba(212, 175, 55, 0.08) !important;
}

/* TYPO */
.info-content {
  margin-top: 0.8rem;
}

.date-text {
  font-size: 0.88rem;
  color: #94a3b8;
  margin: 0 0 0.55rem;
  line-height: 1.5;
}

.title-gold {
  color: #d4af37;
  font-size: 1.28rem;
  font-weight: 900;
  margin: 0 0 0.7rem;
  line-height: 1.25;
}

.title-small {
  font-size: 0.8rem;
  font-weight: 800;
  color: #94a3b8;
  text-transform: uppercase;
  margin: 0 0 1rem;
  letter-spacing: 0.08em;
}

.description {
  font-size: 0.96rem;
  color: #cbd5e1;
  line-height: 1.7;
  margin: 0 0 1rem;
}

.hotline {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin: 0.25rem 0 1.2rem;
  font-weight: 800;
}

.hotline-label {
  color: #94a3b8;
  font-size: 0.85rem;
}

.hotline-value {
  color: #f1f5f9;
  letter-spacing: 0.4px;
  font-size: 0.92rem;
}

/* BOUTONS */
.action-stack {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.signal-btn {
  background: #14b82c !important;
  border: none !important;
  font-weight: 800 !important;
  min-height: 48px !important;
  padding: 0.9rem 1rem !important;
  border-radius: 12px !important;
}

.secondary-btn {
  border-color: rgba(212, 175, 55, 0.35) !important;
  color: #f1f5f9 !important;
  font-weight: 800 !important;
  min-height: 48px !important;
  border-radius: 12px !important;
}

.contact-link {
  color: #94a3b8 !important;
  font-size: 0.88rem !important;
  text-decoration: none;
  justify-content: flex-start !important;
  padding-left: 0 !important;
}

.fine-print {
  margin-top: 14px;
  font-size: 0.78rem;
  color: #94a3b8;
  line-height: 1.6;
}

/* DEVISE */
.values-card {
  background: linear-gradient(145deg, #1a2421, #141c1a);
  border-left: 4px solid #d4af37;
}

.motto-container {
  display: flex;
  flex-direction: column;
}

.motto-label {
  font-size: 0.72rem;
  color: #94a3b8;
  font-weight: 800;
  text-transform: uppercase;
  margin: 0;
  letter-spacing: 0.08em;
}

.motto-main {
  font-size: 1.02rem;
  font-weight: 900;
  color: #f1f5f9;
  margin: 0.4rem 0 0;
  line-height: 1.5;
}

.gold-divider {
  border-top: 1px solid rgba(212, 175, 55, 0.2) !important;
  margin: 1rem 0 !important;
}

.values-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.value-item {
  font-size: 0.68rem;
  font-weight: 900;
  color: #d4af37;
  letter-spacing: 0.14em;
  text-align: center;
  padding: 0.5rem 0.35rem;
  border: 1px solid rgba(212, 175, 55, 0.12);
  border-radius: 10px;
  background: rgba(212, 175, 55, 0.04);
}

/* SOCIAL */
.social-card {
  padding-top: 1.2rem;
  padding-bottom: 1.2rem;
}

.social-flex {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
}

.social-link {
  width: 100%;
  min-height: 48px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  border: 1px solid rgba(148, 163, 184, 0.25);
  color: #94a3b8;
  text-decoration: none;
  transition: 0.2s ease;
  font-size: 1rem;
}

.social-link:hover {
  color: #d4af37;
  border-color: rgba(212, 175, 55, 0.35);
  background: rgba(212, 175, 55, 0.08);
}

/* ANIMATION */
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

/* TABLET */
@media (min-width: 768px) {
  .official-sidebar {
    gap: 1.35rem;
  }

  .sidebar-card {
    padding: 1.5rem;
  }

  .title-gold {
    font-size: 1.34rem;
  }

  .description {
    font-size: 0.98rem;
  }

  .social-link {
    min-height: 50px;
    font-size: 1.05rem;
  }
}

/* MOBILE */
@media (max-width: 576px) {
  .official-sidebar {
    gap: 0.95rem;
  }

  .sidebar-card {
    padding: 1rem;
    border-radius: 14px;
  }

  .card-header {
    align-items: flex-start;
  }

  .status-badge {
    font-size: 0.66rem;
    padding: 7px 10px;
    letter-spacing: 0.06em;
  }

  .date-text {
    font-size: 0.82rem;
  }

  .title-gold {
    font-size: 1.1rem;
    margin-bottom: 0.55rem;
  }

  .description {
    font-size: 0.9rem;
    line-height: 1.65;
  }

  .signal-btn,
  .secondary-btn {
    min-height: 46px !important;
    font-size: 0.94rem !important;
  }

  .contact-link {
    font-size: 0.82rem !important;
    white-space: normal !important;
    line-height: 1.5 !important;
  }

  .fine-print {
    font-size: 0.74rem;
    margin-top: 12px;
  }

  .motto-label {
    font-size: 0.66rem;
  }

  .motto-main {
    font-size: 0.95rem;
  }

  .values-row {
    grid-template-columns: 1fr;
    gap: 8px;
  }

  .value-item {
    font-size: 0.66rem;
    padding: 0.55rem 0.4rem;
  }

  .social-flex {
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
  }

  .social-link {
    min-height: 44px;
    border-radius: 12px;
    font-size: 0.95rem;
  }
}

/* TRÈS PETITS ÉCRANS */
@media (max-width: 380px) {
  .sidebar-card {
    padding: 0.9rem;
  }

  .title-gold {
    font-size: 1.02rem;
  }

  .description {
    font-size: 0.86rem;
  }

  .status-badge {
    font-size: 0.62rem;
    padding: 6px 9px;
  }

  .social-flex {
    grid-template-columns: repeat(2, 1fr);
  }

  .social-link {
    min-height: 46px;
  }
}
</style>