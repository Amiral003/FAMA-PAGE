<script setup>

import SidebarOfficial from '@/components/SidebarOfficial.vue'
import GNMImage from '@/assets/images/GNM.jpg'

</script>

<template>
  <div class="about-container">
    <div class="main-layout container">

      <section class="content-column">
        <header class="about-header">
          <h1>Direction de la Gendarmerie Nationale</h1>
          <div class="header-line"></div>
        </header>

        <article class="about-text">
            <h2> GRM </h2>
            <div class="centre">
                <img :src="GNMImage" alt="Armée de Terre">
            </div>


            <h3 class="devise">
                <strong class="Rens">Renseigner</strong>-
                <strong class="Edu">Eduquer</strong> -
                <strong class="Repri">Au besoin Reprimer</strong>
            </h3>

        <section class="text-block mission">
  <h3>Missions</h3>

  <p>
    Elle a à la fois un rôle judiciaire et administratif, un rôle de participation à la défense nationale et de police militaire (prévôté)
    et peut enfin être mise à disposition des tribunaux.
    Alors que la police malienne a la responsabilité des villes, la gendarmerie a la responsabilité des zones peu peuplées.
  </p>

  <h3> Oragnisation</h3>
  <p>
    La gendarmerie est organiquement une des quatre composantes des forces armées du Mali.
    Les gendarmes peuvent être, « pour emploi », placés sous la responsabilité de trois ministères, celui de
    la Défense dont dépendent directement les forces affectées à la prévôté et à la défense opérationnelle du territoire,*
     celui de la Sécurité duquel relèvent les brigades territoriales et enfin celui de la Justice.
  </p>

</section>

          <section class="text-block highlight-box">
            <h3> Historique: Gendarmerie (17 septembre 1960)</h3>
           <p>
            Créée le 17 septembre 1960 à partir d'une organisation commencée en juillet 1960,
             la gendarmerie avait un effectif de 540 militaires dans les années 1960.
              La gendarmerie a
            connu plusieurs changements de structures. Le dernier, en 1999, a vu la transformation de son état-major en direction générale
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
  border: 3px solid #f8fafc;          /* Vert Mali */
  padding: 12px;
  margin: 15px 0;
  border-radius: 6px;
  background: transparent;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
  max-width: 180px;
  width: 180px;
  height:170px ;
  margin-left: auto;
    margin-right: auto;

}
.centre img{
    display: block;

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
