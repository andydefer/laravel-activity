<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Tests\Integration\Services;

use AndyDefer\LaravelActivity\Contracts\Services\ActivityServiceInterface;
use AndyDefer\LaravelActivity\Models\Activity;
use AndyDefer\LaravelActivity\Tests\Fixtures\Models\TestPost;
use AndyDefer\LaravelActivity\Tests\Fixtures\Models\TestUser;
use AndyDefer\LaravelActivity\Tests\IntegrationTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class ActivityServiceTest extends IntegrationTestCase
{
    use RefreshDatabase;

    private ActivityServiceInterface $service;

    private TestUser $user;

    private TestPost $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(ActivityServiceInterface::class);

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
    // LOG
    // ============================================================

    public function test_log_creates_an_activity_with_all_fields(): void
    {
        $activity = $this->service->log(
            owner: $this->post,
            type: 'viewed',
            description: 'User viewed the post',
            data: ['ip' => '127.0.0.1'],
            metadata: ['source' => 'web'],
        );

        $this->assertInstanceOf(Activity::class, $activity);
        $this->assertSame('viewed', $activity->activity_type->value);
        $this->assertSame('User viewed the post', $activity->description);
        $this->assertSame('127.0.0.1', $activity->data->get('ip'));
        $this->assertSame('web', $activity->metadata->get('source'));
    }

    public function test_log_attaches_the_activity_to_the_owner(): void
    {
        $activity = $this->service->log(
            owner: $this->post,
            type: 'created',
        );

        $this->assertSame(TestPost::class, $activity->owner_type);
        $this->assertSame((string) $this->post->id, $activity->owner_id);
    }

    public function test_log_creates_an_activity_without_data(): void
    {
        $activity = $this->service->log(
            owner: $this->post,
            type: 'updated',
        );

        $this->assertNull($activity->data);
        $this->assertNull($activity->metadata);
    }

    // ============================================================
    // GET FOR
    // ============================================================

    public function test_get_for_returns_activities_of_the_owner(): void
    {
        $otherPost = TestPost::create([
            'user_id' => $this->user->id,
            'title' => 'Other Post',
            'body' => 'Other content',
        ]);

        $this->service->log($this->post, 'viewed');
        $this->service->log($this->post, 'liked');
        $this->service->log($otherPost, 'viewed');

        $activities = $this->service->getFor($this->post);

        $this->assertCount(2, $activities);
    }

    public function test_get_for_returns_empty_collection_when_no_activity(): void
    {
        $activities = $this->service->getFor($this->post);

        $this->assertCount(0, $activities);
    }

    public function test_get_for_applies_limit(): void
    {
        $this->service->log($this->post, 'viewed');
        $this->service->log($this->post, 'liked');
        $this->service->log($this->post, 'shared');

        $activities = $this->service->getFor($this->post, 2);

        $this->assertCount(2, $activities);
    }

    // ============================================================
    // GET FOR BY TYPE
    // ============================================================

    public function test_get_for_by_type_returns_only_matching_activities(): void
    {
        $this->service->log($this->post, 'viewed');
        $this->service->log($this->post, 'liked');
        $this->service->log($this->post, 'liked');

        $activities = $this->service->getForByType($this->post, 'liked');

        $this->assertCount(2, $activities);
    }

    public function test_get_for_by_type_applies_limit(): void
    {
        $this->service->log($this->post, 'liked');
        $this->service->log($this->post, 'liked');
        $this->service->log($this->post, 'liked');

        $activities = $this->service->getForByType($this->post, 'liked', 1);

        $this->assertCount(1, $activities);
    }

    public function test_get_for_by_type_returns_empty_when_no_match(): void
    {
        $this->service->log($this->post, 'viewed');

        $activities = $this->service->getForByType($this->post, 'nonexistent');

        $this->assertCount(0, $activities);
    }

    // ============================================================
    // GET LATEST FOR
    // ============================================================

    public function test_get_latest_for_returns_the_most_recent_activity(): void
    {
        $older = $this->service->log($this->post, 'viewed');
        $older->forceFill(['created_at' => now()->subHour()])->save();

        $newer = $this->service->log($this->post, 'liked');

        $latest = $this->service->getLatestFor($this->post);

        $this->assertNotNull($latest);
        $this->assertSame($newer->id, $latest->id);
    }

    public function test_get_latest_for_returns_null_when_no_activity(): void
    {
        $this->assertNull($this->service->getLatestFor($this->post));
    }

    // ============================================================
    // COUNT FOR
    // ============================================================

    public function test_count_for_returns_the_number_of_activities(): void
    {
        $this->service->log($this->post, 'viewed');
        $this->service->log($this->post, 'liked');
        $this->service->log($this->post, 'shared');

        $this->assertSame(3, $this->service->countFor($this->post));
    }

    public function test_count_for_returns_zero_when_no_activity(): void
    {
        $this->assertSame(0, $this->service->countFor($this->post));
    }

    // ============================================================
    // COUNT FOR BY TYPE
    // ============================================================

    public function test_count_for_by_type_returns_the_number_of_matching_activities(): void
    {
        $this->service->log($this->post, 'viewed');
        $this->service->log($this->post, 'liked');
        $this->service->log($this->post, 'liked');

        $this->assertSame(2, $this->service->countForByType($this->post, 'liked'));
        $this->assertSame(1, $this->service->countForByType($this->post, 'viewed'));
        $this->assertSame(0, $this->service->countForByType($this->post, 'nonexistent'));
    }

    // ============================================================
    // HAS ACTIVITY OF TYPE
    // ============================================================

    public function test_has_activity_of_type_returns_true_when_present(): void
    {
        $this->service->log($this->post, 'payment_completed');

        $this->assertTrue(
            $this->service->hasActivityOfType($this->post, 'payment_completed'),
        );
    }

    public function test_has_activity_of_type_returns_false_when_absent(): void
    {
        $this->service->log($this->post, 'viewed');

        $this->assertFalse(
            $this->service->hasActivityOfType($this->post, 'payment_completed'),
        );
    }

    public function test_has_activity_of_type_returns_false_when_no_activity(): void
    {
        $this->assertFalse(
            $this->service->hasActivityOfType($this->post, 'viewed'),
        );
    }

    // ============================================================
    // CLEAR FOR
    // ============================================================

    public function test_clear_for_deletes_all_activities_of_the_owner(): void
    {
        $this->service->log($this->post, 'viewed');
        $this->service->log($this->post, 'liked');

        $deleted = $this->service->clearFor($this->post);

        $this->assertSame(2, $deleted);
        $this->assertSame(0, $this->service->countFor($this->post));
    }

    public function test_clear_for_returns_zero_when_no_activity(): void
    {
        $this->assertSame(0, $this->service->clearFor($this->post));
    }

    public function test_clear_for_does_not_delete_activities_of_other_owners(): void
    {
        $otherPost = TestPost::create([
            'user_id' => $this->user->id,
            'title' => 'Other Post',
            'body' => 'Other content',
        ]);

        $this->service->log($this->post, 'viewed');
        $this->service->log($otherPost, 'viewed');

        $this->service->clearFor($this->post);

        $this->assertSame(0, $this->service->countFor($this->post));
        $this->assertSame(1, $this->service->countFor($otherPost));
    }
}
