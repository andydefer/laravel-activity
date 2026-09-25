<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\PhpVo\ValueObjects\DateTimeZuluVO;

final class ActivityFilterRecord extends AbstractRecord
{
    public function __construct(
        public readonly ?string $owner_type = null,
        public readonly ?string $owner_id = null,
        public readonly ?string $activity_type = null,
        public readonly ?DateTimeZuluVO $from = null,
        public readonly ?DateTimeZuluVO $to = null,
    ) {}
}
