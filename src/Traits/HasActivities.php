<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Traits;

use AndyDefer\LaravelActivity\Contracts\Services\ActivityServiceInterface;
use AndyDefer\LaravelActivity\Models\Activity;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * Adds activity tracking capabilities to an Eloquent model.
 *
 * Exposes:
 *  - a polymorphic `activities` relation,
 *  - a `logActivity()` helper to persist a new activity,
 *  - the `latest_activities` attribute (10 most recent),
 *  - the `latest_activity` attribute (single most recent).
 *
 * @property-read Collection<int, Activity> $latest_activities
 * @property-read Activity|null $latest_activity
 */
trait HasActivities
{
    private const LATEST_ACTIVITIES_LIMIT = 10;

    /**
     * All activities attached to this model.
     */
    public function activities(): MorphMany
    {
        /** @var Model $this */
        return $this->morphMany(Activity::class, 'owner');
    }

    /**
     * The ten most recent activities of this model, newest first.
     *
     * @return Attribute<Collection<int, Activity>, never>
     */
    protected function latestActivities(): Attribute
    {
        return Attribute::make(
            get: fn (): Collection => $this->activities()
                ->orderByDesc('created_at')
                ->limit(self::LATEST_ACTIVITIES_LIMIT)
                ->get(),
        );
    }

    /**
     * The single most recent activity of this model, or null.
     *
     * @return Attribute<Activity|null, never>
     */
    protected function latestActivity(): Attribute
    {
        return Attribute::make(
            get: fn (): ?Activity => $this->activities()
                ->orderByDesc('created_at')
                ->first(),
        );
    }

    /**
     * Log a new activity for this model.
     *
     * @param  array<string, mixed>|null  $data
     * @param  array<string, mixed>|null  $metadata
     */
    public function logActivity(
        string $type,
        ?string $description = null,
        ?array $data = null,
        ?array $metadata = null,
    ): Activity {
        /** @var ActivityServiceInterface $service */
        $service = app(ActivityServiceInterface::class);

        /** @var Activity $activity */
        $activity = $service->log(
            owner: $this,
            type: $type,
            description: $description,
            data: $data,
            metadata: $metadata,
        );

        return $activity;
    }
}
