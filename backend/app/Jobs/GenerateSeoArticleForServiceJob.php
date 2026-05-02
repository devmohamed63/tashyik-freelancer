<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\Service;
use App\Models\Settings;
use App\Services\Seo\GeminiBlogGenerator;
use App\Utils\HtmlToPlainText;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateSeoArticleForServiceJob implements ShouldQueue
{
    use Queueable;
    //comment

    public int $tries = 5;

    public function __construct(
        public int $serviceId,
        public bool $force = false,
        /** When true (monthly recycle), allow another AI article for the same service this month. */
        public bool $recycle = false,
    ) {
    }

    /**
     * @return array<int, int> Seconds to wait before each retry (after the first failure).
     */
    public function backoff(): array
    {
        return [30, 90, 180, 300];
    }

    public function handle(GeminiBlogGenerator $generator): void
    {
        $settings = Settings::first();

        if (!$settings) {
            Log::info('GenerateSeoArticleForServiceJob: aborted — no settings record', [
                'service_id' => $this->serviceId,
            ]);

            return;
        }

        if (!$this->force && !$settings->ai_blog_automation_enabled) {
            Log::info('GenerateSeoArticleForServiceJob: aborted — ai_blog_automation_enabled is off', [
                'service_id' => $this->serviceId,
            ]);

            return;
        }

        if (!$this->force && $this->limitReached($settings)) {
            Log::info('GenerateSeoArticleForServiceJob: aborted — daily or monthly AI article limit reached', [
                'service_id' => $this->serviceId,
            ]);

            return;
        }

        $service = Service::with('category')->find($this->serviceId);

        if (!$service) {
            Log::warning('GenerateSeoArticleForServiceJob: aborted — service not found', [
                'service_id' => $this->serviceId,
            ]);

            return;
        }

        if (!$this->force && !$this->recycle && $this->serviceAlreadyHasAiArticleThisMonth($service->id)) {
            Log::info('GenerateSeoArticleForServiceJob: skipped — no new article created (service already has an AI article this month)', [
                'service_id' => $service->id,
                'note' => 'This run does not explain missing images on existing articles; find logs for the article created_at time instead.',
            ]);

            return;
        }

        $generated = $generator->generate($service, (string) $settings->ai_blog_prompt);
        $bodies = $this->appendServiceLinksToBodies(
            $generated['body_ar'] ?? '',
            $generated['body_en'] ?? '',
            $service->id
        );

        $slugSource = trim((string) ($generated['slug'] ?? ''));
        if ($slugSource === '') {
            $slugSource = (string) ($generated['title_ar'] ?? $generated['title_en'] ?? 'article');
        }
        $uniqueSlug = Article::generateUniqueSlug($slugSource);

        $article = Article::create([
            'title' => [
                'ar' => $generated['title_ar'] ?? '',
                'en' => $generated['title_en'] ?? '',
            ],
            'slug' => $uniqueSlug,
            'excerpt' => [
                'ar' => HtmlToPlainText::convertString((string) ($generated['excerpt_ar'] ?? '')),
                'en' => HtmlToPlainText::convertString((string) ($generated['excerpt_en'] ?? '')),
            ],
            'body' => [
                'ar' => $bodies['ar'],
                'en' => $bodies['en'],
            ],
            'meta_title' => [
                'ar' => $generated['meta_title_ar'] ?? '',
                'en' => $generated['meta_title_en'] ?? '',
            ],
            'meta_description' => [
                'ar' => HtmlToPlainText::convertString($bodies['ar']),
                'en' => HtmlToPlainText::convertString($bodies['en']),
            ],
            'meta_keywords' => [
                'ar' => $generated['keywords_ar'] ?? [],
                'en' => $generated['keywords_en'] ?? [],
            ],
            'service_id' => $service->id,
            'category_id' => $service->category_id,
            'generated_by_ai' => true,
            'status' => Article::ACTIVE_STATUS,
            'published_at' => now(),
        ]);

        $article->syncMetaToLinkedService(true);

        $imagePrompt = (string) ($generated['image_prompt'] ?? '');
        if ($imagePrompt === '') {
            Log::info('GenerateSeoArticleForServiceJob: empty image_prompt from AI', [
                'article_id' => $article->id,
                'service_id' => $service->id,
            ]);
        } else {
            $tmp = $generator->createImageFromPrompt($imagePrompt);
            if ($tmp) {
                try {
                    $article->addMedia($tmp)->toMediaCollection('featured_image');
                } catch (\Throwable $e) {
                    Log::warning('GenerateSeoArticleForServiceJob: addMedia failed', [
                        'article_id' => $article->id,
                        'service_id' => $service->id,
                        'error' => $e->getMessage(),
                    ]);
                }
                @unlink($tmp);
            } else {
                Log::warning('GenerateSeoArticleForServiceJob: featured image not generated (Gemini image, then OpenAI if configured, then Pollinations — all failed or timed out)', [
                    'article_id' => $article->id,
                    'service_id' => $service->id,
                    'image_provider' => config('services.image_generation.provider'),
                ]);
            }
        }

        $this->attachServiceImageAsFeaturedFallback($article, $service);

        $article->refresh();
        $article->loadMissing('media');
        $hasFeatured = $article->getMedia('featured_image')->isNotEmpty();

        Log::info('GenerateSeoArticleForServiceJob: completed', [
            'article_id' => $article->id,
            'service_id' => $service->id,
            'slug' => $article->slug,
            'has_featured_image' => $hasFeatured,
            'image_prompt_empty' => $imagePrompt === '',
            'image_prompt_length' => strlen($imagePrompt),
        ]);

        if (! $hasFeatured) {
            Log::warning('GenerateSeoArticleForServiceJob: article published without featured image — check earlier log lines in the same request for empty image_prompt, AI image failure, addMedia, or service fallback', [
                'article_id' => $article->id,
                'service_id' => $service->id,
                'slug' => $article->slug,
            ]);
        }
    }

    /**
     * When AI image is missing or failed, use the service main image so listings are not empty.
     */
    private function attachServiceImageAsFeaturedFallback(Article $article, Service $service): void
    {
        $article->loadMissing('media');

        if ($article->getMedia('featured_image')->isNotEmpty()) {
            return;
        }

        $service->loadMissing('media');
        $source = $service->getFirstMedia('image');
        if (! $source) {
            Log::info('GenerateSeoArticleForServiceJob: no service image for featured fallback', [
                'article_id' => $article->id,
                'service_id' => $service->id,
            ]);

            return;
        }

        $path = $source->getPath();
        if (! is_string($path) || $path === '' || ! is_readable($path)) {
            Log::warning('GenerateSeoArticleForServiceJob: service image path not readable for fallback', [
                'article_id' => $article->id,
                'service_id' => $service->id,
            ]);

            return;
        }

        try {
            $article->addMedia($path)
                ->usingFileName($source->file_name)
                ->toMediaCollection('featured_image');

            Log::info('GenerateSeoArticleForServiceJob: featured image filled from service catalog image', [
                'article_id' => $article->id,
                'service_id' => $service->id,
            ]);
        } catch (\Throwable $e) {
            Log::warning('GenerateSeoArticleForServiceJob: service image fallback addMedia failed', [
                'article_id' => $article->id,
                'service_id' => $service->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function serviceAlreadyHasAiArticleThisMonth(int $serviceId): bool
    {
        return Article::where('service_id', $serviceId)
            ->where('generated_by_ai', true)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->exists();
    }

    private function limitReached(Settings $settings): bool
    {
        $todayCount = Article::where('generated_by_ai', true)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($todayCount >= (int) $settings->ai_blog_daily_limit) {
            return true;
        }

        $monthCount = Article::where('generated_by_ai', true)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return $monthCount >= (int) $settings->ai_blog_monthly_limit;
    }

    /**
     * Append a service CTA as a button-shaped link (no raw URL text).
     *
     * @return array{ar:string,en:string}
     */
    private function appendServiceLinksToBodies(string $bodyAr, string $bodyEn, int $serviceId): array
    {
        $frontendUrl = rtrim((string) config('app.frontend_url', env('FRONTEND_URL')), '/');

        $arLink = "{$frontendUrl}/ar/services/{$serviceId}";
        $enLink = "{$frontendUrl}/en/services/{$serviceId}";

        $arCta = $this->servicePageCtaHtml($arLink, 'الانتقال إلى الخدمة', 'rtl');
        $enCta = $this->servicePageCtaHtml($enLink, 'View service', 'ltr');

        return [
            'ar' => trim($bodyAr) . "\n\n" . $arCta,
            'en' => trim($bodyEn) . "\n\n" . $enCta,
        ];
    }

    private function servicePageCtaHtml(string $href, string $label, string $dir): string
    {
        $hex = (string) config('services.image_generation.brand_primary_hex', '#724193');
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $hex)) {
            $hex = '#724193';
        }

        $style = 'display:inline-block;padding:0.75rem 1.5rem;background-color:' . $hex
            . ';color:#ffffff;text-decoration:none;border-radius:0.5rem;font-weight:600;'
            . 'font-size:1rem;line-height:1.25;text-align:center;';

        $safeHref = e($href);
        $safeLabel = e($label);
        $safeDir = $dir === 'ltr' ? 'ltr' : 'rtl';

        return '<p style="margin-top:1.5rem;text-align:center;" dir="' . $safeDir . '">'
            . '<a href="' . $safeHref . '" target="_blank" rel="noopener" style="' . $style . '">' . $safeLabel . '</a>'
            . '</p>';
    }
}
