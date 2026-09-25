<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\LaravelActivity\Datas\ActivityData;

/**
 * @extends AbstractTypedCollection<ActivityData>
 */
final class ActivityDataCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(ActivityData::class);
    }
}
