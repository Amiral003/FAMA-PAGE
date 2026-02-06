<script setup>
import { computed } from 'vue'
import Button from 'primevue/button'
import Divider from 'primevue/divider'

const today = new Date().toLocaleDateString('fr-FR', {
  weekday: 'long',
  day: 'numeric',
  month: 'long'
})
</script>

<template>
  <aside class="official-sidebar">
    
    <div class="sidebar-card alert-card">
      <div class="card-header">
        <span class="status-badge">
          <span class="status-dot pulse"></span>
          SYSTÈME ACTIF
        </span>
      </div>
      
      <div class="info-content">
        <p class="date-text">{{ today }}</p>
        <h3 class="title-gold">Signalement Citoyen</h3>
        <p class="description">
          Aidez les FAMa à assurer votre sécurité. Transmettez toute information suspecte de manière anonyme.
        </p>
        
        <div class="action-stack">
          <Button 
            label="Envoyer un Signalement" 
            icon="pi pi-shield" 
            class="w-full signal-btn"
          />
          <Button 
            label="Contacter la DIRPA" 
            icon="pi pi-info-circle" 
            link
            class="w-full contact-link"
          />
        </div>
      </div>
    </div>

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

    <div class="sidebar-card social-card">
      <h3 class="title-small">Canaux Officiels</h3>
      <div class="social-flex">
        <Button icon="pi pi-facebook" severity="secondary" rounded text class="social-icon" />
        <Button icon="pi pi-twitter" severity="secondary" rounded text class="social-icon" />
        <Button icon="pi pi-youtube" severity="secondary" rounded text class="social-icon" />
        <Button icon="pi pi-instagram" severity="secondary" rounded text class="social-icon" />
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

/* THÈME DE COULEUR : VERT ARMÉE & OR */
.sidebar-card {
  background: #1a2421; /* Vert forêt très sombre */
  border: 1px solid #2a3a35;
  border-radius: 12px;
  padding: 1.8rem;
  color: #f1f5f9;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

/* BADGE DE STATUT */
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

/* TYPOGRAPHIE */
.date-text { font-size: 0.8rem; color: #64748b; margin: 1rem 0 0.5rem; text-transform: capitalize; }
.title-gold { color: #d4af37; font-size: 1.2rem; font-weight: 800; margin-bottom: 1rem; }
.title-small { font-size: 0.8rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 1.2rem; }
.description { font-size: 0.9rem; color: #94a3b8; line-height: 1.6; margin-bottom: 1.5rem; }

/* BOUTONS CUSTOM */
.signal-btn {
  background: #14B82C !important;
  border: none !important;
  font-weight: 700 !important;
  padding: 0.8rem !important;
}

.contact-link {
  color: #94a3b8 !important;
  font-size: 0.85rem !important;
  text-decoration: none;
}

/* SECTION DEVISE */
.values-card {
  background: linear-gradient(145deg, #1a2421, #141c1a);
  border-left: 4px solid #d4af37;
}

.motto-label { font-size: 0.7rem; color: #64748b; font-weight: 700; text-transform: uppercase; margin: 0; }
.motto-main { font-size: 1rem; font-weight: 800; color: #f1f5f9; margin: 5px 0; }
.gold-divider { border-top: 1px solid rgba(212, 175, 55, 0.2) !important; margin: 1rem 0 !important; }

.values-row {
  display: flex;
  justify-content: space-between;
}

.value-item {
  font-size: 0.65rem;
  font-weight: 900;
  color: #d4af37;
  letter-spacing: 2px;
}

/* SOCIAL */
.social-flex { display: flex; justify-content: space-between; }
.social-icon { color: #94a3b8 !important; }
.social-icon:hover { color: #d4af37 !important; background: rgba(212, 175, 55, 0.1) !important; }

@keyframes pulse-green {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(20, 184, 44, 0.4); }
  70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(20, 184, 44, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(20, 184, 44, 0); }
}
</style>