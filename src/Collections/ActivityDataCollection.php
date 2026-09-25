<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\LaravelActivity\Datas\ActivityData;

/**
 * Typed collection dedicated to {@see ActivityData} instances.
 *
 * Guarantees that every element added to the collection is an ActivityData
 * object, and provides IDE/static-analysis support for typed accessors
 * inherited from {@see AbstractTypedCollection}.
 *
 * @extends AbstractTypedCollection<ActivityData>
 */
final class ActivityDataCollection extends AbstractTypedCollection
{
    /**
     * Initialise the collection with its element type constraint.
     */
    public function __construct()
    {
        parent::__construct(ActivityData::class);
    }
}
