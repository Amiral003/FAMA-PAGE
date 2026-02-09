<script setup>

import SidebarOfficial from '@/components/SidebarOfficial.vue'
import GNMImage from '@/assets/images/genie.png'

</script>

<template>
  <div class="about-container">
    <div class="main-layout container">

      <section class="content-column">
        <header class="about-header">
          <h1>Direction du Matériel, des Hydrocarbures et du Transport des Armées </h1>
          <div class="header-line"></div>
        </header>
        <article class="about-text">
            <h2>D.M.H.T.A</h2>
            <!-- <div class="centre">
                <img :src="GNMImage" alt="Genie Militaire">
            </div> -->


         <section class="text-block mission">
            <p>Rattachée à l’Etat-major Général des Armées,
                elle est chargée de soutenir les unités dans le domaine de la maintenance et
                de l’approvisionnement en matériels majeurs,
                en munitions et en carburants.

Sa direction est implantée à Kati et des directions zonales sont présentes dans toutes les Zones de Défense.
</p>

  <h3>Missions</h3>

  <p>

Conformément à l’ordonnance n° 06-023/P-RM du 19 septembre 2006 portant création de la Direction du Matériel, des Hydrocarbures et du Transport des Armées (DMHTA) ratifiée par la loi n° 06-051/ du 09 novembre 2006, la DMHTA a pour mission de pourvoir aux besoins des armées et services en matériels techniques, armement, munitions, hydrocarbures et transport.

A ce titre, elle est chargée de :</p>
<ul>
    <li>identifier, évaluer et centraliser,
        en rapport avec les Etats-majors et Services,
        les besoins en matériels techniques, armement, munitions, hydrocarbures et transport ;</li>
    <li>satisfaire ces besoins conformément aux plans établis ;
        </li>

    <li>
        gérer la réserve ministérielle ;</li>
        <li>assurer le service du transport des Armées ;</li>
        <li>
             veiller à maintenir constamment en bon état de fonctionnement le Matériel technique et l’armement des Armées.
        </li>
</ul>

  <h3>Organisation</h3>
  <p>
Le décret n° 06-564/P-RM du 29 décembre 2006, fixe l’organisation et les modalités de fonctionnement de la Direction du Matériel, des Hydrocarbures et du Transport des Armées comme suit : <br>

<strong>En staff :</strong></p>

  <ul>
        <li>
 le Secrétariat général ;        </li>
        <li> la Cellule de Communication et de l’Informatique.</li>
        <li>Assurer l’entretien et la réparation du matériel ;</li></ul>
<p> <strong>Au niveau central :</strong></p>
<ul>
        <li>  les Sous – Directions ;</li>
        <li>l’Inspection.</li></ul>
<p><strong>Au niveau des zones de défense :</strong></p>
 <ul><li>les Directions zonales du Matériel, des Hydrocarbures et du Transport des Armées</li>
    </ul>

</section>

          <section class="text-block highlight-box">
            <h3> Historique: DMHTA (19 septembre 2006)</h3>
           <p>
            Héritière de l’arme du matériel français, la  Direction du Matériel, des Hydrocarbures et du Transport des Armées a été créée par l’ordonnance n°06-023/P-RM du 19 septembre 2006,
        ratifiée par la loi n°06-051 du 09 novembre 2006
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
  width: 120px;
  height:155px ;
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
