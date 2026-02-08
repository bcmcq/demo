<?php

namespace Tests\Feature;

use App\Http\Middleware\BetterBeWillie;
use App\Models\Account;
use App\Models\SocialMediaCategory;
use App\Models\SocialMediaContent;
use App\Models\SocialMediaPost;
use App\Models\SocialMediaSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialMediaContentControllerTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;

    private Account $otherAccount;

    private User $user;

    private User $adminUser;

    private User $otherUser;

    private SocialMediaCategory $category;

    private SocialMediaCategory $secondCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(BetterBeWillie::class);

        $this->account = Account::create([
            'name' => 'Test Account',
            'website' => 'https://test.com',
        ]);

        $this->otherAccount = Account::create([
            'name' => 'Other Account',
            'website' => 'https://other.com',
        ]);

        $this->user = User::forceCreate([
            'name' => 'Willie Dustice',
            'email' => 'willie@test.com',
            'password' => bcrypt('password'),
            'account_id' => $this->account->id,
            'is_admin' => false,
        ]);

        $this->adminUser = User::forceCreate([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'account_id' => $this->account->id,
            'is_admin' => true,
        ]);

        $this->otherUser = User::forceCreate([
            'name' => 'Other User',
            'email' => 'other@test.com',
            'password' => bcrypt('password'),
            'account_id' => $this->otherAccount->id,
            'is_admin' => false,
        ]);

        $this->actingAs($this->user);

        $this->category = SocialMediaCategory::create([
            'name' => 'holidays',
        ]);

        $this->secondCategory = SocialMediaCategory::create([
            'name' => 'trivia',
        ]);
    }

    /** -------- INDEX -------- */
    public function test_index_returns_paginated_content_for_account(): void
    {
        SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Test Content',
            'content' => 'Test body text.',
        ]);

        SocialMediaContent::create([
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
                        'category' => ['id', 'name'],
                        'is_posted',
                        'is_scheduled',
                        'posts_count',
                        'schedules_count',
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
        $postedContent = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Posted Content',
            'content' => 'This was posted.',
        ]);

        SocialMediaPost::create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $postedContent->id,
            'posted_at' => now(),
        ]);

        SocialMediaContent::create([
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
        $postedContent = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Posted Content',
            'content' => 'This was posted.',
        ]);

        SocialMediaPost::create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $postedContent->id,
            'posted_at' => now(),
        ]);

        SocialMediaContent::create([
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
        $scheduledContent = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Scheduled Content',
            'content' => 'This is scheduled.',
        ]);

        SocialMediaSchedule::create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $scheduledContent->id,
            'scheduled_at' => now()->addDay(),
        ]);

        SocialMediaContent::create([
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
        $scheduledContent = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Scheduled Content',
            'content' => 'This is scheduled.',
        ]);

        SocialMediaSchedule::create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $scheduledContent->id,
            'scheduled_at' => now()->addDay(),
        ]);

        SocialMediaContent::create([
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
        SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Holiday Content',
            'content' => 'Holidays are fun.',
        ]);

        SocialMediaContent::create([
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
        SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Holiday Content',
            'content' => 'Holidays are fun.',
        ]);

        SocialMediaContent::create([
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
        $postedHoliday = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Posted Holiday',
            'content' => 'Posted holiday content.',
        ]);

        SocialMediaPost::create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $postedHoliday->id,
            'posted_at' => now(),
        ]);

        SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Unposted Holiday',
            'content' => 'Not posted yet.',
        ]);

        $postedTrivia = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->secondCategory->id,
            'title' => 'Posted Trivia',
            'content' => 'Posted trivia content.',
        ]);

        SocialMediaPost::create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $postedTrivia->id,
            'posted_at' => now(),
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
        for ($i = 1; $i <= 5; $i++) {
            SocialMediaContent::create([
                'account_id' => $this->account->id,
                'social_media_category_id' => $this->category->id,
                'title' => "Content {$i}",
                'content' => "Body {$i}.",
            ]);
        }

        $response = $this->getJson('/api/social_media_contents?per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 5);
    }

    /** -------- SHOW -------- */
    public function test_show_returns_a_single_content_for_account(): void
    {
        $content = SocialMediaContent::create([
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
                    'is_posted',
                    'is_scheduled',
                    'posts_count',
                    'schedules_count',
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
        $otherContent = SocialMediaContent::create([
            'account_id' => $this->otherAccount->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Other Account Content',
            'content' => 'Should not be visible.',
        ]);

        $response = $this->getJson('/api/social_media_contents/'.$otherContent->id);

        $response->assertForbidden();
    }

    public function test_show_allowed_for_admin_viewing_another_accounts_content(): void
    {
        $this->actingAs($this->adminUser);

        $otherContent = SocialMediaContent::create([
            'account_id' => $this->otherAccount->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Other Account Content',
            'content' => 'Admin can see this.',
        ]);

        $response = $this->getJson('/api/social_media_contents/'.$otherContent->id);

        $response->assertOk()
            ->assertJsonPath('data.id', $otherContent->id);
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
            ->assertJsonPath('data.is_posted', false)
            ->assertJsonPath('data.is_scheduled', false);

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

    /** -------- DESTROY -------- */
    public function test_destroy_deletes_content_belonging_to_account(): void
    {
        $content = SocialMediaContent::create([
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
        $otherContent = SocialMediaContent::create([
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

    public function test_destroy_allowed_for_admin_on_another_accounts_content(): void
    {
        $this->actingAs($this->adminUser);

        $otherContent = SocialMediaContent::create([
            'account_id' => $this->otherAccount->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Admin Deletable',
            'content' => 'Admin can delete this.',
        ]);

        $response = $this->deleteJson('/api/social_media_contents/'.$otherContent->id);

        $response->assertOk();

        $this->assertDatabaseMissing('social_media_contents', [
            'id' => $otherContent->id,
        ]);
    }
}
