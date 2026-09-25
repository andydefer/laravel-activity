# Laravel Activity

> Système de journalisation d'activité polymorphique pour applications Laravel

Un package Laravel pour enregistrer, consulter et gérer les activités d'un modèle : inscription, connexion, création, paiement, etc. Chaque activité est typée par une chaîne libre et peut transporter des données structurées.

---

## 📋 Table des matières

- [Fonctionnalités](#fonctionnalités)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
  - [Enregistrer une activité](#enregistrer-une-activité)
  - [Récupérer les activités](#récupérer-les-activités)
  - [Récupérer par type](#récupérer-par-type)
  - [Récupérer la dernière activité](#récupérer-la-dernière-activité)
  - [Compter les activités](#compter-les-activités)
  - [Vérifier l'existence d'un type](#vérifier-lexistence-dun-type)
  - [Nettoyer les activités](#nettoyer-les-activités)
- [Types d'activité](#types-dactivité)
- [Référence de l'API](#référence-de-lapi)
- [Value Objects](#value-objects)
- [Structure de la base de données](#structure-de-la-base-de-données)
- [Tests](#tests)
- [Licence](#licence)

---

## ✨ Fonctionnalités

- ✅ **Propriétaire polymorphique** - Attachez une activité à n'importe quel modèle Eloquent
- ✅ **Type dynamique** - Le type d'activité est une chaîne libre, pas une enum figée
- ✅ **Données structurées** - Payload et metadata en JSON typés via `StrictDataObject`
- ✅ **Identifiant UUID** - Chaque activité possède un UUID v4
- ✅ **Soft delete** - Suppression non destructive avec restauration
- ✅ **Filtrage** - Par propriétaire, type et plage de dates
- ✅ **Pagination et limite** - Récupération contrôlée des activités
- ✅ **Table configurable** - Nom de table personnalisable via la configuration
- ✅ **Pattern Repository** - Séparation propre de l'accès aux données
- ✅ **DTOs typés** - `ActivityRecord` et `ActivityData` pour les échanges

---

## 🚀 Prérequis

- PHP 8.2 ou supérieur
- Laravel 12.0, 13.0, 14.0 ou 15.0

---

## 📦 Installation

Installez le package via Composer :

```bash
composer require andydefer/laravel-activity
```

### Publier la configuration et les migrations

```bash
php artisan vendor:publish --tag=activity-config
php artisan vendor:publish --tag=activity-migrations
```

### Exécuter les migrations

```bash
php artisan migrate
```

---

## ⚙️ Configuration

Le fichier `config/activity.php` permet de contrôler le comportement du package :

```php
<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Nom de la table
    |--------------------------------------------------------------------------
    |
    | Nom de la table utilisée pour stocker les activités.
    |
    */
    'table' => 'activities',

    /*
    |--------------------------------------------------------------------------
    | Alias morph
    |--------------------------------------------------------------------------
    |
    | Lorsque vrai, les types morph du propriétaire sont stockés en utilisant
    | l'alias morph (config('database.morph_map')) plutôt que le FQCN.
    |
    */
    'use_morph_alias' => false,

    /*
    |--------------------------------------------------------------------------
    | Rétention
    |--------------------------------------------------------------------------
    |
    | Nombre de jours pendant lesquels les activités sont conservées avant
    | d'être éligibles à la purge. Null désactive la purge.
    |
    */
    'retention_days' => null,
];
```

Le package est automatiquement découvert par Laravel. Aucun enregistrement manuel n'est nécessaire.

---

## 📖 Utilisation

Le service d'activité est injectable via `ActivityServiceInterface`.

### Enregistrer une activité

```php
use AndyDefer\LaravelActivity\Contracts\Services\ActivityServiceInterface;

final class RegistrationController extends Controller
{
    public function __construct(
        private readonly ActivityServiceInterface $activityService,
    ) {}

    public function register(Request $request)
    {
        $user = User::create($request->validated());

        $this->activityService->log(
            owner: $user,
            type: 'registered',
            description: 'User registered from the web form',
            data: ['ip' => $request->ip()],
            metadata: ['source' => 'web'],
        );

        return response()->json(['message' => 'Compte créé']);
    }
}
```

### Récupérer les activités

```php
// Toutes les activités du propriétaire
$activities = $this->activityService->getFor($user);

// Avec une limite
$latest = $this->activityService->getFor($user, limit: 10);
```

### Récupérer par type

```php
// Toutes les connexions de l'utilisateur
$logins = $this->activityService->getForByType($user, 'logged_in');

// Les 5 dernières connexions
$recentLogins = $this->activityService->getForByType($user, 'logged_in', limit: 5);
```

### Récupérer la dernière activité

```php
$lastActivity = $this->activityService->getLatestFor($user);

if ($lastActivity !== null) {
    echo $lastActivity->activity_type;
    echo $lastActivity->created_at;
}
```

### Compter les activités

```php
// Nombre total d'activités
$total = $this->activityService->countFor($user);

// Nombre d'activités d'un type donné
$logins = $this->activityService->countForByType($user, 'logged_in');
```

### Vérifier l'existence d'un type

```php
if ($this->activityService->hasActivityOfType($user, 'payment_completed')) {
    // L'utilisateur a au moins un paiement complété
}
```

### Nettoyer les activités

```php
// Supprime toutes les activités du propriétaire
$deleted = $this->activityService->clearFor($user);
```

---

## 🏷️ Types d'activité

Le type d'activité est une **chaîne libre**. Aucune enum n'est imposée par le package — vous pouvez utiliser les valeurs que vous voulez.

Les valeurs suivantes sont fréquemment utilisées comme convention :

| Catégorie | Valeurs suggérées |
|-----------|-------------------|
| Cycle de vie | `created`, `updated`, `deleted`, `restored` |
| Authentification | `logged_in`, `logged_out`, `registered`, `password_changed`, `email_verified` |
| Interaction | `viewed`, `downloaded`, `shared`, `commented`, `liked`, `rated` |
| Paiement | `payment_initiated`, `payment_completed`, `payment_failed`, `payment_refunded` |
| Divers | `other` |

Une enum optionnelle est fournie à titre indicatif (`AndyDefer\LaravelActivity\Enums\ActivityType`) mais **son usage n'est pas requis**. Le stockage se fait toujours sous forme de chaîne, et toute valeur non prévue reste acceptée.

---

## 📚 Référence de l'API

### ActivityServiceInterface

| Méthode | Description | Retourne |
|---------|-------------|----------|
| `log(Model $owner, string $type, ?string $description, ?array $data, ?array $metadata)` | Enregistre une activité | `Model` |
| `getFor(Model $owner, ?int $limit)` | Récupère les activités d'un propriétaire | `Collection` |
| `getForByType(Model $owner, string $type, ?int $limit)` | Récupère les activités filtrées par type | `Collection` |
| `getLatestFor(Model $owner)` | Récupère la dernière activité | `?Model` |
| `countFor(Model $owner)` | Compte les activités d'un propriétaire | `int` |
| `countForByType(Model $owner, string $type)` | Compte les activités d'un type | `int` |
| `hasActivityOfType(Model $owner, string $type)` | Vérifie l'existence d'un type | `bool` |
| `clearFor(Model $owner)` | Supprime toutes les activités d'un propriétaire | `int` |

### ActivityRepositoryInterface

| Méthode | Description | Retourne |
|---------|-------------|----------|
| `getFor(Model $owner, ?int $limit)` | Récupère les activités d'un propriétaire | `Collection` |
| `getForByType(Model $owner, string $type, ?int $limit)` | Récupère les activités filtrées par type | `Collection` |
| `getLatestFor(Model $owner)` | Récupère la dernière activité | `?Activity` |
| `countFor(Model $owner)` | Compte les activités | `int` |
| `countForByType(Model $owner, string $type)` | Compte les activités par type | `int` |

---

## 🎯 Value Objects

| Value Object | Description | Exemple |
|--------------|-------------|---------|
| `StrictDataObject` | Payload et metadata | `StrictDataObject::from(['key' => 'value'])` |
| `DateTimeZuluVO` | Date/heure UTC | `DateTimeZuluVO::from('2024-01-15T10:00:00Z')` |
| `UuidVO` | UUID v4 | `UuidVO::from('9b724dbf-...')` |

### Accesseurs sur le modèle Activity

```php
$activity = Activity::find($id);

// Propriétés typées
$activity->id;              // string (UUID)
$activity->owner_type;      // string
$activity->owner_id;        // string
$activity->activity_type;   // string
$activity->description;     // ?string

// Value Objects via accesseurs
$activity->data;            // ?StrictDataObject
$activity->metadata;        // ?StrictDataObject

// Relation polymorphique
$activity->owner;           // ?Model

// Timestamps
$activity->created_at;
$activity->updated_at;
$activity->deleted_at;
```

---

## 📝 Structure de la base de données

```sql
CREATE TABLE activities (
    id CHAR(36) PRIMARY KEY,               -- UUID v4
    owner_type VARCHAR(191) NOT NULL,      -- Type morph du propriétaire
    owner_id VARCHAR(191) NOT NULL,        -- Identifiant du propriétaire
    activity_type VARCHAR(191) NOT NULL,   -- Type d'activité (chaîne libre)
    description VARCHAR(255) NULL,         -- Description humaine
    data JSON NULL,                        -- Payload structuré
    metadata JSON NULL,                    -- Métadonnées additionnelles
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    INDEX activities_owner_index (owner_type, owner_id),
    INDEX activities_activity_type_index (activity_type),
    INDEX activities_created_at_index (created_at)
);
```

---

## 🔍 Exemple complet

```php
use AndyDefer\LaravelActivity\Contracts\Services\ActivityServiceInterface;
use Illuminate\Http\Request;

final class UserActivityController extends Controller
{
    public function __construct(
        private readonly ActivityServiceInterface $activityService,
    ) {}

    public function show(Request $request, User $user)
    {
        $activities = $this->activityService->getFor($user, limit: 20);
        $logins = $this->activityService->countForByType($user, 'logged_in');
        $lastActivity = $this->activityService->getLatestFor($user);

        return response()->json([
            'total' => $this->activityService->countFor($user),
            'logins' => $logins,
            'last_activity' => $lastActivity?->activity_type,
            'activities' => $activities->map(fn ($activity) => [
                'id' => $activity->id,
                'type' => $activity->activity_type,
                'description' => $activity->description,
                'data' => $activity->data?->toArray(),
                'created_at' => $activity->created_at,
            ]),
        ]);
    }

    public function destroy(User $user)
    {
        $deleted = $this->activityService->clearFor($user);

        return response()->json([
            'deleted' => $deleted,
        ]);
    }
}
```

---

## 🧪 Tests

### Exécuter tous les tests

```bash
composer test
```

### Exécuter uniquement les tests unitaires

```bash
composer test-unit
```

### Exécuter uniquement les tests d'intégration

```bash
composer test-integration
```

Le package utilise `orchestra/testbench` avec une base de données SQLite en mémoire pour les tests d'intégration.

---

## 📦 Dépendances

- [`andydefer/laravel-repository`](https://github.com/andydefer/laravel-repository) - Implémentation du pattern Repository
- [`andydefer/php-vo`](https://github.com/andydefer/php-vo) - Value Objects
- [`andydefer/domain-structures`](https://github.com/andydefer/domain-structures) - Structures de domaine

---

## 👨‍💻 Auteur

**Andy Kani**
- GitHub: [@andydefer](https://github.com/andydefer)
- Email: andykanidimbu@gmail.com

---

## 📄 Licence

Ce package est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus d'informations.