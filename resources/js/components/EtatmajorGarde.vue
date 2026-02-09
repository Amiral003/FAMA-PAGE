<script setup>

import SidebarOfficial from '@/components/SidebarOfficial.vue'
import GardeImage from '@/assets/images/garde.jpg'

</script>

<template>
  <div class="about-container">
    <div class="main-layout container">

      <section class="content-column">
        <header class="about-header">
          <h1>Direction de la Garde Nationale</h1>
          <div class="header-line"></div>
        </header>
        <article class="about-text">
            <h2>GN</h2>
            <div class="centre">
                <img :src="GardeImage" alt="Armée de Terre">
            </div>
            <h3 class="devise">
                <i class="devise">Servir partout avec honneur,
                    loyauté et discrétion</i>

            </h3>

         <section class="text-block mission">
            <p>La garde nationale du Mali est une des composantes
                des Forces armées maliennes.
                Placée sous l'autoritée du ministère de la défense,
                elle est mise à disposition du ministre chargé de la Sécurité

            </p>

  <h3>Objectifs</h3>

  <p>
   Elle a pour but:  </p>

<ul>
    <li>la sécurité des institutions, des autorités politiques et administratives ;</li>
    <li> le service des prisons;</li>

    <li>
        la police générale des collectivités territoriales ;
    </li>
    <li>la sécurité publique et le maintien de l’ordre public;</li>
    <li>la surveillance du territoire ;</li>
    <li>de participer aux opérations de paix et d’assistance humanitaire;</li>
    <li>de contribuer aux actions de développement économique, social et culturel</li>
</ul>
  <h3>Organisation</h3>
  <p>
    La garde nationale est présente dans toutes les collectivités territoriales maliennes[7]. Elle compte environ 12 000 hommes[6].

Elle est constituée autour d'un état-major basé à N’Tomikorobougou (quartier de Bamako) et de vingt groupements territoriaux, répartis dans chacune des régions et district de Bamako[1].,[8]

La garde nationale compte également un groupement de maintien de l’ordre (GMO) à Bamako[9], Un groupement d'intervention à Bamako Rive droite, deux groupements d'intervention rapide à Kangaba et à Kolokani enfin plusieurs nouveaux groupements Tactique d’intervention GTI ( Scorpion,Tama, Bouna,Touramakan, Etoiles et Nampe) sont créés mais avec comme périmètre d'intervention tout le territoire[6], six compagnies méharistes[6] et enfin le Groupement Spécial de la Sécurité Présidentielle (GSSP), chargé de protéger le palais de Koulouba.

  </p>

</section>

          <section class="text-block highlight-box">
            <h3> Historique: Garde (11 Juillet 1894)</h3>
           <p>
             La garde nationale trace ses origines depuis le corps des gardes indigènes à pied,
             créé au Soudan français le 11 juillet 1894. Ce corps devient le corps des gardes régionaux en novembre de la même année, puis
              ses soldats deviennent les gardes cercles territoriaux, gardes goumiers
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
.about-container { background: #f8fafc; min-height: 100vh; padding: 15px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
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
.header-line { width: 60px; height: 5px; background: #FFD700; margin-bottom: 0px; } /* Ligne Jaune Or */

.about-text { line-height: 1.8; color: #334155; font-size: 1.05rem;
padding-inline-start: 01px;}
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
  /*padding: 12px;*/
  margin: 20px 0;
  border-radius: 6px;
  background: transparent;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
  /*max-width: 150px;*/
  width: 100px;
  height:140px ;
  margin-left: auto;
    margin-right: auto;
    overflow: hidden;
    display:flex;
    align-items: center;
    justify-content: center;



}
.centre img{
    display: block;
    object-fit: cover;
    max-height: 135px;
    max-width: 130px;
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
