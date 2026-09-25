<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestUser extends Model
{
    protected $table = 'test_users';

    protected $fillable = ['name', 'email'];

    public function posts(): HasMany
    {
        return $this->hasMany(TestPost::class, 'user_id');
    }
}
