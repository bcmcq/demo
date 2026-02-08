<?php

namespace Tests\Feature;

use App\Http\Middleware\BetterBeWillie;
use App\Models\Account;
use App\Models\SocialMediaAccountCategoryWeight;
use App\Models\SocialMediaCategory;
use App\Models\SocialMediaContent;
use App\Models\SocialMediaPost;
use App\Models\SocialMediaSchedule;
use App\Models\User;
use App\Services\AutopostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutopostTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;

    private User $user;

    private SocialMediaCategory $holidays;

    private SocialMediaCategory $trivia;

    private SocialMediaCategory $news;

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

        $this->actingAs($this->user);

        $this->holidays = SocialMediaCategory::create(['name' => 'holidays']);
        $this->trivia = SocialMediaCategory::create(['name' => 'trivia']);
        $this->news = SocialMediaCategory::create(['name' => 'news']);
    }

    public function test_autopost_returns_content_from_weighted_category(): void
    {
        SocialMediaAccountCategoryWeight::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'weight' => 10,
        ]);

        $content = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Holiday Post',
            'content' => 'Happy holidays!',
        ]);

        $response = $this->getJson('/api/social_media_contents/autopost');

        $response->assertOk()
            ->assertJsonPath('data.id', $content->id)
            ->assertJsonPath('data.title', 'Holiday Post')
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

    public function test_autopost_excludes_posted_content(): void
    {
        SocialMediaAccountCategoryWeight::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'weight' => 10,
        ]);

        $postedContent = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Already Posted',
            'content' => 'This was posted.',
        ]);

        SocialMediaPost::create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $postedContent->id,
            'posted_at' => now(),
        ]);

        $availableContent = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Not Yet Posted',
            'content' => 'Fresh content.',
        ]);

        $response = $this->getJson('/api/social_media_contents/autopost');

        $response->assertOk()
            ->assertJsonPath('data.id', $availableContent->id)
            ->assertJsonPath('data.title', 'Not Yet Posted');
    }

    public function test_autopost_excludes_scheduled_content(): void
    {
        SocialMediaAccountCategoryWeight::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'weight' => 10,
        ]);

        $scheduledContent = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Already Scheduled',
            'content' => 'This is scheduled.',
        ]);

        SocialMediaSchedule::create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $scheduledContent->id,
            'scheduled_at' => now()->addDay(),
        ]);

        $availableContent = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Not Scheduled',
            'content' => 'Fresh content.',
        ]);

        $response = $this->getJson('/api/social_media_contents/autopost');

        $response->assertOk()
            ->assertJsonPath('data.id', $availableContent->id)
            ->assertJsonPath('data.title', 'Not Scheduled');
    }

    public function test_autopost_falls_back_when_primary_category_exhausted(): void
    {
        SocialMediaAccountCategoryWeight::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'weight' => 100,
        ]);

        SocialMediaAccountCategoryWeight::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->trivia->id,
            'weight' => 1,
        ]);

        $postedHoliday = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Posted Holiday',
            'content' => 'Already posted.',
        ]);

        SocialMediaPost::create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $postedHoliday->id,
            'posted_at' => now(),
        ]);

        $triviaContent = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->trivia->id,
            'title' => 'Trivia Fallback',
            'content' => 'Did you know?',
        ]);

        $response = $this->getJson('/api/social_media_contents/autopost');

        $response->assertOk()
            ->assertJsonPath('data.id', $triviaContent->id)
            ->assertJsonPath('data.title', 'Trivia Fallback');
    }

    public function test_autopost_returns_404_when_no_weights_configured(): void
    {
        SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Orphan Content',
            'content' => 'No weights for this account.',
        ]);

        $response = $this->getJson('/api/social_media_contents/autopost');

        $response->assertNotFound()
            ->assertJsonPath('message', 'No available content found for autopost.');
    }

    public function test_autopost_returns_404_when_all_content_exhausted(): void
    {
        SocialMediaAccountCategoryWeight::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'weight' => 10,
        ]);

        $content = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'All Posted',
            'content' => 'Already posted.',
        ]);

        SocialMediaPost::create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $content->id,
            'posted_at' => now(),
        ]);

        $response = $this->getJson('/api/social_media_contents/autopost');

        $response->assertNotFound()
            ->assertJsonPath('message', 'No available content found for autopost.');
    }

    public function test_autopost_ignores_zero_weight_categories(): void
    {
        SocialMediaAccountCategoryWeight::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'weight' => 0,
        ]);

        SocialMediaAccountCategoryWeight::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->trivia->id,
            'weight' => 5,
        ]);

        SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Zero Weight Holiday',
            'content' => 'Should not be selected.',
        ]);

        $triviaContent = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->trivia->id,
            'title' => 'Trivia Content',
            'content' => 'This should be selected.',
        ]);

        $response = $this->getJson('/api/social_media_contents/autopost');

        $response->assertOk()
            ->assertJsonPath('data.id', $triviaContent->id);
    }

    public function test_autopost_scopes_to_authenticated_users_account(): void
    {
        $otherAccount = Account::create([
            'name' => 'Other Account',
            'website' => 'https://other.com',
        ]);

        SocialMediaAccountCategoryWeight::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'weight' => 10,
        ]);

        SocialMediaContent::create([
            'account_id' => $otherAccount->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Other Account Content',
            'content' => 'Not mine.',
        ]);

        $myContent = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'My Content',
            'content' => 'This is mine.',
        ]);

        $response = $this->getJson('/api/social_media_contents/autopost');

        $response->assertOk()
            ->assertJsonPath('data.id', $myContent->id)
            ->assertJsonPath('data.account_id', $this->account->id);
    }

    public function test_autopost_with_multiple_weighted_categories_returns_valid_content(): void
    {
        SocialMediaAccountCategoryWeight::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'weight' => 5,
        ]);

        SocialMediaAccountCategoryWeight::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->trivia->id,
            'weight' => 3,
        ]);

        SocialMediaAccountCategoryWeight::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->news->id,
            'weight' => 2,
        ]);

        $holidayContent = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Holiday Post',
            'content' => 'Holidays!',
        ]);

        $triviaContent = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->trivia->id,
            'title' => 'Trivia Post',
            'content' => 'Fun facts!',
        ]);

        $newsContent = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->news->id,
            'title' => 'News Post',
            'content' => 'Breaking news!',
        ]);

        $validIds = [$holidayContent->id, $triviaContent->id, $newsContent->id];

        $response = $this->getJson('/api/social_media_contents/autopost');

        $response->assertOk();
        $this->assertContains($response->json('data.id'), $validIds);
    }

    public function test_autopost_returns_404_when_no_content_exists_for_weighted_categories(): void
    {
        SocialMediaAccountCategoryWeight::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'weight' => 10,
        ]);

        $response = $this->getJson('/api/social_media_contents/autopost');

        $response->assertNotFound()
            ->assertJsonPath('message', 'No available content found for autopost.');
    }

    /**
     * Set up three weighted categories (holidays=5, trivia=3, news=2) with content,
     * and return the content keyed by category name.
     *
     * @return array{holidays: SocialMediaContent, trivia: SocialMediaContent, news: SocialMediaContent}
     */
    private function createWeightedCategoriesWithContent(): array
    {
        SocialMediaAccountCategoryWeight::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'weight' => 5,
        ]);

        SocialMediaAccountCategoryWeight::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->trivia->id,
            'weight' => 3,
        ]);

        SocialMediaAccountCategoryWeight::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->news->id,
            'weight' => 2,
        ]);

        return [
            'holidays' => SocialMediaContent::create([
                'account_id' => $this->account->id,
                'social_media_category_id' => $this->holidays->id,
                'title' => 'Holiday Post',
                'content' => 'Holidays!',
            ]),
            'trivia' => SocialMediaContent::create([
                'account_id' => $this->account->id,
                'social_media_category_id' => $this->trivia->id,
                'title' => 'Trivia Post',
                'content' => 'Fun facts!',
            ]),
            'news' => SocialMediaContent::create([
                'account_id' => $this->account->id,
                'social_media_category_id' => $this->news->id,
                'title' => 'News Post',
                'content' => 'Breaking news!',
            ]),
        ];
    }

    /**
     * Deterministic boundary tests with weights holidays=5, trivia=3, news=2 (total=10):
     *  ┌──────────────┬───────────────────┬─────────────────────────────┐
     *  │ Random Value │ Expected Category │          Boundary           │
     *  ├──────────────┼───────────────────┼─────────────────────────────┤
     *  │ 1            │ holidays          │ first category, lower edge  │
     *  ├──────────────┼───────────────────┼─────────────────────────────┤
     *  │ 5            │ holidays          │ first category, upper edge  │
     *  ├──────────────┼───────────────────┼─────────────────────────────┤
     *  │ 6            │ trivia            │ second category, lower edge │
     *  ├──────────────┼───────────────────┼─────────────────────────────┤
     *  │ 8            │ trivia            │ second category, upper edge │
     *  ├──────────────┼───────────────────┼─────────────────────────────┤
     *  │ 9            │ news              │ third category, lower edge  │
     *  ├──────────────┼───────────────────┼─────────────────────────────┤
     *  │ 10           │ news              │ third category, upper edge  │
     *  └──────────────┴───────────────────┴─────────────────────────────┘
     */
    public function test_weighted_selection_picks_first_category_at_lower_boundary(): void
    {
        $content = $this->createWeightedCategoriesWithContent();
        $service = new AutopostService;

        $result = $service->selectContent($this->account->id, randomValue: 1);

        $this->assertNotNull($result);
        $this->assertEquals($content['holidays']->id, $result->id);
    }

    public function test_weighted_selection_picks_first_category_at_upper_boundary(): void
    {
        $content = $this->createWeightedCategoriesWithContent();
        $service = new AutopostService;

        $result = $service->selectContent($this->account->id, randomValue: 5);

        $this->assertNotNull($result);
        $this->assertEquals($content['holidays']->id, $result->id);
    }

    public function test_weighted_selection_crosses_to_second_category_at_lower_boundary(): void
    {
        $content = $this->createWeightedCategoriesWithContent();
        $service = new AutopostService;

        $result = $service->selectContent($this->account->id, randomValue: 6);

        $this->assertNotNull($result);
        $this->assertEquals($content['trivia']->id, $result->id);
    }

    public function test_weighted_selection_picks_second_category_at_upper_boundary(): void
    {
        $content = $this->createWeightedCategoriesWithContent();
        $service = new AutopostService;

        $result = $service->selectContent($this->account->id, randomValue: 8);

        $this->assertNotNull($result);
        $this->assertEquals($content['trivia']->id, $result->id);
    }

    public function test_weighted_selection_crosses_to_third_category_at_lower_boundary(): void
    {
        $content = $this->createWeightedCategoriesWithContent();
        $service = new AutopostService;

        $result = $service->selectContent($this->account->id, randomValue: 9);

        $this->assertNotNull($result);
        $this->assertEquals($content['news']->id, $result->id);
    }

    public function test_weighted_selection_picks_third_category_at_max_boundary(): void
    {
        $content = $this->createWeightedCategoriesWithContent();
        $service = new AutopostService;

        $result = $service->selectContent($this->account->id, randomValue: 10);

        $this->assertNotNull($result);
        $this->assertEquals($content['news']->id, $result->id);
    }
}
