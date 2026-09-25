<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Database\Factories;

use AndyDefer\LaravelActivity\Enums\ActivityType;
use AndyDefer\LaravelActivity\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
        return [
            'owner_type' => 'App\\Models\\User',
            'owner_id' => (string) $this->faker->numberBetween(1, 1000),
            'activity_type' => ActivityType::CREATED->value,
            'description' => $this->faker->sentence(),
            'data' => null,
            'metadata' => null,
        ];
    }

    /**
     * Attach the activity to a given owner.
     */
    public function forOwner(Model $owner): self
    {
        return $this->state(fn (): array => [
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => (string) $owner->getKey(),
        ]);
    }

    /**
     * Set the activity type.
     */
    public function ofType(string $type): self
    {
        return $this->state(fn (): array => [
            'activity_type' => $type,
        ]);
    }

    /**
     * Attach a payload to the activity.
     *
     * @param  array<string, mixed>  $data
     */
    public function withData(array $data): self
    {
        return $this->state(fn (): array => [
            'data' => $data,
        ]);
    }

    /**
     * Attach metadata to the activity.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function withMetadata(array $metadata): self
    {
        return $this->state(fn (): array => [
            'metadata' => $metadata,
        ]);
    }
}
