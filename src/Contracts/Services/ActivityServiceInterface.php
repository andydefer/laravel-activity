<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Contracts\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Defines the business operations available for recording and querying
 * polymorphic activities attached to any Eloquent model.
 *
 * Implementations are responsible for translating these operations into
 * persistence calls (typically delegated to an ActivityRepositoryInterface).
 */
interface ActivityServiceInterface
{
    /**
     * Records a new activity for the given owner.
     *
     * @param  Model  $owner  The model the activity is attached to.
     * @param  string  $type  The activity type (backed enum value or arbitrary string).
     * @param  string|null  $description  Optional human-readable description.
     * @param  array<string, mixed>|null  $data  Optional payload data associated with the activity.
     * @param  array<string, mixed>|null  $metadata  Optional metadata associated with the activity.
     * @return Model The newly created activity model.
     */
    public function log(
        Model $owner,
        string $type,
        ?string $description = null,
        ?array $data = null,
        ?array $metadata = null,
    ): Model;

    /**
     * Returns the activities attached to the given owner, most recent first.
     *
     * @param  Model  $owner  The model whose activities should be retrieved.
     * @param  int|null  $limit  Optional maximum number of activities to return.
     * @return Collection<int, Model>
     */
    public function getFor(Model $owner, ?int $limit = null): Collection;

    /**
     * Returns the activities attached to the given owner and matching the given type,
     * most recent first.
     *
     * @param  Model  $owner  The model whose activities should be retrieved.
     * @param  string  $type  The activity type to filter by.
     * @param  int|null  $limit  Optional maximum number of activities to return.
     * @return Collection<int, Model>
     */
    public function getForByType(Model $owner, string $type, ?int $limit = null): Collection;

    /**
     * Returns the most recent activity attached to the given owner, or null if none exists.
     *
     * @param  Model  $owner  The model whose latest activity should be retrieved.
     */
    public function getLatestFor(Model $owner): ?Model;

    /**
     * Counts the activities attached to the given owner.
     *
     * @param  Model  $owner  The model whose activities should be counted.
     */
    public function countFor(Model $owner): int;

    /**
     * Counts the activities attached to the given owner and matching the given type.
     *
     * @param  Model  $owner  The model whose activities should be counted.
     * @param  string  $type  The activity type to filter by.
     */
    public function countForByType(Model $owner, string $type): int;

    /**
     * Determines whether the given owner has at least one activity of the given type.
     *
     * @param  Model  $owner  The model to check.
     * @param  string  $type  The activity type to look for.
     */
    public function hasActivityOfType(Model $owner, string $type): bool;

    /**
     * Deletes every activity attached to the given owner.
     *
     * @param  Model  $owner  The model whose activities should be removed.
     * @return int The number of deleted activities.
     */
    public function clearFor(Model $owner): int;
}
