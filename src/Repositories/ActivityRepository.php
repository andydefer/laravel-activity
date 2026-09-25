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

class ActivityRepository extends AbstractRepository implements ActivityRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(
            modelClass: Activity::class,
            recordClass: ActivityRecord::class,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getFor(Model $owner, ?int $limit = null): Collection
    {
        return $this->buildOwnerQuery($owner, null, $limit);
    }

    /**
     * {@inheritDoc}
     */
    public function getForByType(Model $owner, string $type, ?int $limit = null): Collection
    {
        return $this->buildOwnerQuery($owner, $type, $limit);
    }

    /**
     * {@inheritDoc}
     */
    public function getLatestFor(Model $owner): ?Activity
    {
        return $this->buildOwnerQuery($owner, null, 1)->first();
    }

    /**
     * {@inheritDoc}
     */
    public function countFor(Model $owner): int
    {
        return $this->count(ActivityFilterRecord::from([
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => (string) $owner->getKey(),
        ]));
    }

    /**
     * {@inheritDoc}
     */
    public function countForByType(Model $owner, string $type): int
    {
        return $this->count(ActivityFilterRecord::from([
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => (string) $owner->getKey(),
            'activity_type' => $type,
        ]));
    }

    protected function applyFilters(Builder $query, AbstractRecord $filters): void
    {
        if (! $filters instanceof ActivityFilterRecord) {
            return;
        }

        if ($filters->owner_type !== null) {
            $query->where('owner_type', $filters->owner_type);
        }

        if ($filters->owner_id !== null) {
            $query->where('owner_id', $filters->owner_id);
        }

        if ($filters->activity_type !== null) {
            $query->where('activity_type', $filters->activity_type);
        }

        if ($filters->from !== null) {
            $query->where('created_at', '>=', $filters->from->getValue());
        }

        if ($filters->to !== null) {
            $query->where('created_at', '<=', $filters->to->getValue());
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Activity>
     */
    private function buildOwnerQuery(Model $owner, ?string $type, ?int $limit): \Illuminate\Database\Eloquent\Collection
    {
        $filters = ActivityFilterRecord::from([
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => (string) $owner->getKey(),
            'activity_type' => $type,
        ]);

        $query = $this->buildQuery($filters)->orderByDesc('created_at');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }
}
