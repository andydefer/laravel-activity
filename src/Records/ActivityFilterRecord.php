<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\PhpVo\ValueObjects\DateTimeZuluVO;

/**
 * Immutable filter criteria used to query activities.
 *
 * Every property is optional: a null value means "no constraint on this field".
 * The record is intended to be built from user input and forwarded to the
 * activity repository as a single, well-typed query object.
 *
 * @property-read string|null             $owner_type    Restrict to activities owned by this model class.
 * @property-read string|null             $owner_id      Restrict to activities owned by this identifier.
 * @property-read string|null             $activity_type Restrict to activities of this type.
 * @property-read DateTimeZuluVO|null     $from          Inclusive lower bound on the creation date.
 * @property-read DateTimeZuluVO|null     $to            Inclusive upper bound on the creation date.
 */
final class ActivityFilterRecord extends AbstractRecord
{
    /**
     * @param  string|null  $owner_type  Restrict to activities owned by this model class.
     * @param  string|null  $owner_id  Restrict to activities owned by this identifier.
     * @param  string|null  $activity_type  Restrict to activities of this type.
     * @param  DateTimeZuluVO|null  $from  Inclusive lower bound on the creation date.
     * @param  DateTimeZuluVO|null  $to  Inclusive upper bound on the creation date.
     */
    public function __construct(
        public readonly ?string $owner_type = null,
        public readonly ?string $owner_id = null,
        public readonly ?string $activity_type = null,
        public readonly ?DateTimeZuluVO $from = null,
        public readonly ?DateTimeZuluVO $to = null,
    ) {}
}
