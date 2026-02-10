<script setup>
import { useHead } from '@vueuse/head'
import SidebarOfficial from '@/components/SidebarOfficial.vue'

// SEO OPTIMISÉ
useHead({
  title: 'À propos | FAMa - Portail Officiel des Forces Armées Maliennes',
  meta: [
    { 
      name: 'description', 
      content: 'Découvrez l’histoire, les missions internationales et l’organisation structurelle des Forces Armées Maliennes (FAMa). Rempart de la souveraineté du Mali.' 
    },
    { property: 'og:title', content: 'À propos des FAMa - Engagement et Patrie' },
    { property: 'og:image', content: '/assets/images/fama-flag.jpg' },
    { name: 'twitter:card', content: 'summary' }
  ],
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
              <li><span class="bullet">•</span> État-major Général des Armées (EMGA)</li>
              <li><span class="bullet">•</span> État-major de l'Armée de Terre (EMAT)</li>
              <li><span class="bullet">•</span> État-major de l'Armée de l'Air (EMAA)</li>
              <li><span class="bullet">•</span> État-major de la Garde Nationale du Mali (GNM)</li>
              <li><span class="bullet">•</span> Direction Générale de la Gendarmerie Nationale (DGGN)</li>
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
.about-container { background: #f8fafc; min-height: 100vh; padding: 40px 0; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }

/* Layout */
.main-layout { display: grid; grid-template-columns: 1fr 340px; gap: 40px; }

/* Style du contenu */
.content-column { 
  background: white; 
  padding: 45px; 
  border-radius: 12px; 
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
  border-top: 6px solid #14B82C; 
}

.about-header h1 { font-size: 2.4rem; color: #1a241b; margin-bottom: 10px; font-weight: 800; letter-spacing: -0.5px; }
.header-line { width: 80px; height: 6px; background: #FFD700; margin-bottom: 35px; border-radius: 3px; }

.about-text { line-height: 1.85; color: #334155; font-size: 1.08rem; }
.lead { 
  font-size: 1.3rem; 
  color: #1a241b; 
  margin-bottom: 35px; 
  border-left: 5px solid #14B82C; 
  padding: 10px 0 10px 20px; 
  background: #f0fdf4;
}

.text-block { margin-bottom: 40px; }
.text-block h3 { color: #14B82C; font-size: 1.4rem; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 800; }

.highlight-box { 
  background: #1a241b; 
  color: #f8fafc;
  padding: 30px; 
  border-left: 6px solid #FFD700; 
  border-radius: 4px; 
  margin: 40px 0;
}
.highlight-box h3 { color: #FFD700; margin-bottom: 10px; }

.admin-list { list-style: none; padding: 0; margin-top: 20px; display: grid; gap: 12px; }
.admin-list li { 
  display: flex; 
  gap: 12px; 
  padding: 12px 15px; 
  background: #f8fafc;
  border-radius: 6px;
  font-weight: 600; 
  color: #1a241b; 
  transition: transform 0.2s;
}
.admin-list li:hover { transform: translateX(5px); background: #f1f5f9; }
.admin-list li .bullet { color: #14B82C; font-weight: 900; }

.sidebar-column { position: sticky; top: 20px; height: fit-content; }

@media (max-width: 992px) {
  .main-layout { grid-template-columns: 1fr; }
  .sidebar-column { display: none; }
}

@media (max-width: 640px) {
  .content-column { padding: 25px; }
  .about-header h1 { font-size: 1.8rem; }
  .lead { font-size: 1.1rem; }
}
</style>