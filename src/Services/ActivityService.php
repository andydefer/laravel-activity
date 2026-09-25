<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Services;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelActivity\Contracts\Repositories\ActivityRepositoryInterface;
use AndyDefer\LaravelActivity\Contracts\Services\ActivityServiceInterface;
use AndyDefer\LaravelActivity\Records\ActivityFilterRecord;
use AndyDefer\LaravelActivity\Records\ActivityRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class ActivityService implements ActivityServiceInterface
{
    public function __construct(
        private readonly ActivityRepositoryInterface $activityRepository,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function log(
        Model $owner,
        string $type,
        ?string $description = null,
        ?array $data = null,
        ?array $metadata = null,
    ): Model {
        $record = ActivityRecord::from([
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => (string) $owner->getKey(),
            'activity_type' => $type,
            'description' => $description,
            'data' => $data !== null ? new StrictDataObject($data) : null,
            'metadata' => $metadata !== null ? new StrictDataObject($metadata) : null,
        ]);

        return $this->activityRepository->create($record);
    }

    /**
     * {@inheritDoc}
     */
    public function getFor(Model $owner, ?int $limit = null): Collection
    {
        return $this->activityRepository->getFor($owner, $limit);
    }

    /**
     * {@inheritDoc}
     */
    public function getForByType(Model $owner, string $type, ?int $limit = null): Collection
    {
        return $this->activityRepository->getForByType($owner, $type, $limit);
    }

    /**
     * {@inheritDoc}
     */
    public function getLatestFor(Model $owner): ?Model
    {
        return $this->activityRepository->getLatestFor($owner);
    }

    /**
     * {@inheritDoc}
     */
    public function countFor(Model $owner): int
    {
        return $this->activityRepository->countFor($owner);
    }

    /**
     * {@inheritDoc}
     */
    public function countForByType(Model $owner, string $type): int
    {
        return $this->activityRepository->countForByType($owner, $type);
    }

    /**
     * {@inheritDoc}
     */
    public function hasActivityOfType(Model $owner, string $type): bool
    {
        return $this->activityRepository->exists(ActivityFilterRecord::from([
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => (string) $owner->getKey(),
            'activity_type' => $type,
        ]));
    }

    /**
     * {@inheritDoc}
     */
    public function clearFor(Model $owner): int
    {
        return $this->activityRepository->deleteBulk(ActivityFilterRecord::from([
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => (string) $owner->getKey(),
        ]));
    }
}
