<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Contracts\Repositories;

use AndyDefer\LaravelActivity\Models\Activity;
use AndyDefer\Repository\AbstractRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface ActivityRepositoryInterface extends AbstractRepositoryInterface
{
    /**
     * Return all activities for the given owner, with an optional limit.
     *
     * @return Collection<int, Activity>
     */
    public function getFor(Model $owner, ?int $limit = null): Collection;

    /**
     * Return activities for the given owner filtered by type, with an optional limit.
     *
     * @return Collection<int, Activity>
     */
    public function getForByType(Model $owner, string $type, ?int $limit = null): Collection;

    /**
     * Return the latest activity for the given owner, or null.
     */
    public function getLatestFor(Model $owner): ?Activity;

    /**
     * Count activities for the given owner.
     */
    public function countFor(Model $owner): int;

    /**
     * Count activities for the given owner filtered by type.
     */
    public function countForByType(Model $owner, string $type): int;
}
