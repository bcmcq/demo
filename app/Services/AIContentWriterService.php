<?php

namespace App\Services;

use App\Enums\Platform;
use App\Enums\Tone;
use App\Models\SocialMediaContent;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

class AIContentWriterService
{
    public function __construct(protected int $variationCount = 3) {}

    /**
     * Rewrite existing content tailored for a specific platform and tone.
     *
     * @return array<int, string>
     */
    public function rewrite(SocialMediaContent $content, Platform $platform, Tone $tone): array
    {
        $response = Prism::structured()
            ->using(Provider::OpenAI, config('services.openai.model'))
            ->withSchema($this->buildVariationsSchema())
            ->withSystemPrompt($this->buildSystemPrompt($platform, $tone))
            ->withPrompt($this->buildRewritePrompt($content, $platform))
            ->asStructured();

        return $response->structured['variations'];
    }

    /**
     * Generate new content from a freeform prompt for a specific platform and tone.
     *
     * @return array<int, string>
     */
    public function generate(string $prompt, Platform $platform, Tone $tone): array
    {
        $response = Prism::structured()
            ->using(Provider::OpenAI, config('services.openai.model'))
            ->withSchema($this->buildVariationsSchema())
            ->withSystemPrompt($this->buildSystemPrompt($platform, $tone))
            ->withPrompt($this->buildGeneratePrompt($prompt, $platform))
            ->asStructured();

        return $response->structured['variations'];
    }

    public function getVariationCount(): int
    {
        return $this->variationCount;
    }

    /**
     * Build the structured output schema for content variations.
     */
    protected function buildVariationsSchema(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'content_variations',
            description: 'Multiple variations of social media content',
            properties: [
                new ArraySchema(
                    name: 'variations',
                    description: 'Array of unique content variations',
                    items: new StringSchema(
                        name: 'variation',
                        description: 'A single content variation',
                    ),
                ),
            ],
            requiredFields: ['variations'],
        );
    }

    /**
     * Build a system prompt that instructs the LLM on platform constraints and tone.
     */
    protected function buildSystemPrompt(Platform $platform, Tone $tone): string
    {
        $platformGuidelines = $this->getPlatformGuidelines($platform);

        return <<<PROMPT
        You are a social media content writer. Your job is to create engaging, platform-optimized content.

        Platform: {$platform->value}
        Tone: {$tone->value}

        Platform guidelines:
        {$platformGuidelines}

        Rules:
        - Write ONLY the post content. Do not include explanations, labels, or commentary.
        - Match the requested tone precisely.
        - Follow the platform's character limits and conventions.
        - Use appropriate hashtags and formatting for the platform.
        - Generate exactly {$this->variationCount} unique variations. Each should be distinct in style and approach.
        PROMPT;
    }

    /**
     * Build the prompt for rewriting existing content.
     */
    protected function buildRewritePrompt(SocialMediaContent $content, Platform $platform): string
    {
        return <<<PROMPT
        Rewrite the following social media content for {$platform->value}.

        Title: {$content->title}
        Original content: {$content->content}

        Produce {$this->variationCount} unique rewritten versions optimized for the platform. Each variation should take a different angle or approach.
        PROMPT;
    }

    /**
     * Build the prompt for generating new content from a freeform prompt.
     */
    protected function buildGeneratePrompt(string $prompt, Platform $platform): string
    {
        return <<<PROMPT
        Create social media posts for {$platform->value} based on the following idea:

        {$prompt}

        Produce {$this->variationCount} unique posts optimized for the platform. Each variation should take a different angle or approach.
        PROMPT;
    }

    /**
     * Get platform-specific guidelines for the LLM.
     *
     * @return string Platform-specific content guidelines
     */
    protected function getPlatformGuidelines(Platform $platform): string
    {
        return match ($platform) {
            Platform::Twitter => 'Maximum 280 characters. Use concise language. Hashtags are common but keep to 1-3. Mentions with @. Threads are acceptable for longer content.',
            Platform::Instagram => 'Caption can be up to 2,200 characters. Use 5-15 relevant hashtags at the end. Emojis are encouraged. Line breaks improve readability.',
            Platform::Facebook => 'No strict character limit but 40-80 characters get the most engagement. Questions and calls-to-action perform well. Minimal hashtags (0-2).',
            Platform::LinkedIn => 'Professional tone expected. Up to 3,000 characters. Use line breaks for readability. 3-5 relevant hashtags. Focus on industry insights and value.',
        };
    }
}
