<script setup>

import SidebarOfficial from '@/components/SidebarOfficial.vue'
import atImage from '@/assets/images/AT.png'
import arouImage from '@/assets/images/Arouna.jpeg'

</script>

<template>
  <div class="about-container">
    <div class="main-layout container">

      <section class="content-column">
        <header class="about-header">
          <h1>Etat Major de l'Armée de Terre </h1>
          <div class="header-line"></div>
        </header>

        <article class="about-text">
            <h2>EMAT </h2>
            <div class="centre">
                <img :src="atImage" alt="Armée de Terre">
            </div>


            <h3 class="devise">
                <strong class="discipline">DISCIPLINE</strong> -
                <strong class="bravoure">BRAVOURE</strong>-
                <strong class="patriotisme">PATRIOTISME</strong>
            </h3>

         <section class="text-block mission">
  <h3>Missions</h3>

  <p>
  L’Armée de Terre, dans le
    cadre de la Défense nationale et en collaboration avec les autres composantes
    des Forces Armées, est chargée :
  </p>

  <ul>
    <li>de recruter, d’organiser les formations et de préparer les forces terrestres ;</li>
    <li>de préserver l’intégrité du territoire national ;</li>
    <li>de concourir à la défense opérationnelle du territoire ;</li>
    <li>de participer aux actions en faveur de la paix et de l’assistance humanitaire.</li>
  </ul>

  <p>
    L’Armée de Terre est commandée par un Officier général nommé par décret pris en
    Conseil des Ministres, portant le titre de Chef d’État-major de l’Armée de Terre.
    Elle est placée sous l’autorité du Chef d’État-major général des Armées.
  </p>

  <p>
    Le Chef d’État-major de l’Armée de Terre est assisté par un adjoint, Officier
    général ou supérieur, qui le remplace dans la plénitude de ses attributions en
    cas d’absence ou d’empêchement. Il porte le titre de Chef d’État-major adjoint
    de l’Armée de Terre.
  </p>

  <h4>Organisation et fonctionnement</h4>

  <p>
    Le décret n°2019-0132/P-RM du 04 mars 2019 fixe l’organisation et les modalités
    de fonctionnement de l’Armée de Terre.
  </p>

  <p><strong>L’Armée de Terre comprend :</strong></p>
  <ul>
    <li>un Chef d’État-major ;</li>
    <li>
      un État-major composé d’un Cabinet, de trois Conseillers et de quatre
      Sous-chefferies : Opérations, Logistique, Ressources humaines et Finances ;
    </li>
    <li>une Inspection de l’Armée de Terre ;</li>
    <li>des Régions militaires ;</li>
    <li>des Commandements fonctionnels.</li>
  </ul>

  <p><strong>Elle est structurée autour des Armes et Spécialités suivantes :</strong></p>
  <ul>
    <li>Infanterie ;</li>
    <li>Arme blindée et cavalerie ;</li>
    <li>Artillerie ;</li>
    <li>Transmissions ;</li>
    <li>Logistique ;</li>
    <li>Administration.</li>
  </ul>

  <p>
    Le Chef d’État-major de l’Armée de Terre conçoit, dirige, coordonne et contrôle
    les activités des différentes composantes. Il est notamment responsable :
  </p>

  <ul>
    <li>de l’instruction, de l’entraînement et de l’aptitude opérationnelle ;</li>
    <li>de la discipline, de la sécurité et du moral du personnel ;</li>
    <li>de la gestion du personnel et des matériels ;</li>
    <li>de l’utilisation efficiente des ressources ;</li>
    <li>de l’élaboration de la doctrine d’emploi des forces terrestres ;</li>
    <li>de l’organisation et de la mobilisation des forces terrestres.</li>
  </ul>

  <p>
    Les Régions militaires ont pour mission d’assurer la préparation opérationnelle
    des régiments, la défense de l’intégrité territoriale et la gestion des
    ressources humaines, financières et matérielles.
  </p>

  <p>
    L’État-major de l’Armée de Terre constitue la structure centrale de
    commandement. Le Chef d’État-major rend compte au Chef d’État-major général
    des Armées de la capacité et de la disponibilité opérationnelle.
  </p>
  <h3> Fonctionnement</h3>
  <p>
    L’État-major de l’Armée de Terre est la structure centrale de commandement de l’Armée de Terre.
    Le Chef d’État-major de l’Armée de Terre rend compte au Chef d’État-major Général des Armées, notamment de la capacité et de la disponibilité opérationnelle de l’Armée de Terre.
  </p>
  <p>
    <strong> Sous l’autorité du Chef d’État-major de l’Armée de Terre</strong>,
    l’Inspecteur en Chef, les Commandants de Régions militaires et les Commandants du COFEEAT et du COSAT planifient,
     contrôlent et coordonnent les activités de leurs structures respectives.
  </p>

</section>

          <section class="text-block highlight-box">
            <h3>Faits Historiques : Armée de Terre (1960)</h3>
            <p>
              L'Armée de terre malienne, composante majeure des FAMa (Forces Armées Maliennes) créée en 1960 et restructurée en 2019, est le fer de lance de
              la lutte contre les groupes armés terroristes (GAT)
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
  border: 0px solid #f8fafc;          /* Vert Mali */
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

.discipline {
  background: #14B82C; /* Vert */
  color: #1a241b;
  padding: 6px 12px;
  border-radius: 4px;
  font-weight: 700;
}

.bravoure {
  background: #FFD700; /* Jaune */
  color: #1a241b;
  padding: 6px 12px;
  border-radius: 4px;
  font-weight: 700;
}

.patriotisme {
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
