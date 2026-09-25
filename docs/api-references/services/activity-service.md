# ActivityService - Référence Technique

## Description

Service applicatif qui orchestre l'enregistrement, la consultation, le comptage et la suppression des activités rattachées à un modèle Eloquent propriétaire.

## Hiérarchie / Implémentations

```
AndyDefer\LaravelActivity\Contracts\Services\ActivityServiceInterface
    └── ActivityService (final)
```

## Rôle principal

`ActivityService` est le **point d'entrée recommandé** pour les consommateurs du package. Il masque le contrat du repository et centralise la traduction des entrées brutes (tableaux, chaînes) en objets typés (`ActivityRecord`, `ActivityFilterRecord`, `StrictDataObject`).

Il joue trois rôles :

1. **Façade applicative** : expose une API haut niveau (`log`, `getFor`, `countFor`, `clearFor`).
2. **Traducteur d'entrée** : convertit les payloads `array` en `StrictDataObject`.
3. **Constructeur de filtres** : génère les `ActivityFilterRecord` à partir d'un modèle propriétaire.

## Installation

Aucune installation spécifique. Le service est enregistré dans le conteneur par `ActivityServiceProvider` :

```bash
composer require andydefer/laravel-activity
```

## API / Méthodes publiques

### `__construct(ActivityRepositoryInterface $activityRepository)`

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$activityRepository` | `ActivityRepositoryInterface` | Couche de persistance des activités. |

**Retourne :** rien (constructeur).

**Exemple :**
```php
$service = new ActivityService(
    activityRepository: app(ActivityRepositoryInterface::class),
);
```

---

### `log(Model $owner, string $type, ?string $description = null, ?array $data = null, ?array $metadata = null): Model`

Enregistre une nouvelle activité rattachée au propriétaire donné.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$owner` | `Model` | Modèle Eloquent propriétaire de l'activité. |
| `$type` | `string` | Type d'activité (ex : `'logged_in'`). |
| `$description` | `?string` | Description lisible, optionnelle. |
| `$data` | `?array` | Payload arbitraire converti en `StrictDataObject`, optionnel. |
| `$metadata` | `?array` | Métadonnées contextuelles converties en `StrictDataObject`, optionnelles. |

**Retourne :** `Model` — L'instance `Activity` persistée.

**Exceptions :** dépend du repository (`QueryException` Eloquent si l'insertion échoue).

**Exemple :**
```php
$activity = $service->log(
    owner: $user,
    type: 'logged_in',
    description: 'John Doe logged in',
    data: ['ip' => '127.0.0.1'],
    metadata: ['source' => 'web'],
);
```

---

### `getFor(Model $owner, ?int $limit = null): Collection`

Retourne les activités du propriétaire, de la plus récente à la plus ancienne.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$owner` | `Model` | Modèle Eloquent propriétaire. |
| `$limit` | `?int` | Nombre maximal d'activités retournées, ou `null` pour illimité. |

**Retourne :** `Collection` — Collection d'activités.

**Exemple :**
```php
$activities = $service->getFor($user, limit: 10);
```

---

### `getForByType(Model $owner, string $type, ?int $limit = null): Collection`

Retourne les activités du propriétaire filtrées par type.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$owner` | `Model` | Modèle Eloquent propriétaire. |
| `$type` | `string` | Type d'activité à filtrer. |
| `$limit` | `?int` | Nombre maximal d'activités retournées, ou `null` pour illimité. |

**Retourne :** `Collection` — Collection d'activités filtrées.

**Exemple :**
```php
$logins = $service->getForByType($user, 'logged_in', limit: 5);
```

---

### `getLatestFor(Model $owner): ?Model`

Retourne l'activité la plus récente du propriétaire.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$owner` | `Model` | Modèle Eloquent propriétaire. |

**Retourne :** `?Model` — La dernière activité, ou `null` si le propriétaire n'en a aucune.

**Exemple :**
```php
$latest = $service->getLatestFor($user);
```

---

### `countFor(Model $owner): int`

Compte le nombre total d'activités du propriétaire.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$owner` | `Model` | Modèle Eloquent propriétaire. |

**Retourne :** `int` — Nombre total d'activités.

**Exemple :**
```php
$total = $service->countFor($user);
```

---

### `countForByType(Model $owner, string $type): int`

Compte les activités du propriétaire filtrées par type.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$owner` | `Model` | Modèle Eloquent propriétaire. |
| `$type` | `string` | Type d'activité à compter. |

**Retourne :** `int` — Nombre d'activités correspondantes.

**Exemple :**
```php
$loginCount = $service->countForByType($user, 'logged_in');
```

---

### `hasActivityOfType(Model $owner, string $type): bool`

Indique si le propriétaire possède au moins une activité du type donné.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$owner` | `Model` | Modèle Eloquent propriétaire. |
| `$type` | `string` | Type d'activité recherché. |

**Retourne :** `bool` — `true` si au moins une activité correspond, sinon `false`.

**Exemple :**
```php
if ($service->hasActivityOfType($user, 'logged_in')) {
    // L'utilisateur s'est déjà connecté au moins une fois.
}
```

---

### `clearFor(Model $owner): int`

Supprime toutes les activités du propriétaire.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$owner` | `Model` | Modèle Eloquent propriétaire. |

**Retourne :** `int` — Nombre d'activités supprimées.

**Exemple :**
```php
$deleted = $service->clearFor($user);
```

## Cas d'utilisation

### Cas 1 : Journaliser une connexion utilisateur

À chaque connexion, on enregistre une activité traçable pour audit.

```php
<?php

declare(strict_types=1);

use AndyDefer\LaravelActivity\Services\ActivityService;

final class LoginController
{
    public function __construct(
        private readonly ActivityService $activities,
    ) {}

    public function login(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $this->activities->log(
            owner: $user,
            type: 'logged_in',
            description: "{$user->name} logged in",
            data: ['ip' => $request->ip()],
            metadata: ['user_agent' => $request->userAgent()],
        );

        return redirect()->intended('/dashboard');
    }
}
```

### Cas 2 : Afficher le fil d'activité récent d'un utilisateur

On affiche les 20 dernières activités sur la page profil.

```php
<?php

declare(strict_types=1);

use AndyDefer\LaravelActivity\Services\ActivityService;

final class ProfileController
{
    public function __construct(
        private readonly ActivityService $activities,
    ) {}

    public function show(User $user): View
    {
        return view('profile.show', [
            'user' => $user,
            'activities' => $this->activities->getFor($user, limit: 20),
            'loginCount' => $this->activities->countForByType($user, 'logged_in'),
        ]);
    }
}
```

### Cas 3 : Vérifier une condition avant action

On empêche un utilisateur de réclamer un bonus s'il ne s'est jamais connecté.

```php
<?php

declare(strict_types=1);

use AndyDefer\LaravelActivity\Services\ActivityService;
use RuntimeException;

final class BonusClaimService
{
    public function __construct(
        private readonly ActivityService $activities,
    ) {}

    public function claim(User $user): void
    {
        if (! $this->activities->hasActivityOfType($user, 'logged_in')) {
            throw new RuntimeException('User must log in before claiming a bonus.');
        }

        // ...
    }
}
```

### Cas 4 : Purger les activités d'un utilisateur (RGPD)

Sur demande de suppression de compte, on efface toutes les activités.

```php
<?php

declare(strict_types=1);

use AndyDefer\LaravelActivity\Services\ActivityService;

final class GdprService
{
    public function __construct(
        private readonly ActivityService $activities,
    ) {}

    public function eraseActivityHistory(User $user): int
    {
        return $this->activities->clearFor($user);
    }
}
```

## Flux d'exécution

```
Appel utilisateur
    │
    ├── log() ────────────► ActivityRecord::from([...])
    │                       │
    │                       └──► repository->create(record)  ──► Model persisté
    │
    ├── getFor() ─────────► repository->getFor(owner, limit) ──► Collection
    │
    ├── countFor() ───────► repository->countFor(owner)     ──► int
    │
    ├── hasActivityOfType()► buildOwnerFilter(owner, type)
    │                       │
    │                       └──► repository->exists(filter)  ──► bool
    │
    └── clearFor() ───────► buildOwnerFilter(owner)
                            │
                            └──► repository->deleteBulk(filter) ──► int
```

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| Insertion échouée en base | `Illuminate\Database\QueryException` | Message natif PDO/SQL. |
| Propriétaire sans clé (`getKey()` null) | Aucune levée | L'`owner_id` vaut `''` (chaîne vide) → insertion possible avec un ID invalide. |
| Payload `$data` non sérialisable | `InvalidArgumentException` (via `StrictDataObject`) | Dépend de l'implémentation de `StrictDataObject`. |

`ActivityService` **ne lève aucune exception propre**. Toutes les erreurs proviennent du repository ou des value objects sous-jacents.

## Intégration

`ActivityService` s'intègre dans l'écosystème du package comme suit :

```
ActivityServiceProvider
    │
    ├── enregistre ActivityRepositoryInterface → ActivityRepository
    │
    └── enregistre ActivityServiceInterface   → ActivityService
                                                    │
                                                    └── dépend de ActivityRepositoryInterface
                                                            │
                                                            ├── utilise Activity (Model Eloquent)
                                                            ├── construit ActivityRecord
                                                            └── construit ActivityFilterRecord
```

**Points d'intégration côté consommateur :**

- Injection par interface : `ActivityServiceInterface` (recommandé).
- Injection par classe : `ActivityService` (possible, singleton).
- Utilisation via façade (si ajoutée ultérieurement) : `Activity::log(...)`.

## Performance

- **`log()`** : 1 insertion SQL + 2 constructions d'objets (`ActivityRecord`, `StrictDataObject` éventuels). Complexité O(1).
- **`getFor()` / `getForByType()`** : 1 requête SELECT avec `ORDER BY created_at DESC` et `LIMIT` optionnel. Index recommandé sur `(owner_type, owner_id, created_at)`.
- **`getLatestFor()`** : équivalent à `getFor(owner, limit: 1)` → 1 SELECT LIMIT 1.
- **`countFor()` / `countForByType()`** : 1 SELECT COUNT. Efficace si index sur `(owner_type, owner_id)`.
- **`hasActivityOfType()`** : utilise `exists()` → 1 SELECT avec `LIMIT 1` implicite, plus rapide que `count() > 0`.
- **`clearFor()`** : utilise `deleteBulk()` → 1 DELETE massif. Attention : sur `SoftDeletes`, vérifier si le bulk delete déclenche un soft delete ou un hard delete.

Aucun cache interne. Aucune transaction explicite (chaque appel est atomique).

## Compatibilité

| Version | Support |
|---------|---------|
| PHP 8.1 | ✅ Complet |
| PHP 8.2 | ✅ Complet |
| PHP 8.3 | ✅ Complet |
| Laravel 10 | ✅ Complet |
| Laravel 11 | ✅ Complet |

**Prérequis :**
- `ext-json` (pour la sérialisation des `StrictDataObject`).
- `illuminate/database` et `illuminate/support`.

## Exemple complet

```php
<?php

declare(strict_types=1);

use AndyDefer\LaravelActivity\Contracts\Services\ActivityServiceInterface;
use AndyDefer\LaravelActivity\Services\ActivityService;

// Résolution depuis le conteneur Laravel
$activities = app(ActivityServiceInterface::class);

// Enregistrement d'une activité
$activity = $activities->log(
    owner: $user,
    type: 'password_changed',
    description: 'Password updated by user',
    data: ['ip' => '10.0.0.1'],
    metadata: ['channel' => 'settings'],
);

// Récupération des 10 dernières activités
$recent = $activities->getFor($user, limit: 10);

// Récupération des activités d'un type précis
$logins = $activities->getForByType($user, 'logged_in', limit: 5);

// Dernière activité
$latest = $activities->getLatestFor($user);

// Comptages
$total      = $activities->countFor($user);
$loginCount = $activities->countForByType($user, 'logged_in');

// Vérification d'existence
$hasLoggedIn = $activities->hasActivityOfType($user, 'logged_in');

// Nettoyage complet
$deletedCount = $activities->clearFor($user);
```

## Voir aussi

- `ActivityServiceInterface` - Contrat public implémenté par ce service.
- `ActivityRepositoryInterface` - Contrat du repository sous-jacent.
- `ActivityRepository` - Implémentation Eloquent du repository.
- `Activity` - Modèle Eloquent représentant une activité.
- `ActivityRecord` - Read model DTO utilisé en entrée du repository.
- `ActivityFilterRecord` - Query object utilisé pour filtrer les activités.
- `StrictDataObject` - Enveloppe typée pour les payloads `data` et `metadata`.
- `ActivityServiceProvider` - Enregistrement du service dans le conteneur Laravel.