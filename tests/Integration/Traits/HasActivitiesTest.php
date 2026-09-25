<?php

declare(strict_types=1);

namespace Tests\Integration\Traits;

use AndyDefer\LaravelActivity\Contracts\Services\ActivityServiceInterface;
use AndyDefer\LaravelActivity\Enums\ActivityType;
use AndyDefer\LaravelActivity\Models\Activity;
use AndyDefer\LaravelActivity\Tests\Fixtures\Models\TestPost;
use AndyDefer\LaravelActivity\Tests\Fixtures\Models\TestUser;
use AndyDefer\LaravelActivity\Tests\IntegrationTestCase;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class HasActivitiesTest extends IntegrationTestCase
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
    // RELATION
    // ============================================================

    public function test_activities_relation_returns_morph_many(): void
    {
        $this->assertInstanceOf(
            MorphMany::class,
            $this->user->activities(),
        );
    }

    public function test_activities_relation_returns_attached_activities(): void
    {
        $this->user->logActivity('logged_in');
        $this->user->logActivity('viewed', data: ['page' => 'dashboard']);

        $this->assertSame(2, $this->user->activities()->count());
    }

    public function test_activities_relation_returns_only_activities_of_the_model(): void
    {
        $otherUser = TestUser::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $this->user->logActivity('logged_in');
        $otherUser->logActivity('logged_in');
        $otherUser->logActivity('logged_out');

        $this->assertSame(1, $this->user->activities()->count());
        $this->assertSame(2, $otherUser->activities()->count());
    }

    public function test_activities_relation_supports_polymorphic_types(): void
    {
        $this->user->logActivity('viewed');
        $this->post->logActivity('viewed');

        $this->assertSame(1, $this->user->activities()->count());
        $this->assertSame(1, $this->post->activities()->count());
    }

    // ============================================================
    // LOG ACTIVITY
    // ============================================================

    public function test_log_activity_persists_an_activity(): void
    {
        $this->user->logActivity('logged_in');

        $this->assertDatabaseHas('activities', [
            'owner_type' => $this->user->getMorphClass(),
            'owner_id' => (string) $this->user->id,
            'activity_type' => 'logged_in',
        ]);
    }

    public function test_log_activity_returns_the_created_activity(): void
    {
        $activity = $this->user->logActivity('logged_in');

        $this->assertInstanceOf(Activity::class, $activity);
        $this->assertSame('logged_in', $activity->activity_type->getValue());
        $this->assertSame($this->user->getMorphClass(), $activity->owner_type);
        $this->assertSame((string) $this->user->id, $activity->owner_id);
    }

    public function test_log_activity_stores_description(): void
    {
        $this->user->logActivity(
            type: 'logged_in',
            description: 'User signed in from the web',
        );

        $activity = $this->user->latest_activity;

        $this->assertNotNull($activity);
        $this->assertSame('User signed in from the web', $activity->description);
    }

    public function test_log_activity_stores_data_payload(): void
    {
        $this->user->logActivity(
            type: 'viewed',
            data: ['page' => 'dashboard', 'duration_ms' => 320],
        );

        $activity = $this->user->latest_activity;

        $this->assertNotNull($activity);
        $this->assertNotNull($activity->data);
        $this->assertSame('dashboard', $activity->data->get('page'));
        $this->assertSame(320, $activity->data->get('duration_ms'));
    }

    public function test_log_activity_stores_metadata_payload(): void
    {
        $this->user->logActivity(
            type: 'viewed',
            metadata: ['source' => 'mobile', 'app_version' => '2.1.0'],
        );

        $activity = $this->user->latest_activity;

        $this->assertNotNull($activity);
        $this->assertNotNull($activity->metadata);
        $this->assertSame('mobile', $activity->metadata->get('source'));
        $this->assertSame('2.1.0', $activity->metadata->get('app_version'));
    }

    public function test_log_activity_accepts_all_arguments(): void
    {
        $activity = $this->user->logActivity(
            type: 'payment_completed',
            description: 'Payment completed',
            data: ['amount' => 99.00],
            metadata: ['source' => 'web'],
        );

        $this->assertSame('payment_completed', $activity->activity_type->getValue());
        $this->assertSame('Payment completed', $activity->description);
        $this->assertSame(99.00, (float) $activity->data->get('amount'));
        $this->assertSame('web', $activity->metadata->get('source'));
    }

    // ============================================================
    // LATEST ACTIVITY (single)
    // ============================================================

    public function test_latest_activity_returns_null_when_no_activity(): void
    {
        $this->assertNull($this->user->latest_activity);
    }

    public function test_latest_activity_returns_the_most_recent_activity(): void
    {
        $old = $this->user->logActivity('viewed');
        $old->forceFill(['created_at' => now()->subHour()])->save();

        $new = $this->user->logActivity('logged_in');

        $latest = $this->user->latest_activity;

        $this->assertNotNull($latest);
        $this->assertSame($new->id, $latest->id);
    }

    // ============================================================
    // LATEST ACTIVITIES (collection)
    // ============================================================

    public function test_latest_activities_returns_empty_collection_when_no_activity(): void
    {
        $latest = $this->user->latest_activities;

        $this->assertCount(0, $latest);
    }

    public function test_latest_activities_returns_activities_newest_first(): void
    {
        $first = $this->user->logActivity('viewed');
        $first->forceFill(['created_at' => now()->subHours(3)])->save();

        $second = $this->user->logActivity('liked');
        $second->forceFill(['created_at' => now()->subHours(2)])->save();

        $third = $this->user->logActivity('logged_in');
        $third->forceFill(['created_at' => now()->subHour()])->save();

        $latest = $this->user->latest_activities;

        $this->assertCount(3, $latest);
        $this->assertSame($third->id, $latest->first()->id);
        $this->assertSame($first->id, $latest->last()->id);
    }

    public function test_latest_activities_is_limited_to_ten(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $this->user->logActivity(ActivityType::CREATED->value);
        }

        $latest = $this->user->latest_activities;

        $this->assertCount(10, $latest);
    }

    public function test_latest_activities_returns_the_ten_most_recent(): void
    {
        $activities = [];

        for ($i = 1; $i <= 15; $i++) {
            $activity = $this->user->logActivity(ActivityType::CREATED->value);
            $activity->forceFill(['created_at' => now()->subMinutes(15 - $i)])->save();
            $activities[] = $activity;
        }

        $latest = $this->user->latest_activities;

        $this->assertCount(10, $latest);
        $this->assertSame($activities[14]->id, $latest->first()->id);  // i = 15, plus récent
        $this->assertSame($activities[5]->id, $latest->last()->id);    // i = 6, plus ancien des 10
    }
    // ============================================================
    // INTEGRATION WITH ActivityService
    // ============================================================

    public function test_log_activity_uses_the_activity_service(): void
    {
        $this->user->logActivity('logged_in');

        $service = $this->app->make(ActivityServiceInterface::class);

        $this->assertSame(
            1,
            $service->countFor($this->user),
        );
    }

    public function test_log_activity_is_compatible_with_activity_service_queries(): void
    {
        $this->user->logActivity('logged_in');
        $this->user->logActivity('logged_out');
        $this->user->logActivity('viewed');

        $service = $this->app->make(ActivityServiceInterface::class);

        $this->assertTrue(
            $service->hasActivityOfType($this->user, 'logged_in'),
        );

        $this->assertSame(3, $service->countFor($this->user));
        $this->assertSame(1, $service->countForByType($this->user, 'viewed'));
    }
}
