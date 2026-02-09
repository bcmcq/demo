<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\SocialMediaCategory;
use App\Models\SocialMediaContent;
use App\Models\SocialMediaPost;
use App\Models\SocialMediaSchedule;
use App\Models\User;

class SocialMediaContentControllerTest extends BaseTestCase
{
    private Account $otherAccount;

    private User $adminUser;

    private User $otherUser;

    private SocialMediaCategory $category;

    private SocialMediaCategory $secondCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->otherAccount = $this->createOtherAccount();
        $this->adminUser = $this->createAdminUser();
        $this->otherUser = $this->createUserForAccount($this->otherAccount);

        $this->category = SocialMediaCategory::factory()->create(['name' => 'holidays']);
        $this->secondCategory = SocialMediaCategory::factory()->create(['name' => 'trivia']);
    }

    /** -------- INDEX -------- */
    public function test_index_returns_paginated_content_for_account(): void
    {
        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Test Content',
            'content' => 'Test body text.',
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->otherAccount->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Other Account Content',
            'content' => 'Should not appear.',
        ]);

        $response = $this->getJson('/api/social_media_contents');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Test Content')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'account_id',
                        'title',
                        'content',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_index_returns_empty_when_no_content_exists(): void
    {
        $response = $this->getJson('/api/social_media_contents');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_index_filters_by_posts_true(): void
    {
        $postedContent = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Posted Content',
            'content' => 'This was posted.',
        ]);

        SocialMediaPost::factory()->create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $postedContent->id,
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Unposted Content',
            'content' => 'Never posted.',
        ]);

        $response = $this->getJson('/api/social_media_contents?filter[posts]=true');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Posted Content');
    }

    public function test_index_filters_by_posts_false(): void
    {
        $postedContent = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Posted Content',
            'content' => 'This was posted.',
        ]);

        SocialMediaPost::factory()->create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $postedContent->id,
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Unposted Content',
            'content' => 'Never posted.',
        ]);

        $response = $this->getJson('/api/social_media_contents?filter[posts]=false');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Unposted Content');
    }

    public function test_index_filters_by_schedules_true(): void
    {
        $scheduledContent = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Scheduled Content',
            'content' => 'This is scheduled.',
        ]);

        SocialMediaSchedule::factory()->create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $scheduledContent->id,
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Unscheduled Content',
            'content' => 'Not scheduled.',
        ]);

        $response = $this->getJson('/api/social_media_contents?filter[schedules]=true');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Scheduled Content');
    }

    public function test_index_filters_by_schedules_false(): void
    {
        $scheduledContent = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Scheduled Content',
            'content' => 'This is scheduled.',
        ]);

        SocialMediaSchedule::factory()->create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $scheduledContent->id,
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Unscheduled Content',
            'content' => 'Not scheduled.',
        ]);

        $response = $this->getJson('/api/social_media_contents?filter[schedules]=false');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Unscheduled Content');
    }

    public function test_index_filters_by_single_category(): void
    {
        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Holiday Content',
            'content' => 'Holidays are fun.',
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->secondCategory->id,
            'title' => 'Trivia Content',
            'content' => 'Did you know?',
        ]);

        $response = $this->getJson('/api/social_media_contents?filter[category]='.$this->category->id);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Holiday Content');
    }

    public function test_index_filters_by_multiple_categories(): void
    {
        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Holiday Content',
            'content' => 'Holidays are fun.',
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->secondCategory->id,
            'title' => 'Trivia Content',
            'content' => 'Did you know?',
        ]);

        $response = $this->getJson(
            '/api/social_media_contents?filter[category]='.$this->category->id.','.$this->secondCategory->id
        );

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_index_combines_posts_and_category_filters(): void
    {
        $postedHoliday = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Posted Holiday',
            'content' => 'Posted holiday content.',
        ]);

        SocialMediaPost::factory()->create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $postedHoliday->id,
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Unposted Holiday',
            'content' => 'Not posted yet.',
        ]);

        $postedTrivia = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->secondCategory->id,
            'title' => 'Posted Trivia',
            'content' => 'Posted trivia content.',
        ]);

        SocialMediaPost::factory()->create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $postedTrivia->id,
        ]);

        $response = $this->getJson(
            '/api/social_media_contents?filter[posts]=true&filter[category]='.$this->category->id
        );

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Posted Holiday');
    }

    public function test_index_respects_per_page_parameter(): void
    {
        SocialMediaContent::factory()->count(5)->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
        ]);

        $response = $this->getJson('/api/social_media_contents?per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 5);
    }

    /** -------- SHOW -------- */
    public function test_show_returns_a_single_content_for_account(): void
    {
        $content = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Show Me',
            'content' => 'Detailed body.',
        ]);

        $response = $this->getJson('/api/social_media_contents/'.$content->id);

        $response->assertOk()
            ->assertJsonPath('data.id', $content->id)
            ->assertJsonPath('data.title', 'Show Me')
            ->assertJsonPath('data.content', 'Detailed body.')
            ->assertJsonPath('data.account_id', $this->account->id)
            ->assertJsonPath('data.category.id', $this->category->id)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'account_id',
                    'title',
                    'content',
                    'category' => ['id', 'name'],
                    'posts',
                    'schedules',
                    'media',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_content(): void
    {
        $response = $this->getJson('/api/social_media_contents/9999');

        $response->assertNotFound();
    }

    public function test_show_forbidden_for_content_belonging_to_another_account(): void
    {
        $otherContent = SocialMediaContent::factory()->create([
            'account_id' => $this->otherAccount->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Other Account Content',
            'content' => 'Should not be visible.',
        ]);

        $response = $this->getJson('/api/social_media_contents/'.$otherContent->id);

        $response->assertForbidden();
    }

    /** -------- STORE -------- */
    public function test_store_creates_content_for_authenticated_account(): void
    {
        $payload = [
            'social_media_category_id' => $this->category->id,
            'title' => 'New Content',
            'content' => 'This is brand new content.',
        ];

        $response = $this->postJson('/api/social_media_contents', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'New Content')
            ->assertJsonPath('data.content', 'This is brand new content.')
            ->assertJsonPath('data.account_id', $this->account->id)
            ->assertJsonPath('data.category.id', $this->category->id)
            ->assertJsonPath('data.category.name', 'holidays')
            ->assertJsonPath('data.posts', [])
            ->assertJsonPath('data.schedules', []);

        $this->assertDatabaseHas('social_media_contents', [
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'New Content',
        ]);
    }

    public function test_store_fails_without_title(): void
    {
        $payload = [
            'social_media_category_id' => $this->category->id,
            'content' => 'Missing a title.',
        ];

        $response = $this->postJson('/api/social_media_contents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_store_fails_without_content(): void
    {
        $payload = [
            'social_media_category_id' => $this->category->id,
            'title' => 'Missing Content',
        ];

        $response = $this->postJson('/api/social_media_contents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_store_fails_without_category(): void
    {
        $payload = [
            'title' => 'No Category',
            'content' => 'Missing a category.',
        ];

        $response = $this->postJson('/api/social_media_contents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['social_media_category_id']);
    }

    public function test_store_fails_with_nonexistent_category(): void
    {
        $payload = [
            'social_media_category_id' => 9999,
            'title' => 'Bad Category',
            'content' => 'Category does not exist.',
        ];

        $response = $this->postJson('/api/social_media_contents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['social_media_category_id']);
    }

    public function test_store_fails_with_empty_payload(): void
    {
        $response = $this->postJson('/api/social_media_contents', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['social_media_category_id', 'title', 'content']);
    }

    public function test_store_fails_with_title_exceeding_max_length(): void
    {
        $payload = [
            'social_media_category_id' => $this->category->id,
            'title' => str_repeat('a', 256),
            'content' => 'Valid content.',
        ];

        $response = $this->postJson('/api/social_media_contents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /** -------- UPDATE -------- */
    public function test_update_modifies_content_belonging_to_account(): void
    {
        $content = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Original Title',
            'content' => 'Original body text.',
        ]);

        $payload = [
            'title' => 'Updated Title',
            'content' => 'Updated body text.',
        ];

        $response = $this->putJson('/api/social_media_contents/'.$content->id, $payload);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.content', 'Updated body text.')
            ->assertJsonPath('data.account_id', $this->account->id)
            ->assertJsonPath('data.category.id', $this->category->id)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'account_id',
                    'title',
                    'content',
                    'category' => ['id', 'name'],
                    'posts',
                    'schedules',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('social_media_contents', [
            'id' => $content->id,
            'title' => 'Updated Title',
            'content' => 'Updated body text.',
        ]);
    }

    public function test_update_can_change_category(): void
    {
        $content = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Category Change',
            'content' => 'Testing category update.',
        ]);

        $payload = [
            'social_media_category_id' => $this->secondCategory->id,
        ];

        $response = $this->putJson('/api/social_media_contents/'.$content->id, $payload);

        $response->assertOk()
            ->assertJsonPath('data.category.id', $this->secondCategory->id)
            ->assertJsonPath('data.category.name', 'trivia')
            ->assertJsonPath('data.title', 'Category Change');

        $this->assertDatabaseHas('social_media_contents', [
            'id' => $content->id,
            'social_media_category_id' => $this->secondCategory->id,
        ]);
    }

    public function test_update_allows_partial_update(): void
    {
        $content = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Partial Update',
            'content' => 'Original content stays.',
        ]);

        $payload = [
            'title' => 'Only Title Changed',
        ];

        $response = $this->putJson('/api/social_media_contents/'.$content->id, $payload);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Only Title Changed')
            ->assertJsonPath('data.content', 'Original content stays.');
    }

    public function test_update_returns_404_for_nonexistent_content(): void
    {
        $response = $this->putJson('/api/social_media_contents/9999', [
            'title' => 'Does Not Exist',
        ]);

        $response->assertNotFound();
    }

    public function test_update_forbidden_for_content_belonging_to_another_account(): void
    {
        $otherContent = SocialMediaContent::factory()->create([
            'account_id' => $this->otherAccount->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Other Account Content',
            'content' => 'Should not be updatable.',
        ]);

        $response = $this->putJson('/api/social_media_contents/'.$otherContent->id, [
            'title' => 'Hacked Title',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('social_media_contents', [
            'id' => $otherContent->id,
            'title' => 'Other Account Content',
        ]);
    }

    public function test_update_fails_with_nonexistent_category(): void
    {
        $content = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Bad Category Update',
            'content' => 'Testing invalid category.',
        ]);

        $response = $this->putJson('/api/social_media_contents/'.$content->id, [
            'social_media_category_id' => 9999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['social_media_category_id']);
    }

    public function test_update_fails_with_title_exceeding_max_length(): void
    {
        $content = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Max Length Test',
            'content' => 'Testing title max length.',
        ]);

        $response = $this->putJson('/api/social_media_contents/'.$content->id, [
            'title' => str_repeat('a', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_update_with_empty_payload_returns_unchanged_content(): void
    {
        $content = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Unchanged',
            'content' => 'Nothing changes.',
        ]);

        $response = $this->putJson('/api/social_media_contents/'.$content->id, []);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Unchanged')
            ->assertJsonPath('data.content', 'Nothing changes.');
    }

    /** -------- DESTROY -------- */
    public function test_destroy_deletes_content_belonging_to_account(): void
    {
        $content = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'To Be Deleted',
            'content' => 'This will be deleted.',
        ]);

        $response = $this->deleteJson('/api/social_media_contents/'.$content->id);

        $response->assertOk()
            ->assertJsonPath('message', 'Content deleted successfully.');

        $this->assertDatabaseMissing('social_media_contents', [
            'id' => $content->id,
        ]);
    }

    public function test_destroy_returns_404_for_nonexistent_content(): void
    {
        $response = $this->deleteJson('/api/social_media_contents/9999');

        $response->assertNotFound();
    }

    public function test_destroy_forbidden_for_content_belonging_to_another_account(): void
    {
        $otherContent = SocialMediaContent::factory()->create([
            'account_id' => $this->otherAccount->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Other Account Content',
            'content' => 'Should not be deletable.',
        ]);

        $response = $this->deleteJson('/api/social_media_contents/'.$otherContent->id);

        $response->assertForbidden();

        $this->assertDatabaseHas('social_media_contents', [
            'id' => $otherContent->id,
        ]);
    }
}
