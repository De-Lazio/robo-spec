# RoboForge

RoboForge est une plateforme de gestion de projets robotiques : cahier des charges multi-étapes, ressources techniques, équipe projet et liaison GitHub.

Le projet est construit avec Laravel 12, Inertia 2, React et SQLite pour le développement local.

## Prérequis

- PHP 8.2 ou supérieur
- Composer 2
- Node.js 20 et npm
- Extension PHP SQLite (`pdo_sqlite`)

## Installation locale

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
```

La configuration par défaut utilise SQLite, le français et le fuseau horaire `Africa/Lagos`. Les ressources projet seront stockées sur le disque Laravel privé `local` ; elles ne seront pas exposées directement depuis `public`.

## Développement

Dans un premier terminal :

```bash
php artisan serve
```

Dans un second terminal :

```bash
npm run dev
```

Pour traiter les tâches asynchrones lorsque le domaine métier les utilisera :

```bash
php artisan queue:listen
```

## Vérifications

```bash
php artisan test
./vendor/bin/pint --test
npm run build
```

## Conventions de code

- PHP : conventions Laravel/PSR-12, vérifiées avec Pint.
- Code (classes, routes, tables, variables) : anglais ; textes affichés : français.
- Composants React métier : TypeScript (`.tsx`) ; les pages Breeze existantes restent en JSX jusqu'à leur migration contrôlée.
- Dates : persistées au format ISO/UTC lorsque nécessaire, puis affichées selon la locale française.
- Montants : les futures valeurs monétaires seront stockées en unités mineures avec un code devise, jamais comme nombre flottant.

## Configuration locale utile

| Variable | Valeur par défaut | Rôle |
| --- | --- | --- |
| `DB_CONNECTION` | `sqlite` | Base locale |
| `APP_LOCALE` | `fr` | Langue de l'application |
| `APP_TIMEZONE` | `Africa/Lagos` | Fuseau horaire applicatif |
| `FILESYSTEM_DISK` | `local` | Disque privé Laravel |
| `ROBOFORGE_MAX_UPLOAD_KB` | `25600` | Taille maximale future d'une ressource (25 Mo) |
