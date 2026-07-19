# Proposition — Éditeur d'algorigramme

Document de discussion (pas un plan technique) : on se met d'accord ici sur **ce que l'outil doit permettre de faire**, pas sur son architecture. Une fois validé, ce document sert de base au vrai plan d'implémentation (migrations, contrôleurs, tests), comme `PLAN_V2_ROBOFORGE.md` l'a été pour l'Organisation/Bibliothèque/CDC.

## 1. Deux formalismes différents se cachent derrière « algorigramme » — on ne les mélange pas

C'est le point le plus important à trancher avant tout le reste, parce qu'il détermine la palette d'éléments, les règles de l'éditeur et le rendu visuel.

- **L'algorigramme classique** (celui enseigné en algorithmique) décrit un algorithme séquentiel : Début/Fin, actions, tests (Oui/Non), boucles. Une seule chose se passe à la fois.
- **Le GRAFCET** (norme CEI 60848, *Sequential Function Chart*) est le formalisme enseigné en automatisme/robotique industrielle française pour décrire un système **séquentiel avec du parallélisme réel** : des Étapes reliées par des Transitions (conditions de réceptivité), avec des structures normalisées de **divergence/convergence en OU** (une seule branche s'exécute, au choix) et **en ET** (toutes les branches s'exécutent en même temps, puis se resynchronisent).

Votre description — *« les étapes en ET, les étapes en OU, les sauts d'étape, les reprises d'étape »* — est très précisément le vocabulaire du GRAFCET, pas de l'algorigramme classique.

**Décision : pas de palette hybride.** Vous avez raison de refuser le mélange — chaque notation a sa propre grammaire visuelle et ses propres règles (une Étape GRAFCET n'est pas un rectangle d'action d'algorigramme ; une Transition n'est pas un losange de test), et les mélanger produirait des diagrammes qui ne respectent correctement aucune des deux normes. On retient plutôt :

**Un seul éditeur, deux formalismes au choix, tranché à la création du diagramme et figé ensuite.** Quand on crée un nouveau diagramme, on choisit « Algorigramme » ou « GRAFCET » — ce choix fixe la palette disponible et le rendu des éléments pour toute la vie du diagramme (changer de formalisme en cours de route n'aurait pas de sens, il faudrait reprendre les nœuds un par un). Le moteur commun (canevas, glisser pour connecter, zoom/pan, undo/redo, sélection, lien vers les Choix techniques, persistance) est entièrement partagé sous le capot ; seuls le jeu de types de nœuds et leur apparence changent selon le formalisme choisi. Un projet peut avoir des diagrammes des deux sortes (ex. un GRAFCET pour la séquence principale, un algorigramme classique pour un sous-traitement de calcul), chacun cohérent avec sa propre norme.

## 2. Palette d'éléments — une par formalisme

### Palette « Algorigramme »

| Élément | Forme | Rôle |
|---|---|---|
| **Début** / **Fin** | Ovale | Terminateurs ; plusieurs fins possibles (arrêt normal, arrêt d'urgence...) |
| **Traitement** | Rectangle | Une instruction / action. Peut être **liée à un ou plusieurs Choix techniques** du projet |
| **Condition** | Losange | Un test, avec autant de branches sortantes que nécessaire, chacune librement étiquetée (pas figé à Oui/Non) |
| **Entrée / Sortie** | Parallélogramme | Lecture d'un capteur, écriture vers un actionneur — optionnel, sinon on utilise un Traitement classique |
| **Attente** | Rectangle avec horloge | Temporisation : un champ durée (fixe ou variable référencée, voir plus bas), aucun composant lié — c'est le microcontrôleur qui attend, pas une pièce physique |
| **Calcul / Affectation** | Rectangle avec « = » | Une opération interne (variable, compteur, valeur intermédiaire), aucun composant lié — distingue visuellement « ce qui pense » de « ce qui agit » (Traitement) |
| **Communication** | Rectangle avec flèches ↔ | Envoi ou réception de données (télémétrie, commande opérateur, liaison série/sans-fil) ; composant lié optionnel si un module radio précis figure dans les Choix techniques, sinon purement logique |
| **Commentaire** | Note libre | Non connectée, purement documentaire |

Une boucle est simplement une liaison qui revient en arrière depuis une Condition — pas un nœud à part — avec un style visuel distinct (pointillé) pour la repérer d'un coup d'œil. Un compteur de boucle (« répéter 3 fois ») ne nécessite aucun nœud supplémentaire : il se compose d'un Calcul (incrément), d'une Condition (compteur < 3) et d'une boucle — la palette ci-dessus suffit déjà.

**Registre de variables du diagramme.** Comme les fonctions F1/F2 du CDC, chaque diagramme tient sa propre liste de variables nommées (créées à la volée depuis un nœud Calcul ou Attente). Un nœud Calcul, Attente ou Condition peut ensuite référencer une variable de cette liste plutôt que de la retaper en texte libre à chaque fois — évite les incohérences (« distance_totale » vs « distanceTotale »). Ce sont des noms et rien de plus : pas de type, pas de portée, pas d'évaluation d'expression réelle (voir §7, toujours hors génération de code).

### Palette « GRAFCET »

| Élément | Forme | Rôle |
|---|---|---|
| **Étape initiale** | Rectangle à double bordure, numéroté | Point de départ (une ou plusieurs possibles) |
| **Étape** | Rectangle, numéroté | Un état stable du système. Peut être **liée à un ou plusieurs Choix techniques** (l'action associée à l'étape) |
| **Transition** | Barre perpendiculaire sur la liaison, avec sa condition de réceptivité en étiquette | Toujours entre deux étapes, jamais deux étapes reliées directement |
| **Divergence / convergence en OU** | Barre simple | Alternative : une seule branche s'exécute |
| **Divergence / convergence en ET** | Barre double | Parallélisme réel : toutes les branches s'exécutent, resynchronisation à la convergence |
| **Commentaire** | Note libre | Non connectée, purement documentaire |

Un **saut d'étape** ou une **reprise de séquence** ne sont pas des nœuds à part : ce sont des liaisons étape→transition→étape comme les autres, simplement dessinées vers un point non adjacent du diagramme (en avant pour un saut, en arrière pour une reprise). Une action dédiée dans la barre d'outils permet de créer facilement ce genre de liaison longue sans avoir à tout dézoomer.

*Note pour le futur chantier GRAFCET* : la temporisation n'y sera pas un nœud à part comme dans l'algorigramme — la convention normalisée porte le délai directement sur la **Transition** (receptivité du type « 5s après l'étape précédente »). Calcul/Affectation et Communication, en revanche, s'y retrouveront probablement comme actions associées à une Étape, à affiner le moment venu.

### Éléments hors v1 (les deux palettes), voir §7
- **Sous-diagramme / renvoi** vers un autre diagramme du même projet — utile pour découper un comportement complexe, mais ajoute de la complexité qu'on préfère différer.

Dans les deux palettes, un nœud « Traitement »/« Étape » qui ne référence aucun Choix technique reste valide (ex. une étape purement logique comme « incrémenter un compteur ») — le lien est une aide à la traçabilité, jamais une obligation.

## 3. Association aux composants du projet (capteurs, actionneurs, UC...)

Vous avez raison : ce n'est pas une fonctionnalité isolée. Chaque nœud d'action ou de condition doit pouvoir pointer vers les composants réels du projet — pas vers le catalogue général, mais vers ce que l'équipe a déjà choisi dans **Choix techniques** (`project_components`). C'est la continuité naturelle de la chaîne de traçabilité déjà amorcée : Fonction CDC → Choix technique → maintenant Action/Transition de l'algorigramme.

**Restriction proposée : le sélecteur ne montre que les composants déjà présents dans la nomenclature du projet.** Pas le catalogue transversal complet. Si un composant nécessaire n'y figure pas encore, il faut d'abord l'ajouter dans Choix techniques — ça évite qu'un diagramme référence un composant que le projet n'a en réalité jamais retenu, et garde Choix techniques comme source unique de vérité de « ce qu'il y a vraiment sur le robot ».

**Une seconde restriction : quel type de composant a du sens sur quel type de nœud — tranchée en mode strict.** Le catalogue (`ComponentType`) distingue Microcontrôleur, Capteur, Pré-actionneur, Actionneur, Effecteur, Source d'énergie — ce qui correspond exactement à la chaîne d'énergie / chaîne d'information enseignée en SI/STI2D. **Chaque type de nœud n'accepte que les types de composants qui ont un sens pour lui — le sélecteur ne propose que ceux-là, aucune échappatoire** :

| Type de nœud | Composants autorisés | Pourquoi |
|---|---|---|
| **Traitement** (algorigramme) / **Étape**, **Étape initiale** (GRAFCET) | Actionneur, Pré-actionneur, Effecteur | La chaîne d'énergie : ce qui agit physiquement |
| **Condition** (losange, algorigramme) / **Transition** (GRAFCET) | Capteur | La chaîne d'information : ce qui renseigne sur l'état du système |
| **Entrée / Sortie** (algorigramme uniquement) | Capteur **ou** Actionneur/Pré-actionneur/Effecteur | Seul nœud à couvrir les deux sens (lecture d'un capteur, écriture vers un actionneur) — cohérent avec son rôle |
| **Attente**, **Calcul / Affectation** (algorigramme) | Aucun | Logique interne au microcontrôleur, jamais liée à un composant du projet |
| **Communication** (algorigramme) | Aucun, ou un composant de communication si un module précis figure dans les Choix techniques | Seul cas « optionnel » de la palette, pour ne pas bloquer une équipe qui n'a pas encore détaillé son module radio dans la nomenclature |
| Début / Fin / Commentaire | Aucun | Les terminateurs et notes ne portent pas d'action |
| — | Microcontrôleur, Source d'énergie | Pas liés à un nœud précis : ce sont des propriétés de l'ensemble du diagramme (« ce GRAFCET tourne sur cet ESP32, alimenté par cette batterie »), réglées une fois dans les paramètres du diagramme plutôt que répétées à chaque nœud |

Un nœud peut toujours référencer **plusieurs** composants du (ou des) type(s) qui lui sont autorisés (ex. un Traitement « avancer » lié à deux moteurs), et un même composant peut être référencé par **plusieurs** nœuds à travers le diagramme (ex. un capteur de distance vérifié à trois endroits différents) — aucune restriction sur ces deux points, seule la correspondance type-de-nœud ↔ type-de-composant est verrouillée.

## 4. Édition graphique — barre d'outils proposée

- **Palette de nœuds** : clic sur un type puis clic sur le canevas pour le poser (pas de glisser-déposer complexe requis, mais on peut l'ajouter si l'ergonomie le justifie).
- **Connexion** : tirer une liaison depuis le bord d'un nœud vers n'importe quel autre nœud, y compris non-adjacent (sauts) ou en arrière (reprises). Chaque liaison peut porter une étiquette texte libre (condition, délai...).
- **Édition** : double-clic sur un nœud pour éditer son libellé, son type, ses Choix techniques liés.
- **Commentaire par nœud** : un champ de note libre optionnel sur chaque nœud (tous types confondus), affiché en infobulle au survol — pour un détail propre à ce nœud précis (ex. « capteur calibré à 2cm du sol ») sans l'imposer en permanence sur le canevas. Un petit indicateur visuel (icône dans un coin du nœud) signale qu'un commentaire existe, pour rester découvrable sans avoir à survoler chaque nœud à l'aveugle. Distinct du nœud « Commentaire » flottant (§2), qui reste pour une remarque sur une zone/un enchaînement plutôt que sur un nœud précis — les deux se complètent.
- **Sélection multiple, copier/coller, suppression**.
- **Déplacement libre** des nœuds sur le canevas (positionnement manuel), avec un bouton « **Ranger automatiquement** » pour ré-arranger proprement en un clic si le diagramme devient confus — pas de mise en page forcée en continu.
- **Zoom / pan**, **annuler/rétablir**.
- **Avertissements non bloquants**, propres à chaque formalisme : pour un algorigramme, nœud orphelin ou absence de Fin ; pour un GRAFCET, divergence ET sans convergence correspondante, transition manquante entre deux étapes — affichés dans un panneau, sans empêcher l'enregistrement (contrairement au CDC, ce module n'a pas de logique de publication qui bloque sur des champs manquants).

## 5. Persistance — validé

- **Un diagramme vivant et éditable, pas un fichier.** Le graphe (formalisme, nœuds, liaisons, positions, libellés, liens vers les Choix techniques) est stocké comme **donnée structurée** (JSON) dans sa propre table, pas comme un fichier binaire dans le module Ressources existant — Ressources ne sait stocker que des fichiers opaques (téléchargement, pas ré-édition), et les liens vers les Choix techniques ont besoin d'intégrité référentielle réelle. Même choix architectural que Choix techniques : un nouveau domaine `AlgorithmDiagrams`, toujours modifiable, pas de cycle brouillon/publication comme le CDC.
- **Export ponctuel vers Ressources.** Un bouton **« Exporter en image/PDF »** génère un instantané visuel du diagramme et le dépose dans le module Ressources existant, comme n'importe quel autre document du projet — utile pour l'impression, le partage hors plateforme, ou en pièce jointe à un rapport. Le diagramme éditable et son export restent deux choses distinctes : modifier le diagramme ne modifie pas rétroactivement un export déjà déposé. Une infobulle au survol ne veut rien dire sur une image statique : les commentaires de nœuds non vides sont listés en note de bas de page dans l'export plutôt que silencieusement perdus.
- **Plusieurs diagrammes nommés par projet**, chacun avec son formalisme propre choisi à la création (§1) — comme Ressources permet plusieurs fichiers, un projet peut avoir « Séquence de démarrage » (GRAFCET) et « Calcul de trajectoire » (algorigramme) côte à côte.

## 6. Choix de la librairie graphique

**React Flow** (`@xyflow/react`) reste le choix recommandé, comme pressenti dans la roadmap V2 : c'est la référence pour ce genre d'éditeur en React (nœuds personnalisés, liaisons personnalisées avec étiquettes, zoom/pan/minimap, gestion de multiples points de connexion par nœud), activement maintenue, typée TypeScript nativement, licence MIT. Réinventer un moteur de canevas (hit-testing, glisser-déposer, tracé de flèches) à la main serait un chantier en soi pour un résultat moins robuste. Ce sera la première dépendance frontend « lourde » du projet en dehors de `lucide-react` — assumé, puisque c'est exactement le genre de brique que `CLAUDE.md` anticipe pour ce module.

## 7. Hors scope pour cette première version

Pour éviter de sur-construire dès le départ :
- Pas de génération de code ni de simulation/exécution du diagramme.
- Pas d'édition collaborative temps réel (un seul éditeur à la fois, comme le reste de l'app).
- Pas de sous-diagrammes / appels imbriqués (§2) — v2 si le besoin se confirme.
- Pas de vérification de conformité stricte à la norme CEI 60848 — seulement des avertissements simples (§4).

## 8. Décisions retenues

- Deux formalismes distincts, choisis à la création de chaque diagramme et figés ensuite (§1) — pas de palette hybride.
- Palette Algorigramme enrichie de trois nœuds sans lien matériel obligatoire : **Attente** (temporisation), **Calcul/Affectation** (logique interne) et **Communication** (envoi/réception de données) — plus un registre de variables nommées par diagramme (§2).
- Association aux composants du projet (Choix techniques uniquement, pas le catalogue entier), avec une correspondance **stricte et verrouillée** type-de-nœud ↔ type-de-composant : Traitement/Étape ↔ chaîne d'énergie (Actionneur/Pré-actionneur/Effecteur), Condition/Transition ↔ Capteur, Entrée-Sortie ↔ les deux, Attente/Calcul ↔ aucun, Communication ↔ optionnel (§3).
- Commentaire optionnel par nœud, affiché en infobulle au survol avec indicateur visuel, distinct du nœud Commentaire flottant ; repris en note de bas de page dans l'export image/PDF (§4, §5).
- Domaine dédié (`AlgorithmDiagrams`), toujours modifiable, plus export ponctuel en image/PDF vers Ressources (§5).
- Plusieurs diagrammes nommés par projet (§5).
- **Ordre de construction : l'algorigramme classique d'abord**, pour valider le moteur commun (canevas React Flow, glisser-connecter, zoom/pan, persistance, lien vers les Choix techniques) sur la palette la plus simple — sans la contrainte d'alternance étape/transition du GRAFCET. Le GRAFCET suivra dans un second chantier, en réutilisant ce même moteur et en n'ajoutant que sa palette et ses règles propres.

La discussion de cadrage est close. Prochaine étape : plan d'implémentation détaillé pour la première tranche (algorigramme classique), au même rythme que les chantiers précédents.
