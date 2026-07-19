# Feuille de route — RoboForge

Ce document liste ce qu'il reste à construire après le MVP (`PLAN_IMPLEMENTATION_ROBOFORGE.md`, Lots 1 à 7) et la V2 (`PLAN_V2_ROBOFORGE.md`, Lots A/B/C — Organisation, Administration + Bibliothèque de composants, Refonte CDC + Choix techniques), tous livrés.

C'est un inventaire, pas un plan technique détaillé : chaque chantier ci-dessous sera discuté puis planifié en détail (architecture, migrations, tests) au moment de s'y attaquer, un par un.

## État des lieux (déjà livré)

- MVP : Projets, Membres/invitations, CDC (version initiale 12 étapes), Ressources, GitHub.
- V2 : Organisations, Administration plateforme, Bibliothèque de composants transversale, CDC recentré sur les exigences (9 étapes), Choix techniques (nomenclature projet tracée aux fonctions du CDC).
- Module Tâches/Planning : Kanban par projet (à faire/en cours/terminé), assignation à un membre, lien optionnel aux fonctions du CDC, avancement de projet recalculé depuis le ratio de tâches terminées (repli sur l'ancien calcul par statut si aucune tâche). CDC recentré à 8 étapes (retrait de l'étape Planning, absorbée par ce module).

## Chantiers restants

### 1. Éditeur d'algorigramme
Module visuel de comportement du robot (organigramme/logigramme), dont les nœuds référencent les choix techniques du projet.
**Dépendances :** choix d'une librairie graphique (React Flow pressenti, à valider) ; Choix techniques (fait).

### 2. Budget agrégé
Le coût total existe déjà par projet (`totalCostCents` des Choix techniques). Reste à agréger au niveau organisation/dashboard, et éventuellement suivre un budget cible vs réel.
**Dépendances :** Organisation (fait), Choix techniques (fait).

### 3. Export PDF
Export du CDC publié (et éventuellement de la nomenclature) en PDF pour partage hors plateforme.
**Dépendances :** CDC (fait).

### 4. Notifications
Notifier les membres des événements pertinents (invitation, CDC publié, activité projet...), au moins in-app, éventuellement email. `ProjectActivity`/`OrganizationActivity` fournissent déjà la source d'événements.
**Dépendances :** aucune.

### 5. Recherche globale
Recherche transversale (projets, composants, membres) depuis la barre de navigation.
**Dépendances :** aucune techniquement, mais gagne à venir après que le catalogue/les organisations aient du volume réel.

### 6. Aperçu CAD natif
Prévisualisation des fichiers CAD directement dans le module Ressources, plutôt qu'un simple téléchargement.
**Dépendances :** Ressources (fait) ; nécessite de choisir un format/une librairie de rendu.

### 7. Éditeur de câblage/pinout
Outil visuel pour définir les connexions électriques entre les composants d'un projet.
**Dépendances :** Choix techniques (fait). Chevauche potentiellement l'algorigramme (#1) — à clarifier avant de lancer l'un ou l'autre.

## Méthode

Chaque chantier est traité dans l'ordre choisi par l'utilisateur : discussion → plan détaillé (mode plan) → implémentation → tests/Pint/tsc → vérification live (smoke test) → commit/push sur confirmation explicite. Ce fichier est mis à jour au fil de l'eau (statuts, nouveaux chantiers identifiés en cours de route).
