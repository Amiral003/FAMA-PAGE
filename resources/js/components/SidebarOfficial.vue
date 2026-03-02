<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import Button from 'primevue/button'
import Divider from 'primevue/divider'

const router = useRouter()

const today = computed(() => {
  const d = new Date()
  const txt = d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' })
  // Capitaliser la première lettre (souvent "dimanche ..." -> "Dimanche ...")
  return txt.charAt(0).toUpperCase() + txt.slice(1)
})

// ⚙️ Tu peux centraliser ces infos plus tard depuis /api/public/settings
const HOTLINE_1 = '80 00 11 11'
const HOTLINE_2 = '80 00 22 22'

// ✅ actions
const goSignalement = () => {
  router.push({ path: '/contact', query: { type: 'signalement', subject: 'information' } })
}

const goDirpa = () => {
  // Option A (recommandée) : page état-major dirpa si tu as staff.slug = 'dirpa'
  // router.push('/etat-major/dirpa')

  // Option B : page contact avec sujet "presse"
  router.push({ path: '/contact', query: { subject: 'presse' } })
}

const goContact = () => {
  router.push('/contact')
}

const copyHotline = async () => {
  try {
    await navigator.clipboard.writeText(`${HOTLINE_1} / ${HOTLINE_2}`)
  } catch (e) {
    // si clipboard bloqué, on ignore silencieusement
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

        <!-- <Button
          icon="pi pi-copy"
          text
          rounded
          class="copy-btn"
          aria-label="Copier les numéros verts"
          title="Copier les numéros verts"
          @click="copyHotline"
        /> -->
      </div>

      <div class="info-content">
        <p class="date-text">{{ today }}</p>
        <h3 class="title-gold">Signalement citoyen</h3>

        <p class="description">
          Transmettez toute information utile de manière responsable. En cas d’urgence immédiate, privilégiez l’appel.
        </p>

        <!-- <div class="hotline">
          <span class="hotline-label">Numéros verts :</span>
          <span class="hotline-value">{{ HOTLINE_1 }} / {{ HOTLINE_2 }}</span>
        </div> -->

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
        <!-- Remplace les # par tes vrais liens -->
        <a class="social-link" href="https://www.facebook.com/share/18FFdPGpxG/?mibextid=wwXIfr" target="_blank" rel="noopener" aria-label="Facebook officiel">
          <i class="pi pi-facebook"></i>
        </a>

        <a class="social-link" href="https://x.com/dirpafa?s=11" target="_blank" rel="noopener" aria-label="X / Twitter officiel">
          <i class="pi pi-twitter"></i>
        </a>

        <a class="social-link" href="https://youtube.com/@fama-dirpa6233?si=J9QQ3Jpfg3AUZLQx" target="_blank" rel="noopener" aria-label="YouTube officiel">
          <i class="pi pi-youtube"></i>
        </a>

        <a class="social-link" href="https://www.instagram.com/fama.ml?igsh=Y2ttMjVydm1kMmw1" target="_blank" rel="noopener" aria-label="Instagram officiel">
          <i class="pi pi-instagram"></i>
        </a>
      </div>
    </div>
  </aside>
</template>

<style scoped>
.official-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  width: 100%;
}

/* THÈME */
.sidebar-card {
  background: #1a2421;
  border: 1px solid #2a3a35;
  border-radius: 12px;
  padding: 1.8rem;
  color: #f1f5f9;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.card-header{
  display:flex;
  justify-content: space-between;
  align-items:center;
}

/* BADGE */
.status-badge {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(20, 184, 44, 0.1);
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 800;
  color: #14B82C;
  letter-spacing: 1px;
}

.status-dot { width: 8px; height: 8px; background: #14B82C; border-radius: 50%; }
.pulse { animation: pulse-green 2s infinite; }

.copy-btn{
  color: #94a3b8 !important;
}
.copy-btn:hover{
  color: #d4af37 !important;
  background: rgba(212, 175, 55, 0.08) !important;
}

/* TYPO */
.date-text { font-size: 0.85rem; color: #94a3b8; margin: 1rem 0 0.5rem; }
.title-gold { color: #d4af37; font-size: 1.2rem; font-weight: 800; margin-bottom: 0.6rem; }
.title-small { font-size: 0.8rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 1.2rem; }
.description { font-size: 0.92rem; color: #cbd5e1; line-height: 1.6; margin-bottom: 1rem; }

.hotline{
  display:flex;
  flex-wrap: wrap;
  gap: 6px;
  margin: 0.2rem 0 1.2rem;
  font-weight: 800;
}
.hotline-label{ color: #94a3b8; font-size: 0.85rem; }
.hotline-value{ color: #f1f5f9; letter-spacing: 0.4px; }

/* BOUTONS */
.signal-btn {
  background: #14B82C !important;
  border: none !important;
  font-weight: 800 !important;
  padding: 0.85rem !important;
}

.secondary-btn{
  border-color: rgba(212, 175, 55, 0.35) !important;
  color: #f1f5f9 !important;
  font-weight: 800 !important;
}

.contact-link {
  color: #94a3b8 !important;
  font-size: 0.85rem !important;
  text-decoration: none;
}

.action-stack{
  display:flex;
  flex-direction: column;
  gap: 10px;
}

.fine-print{
  margin-top: 12px;
  font-size: 0.78rem;
  color: #94a3b8;
  line-height: 1.5;
}

/* DEVISE */
.values-card {
  background: linear-gradient(145deg, #1a2421, #141c1a);
  border-left: 4px solid #d4af37;
}

.motto-label { font-size: 0.7rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; margin: 0; }
.motto-main { font-size: 1rem; font-weight: 800; color: #f1f5f9; margin: 5px 0; }
.gold-divider { border-top: 1px solid rgba(212, 175, 55, 0.2) !important; margin: 1rem 0 !important; }

.values-row { display: flex; justify-content: space-between; }
.value-item { font-size: 0.65rem; font-weight: 900; color: #d4af37; letter-spacing: 2px; }

/* SOCIAL */
.social-flex { display: flex; justify-content: space-between; gap: 10px; }
.social-link{
  width: 42px;
  height: 42px;
  border-radius: 999px;
  display: grid;
  place-items: center;
  border: 1px solid rgba(148, 163, 184, 0.25);
  color: #94a3b8;
  text-decoration: none;
  transition: 0.2s;
}
.social-link:hover{
  color: #d4af37;
  border-color: rgba(212, 175, 55, 0.35);
  background: rgba(212, 175, 55, 0.08);
}

@keyframes pulse-green {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(20, 184, 44, 0.4); }
  70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(20, 184, 44, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(20, 184, 44, 0); }
}
</style>