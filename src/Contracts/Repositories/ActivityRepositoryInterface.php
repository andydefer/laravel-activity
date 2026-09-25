<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Contracts\Repositories;

use AndyDefer\LaravelActivity\Models\Activity;
use AndyDefer\Repository\AbstractRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Contract for the activity repository.
 *
 * Defines the read operations required to retrieve and count activities
 * attached to a given Eloquent model (the "owner").
 *
 * All retrieval methods return activities ordered from most recent to oldest,
 * unless stated otherwise by the implementation.
 *
 * @extends AbstractRepositoryInterface<Activity>
 */
interface ActivityRepositoryInterface extends AbstractRepositoryInterface
{
    /**
     * Retrieve all activities belonging to the given owner.
     *
     * @param  Model  $owner  The Eloquent model that owns the activities.
     * @param  int|null  $limit  Maximum number of activities to return, or null for no limit.
     * @return Collection<int, Activity>
     */
    public function getFor(Model $owner, ?int $limit = null): Collection;

    /**
     * Retrieve activities belonging to the given owner, filtered by type.
     *
     * @param  Model  $owner  The Eloquent model that owns the activities.
     * @param  string  $type  The activity type to filter on.
     * @param  int|null  $limit  Maximum number of activities to return, or null for no limit.
     * @return Collection<int, Activity>
     */
    public function getForByType(Model $owner, string $type, ?int $limit = null): Collection;

    /**
     * Retrieve the most recent activity for the given owner.
     *
     * @param  Model  $owner  The Eloquent model that owns the activities.
     * @return Activity|null The latest activity, or null when the owner has none.
     */
    public function getLatestFor(Model $owner): ?Activity;

    /**
     * Count all activities belonging to the given owner.
     *
     * @param  Model  $owner  The Eloquent model that owns the activities.
     * @return int The total number of activities for this owner.
     */
    public function countFor(Model $owner): int;

    /**
     * Count activities belonging to the given owner, filtered by type.
     *
     * @param  Model  $owner  The Eloquent model that owns the activities.
     * @param  string  $type  The activity type to filter on.
     * @return int The number of matching activities for this owner.
     */
    public function countForByType(Model $owner, string $type): int;
}
