<script setup>

import SidebarOfficial from '@/components/SidebarOfficial.vue'
import AirImlage from '@/assets/images/air.jpeg'

</script>

<template>
  <div class="about-container">
    <div class="main-layout container">

      <section class="content-column">
        <header class="about-header">
          <h1> Etat Major de l'Armée de l'Air</h1>
          <div class="header-line"></div>
        </header>

        <article class="about-text">
            <h2>EMAA</h2>
            <div class="centre">
                <img :src="AirImlage" alt="Armée de l'air">
            </div>
            <h4 class="devise" > <strong class="haut">PLUS HAUT</strong>-
            <strong class="loin">PLUS LOIN</strong>-
            <strong class="fort">PLUS FORT</strong>

            </h4>
            <h3>Historique</h3>
          <p class="lead">
            Il survient dans la vie des nations, des moments difficiles dont la gestion sur le plan militaire engendre des réflexions sur la création de nouvelles forces pour juger les crises. L’Armée de l’Air du Mali tire son origine de pareilles circonstances. En effet, en 1963, dans le nord du Mali une rébellion avait éclaté.

Le Général Abdoulaye SOUMARE alors Chef d’Etat-major de l’Armée Malienne et Commandant des forces d’intervention pour le Maintien de l’Ordre demanda la participation de l’Aviation légère aux dites opérations, Toutefois l’Armée Malienne n’en dispose pas encore. Il fut alors décidé de la réquisition d’aéronefs de la Société Nationale Air Mali.

Deux appareils de types AN-2 et un appareil L 200A Morava, aussi rien que le personnel d’exploitation furent mis à la disposition de l’Etat-major opérationnel pour assurer les missions de transports et de reconnaissance. Ainsi venait d’être constitué à partir d’une compagnie civile le premier embryon de ce qui allait devenir successivement l’aviation militaire (1er mars 1966).puis le groupement aérien tactique (Novembre 1968) et en fin l’Armée de l’Air (Ordonnance N°76-99 CML du 06 février 1976).
            </p>
          <section class="text-block">
            <h3>Missions Internationales</h3>
            <p>
              L’ordonnance n°99 048/P-RM du 1er octobre 1999 portant création de l’Armée de l’Air, ratifiée par la loi n°99-053 du 28 décembre 1999 fixe les missions de l’Armée de l’Air qui sont :

–    préserver l’intégrité de l’espace aérien national ;
–    concourir à la défense opérationnelle du territoire ;
–    participer à la recherche et au sauvetage des aéronefs en détresse.
–    participer à l’effort de développement économique social et culturel du pays.
–    Participer aux actions en faveur de la paix et de l’assistance humanitaire.

A. la préservation de l’intégrité de l’espace aérien national permet de protéger les centres d’intérêts de notre capacité militaire, politique et économique. Elle requirent une intégration des systèmes d’alerte, de contrôle et d’interception pour détecter, identifier, intercepter et déduire les forces aériennes ennemies s’attaquant aux intérêts de notre nation. Ainsi, intègre-t-elle la supériorité aérienne ? dont l’objectif est d’obtenir le contrôle de l’environnement aérien autrement dit atteindre la suprématie aérienne.

B- la participation de l’armée de l’Air à la Défense opérationnelle du territoire repose en substance sur l’interdiction de son espace aérien aux avions ennemis, l’appui aérien rapproche, le transport et la protection des point sensibles.
L’interdiction implique des opérations de combat dont les objectifs sont de retarder, rompre, distraire ou détruire le potentiel militaire ennemi avant qu’il ne puise être utilisé efficacement contre les forces amies c’est-à-dire nos propre forces et les forces alliées.

Quant à l’appui aérien rapproché, il consiste à effectuer des opérations de combat ayant pour objectifs d’appuyer les troupes amie menant des opérations terrestres ou sur eau en attaquant les forces ennemies se trouvant près d’elles.

S’agissant des opérations de transport, elles consistent à déployer les forces militaires ou les moyens logistiques par voies aériennes.

C- La participation à la recherche et au sauvetage des aéronefs en détresse dans le but d’assurer une aide rapide et efficace aux personnes et biens victimes d’un accident d’aviation ou d’un atterrissage forcé sur le territoire de la République du Mali. L’Etat-major de  m’Armée de l’Air assure la conduite de ces opérations.

D- la participation à l’effort de développement économique, social et culturel englobe :

•    Le désenclavement intérieur et extérieur du pays :
•    La lutte antiacridienne et anti aviaire avec les services spécialisés.
•    La participation au programme de pluie provoquée (l’enseignement des nuages).

E- la participation aux actions en faveur de la Paix et de l’assistance humanitaire dans le cadre des Nations Unies, de l’UA ou de la CEDEAO.

3-Organisation de l’Armée de l’Air :

Le décret N°99-366/P-RM du 19 novembre 1999 fixe l’organisation et les attributions de l’Armée de l’Air. Pour l’exercice de ses fonctions, le Chef d’Etat-major dispose de :

–    un Etat-major,
–    un Cabinet,
–    une inspection de l’Armée de l’Air,
–    Des régions aériennes comprenant des bases aériennes et des détachements Air.

Partager :
            </p>
          </section>

          <section class="text-block highlight-box">
            <h3>Faits Historiques : La Guerre de Noël (1985)</h3>
            <p>
              Connu sous le nom de guerre de la Bande d'Agacher, ce conflit frontalier a opposé le Mali et le Burkina Faso.
              Le Mali, sous le commandement de Moussa Traoré, a mobilisé 7 600 hommes pour défendre l'intégrité territoriale.
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
  border: 3px solid #331072;          /* Vert Mali */
 /* padding: 12px;*/
  margin: 15px 0;
  border-radius: 6px;
  background: transparent;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
  max-width: 200px;
    overflow: hidden;
  height:150px ;
  margin-left: auto;
    margin-right: auto;

}
.centre img{
    display: block;
    max-height: 200%;
    max-width: 110%;
    object-fit: cover;


}

.haut {
  background: #06b429; /* Vert */
  color: #1a241b;
  padding: 6px 12px;
  border-radius: 4px;
  font-weight: 700;
}

.loin {
  background: #FFD700; /* Jaune */
  color: #1a241b;
  padding: 6px 12px;
  border-radius: 4px;
  font-weight: 700;
}

.fort {
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
