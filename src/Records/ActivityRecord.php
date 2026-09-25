<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelActivity\Models\Activity;
use AndyDefer\PhpVo\ValueObjects\DateTimeZuluVO;
use AndyDefer\PhpVo\ValueObjects\Strings\UuidVO;

/**
 * Immutable read model representing a single persisted activity.
 *
 * Mirrors the {@see Activity} shape but
 * decoupled from Eloquent: identifiers and timestamps are exposed as value
 * objects so that domain code never has to deal with raw strings or Carbon.
 *
 * All properties are nullable to support partial hydration (e.g. when only
 * a subset of columns is selected by the repository).
 *
 * @property-read UuidVO|null             $id            UUID identifier of the activity.
 * @property-read string|null             $owner_type    Fully-qualified class name of the owning model.
 * @property-read string|null             $owner_id      Identifier of the owning model instance.
 * @property-read string|null             $activity_type Machine-readable activity type.
 * @property-read string|null             $description   Human-readable description, if any.
 * @property-read StrictDataObject|null   $data          Arbitrary activity payload, if any.
 * @property-read StrictDataObject|null   $metadata      Arbitrary contextual metadata, if any.
 * @property-read DateTimeZuluVO|null     $created_at    Creation timestamp (UTC), if any.
 * @property-read DateTimeZuluVO|null     $updated_at    Last update timestamp (UTC), if any.
 * @property-read DateTimeZuluVO|null     $deleted_at    Soft-deletion timestamp (UTC), if any.
 */
final class ActivityRecord extends AbstractRecord
{
    /**
     * @param  UuidVO|null  $id  UUID identifier of the activity.
     * @param  string|null  $owner_type  Fully-qualified class name of the owning model.
     * @param  string|null  $owner_id  Identifier of the owning model instance.
     * @param  string|null  $activity_type  Machine-readable activity type.
     * @param  string|null  $description  Human-readable description, if any.
     * @param  StrictDataObject|null  $data  Arbitrary activity payload, if any.
     * @param  StrictDataObject|null  $metadata  Arbitrary contextual metadata, if any.
     * @param  DateTimeZuluVO|null  $created_at  Creation timestamp (UTC), if any.
     * @param  DateTimeZuluVO|null  $updated_at  Last update timestamp (UTC), if any.
     * @param  DateTimeZuluVO|null  $deleted_at  Soft-deletion timestamp (UTC), if any.
     */
    public function __construct(
        public readonly ?UuidVO $id = null,
        public readonly ?string $owner_type = null,
        public readonly ?string $owner_id = null,
        public readonly ?string $activity_type = null,
        public readonly ?string $description = null,
        public readonly ?StrictDataObject $data = null,
        public readonly ?StrictDataObject $metadata = null,
        public readonly ?DateTimeZuluVO $created_at = null,
        public readonly ?DateTimeZuluVO $updated_at = null,
        public readonly ?DateTimeZuluVO $deleted_at = null,
    ) {}
}
