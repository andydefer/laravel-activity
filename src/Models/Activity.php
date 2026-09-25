<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Models;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelActivity\Database\Factories\ActivityFactory;
use AndyDefer\Repository\Casts\EnumCast;
use AndyDefer\Repository\Contracts\EnumerableInterface;
use AndyDefer\Repository\Proxies\AttributeProxy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Activity model representing a tracked action on an owner.
 *
 * @property string $id
 * @property string $owner_type
 * @property string $owner_id
 * @property EnumerableInterface $activity_type
 * @property string|null $description
 * @property StrictDataObject|null $data
 * @property StrictDataObject|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Model|null $owner
 */
class Activity extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $incrementing = false;

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'activity_type',
        'description',
        'data',
        'metadata',
    ];

    protected $casts = [
        'activity_type' => EnumCast::class,
    ];

    public function getTable(): string
    {
        return (string) config('activity.table', 'activities');
    }

    protected static function newFactory(): Factory
    {
        return ActivityFactory::new();
    }

    protected static function boot(): void
    {
        parent::boot();

        self::creating(function (self $activity): void {
            if (empty($activity->id)) {
                $activity->id = (string) str()->uuid();
            }
        });
    }

    /**
     * The owner of the activity.
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    protected function data(): Attribute
    {
        return AttributeProxy::nullable(
            StrictDataObject::class,
            column: 'data',
        );
    }

    protected function metadata(): Attribute
    {
        return AttributeProxy::nullable(
            StrictDataObject::class,
            column: 'metadata',
        );
    }
}
