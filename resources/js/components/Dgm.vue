<script setup>

import SidebarOfficial from '@/components/SidebarOfficial.vue'
import GNMImage from '@/assets/images/genie.png'

</script>

<template>
  <div class="about-container">
    <div class="main-layout container">

      <section class="content-column">
        <header class="about-header">
          <h1>Direction du Génie Militaire </h1>
          <div class="header-line"></div>
        </header>
        <article class="about-text">
            <h2>GNM </h2>
            <div class="centre">
                <img :src="GNMImage" alt="Genie Militaire">
            </div>
            <h4 class="devise">
                <strong class="Rens">Construire</strong>-
                <strong class="Edu">Parfois détruire</strong> -
                <strong class="Repri">Toujours Servir</strong>
            </h4>

         <section class="text-block mission">
            <p>Acteur clé de la réussite au combat, Élément précurseur préparant le déploiement initial, la Direction du Génie Militaire est une composante essentielle de
                l’Armée Malienne qui remplit entièrement ses missions dans le souci d’atteindre ses objectifs.</p>

  <h3>Missions</h3>

  <p>
    En général les missions de toute Arme du Génie découlent de son rôle et de son emploi sur le terrain.
Son rôle : Repose essentiellement sur 3 composantes :

Combat: Appui des grandes unités
Sécurité: Mission de sécurité civile et protection des personnes et de biens
Infrastructure: Gestion des  domaines militaires

Son emploi  se résume entre autre par :
 </p>
<ul>
    <li>Un Appui direct des forces amies avec pour but,
         de favoriser l’engagement et la manœuvre des forces amies en garantissant leur liberté de mouvement
         sur l’ensemble du théâtre d’opération.</li>
    <li>Un appui à la contre mobilité dans le but de réduire les possibilités
        de mouvement de l’adversaire par la mise en oeuvre d’obstacles de tout genre en vu de canaliser,
         de retarder ou d’arrêter l’adversaire.</li>

    <li>
        Un appui Général par  la mise en ouvre d’actions permettant aux forces projetées sur le terrain, de se déplacer,
        s’installer et de durer sur le terrain
    </li>
</ul>


  <p>
    Au Mali en plus de ces missions, le Génie militaire est en outre chargé de :
    </p>
    <ul>
        <li>
             Assurer la réalisation, le suivi et l’entretien du domaine et des infrastructures militaires ;
        </li>
        <li>Assurer la formation technique et tactique de son personnel ;</li>
        <li>Assurer l’entretien et la réparation du matériel ;</li>
        <li>Réaliser des études et/ou des travaux d’intérêt public à la demande du Gouvernement ;</li>
        <li>Participer au développement Socio- économique du pays ;</li>
        <li>Participer aux activités en faveur de la Paix et de l’Assistance Humanitaire.</li>
    </ul>

  <p>De nos jours dans l’exécution des ces missions le Génie Militaire est appuyé par la coopération militaire Allemande dans les domaines que sont : l’Aide à la formation, à l’équipement et par la mise en place d’assistants techniques militaires.</p>

</section>

          <section class="text-block highlight-box">
            <h3> Historique: Génie Militaire (12 octobre 1964 )</h3>
           <p>
            Avec la formation de la première compagnie.
             Au fil des ans, cette unité a évolué, devenant le 34ème Bataillon du Génie en 1982,
              et a connu plusieurs changements de structure et de commandement.
               Le Génie Militaire est responsable de diverses missions, notamment la construction d'infrastructures militaires
             et le soutien logistique sur le terrain.
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
  /*padding: 12px;*/
  margin: 15px 0;
  border-radius: 6px;
  background: transparent;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
 /* max-width: 160px;*/
  width: 123px;
  height:160px ;
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


.devise{
    text-align: center;

}

</style>
