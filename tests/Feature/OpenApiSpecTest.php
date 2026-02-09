<?php

namespace Tests\Feature;

use App\Models\SocialMediaCategory;
use App\Models\SocialMediaContent;
use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

class OpenApiSpecTest extends BaseTestCase
{
    /**
     * Fetch the OpenAPI spec JSON, bypassing docs access restrictions.
     *
     * @return array<string, mixed>
     */
    private function fetchSpec(): array
    {
        return $this->withoutMiddleware(RestrictedDocsAccess::class)
            ->getJson('/api/docs.json')
            ->assertStatus(200)
            ->json();
    }

    /**
     * The OpenAPI spec endpoint returns valid JSON with the expected structure.
     */
    public function test_open_api_spec_is_valid_json(): void
    {
        $response = $this->withoutMiddleware(RestrictedDocsAccess::class)
            ->getJson('/api/docs.json');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');

        $spec = $response->json();

        $this->assertIsArray($spec);
        $this->assertArrayHasKey('openapi', $spec);
        $this->assertStringStartsWith('3.1', $spec['openapi']);
    }

    /**
     * The OpenAPI spec contains the correct info section.
     */
    public function test_open_api_spec_has_info_section(): void
    {
        $spec = $this->fetchSpec();

        $this->assertArrayHasKey('info', $spec);
        $this->assertArrayHasKey('title', $spec['info']);
        $this->assertArrayHasKey('version', $spec['info']);
        $this->assertEquals('1.0.0', $spec['info']['version']);
    }

    /**
     * The OpenAPI spec contains security schemes.
     */
    public function test_open_api_spec_has_security_scheme(): void
    {
        $spec = $this->fetchSpec();

        $this->assertArrayHasKey('components', $spec);
        $this->assertArrayHasKey('securitySchemes', $spec['components']);
    }

    /**
     * The OpenAPI spec documents all expected API paths.
     */
    public function test_open_api_spec_covers_all_endpoints(): void
    {
        $spec = $this->fetchSpec();

        $this->assertArrayHasKey('paths', $spec);

        $paths = array_keys($spec['paths']);

        $expectedPaths = [
            '/social_media_categories',
            '/social_media_contents',
            '/social_media_contents/autopost',
            '/social_media_posts',
            '/social_media_contents/{socialMediaContent}/media',
            '/social_media_contents/{socialMediaContent}/rewrite',
            '/social_media_contents/generate',
            '/social_media_contents/generate/{contentGenerationRequest}',
        ];

        foreach ($expectedPaths as $expectedPath) {
            $this->assertContains(
                $expectedPath,
                $paths,
                "Expected path '{$expectedPath}' not found in spec. Available paths: ".implode(', ', $paths)
            );
        }
    }

    /**
     * The OpenAPI spec includes the correct HTTP methods for content endpoints.
     */
    public function test_open_api_spec_has_correct_methods_for_content(): void
    {
        $spec = $this->fetchSpec();

        $contentPath = $spec['paths']['/social_media_contents'] ?? [];
        $this->assertArrayHasKey('get', $contentPath, 'Content index (GET) missing from spec');
        $this->assertArrayHasKey('post', $contentPath, 'Content store (POST) missing from spec');
    }

    /**
     * The OpenAPI spec contains reusable component schemas.
     */
    public function test_open_api_spec_has_component_schemas(): void
    {
        $spec = $this->fetchSpec();

        $this->assertArrayHasKey('components', $spec);
        $this->assertArrayHasKey('schemas', $spec['components']);
        $this->assertNotEmpty($spec['components']['schemas']);
    }

    /**
     * The category index API response matches the documented spec structure.
     */
    public function test_category_index_response_matches_spec(): void
    {
        SocialMediaCategory::factory()->count(3)->create();

        $response = $this->getJson('/api/social_media_categories');
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name'],
            ],
        ]);
    }

    /**
     * The content store API response matches the documented spec structure.
     */
    public function test_content_store_response_matches_spec(): void
    {
        $category = SocialMediaCategory::factory()->create();

        $response = $this->postJson('/api/social_media_contents', [
            'social_media_category_id' => $category->id,
            'title' => 'Test Content for Spec Validation',
            'content' => 'This content validates the OpenAPI spec.',
        ]);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'data' => ['id', 'account_id', 'title', 'content', 'created_at', 'updated_at'],
        ]);
    }

    /**
     * The content show API response matches the documented spec structure.
     */
    public function test_content_show_response_matches_spec(): void
    {
        $content = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
        ]);

        $response = $this->getJson("/api/social_media_contents/{$content->id}");
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => ['id', 'account_id', 'title', 'content', 'created_at', 'updated_at'],
        ]);
    }

    /**
     * The post store API response matches the documented spec structure.
     */
    public function test_post_store_response_matches_spec(): void
    {
        $content = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
        ]);

        $response = $this->postJson('/api/social_media_posts', [
            'social_media_content_id' => $content->id,
            'posted_at' => now()->toIso8601String(),
        ]);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'data' => ['id', 'account_id', 'social_media_content_id', 'posted_at'],
        ]);
    }

    /**
     * The autopost endpoint returns content or 404 as documented in the spec.
     */
    public function test_autopost_response_matches_spec(): void
    {
        $response = $this->getJson('/api/social_media_contents/autopost');

        $this->assertContains($response->status(), [200, 404]);

        if ($response->status() === 404) {
            $response->assertJsonStructure(['message']);
        }

        if ($response->status() === 200) {
            $response->assertJsonStructure([
                'data' => ['id', 'title', 'content'],
            ]);
        }
    }

    /**
     * Validation error responses match the expected 422 structure.
     */
    public function test_validation_error_response_matches_spec(): void
    {
        $response = $this->postJson('/api/social_media_contents', []);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors',
        ]);
    }

    /**
     * The delete endpoint returns the expected JSON structure.
     */
    public function test_delete_response_matches_spec(): void
    {
        $content = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
        ]);

        $this->actingAs($this->createAdminUser());

        $response = $this->deleteJson("/api/social_media_contents/{$content->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure(['message']);
    }
}
