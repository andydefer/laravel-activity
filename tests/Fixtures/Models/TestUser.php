<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Tests\Fixtures\Models;

use AndyDefer\LaravelActivity\Traits\HasActivities;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestUser extends Model
{
    use HasActivities;

    protected $table = 'test_users';

    protected $fillable = ['name', 'email'];

    public function posts(): HasMany
    {
        return $this->hasMany(TestPost::class, 'user_id');
    }
}
