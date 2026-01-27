<script setup>
import { useHead } from '@vueuse/head'
import SidebarOfficial from '@/components/SidebarOfficial.vue'

useHead({
  title: 'À propos | FAMa',
  meta: [{ name: 'description', content: 'Histoire et organisation des Forces Armées Maliennes.' }],
})
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
            Fondées le <strong>1er octobre 1960</strong> après l'indépendance du pays, les forces armées maliennes (FAMa) 
            constituent le rempart de la nation. Elles se composent d'une Armée de terre, une armée de l'air, 
            une garde nationale ainsi que d'une Gendarmerie.
          </p>

          <section class="text-block">
            <h3>Missions Internationales</h3>
            <p>
              Les FAMa ont une longue tradition de maintien de la paix. Elles ont participé à des missions en 
              <strong>République démocratique du Congo</strong> (ONUC 1960-1964 et MONUC depuis 1999), 
              au <strong>Liberia</strong> en 1990, en <strong>Sierra Leone</strong> en 1997 et en <strong>Centrafrique</strong> en 2000.
            </p>
          </section>

          <section class="text-block highlight-box">
            <h3>Faits Historiques : La Guerre de Noël (1985)</h3>
            <p>
              Connu sous le nom de guerre de la Bande d'Agacher, ce conflit frontalier a opposé le Mali et le Burkina Faso. 
              Le Mali, sous le commandement de Moussa Traoré, a mobilisé 7 600 hommes pour défendre l'intégrité territoriale.
            </p>
          </section>

          <section class="text-block">
            <h3>Évolution et Défis</h3>
            <p>
              Durant les années 2000, les militaires maliens ont renforcé leurs capacités via diverses coopérations internationales. 
              Malgré les épreuves de 2012, l'armée malienne poursuit sa restructuration pour assurer la souveraineté totale du Mali.
            </p>
          </section>

          <section class="text-block command-section">
            <h3>Commandement et Administration Centrale</h3>
            <p>
              Le commandant en chef des armées est le président de la République, <strong>le Colonel Assimi GOÏTA</strong>, 
              assisté du ministre de la Défense. Le chef d'état-major général est le <strong>Général Oumar Diarra</strong>.
            </p>
            
            <ul class="admin-list">
              <li><span>•</span> État-major Général des Armées (EMGA)</li>
              <li><span>•</span> État-major de l'Armée de Terre (EMAT)</li>
              <li><span>•</span> État-major de l'Armée de l'Air (EMAA)</li>
              <li><span>•</span> État-major de la Garde Nationale du Mali (GNM)</li>
              <li><span>•</span> Direction Générale de la Gendarmerie Nationale (DGGN)</li>
            </ul>
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
.about-container { background: #f4f6f8; min-height: 100vh; padding: 40px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }

/* Layout */
.main-layout { display: grid; grid-template-columns: 1fr 340px; gap: 40px; }

@media (max-width: 992px) {
  .main-layout { grid-template-columns: 1fr; }
  .sidebar-column { display: none; }
}

/* Style du contenu */
.content-column { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }

.about-header h1 { font-size: 2.2rem; color: #1a1c1e; margin-bottom: 10px; }
.header-line { width: 60px; height: 4px; background: #ce1126; margin-bottom: 30px; }

.about-text { line-height: 1.8; color: #374151; font-size: 1.05rem; }
.lead { font-size: 1.2rem; color: #111827; margin-bottom: 30px; }

.text-block { margin-bottom: 35px; }
.text-block h3 { color: #ce1126; font-size: 1.3rem; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px; }

.highlight-box { background: #f9fafb; padding: 20px; border-left: 4px solid #1a1c1e; border-radius: 0 8px 8px 0; }

.admin-list { list-style: none; padding: 0; margin-top: 15px; }
.admin-list li { display: flex; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f3f4f6; }
.admin-list li span { color: #ce1126; font-weight: bold; }

/* Responsive mobile */
@media (max-width: 640px) {
  .content-column { padding: 20px; }
  .about-header h1 { font-size: 1.6rem; }
}
</style>