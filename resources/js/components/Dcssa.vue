<script setup>

import SidebarOfficial from '@/components/SidebarOfficial.vue'
import DcssaImage from '@/assets/images/dcssa.png'

</script>

<template>
  <div class="about-container">
    <div class="main-layout container">

      <section class="content-column">
        <header class="about-header">
          <h1>Direction Centrale des Services de Santé des Armées </h1>
          <div class="header-line"></div>
        </header>
        <article class="about-text">
            <h2 class="center">D.C.S.S.A</h2>
            <div class="centre">
                <img :src="DcssaImage" alt="Genie Militaire">
            </div>


         <section class="text-block mission">
            <p>La Direction Centrale des Services de Santé des Armées (DCSSA) du Mali est l'organe clé des FAMa (Forces Armées Maliennes) responsable de la prise en charge médicale des troupes, de la gestion des hôpitaux militaires,
                de la formation du personnel de santé militaire et de la logistique sanitaire.
                 Elle œuvre à renforcer la résilience des forces,
                 notamment dans le Sahel.
</p>

  <h3>Missions et Activités clés</h3>



<ul>
    <li> <strong>Création et évolution </strong>Bien que les forces armées aient été créées en 1960, la gouvernance centralisée du service de santé s'est consolidée au début des années 1960. Elle a évolué en regroupant
        les services de santé de l'armée de terre et de l'air.</li>
    <li> <strong>Rôle opérationnel :</strong>La DCSSA forme le personnel médical et paramédical, incluant des infirmiers et laborantins aptes à servir dans les théâtres d'opérations.
         Elle est impliquée dans la lutte contre les épidémies, comme la COVID-19.</li>

    <li>
        <strong>Récentes avancées :</strong>La direction s'engage dans la formation continue et le renforcement des capacités, illustré par
        des échanges sur la gestion du VIH et des laboratoires avec des partenaires.</li>
        <li>
            <strong>Coopération Internationale :</strong>
            En 2025, la coopération sanitaire avec le Maroc a été renforcée pour améliorer le soutien médical aux militaires. Elle est dirigée par des hauts gradés, comme le Général de Brigade Guédiouma Dembélé en 2025.

        </li>
</ul>
    La DCSSA s'inscrit dans le cadre global du renforcement des FAMa, visant une meilleure prise en charge sanitaire de la troupe et des familles.


</section>

          <section class="text-block highlight-box">
            <h3> Historique: DCSSA (1er octobre 1960)</h3>
           <p>
            structurée dans sa forme actuelle en 1962 après l'indépendance ,
             est l'organe pilier de la santé militaire. Elle assure la prise en charge sanitaire des FAMa,
             forme le personnel médical, et participe à des crises sanitaires,
             collaborant avec des partenaires pour renforcer ses capacités, comme la coopération avec le Maroc.
           </p>
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
/* Fond légèrement plus neutre pour faire ressortir le blanc */
.about-container { background: #f8fafc; min-height: 100vh; padding: 40px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }

/* Layout */
.main-layout { display: grid; grid-template-columns: 1fr 340px; gap: 40px; }

.sidebar-column
  {
    position: sticky;
    top: 10px;
    grid-template-columns: 1fr 340px;
    gap: 20px;
    align-self: start;
}


@media (max-width: 992px) {
  .main-layout { grid-template-columns: 1fr; }
  .sidebar-column { display: none; }
}
/* .center{
    text-align: center;
} */

/* Style du contenu */
.content-column {
  background: white;
  padding: 40px;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  border-top: 4px solid #14B82C; /* Rappel du vert Mali en haut */
}

.about-header h1 { font-size: 2.2rem; color: #1a241b; margin-bottom: 10px; font-weight: 800; }
.header-line { width: 60px; height: 5px; background: #FFD700; margin-bottom: 30px; } /* Ligne Jaune Or */

.about-text { line-height: 1.8; color: #334155; font-size: 1.05rem; }
.lead { font-size: 1.25rem; color: #1a241b; margin-bottom: 30px; border-left: 4px solid #14B82C; padding-left: 15px; }

.text-block { margin-bottom: 35px; }
/* Titres en Vert Militaire */
.text-block h3 { color: #14B82C; font-size: 1.3rem; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; }

/* Boite de mise en valeur en Vert Foncé/Noir */
.highlight-box {
  background: #1a241b;
  color: #f8fafc;
  padding: 25px;
  border-left: 4px solid #FFD700;
  border-radius: 0 8px 8px 0;
}
.highlight-box h3 { color: #FFD700; }

.admin-list { list-style: none; padding: 0; margin-top: 15px; }
.admin-list li { display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #1a241b; }
.admin-list li .bullet { color: #14B82C; font-weight: bold; }

/* Responsive mobile */
@media (max-width: 640px) {
  .content-column { padding: 20px; }
  .about-header h1 { font-size: 1.6rem; }
}





/* Image*/
.centre {
  border: 3px solid #1466b8;          /* Vert Mali */
 /* padding: 12px;*/
  margin: 15px 0;
  border-radius: 6px;
  background: transparent;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
 /* max-width: 160px;*/
  width: 120px;
  height:190px ;
  margin-left: auto;
    margin-right: auto;
    overflow: hidden;

}
.centre img{
    display: block;
    object-fit: contain;
    max-width: 100%;
}

.Rens {
  background: #14B82C; /* Vert */
  color: #1a241b;
  padding: 6px 12px;
  border-radius: 4px;
  font-weight: 700;
}

.Edu {
  background: #FFD700; /* Jaune */
  color: #1a241b;
  padding: 6px 12px;
  border-radius: 4px;
  font-weight: 700;
}

.Repri {
  background: #C1121F; /* Rouge */
  color: #1a241b;
  padding: 6px 12px;
  border-radius: 4px;
  font-weight: 700;
}



</style>
