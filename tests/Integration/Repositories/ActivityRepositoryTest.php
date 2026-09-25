<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Tests\Integration\Repositories;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelActivity\Models\Activity;
use AndyDefer\LaravelActivity\Records\ActivityFilterRecord;
use AndyDefer\LaravelActivity\Records\ActivityRecord;
use AndyDefer\LaravelActivity\Repositories\ActivityRepository;
use AndyDefer\LaravelActivity\Tests\Fixtures\Models\TestPost;
use AndyDefer\LaravelActivity\Tests\Fixtures\Models\TestUser;
use AndyDefer\LaravelActivity\Tests\IntegrationTestCase;
use AndyDefer\PhpVo\ValueObjects\DateTimeZuluVO;
use AndyDefer\Repository\Records\FindByRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class ActivityRepositoryTest extends IntegrationTestCase
{
    use RefreshDatabase;

    private ActivityRepository $repository;

    private TestUser $user;

    private TestPost $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new ActivityRepository;

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

    private function makeRecord(
        string $type = 'viewed',
        ?array $data = null,
    ): ActivityRecord {
        return ActivityRecord::from([
            'owner_type' => $this->post->getMorphClass(),
            'owner_id' => (string) $this->post->id,
            'activity_type' => $type,
            'description' => 'Viewed the post',
            'data' => $data !== null ? new StrictDataObject($data) : null,
        ]);
    }

    // ============================================================
    // CREATE / READ
    // ============================================================

    public function test_create_persists_an_activity(): void
    {
        $record = $this->makeRecord();

        $activity = $this->repository->create($record);

        $this->assertInstanceOf(Activity::class, $activity);
        $this->assertDatabaseHas('activities', [
            'id' => $activity->id,
            'owner_type' => $this->post->getMorphClass(),
            'owner_id' => (string) $this->post->id,
            'activity_type' => 'viewed',
        ]);
    }

    public function test_find_returns_activity_by_id(): void
    {
        $created = $this->repository->create($this->makeRecord());

        $found = $this->repository->find($created->id);

        $this->assertNotNull($found);
        $this->assertSame($created->id, $found->id);
    }

    // ============================================================
    // FILTERS
    // ============================================================

    public function test_filters_by_owner(): void
    {
        $otherPost = TestPost::create([
            'user_id' => $this->user->id,
            'title' => 'Other Post',
            'body' => 'Other content',
        ]);

        $this->repository->create($this->makeRecord());

        $this->repository->create(ActivityRecord::from([
            'owner_type' => $otherPost->getMorphClass(),
            'owner_id' => (string) $otherPost->id,
            'activity_type' => 'viewed',
        ]));

        $filter = ActivityFilterRecord::from([
            'owner_type' => $this->post->getMorphClass(),
            'owner_id' => (string) $this->post->id,
        ]);

        $results = $this->repository->findBy(new FindByRecord(filters: $filter));

        $this->assertCount(1, $results);
        $this->assertSame((string) $this->post->id, $results->first()->owner_id);
    }

    public function test_filters_by_activity_type(): void
    {
        $this->repository->create($this->makeRecord(type: 'viewed'));
        $this->repository->create($this->makeRecord(type: 'liked'));

        $filter = ActivityFilterRecord::from([
            'activity_type' => 'liked',
        ]);

        $results = $this->repository->findBy(new FindByRecord(filters: $filter));

        $this->assertCount(1, $results);
        $this->assertSame('liked', $results->first()->activity_type->value);
    }

    public function test_filters_by_date_range(): void
    {
        $old = $this->repository->create($this->makeRecord());
        $old->forceFill(['created_at' => now()->subDays(10)])->save();

        $recent = $this->repository->create($this->makeRecord());

        $filter = ActivityFilterRecord::from([
            'from' => DateTimeZuluVO::from(now()->subDays(1)->toIso8601ZuluString()),
            'to' => DateTimeZuluVO::from(now()->addDay()->toIso8601ZuluString()),
        ]);

        $results = $this->repository->findBy(new FindByRecord(filters: $filter));

        $this->assertCount(1, $results);
        $this->assertSame($recent->id, $results->first()->id);
    }

    // ============================================================
    // CUSTOM METHODS
    // ============================================================

    public function test_get_latest_for_returns_the_most_recent_activity(): void
    {
        $older = $this->repository->create($this->makeRecord());
        $older->forceFill(['created_at' => now()->subHour()])->save();

        $newer = $this->repository->create($this->makeRecord());

        $latest = $this->repository->getLatestFor($this->post);

        $this->assertNotNull($latest);
        $this->assertSame($newer->id, $latest->id);
    }

    public function test_get_latest_for_returns_null_when_no_activity(): void
    {
        $this->assertNull($this->repository->getLatestFor($this->post));
    }

    public function test_get_for_returns_all_activities_of_the_owner(): void
    {
        $this->repository->create($this->makeRecord());
        $this->repository->create($this->makeRecord());
        $this->repository->create($this->makeRecord());

        $results = $this->repository->getFor($this->post);

        $this->assertCount(3, $results);
    }

    public function test_get_for_applies_limit(): void
    {
        $this->repository->create($this->makeRecord());
        $this->repository->create($this->makeRecord());
        $this->repository->create($this->makeRecord());

        $results = $this->repository->getFor($this->post, 2);

        $this->assertCount(2, $results);
    }

    public function test_get_for_by_type_returns_only_matching_activities(): void
    {
        $this->repository->create($this->makeRecord(type: 'viewed'));
        $this->repository->create($this->makeRecord(type: 'liked'));
        $this->repository->create($this->makeRecord(type: 'liked'));

        $results = $this->repository->getForByType($this->post, 'liked');

        $this->assertCount(2, $results);
    }

    public function test_get_for_by_type_applies_limit(): void
    {
        $this->repository->create($this->makeRecord(type: 'liked'));
        $this->repository->create($this->makeRecord(type: 'liked'));
        $this->repository->create($this->makeRecord(type: 'liked'));

        $results = $this->repository->getForByType($this->post, 'liked', 1);

        $this->assertCount(1, $results);
    }

    public function test_get_for_by_type_returns_empty_when_no_match(): void
    {
        $this->repository->create($this->makeRecord(type: 'viewed'));

        $results = $this->repository->getForByType($this->post, 'nonexistent');

        $this->assertCount(0, $results);
    }

    public function test_count_for_returns_the_number_of_activities(): void
    {
        $this->repository->create($this->makeRecord());
        $this->repository->create($this->makeRecord());
        $this->repository->create($this->makeRecord());

        $this->assertSame(3, $this->repository->countFor($this->post));
    }

    public function test_count_for_returns_zero_when_no_activity(): void
    {
        $this->assertSame(0, $this->repository->countFor($this->post));
    }

    public function test_count_for_by_type_returns_the_number_of_matching_activities(): void
    {
        $this->repository->create($this->makeRecord(type: 'viewed'));
        $this->repository->create($this->makeRecord(type: 'liked'));
        $this->repository->create($this->makeRecord(type: 'liked'));

        $this->assertSame(2, $this->repository->countForByType($this->post, 'liked'));
        $this->assertSame(1, $this->repository->countForByType($this->post, 'viewed'));
        $this->assertSame(0, $this->repository->countForByType($this->post, 'nonexistent'));
    }
}
