# CRUDEMO — Le CRUD avec Laravel Eloquent

Présentation interactive de **20 minutes** (25 slides) : chaque méthode Eloquent est expliquée,
puis exécutée **en direct** contre une vraie base SQLite. Chaque playground affiche la réponse JSON réelle,
le SQL réellement exécuté et l'état de la table avant / après la requête.

- Laravel 12 · PHP ≥ 8.2 · SQLite · Blade · Tailwind CSS 4 · Alpine.js · highlight.js · Lucide
- Polices auto-hébergées (Inter, JetBrains Mono) : **aucune connexion Internet requise** pendant la présentation.

---

## 1. Installation

Prérequis : PHP 8.2+ avec `pdo_sqlite`, Composer, Node.js 18+.

```bash
composer install
cp .env.example .env              # Windows (cmd) : copy .env.example .env
php artisan key:generate
```

Sous Windows, si le fichier `database/database.sqlite` n'existe pas :

```bash
php -r "touch('database/database.sqlite');"
```

## 2. Base de données : migrer et peupler

```bash
php artisan migrate:fresh --seed
```

Crée la table `products` et 12 produits déterministes (IDs 1 à 12, le n° 12 est déjà dans la corbeille).

## 3. Compiler les assets

```bash
npm install
npm run build
```

## 4. Lancer la présentation

```bash
php artisan serve
```

Ouvrir **http://127.0.0.1:8000** — puis `F` pour le plein écran, `Entrée` pour démarrer (le minuteur se lance).

> Pendant le développement de l'interface : `npm run dev` dans un second terminal (rechargement à chaud).

## 5. Réinitialiser la démonstration

Trois possibilités, au choix :

| Méthode | Commande |
|---|---|
| Bouton **Réinitialiser la base** (barre supérieure) | raccourci `Maj` + `R` |
| API | `curl -X POST http://127.0.0.1:8000/api/demo/reset -H "Accept: application/json"` |
| Ligne de commande | `php artisan migrate:fresh --seed` |

Le bouton et l'API restaurent exactement les 12 produits d'origine et remettent l'auto-incrément à zéro :
le prochain produit créé reçoit toujours l'ID 13.

## 6. Tests

```bash
php artisan test
```

70 tests de fonctionnalité (base SQLite en mémoire, la base de démonstration n'est pas touchée) :

| Fichier | Vérifie |
|---|---|
| `tests/Feature/Demo/*Test.php` | les 20 démonstrations : succès, erreurs 404 / 409 / 422, changements réels en base |
| `InspectionTest` | le SQL affiché est celui réellement exécuté ; différentiel avant / après exact ; réponse métier non modifiée |
| `PresentationTest` | 25 slides, durée totale 20 min, chaque endpoint affiché correspond au contrôleur démontré, le code affiché est le code réel, chaque requête par défaut et chaque scénario s'exécutent |
| `MethodCatalogueTest` | chaque méthode de la slide 25 existe dans la version de Laravel installée |
| `ResetAndSafetyTest` | réinitialisation fiable, endpoints de démo indisponibles hors environnement local, l'affichage ne modifie pas la base |
| `ProductControllerTest` | l'API REST classique (`apiResource`) |

---

## Raccourcis clavier

| Touche | Action |
|---|---|
| `←` `→` · `Page↑` `Page↓` | slide précédente / suivante |
| `Début` / `Fin` | première / dernière slide |
| `1` `2` `Entrée` | aller directement à la slide 12 |
| `Ctrl` + `Entrée` | **Send** : exécuter la requête de la slide |
| `F` | plein écran |
| `S` | plan des slides (barre latérale) |
| `N` | notes de l'orateur |
| `T` | thème clair / sombre |
| `P` | minuteur : lecture / pause |
| `Maj` + `R` | réinitialiser la base |
| `?` | aide |
| `Échap` | fermer une fenêtre / quitter un champ |

Les raccourcis sont ignorés pendant la saisie dans un champ ou l'éditeur JSON (sauf `Ctrl` + `Entrée` et `Échap`).

---

## Plan et correspondance slide → endpoint

| # | Slide | Endpoint | Contrôleur |
|---|---|---|---|
| 1 | Introduction au CRUD | — | — |
| 2 | Présentation de CRUDEMO | — | — |
| 3 | Cartographie des routes | `GET /api/products/{id}` (essai en direct) | `ProductController@show` |
| 4 | `save()` | `POST /api/demo/create/save` | `CreateDemoController@save` |
| 5 | `create()` | `POST /api/demo/create/create` | `CreateDemoController@create` |
| 6 | `insert()` | `POST /api/demo/create/insert` | `CreateDemoController@insert` |
| 7 | `firstOrCreate()` | `POST /api/demo/create/first-or-create` | `CreateDemoController@firstOrCreate` |
| 8 | `updateOrCreate()` | `POST /api/demo/create/update-or-create` | `CreateDemoController@updateOrCreate` |
| 9 | `all()` | `GET /api/demo/read/all` | `ReadDemoController@all` |
| 10 | `find()` | `GET /api/demo/read/find/{id}` | `ReadDemoController@find` |
| 11 | `findOrFail()` | `GET /api/demo/read/find-or-fail/{id}` | `ReadDemoController@findOrFail` |
| 12 | `where()->get()` | `GET /api/demo/read/filter` | `ReadDemoController@filter` |
| 13 | `paginate()` | `GET /api/demo/read/paginate` | `ReadDemoController@paginate` |
| 14 | `save()` | `PATCH /api/demo/update/save/{id}` | `UpdateDemoController@save` |
| 15 | `update()` | `PATCH /api/demo/update/direct/{id}` | `UpdateDemoController@update` |
| 16 | `where()->update()` | `PATCH /api/demo/update/bulk` | `UpdateDemoController@bulk` |
| 17 | `increment()` | `PATCH /api/demo/update/increment/{id}` | `UpdateDemoController@increment` |
| 18 | `decrement()` | `PATCH /api/demo/update/decrement/{id}` | `UpdateDemoController@decrement` |
| 19 | `delete()` | `DELETE /api/demo/delete/model/{id}` | `DeleteDemoController@delete` |
| 20 | `destroy()` | `DELETE /api/demo/delete/destroy` | `DeleteDemoController@destroy` |
| 21 | SoftDeletes | `GET /api/demo/delete/trashed` | `DeleteDemoController@trashed` |
| 22 | `restore()` | `PATCH /api/demo/delete/restore/{id}` | `DeleteDemoController@restore` |
| 23 | `forceDelete()` | `DELETE /api/demo/delete/force/{id}` | `DeleteDemoController@forceDelete` |
| 24 | Choisir la bonne méthode Eloquent | — | — |
| 25 | Aller plus loin avec Eloquent (catalogue interactif de 38 méthodes) | — | — |

Minutage : introduction 3 min · Create 4 · Read 4 · Update 4 · Delete 4 · conclusion 1 (45 s + 15 s) = **20 min**.
Les notes de l'orateur (`N`) indiquent la durée cible de chaque slide et l'heure prévue ; le minuteur affiche l'avance / le retard.

---

## Comment ça marche

**Le code affiché est le code exécuté.** Les slides lisent le corps des méthodes de contrôleur par réflexion
(`app/Presentation/SourceCode.php`). Modifier un contrôleur met la slide à jour automatiquement.

**Rien n'est simulé.** Chaque bouton *Send* envoie une vraie requête `fetch()` à l'API Laravel. La durée et la taille
affichées sont mesurées dans le navigateur.

**Instrumentation séparée du métier.** Le middleware `InspectDemoRequest` :

1. photographie la table `products` (requêtes marquées *inspection*) ;
2. exécute le contrôleur en enregistrant son SQL via `DB::listen` (requêtes marquées *operation*, avec bindings et durées réelles) ;
3. re-photographie la table et calcule le différentiel (insérée, modifiée, supprimée logiquement, restaurée, supprimée définitivement) ;
4. stocke le résultat en cache et ajoute seulement l'en-tête `X-Demo-Inspection` à la réponse.

La réponse JSON métier n'est jamais modifiée ; le navigateur récupère l'inspection via `GET /api/demo/inspections/{id}`.

**Sécurité.** Les routes `/api/demo/*`, l'inspection SQL et la réinitialisation n'existent qu'en environnement
`local` (et `testing`) — sinon 404. Validation systématique (`FormRequest` / `validate()`), `$fillable`, contrainte
UNIQUE sur `sku`, décrément de stock atomique et conditionnel, opérations de masse toujours filtrées,
`forceDelete()` soumis à une confirmation explicite.

### Fichiers principaux

```
routes/api.php                                  routes REST + 20 routes de démonstration
app/Models/Product.php                          modèle : $fillable, casts, SoftDeletes
database/migrations/…_create_products_table.php table products (sku UNIQUE, deleted_at)
database/seeders/ProductSeeder.php              12 produits déterministes
app/Http/Controllers/ProductController.php      CRUD REST classique (apiResource)
app/Http/Controllers/Demo/*DemoController.php   une méthode par technique Eloquent
app/Http/Middleware/InspectDemoRequest.php      capture SQL + différentiel de la table
app/Presentation/Slides.php                     contenu des 25 slides (textes, scénarios, notes)
app/Presentation/MethodCatalogue.php            catalogue de la slide 25
resources/views/                                interface Blade (slides, playground)
resources/js/                                   navigation, playground, catalogue (Alpine.js)
```

### Conseils pour la présentation

- Réinitialisez la base juste avant de commencer (`Maj` + `R`).
- Les scénarios (*Cas normal*, *ID inexistant*, *Données invalides*, *Stock insuffisant*…) préremplissent la requête : aucun JSON à taper.
- Les SKU des démonstrations de création sont générés aléatoirement (`{rand}`) : cliquez de nouveau sur *Cas normal* pour en obtenir un nouveau.
- Sur `firstOrCreate()`, cliquez **deux fois** sur *Send* sans rien changer : 201 puis 200.
- Sur `forceDelete()`, utilisez **Créer un produit jetable** : l'ID est prérempli automatiquement.
- Naviguer entre les slides ne modifie jamais la base : seules les actions *Send*, de préparation et de réinitialisation écrivent.
