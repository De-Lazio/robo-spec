# Plan d'évolution V2 — RoboForge

Ce document prolonge `docs/PLAN_IMPLEMENTATION_ROBOFORGE.md` (MVP, Lots 1 à 7, tous livrés). Il couvre la transformation de RoboForge d'un outil de documentation/dépôt de fichiers vers un véritable outil de pilotage de projet robotique : organisation permanente des équipes, bibliothèque de composants transversale, et refonte du CDC pour séparer exigences, choix techniques et algorithme.

## 1. Diagnostic et principes retenus

### Constat

Le MVP documente un projet (CDC, fichiers, membres, dépôt GitHub) mais ne pilote pas son exécution : `progress` est un pourcentage fixe dérivé du seul statut, il n'y a pas de subdivision en tâches, pas de nomenclature de composants structurée (l'étape 6 du CDC — capteurs/actionneurs/unité de contrôle/énergie — et l'étape 10 — matériel — sont du texte libre non réutilisable), pas d'entité au-dessus du Projet pour une équipe qui enchaîne plusieurs projets dans le temps, et aucun rôle d'administration plateforme.

### Principes retenus pour la V2

- **Séparation exigences / conception / comportement.** Le CDC reste le contrat stable (quoi, pourquoi, contraintes, critères de succès) : versionné, publié, peu de churn. Les choix techniques (quels composants, pour quelle fonction) et l'algorigramme (comment le robot se comporte) deviennent des modules séparés, à évolution continue, sans cycle de publication lourd — un changement de capteur ne doit plus jamais nécessiter une republication du CDC.
- **Traçabilité par les fonctions.** L'étape 5 du CDC a déjà des identifiants stables (`F1`, `F2`...). Les choix techniques s'y rattachent ; l'algorigramme se rattache aux choix techniques. La chaîne complète (Fonction → Composant → Comportement) devient interrogeable.
- **Bibliothèque de composants transversale, pas par projet.** Un seul catalogue pour toute la plateforme, géré par un rôle admin distinct des rôles projet existants. Chaque projet compose sa propre nomenclature (BOM) à partir de ce catalogue.
- **Organisation avant tout le reste.** Une équipe robotique réelle (club, labo, équipe de compétition) existe au-delà d'un seul projet. Poser cette entité maintenant évite un deuxième re-scope plus tard une fois tâches/bibliothèque/organisation entremêlées.
- **Continuité archi.** Même layering que le MVP (Controller → FormRequest → Service → Repository → Model, policies + middleware, DTOs typés) pour chaque nouveau domaine. Pas de nouvelle convention introduite sans raison.
- **Pas de données de production à préserver.** RoboForge est en développement actif, aucun CDC publié réel n'existe en dehors des données de démo. La refonte du CDC peut être une coupure franche (retrait des étapes concernées, renumérotation), pas une migration progressive.

## 2. Portée de la V2

### Inclus

1. **Organisation** : entité permanente au-dessus des projets, membres avec rôles (Owner/Admin/Member), rattachement optionnel des projets à une organisation.
2. **Administration plateforme** : rôle admin global, distinct des rôles projet/organisation.
3. **Bibliothèque de composants** : catalogue transversal (types, catégories, fiches composants), gestion admin uniquement, consultation par tous les membres.
4. **Choix techniques** : module par projet, nomenclature (BOM) construite depuis le catalogue, liée aux fonctions du CDC.
5. **Refonte du CDC** : retrait des étapes Architecture (6), Schéma Fonctionnel (9) et Matériel (10) ; renumérotation en 9 étapes.

### Différé (phases suivantes, hors scope de ce document)

- **Module Tâches/Planning** (subdivision en tâches, assignation, Kanban, avancement recalculé) — mentionné et validé sur le principe, mais pas encore priorisé ; l'étape 11 (Planning) du CDC reste en l'état jusqu'à l'arrivée de ce module.
- **Éditeur d'algorigramme** (remplacement de l'ancienne étape 9) — nécessite une brique graphique (type React Flow) non encore choisie ; dépend des Choix techniques (les nœuds référenceront des composants).
- Aperçu CAD natif, éditeur de câblage/pinout, export PDF, notifications, recherche globale, budget agrégé — capturés comme pistes identifiées, non planifiés ici.

## 3. Modèle de données cible

### Nouvelles tables

| Table | Colonnes essentielles | Notes |
| --- | --- | --- |
| `organizations` | `id` ULID, `owner_id`, `name`, `slug`, `description` | slug unique, soft delete |
| `organization_members` | `organization_id`, `user_id`, `role`, `joined_at` | unique organisation/utilisateur ; rôle : owner, admin, member |
| `component_categories` | `id`, `type`, `name`, `slug` | `type` = enum `ComponentType` ; unique par (type, slug) ; gérée par admin |
| `components` | `id` ULID, `component_category_id`, `name`, `manufacturer`, `reference`, `description`, `specs` JSON, `datasheet_path`, `price_cents`, `currency`, `supplier_url`, `is_active`, `created_by` | catalogue global ; `is_active=false` = retiré sans casser les projets qui l'utilisent déjà |
| `project_components` | `project_id`, `component_id`, `quantity`, `rationale`, `linked_function_ids` JSON, `added_by` | nomenclature (BOM) du projet ; `linked_function_ids` référence les IDs `F1`/`F2`... de l'étape 5 du CDC |

### Évolutions de tables existantes

- `projects` : ajout de `organization_id` ULID nullable, FK `nullOnDelete` (un projet peut rester "personnel" ou rejoindre une organisation).
- `users` : ajout de `is_platform_admin` boolean (défaut `false`). Pas de table de rôles séparée pour l'admin plateforme — c'est un privilège rare et binaire, pas un système RBAC à part entière.
- `requirements_documents` (`data` JSON) : les clés `step6`, `step9`, `step10` disparaissent du schéma de données par défaut (`RequirementsDefaults`) ; les étapes suivantes sont renumérotées (voir §5).

### Relations Eloquent

- `Organization` : owner, members, projects.
- `User` : ownedOrganizations, organizationMemberships.
- `Project` : organization (nullable), components (via `project_components`).
- `Component` : category, projects (via `project_components`).
- `ComponentCategory` : type (enum, pas de relation), components.

## 4. Architecture backend proposée

```text
app/
  Domain/
    Organizations/{Enums,DTOs,Contracts,Repositories,Services}/
    Components/{Enums,DTOs,Contracts,Repositories,Services}/
    Projects/... (existant, + lien organization)
    Requirements/... (existant, allégé des étapes 6/9/10)
  Http/
    Controllers/{Organization,Admin/Component,TechnicalChoice}/
    Requests/...
    Middleware/EnsureOrganizationMember.php
  Models/
    Organization.php, OrganizationMember.php, Component.php, ComponentCategory.php, ProjectComponent.php
  Policies/
    OrganizationPolicy.php, ComponentLibraryPolicy.php
```

Même layering que le MVP : Controller mince → FormRequest → Service (transactions, activité) → Repository (interface + Eloquent) → Model. `ComponentLibraryPolicy` s'appuie sur `$user->is_platform_admin`, pas sur une appartenance projet/organisation.

### Matrice de permissions (ajouts V2)

| Action | Admin plateforme | Owner Organisation | Admin Organisation | Membre Organisation | Rôles projet (existants) |
| --- | --- | --- | --- | --- | --- |
| Gérer le catalogue (types/catégories/composants) | oui | non | non | non | non |
| Créer une organisation | oui (+ tout utilisateur) | — | — | — | — |
| Gérer les membres de l'organisation | oui | oui | oui | non | — |
| Créer un projet sous l'organisation | oui | oui | oui | non* | — |
| Voir la liste des projets de l'organisation | oui | oui | oui | oui | — |
| Ajouter un composant à la nomenclature d'un projet | — | — | — | — | comme `editRequirements` (Owner/Manager/Mechanical/Electronics/Software/Contributor) |

\* Un membre d'organisation n'a pas automatiquement accès aux projets de l'organisation — l'accès à un projet reste gouverné par `project_members` (invitation/rôle), comme aujourd'hui. L'appartenance à l'organisation donne une visibilité de portefeuille, pas des droits d'édition.

## 5. Refonte du CDC — mapping des étapes

| Ancienne étape | Nouvelle étape | Devenir |
| --- | --- | --- |
| 1 Informations générales | 1 | inchangée |
| 2 Contexte & Problématique | 2 | inchangée |
| 3 Mission du Robot | 3 | inchangée |
| 4 Utilisateurs Cibles | 4 | inchangée |
| 5 Fonctions Principales | 5 | inchangée — les IDs `F1`/`F2`... deviennent le point d'ancrage de la traçabilité |
| 6 Architecture | — | **retirée** ; devient la nomenclature (Choix techniques), composants de type Capteur/Actionneur/Microcontrôleur/Source d'énergie |
| 7 Contraintes | 6 | renumérotée |
| 8 Performances | 7 | renumérotée |
| 9 Schéma Fonctionnel | — | **retirée** ; devient le module Algorigramme (phase différée) |
| 10 Matériel | — | **retirée** ; fusionne dans la nomenclature (Choix techniques) — c'était déjà une liste libre component/quantité/référence, redondante avec la nouvelle BOM |
| 11 Planning | 8 | renumérotée (reste en l'état jusqu'au futur module Tâches) |
| 12 Résultat attendu | 9 | renumérotée |

**Total : 12 étapes → 9 étapes.** `RequirementsStepRules`, `RequirementsDefaults`, `StepIndicator`, le switch de `Edit.tsx`, la vue `Show.tsx` et les composants `ArchitectureStep.tsx`/`FunctionalDiagramStep.tsx`/`MaterialsStep.tsx` sont concernés. Les tests couvrant l'ancien step 6/9/10 sont supprimés, ceux couvrant les steps renumérotés sont ajustés.

## 6. Phasage

### Lot A — Organisation

1. Enum `OrganizationRole` (Owner/Admin/Member), migrations `organizations` + `organization_members`, ajout `organization_id` nullable sur `projects`.
2. Modèles, repository, `OrganizationService` (création + owner automatique + activité, invitation de membres — réutilise le pattern `ProjectInvitationService`).
3. `OrganizationPolicy`, middleware `EnsureOrganizationMember`.
4. Pages : liste des organisations, création, détail (liste des projets rattachés), gestion des membres. Remplace le lien "Équipe globale" déjà prévu (commentaire "phase ultérieure") dans `AppLayout.tsx`.
5. `ProjectController::create` propose de rattacher le nouveau projet à une organisation existante (optionnel).
6. Tests : policy, service, controller.

**Livrable :** une organisation persiste plusieurs projets ; les rôles projet existants ne changent pas de comportement.

### Lot B — Administration plateforme + Bibliothèque de composants

1. `is_platform_admin` sur `users`, commande artisan `user:promote-admin {email}` pour désigner un admin (pas d'UI de promotion en V2, surface d'attaque minimale).
2. Enum `ComponentType` (Microcontroller, Sensor, Actuator, PreActuator, EnergySource, Effector), migrations `component_categories` + `components`.
3. `ComponentLibraryPolicy` (admin uniquement en écriture, tout utilisateur authentifié en lecture), repository, `ComponentLibraryService`.
4. Pages admin (`/admin/components`) : gestion des types/catégories/fiches ; pages publiques de consultation/recherche du catalogue.
5. Tests : policy (admin vs non-admin), service, controller.

**Livrable :** un admin alimente un catalogue transversal ; tous les utilisateurs peuvent le parcourir en lecture seule.

### Lot C — Refonte CDC + Choix techniques

1. Retirer les étapes 6/9/10 de `RequirementsStepRules`/`RequirementsDefaults`, renuméroter (voir §5), ajuster `Edit.tsx`/`Show.tsx`/`StepIndicator` et les tests associés.
2. Migration `project_components`, modèle `ProjectComponent`, repository, `TechnicalChoiceService` (ajouter/retirer un composant, lier à des fonctions, justification).
3. Policy : réutilise le même périmètre de rôles que `editRequirements`.
4. Page Choix techniques (`/projects/{project}/technical-choices`) : composants groupés par type, sélection depuis le catalogue (Lot B), liaison aux fonctions du CDC (étape 5), coût total agrégé.
5. Tests : service, controller, régression CDC (steps renumérotés).

**Livrable :** le CDC ne porte plus de détails techniques ; les choix techniques sont structurés, tracés aux fonctions, et n'impliquent plus de republier le CDC.

### Lots suivants (hors scope de ce document, à planifier plus tard)

- **Module Tâches/Planning** : migration de l'étape Planning, avancement de projet recalculé depuis les tâches réelles (remplace `ProjectProgressCalculator` actuel, qui reste un point de repère fixe en attendant).
- **Éditeur d'algorigramme** : dépend du choix d'une librairie graphique, référence les entrées de `project_components`.

## 7. Risques et points de vigilance

- **Rétrocompatibilité des projets existants** : les projets créés avant ce chantier ont des CDC avec `data.step6/9/10` renseignés. Puisqu'aucune donnée de production n'est à préserver, on repart d'un `migrate:fresh --seed` plutôt que d'écrire une migration de données — à confirmer si un jeu de données de démo doit être conservé au-delà du seeder.
- **Portée de "membre d'organisation" vs "membre de projet"** : le choix retenu (organisation = visibilité de portefeuille, pas d'accès automatique) évite de dupliquer un système de permissions ; à revalider si l'usage réel montre le contraire.
- **Bibliothèque et fichiers** : les fiches composants ont une datasheet, mais ce ne sont pas des `Resource` au sens du domaine existant (pas de `project_id`). Prévoir un stockage dédié simple (disque privé, route de téléchargement gardée par `ComponentLibraryPolicy`) plutôt que de forcer le modèle `Resource` à devenir nullable sur `project_id`.
