<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Traits\Hydratable;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpVo\ValueObjects\DateTimeZuluVO;

/**
 * Data Transfer Object for Activity.
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
