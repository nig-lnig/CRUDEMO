<?php

namespace App\Presentation;

use App\Http\Controllers\Demo\CreateDemoController;
use App\Http\Controllers\Demo\DeleteDemoController;
use App\Http\Controllers\Demo\ReadDemoController;
use App\Http\Controllers\Demo\UpdateDemoController;

/**
 * Contenu des 25 slides de CRUDEMO.
 *
 * Les slides « method » décrivent : le texte pédagogique, la méthode de contrôleur
 * démontrée (son code est extrait par réflexion) et la requête HTTP du playground.
 * Le jeton {rand} dans un corps de requête est remplacé côté navigateur par
 * 4 chiffres aléatoires (SKU uniques → démonstrations répétables).
 */
class Slides
{
    public const SECTIONS = [
        'intro' => ['label' => 'Introduction', 'short' => 'Intro', 'minutes' => 3],
        'create' => ['label' => 'Create', 'short' => 'C', 'fr' => 'Créer', 'minutes' => 4],
        'read' => ['label' => 'Read', 'short' => 'R', 'fr' => 'Lire', 'minutes' => 4],
        'update' => ['label' => 'Update', 'short' => 'U', 'fr' => 'Modifier', 'minutes' => 4],
        'delete' => ['label' => 'Delete', 'short' => 'D', 'fr' => 'Supprimer', 'minutes' => 4],
        'conclusion' => ['label' => 'Conclusion', 'short' => 'Fin', 'minutes' => 1],
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        $slides = [
            ...self::introduction(),
            ...self::create(),
            ...self::read(),
            ...self::update(),
            ...self::delete(),
            ...self::conclusion(),
        ];

        foreach ($slides as $index => &$slide) {
            $slide['number'] = $index + 1;

            if ($slide['type'] === 'method') {
                [$class, $method] = $slide['source'];
                $slide['code'] = SourceCode::method($class, $method);
                $slide['sourceLabel'] = class_basename($class).'@'.$method;
                $slide['sourceFile'] = 'app/Http/Controllers/Demo/'.class_basename($class).'.php';
                $slide['request'] += ['params' => [], 'query' => [], 'body' => null];
                $slide += ['scenarios' => [], 'setup' => [], 'predict' => null, 'compare' => null, 'confirm' => null];
            }
        }

        return $slides;
    }

    private static function introduction(): array
    {
        return [
            [
                'id' => 'intro-crud',
                'type' => 'intro-crud',
                'section' => 'intro',
                'title' => 'Introduction au CRUD',
                'duration' => 60,
                'notes' => "Accroche : presque toutes les applications web se résument à 4 opérations sur des données. "
                    ."Prenez l'exemple du catalogue de produits : ajouter un produit (Create), afficher la liste (Read), "
                    ."changer un prix (Update), retirer un produit (Delete). Montrez la correspondance HTTP ↔ SQL ↔ Eloquent : "
                    ."c'est le fil conducteur des 20 démonstrations.",
            ],
            [
                'id' => 'intro-app',
                'type' => 'intro-app',
                'section' => 'intro',
                'title' => 'Présentation de CRUDEMO',
                'duration' => 60,
                'notes' => "Cliquez sur chaque fichier : le contenu affiché est le vrai fichier du projet. "
                    ."Insistez sur trois éléments : la migration (contrainte UNIQUE sur sku, deleted_at), le modèle "
                    ."(\$fillable, casts, SoftDeletes) et les contrôleurs de démonstration. Base SQLite : 12 produits, dont 1 déjà dans la corbeille.",
            ],
            [
                'id' => 'intro-routes',
                'type' => 'intro-routes',
                'section' => 'intro',
                'title' => 'Cartographie des routes',
                'duration' => 60,
                'notes' => "Suivez le trajet d'une requête : route → contrôleur → Eloquent → SQL → JSON. "
                    ."Cliquez sur « Essayer » pour exécuter GET /api/products/1 en direct. Le tableau montre l'API REST "
                    ."classique (apiResource) ; ensuite chaque slide utilise une route /api/demo/… dédiée à UNE méthode Eloquent.",
            ],
        ];
    }

    private static function create(): array
    {
        $validProduct = [
            'name' => 'Lampe de bureau LED',
            'sku' => 'MOB-{rand}',
            'category' => 'Mobilier',
            'price' => 39.90,
            'stock' => 25,
        ];

        $invalidProduct = [
            'name' => '',
            'sku' => 'INF-001',
            'category' => 'Mobilier',
            'price' => -5,
            'stock' => 'beaucoup',
        ];

        return [
            [
                'id' => 'create-save',
                'type' => 'method',
                'section' => 'create',
                'title' => 'save()',
                'method' => 'save()',
                'definition' => "On instancie un modèle en mémoire, on assigne ses attributs un par un, puis save() exécute l'INSERT.",
                'useCase' => 'Construire un produit étape par étape, avec une logique entre les affectations.',
                'source' => [CreateDemoController::class, 'save'],
                'highlight' => ['save'],
                'returns' => 'bool',
                'returnsNote' => "true si l'INSERT a réussi — le modèle reçoit alors son id",
                'tip' => ['kind' => 'mistake', 'text' => "Oublier save() : sans lui, l'objet n'existe qu'en mémoire et rien n'est écrit en base."],
                'predict' => [
                    'question' => 'Le JSON renvoyé contiendra-t-il un id et des dates ?',
                    'answer' => "Oui : après l'INSERT, Eloquent récupère l'id auto-incrémenté et remplit created_at / updated_at.",
                ],
                'request' => ['method' => 'POST', 'route' => '/api/demo/create/save', 'body' => $validProduct],
                'scenarios' => [
                    ['label' => 'Cas normal', 'tone' => 'ok', 'body' => $validProduct],
                    ['label' => 'Données invalides', 'tone' => 'error', 'body' => $invalidProduct],
                ],
                'duration' => 48,
                'notes' => "Montrez le new Product() puis les affectations. Envoyez : 201 Created, l'id apparaît. "
                    ."Onglet SQL : un seul INSERT, précédé du SELECT de la règle unique. Onglet Base : la nouvelle ligne en vert. "
                    ."Puis « Données invalides » : 422, rien n'est écrit.",
            ],
            [
                'id' => 'create-create',
                'type' => 'method',
                'section' => 'create',
                'title' => 'create()',
                'method' => 'create()',
                'definition' => 'Crée ET enregistre un modèle en une seule ligne, à partir d\'un tableau d\'attributs validés.',
                'useCase' => 'Le cas le plus courant : enregistrer les données d\'un formulaire validé.',
                'source' => [CreateDemoController::class, 'create'],
                'highlight' => ['create'],
                'returns' => 'Product',
                'returnsNote' => 'le modèle créé, avec son id et ses timestamps',
                'tip' => ['kind' => 'practice', 'text' => "\$fillable liste les attributs autorisés en assignation de masse. Un champ hors liste (ex. id, is_admin) est ignoré : c'est une protection."],
                'predict' => [
                    'question' => 'Si on envoie "id": 500 dans le corps, le produit aura-t-il l\'id 500 ?',
                    'answer' => "Non : id n'est ni validé ni dans \$fillable, il est ignoré. La base attribue l'id suivant.",
                ],
                'request' => ['method' => 'POST', 'route' => '/api/demo/create/create', 'body' => [...$validProduct, 'name' => 'Tapis de souris XXL', 'sku' => 'ACC-{rand}', 'category' => 'Accessoires', 'price' => 19.90]],
                'scenarios' => [
                    ['label' => 'Cas normal', 'tone' => 'ok', 'body' => [...$validProduct, 'name' => 'Tapis de souris XXL', 'sku' => 'ACC-{rand}', 'category' => 'Accessoires', 'price' => 19.90]],
                    ['label' => 'Champ hors $fillable', 'tone' => 'warn', 'body' => ['id' => 500, ...$validProduct, 'name' => 'Support écran', 'sku' => 'ACC-{rand}', 'category' => 'Accessoires']],
                    ['label' => 'Données invalides', 'tone' => 'error', 'body' => $invalidProduct],
                ],
                'duration' => 48,
                'notes' => "Une ligne remplace les 7 de la slide précédente. Expliquez \$fillable en 10 secondes, "
                    ."puis lancez « Champ hors \$fillable » : l'id 500 est ignoré. Même SQL que save().",
            ],
            [
                'id' => 'create-insert',
                'type' => 'method',
                'section' => 'create',
                'title' => 'insert()',
                'method' => 'insert()',
                'definition' => 'Insère plusieurs lignes en UNE requête SQL, via le Query Builder, sans créer d\'objets modèle.',
                'useCase' => 'Importer un lot de produits (fichier CSV, synchronisation) le plus rapidement possible.',
                'source' => [CreateDemoController::class, 'insert'],
                'highlight' => ['insert'],
                'returns' => 'bool',
                'returnsNote' => 'true / false — pas de modèles, pas d\'id',
                'tip' => ['kind' => 'mistake', 'text' => 'insert() contourne $fillable, les casts, les événements et les timestamps : validez soigneusement et ajoutez created_at vous-même si besoin.'],
                'compare' => [
                    'title' => 'create() vs insert()',
                    'headers' => ['create()', 'insert()'],
                    'rows' => [
                        ['Requêtes SQL', '1 par produit', '1 pour tout le lot'],
                        ['Retour', 'Product', 'bool'],
                        ['Timestamps', 'automatiques', 'NULL'],
                        ['Événements, casts', 'oui', 'non'],
                        ['$fillable', 'appliqué', 'ignoré'],
                    ],
                ],
                'predict' => [
                    'question' => 'Quelle valeur aura created_at pour ces nouvelles lignes ?',
                    'answer' => 'NULL ! insert() ne passe pas par le modèle. Regardez l\'onglet Base de données.',
                ],
                'request' => ['method' => 'POST', 'route' => '/api/demo/create/insert', 'body' => ['products' => [
                    ['name' => 'Câble USB-C 2 m', 'sku' => 'CAB-{rand}-A', 'category' => 'Accessoires', 'price' => 12.90, 'stock' => 100],
                    ['name' => 'Adaptateur HDMI', 'sku' => 'CAB-{rand}-B', 'category' => 'Accessoires', 'price' => 17.50, 'stock' => 40],
                    ['name' => 'Repose-poignets', 'sku' => 'CAB-{rand}-C', 'category' => 'Accessoires', 'price' => 22.00, 'stock' => 15],
                ]]],
                'scenarios' => [
                    ['label' => 'Cas normal (3 produits)', 'tone' => 'ok', 'body' => ['products' => [
                        ['name' => 'Câble USB-C 2 m', 'sku' => 'CAB-{rand}-A', 'category' => 'Accessoires', 'price' => 12.90, 'stock' => 100],
                        ['name' => 'Adaptateur HDMI', 'sku' => 'CAB-{rand}-B', 'category' => 'Accessoires', 'price' => 17.50, 'stock' => 40],
                        ['name' => 'Repose-poignets', 'sku' => 'CAB-{rand}-C', 'category' => 'Accessoires', 'price' => 22.00, 'stock' => 15],
                    ]]],
                    ['label' => 'Données invalides', 'tone' => 'error', 'body' => ['products' => [
                        ['name' => 'Doublon A', 'sku' => 'DUP-1', 'category' => 'Accessoires', 'price' => 10, 'stock' => 1],
                        ['name' => 'Doublon B', 'sku' => 'DUP-1', 'category' => 'Accessoires', 'price' => 10, 'stock' => 1],
                    ]]],
                ],
                'duration' => 48,
                'notes' => "Posez la question de prédiction sur created_at. Envoyez : onglet SQL → UN seul INSERT avec 3 lignes de valeurs. "
                    ."Onglet Base → created_at et updated_at à NULL. Le tableau de comparaison résume create() vs insert().",
            ],
            [
                'id' => 'create-first-or-create',
                'type' => 'method',
                'section' => 'create',
                'title' => 'firstOrCreate()',
                'method' => 'firstOrCreate()',
                'definition' => 'Cherche un enregistrement selon des critères ; s\'il n\'existe pas, le crée avec les valeurs fournies.',
                'useCase' => 'Importer un produit sans risquer de le créer deux fois (même SKU).',
                'source' => [CreateDemoController::class, 'firstOrCreate'],
                'highlight' => ['firstOrCreate'],
                'returns' => 'Product',
                'returnsNote' => 'existant ou nouveau — $product->wasRecentlyCreated le précise',
                'tip' => ['kind' => 'practice', 'text' => 'Le SELECT puis l\'INSERT ne sont pas atomiques : seule une contrainte UNIQUE en base empêche un doublon si deux requêtes arrivent en même temps.'],
                'predict' => [
                    'question' => 'Que se passe-t-il si on envoie deux fois la même requête ?',
                    'answer' => '1er envoi : 201 Created (SELECT + INSERT). 2e envoi : 200 OK, le produit existant est renvoyé — un seul SELECT, aucune écriture.',
                ],
                'request' => ['method' => 'POST', 'route' => '/api/demo/create/first-or-create', 'body' => ['sku' => 'ACC-HUB-{rand}', 'name' => 'Hub USB-C 4 ports', 'category' => 'Accessoires', 'price' => 29.90, 'stock' => 18]],
                'scenarios' => [
                    ['label' => 'Produit inexistant', 'tone' => 'ok', 'body' => ['sku' => 'ACC-HUB-{rand}', 'name' => 'Hub USB-C 4 ports', 'category' => 'Accessoires', 'price' => 29.90, 'stock' => 18]],
                    ['label' => 'Produit existant', 'tone' => 'warn', 'body' => ['sku' => 'INF-001', 'name' => 'Nom ignoré', 'category' => 'Informatique', 'price' => 1, 'stock' => 1]],
                    ['label' => 'Données invalides', 'tone' => 'error', 'body' => ['sku' => '', 'name' => 'Sans SKU', 'category' => 'Accessoires', 'price' => 'gratuit', 'stock' => 1]],
                ],
                'duration' => 48,
                'notes' => "Posez la question, puis cliquez Send DEUX fois sans rien changer : 201 puis 200. "
                    ."Comparez l'onglet SQL des deux envois. « Produit existant » : INF-001 est renvoyé tel quel, le nom et le prix envoyés sont ignorés.",
            ],
            [
                'id' => 'create-update-or-create',
                'type' => 'method',
                'section' => 'create',
                'title' => 'updateOrCreate()',
                'method' => 'updateOrCreate()',
                'definition' => 'Cherche selon des critères : met à jour l\'enregistrement trouvé, ou le crée s\'il n\'existe pas.',
                'useCase' => 'Synchroniser un catalogue fournisseur : nouveaux produits créés, existants mis à jour.',
                'source' => [CreateDemoController::class, 'updateOrCreate'],
                'highlight' => ['updateOrCreate'],
                'returns' => 'Product',
                'returnsNote' => 'le modèle à jour — wasRecentlyCreated indique une création',
                'tip' => ['kind' => 'mistake', 'text' => '1er tableau = critères de recherche, 2e = valeurs à écrire. Mettre le prix dans les critères créerait un nouveau produit à chaque changement de prix.'],
                'compare' => [
                    'title' => 'firstOrCreate() vs updateOrCreate()',
                    'headers' => ['firstOrCreate()', 'updateOrCreate()'],
                    'rows' => [
                        ['Produit absent', 'INSERT', 'INSERT'],
                        ['Produit présent', 'renvoyé tel quel', 'mis à jour'],
                        ['SQL si présent', 'SELECT', 'SELECT + UPDATE'],
                        ['Intention', 's\'assurer qu\'il existe', 'synchroniser'],
                    ],
                ],
                'predict' => [
                    'question' => 'Le SKU INF-002 existe déjà. Son prix va-t-il changer ?',
                    'answer' => 'Oui : updateOrCreate() écrit les nouvelles valeurs (UPDATE). firstOrCreate() les aurait ignorées.',
                ],
                'request' => ['method' => 'POST', 'route' => '/api/demo/create/update-or-create', 'body' => ['sku' => 'INF-002', 'name' => 'Écran 27 pouces 4K', 'category' => 'Informatique', 'price' => 399.00, 'stock' => 20]],
                'scenarios' => [
                    ['label' => 'Produit existant', 'tone' => 'warn', 'body' => ['sku' => 'INF-002', 'name' => 'Écran 27 pouces 4K', 'category' => 'Informatique', 'price' => 399.00, 'stock' => 20]],
                    ['label' => 'Produit inexistant', 'tone' => 'ok', 'body' => ['sku' => 'INF-{rand}', 'name' => 'Station d\'accueil USB-C', 'category' => 'Informatique', 'price' => 189.00, 'stock' => 10]],
                    ['label' => 'Données invalides', 'tone' => 'error', 'body' => ['sku' => 'INF-002', 'name' => '', 'category' => 'Informatique', 'price' => -1, 'stock' => 20]],
                ],
                'duration' => 48,
                'notes' => "Envoyez le cas existant : 200 OK, onglet Base → prix et stock en orange (modifiés). "
                    ."Puis « Produit inexistant » : 201, ligne verte. Terminez par le tableau de comparaison avec firstOrCreate().",
            ],
        ];
    }

    private static function read(): array
    {
        return [
            [
                'id' => 'read-all',
                'type' => 'method',
                'section' => 'read',
                'title' => 'all()',
                'method' => 'all()',
                'definition' => 'Récupère TOUS les enregistrements de la table dans une Collection Eloquent.',
                'useCase' => 'Petites tables de référence : catégories, pays, statuts…',
                'source' => [ReadDemoController::class, 'all'],
                'highlight' => ['all'],
                'returns' => 'Collection<int, Product>',
                'returnsNote' => 'une Collection (tableau amélioré : map, filter, count…)',
                'tip' => ['kind' => 'mistake', 'text' => 'Sur 100 000 lignes, all() charge tout en mémoire et dans la réponse. Préférez paginate() ou un where().'],
                'predict' => [
                    'question' => 'La base contient 12 produits. Combien en recevrons-nous ?',
                    'answer' => '11 ! Le produit #12 est dans la corbeille : SoftDeletes ajoute automatiquement « deleted_at is null » (voir l\'onglet SQL).',
                ],
                'request' => ['method' => 'GET', 'route' => '/api/demo/read/all'],
                'duration' => 48,
                'notes' => "Question de prédiction (12 ?). Envoyez : 11 produits. Onglet SQL : « where deleted_at is null » ajouté par SoftDeletes. "
                    ."Insistez : all() ne prend aucun paramètre, il n'y a ni filtre ni limite.",
            ],
            [
                'id' => 'read-find',
                'type' => 'method',
                'section' => 'read',
                'title' => 'find()',
                'method' => 'find()',
                'definition' => 'Récupère un modèle par sa clé primaire. Renvoie null s\'il n\'existe pas.',
                'useCase' => 'Chercher un produit dont l\'absence est un cas normal à gérer soi-même.',
                'source' => [ReadDemoController::class, 'find'],
                'highlight' => ['find'],
                'returns' => 'Product|null',
                'returnsNote' => 'le modèle, ou null — jamais d\'erreur',
                'tip' => ['kind' => 'mistake', 'text' => 'Utiliser le résultat sans tester null : $product->name provoque « Attempt to read property on null ».'],
                'predict' => [
                    'question' => 'Avec l\'ID 999, obtient-on une erreur ?',
                    'answer' => 'Non : find() renvoie null et la réponse reste 200 OK. C\'est au développeur de gérer l\'absence.',
                ],
                'request' => ['method' => 'GET', 'route' => '/api/demo/read/find/{id}', 'params' => ['id' => '3']],
                'scenarios' => [
                    ['label' => 'Cas normal', 'tone' => 'ok', 'params' => ['id' => '3']],
                    ['label' => 'ID inexistant', 'tone' => 'error', 'params' => ['id' => '999']],
                    ['label' => 'Produit supprimé (#12)', 'tone' => 'warn', 'params' => ['id' => '12']],
                ],
                'duration' => 48,
                'notes' => "Modifiez l'ID directement dans l'onglet Params. ID 3 → le clavier. ID 999 → 200 OK avec product: null. "
                    ."ID 12 → null aussi : le produit est soft-deleted. Transition : « et si on voulait un vrai 404 ? »",
            ],
            [
                'id' => 'read-find-or-fail',
                'type' => 'method',
                'section' => 'read',
                'title' => 'findOrFail()',
                'method' => 'findOrFail()',
                'definition' => 'Comme find(), mais lève une ModelNotFoundException si rien n\'est trouvé. Laravel la transforme en 404.',
                'useCase' => 'Afficher, modifier ou supprimer la ressource demandée dans l\'URL.',
                'source' => [ReadDemoController::class, 'findOrFail'],
                'highlight' => ['findOrFail'],
                'returns' => 'Product',
                'returnsNote' => 'toujours un modèle — sinon exception → HTTP 404',
                'tip' => ['kind' => 'practice', 'text' => 'Dans une API, utilisez findOrFail() pour toute ressource désignée dans l\'URL : pas de if, et le bon code HTTP.'],
                'compare' => [
                    'title' => 'find() vs findOrFail()',
                    'headers' => ['find()', 'findOrFail()'],
                    'rows' => [
                        ['Produit trouvé', 'Product', 'Product'],
                        ['Produit absent', 'null (200 OK)', 'exception → 404'],
                        ['Gestion de l\'absence', 'manuelle (if)', 'automatique'],
                        ['Usage', 'absence = cas normal', 'ressource obligatoire'],
                    ],
                ],
                'request' => ['method' => 'GET', 'route' => '/api/demo/read/find-or-fail/{id}', 'params' => ['id' => '3']],
                'scenarios' => [
                    ['label' => 'Cas normal', 'tone' => 'ok', 'params' => ['id' => '3']],
                    ['label' => 'ID inexistant', 'tone' => 'error', 'params' => ['id' => '999']],
                ],
                'duration' => 48,
                'notes' => "Même requête qu'avant avec 999 : cette fois un vrai 404 Not Found, sans aucun if dans le contrôleur. "
                    ."Montrez le tableau de comparaison find() / findOrFail().",
            ],
            [
                'id' => 'read-where',
                'type' => 'method',
                'section' => 'read',
                'title' => 'where()->get()',
                'method' => 'where()->get()',
                'definition' => 'where() ajoute des conditions à une requête en construction ; get() l\'exécute et renvoie une Collection.',
                'useCase' => 'Filtrer le catalogue : une catégorie, un budget maximum, des produits en stock.',
                'source' => [ReadDemoController::class, 'filter'],
                'highlight' => ['where', 'get'],
                'returns' => 'Collection<int, Product>',
                'returnsNote' => 'éventuellement vide — jamais null',
                'tip' => ['kind' => 'mistake', 'text' => 'Oublier get() : sans lui, on manipule un Builder (la requête), pas les résultats.'],
                'request' => ['method' => 'GET', 'route' => '/api/demo/read/filter', 'query' => ['category' => 'Informatique', 'max_price' => '500', 'min_stock' => '1']],
                'scenarios' => [
                    ['label' => 'Cas normal', 'tone' => 'ok', 'query' => ['category' => 'Informatique', 'max_price' => '500', 'min_stock' => '1']],
                    ['label' => 'Audio ≤ 300 €', 'tone' => 'ok', 'query' => ['category' => 'Audio', 'max_price' => '300', 'min_stock' => '0']],
                    ['label' => 'Aucun résultat', 'tone' => 'warn', 'query' => ['category' => 'Informatique', 'max_price' => '1', 'min_stock' => '0']],
                    ['label' => 'Données invalides', 'tone' => 'error', 'query' => ['category' => 'Informatique', 'max_price' => 'abc', 'min_stock' => '1']],
                ],
                'duration' => 48,
                'notes' => "Modifiez max_price en direct (500 → 1500) : l'ordinateur portable apparaît. "
                    ."Onglet SQL : chaque where() devient une condition AND, les valeurs passent en bindings (protection injection SQL).",
            ],
            [
                'id' => 'read-paginate',
                'type' => 'method',
                'section' => 'read',
                'title' => 'paginate()',
                'method' => 'paginate()',
                'definition' => 'Renvoie une page de résultats et les métadonnées de pagination (total, last_page, liens…).',
                'useCase' => 'Toute liste qui peut grandir : catalogue, commandes, utilisateurs.',
                'source' => [ReadDemoController::class, 'paginate'],
                'highlight' => ['paginate'],
                'returns' => 'LengthAwarePaginator',
                'returnsNote' => 'data + current_page, last_page, per_page, total, links…',
                'tip' => ['kind' => 'practice', 'text' => 'Bornez per_page côté serveur (max:50) : un client ne doit pas pouvoir demander un million de lignes.'],
                'compare' => [
                    'title' => 'all() vs paginate()',
                    'headers' => ['all()', 'paginate()'],
                    'rows' => [
                        ['Lignes chargées', 'toutes', 'per_page'],
                        ['Requêtes SQL', '1', '2 (COUNT + LIMIT)'],
                        ['Mémoire', 'grandit avec la table', 'constante'],
                        ['Métadonnées', 'aucune', 'total, pages, liens'],
                    ],
                ],
                'request' => ['method' => 'GET', 'route' => '/api/demo/read/paginate', 'query' => ['per_page' => '5', 'page' => '1']],
                'scenarios' => [
                    ['label' => 'Page 1', 'tone' => 'ok', 'query' => ['per_page' => '5', 'page' => '1']],
                    ['label' => 'Page 2', 'tone' => 'ok', 'query' => ['per_page' => '5', 'page' => '2']],
                    ['label' => 'Page hors limites', 'tone' => 'warn', 'query' => ['per_page' => '5', 'page' => '99']],
                    ['label' => 'Données invalides', 'tone' => 'error', 'query' => ['per_page' => '500', 'page' => '1']],
                ],
                'duration' => 48,
                'notes' => "Montrez les métadonnées réelles : total 11, last_page 3, next_page_url. "
                    ."Onglet SQL : un COUNT(*) puis un SELECT avec limit/offset. Passez à la page 2.",
            ],
        ];
    }

    private static function update(): array
    {
        return [
            [
                'id' => 'update-save',
                'type' => 'method',
                'section' => 'update',
                'title' => 'save()',
                'method' => 'save()',
                'definition' => 'Sur un modèle existant, save() n\'envoie que les attributs modifiés (« dirty ») dans un UPDATE.',
                'useCase' => 'Modifier quelques champs d\'un produit chargé, avec de la logique entre les étapes.',
                'source' => [UpdateDemoController::class, 'save'],
                'highlight' => ['save'],
                'returns' => 'bool',
                'returnsNote' => 'true — getChanges() liste ce qui a vraiment changé',
                'tip' => ['kind' => 'practice', 'text' => 'Une seule méthode, deux comportements : save() fait un INSERT si le modèle est nouveau, un UPDATE s\'il existe déjà.'],
                'compare' => [
                    'title' => 'save() vs update()',
                    'headers' => ['save()', 'update()'],
                    'rows' => [
                        ['Assignation', 'attribut par attribut', 'tableau (masse)'],
                        ['$fillable', 'non concerné', 'appliqué'],
                        ['Code', 'plusieurs lignes', 'une ligne'],
                        ['Nouveau modèle', 'INSERT', '— (modèle existant)'],
                    ],
                ],
                'predict' => [
                    'question' => 'La requête UPDATE contiendra-t-elle toutes les colonnes du produit ?',
                    'answer' => 'Non : uniquement les colonnes modifiées, plus updated_at. Vérifiez dans l\'onglet SQL.',
                ],
                'request' => ['method' => 'PATCH', 'route' => '/api/demo/update/save/{id}', 'params' => ['id' => '2'], 'body' => ['price' => 429.90, 'stock' => 20]],
                'scenarios' => [
                    ['label' => 'Cas normal', 'tone' => 'ok', 'params' => ['id' => '2'], 'body' => ['price' => 429.90, 'stock' => 20]],
                    ['label' => 'ID inexistant', 'tone' => 'error', 'params' => ['id' => '999'], 'body' => ['price' => 429.90, 'stock' => 20]],
                    ['label' => 'Données invalides', 'tone' => 'error', 'params' => ['id' => '2'], 'body' => ['price' => -1, 'stock' => 'vingt']],
                ],
                'duration' => 48,
                'notes' => "Même méthode save() qu'en Create, mais sur un modèle chargé par findOrFail(). "
                    ."Réponse : original vs changes. Onglet SQL : « update products set price = ?, stock = ?, updated_at = ? » — rien d'autre. "
                    ."Renvoyez une 2e fois : aucune valeur n'a changé, donc save() n'exécute AUCUN UPDATE et changes est vide.",
            ],
            [
                'id' => 'update-update',
                'type' => 'method',
                'section' => 'update',
                'title' => 'update()',
                'method' => 'update()',
                'definition' => 'Remplit le modèle avec un tableau d\'attributs puis l\'enregistre : fill() + save() en un appel.',
                'useCase' => 'Formulaire d\'édition : appliquer directement les champs validés.',
                'source' => [UpdateDemoController::class, 'update'],
                'highlight' => ['update'],
                'returns' => 'bool',
                'returnsNote' => 'true si l\'enregistrement a réussi',
                'tip' => ['kind' => 'mistake', 'text' => 'Passer $request->all() au lieu de $request->validated() : des champs non contrôlés atteindraient la base.'],
                'request' => ['method' => 'PATCH', 'route' => '/api/demo/update/direct/{id}', 'params' => ['id' => '5'], 'body' => ['name' => 'Casque à réduction de bruit (2026)', 'price' => 249.00]],
                'scenarios' => [
                    ['label' => 'Cas normal', 'tone' => 'ok', 'params' => ['id' => '5'], 'body' => ['name' => 'Casque à réduction de bruit (2026)', 'price' => 249.00]],
                    ['label' => 'ID inexistant', 'tone' => 'error', 'params' => ['id' => '999'], 'body' => ['price' => 249.00]],
                    ['label' => 'Données invalides', 'tone' => 'error', 'params' => ['id' => '5'], 'body' => ['sku' => 'INF-001', 'price' => 'gratuit']],
                ],
                'duration' => 48,
                'notes' => "Une seule ligne d'écriture. Envoyez : nom et prix en orange dans l'onglet Base. "
                    ."« Données invalides » : SKU déjà utilisé par un autre produit (règle unique) et prix non numérique → 422.",
            ],
            [
                'id' => 'update-bulk',
                'type' => 'method',
                'section' => 'update',
                'title' => 'where()->update()',
                'method' => 'where()->update()',
                'definition' => 'Met à jour en UNE requête SQL toutes les lignes qui correspondent aux conditions.',
                'useCase' => 'Désactiver tous les accessoires presque épuisés, en une seule opération.',
                'source' => [UpdateDemoController::class, 'bulk'],
                'highlight' => ['where', 'update'],
                'returns' => 'int',
                'returnsNote' => 'le nombre de lignes affectées',
                'tip' => ['kind' => 'mistake', 'text' => 'Aucun modèle n\'est chargé : les événements (updating, updated) ne sont PAS déclenchés. Et sans where(), c\'est TOUTE la table qui change.'],
                'predict' => [
                    'question' => 'Combien de produits seront désactivés ?',
                    'answer' => '2 : la souris (stock 9) et la webcam (stock 5). Le hub USB-C est dans la corbeille : SoftDeletes l\'exclut.',
                ],
                'request' => ['method' => 'PATCH', 'route' => '/api/demo/update/bulk', 'body' => ['category' => 'Accessoires', 'max_stock' => 10, 'is_active' => false]],
                'scenarios' => [
                    ['label' => 'Désactiver', 'tone' => 'ok', 'body' => ['category' => 'Accessoires', 'max_stock' => 10, 'is_active' => false]],
                    ['label' => 'Réactiver', 'tone' => 'ok', 'body' => ['category' => 'Accessoires', 'max_stock' => 10, 'is_active' => true]],
                    ['label' => 'Aucune ligne', 'tone' => 'warn', 'body' => ['category' => 'Jardin', 'max_stock' => 10, 'is_active' => false]],
                    ['label' => 'Données invalides', 'tone' => 'error', 'body' => ['max_stock' => 10, 'is_active' => false]],
                ],
                'duration' => 48,
                'notes' => "Prédiction, puis envoi : affected_rows = 2. Onglet SQL : un seul UPDATE, aucun SELECT préalable. "
                    ."« Données invalides » : la catégorie est obligatoire — on refuse une mise à jour sans filtre.",
            ],
            [
                'id' => 'update-increment',
                'type' => 'method',
                'section' => 'update',
                'title' => 'increment()',
                'method' => 'increment()',
                'definition' => 'Augmente une colonne numérique directement en SQL : SET stock = stock + n.',
                'useCase' => 'Réception d\'un réassort, compteur de vues, points de fidélité.',
                'source' => [UpdateDemoController::class, 'increment'],
                'highlight' => ['increment'],
                'returns' => 'int',
                'returnsNote' => 'le nombre de lignes affectées ; le modèle est mis à jour en mémoire',
                'tip' => ['kind' => 'practice', 'text' => 'Lire, ajouter en PHP puis save() : deux requêtes simultanées s\'écrasent. increment() laisse la base calculer → opération atomique.'],
                'request' => ['method' => 'PATCH', 'route' => '/api/demo/update/increment/{id}', 'params' => ['id' => '6'], 'body' => ['quantity' => 20]],
                'scenarios' => [
                    ['label' => 'Réassort +20', 'tone' => 'ok', 'params' => ['id' => '6'], 'body' => ['quantity' => 20]],
                    ['label' => 'ID inexistant', 'tone' => 'error', 'params' => ['id' => '999'], 'body' => ['quantity' => 20]],
                    ['label' => 'Données invalides', 'tone' => 'error', 'params' => ['id' => '6'], 'body' => ['quantity' => 0]],
                ],
                'duration' => 48,
                'notes' => "L'enceinte (#6) est en rupture : stock 0. Envoyez : stock_before 0, stock_after 20. "
                    ."Onglet SQL : « stock = stock + 20 » — le calcul est fait par la base, pas par PHP.",
            ],
            [
                'id' => 'update-decrement',
                'type' => 'method',
                'section' => 'update',
                'title' => 'decrement()',
                'method' => 'decrement()',
                'definition' => 'Diminue une colonne numérique en SQL. Combiné à un where(), le stock ne peut jamais devenir négatif.',
                'useCase' => 'Valider une commande : retirer la quantité achetée du stock.',
                'source' => [UpdateDemoController::class, 'decrement'],
                'highlight' => ['decrement'],
                'returns' => 'int',
                'returnsNote' => '1 si la ligne a été modifiée, 0 si la condition a échoué',
                'tip' => ['kind' => 'mistake', 'text' => 'Un simple if ($product->stock >= $qty) en PHP ne suffit pas : entre la lecture et l\'écriture, un autre achat peut passer. La condition doit être DANS l\'UPDATE.'],
                'predict' => [
                    'question' => 'Il reste 5 webcams. Que se passe-t-il si on en commande 50 ?',
                    'answer' => '« where stock >= 50 » ne trouve aucune ligne : 0 ligne modifiée → 409 Conflict. Le stock reste intact.',
                ],
                'request' => ['method' => 'PATCH', 'route' => '/api/demo/update/decrement/{id}', 'params' => ['id' => '7'], 'body' => ['quantity' => 2]],
                'scenarios' => [
                    ['label' => 'Cas normal', 'tone' => 'ok', 'params' => ['id' => '7'], 'body' => ['quantity' => 2]],
                    ['label' => 'Stock insuffisant', 'tone' => 'error', 'params' => ['id' => '7'], 'body' => ['quantity' => 50]],
                    ['label' => 'ID inexistant', 'tone' => 'error', 'params' => ['id' => '999'], 'body' => ['quantity' => 2]],
                ],
                'duration' => 48,
                'notes' => "Cas normal : 5 → 3. Puis « Stock insuffisant » : 409 Conflict. Onglet SQL : l'UPDATE contient « stock >= ? » "
                    ."et n'a touché aucune ligne. Onglet Base : aucune modification.",
            ],
        ];
    }

    private static function delete(): array
    {
        return [
            [
                'id' => 'delete-delete',
                'type' => 'method',
                'section' => 'delete',
                'title' => 'delete()',
                'method' => 'delete()',
                'definition' => 'Supprime le modèle chargé. Avec SoftDeletes, c\'est une suppression logique : deleted_at est rempli.',
                'useCase' => 'Retirer un produit du catalogue tout en gardant son historique.',
                'source' => [DeleteDemoController::class, 'delete'],
                'highlight' => ['delete'],
                'returns' => 'bool|null',
                'returnsNote' => 'true si la suppression a réussi',
                'tip' => ['kind' => 'practice', 'text' => 'Utilisez SoftDeletes pour les données métier (produits, commandes, clients) : restauration possible et historique conservé.'],
                'predict' => [
                    'question' => 'La ligne va-t-elle disparaître de la table ?',
                    'answer' => 'Non : le SQL est un UPDATE … SET deleted_at. La ligne reste en base, elle est seulement masquée.',
                ],
                'request' => ['method' => 'DELETE', 'route' => '/api/demo/delete/model/{id}', 'params' => ['id' => '4']],
                'scenarios' => [
                    ['label' => 'Cas normal', 'tone' => 'ok', 'params' => ['id' => '4']],
                    ['label' => 'ID inexistant', 'tone' => 'error', 'params' => ['id' => '999']],
                    ['label' => 'Déjà supprimé (#12)', 'tone' => 'warn', 'params' => ['id' => '12']],
                ],
                'duration' => 48,
                'notes' => "Posez la question. Envoyez : onglet SQL → c'est un UPDATE, pas un DELETE. "
                    ."Onglet Base → ligne rouge, deleted_at rempli. Renvoyez : 404, findOrFail() ne voit plus le produit.",
            ],
            [
                'id' => 'delete-destroy',
                'type' => 'method',
                'section' => 'delete',
                'title' => 'destroy()',
                'method' => 'destroy()',
                'definition' => 'Supprime un ou plusieurs enregistrements à partir de leurs clés primaires.',
                'useCase' => 'Suppression multiple : cases à cocher dans une liste d\'administration.',
                'source' => [DeleteDemoController::class, 'destroy'],
                'highlight' => ['destroy'],
                'returns' => 'int',
                'returnsNote' => 'le nombre d\'enregistrements supprimés',
                'tip' => ['kind' => 'practice', 'text' => 'destroy() charge chaque modèle puis appelle delete() : SoftDeletes et événements sont respectés (1 SELECT + 1 UPDATE par produit).'],
                'compare' => [
                    'title' => 'delete() vs destroy()',
                    'headers' => ['delete()', 'destroy()'],
                    'rows' => [
                        ['On fournit', 'un modèle chargé', 'des ID'],
                        ['Appel', '$product->delete()', 'Product::destroy($ids)'],
                        ['Retour', 'bool', 'int (nombre)'],
                        ['SoftDeletes', 'respecté', 'respecté'],
                    ],
                ],
                'request' => ['method' => 'DELETE', 'route' => '/api/demo/delete/destroy', 'body' => ['ids' => [2, 3]]],
                'scenarios' => [
                    ['label' => 'Plusieurs ID', 'tone' => 'ok', 'body' => ['ids' => [2, 3]]],
                    ['label' => 'Un seul ID', 'tone' => 'ok', 'body' => ['ids' => [5]]],
                    ['label' => 'ID inexistant', 'tone' => 'warn', 'body' => ['ids' => [999]]],
                    ['label' => 'Données invalides', 'tone' => 'error', 'body' => ['ids' => []]],
                ],
                'duration' => 48,
                'notes' => "Ici on ne charge rien soi-même : on donne des ID. Envoyez [2, 3] : deleted_count 2, deux lignes rouges. "
                    ."ID inexistant : pas d'erreur, deleted_count 0.",
            ],
            [
                'id' => 'delete-soft',
                'type' => 'method',
                'section' => 'delete',
                'title' => 'SoftDeletes',
                'method' => 'SoftDeletes',
                'definition' => 'Le trait SoftDeletes ajoute « deleted_at is null » à chaque requête. withTrashed() et onlyTrashed() ouvrent la corbeille.',
                'useCase' => 'Afficher une corbeille, auditer, restaurer une suppression accidentelle.',
                'source' => [DeleteDemoController::class, 'trashed'],
                'highlight' => ['count', 'withTrashed', 'onlyTrashed'],
                'returns' => 'Builder',
                'returnsNote' => 'withTrashed() / onlyTrashed() modifient la requête',
                'tip' => ['kind' => 'mistake', 'text' => 'La contrainte UNIQUE (sku) s\'applique aussi aux lignes soft-deleted : impossible de recréer un produit avec le SKU d\'un produit dans la corbeille.'],
                'predict' => [
                    'question' => 'Le produit #12, supprimé, existe-t-il encore dans la base ?',
                    'answer' => 'Oui : default_count l\'ignore mais with_trashed_count le compte. Comparez les 3 requêtes SQL.',
                ],
                'request' => ['method' => 'GET', 'route' => '/api/demo/delete/trashed'],
                'duration' => 48,
                'notes' => "Le cœur de la section : comparez les 3 requêtes de l'onglet SQL. "
                    ."Sans rien : « deleted_at is null ». withTrashed() : aucune condition. onlyTrashed() : « deleted_at is not null ».",
            ],
            [
                'id' => 'delete-restore',
                'type' => 'method',
                'section' => 'delete',
                'title' => 'restore()',
                'method' => 'restore()',
                'definition' => 'Annule une suppression logique : deleted_at repasse à NULL et le produit réapparaît.',
                'useCase' => 'Bouton « Annuler » après une suppression, ou restauration depuis la corbeille.',
                'source' => [DeleteDemoController::class, 'restore'],
                'highlight' => ['restore', 'onlyTrashed'],
                'returns' => 'bool',
                'returnsNote' => 'true si la restauration a réussi',
                'tip' => ['kind' => 'mistake', 'text' => 'Chercher avec Product::find() : il ignore la corbeille et renvoie null. Utilisez onlyTrashed() ou withTrashed().'],
                'request' => ['method' => 'PATCH', 'route' => '/api/demo/delete/restore/{id}', 'params' => ['id' => '12']],
                'scenarios' => [
                    ['label' => 'Produit dans la corbeille', 'tone' => 'ok', 'params' => ['id' => '12']],
                    ['label' => 'Produit actif', 'tone' => 'warn', 'params' => ['id' => '1']],
                    ['label' => 'ID inexistant', 'tone' => 'error', 'params' => ['id' => '999']],
                ],
                'setup' => [
                    ['label' => 'Mettre #4 à la corbeille', 'method' => 'DELETE', 'url' => '/api/demo/delete/model/4', 'fill' => ['id' => '4']],
                ],
                'duration' => 48,
                'notes' => "Le hub #12 est dans la corbeille depuis le début. Envoyez : onglet Base → badge « restauré », deleted_at → NULL. "
                    ."Renvoyez : 404, il n'est plus dans la corbeille. Le bouton de préparation met un autre produit à la corbeille.",
            ],
            [
                'id' => 'delete-force',
                'type' => 'method',
                'section' => 'delete',
                'title' => 'forceDelete()',
                'method' => 'forceDelete()',
                'definition' => 'Supprime DÉFINITIVEMENT la ligne (DELETE SQL), même si le modèle utilise SoftDeletes.',
                'useCase' => 'Droit à l\'effacement (RGPD), nettoyage de données de test.',
                'source' => [DeleteDemoController::class, 'forceDelete'],
                'highlight' => ['forceDelete'],
                'returns' => 'bool|null',
                'returnsNote' => 'true — la ligne n\'existe plus, restore() est impossible',
                'tip' => ['kind' => 'practice', 'text' => 'Réservez forceDelete() aux cas justifiés et exigez une confirmation explicite : aucune annulation possible.'],
                'compare' => [
                    'title' => 'delete() vs forceDelete()',
                    'headers' => ['delete()', 'forceDelete()'],
                    'rows' => [
                        ['SQL', 'UPDATE … deleted_at', 'DELETE FROM'],
                        ['Ligne en base', 'conservée', 'effacée'],
                        ['Restauration', 'restore()', 'impossible'],
                        ['Usage', 'par défaut', 'exceptionnel'],
                    ],
                ],
                'request' => ['method' => 'DELETE', 'route' => '/api/demo/delete/force/{id}', 'params' => ['id' => '12'], 'body' => ['confirm' => true]],
                'scenarios' => [
                    ['label' => 'Avec confirmation', 'tone' => 'ok', 'body' => ['confirm' => true]],
                    ['label' => 'Sans confirmation', 'tone' => 'error', 'body' => ['confirm' => false]],
                    ['label' => 'ID inexistant', 'tone' => 'error', 'params' => ['id' => '999'], 'body' => ['confirm' => true]],
                ],
                'setup' => [
                    ['label' => 'Créer un produit jetable', 'method' => 'POST', 'url' => '/api/demo/create/create', 'body' => ['name' => 'Produit jetable', 'sku' => 'TMP-{rand}', 'category' => 'Démo', 'price' => 1, 'stock' => 1], 'fill' => ['id' => '@id']],
                ],
                'confirm' => 'Supprimer DÉFINITIVEMENT le produit #{id} ? Cette action est irréversible.',
                'duration' => 48,
                'notes' => "Créez d'abord un produit jetable (bouton de préparation), son ID est rempli automatiquement. "
                    ."Confirmez, envoyez : 204 No Content, onglet SQL → DELETE FROM, onglet Base → ligne effacée. Comparez avec delete().",
            ],
        ];
    }

    private static function conclusion(): array
    {
        return [
            [
                'id' => 'conclusion',
                'type' => 'conclusion',
                'section' => 'conclusion',
                'title' => 'Choisir la bonne méthode',
                'duration' => 45,
                'notes' => "Résumez avec le tableau « Je veux… → j'utilise… ». Lisez les 7 bonnes pratiques à voix haute, "
                    ."en insistant sur : valider avant d'écrire, findOrFail() pour les 404, paginate() pour les listes.",
            ],
            [
                'id' => 'catalogue',
                'type' => 'catalogue',
                'section' => 'conclusion',
                'title' => 'Aller plus loin avec Eloquent',
                'duration' => 15,
                'notes' => "15 secondes maximum : « Voici un catalogue interactif d'autres méthodes Eloquent : recherche, filtres, exemples. "
                    ."Explorez-le après la présentation. » Lisez la phrase de clôture.",
            ],
        ];
    }
}
