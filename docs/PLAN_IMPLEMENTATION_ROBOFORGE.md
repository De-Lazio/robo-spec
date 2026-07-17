# Plan d'implémentation — RoboForge

## 1. Diagnostic et décision d'architecture

### État initial constaté

Le dépôt contient une application Laravel 12 avec Breeze, Inertia 2 et React 18. L'authentification, la vérification d'e-mail et le profil sont déjà présents, mais le domaine métier RoboForge n'est pas encore implémenté. La connexion par défaut est déjà SQLite et le fichier `database/database.sqlite` existe.

`resources/template_frontend` est une excellente référence fonctionnelle et visuelle : il couvre l'authentification, le tableau de bord, le détail d'un projet, le CDC à 12 étapes, les ressources, l'équipe et GitHub. En revanche, il s'agit d'un prototype autonome : état React local, fausses données et actions sans persistance. Il doit servir de référence de design et de parcours, pas être copié directement.

Le template utilise TypeScript, React 19, Tailwind 4 et `lucide-react`, alors que l'application cible utilise JSX, React 18 et Tailwind 3. Pour garder une base pérenne, l'implémentation migrera les pages RoboForge vers TypeScript (`.tsx`) et ajoutera `lucide-react`, sans faire une migration globale et risquée vers React 19 ou Tailwind 4 au début.

### Principes retenus

- Laravel reste responsable de l'autorisation, de la validation, des transactions, du stockage de fichiers et de l'intégration GitHub ; React/Inertia ne contient que l'expérience utilisateur et l'état transitoire de formulaire.
- Architecture modulaire par domaine : contrôleurs minces → Form Requests → services applicatifs → repositories (interfaces + Eloquent) → modèles.
- Les règles d'accès sont centralisées dans des Policies et des middlewares ; aucun contrôle de rôle ne doit reposer uniquement sur le frontend.
- SQLite est la base de départ. Les migrations, clés étrangères, index, UUID/ULID et types de données resteront portables vers MySQL/PostgreSQL.
- Le CDC est versionné par brouillon/publication et stocké dans un document JSON validé côté serveur : ses 12 sections forment un formulaire évolutif et sont toujours affichées/éditées ensemble. Les éléments réellement transverses et recherchables (projet, membre, ressource, intégration) restent normalisés.
- Les fichiers restent hors base de données, sur le disque privé Laravel. Seuls leurs métadonnées, leur chemin et leur contrôle d'accès sont persistés.
- Le calcul d'avancement est explicite, documenté et centralisé dans un service ; il ne doit pas être une valeur arbitraire saisie à plusieurs endroits.

## 2. Portée du premier incrément (MVP)

### Inclus

1. Comptes et profils existants, complétés par un avatar facultatif.
2. Création, consultation, modification, archivage/restauration d'un projet robotique.
3. Équipe projet avec rôles, invitation par e-mail et gestion des membres.
4. Cahier des charges multi-étapes, reprise d'un brouillon, validation par étape et publication.
5. Dépôt et gestion sécurisée de ressources : mécanique, électronique, informatique et autre.
6. Liaison initiale à un dépôt GitHub public par URL ; lecture des métadonnées, branches et derniers commits via GitHub API.
7. Dashboard, recherche simple, filtres et journal d'activité.
8. Tests automatisés, seeders de démonstration et documentation locale.

### Différé volontairement après le MVP

- édition collaborative en temps réel, commentaires/annotations sur fichiers ;
- synchronisation automatique GitHub par webhook, import de commits et gestion de plusieurs dépôts ;
- export PDF/Word du CDC, versionnage de fichiers et antivirus ;
- organisations multi-tenant, rôles institutionnels avancés et SSO ;
- visualisation native CAD/EDA/algorigrammes dans le navigateur.

Ces fonctions seront préparées par le modèle de données, mais ne doivent pas retarder le parcours principal.

## 3. Structure des pages et expérience utilisateur

### Direction artistique

Reprendre le langage visuel du template : fond bleu-gris très clair (`#f7faff`), surfaces blanches, bordures bleutées discrètes, un seul bleu d'action (`#2563eb`), états vert/ambre/rouge uniquement pour le sens métier. Police sans-serif moderne, rayon de 8–14 px, ombres faibles, espaces généreux. Le résultat doit être sobre, lisible et technique, non décoratif.

Créer un système de design dans `resources/js/Components/ui/` avec tokens CSS, boutons, champs, badges, tables, empty states, dialog de confirmation, toasts, avatar et upload zone. Le CSS du template est une source de référence à extraire et rationaliser ; les styles inline seront progressivement remplacés par classes/utilitaires réutilisables.

### Navigation globale authentifiée

- Sidebar : logo RoboForge, Tableau de bord, Mes projets, Équipe globale (phase ultérieure), Paramètres/profil ; liste condensée des projets accessibles et leur avancement.
- Header : fil d'Ariane, recherche, notifications (placeholder MVP), menu utilisateur.
- Responsive : sidebar repliable sur tablette, panneau mobile, tables converties en cartes ou défilement horizontal accessible.

### Pages et routes proposées

| Route | Page | Contenu principal |
| --- | --- | --- |
| `/dashboard` | Tableau de bord | cartes KPI, projets récents, filtres statut, recherche, bouton Nouveau projet, activité récente |
| `/projects` | Index projets | recherche, filtres (statut/type/domaine), tri, pagination, vues grille/liste |
| `/projects/create` | Création projet | informations minimales : nom, description, type, domaine, tags ; créateur ajouté comme chef |
| `/projects/{project}` | Aperçu projet | état, avancement, CDC, ressources, membres, jalons et dernier événement |
| `/projects/{project}/cdc` | CDC lecture | résumé par sections, complétude, version publiée et action Modifier |
| `/projects/{project}/cdc/edit` | Assistant CDC | 12 étapes, navigation précédent/suivant, sauvegarde brouillon, validation, publication |
| `/projects/{project}/resources` | Ressources | filtres catégorie/type, upload, recherche, liste/grille, téléchargement et suppression autorisée |
| `/projects/{project}/github` | GitHub | liaison URL, état de synchronisation, dépôt, branches et derniers commits |
| `/projects/{project}/members` | Équipe | membres, rôle, invitation, changement de rôle, retrait |
| `/projects/{project}/settings` | Paramètres | informations, statut, archivage/restauration, zone de suppression avec confirmation |
| `/profile` | Profil | écran Breeze adapté au système de design |

Les onglets de la fiche projet du template deviennent des routes Inertia distinctes afin d'obtenir URL partageable, chargement ciblé, autorisation claire et navigation navigateur fiable. Un sous-menu d'onglets conserve visuellement l'expérience de référence.

### Assistant CDC à 12 étapes

Chaque étape reprend le contenu du template et possède : indicateur d'étape, navigation clavier, sauvegarde brouillon, erreurs localisées, état complété/incomplet et validation côté serveur.

| Étape | Données | Validation MVP |
| --- | --- | --- |
| 1. Informations générales | nom, équipe, date, type, superviseur | nom, équipe et date requis |
| 2. Contexte | contexte, problématique, justification robot | trois textes requis |
| 3. Mission | liste de missions mesurables | au moins une mission non vide |
| 4. Utilisateurs | catégories et autre | au moins une catégorie ou autre |
| 5. Fonctions | F1…Fn | au moins une fonction non vide, identifiants uniques |
| 6. Architecture | capteurs, unité de contrôle, actionneurs, énergie | au moins un capteur/actionneur et unité/énergie |
| 7. Contraintes | techniques, budget, sécurité, environnement | sécurité et données critiques validées selon le projet |
| 8. Performance | critère, valeur attendue, unité si utile | au moins un critère |
| 9. Schéma fonctionnel | mission → perception → décision → action → retour | quatre maillons requis ; diagramme simple rendu côté client |
| 10. Matériel | composant, quantité, référence | composants non vides, quantités valides |
| 11. Planning | étape, date, état | étapes non vides, dates cohérentes |
| 12. Résultat attendu | résultat, critères de succès, méthode de test | résultat et critère de succès requis |

À la publication, valider l'ensemble des 12 étapes. Avant cela, autoriser la sauvegarde de brouillon avec validation partielle. La première étape préremplit le nom et le type depuis le projet mais ne les écrase pas sans confirmation explicite.

## 4. Modèle de données cible

### Tables métier

| Table | Colonnes essentielles | Notes |
| --- | --- | --- |
| `users` | existantes + `avatar_path`, `locale` | aucune hiérarchie globale imposée au MVP |
| `projects` | `id` ULID, `owner_id`, `name`, `slug`, `description`, `robot_type`, `domain`, `status`, `progress`, `archived_at` | index owner/statut, slug unique ; soft delete |
| `project_tags` | `project_id`, `name` | unique par projet ; plus simple et portable qu'un JSON de tags |
| `project_members` | `project_id`, `user_id`, `role`, `joined_at` | unique projet/utilisateur ; rôle : owner, manager, mechanical, electronics, software, contributor, viewer |
| `project_invitations` | `project_id`, `email`, `role`, `token`, `expires_at`, `accepted_at`, `invited_by` | jeton haché ; pas d'accès avant acceptation |
| `requirements_documents` | `project_id`, `version`, `status`, `current_step`, `data` JSON, `published_at`, `created_by`, `updated_by` | brouillon unique actif, versions publiées immuables |
| `resources` | `project_id`, `uploaded_by`, `category`, `kind`, `name`, `original_name`, `disk`, `path`, `mime_type`, `size_bytes`, `description`, `checksum` | index projet/catégorie/kind ; soft delete |
| `github_repositories` | `project_id`, `provider`, `owner`, `repository`, `url`, `default_branch`, `visibility`, `last_synced_at`, `sync_status`, `metadata` JSON | unique projet/provider/repo ; token jamais dans cette table sans chiffrement |
| `project_activities` | `project_id`, `actor_id` nullable, `event`, `subject_type`, `subject_id`, `properties` JSON | audit lisible : création, CDC, fichier, membre, GitHub |

### Relations Eloquent

- `User`: ownedProjects, projectMemberships, projects, uploadedResources, activities.
- `Project`: owner, members, tags, requirementsDocuments, currentRequirementsDocument, resources, githubRepositories, activities.
- `RequirementsDocument`: project, author, editor ; cast `data` en array.
- `Resource`: project, uploader.
- `ProjectMember`: project, user.

Toutes les migrations utiliseront des contraintes FK, suppression contrôlée et index. La suppression définitive d'un projet passera par une commande/service dédié qui supprime aussi les fichiers, jamais par une cascade silencieuse non auditée.

## 5. Architecture backend proposée

```text
app/
  Domain/
    Projects/{Enums,DTOs,Contracts,Repositories,Services,Events}/
    Requirements/{DTOs,Rules,Services}/
    Resources/{DTOs,Services}/
    Integrations/GitHub/{Clients,DTOs,Services}/
  Http/
    Controllers/{Project,Requirements,Resource,GitHub,Membership}/
    Requests/{Project,Requirements,Resource,GitHub,Membership}/
    Middleware/EnsureProjectMember.php
  Models/
  Policies/
  Notifications/
  Jobs/
  Support/
```

### Responsabilités

- **Controllers** : résolution de route-model binding, appel d'un service, réponse/redirect Inertia. Aucun SQL ou règle métier complexe.
- **Form Requests** : autorisation préliminaire et validation de payload ; une request par cas d'usage (`StoreProjectRequest`, `UpdateProjectRequest`, `SaveRequirementsStepRequest`, etc.).
- **DTOs** : données typées et normalisées pour chaque action ; pas de tableaux HTTP bruts dans les services.
- **Repositories** : contrats ciblés (`ProjectRepositoryInterface`, `ResourceRepositoryInterface`, `RequirementsDocumentRepositoryInterface`), implémentations Eloquent, requêtes réutilisables/paginées. Ne pas créer de repository générique opaque.
- **Services** : cas d'usage transactionnels : création projet + propriétaire + activité, sauvegarde CDC, publication, upload atomique, suppression de ressource, invitation, synchronisation GitHub.
- **Policies** : `ProjectPolicy`, `ResourcePolicy`, `RequirementsDocumentPolicy`, `GitHubRepositoryPolicy`. Les droits dépendent du rôle du membre, pas de l'ID propriétaire seul.
- **Middleware `EnsureProjectMember`** : vérifie l'appartenance pour toutes les routes imbriquées ; la policy affine ensuite l'action demandée. `auth` et `verified` encadrent l'espace projet.
- **Events/listeners** : `ProjectCreated`, `RequirementsSaved`, `ResourceUploaded`, `MemberInvited`, `GitHubRepositoryLinked` alimentent l'activité ; notifications placées en queue lorsque nécessaire.
- **API client GitHub** : interface `GitHubClientInterface`, implémentation HTTP Laravel, timeouts, cache court, mapping DTO ; testable avec `Http::fake()`.

### Matrice minimale de permissions

| Action | Owner | Manager | Contributeur métier | Viewer |
| --- | --- | --- | --- | --- |
| Voir projet/CDC/ressources | oui | oui | oui | oui |
| Modifier projet, statut, GitHub | oui | oui | non | non |
| Éditer/publier CDC | oui | oui | oui selon rôle | non |
| Ajouter une ressource | oui | oui | oui | non |
| Supprimer une ressource | oui | oui | seulement la sienne (option MVP) | non |
| Inviter/changer les rôles | oui | oui sauf owner | non | non |
| Archiver/supprimer | owner | non | non | non |

Les détails exacts seront encodés dans une enum de rôles et testés. Le rôle `owner` est unique par projet et ne peut pas être retiré sans transfert de propriété.

## 6. Ressources et sécurité des fichiers

1. Validation serveur : autorisation, taille maximale configurable, MIME réel via `finfo`, extension attendue et liste blanche par type.
2. Catégories : mécanique, électronique, informatique, autre. Types : image, document/PDF, code, CAO, schéma, archive, autre.
3. Stockage initial sur le disque `local` privé : `projects/{project-ulid}/resources/{resource-ulid}`. Aucun fichier n'est directement exposé par URL publique.
4. Téléchargement via contrôleur autorisé et nom d'origine assaini ; prévisualisation seulement pour images/PDF, après autorisation.
5. Transaction compensatoire : si la base échoue après écriture, supprimer le fichier ; si le stockage échoue, ne créer aucune ligne.
6. Journaliser upload, téléchargement (si nécessaire) et suppression. Prévoir la migration vers S3 dans `filesystems.php` sans changer le domaine.
7. Prévoir une tâche antivirus asynchrone et le statut `pending_scan/available/rejected` avant ouverture aux contenus non fiables ; MVP local : avertissement/documentation si antivirus non configuré.

## 7. Intégration GitHub progressive et sûre

### MVP : dépôt public par URL

1. L'utilisateur saisit une URL GitHub normalisée (`https://github.com/{owner}/{repo}`).
2. Le backend valide et extrait owner/repository, interroge l'API GitHub avec timeout, enregistre les métadonnées utiles et crée l'activité.
3. La page affiche dépôt, branche par défaut, langage, étoiles/forks/issues, branches et derniers commits ; le frontend ne contacte jamais GitHub directement.
4. Le bouton « synchroniser » déclenche un job ; le dernier état de sync est affiché.

### Phase suivante : dépôt privé

OAuth GitHub via Socialite ou GitHub App avec permissions minimales (`repo` seulement si nécessaire), jetons chiffrés par Laravel `Crypt`, révocation, redirection sécurisée, rate limiting et aucun secret injecté dans les props Inertia. Les webhooks ne seront ajoutés qu'après authentification de signature et gestion idempotente des événements.

## 8. Plan d'exécution étape par étape

### Phase 0 — Cadrage et socle local

- [x] Valider ce plan et figer les rôles MVP, la limite de fichiers et le périmètre GitHub public/privé.
- [x] Mettre à jour `.env` : `APP_NAME=RoboForge`, locale `fr`, SQLite, disque local privé, queue/cache database.
- [x] Vérifier `php artisan migrate`, tests Breeze, build Vite ; documenter les commandes de démarrage dans le README.
- [x] Installer uniquement les dépendances frontend nécessaires (`lucide-react`, typage React si migration TSX).
- [x] Définir conventions PHP (Pint), JS/TS (ESLint/Prettier si retenus), nommage français UI/anglais code, format de date/montant.

Critère de sortie : starter fonctionnel, build et tests verts, aucune dépendance du template frontend exécutée comme application séparée.

### Phase 1 — Fondation de domaine et autorisation

- [x] Créer les enums du domaine Projet : type de robot, statut et rôle membre.
- [x] Écrire les migrations et modèles Projet, tags, membres et activité ; ajouter casts et factory Projet.
- [x] Ajouter les relations User/Project et l'inscription automatique du créateur comme `owner`.
- [x] Créer repositories, contrats et bindings dans `AppServiceProvider`.
- [x] Créer policies, middleware d'appartenance et matrice de permissions testée.
- [x] Ajouter le journal d'activité de création de projet.

Critère de sortie : un utilisateur autorisé peut créer/lire son projet ; un autre utilisateur reçoit systématiquement 403 sur les routes et fichiers non autorisés.

### Phase 2 — Projets, dashboard et intégration de design

- [ ] Extraire les tokens, composants UI et layout depuis le template sans ses mock data ni navigation locale.
- [ ] Ajouter `AppLayout`, sidebar responsive, header, fil d'Ariane et navigation Inertia.
- [ ] Implémenter `ProjectController` et requests : index, create/store, show, edit/update, archive/restore/delete avec confirmation.
- [ ] Créer pages `Dashboard`, `Projects/Index`, `Projects/Create`, `Projects/Show`, `Projects/Settings` en TSX.
- [ ] Alimenter les KPI et listes à partir de requêtes paginées/ciblées ; définir le calcul de `progress` (CDC publié, jalons et ressources) dans un service.
- [ ] Adapter les pages Breeze au même thème, sans modifier le flux d'authentification déjà testé.

Critère de sortie : les parcours création → dashboard → fiche projet → modification sont persistants, autorisés et ne contiennent aucune donnée fictive.

### Phase 3 — Équipe et invitations

- [ ] Ajouter contrôleur/service/repository de membres et invitations.
- [ ] Construire pages équipe, formulaire d'invitation, changement de rôle et retrait de membre.
- [ ] Créer notification e-mail + route d'acceptation avec jeton expirant ; prévoir l'inscription si l'e-mail n'a pas encore de compte.
- [ ] Empêcher la suppression du dernier owner et auditer les changements de rôle.

Critère de sortie : une invitation crée un accès uniquement après acceptation ; les droits changent immédiatement et sont couverts par tests.

### Phase 4 — Cahier des charges multi-étapes

- [ ] Implémenter `RequirementsDocument`, DTO de document, règles de validation par étape et `RequirementsService` transactionnel.
- [ ] Créer routes lecture/édition, contrôleur, `SaveRequirementsStepRequest` et actions brouillon/publication.
- [ ] Migrer les 12 écrans du `CDCWizard` en composants TSX indépendants (`Steps/GeneralInformationStep`, etc.) et un shell `RequirementsWizard`.
- [ ] Conserver `current_step`, sauvegarder explicitement et, si pertinent, activer une sauvegarde automatique limitée avec indicateur d'état.
- [ ] Préremplir depuis le projet, afficher la complétude et rendre le schéma fonctionnel accessible (texte + diagramme non dépendant de la couleur).
- [ ] Ajouter vue de lecture, confirmation de publication et activité associée.

Critère de sortie : un brouillon survit à un rechargement, chaque étape refuse ses entrées invalides, la publication exige un CDC complet et une version publiée reste consultable.

### Phase 5 — Ressources projet

- [ ] Définir configuration de fichiers et règles par catégorie/type.
- [ ] Implémenter `ResourceService`, contrôleur, upload multipart, téléchargement autorisé, suppression et nettoyage compensatoire.
- [ ] Créer la page Ressources : dépôt glisser-déposer, progression, filtres, recherche, tableaux/cartes et prévisualisation sûre.
- [ ] Ajouter les politiques et tests de traversée de chemin, MIME, taille, accès inter-projet et suppression.
- [ ] Configurer le lien de stockage uniquement si des fichiers publics deviennent nécessaires ; sinon rester sur contrôleurs privés.

Critère de sortie : les fichiers sont persistés hors public, téléchargeables uniquement par les membres autorisés et supprimés de façon cohérente.

### Phase 6 — GitHub public puis synchronisation

- [ ] Ajouter config GitHub, `GitHubClientInterface`, client HTTP, DTOs, service et job de synchronisation.
- [ ] Implémenter liaison/déliaison/manuelle sync, policies et page GitHub du template alimentée par le backend.
- [ ] Gérer explicitement URL invalide, dépôt absent/privé, délai API et rate limiting ; ne jamais bloquer le rendu de page sur l'API.
- [ ] Ajouter `Http::fake()` aux tests et cache de métadonnées.

Critère de sortie : un dépôt public valide est lié et rafraîchissable ; une erreur GitHub est claire, traçable et sans fuite de secret.

### Phase 7 — Qualité, exploitation et livraison

- [ ] Tests Feature : auth, projets, policy, CDC, ressources, équipe, GitHub.
- [ ] Tests Unit : services, DTO/règles, repositories, calcul d'avancement, parsing URL GitHub.
- [ ] Factories/seeders : utilisateurs, projets complets, CDC, ressources de métadonnées (sans gros binaires), membres.
- [ ] Lancer Pint, tests PHP, build Vite, vérification responsive et audit manuel d'accessibilité (focus, clavier, contraste, erreurs annoncées).
- [ ] Ajouter pages d'erreur Inertia 403/404/419/500 cohérentes avec le design.
- [ ] Documenter installation SQLite, stockage, queue, configuration GitHub et stratégie de passage à MySQL/PostgreSQL/S3.

Critère de sortie : CI locale reproductible et parcours MVP testé de bout en bout.

## 9. Routes, contrôleurs et requests à créer

```text
ProjectController
  index, create, store, show, edit, update, archive, restore, destroy
RequirementsDocumentController
  show, edit, saveStep, saveDraft, publish
ResourceController
  index, store, download, preview, destroy
ProjectMemberController
  index, storeInvitation, acceptInvitation, update, destroy
GitHubRepositoryController
  show, store, sync, destroy
```

Les routes seront nommées, regroupées sous `auth`, `verified` et `project.member` lorsque le projet est concerné. Les `FormRequest` correspondants restent distincts des contrôleurs afin que les validations soient lisibles et testables.

## 10. Risques et décisions à vérifier ensemble avant le code

1. **Organisation des équipes** : MVP centré sur un projet et ses membres, ou faut-il dès maintenant une organisation/école/entreprise avec administrateurs ? Cela change fortement le modèle d'autorisation.
2. **GitHub** : commencer uniquement par les dépôts publics est le choix le plus sûr et rapide ; les dépôts privés nécessitent OAuth/GitHub App et une gestion de secrets.
3. **Fichiers CAO/EDA** : une prévisualisation native ne sera pas fiable pour tous les formats. Le MVP offrira upload, métadonnées, téléchargement et aperçu image/PDF.
4. **CDC** : le JSON versionné est le meilleur compromis pour démarrer. Si des exports réglementaires ou des statistiques fines deviennent prioritaires, certaines données pourront être extraites dans des tables normalisées plus tard.
5. **Suppression** : recommander archivage et soft delete ; la suppression définitive devra demander une confirmation textuelle et être réservée au owner.

## 11. Ordre recommandé de réalisation

Commencer par les phases 0, 1 et 2 : elles donnent un projet réel, sécurisé et visuellement proche de la référence. Ensuite réaliser CDC (phase 4) et ressources (phase 5), qui constituent le cœur métier. Équipe (phase 3) et GitHub (phase 6) s'ajoutent ensuite sur une architecture déjà stable. La phase 7 accompagne chaque étape, mais sert de porte de sortie avant livraison.

## 12. Plan d'exécution opérationnel

Chaque lot doit être terminé, vérifié et validé avant de commencer le suivant. Les migrations ne seront jamais modifiées après usage : toute évolution ultérieure crée une nouvelle migration.

### Lot 0 — Préparer le socle (ordre d'exécution)

1. Vérifier l'état de l'application : version PHP/Node, installation Composer/npm, configuration SQLite, migrations et tests Breeze.
2. Définir les valeurs de configuration locales : application `RoboForge`, français, fuseau horaire retenu, disque de fichiers privé et limites d'upload.
3. Créer les dossiers de domaine et conventions d'import sans déplacer le code Breeze existant.
4. Installer `lucide-react` et préparer la résolution des pages `.tsx` dans Inertia.
5. Ajouter les scripts de contrôle qualité documentés : formatage PHP, tests et build frontend.

**Livrable :** starter Laravel/Inertia utilisable en SQLite, documenté et vérifié ; aucun écran RoboForge métier à ce stade.

### Lot 1 — Créer le noyau Projet

1. Créer les enums `ProjectStatus`, `RobotType` et `ProjectMemberRole`.
2. Créer les migrations `projects`, `project_tags`, `project_members` et `project_activities`, avec ULID, index et clés étrangères.
3. Créer les modèles, casts, relations et factories associés.
4. Étendre `User` avec les relations de propriétaire et de membre.
5. Créer les contrats de repository et leurs implémentations Eloquent ciblées.
6. Créer `ProjectService::create`, qui crée le projet, ajoute son créateur en owner et écrit une activité dans une transaction.
7. Créer `ProjectPolicy` et le middleware d'appartenance projet.
8. Écrire les tests unitaires/feature de création, visibilité et refus d'accès.

**Livrable :** domaine Projet persistant et sécurisé, testable sans interface graphique.

### Lot 2 — Brancher les pages Projet au design de référence

1. Extraire les tokens de couleur, typographie, composants UI et layout du template dans l'application Inertia.
2. Créer `AppLayout` responsive et remplacer progressivement le layout Breeze authentifié, sans modifier les contrôleurs d'authentification.
3. Créer `StoreProjectRequest`, `UpdateProjectRequest` et `ProjectController`.
4. Ajouter les routes nommées dashboard, index, création, création persistée, aperçu et paramètres.
5. Créer les pages TSX Dashboard, index projets, formulaire de création, aperçu et paramètres.
6. Connecter les cartes, filtres et KPI aux props réellement fournies par Laravel ; supprimer toute dépendance aux mock data.
7. Ajouter archivage/restauration avec modal de confirmation et policy owner.
8. Vérifier navigation, responsive, erreurs de formulaire et pages 403/404.

**Livrable :** un utilisateur crée et gère ses projets depuis une interface fidèle au design de référence.

### Lot 3 — Implémenter le Cahier des charges

1. Créer migration et modèle `requirements_documents` ; ajouter le cast JSON et les statuts brouillon/publié.
2. Définir le DTO du document, les valeurs par défaut des 12 étapes et les règles de validation réutilisables.
3. Créer le repository et `RequirementsService` avec sauvegarde transactionnelle, mise à jour de `current_step`, publication et activité.
4. Créer les policies, routes, contrôleur et Form Requests de lecture/édition/sauvegarde/publication.
5. Migrer le shell `CDCWizard` et ses composants génériques (tableau dynamique, liste, indicateur, champ) en TSX réutilisable.
6. Migrer les étapes 1 à 4 et vérifier brouillon/reprise/erreurs.
7. Migrer les étapes 5 à 8 et leurs tableaux/listes dynamiques.
8. Migrer les étapes 9 à 12, la vue de lecture et l'indicateur de complétude.
9. Ajouter confirmation de publication et interdire une publication incomplète côté serveur.
10. Tester chaque étape, les droits de modification et la persistance après rechargement.

**Livrable :** CDC complet à 12 étapes, récupérable comme brouillon et publiable de manière fiable.

### Lot 4 — Ajouter les ressources techniques

1. Créer les enums de catégories/types/statuts de ressource et la migration `resources`.
2. Déclarer le disque privé et la configuration de tailles/MIME autorisés.
3. Créer modèle, repository, policy et `ResourceService` (upload, téléchargement, suppression et compensation en cas d'erreur).
4. Créer `StoreResourceRequest`, contrôleur et routes sécurisées.
5. Créer la page Ressources à partir de la référence : dépôt de fichier, filtres, recherche, liste/grille, aperçu image/PDF lorsque autorisé.
6. Ajouter les tests de type MIME, taille, accès privé, accès inter-projet et suppression de fichier.
7. Journaliser les uploads/suppressions dans l'activité projet.

**Livrable :** ressources mécaniques, électroniques, informatiques et autres stockées de façon privée et contrôlée.

### Lot 5 — Ajouter l'équipe projet

1. Créer migration `project_invitations`, modèle et relation projet.
2. Créer services d'invitation, acceptation, changement de rôle, retrait et transfert éventuel de propriété.
3. Créer notification e-mail mise en queue et route sécurisée d'acceptation.
4. Créer contrôleur, requests et page Équipe du projet.
5. Tester expiration de jeton, refus du dernier owner, changement des permissions et ajout d'un nouvel utilisateur.

**Livrable :** équipe projet avec rôles et invitations sécurisées.

### Lot 6 — Lier GitHub

1. Ajouter la configuration GitHub et définir le contrat du client API.
2. Créer migration/modèle `github_repositories`, repository et service de liaison.
3. Implémenter l'analyse d'URL, la récupération sécurisée des métadonnées d'un dépôt public et la gestion d'erreurs.
4. Ajouter le job de synchronisation et les statuts de synchronisation.
5. Créer contrôleur, policies, routes et page GitHub du projet.
6. Tester le client avec `Http::fake()`, les erreurs 404/rate limit et la déliaison.

**Livrable :** lien fiable à un dépôt GitHub public, sans jeton utilisateur ni secret exposé.

### Lot 7 — Finaliser et livrer le MVP

1. Revoir les calculs de KPI/avancement et supprimer les éventuelles requêtes N+1.
2. Compléter factories, seeders et données de démonstration.
3. Lancer l'ensemble des tests, Pint et build Vite ; corriger tout échec.
4. Vérifier manuellement desktop/tablette/mobile, clavier, focus, contraste et messages d'erreur.
5. Ajouter README de démarrage SQLite, configuration GitHub, stratégie des fichiers et commandes de contrôle.
6. Exécuter une recette complète : inscription → projet → CDC → fichier → membre → GitHub → archivage.

**Livrable :** MVP RoboForge cohérent, testé, documenté et prêt pour une démonstration locale.

### Séquence de démarrage immédiate

Lorsque ce plan est validé, nous démarrons par le **Lot 0**, puis le **Lot 1**. Le premier changement métier sera la création du noyau Projet (migrations, modèles, enums, policies, service/repository et tests) ; l'interface complète viendra seulement après cette fondation.
