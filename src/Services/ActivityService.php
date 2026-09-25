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

/**
 * Application-level service orchestrating activity tracking.
 *
 * Provides a high-level, repository-agnostic API to log activities and to
 * query, count, check, or clear the activities owned by a given Eloquent model.
 *
 * This service is the recommended entry point for consumers: it hides the
 * repository contract and centralises the translation from raw input into
 * {@see ActivityFilterRecord} and {@see ActivityRecord} objects.
 *
 * @see ActivityServiceInterface
 */
final class ActivityService implements ActivityServiceInterface
{
    /**
     * @param  ActivityRepositoryInterface  $activityRepository  Persistence layer for activities.
     */
    public function __construct(
        private readonly ActivityRepositoryInterface $activityRepository,
    ) {}

    /**
     * {@inheritDoc}
     *
     * @see ActivityServiceInterface::log()
     */
    public function log(
        Model $owner,
        string $type,
        ?string $description = null,
        ?array $data = null,
        ?array $metadata = null,
    ): Model {
        return $this->activityRepository->create(ActivityRecord::from([
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => (string) $owner->getKey(),
            'activity_type' => $type,
            'description' => $description,
            'data' => $this->toStrictDataObject($data),
            'metadata' => $this->toStrictDataObject($metadata),
        ]));
    }

    /**
     * {@inheritDoc}
     *
     * @see ActivityServiceInterface::getFor()
     */
    public function getFor(Model $owner, ?int $limit = null): Collection
    {
        return $this->activityRepository->getFor($owner, $limit);
    }

    /**
     * {@inheritDoc}
     *
     * @see ActivityServiceInterface::getForByType()
     */
    public function getForByType(Model $owner, string $type, ?int $limit = null): Collection
    {
        return $this->activityRepository->getForByType($owner, $type, $limit);
    }

    /**
     * {@inheritDoc}
     *
     * @see ActivityServiceInterface::getLatestFor()
     */
    public function getLatestFor(Model $owner): ?Model
    {
        return $this->activityRepository->getLatestFor($owner);
    }

    /**
     * {@inheritDoc}
     *
     * @see ActivityServiceInterface::countFor()
     */
    public function countFor(Model $owner): int
    {
        return $this->activityRepository->countFor($owner);
    }

    /**
     * {@inheritDoc}
     *
     * @see ActivityServiceInterface::countForByType()
     */
    public function countForByType(Model $owner, string $type): int
    {
        return $this->activityRepository->countForByType($owner, $type);
    }

    /**
     * {@inheritDoc}
     *
     * @see ActivityServiceInterface::hasActivityOfType()
     */
    public function hasActivityOfType(Model $owner, string $type): bool
    {
        return $this->activityRepository->exists(
            $this->buildOwnerFilter($owner, type: $type)
        );
    }

    /**
     * {@inheritDoc}
     *
     * @see ActivityServiceInterface::clearFor()
     */
    public function clearFor(Model $owner): int
    {
        return $this->activityRepository->deleteBulk(
            $this->buildOwnerFilter($owner)
        );
    }

    /**
     * Build a filter record identifying activities owned by the given model,
     * optionally restricted to a specific activity type.
     */
    private function buildOwnerFilter(Model $owner, ?string $type = null): ActivityFilterRecord
    {
        return ActivityFilterRecord::from([
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => (string) $owner->getKey(),
            'activity_type' => $type,
        ]);
    }

    /**
     * Wrap the given payload into a {@see StrictDataObject}, or return null
     * when no payload was provided.
     *
     * @param  array<string, mixed>|null  $payload
     */
    private function toStrictDataObject(?array $payload): ?StrictDataObject
    {
        return $payload !== null ? new StrictDataObject($payload) : null;
    }
}
