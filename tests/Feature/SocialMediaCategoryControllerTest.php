<?php

namespace Tests\Feature;

use App\Http\Middleware\BetterBeWillie;
use App\Models\Account;
use App\Models\SocialMediaCategory;
use App\Models\SocialMediaContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialMediaCategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;

    private User $user;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(BetterBeWillie::class);

        $this->account = Account::create([
            'name' => 'Test Account',
            'website' => 'https://test.com',
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

        $this->actingAs($this->user);
    }

    /** -------- INDEX -------- */
    public function test_index_returns_paginated_categories(): void
    {
        SocialMediaCategory::create(['name' => 'holidays']);
        SocialMediaCategory::create(['name' => 'trivia']);
        SocialMediaCategory::create(['name' => 'news']);

        $response = $this->getJson('/api/social_media_categories');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'contents_count', 'created_at', 'updated_at'],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_index_returns_empty_when_no_categories_exist(): void
    {
        $response = $this->getJson('/api/social_media_categories');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_index_sorts_by_name_by_default(): void
    {
        SocialMediaCategory::create(['name' => 'trivia']);
        SocialMediaCategory::create(['name' => 'holidays']);
        SocialMediaCategory::create(['name' => 'news']);

        $response = $this->getJson('/api/social_media_categories');

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'holidays')
            ->assertJsonPath('data.1.name', 'news')
            ->assertJsonPath('data.2.name', 'trivia');
    }

    public function test_index_filters_by_name(): void
    {
        SocialMediaCategory::create(['name' => 'holidays']);
        SocialMediaCategory::create(['name' => 'trivia']);

        $response = $this->getJson('/api/social_media_categories?filter[name]=holi');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'holidays');
    }

    public function test_index_includes_contents_count(): void
    {
        $category = SocialMediaCategory::create(['name' => 'holidays']);

        SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $category->id,
            'title' => 'Content 1',
            'content' => 'Body 1.',
        ]);

        SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $category->id,
            'title' => 'Content 2',
            'content' => 'Body 2.',
        ]);

        $response = $this->getJson('/api/social_media_categories');

        $response->assertOk()
            ->assertJsonPath('data.0.contents_count', 2);
    }

    public function test_index_respects_per_page_parameter(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            SocialMediaCategory::create(['name' => "Category {$i}"]);
        }

        $response = $this->getJson('/api/social_media_categories?per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 5);
    }

    /** -------- SHOW -------- */
    public function test_show_returns_a_single_category(): void
    {
        $category = SocialMediaCategory::create(['name' => 'holidays']);

        SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $category->id,
            'title' => 'Content 1',
            'content' => 'Body 1.',
        ]);

        $response = $this->getJson('/api/social_media_categories/'.$category->id);

        $response->assertOk()
            ->assertJsonPath('data.id', $category->id)
            ->assertJsonPath('data.name', 'holidays')
            ->assertJsonPath('data.contents_count', 1)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'contents_count', 'created_at', 'updated_at'],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_category(): void
    {
        $response = $this->getJson('/api/social_media_categories/9999');

        $response->assertNotFound();
    }

    /** -------- STORE -------- */
    public function test_store_creates_a_category_as_admin(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->postJson('/api/social_media_categories', ['name' => 'new_category']);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'new_category');

        $this->assertDatabaseHas('social_media_categories', ['name' => 'new_category']);
    }

    public function test_store_forbidden_for_non_admin(): void
    {
        $response = $this->postJson('/api/social_media_categories', ['name' => 'new_category']);

        $response->assertForbidden();

        $this->assertDatabaseMissing('social_media_categories', ['name' => 'new_category']);
    }

    public function test_store_fails_without_name(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->postJson('/api/social_media_categories', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_fails_with_duplicate_name(): void
    {
        $this->actingAs($this->adminUser);

        SocialMediaCategory::create(['name' => 'holidays']);

        $response = $this->postJson('/api/social_media_categories', ['name' => 'holidays']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_fails_with_name_exceeding_max_length(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->postJson('/api/social_media_categories', ['name' => str_repeat('a', 256)]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** -------- DESTROY -------- */
    public function test_destroy_deletes_a_category_as_admin(): void
    {
        $this->actingAs($this->adminUser);

        $category = SocialMediaCategory::create(['name' => 'to_delete']);

        $response = $this->deleteJson('/api/social_media_categories/'.$category->id);

        $response->assertOk()
            ->assertJsonPath('message', 'Category deleted successfully.');

        $this->assertDatabaseMissing('social_media_categories', ['id' => $category->id]);
    }

    public function test_destroy_forbidden_for_non_admin(): void
    {
        $category = SocialMediaCategory::create(['name' => 'protected']);

        $response = $this->deleteJson('/api/social_media_categories/'.$category->id);

        $response->assertForbidden();

        $this->assertDatabaseHas('social_media_categories', ['id' => $category->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_category(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->deleteJson('/api/social_media_categories/9999');

        $response->assertNotFound();
    }
}
