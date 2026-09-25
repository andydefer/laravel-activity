<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Contracts\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Service contract for managing polymorphic activities.
 */
interface ActivityServiceInterface
{
    /**
     * Record a new activity on the given owner.
     *
     * @param  Model  $owner  The model the activity is attached to.
     * @param  string  $type  The activity type (backed enum value or arbitrary string).
     * @param  string|null  $description  Optional human-readable description.
     * @param  array<string, mixed>|null  $data  Optional payload data.
     * @param  array<string, mixed>|null  $metadata  Optional metadata.
     * @return Model The created activity model.
     */
    public function log(
        Model $owner,
        string $type,
        ?string $description = null,
        ?array $data = null,
        ?array $metadata = null,
    ): Model;

    /**
     * Return all activities attached to the given owner, with an optional limit.
     *
     * @return Collection<int, Model>
     */
    public function getFor(Model $owner, ?int $limit = null): Collection;

    /**
     * Return activities attached to the given owner filtered by type, with an optional limit.
     *
     * @return Collection<int, Model>
     */
    public function getForByType(Model $owner, string $type, ?int $limit = null): Collection;

    /**
     * Return the latest activity attached to the given owner, or null.
     */
    public function getLatestFor(Model $owner): ?Model;

    /**
     * Count the activities attached to the given owner.
     */
    public function countFor(Model $owner): int;

    /**
     * Count the activities attached to the given owner filtered by type.
     */
    public function countForByType(Model $owner, string $type): int;

    /**
     * Check whether the given owner has at least one activity of the given type.
     */
    public function hasActivityOfType(Model $owner, string $type): bool;

    /**
     * Delete all activities attached to the given owner.
     *
     * @return int Number of deleted rows.
     */
    public function clearFor(Model $owner): int;
}
