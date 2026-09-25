<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Traits\Hydratable;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpVo\ValueObjects\DateTimeZuluVO;

/**
 * Immutable Data Transfer Object representing a single activity record.
 *
 * Carries the persisted state of an activity: its identity, polymorphic owner,
 * type, optional description and payload, plus audit timestamps.
 *
 * Instances are created either directly through the constructor or via the
 * hydration helpers provided by {@see Hydratable} (e.g. `ActivityData::from()`).
 *
 * @property-read string             $id           Unique activity identifier (UUID).
 * @property-read string             $ownerType    Fully-qualified class name of the owning model.
 * @property-read string             $ownerId      Identifier of the owning model instance.
 * @property-read string             $activityType Machine-readable activity type (e.g. 'logged_in').
 * @property-read string|null        $description  Human-readable description, if any.
 * @property-read StrictDataObject|null $data      Arbitrary activity payload, if any.
 * @property-read StrictDataObject|null $metadata  Arbitrary contextual metadata, if any.
 * @property-read DateTimeZuluVO|null $createdAt  Creation timestamp (UTC), if any.
 * @property-read DateTimeZuluVO|null $updatedAt  Last update timestamp (UTC), if any.
 * @property-read DateTimeZuluVO|null $deletedAt  Soft-deletion timestamp (UTC), if any.
 *
 * @method static self from(array<string, mixed> $attributes) Hydrate an instance from an associative array.
 *
 * @example
 * $activityData = ActivityData::from([
 *     'id' => '9b724dbf-32a7-4e63-96bb-59a4747e43ca',
 *     'ownerType' => User::class,
 *     'ownerId' => '123',
 *     'activityType' => 'logged_in',
 *     'description' => 'John Doe logged in',
 *     'data' => ['ip' => '127.0.0.1'],
 *     'metadata' => ['source' => 'web'],
 *     'createdAt' => '2024-01-15T10:00:00Z',
 * ]);
 */
final class ActivityData extends AbstractData
{
    use Hydratable;

    /**
     * @param  string  $id  Unique activity identifier (UUID).
     * @param  string  $ownerType  Fully-qualified class name of the owning model.
     * @param  string  $ownerId  Identifier of the owning model instance.
     * @param  string  $activityType  Machine-readable activity type.
     * @param  string|null  $description  Human-readable description, if any.
     * @param  StrictDataObject|null  $data  Arbitrary activity payload, if any.
     * @param  StrictDataObject|null  $metadata  Arbitrary contextual metadata, if any.
     * @param  DateTimeZuluVO|null  $createdAt  Creation timestamp (UTC), if any.
     * @param  DateTimeZuluVO|null  $updatedAt  Last update timestamp (UTC), if any.
     * @param  DateTimeZuluVO|null  $deletedAt  Soft-deletion timestamp (UTC), if any.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $ownerType,
        public readonly string $ownerId,
        public readonly string $activityType,
        public readonly ?string $description,
        public readonly ?StrictDataObject $data,
        public readonly ?StrictDataObject $metadata,
        public readonly ?DateTimeZuluVO $createdAt,
        public readonly ?DateTimeZuluVO $updatedAt,
        public readonly ?DateTimeZuluVO $deletedAt,
    ) {}
}
