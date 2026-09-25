<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Repositories;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\LaravelActivity\Contracts\Repositories\ActivityRepositoryInterface;
use AndyDefer\LaravelActivity\Models\Activity;
use AndyDefer\LaravelActivity\Records\ActivityFilterRecord;
use AndyDefer\LaravelActivity\Records\ActivityRecord;
use AndyDefer\Repository\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Eloquent-backed implementation of {@see ActivityRepositoryInterface}.
 *
 * Retrieves and counts activities owned by a given Eloquent model, using
 * {@see ActivityFilterRecord} as the internal query object and returning
 * hydrated {@see ActivityRecord} instances (or raw {@see Activity} models
 * for single-result lookups).
 *
 * @extends AbstractRepository<Activity, ActivityRecord>
 */
class ActivityRepository extends AbstractRepository implements ActivityRepositoryInterface
{
    /**
     * Bind the repository to its Eloquent model and read model.
     */
    public function __construct()
    {
        parent::__construct(
            modelClass: Activity::class,
            recordClass: ActivityRecord::class,
        );
    }

    /**
     * {@inheritDoc}
     *
     * @see ActivityRepositoryInterface::getFor()
     */
    public function getFor(Model $owner, ?int $limit = null): Collection
    {
        return $this->buildOwnerQuery($owner, type: null, limit: $limit);
    }

    /**
     * {@inheritDoc}
     *
     * @see ActivityRepositoryInterface::getForByType()
     */
    public function getForByType(Model $owner, string $type, ?int $limit = null): Collection
    {
        return $this->buildOwnerQuery($owner, type: $type, limit: $limit);
    }

    /**
     * {@inheritDoc}
     *
     * @see ActivityRepositoryInterface::getLatestFor()
     */
    public function getLatestFor(Model $owner): ?Activity
    {
        return $this->buildOwnerQuery($owner, type: null, limit: 1)->first();
    }

    /**
     * {@inheritDoc}
     *
     * @see ActivityRepositoryInterface::countFor()
     */
    public function countFor(Model $owner): int
    {
        return $this->count($this->buildOwnerFilters($owner));
    }

    /**
     * {@inheritDoc}
     *
     * @see ActivityRepositoryInterface::countForByType()
     */
    public function countForByType(Model $owner, string $type): int
    {
        return $this->count($this->buildOwnerFilters($owner, type: $type));
    }

    /**
     * Apply the given filters to the query when they are an ActivityFilterRecord.
     *
     * Non-activity filter records are ignored: this keeps the method compatible
     * with the parent repository contract while remaining a no-op for other
     * filter types.
     */
    protected function applyFilters(Builder $query, AbstractRecord $filters): void
    {
        if (! $filters instanceof ActivityFilterRecord) {
            return;
        }

        $this->whereIfNotNull($query, 'owner_type', $filters->owner_type);
        $this->whereIfNotNull($query, 'owner_id', $filters->owner_id);
        $this->whereIfNotNull($query, 'activity_type', $filters->activity_type);

        if ($filters->from !== null) {
            $query->where('created_at', '>=', $filters->from->getValue());
        }

        if ($filters->to !== null) {
            $query->where('created_at', '<=', $filters->to->getValue());
        }
    }

    /**
     * Build and execute the retrieval query for activities owned by the given model.
     *
     * Results are ordered from most recent to oldest and optionally limited.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Activity>
     */
    private function buildOwnerQuery(Model $owner, ?string $type, ?int $limit): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->buildQuery($this->buildOwnerFilters($owner, $type))
            ->orderByDesc('created_at');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Build the filter record identifying activities owned by the given model,
     * optionally restricted to a specific activity type.
     */
    private function buildOwnerFilters(Model $owner, ?string $type = null): ActivityFilterRecord
    {
        return ActivityFilterRecord::from([
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => (string) $owner->getKey(),
            'activity_type' => $type,
        ]);
    }

    /**
     * Add a `where` clause on the given column when the value is not null.
     */
    private function whereIfNotNull(Builder $query, string $column, mixed $value): void
    {
        if ($value !== null) {
            $query->where($column, $value);
        }
    }
}
