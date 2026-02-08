<?php

namespace Tests\Feature;

use App\Http\Middleware\BetterBeWillie;
use App\Models\Account;
use App\Models\SocialMediaCategory;
use App\Models\SocialMediaContent;
use App\Models\SocialMediaPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialMediaPostControllerTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;

    private Account $otherAccount;

    private User $user;

    private User $adminUser;

    private User $otherUser;

    private SocialMediaCategory $category;

    private SocialMediaContent $content;

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

        $this->category = SocialMediaCategory::create(['name' => 'holidays']);

        $this->content = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Test Content',
            'content' => 'Test body.',
        ]);
    }

    /** -------- INDEX -------- */
    public function test_index_returns_paginated_posts_for_account(): void
    {
        SocialMediaPost::create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $this->content->id,
            'posted_at' => now(),
        ]);

        $otherContent = SocialMediaContent::create([
            'account_id' => $this->otherAccount->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Other Content',
            'content' => 'Other body.',
        ]);

        SocialMediaPost::create([
            'account_id' => $this->otherAccount->id,
            'social_media_content_id' => $otherContent->id,
            'posted_at' => now(),
        ]);

        $response = $this->getJson('/api/social_media_posts');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'account_id', 'social_media_content_id', 'posted_at'],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_index_returns_empty_when_no_posts_exist(): void
    {
        $response = $this->getJson('/api/social_media_posts');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_index_filters_by_content_id(): void
    {
        $secondContent = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Second Content',
            'content' => 'Second body.',
        ]);

        SocialMediaPost::create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $this->content->id,
            'posted_at' => now(),
        ]);

        SocialMediaPost::create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $secondContent->id,
            'posted_at' => now(),
        ]);

        $response = $this->getJson('/api/social_media_posts?filter[content]='.$this->content->id);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.social_media_content_id', $this->content->id);
    }

    public function test_index_sorts_by_posted_at_descending_by_default(): void
    {
        $olderPost = SocialMediaPost::create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $this->content->id,
            'posted_at' => now()->subDays(2),
        ]);

        $newerPost = SocialMediaPost::create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $this->content->id,
            'posted_at' => now(),
        ]);

        $response = $this->getJson('/api/social_media_posts');

        $response->assertOk()
            ->assertJsonPath('data.0.id', $newerPost->id)
            ->assertJsonPath('data.1.id', $olderPost->id);
    }

    public function test_index_respects_per_page_parameter(): void
    {
        for ($i = 0; $i < 5; $i++) {
            SocialMediaPost::create([
                'account_id' => $this->account->id,
                'social_media_content_id' => $this->content->id,
                'posted_at' => now()->subDays($i),
            ]);
        }

        $response = $this->getJson('/api/social_media_posts?per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 5);
    }

    /** -------- SHOW -------- */
    public function test_show_returns_a_single_post_for_account(): void
    {
        $post = SocialMediaPost::create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $this->content->id,
            'posted_at' => '2026-02-07 12:00:00',
        ]);

        $response = $this->getJson('/api/social_media_posts/'.$post->id);

        $response->assertOk()
            ->assertJsonPath('data.id', $post->id)
            ->assertJsonPath('data.account_id', $this->account->id)
            ->assertJsonPath('data.social_media_content_id', $this->content->id)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'account_id',
                    'social_media_content_id',
                    'posted_at',
                    'content' => ['id', 'title', 'content'],
                ],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_post(): void
    {
        $response = $this->getJson('/api/social_media_posts/9999');

        $response->assertNotFound();
    }

    public function test_show_forbidden_for_post_belonging_to_another_account(): void
    {
        $otherContent = SocialMediaContent::create([
            'account_id' => $this->otherAccount->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Other Content',
            'content' => 'Other body.',
        ]);

        $otherPost = SocialMediaPost::create([
            'account_id' => $this->otherAccount->id,
            'social_media_content_id' => $otherContent->id,
            'posted_at' => now(),
        ]);

        $response = $this->getJson('/api/social_media_posts/'.$otherPost->id);

        $response->assertForbidden();
    }

    public function test_show_allowed_for_admin_viewing_another_accounts_post(): void
    {
        $this->actingAs($this->adminUser);

        $otherContent = SocialMediaContent::create([
            'account_id' => $this->otherAccount->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Other Content',
            'content' => 'Other body.',
        ]);

        $otherPost = SocialMediaPost::create([
            'account_id' => $this->otherAccount->id,
            'social_media_content_id' => $otherContent->id,
            'posted_at' => now(),
        ]);

        $response = $this->getJson('/api/social_media_posts/'.$otherPost->id);

        $response->assertOk()
            ->assertJsonPath('data.id', $otherPost->id);
    }

    /** -------- STORE -------- */
    public function test_store_creates_a_post(): void
    {
        $response = $this->postJson('/api/social_media_posts', [
            'social_media_content_id' => $this->content->id,
            'posted_at' => '2026-02-07 12:00:00',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.social_media_content_id', $this->content->id)
            ->assertJsonPath('data.account_id', $this->account->id);

        $this->assertDatabaseHas('social_media_posts', [
            'account_id' => $this->account->id,
            'social_media_content_id' => $this->content->id,
        ]);
    }

    public function test_store_defaults_posted_at_to_now_when_omitted(): void
    {
        $response = $this->postJson('/api/social_media_posts', [
            'social_media_content_id' => $this->content->id,
        ]);

        $response->assertStatus(201);

        $this->assertNotNull($response->json('data.posted_at'));
    }

    public function test_store_fails_without_content_id(): void
    {
        $response = $this->postJson('/api/social_media_posts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['social_media_content_id']);
    }

    public function test_store_fails_with_nonexistent_content(): void
    {
        $response = $this->postJson('/api/social_media_posts', [
            'social_media_content_id' => 9999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['social_media_content_id']);
    }

    public function test_store_fails_with_invalid_date(): void
    {
        $response = $this->postJson('/api/social_media_posts', [
            'social_media_content_id' => $this->content->id,
            'posted_at' => 'not-a-date',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['posted_at']);
    }

    /** -------- DESTROY -------- */
    public function test_destroy_deletes_post_belonging_to_account(): void
    {
        $post = SocialMediaPost::create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $this->content->id,
            'posted_at' => now(),
        ]);

        $response = $this->deleteJson('/api/social_media_posts/'.$post->id);

        $response->assertOk()
            ->assertJsonPath('message', 'Post deleted successfully.');

        $this->assertDatabaseMissing('social_media_posts', ['id' => $post->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_post(): void
    {
        $response = $this->deleteJson('/api/social_media_posts/9999');

        $response->assertNotFound();
    }

    public function test_destroy_forbidden_for_post_belonging_to_another_account(): void
    {
        $otherContent = SocialMediaContent::create([
            'account_id' => $this->otherAccount->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Other Content',
            'content' => 'Other body.',
        ]);

        $otherPost = SocialMediaPost::create([
            'account_id' => $this->otherAccount->id,
            'social_media_content_id' => $otherContent->id,
            'posted_at' => now(),
        ]);

        $response = $this->deleteJson('/api/social_media_posts/'.$otherPost->id);

        $response->assertForbidden();

        $this->assertDatabaseHas('social_media_posts', ['id' => $otherPost->id]);
    }

    public function test_destroy_allowed_for_admin_on_another_accounts_post(): void
    {
        $this->actingAs($this->adminUser);

        $otherContent = SocialMediaContent::create([
            'account_id' => $this->otherAccount->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Other Content',
            'content' => 'Other body.',
        ]);

        $otherPost = SocialMediaPost::create([
            'account_id' => $this->otherAccount->id,
            'social_media_content_id' => $otherContent->id,
            'posted_at' => now(),
        ]);

        $response = $this->deleteJson('/api/social_media_posts/'.$otherPost->id);

        $response->assertOk();

        $this->assertDatabaseMissing('social_media_posts', ['id' => $otherPost->id]);
    }
}
