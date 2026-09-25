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
 * Eloquent model representing a tracked activity performed on an owner.
 *
 * Each activity belongs to a polymorphic owner (via `owner_type` / `owner_id`)
 * and carries an activity type, an optional description, plus optional
 * `data` and `metadata` payloads stored as {@see StrictDataObject} instances.
 *
 * The primary key is a UUID string generated automatically on creation.
 * Soft deletes are enabled: records are flagged with `deleted_at` rather than
 * physically removed.
 *
 * @property string $id UUID primary key.
 * @property string $owner_type Fully-qualified class name of the owning model.
 * @property string $owner_id Identifier of the owning model instance.
 * @property EnumerableInterface $activity_type Activity type value object.
 * @property string|null $description Human-readable description, if any.
 * @property StrictDataObject|null $data Arbitrary activity payload, if any.
 * @property StrictDataObject|null $metadata Arbitrary contextual metadata, if any.
 * @property Carbon|null $created_at Creation timestamp.
 * @property Carbon|null $updated_at Last update timestamp.
 * @property Carbon|null $deleted_at Soft-deletion timestamp.
 * @property-read Model|null            $owner         Polymorphic owner of the activity.
 *
 * @use HasFactory<ActivityFactory>
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

    /**
     * Resolve the database table name from the package configuration.
     *
     * Falls back to `activities` when no custom table is configured.
     */
    public function getTable(): string
    {
        return (string) config('activity.table', 'activities');
    }

    /**
     * Bind the dedicated activity factory to this model.
     */
    protected static function newFactory(): Factory
    {
        return ActivityFactory::new();
    }

    /**
     * Register model event listeners.
     */
    protected static function boot(): void
    {
        parent::boot();

        self::creating(function (self $activity): void {
            $activity->assignUuidIfMissing();
        });
    }

    /**
     * The polymorphic owner of the activity.
     *
     * @return MorphTo<Model, $this>
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Cast the `data` column to a {@see StrictDataObject}, or null when absent.
     */
    protected function data(): Attribute
    {
        return AttributeProxy::nullable(
            StrictDataObject::class,
            column: 'data',
        );
    }

    /**
     * Cast the `metadata` column to a {@see StrictDataObject}, or null when absent.
     */
    protected function metadata(): Attribute
    {
        return AttributeProxy::nullable(
            StrictDataObject::class,
            column: 'metadata',
        );
    }

    /**
     * Ensure the model has a UUID primary key before persistence.
     */
    private function assignUuidIfMissing(): void
    {
        if (empty($this->id)) {
            $this->id = (string) str()->uuid();
        }
    }
}
