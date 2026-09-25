<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Tests\Integration\Models;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelActivity\Models\Activity;
use AndyDefer\LaravelActivity\Tests\Fixtures\Models\TestPost;
use AndyDefer\LaravelActivity\Tests\Fixtures\Models\TestUser;
use AndyDefer\LaravelActivity\Tests\IntegrationTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class ActivityTest extends IntegrationTestCase
{
    use RefreshDatabase;

    private TestUser $user;

    private TestPost $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->post = TestPost::create([
            'user_id' => $this->user->id,
            'title' => 'Test Post',
            'body' => 'Test content',
        ]);
    }

    // ============================================================
    // FACTORY
    // ============================================================

    public function test_factory_creates_an_activity_with_defaults(): void
    {
        $activity = Activity::factory()->create();

        $this->assertInstanceOf(Activity::class, $activity);
        $this->assertNotNull($activity->id);
        $this->assertNotNull($activity->owner_type);
        $this->assertNotNull($activity->owner_id);
        $this->assertNotEmpty($activity->activity_type);
    }

    public function test_factory_for_owner_attaches_the_activity_to_the_owner(): void
    {
        $activity = Activity::factory()
            ->forOwner($this->post)
            ->create();

        $this->assertSame(TestPost::class, $activity->owner_type);
        $this->assertSame((string) $this->post->id, $activity->owner_id);
    }

    public function test_factory_of_type_sets_the_activity_type(): void
    {
        $activity = Activity::factory()
            ->ofType('logged_in')
            ->create();

        $this->assertSame('logged_in', $activity->activity_type->value);
    }

    public function test_factory_with_data_stores_the_payload(): void
    {
        $activity = Activity::factory()
            ->withData(['ip' => '127.0.0.1', 'device' => 'iPhone'])
            ->create();

        $this->assertInstanceOf(StrictDataObject::class, $activity->data);
        $this->assertSame('127.0.0.1', $activity->data->get('ip'));
        $this->assertSame('iPhone', $activity->data->get('device'));
    }

    public function test_factory_with_metadata_stores_the_metadata(): void
    {
        $activity = Activity::factory()
            ->withMetadata(['source' => 'web'])
            ->create();

        $this->assertInstanceOf(StrictDataObject::class, $activity->metadata);
        $this->assertSame('web', $activity->metadata->get('source'));
    }

    // ============================================================
    // CASTS
    // ============================================================

    public function test_activity_type_is_stored_and_retrieved_as_string(): void
    {
        $activity = Activity::factory()
            ->ofType('logged_in')
            ->create();

        $this->assertSame('logged_in', $activity->activity_type->value);
    }

    public function test_data_returns_strict_data_object(): void
    {
        $activity = Activity::factory()
            ->withData(['ip' => '127.0.0.1', 'device' => 'iPhone'])
            ->create();

        $this->assertInstanceOf(StrictDataObject::class, $activity->data);
        $this->assertSame('127.0.0.1', $activity->data->get('ip'));
        $this->assertSame('iPhone', $activity->data->get('device'));
    }

    public function test_data_returns_null_when_not_set(): void
    {
        $activity = Activity::factory()->create();

        $this->assertNull($activity->data);
    }

    public function test_metadata_returns_strict_data_object(): void
    {
        $activity = Activity::factory()
            ->withMetadata(['source' => 'web'])
            ->create();

        $this->assertInstanceOf(StrictDataObject::class, $activity->metadata);
        $this->assertSame('web', $activity->metadata->get('source'));
    }

    public function test_metadata_returns_null_when_not_set(): void
    {
        $activity = Activity::factory()->create();

        $this->assertNull($activity->metadata);
    }

    // ============================================================
    // RELATIONS
    // ============================================================

    public function test_owner_returns_the_correct_model(): void
    {
        $activity = Activity::factory()
            ->forOwner($this->post)
            ->create();

        $owner = $activity->owner;

        $this->assertInstanceOf(TestPost::class, $owner);
        $this->assertSame($this->post->id, $owner->id);
    }

    public function test_owner_returns_null_when_owner_model_is_deleted(): void
    {
        $activity = Activity::factory()
            ->forOwner($this->post)
            ->create();

        $this->post->delete();

        $fresh = Activity::find($activity->id);

        $this->assertNull($fresh->owner);
    }

    // ============================================================
    // SOFT DELETES
    // ============================================================

    public function test_soft_delete_excludes_from_default_queries(): void
    {
        $activity = Activity::factory()->create();

        $activity->delete();

        $this->assertNull(Activity::find($activity->id));
    }

    public function test_with_trashed_includes_deleted_activities(): void
    {
        $activity = Activity::factory()->create();

        $activity->delete();

        $found = Activity::withTrashed()->find($activity->id);

        $this->assertNotNull($found);
        $this->assertNotNull($found->deleted_at);
    }

    public function test_restore_recovers_a_soft_deleted_activity(): void
    {
        $activity = Activity::factory()->create();

        $activity->delete();
        $activity->restore();

        $this->assertNotNull(Activity::find($activity->id));
    }

    // ============================================================
    // TABLE NAME FROM CONFIG
    // ============================================================

    public function test_table_name_comes_from_config(): void
    {
        config()->set('activity.table', 'custom_activities');

        $activity = new Activity;

        $this->assertSame('custom_activities', $activity->getTable());
    }

    public function test_table_name_defaults_to_activities(): void
    {
        config()->set('activity.table', 'activities');

        $activity = new Activity;

        $this->assertSame('activities', $activity->getTable());
    }
}
