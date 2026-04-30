<?php

namespace App\Services\Seo;

use App\Models\Article;
use App\Models\Service;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeminiBlogGenerator
{
    //comment
    public function generate(Service $service, string $extraPrompt = ''): array
    {
        $relatedArticles = Article::published()
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->limit(3)
            ->get(['title', 'slug']);

        $internalLinks = $relatedArticles
            ->map(function (Article $article) {
                return [
                    'title_ar' => $article->getTranslation('title', 'ar', false),
                    'title_en' => $article->getTranslation('title', 'en', false),
                    'slug' => $article->slug,
                ];
            })
            ->values()
            ->all();

        $prompt = $this->buildPrompt($service, $internalLinks, $extraPrompt);

        $configuredBase = rtrim((string) config('services.gemini.base_url'), '/');
        $apiKey = (string) config('services.gemini.api_key');
        $preferredModel = $this->normalizeModelId((string) config('services.gemini.text_model'));
        $fallbackModels = array_map(
            fn (string $m) => $this->normalizeModelId($m),
            (array) config('services.gemini.text_models', [])
        );

        $models = array_values(array_unique(array_filter([$preferredModel, ...$fallbackModels])));

        $baseCandidates = [$configuredBase];
        $v1FromBeta = preg_replace('#/v1beta$#', '/v1', $configuredBase);
        if ($v1FromBeta !== $configuredBase && $v1FromBeta !== '') {
            $baseCandidates[] = $v1FromBeta;
        }
        $baseCandidates[] = 'https://generativelanguage.googleapis.com/v1beta';
        $baseCandidates[] = 'https://generativelanguage.googleapis.com/v1';
        $baseUrls = array_values(array_unique(array_filter($baseCandidates)));

        $response = null;
        $lastException = null;
        $tried = [];

        foreach ($baseUrls as $baseUrl) {
            foreach ($models as $model) {
                $tried[] = "{$baseUrl}/models/{$model}";
                $response = $this->requestGenerateContent(
                    "{$baseUrl}/models/{$model}:generateContent?key={$apiKey}",
                    $prompt,
                    $lastException
                );

                if (is_array($response)) {
                    break 2;
                }
            }
        }

        if (!is_array($response)) {
            throw new \RuntimeException(
                'Gemini text models not available. Tried: ' . implode(' | ', $tried),
                previous: $lastException
            );
        }

        $text = $this->extractCandidateText($response);
        $finishReason = (string) data_get($response, 'candidates.0.finishReason', '');

        if ($finishReason === 'MAX_TOKENS') {
            Log::warning('Gemini finished with MAX_TOKENS; JSON may be truncated. Raise GEMINI_MAX_OUTPUT_TOKENS or shorten the prompt output.', [
                'finishReason' => $finishReason,
            ]);
        }

        $payload = $this->decodeBlogJsonPayload($text);

        if (!is_array($payload)) {
            $hint = $finishReason === 'MAX_TOKENS'
                ? ' (likely output token limit; set GEMINI_MAX_OUTPUT_TOKENS higher, e.g. 32768, and ensure meta_description fields stay empty in the model output.)'
                : '';

            throw new \RuntimeException(
                'Gemini response is not a valid JSON payload.' . $hint
                . ' Snippet: ' . Str::limit(preg_replace('/\s+/', ' ', $text) ?? '', 400)
            );
        }

        return $payload;
    }

    public function createImageFromPrompt(string $imagePrompt): ?string
    {
        $parts = $this->enhanceImagePromptParts($imagePrompt);
        $combinedPrompt = $this->combinePromptAndNegativeForPollinations($parts);

        $geminiImage = $this->createImageFromGemini($parts);
        if ($geminiImage) {
            return $geminiImage;
        }

        return $this->createImageFromPollinations($combinedPrompt);
    }

    /**
     * Gemini native image (e.g. gemini-3.1-flash-image-preview). Uses GEMINI_API_KEY.
     *
     * @param  array{raw: string, prompt: string, negative: string}  $parts
     * @return string|null Local temp path to decoded image, or null on failure
     */
    private function createImageFromGemini(array $parts): ?string
    {
        $apiKey = trim((string) config('services.gemini.api_key'));
        if ($apiKey === '') {
            return null;
        }

        $base = rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $model = $this->normalizeModelId((string) config('services.gemini.image_model', 'gemini-3.1-flash-image-preview'));
        if ($model === '') {
            $model = 'gemini-3.1-flash-image-preview';
        }

        $userText = $this->buildGeminiFlashImageUserText($parts);
        if (strlen($userText) > 32000) {
            $userText = substr($userText, 0, 31997) . '...';
        }

        $body = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $userText],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseModalities' => ['IMAGE', 'TEXT'],
                'imageConfig' => [
                    'aspectRatio' => '3:2',
                ],
            ],
        ];

        $url = "{$base}/models/{$model}:streamGenerateContent?key={$apiKey}";

        try {
            $maxAttempts = 3;

            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                $response = Http::acceptJson()
                    ->timeout(180)
                    ->post($url, $body);

                if ($response->successful()) {
                    $raw = (string) $response->body();
                    $chunks = $this->parseGeminiStreamResponseChunks($raw);
                    $binary = $this->firstInlineImageBinaryFromChunks($chunks);

                    if ($binary !== null) {
                        $ext = str_starts_with($binary, "\x89PNG") ? 'png'
                            : (str_starts_with($binary, "\xff\xd8\xff") ? 'jpg' : 'jpg');

                        return $this->writeTempImage($binary, $ext);
                    }

                    Log::warning('Gemini image stream returned no inline image data', [
                        'snippet' => Str::limit($raw, 400),
                    ]);

                    return null;
                }

                $status = $response->status();

                if (in_array($status, [429, 503, 408], true) && $attempt < $maxAttempts) {
                    usleep(random_int(1_500_000, 4_000_000));

                    continue;
                }

                Log::warning('Gemini image streamGenerateContent failed', [
                    'status' => $status,
                    'snippet' => Str::limit($response->body(), 400),
                ]);

                return null;
            }
        } catch (\Throwable $e) {
            Log::warning('Gemini image generation exception', ['message' => $e->getMessage()]);

            return null;
        }

        return null;
    }

    /**
     * Single-block prompt: split hero + Arabic typography panel (image models often skip text if layout is vague
     * or if "random text" appears in negatives — see geminiImageNegative()).
     *
     * @param  array{raw: string, prompt: string, negative: string}  $parts
     */
    private function buildGeminiFlashImageUserText(array $parts): string
    {
        $hex = $this->normalizedBrandPrimaryHex();
        $rawScene = trim((string) ($parts['raw'] ?? $parts['prompt']));
        $phone = trim((string) config('services.gemini.image_ad_phone', '+966582250326'));
        if ($phone === '') {
            $phone = '+966582250326';
        }

        $rawScene = preg_replace('/\s+/u', ' ', $rawScene) ?? $rawScene;
        $avoid = $this->geminiImageNegative();

        $headlineInstruction = 'Large main headline in Arabic (RTL), bold modern Arabic font, white and soft purple, '
            . 'perfect spelling and alignment, must describe the SAME service as the technician scene (same appliance/trade as above). '
            . 'Example length/style only (adapt words to match the scene): "تصليح ثلاجات سريع وموثوق" for refrigerator repair.';

        $sub = 'فنيين محترفين • خدمة في نفس اليوم • ضمان على الخدمة';
        $cta = 'احجز خدمتك الآن';

        return <<<PROMPT
Design a single finished advertisement image (not a plain photo). Layout is mandatory:

Overall format: wide horizontal LANDSCAPE banner, aspect ratio 3:2, suitable for a blog listing and article hero (not square, not portrait). Fill the full frame edge to edge.

LEFT approximately 65 percent of the frame: cinematic photoreal hero — Tashyik (تشييك) commercial.
{$rawScene}
One professional male technician, clean uniform with English "TASHYIK" on the chest, actively working on equipment that matches this service. Modern luxury kitchen or workspace, purple neon accents, brand color {$hex}, ultra realistic, 8k, depth of field, volumetric light, shot on Sony a7R IV 50mm, premium advertising photography.

RIGHT approximately 35 percent of the frame: a solid vertical panel with dark purple gradient (no busy photo behind text). This panel MUST contain clearly painted, high-contrast advertisement typography — all of the following must appear as real text in the image (not empty, not implied):

1) HEADLINE — {$headlineInstruction}

2) SUBHEADLINE — smaller white Arabic below, exact line (copy exactly):
{$sub}

3) CALL TO ACTION — rounded purple button or pill shape with white Arabic text, exact phrase (copy exactly):
{$cta}

4) PHONE — below the button, white Arabic numerals and plus sign, exact string (copy exactly):
{$phone}

Typography rules: crisp vector-like edges, RTL where appropriate, no gibberish, no missing letters, no Latin substitute for Arabic. The Arabic copy above is required on-image; do not output an image with only the photo and no text panel.

Avoid: {$avoid}.
PROMPT;
    }

    /**
     * Pollinations negative list includes "random text" which suppresses designed ad copy for Gemini; use a stricter subset.
     */
    private function geminiImageNegative(): string
    {
        return 'cartoon, illustration, painting, CGI render look, anime, distorted hands or face, blurry, low resolution, '
            . 'female technician, unrelated appliances, garbled or misspelled Arabic, extra headlines beyond the four specified blocks, '
            . 'watermark, stock photo marks, empty right panel with no text, text only on shirt without the right panel layout';
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseGeminiStreamResponseChunks(string $body): array
    {
        $body = trim($body);
        if ($body === '') {
            return [];
        }

        $decoded = json_decode($body, true);
        if (is_array($decoded)) {
            if (isset($decoded['candidates'])) {
                return [$decoded];
            }

            $allList = true;
            foreach ($decoded as $item) {
                if (!is_array($item)) {
                    $allList = false;
                    break;
                }
            }
            if ($allList && $decoded !== []) {
                /** @var list<array<string, mixed>> $decoded */
                return $decoded;
            }
        }

        $out = [];
        foreach (preg_split('/\r?\n/', $body) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (str_starts_with($line, 'data:')) {
                $line = trim(substr($line, 5));
                if ($line === '[DONE]') {
                    continue;
                }
            }
            $row = json_decode($line, true);
            if (is_array($row)) {
                $out[] = $row;
            }
        }

        return $out;
    }

    /**
     * @param  list<array<string, mixed>>  $chunks
     */
    private function firstInlineImageBinaryFromChunks(array $chunks): ?string
    {
        foreach ($chunks as $chunk) {
            $parts = data_get($chunk, 'candidates.0.content.parts', []);
            if (!is_array($parts)) {
                continue;
            }
            foreach ($parts as $part) {
                if (!is_array($part)) {
                    continue;
                }
                $b64 = data_get($part, 'inlineData.data') ?? data_get($part, 'inline_data.data');
                if (!is_string($b64) || $b64 === '') {
                    continue;
                }
                $binary = base64_decode($b64, true);
                if ($binary !== false && $binary !== '') {
                    return $binary;
                }
            }
        }

        return null;
    }

    /**
     * OpenAI Images API (DALL·E 3). API key: OPENAI_API_KEY.
     *
     * @return string|null Local temp path to downloaded image, or null on failure
     */
    private function createImageFromOpenAI(string $imagePrompt): ?string
    {
        $apiKey = (string) config('services.image_generation.openai.api_key');
        if ($apiKey === '') {
            return null;
        }

        $model = (string) config('services.image_generation.openai.model', 'gpt-image-1.5');
        $size = (string) config('services.image_generation.openai.size', '1024x1024');
        $quality = (string) config('services.image_generation.openai.quality', 'standard');

        if (strlen($imagePrompt) > 4000) {
            $imagePrompt = substr($imagePrompt, 0, 3997) . '...';
        }

        try {
            $maxAttempts = 3;

            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                $response = Http::withToken($apiKey)
                    ->acceptJson()
                    ->timeout(120)
                    ->post('https://api.openai.com/v1/images/generations', [
                        'model' => $model,
                        'prompt' => $imagePrompt,
                        'n' => 1,
                        'size' => $size,
                        'quality' => $quality,
                    ]);

                if ($response->successful()) {
                    $url = data_get($response->json(), 'data.0.url');

                    if (is_string($url) && $url !== '') {
                        $imageResponse = Http::timeout(90)
                            ->withHeaders(['User-Agent' => 'TashyikBackend/1.0'])
                            ->get($url);

                        if (!$imageResponse->successful()) {
                            Log::warning('OpenAI image URL download HTTP error', [
                                'status' => $imageResponse->status(),
                                'snippet' => Str::limit($imageResponse->body(), 200),
                            ]);
                        }

                        $imageData = $imageResponse->body();
                        $binary = (string) $imageData;

                        if ($binary !== '' && strlen($binary) > 500) {
                            $ext = str_starts_with($binary, "\x89PNG") ? 'png'
                                : (str_starts_with($binary, "\xff\xd8\xff") ? 'jpg' : null);

                            if ($ext !== null) {
                                return $this->writeTempImage($binary, $ext);
                            }
                        }

                        Log::warning('OpenAI image URL returned empty or unrecognized image body', [
                            'body_length' => strlen($binary),
                        ]);

                        return null;
                    }

                    $b64 = data_get($response->json(), 'data.0.b64_json');

                    if (is_string($b64) && $b64 !== '') {
                        $binary = base64_decode($b64, true);

                        if ($binary !== false) {
                            return $this->writeTempImage($binary, 'png');
                        }
                    }

                    return null;
                }

                $status = $response->status();

                if (in_array($status, [429, 503, 408], true) && $attempt < $maxAttempts) {
                    usleep(random_int(1_500_000, 4_000_000));

                    continue;
                }

                Log::warning('OpenAI images/generations failed', [
                    'status' => $response->status(),
                    'snippet' => Str::limit($response->body(), 300),
                ]);

                return null;
            }
        } catch (\Throwable $e) {
            Log::warning('OpenAI image generation exception', ['message' => $e->getMessage()]);

            return null;
        }

        return null;
    }

    private function createImageFromPollinations(string $imagePrompt): ?string
    {
        $url = 'https://image.pollinations.ai/prompt/' . rawurlencode($imagePrompt);
        $imageData = Http::timeout(45)->get($url)->body();

        if (!$imageData) {
            return null;
        }

        return $this->writeTempImage($imageData, 'png');
    }

    private function writeTempImage(string $imageData, string $extension = 'png'): ?string
    {
        $tmp = storage_path('app/tmp/' . Str::uuid() . '.' . $extension);

        if (!is_dir(dirname($tmp))) {
            mkdir(dirname($tmp), 0755, true);
        }

        file_put_contents($tmp, $imageData);

        return $tmp;
    }

    /**
     * Shared base prompt: max quality + strict no-text + brand primary color.
     *
     * @return array{raw: string, prompt: string, negative: string}
     */
    private function enhanceImagePromptParts(string $prompt): array
    {
        $hex = $this->normalizedBrandPrimaryHex();

        $quality = 'Ultra photorealistic premium advertising photography, highest quality, ultra-detailed, sharp focus, true-to-life proportions, realistic hands and face, high dynamic range, studio-grade lighting, clean composition.';
        $hexDigits = strtoupper(ltrim($hex, '#'));
        $brand = "Dominant mauve purple brand color exactly hex {$hex} (Flutter Color(0xFF{$hexDigits})). Use this exact rich purple/mauve hue for clothing accents, rim light, and soft background gradients; do not shift to blue, magenta, or brown.";

        $negative = 'cartoon, illustration, painting, CGI, 3D render, anime, distorted anatomy, deformed hands, blurry, low quality, low resolution, female person, unrelated objects, unrelated service scene, random text, random letters, random words, watermark, logo';

        $raw = trim($prompt);

        return [
            'raw' => $raw,
            'prompt' => $raw . ' ' . $quality . ' ' . $brand,
            'negative' => $negative,
        ];
    }

    /**
     * Reinforces photorealism + exact brand hex with no text overlays.
     */
    private function highFidelityImageDirectives(): string
    {
        $hex = $this->normalizedBrandPrimaryHex();

        return "Real DSLR-style commercial shot, highest fidelity, strictly relevant to the requested service only. Show one male technician actively repairing the exact service item. Keep {$hex} as the core mauve-purple accent on clothing trim and background lighting. Only allowed text in the image is the word TASHYIK printed clearly on the technician shirt; no other text, letters, logos, labels, captions, or watermarks.";
    }

    private function normalizedBrandPrimaryHex(): string
    {
        $raw = trim((string) config('services.image_generation.brand_primary_hex', '#724193'));
        if ($raw === '') {
            return '#724193';
        }

        if ($raw[0] !== '#') {
            $raw = '#' . ltrim($raw, '#');
        }

        return $raw;
    }

    /**
     * Pollinations: single prompt string (negative instructions inlined).
     *
     * @param  array{raw?: string, prompt: string, negative: string}  $parts
     */
    private function combinePromptAndNegativeForPollinations(array $parts): string
    {
        return $parts['prompt'] . ' ' . $this->highFidelityImageDirectives()
            . ' No cartoon, no illustration, no painting, no CGI, no 3D render, no anime, no distorted anatomy, no deformed hands, no blurry, no low quality, no wrong brand colors, no text, no letters, no words, no watermark.';
    }

    private function buildPrompt(Service $service, array $internalLinks, string $extraPrompt): string
    {
        $category = $service->category;
        $serviceNameAr = $service->getTranslation('name', 'ar', false) ?: 'الخدمة';
        $serviceNameEn = $service->getTranslation('name', 'en', false) ?: 'service';
        $brandHex = $this->normalizedBrandPrimaryHex();

        return "You are an SEO Arabic/English blog generator.\n"
            . "Return only valid JSON without markdown fences.\n"
            . "JSON keys exactly:\n"
            . "title_ar,title_en,excerpt_ar,excerpt_en,body_ar,body_en,meta_title_ar,meta_title_en,meta_description_ar,meta_description_en,keywords_ar,keywords_en,slug,image_prompt\n"
            . "keywords_ar and keywords_en: arrays of at most 8 short strings each.\n"
            . "excerpt_ar and excerpt_en must be plain text only: no HTML tags, no markdown, single short summary paragraph each (under 400 characters each).\n"
            . "meta_description_ar and meta_description_en must be exactly \"\" (empty strings). The server fills SEO description from body; do not write any text in these two fields.\n"
            . "body_ar and body_en must be clean semantic HTML (no markdown) using only: h2,h3,p,strong,ul,ol,li,a.\n"
            . "For body_ar, keep RTL-friendly sectioning and bold key phrases with <strong>.\n"
            . "Use this article structure for body_ar EXACTLY and in the same order with numbered sections and service points.\n"
            . "Match this editorial style closely: practical, promotional, clear Arabic, and easy to scan.\n"
            . "CRITICAL TOPIC RULE: The article MUST stay strictly about the provided service and category only.\n"
            . "Never switch to another domain (for example: do not talk about AC maintenance unless the service itself is AC-related).\n"
            . "Use {$serviceNameAr} as the central topic in all headings and paragraphs.\n"
            . "Required body_ar sections (STRICT FORMAT):\n"
            . "- Use exactly six <h2> headings and they MUST start with numeric prefixes: 1. , 2. , 3. , 4. , 5. , 6.\n"
            . "- Section 1: intro about {$serviceNameAr} importance + mention Tashyik.\n"
            . "- Section 2: why periodic maintenance/professional work is needed + bullet list of neglect risks.\n"
            . "- Section 3: Tashyik services for {$serviceNameAr} + ordered list with five numbered items (1..5) and short explanation under each.\n"
            . "- Section 4: Tashyik app advantages + bullet list.\n"
            . "- Section 5: how to choose the right {$serviceNameAr} provider + bullet list.\n"
            . "- Section 6: professional tip + concise closing CTA.\n"
            . "- Do not add any section before 1 or after 6.\n"
            . "- Do not append unrelated paragraphs, examples, or extra articles.\n"
            . "Do not output plain text blocks for body_ar/body_en; output proper HTML sections.\n"
            . "Use h2, h3, p, ul, ol, li, strong, a tags only.\n"
            . "For the services section, use an ordered list (ol) with visible numbering.\n"
            . "When listing major benefits or warnings, highlight lead phrase in <strong>.\n"
            . "Include internal links naturally in body content.\n"
            . "image_prompt must describe a marketing hero image in Arabic context with:\n"
            . "- One clear professional MALE technician only (no female), camera slightly zoomed out to show upper body and context.\n"
            . "- The male technician must be actively repairing an object directly tied to {$serviceNameAr} only.\n"
            . "- Keep the scene strictly related to {$serviceNameAr} only; do not include unrelated tools or appliances.\n"
            . "- Technician shirt must clearly contain the word TASHYIK (English) as shirt print.\n"
            . "- IMPORTANT TEXT RULE: No text overlays in the scene, no headlines, no captions, no extra words at all. The only permitted text is TASHYIK on the shirt.\n"
            . "- High-quality, sharp, premium, realistic-advertising look.\n"
            . "- Dominant brand palette based on {$brandHex} with matching violet/purple gradients and glow, and avoid off-palette colors.\n"
            . "- Clean composition suitable as blog featured image, no clutter.\n"
            . "Service data:\n"
            . json_encode([
                'service_name_ar' => $serviceNameAr,
                'service_name_en' => $serviceNameEn,
                'service_description_ar' => $service->getTranslation('description', 'ar', false),
                'service_description_en' => $service->getTranslation('description', 'en', false),
                'service_slug' => $service->slug,
                'category_name_ar' => $category?->getTranslation('name', 'ar', false),
                'category_name_en' => $category?->getTranslation('name', 'en', false),
                'category_slug' => $category?->slug,
                'internal_links' => $internalLinks,
                'client_extra_prompt' => $extraPrompt,
            ], JSON_UNESCAPED_UNICODE);
    }

    private function normalizeModelId(string $model): string
    {
        $model = trim($model);

        if ($model === '') {
            return '';
        }

        $model = preg_replace('#^models/#', '', $model) ?? $model;

        return $model;
    }

    /**
     * @return array<string, mixed>
     */
    private function generationConfig(): array
    {
        $config = [
            'maxOutputTokens' => max(8192, (int) config('services.gemini.max_output_tokens', 32768)),
            'temperature' => 0.7,
            'responseMimeType' => 'application/json',
        ];

        $thinking = config('services.gemini.thinking_budget');

        if ($thinking !== null && $thinking !== '') {
            $config['thinkingConfig'] = [
                'thinkingBudget' => (int) $thinking,
            ];
        }

        return $config;
    }

    /**
     * @param  ?\Throwable  $lastException  updated when a non-success response occurs
     * @return array<string, mixed>|null Decoded JSON body on success, or null to try next model/base.
     */
    private function requestGenerateContent(string $url, string $prompt, ?\Throwable &$lastException): ?array
    {
        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $httpResponse = Http::acceptJson()
                    ->timeout(180)
                    ->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                ],
                            ],
                        ],
                        'generationConfig' => $this->generationConfig(),
                    ]);

                if ($httpResponse->successful()) {
                    return $httpResponse->json();
                }

                $status = $httpResponse->status();
                $lastException = $httpResponse->toException();

                if ($status === 404) {
                    return null;
                }

                if (in_array($status, [503, 429, 408], true) && $attempt < $maxAttempts) {
                    usleep(random_int(1_500_000, 4_000_000));

                    continue;
                }

                if (in_array($status, [503, 429, 408], true)) {
                    return null;
                }

                throw $lastException;
            } catch (RequestException $exception) {
                $lastException = $exception;
                $status = $exception->response?->status();

                if ($status === 404) {
                    return null;
                }

                if (in_array($status, [503, 429, 408], true) && $attempt < $maxAttempts) {
                    usleep(random_int(1_500_000, 4_000_000));

                    continue;
                }

                if (in_array($status, [503, 429, 408], true)) {
                    return null;
                }

                throw $exception;
            }
        }

        return null;
    }

    /**
     * Concatenate all text parts from the first candidate (some models split output across parts).
     */
    private function extractCandidateText(array $apiResponse): string
    {
        $parts = data_get($apiResponse, 'candidates.0.content.parts', []);

        if (!is_array($parts)) {
            return '';
        }

        $chunks = [];

        foreach ($parts as $part) {
            if (!is_array($part)) {
                continue;
            }

            if (isset($part['text']) && is_string($part['text'])) {
                $chunks[] = $part['text'];
            }
        }

        return trim(implode("\n", $chunks));
    }

    /**
     * Parse model output into an array (handles markdown fences and leading/trailing prose).
     *
     * @return array<string, mixed>|null
     */
    private function decodeBlogJsonPayload(string $raw): ?array
    {
        $raw = trim($raw);

        if ($raw === '') {
            return null;
        }

        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw) ?? $raw;
        $raw = preg_replace('/\s*```\s*$/', '', $raw) ?? $raw;
        $raw = trim($raw);

        $decoded = json_decode($raw, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');

        if ($start !== false && $end !== false && $end > $start) {
            $slice = substr($raw, $start, $end - $start + 1);
            $decoded = json_decode($slice, true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}
