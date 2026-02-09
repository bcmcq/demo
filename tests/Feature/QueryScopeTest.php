<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\SocialMediaCategory;
use App\Models\SocialMediaContent;
use App\Models\SocialMediaPost;
use App\Models\SocialMediaSchedule;

class QueryScopeTest extends BaseTestCase
{
    private Account $otherAccount;

    private SocialMediaCategory $holidays;

    private SocialMediaCategory $trivia;

    protected function setUp(): void
    {
        parent::setUp();

        $this->otherAccount = $this->createOtherAccount();

        $this->holidays = SocialMediaCategory::factory()->create(['name' => 'holidays']);
        $this->trivia = SocialMediaCategory::factory()->create(['name' => 'trivia']);
    }

    public function test_for_account_scope_filters_by_account(): void
    {
        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'My Content',
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->otherAccount->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Other Content',
        ]);

        $results = SocialMediaContent::query()->forAccount($this->account->id)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('My Content', $results->first()->title);
    }

    public function test_for_category_scope_filters_by_category(): void
    {
        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Holiday Content',
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->trivia->id,
            'title' => 'Trivia Content',
        ]);

        $results = SocialMediaContent::query()->forCategory($this->holidays->id)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Holiday Content', $results->first()->title);
    }

    public function test_posted_scope_returns_only_posted_content(): void
    {
        $posted = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Posted',
        ]);

        SocialMediaPost::factory()->create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $posted->id,
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Not Posted',
        ]);

        $results = SocialMediaContent::query()->posted()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Posted', $results->first()->title);
    }

    public function test_unposted_scope_returns_only_unposted_content(): void
    {
        $posted = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Posted',
        ]);

        SocialMediaPost::factory()->create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $posted->id,
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Not Posted',
        ]);

        $results = SocialMediaContent::query()->unposted()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Not Posted', $results->first()->title);
    }

    public function test_scheduled_scope_returns_only_scheduled_content(): void
    {
        $scheduled = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Scheduled',
        ]);

        SocialMediaSchedule::factory()->create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $scheduled->id,
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Not Scheduled',
        ]);

        $results = SocialMediaContent::query()->scheduled()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Scheduled', $results->first()->title);
    }

    public function test_unscheduled_scope_returns_only_unscheduled_content(): void
    {
        $scheduled = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Scheduled',
        ]);

        SocialMediaSchedule::factory()->create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $scheduled->id,
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Not Scheduled',
        ]);

        $results = SocialMediaContent::query()->unscheduled()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Not Scheduled', $results->first()->title);
    }

    public function test_available_scope_excludes_posted_and_scheduled(): void
    {
        $posted = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Posted',
        ]);

        SocialMediaPost::factory()->create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $posted->id,
        ]);

        $scheduled = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Scheduled',
        ]);

        SocialMediaSchedule::factory()->create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $scheduled->id,
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Available',
        ]);

        $results = SocialMediaContent::query()->available()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Available', $results->first()->title);
    }

    public function test_scopes_can_be_chained(): void
    {
        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'My Holiday Available',
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->trivia->id,
            'title' => 'My Trivia Available',
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->otherAccount->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Other Holiday',
        ]);

        $posted = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'My Posted Holiday',
        ]);

        SocialMediaPost::factory()->create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $posted->id,
        ]);

        $results = SocialMediaContent::query()
            ->forAccount($this->account->id)
            ->forCategory($this->holidays->id)
            ->available()
            ->get();

        $this->assertCount(1, $results);
        $this->assertEquals('My Holiday Available', $results->first()->title);
    }
}
