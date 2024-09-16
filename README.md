![Enki](https://raw.githubusercontent.com/zone47/enki/main/icone-enki.png) Enki - Wikidata Louvre Collections
======
Ajoute un bloc d'informations via Wikidata à une notice du site Collections du Louvre - module non officiel

# Présentation
Cette extension est active uniquement sur le [site des Collections du musée du Louvre](https://collections.louvre.fr).

Le module teste si un élément Wikidata est en correspondance avec l'identifiant d'une notice du site. Si un élément existe, certaines informations issues de Wikidata sont affichées quand elles sont renseignées (ID wikidata, libellé, image, créateur/créatrice, géolocalisation, lieu de découverte, ressources externes, expositions, sujet/thème et éléments iconographiques). Le module effectuant plusieurs requêtes pour récupérer des informations sur Wikidata, l'affichage peut prendre plusieurs secondes.

Pour les notices n'ayant pas d'élément Wikidata en correspondance mais un ou plusieurs identifiants Wikidata renseignés pour les entités artistes (cas fréquent pour les œuvres du département des Arts graphiques), des renvois vers des notices d'autorité de personnes sont proposés.

Les affirmations reprises depuis Wikidata sont affichées de façon simple, sans les qualifications. Les éventuelles nuances d'attribution, de données obsolètes ou autres (comme le fait que ce soit le modèle d'une sculpture et non la sculpture qui fut exposée au Salon) ne sont pas restituées et restent accessibles dans l'élément Wikidata.

Exemple 1

![Capture de la notice du Cratère Borghèse](https://raw.githubusercontent.com/zone47/enki/main/captures/cratere_borghese.png)

Exemple 2

![Capture de la notice du Radeau de la Méduses](https://raw.githubusercontent.com/zone47/enki/main/captures/le_radeau_de_la_meduse.png)

Exemple 3

![Capture de la notice d'Ève tendant la pomme](https://raw.githubusercontent.com/zone47/enki/main/captures/eve_tendant_la_pomme.png)

# Objectif
Les extensions utilisant le web sémantique pour donner des informations complémentaires sur une ressource web sont nombreuses et existent depuis longtemps.

L'idée pour ce projet est d'éditorialiser les données dans un contexte spécifique en ne gardant que ce qui pourrait être pertinent et en faisant des renvois adaptés. Ainsi sont présentés par exemple des liens vers les articles Wikipédia, des liens vers des cartes pour les lieux géolocalisés, des liens vers des listes d'éléments liés à une même exposition ou ayant un même élément en description. En revanche de nombreuses informations, présentes dans les éléments Wikidata, ne sont pas restituées comme les dates ou matériaux,  déjà renseignés dans la notice d'autorité. 

Dans le même ordre idée, Wikidata étant un hub informationnel majeur, les liens vers les ressources externes sont très nombreux ; afficher systématiquement tous ces liens créerait un bruit dommageable où la ressource utile serait noyée au milieu d'autres non pertinentes. Sur 288 ressources externes différentes liées à des éléments Wikidata ayant un indentifiant de notice Collections Louvre recensées au moment de la réalisation, seules 35 ont été conservées.
Enfin, il est toujours possible d'accéder à toutes les informations  sur la page de l'élément wikidata.

En résumé, l'objectif est, _Less is More_, de donner seulement quelques informations et liens complémentaires potentiellement pertinents et utiles aux internautes.

Une fois l'extension installée, le potentiel peut être aperçu en allant par exemple sur les notices de l'[album de sélection de chefs d'œuvres](https://collections.louvre.fr/album/2).

# Installation / Désactivation / Désinstallation
L'extension ne fonctionne actuellement que sur le [navigateur Firefox](https://www.mozilla.org/fr/firefox/new/). 

L'extension est téléchargeable en faisant un clic droit "Enregistrer la cible du lien sous" : [https://raw.githubusercontent.com/zone47/enki/main/enki.xpi](https://raw.githubusercontent.com/zone47/enki/main/enki.xpi)

Pour l'installer, il suffit d'aller dans le menu _Extensions et thèmes_ / _icône Outils pour les extensions_ / _Installer un module depuis un fichier_ et de charger le fichier téléchargé.

Pour désactiver ou désinstaller l'extension, il suffit de se rendre dans l'entrée du Menu _Extensions et thèmes_.

# Option de langue
Un des intérêts de Wikidata est la dimension multilingue. La restitution des informations dans une langue souhaitée dépend de la présence de libellés. Il n'y a pas de traduction automatique. Pour le moment l'extension est par défaut en français dont les libellés existent dans près de 100% des cas. L'anglais peut être choisie comme langue préférentielle dans :

Options de l'extension /  Langue préférentielle

En cas d'absence de libellé dans la langue choisie, un libellé dans une autre langue est restitué.

# N'hésitez pas à modififer ou enrichir Wikidata 
Wikidata est une base collaborative libre faisant partie de la galaxie Wikimedia, comme Wikipédia ou Wikimedia Commons. Si vous vous remarquez une erreur ou une information à améliorer, vous pouvez la corriger ou modifier la donnée.

Une [ancienne vidéo de présentation sur comment créer et éditer une oeuvre d'art sur Wikidata](https://www.youtube.com/watch?v=-PiS-A3w3AM).

# À faire
- Développer le multinguisme
- Ajouter plus d'informations sur les créateurs / créatrices 
- Ajouter un lien vers l'interface de création semi-automatique de nouveaux éléments
- Refactoriser le code
- Adaption de l'extention pour Chrome
- Publier l'extension dans les catalogues

# Contact
[User:Shonagon](https://www.wikidata.org/wiki/User:Shonagon)

Courriel : benoit[at]zone47.com
