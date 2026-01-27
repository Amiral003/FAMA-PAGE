<script setup>
import { computed } from 'vue'

const props = defineProps({
  recentDocs: {
    type: Array,
    default: () => []
  }
})

const today = new Date().toLocaleDateString('fr-FR', {
  weekday: 'long',
  day: 'numeric',
  month: 'long'
})
</script>

<template>
  <aside class="official-sidebar">
    <div class="sidebar-card alert-dark">
      <div class="card-header">
        <span class="status-dot pulse"></span>
        <h3>Vigilance Nationale</h3>
      </div>
      <p class="date-display">{{ today }}</p>
      <div class="emergency-box">
        <p>Centre d'appel d'urgence :</p>
        <a href="tel:80001111" class="phone-link">80 00 11 11</a>
      </div>
    </div>

    <div class="sidebar-card docs-dark" v-if="recentDocs.length > 0">
      <h3>Dernières Publications</h3>
      <ul class="doc-list">
        <li v-for="doc in recentDocs" :key="doc.id">
          <a :href="`/storage/${doc.pdf_path}`" download class="doc-item">
            <span class="doc-icon">📄</span>
            <div class="doc-info">
              <span class="doc-title">{{ doc.title }}</span>
              <span class="doc-meta">PDF • Officiel</span>
            </div>
          </a>
        </li>
      </ul>
    </div>

    <div class="sidebar-card values-dark">
      <h3>Notre Devise</h3>
      <div class="devise-container">
        <div class="motto">Un Peuple • Un But • Une Foi</div>
        <div class="values-grid">
          <span>HONNEUR</span>
          <span>PATRIE</span>
          <span>FIDÉLITÉ</span>
        </div>
      </div>
    </div>

    <div class="sidebar-card social-dark">
      <h3>Canaux Officiels</h3>
      <div class="social-btns">
        <a href="#" class="s-btn">Facebook</a>
        <a href="#" class="s-btn">Twitter (X)</a>
        <a href="#" class="s-btn">YouTube</a>
      </div>
    </div>
  </aside>
</template>

<style scoped>
.official-sidebar {
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100%;
}

/* Base des cartes en thème sombre */
.sidebar-card {
  background: #1a1c1e; /* Fond très sombre */
  color: #e2e8f0;
  padding: 24px;
  border-radius: 12px;
  border: 1px solid #2d3135;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
}

.card-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 5px;
}

h3 {
  font-size: 0.9rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #94a3b8;
  margin: 0;
}

.date-display {
  font-size: 0.8rem;
  color: #64748b;
  margin-bottom: 15px;
  text-transform: capitalize;
}

/* Animation Pulse pour l'alerte */
.status-dot {
  width: 8px;
  height: 8px;
  background: #ef4444;
  border-radius: 50%;
}

.pulse {
  box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
  animation: pulse-red 2s infinite;
}

@keyframes pulse-red {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

/* Urgence */
.emergency-box {
  background: #2d1a1a;
  border: 1px solid #451a1a;
  padding: 15px;
  border-radius: 8px;
  text-align: center;
}

.phone-link {
  display: block;
  font-size: 1.4rem;
  font-weight: 800;
  color: #ef4444;
  text-decoration: none;
  margin-top: 5px;
}

/* Liste de documents */
.doc-list {
  list-style: none;
  padding: 0;
  margin-top: 15px;
}

.doc-item {
  display: flex;
  gap: 12px;
  padding: 12px;
  background: #26292d;
  border-radius: 8px;
  text-decoration: none;
  color: inherit;
  margin-bottom: 10px;
  transition: 0.2s;
}

.doc-item:hover {
  background: #32363b;
  transform: translateX(5px);
}

.doc-title {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  line-height: 1.2;
}

.doc-meta {
  font-size: 0.7rem;
  color: #2f855a;
  font-weight: bold;
}

/* Devise */
.devise-container {
  text-align: center;
  margin-top: 15px;
}

.motto {
  font-style: italic;
  font-size: 0.9rem;
  color: #fbbf24;
  margin-bottom: 10px;
}

.values-grid {
  display: flex;
  justify-content: space-between;
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 2px;
  color: #64748b;
}

/* Réseaux */
.social-btns {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 15px;
}

.s-btn {
  background: #26292d;
  padding: 10px;
  border-radius: 6px;
  text-align: center;
  text-decoration: none;
  color: #cbd5e0;
  font-size: 0.85rem;
  font-weight: 600;
  border: 1px solid #32363b;
}

.s-btn:hover {
  background: #32363b;
  color: white;
}
</style>