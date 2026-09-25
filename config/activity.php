<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Table name
    |--------------------------------------------------------------------------
    |
    | Name of the table used to store activities.
    |
    */
    'table' => 'activities',

    /*
    |--------------------------------------------------------------------------
    | Model morph alias
    |--------------------------------------------------------------------------
    |
    | When true, the `subject` and `causer` morph types are stored using the
    | morph alias (config('database.morph_map')) instead of the FQCN.
    |
    */
    'use_morph_alias' => false,

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    |
    | Number of days activities are kept before being eligible for pruning.
    | Null disables pruning.
    |
    */
    'retention_days' => null,
];
