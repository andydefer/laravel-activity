<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpVo\ValueObjects\DateTimeZuluVO;
use AndyDefer\PhpVo\ValueObjects\Strings\UuidVO;

final class ActivityRecord extends AbstractRecord
{
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
