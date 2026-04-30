<?php

namespace App\Services\Gemini;

use App\Models\AiChatConversation;
use App\Models\City;
use App\Models\User;
use App\Services\Pinecone\PineconeService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class GeminiServiceProviderChatAgent
{
    /**
     * @return array{
     *   normal_answer:bool,
     *   message:string,
     *   service_providers:array<int, array<string,mixed>>,
     *   service_provider_ids:array<int, int>,
     *   intent:string,
     *   ready_to_login:bool,
     *   login_data:array{username:string,email:string,phone:string,password:string,city_id:int|null},
     *   requires_city_selection:bool,
     *   city_options:array<int, array{id:int,name:string}>
     * }
     */
    public function reply(AiChatConversation $chat, string $userMessage, bool $isGuest): array
    {
        $chat->refresh();

        $history = $chat->messages()
            ->orderByDesc('id')
            ->limit(40)
            ->get(['sender_type', 'content'])
            ->map(fn ($message) => [
                'role' => $message->sender_type,
                'content' => $message->content,
            ])
            ->reverse()
            ->values()
            ->all();

        $loginFlowSummary = $this->formatLoginFlowSummaryForPrompt($chat);
        $analysis = $this->analyzeIntent($history, $userMessage, $isGuest, $loginFlowSummary);
        $intent = (string) ($analysis['intent'] ?? 'general_help');

        if ($isGuest && $intent !== 'recommend_provider' && $this->hasIncompleteGuestLoginFlow($chat)) {
            if ($intent === 'general_help' && ! $this->userMessageLooksLikeMaintenanceTopic($userMessage)) {
                $intent = 'login_help';
            }
        }

        if ($intent === 'login_help') {
            return $this->handleLoginHelpMission($chat, $history, $userMessage, $isGuest);
        }

        if ($intent === 'general_help') {
            return $this->handleGeneralHelpMission($history, $userMessage);
        }

        $recommendationPlan = $this->buildRecommendationPlanMission($history, $userMessage);
        $pineconeResults = $this->searchFromPinecone($recommendationPlan);
        $hits = $pineconeResults['hits'];
        $candidateChunks = $pineconeResults['candidate_chunks'];

        if ($hits === []) {
            return [
                'normal_answer' => true,
                'intent' => 'recommend_provider',
                'message' => 'للآن ما ظهر عندي مرشحين بالبحث الحالي. عطِني المدينة ونوع الخدمة بوضوح أكثر وأرشّح لك فنيين يناسبونك.',
                'service_providers' => [],
                'service_provider_ids' => [],
                'ready_to_login' => false,
                'login_data' => ['username' => '', 'email' => '', 'phone' => '', 'password' => '', 'city_id' => null],
                'requires_city_selection' => false,
                'city_options' => [],
            ];
        }

        $pickLimit = (int) ($recommendationPlan['limit'] ?? 5);

        $decision = $this->selectProvidersWithGemini(
            (string) ($recommendationPlan['search_query'] ?? $userMessage),
            $candidateChunks,
            $pickLimit
        );

        $providerIds = $decision['service_provider_ids'];
        $message = $decision['message'];

        if ($providerIds === [] && $candidateChunks !== []) {
            $providerIds = $this->uniqueProviderIdsFromCandidates($candidateChunks, $pickLimit);
            $message = $this->defaultProviderRecommendMessage();
        }

        if ($providerIds !== []) {
            $message = $this->sanitizeProviderRecommendMessage($message);
        }

        if ($providerIds === []) {
            $finalMessage = $this->sanitizeProviderRecommendMessage($message);
            if ($finalMessage === '') {
                $finalMessage = 'عطني المدينة ونوع الخدمة بشكل أوضح عشان أرشّح لك فنيين مناسبين من اللي متاحين عندنا.';
            }

            return [
                'normal_answer' => true,
                'intent' => 'recommend_provider',
                'message' => $finalMessage,
                'service_providers' => [],
                'service_provider_ids' => [],
                'ready_to_login' => false,
                'login_data' => ['username' => '', 'email' => '', 'phone' => '', 'password' => '', 'city_id' => null],
                'requires_city_selection' => false,
                'city_options' => [],
            ];
        }

        $providers = User::query()
            ->with(['city', 'categories'])
            ->whereIn('id', $providerIds)
            ->get()
            ->keyBy('id');

        $ordered = [];
        foreach ($providerIds as $providerId) {
            $provider = $providers->get($providerId);
            if (! $provider) {
                continue;
            }

            $ordered[] = [
                'id' => (int) $provider->id,
                'name' => (string) $provider->name,
                'phone' => (string) $provider->phone,
                'status' => (string) $provider->status,
                'entity_type' => (string) $provider->entity_type,
                'city' => $provider->city?->getTranslation('name', 'ar') ?? null,
                'categories' => $provider->categories
                    ->map(fn ($category) => (string) $category->getTranslation('name', 'ar'))
                    ->filter()
                    ->values()
                    ->all(),
                'avatar' => $provider->getAvatarUrl('sm'),
            ];
        }

        return [
            'normal_answer' => false,
            'intent' => 'recommend_provider',
            'message' => $message,
            'service_providers' => $ordered,
            'service_provider_ids' => $providerIds,
            'ready_to_login' => false,
            'login_data' => ['username' => '', 'email' => '', 'phone' => '', 'password' => '', 'city_id' => null],
            'requires_city_selection' => false,
            'city_options' => [],
        ];
    }

    /**
     * @param  array<int, array{role?: string, content?: string}>  $history
     * @return array<string, mixed>
     */
    private function analyzeIntent(array $history, string $userMessage, bool $isGuest, string $loginFlowSummary): array
    {
        $historyText = collect($history)->map(fn ($m) => ($m['role'] ?? 'unknown').': '.($m['content'] ?? ''))->implode("\n");
        $guestRule = $isGuest
            ? '- إذا المستخدم سأل عن تسجيل الدخول أو الحساب أو نسيت كلمة المرور أو إنشاء حساب، خل intent = "login_help".
- إذا في «حالة تسجيل الدخول المعروفة من النظام» وفيها حقول ناقصة أو ready_to_login = false، والمستخدم ما حوّل بوضوح لموضوع فني/ترشيح فني، لازم intent = "login_help" حتى لو رسالته قصيرة أو عامة (مثل: تمام، أو لا شي، أو جاري التسجيل).'
            : '- المستخدم مسجل دخول. لا ترجع login_help إلا إذا طلب مساعدة حساب بشكل صريح.';

        $prompt = <<<PROMPT
أنت مساعد دعم فني لخدمات الصيانة المنزلية في السعودية.
لازم يكون الرد دائمًا بالعربي السعودي (لهجة سعودية واضحة).
أعد JSON فقط بدون أي نص إضافي.

سجل المحادثة:
{$historyText}

رسالة المستخدم:
{$userMessage}

حالة تسجيل الدخول المعروفة من النظام (قد تكون ناقصة؛ لا تعتمد عليها وحدها بل راجع السجل):
{$loginFlowSummary}

المهمة في هذه الخطوة:
- فقط حلّل النية (intent classification) بدون تنفيذ المهمة.
- إذا السؤال عن السياق أو الذاكرة أو «فاكر كلامنا» مع وجود موضوع صيانة في السجل، غالبًا intent = "general_help".

القواعد:
- تدعم الصيانة والكهرباء والسباكة والنجارة وجميع الأعمال الفنية.
{$guestRule}
- intent لازم يكون واحد من: "login_help", "general_help", "recommend_provider".
- لا ترجع تفاصيل تنفيذ، فقط تصنيف واضح.

شكل JSON المطلوب:
{
  "intent": "general_help",
  "confidence": 0.9
}
PROMPT;

        $json = $this->callGeminiJson($prompt);
        if (is_array($json)) {
            $intent = (string) ($json['intent'] ?? 'general_help');
            if (! in_array($intent, ['login_help', 'general_help', 'recommend_provider'], true)) {
                $intent = 'general_help';
            }

            return ['intent' => $intent];
        }

        return ['intent' => 'general_help'];
    }

    private function formatLoginFlowSummaryForPrompt(AiChatConversation $chat): string
    {
        $meta = is_array($chat->meta) ? $chat->meta : [];
        $flow = is_array($meta['login_flow'] ?? null) ? $meta['login_flow'] : [];
        if ($flow === []) {
            return json_encode([
                'note' => 'لا توجد بيانات تسجيل محفوظة بعد في هذه المحادثة',
                'ready_to_login' => false,
            ], JSON_UNESCAPED_UNICODE);
        }

        $password = trim((string) ($flow['password'] ?? ''));

        return json_encode([
            'username' => (string) ($flow['username'] ?? ''),
            'email' => (string) ($flow['email'] ?? ''),
            'phone' => (string) ($flow['phone'] ?? ''),
            'password_saved' => $password !== '',
            'city_id' => $flow['city_id'] ?? null,
            'ready_to_login' => (bool) ($flow['ready_to_login'] ?? false),
        ], JSON_UNESCAPED_UNICODE);
    }

    private function hasIncompleteGuestLoginFlow(AiChatConversation $chat): bool
    {
        $meta = is_array($chat->meta) ? $chat->meta : [];
        $flow = is_array($meta['login_flow'] ?? null) ? $meta['login_flow'] : [];
        if ($flow === []) {
            return false;
        }

        if ((bool) ($flow['ready_to_login'] ?? false)) {
            return false;
        }

        $username = trim((string) ($flow['username'] ?? ''));
        $email = trim((string) ($flow['email'] ?? ''));
        $phone = trim((string) ($flow['phone'] ?? ''));
        $password = trim((string) ($flow['password'] ?? ''));
        $cityId = (int) ($flow['city_id'] ?? 0);

        return $username !== '' || $email !== '' || $phone !== '' || $password !== '' || $cityId > 0;
    }

    private function userMessageLooksLikeMaintenanceTopic(string $userMessage): bool
    {
        $haystack = mb_strtolower($userMessage);

        $needles = [
            'فني', 'صيانة', 'تكييف', 'مكيف', 'سباكة', 'سباك', 'كهرباء', 'كهربائي', 'نجار', 'نجارة',
            'عطل', 'إصلاح', 'اصلاح', 'ترشيح', 'أرشح', 'ارشح', 'تسريب', 'سخان', 'دينمو', 'ثلاجة',
            'غسالة', 'أعمال فنية', 'اعمال فنية',
        ];

        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string,mixed>  $analysis
     * @return array{hits:array<int,mixed>, candidate_chunks:array<int,array<string,mixed>>}
     */
    private function searchFromPinecone(array $analysis): array
    {
        /** @var PineconeService $pinecone */
        $pinecone = app(PineconeService::class);
        $filters = is_array($analysis['filters'] ?? null) ? $analysis['filters'] : [];
        $textField = (string) config('services.pinecone.record_text_key', 'text');

        $filter = ['type' => ['$eq' => 'service_provider']];
        $status = (string) ($filters['status'] ?? 'active');
        if ($status !== '') {
            $filter['status'] = ['$eq' => $status];
        }

        $cityId = (int) ($filters['city_id'] ?? 0);
        if ($cityId > 0) {
            $filter['city_id'] = ['$eq' => $cityId];
        }

        $categoryIds = is_array($filters['category_ids'] ?? null) ? $filters['category_ids'] : [];
        $categoryIds = array_values(array_filter(array_map(static fn ($value) => (int) $value, $categoryIds), static fn ($value) => $value > 0));
        if ($categoryIds !== []) {
            $filter['category_ids'] = ['$in' => array_map('strval', $categoryIds)];
        }

        $extra = [
            'fields' => [
                'provider_id',
                'provider_name',
                'city_name',
                'category_names',
                'average_rating',
                'reviews_count',
                'completed_orders_count',
                'top_worked_city_name',
                $textField,
            ],
            'filter' => $filter,
        ];

        $query = (string) ($analysis['search_query'] ?? '');
        $topK = max(8, min(40, (int) ($analysis['limit'] ?? 5) * 4));
        $response = $pinecone->searchRecords($query !== '' ? $query : 'Home maintenance technicians', $topK, $extra);
        $hits = is_array($response['result']['hits'] ?? null) ? $response['result']['hits'] : [];

        $candidateChunks = [];
        foreach ($hits as $hit) {
            $fields = is_array($hit['fields'] ?? null) ? $hit['fields'] : [];
            $providerId = $fields['provider_id'] ?? ($hit['_id'] ?? null);
            if (! $providerId) {
                continue;
            }

            $candidateChunks[] = [
                'provider_id' => (int) $providerId,
                'provider_name' => (string) ($fields['provider_name'] ?? ''),
                'city_name' => (string) ($fields['city_name'] ?? ''),
                'category_names' => is_array($fields['category_names'] ?? null) ? implode(', ', $fields['category_names']) : '',
                'average_rating' => $fields['average_rating'] ?? null,
                'reviews_count' => $fields['reviews_count'] ?? null,
                'completed_orders_count' => $fields['completed_orders_count'] ?? null,
                'top_worked_city_name' => (string) ($fields['top_worked_city_name'] ?? ''),
                'text' => (string) ($fields[$textField] ?? ''),
            ];
        }

        return ['hits' => $hits, 'candidate_chunks' => $candidateChunks];
    }

    /**
     * @param  array<int,array<string,mixed>>  $candidateChunks
     * @return array{message:string, service_provider_ids:array<int,int>}
     */
    private function selectProvidersWithGemini(string $query, array $candidateChunks, int $limit): array
    {
        $candidateText = collect($candidateChunks)->take(24)->map(function (array $c): string {
            return "provider_id={$c['provider_id']}\nname={$c['provider_name']}\ncity={$c['city_name']}\ncategories={$c['category_names']}\nrating={$c['average_rating']}\nreviews={$c['reviews_count']}\ncompleted_orders={$c['completed_orders_count']}\ntext={$c['text']}";
        })->implode("\n\n");

        $prompt = <<<PROMPT
أنت شخصية مساعدة عملاء محترفة في منصة صيانة منزلية بالسعودية: دورك تربط العميل بفنيين مسجلين في المنصة (أخصائيين/فنيين)، وتتصرف كمستشار مبيعات خدمة بأسلوب لبق — هدفك ترشيح فنيين مناسبين من القائمة، مو رفض الطلب.
لازم يكون الرد دائمًا بالعربي السعودي (لهجة سعودية واضحة).
أعد JSON فقط بدون أي نص إضافي.

طلب المستخدم:
{$query}

المرشحين (من محرك البحث؛ رتبهم حسب أهمية النتيجة):
{$candidateText}

شكل JSON المطلوب:
{
  "message": "رد قصير مفيد",
  "service_provider_ids": [1,2,3]
}

القواعد:
- IDs لازم تكون من قائمة المرشحين فقط.
- ممنوع تقول إنكم «ما لقيتوا» أو «ما في فني» أو «ما عندنا بتقييم ممتاز» أو أي صيغة رفض لأن المستخدم طلب تقييم عالٍ: لازم ترشّح أفضل المتاحين من القائمة فعليًا (حتى لو التقييم في البيانات مو مكتوب «ممتاز»).
- إذا طلب المستخدم «ممتاز» أو «أعلى تقييم»: اختر الأقوى من المرشحين حسب rating/reviews/orders في البيانات، وفي الرسالة قدّمهم بإيجابية بدون وعد تقييم ما هو مذكور صراحة.
- ممنوع إرجاع service_provider_ids فارغة طالما فيه مرشح واحد على الأقل في القائمة.
- الرسالة عملية ومختصرة وتشجّع على المقارنة والتواصل مع الفنيين.
- message لازم تكون بالعربي السعودي فقط.
PROMPT;

        $json = $this->callGeminiJson($prompt);
        if (! is_array($json)) {
            return ['message' => '', 'service_provider_ids' => []];
        }

        $ids = is_array($json['service_provider_ids'] ?? null) ? $json['service_provider_ids'] : [];
        $ids = array_values(array_unique(array_filter(array_map(static fn ($value) => (int) $value, $ids), static fn ($value) => $value > 0)));
        $ids = array_slice($ids, 0, max(1, $limit));

        return [
            'message' => (string) ($json['message'] ?? ''),
            'service_provider_ids' => $ids,
        ];
    }

    /**
     * @param  array<int, array<string,mixed>>  $candidateChunks
     * @return array<int, int>
     */
    private function uniqueProviderIdsFromCandidates(array $candidateChunks, int $limit): array
    {
        $limit = max(1, min(10, $limit));
        $seen = [];
        $ids = [];
        foreach ($candidateChunks as $chunk) {
            $id = (int) ($chunk['provider_id'] ?? 0);
            if ($id <= 0 || isset($seen[$id])) {
                continue;
            }
            $seen[$id] = true;
            $ids[] = $id;
            if (count($ids) >= $limit) {
                break;
            }
        }

        return $ids;
    }

    private function defaultProviderRecommendMessage(): string
    {
        return 'أنا هنا أساعدك تتواصل مع فنيين محترفين في المنصة. هذي أقوى الخيارات المتاحة حسب بحثك، قارن بينهم وتواصل مع اللي يناسبك.';
    }

    private function sanitizeProviderRecommendMessage(string $message): string
    {
        $trimmed = trim($message);
        if ($trimmed === '') {
            return '';
        }

        $needles = [
            'ما لقينا', 'ما لقيت', 'لم نجد', 'ما في فني', 'مافي فني', 'ما فيه فني',
            'لا يوجد فني', 'ما وجدنا', 'غير متوفر', 'لا نملك', 'ما عندنا فني', 'مافي أحد',
            'ما لقينا فني', 'ما في كهربائي', 'ما في سباك',
        ];

        $lower = mb_strtolower($trimmed);
        foreach ($needles as $needle) {
            if (mb_strpos($lower, mb_strtolower($needle)) !== false) {
                return $this->defaultProviderRecommendMessage();
            }
        }

        if (
            (mb_strpos($lower, 'تقييم ممتاز') !== false || mb_strpos($lower, 'تقييم عالي') !== false)
            && (mb_strpos($lower, 'ما ') !== false || mb_strpos($lower, 'مافي') !== false || mb_strpos($lower, 'لا يوجد') !== false)
        ) {
            return $this->defaultProviderRecommendMessage();
        }

        return $trimmed;
    }

    /**
     * @param  array<int, array{role?: string, content?: string}>  $history
     */
    private function fallbackGeneralHelpFromHistory(array $history, string $userMessage): string
    {
        $userLines = [];
        foreach ($history as $m) {
            if (($m['role'] ?? '') !== 'user') {
                continue;
            }
            $c = trim((string) ($m['content'] ?? ''));
            if ($c === '') {
                continue;
            }
            $userLines[] = $c;
        }

        if ($userLines !== [] && end($userLines) === trim($userMessage)) {
            array_pop($userLines);
        }

        $topic = $userLines !== [] ? $userLines[array_key_last($userLines)] : '';
        if ($topic !== '') {
            $snippet = Str::limit($topic, 220);

            return "أي، فاكر سياقنا: كنت تتكلم عن «{$snippet}». لو لسه عندك نفس المشكلة وصف لي بالضبط إيش صار بعد آخر خطوة جربتها، وبنكمّل من هناك.";
        }

        return 'هلا، اشرح لي المشكلة بشكل أوضح وبعطيك الخطوات المناسبة.';
    }

    /**
     * @param  array<int, array{role?: string, content?: string}>  $history
     * @return array{
     *   normal_answer:bool,
     *   message:string,
     *   service_providers:array<int, array<string,mixed>>,
     *   service_provider_ids:array<int, int>,
     *   intent:string,
     *   ready_to_login:bool,
     *   login_data:array{username:string,email:string,phone:string,password:string,city_id:int|null},
     *   requires_city_selection:bool,
     *   city_options:array<int, array{id:int,name:string}>
     * }
     */
    private function handleGeneralHelpMission(array $history, string $userMessage): array
    {
        $historyText = collect($history)->map(fn ($m) => ($m['role'] ?? 'unknown').': '.($m['content'] ?? ''))->implode("\n");
        $prompt = <<<PROMPT
أنت خبير صيانة منزلية في السعودية.
رد بالعربي السعودي فقط.
هذه خطوة تنفيذ الرد العام (بعد تحليل النية).
أعد JSON فقط.

سجل المحادثة (من الأقدم للأحدث؛ لازم تبني عليه):
{$historyText}

رسالة المستخدم الحالية:
{$userMessage}

قواعد مهمة:
- لازم تراعي سجل المحادثة كاملًا؛ إذا سأل عن الذاكرة أو السياق أو «فاكر»، أكد أنك تذكر ما ذكره في السجل واربط إجابتك به مباشرة.
- لا تبدأ من الصفر كأن المحادثة جديدة إذا فيه موضوع متابع في السجل.

أعد JSON بهذا الشكل:
{
  "assistant_message": "رد مختصر ومفيد باللهجة السعودية"
}
PROMPT;

        $json = $this->callGeminiJson($prompt);
        $message = is_array($json) ? (string) ($json['assistant_message'] ?? '') : '';
        if (trim($message) === '') {
            $message = $this->fallbackGeneralHelpFromHistory($history, $userMessage);
        }

        return [
            'normal_answer' => true,
            'intent' => 'general_help',
            'message' => $message,
            'service_providers' => [],
            'service_provider_ids' => [],
            'ready_to_login' => false,
            'login_data' => ['username' => '', 'email' => '', 'phone' => '', 'password' => '', 'city_id' => null],
            'requires_city_selection' => false,
            'city_options' => [],
        ];
    }

    /**
     * @param  array<int, array{role?: string, content?: string}>  $history
     * @return array<string,mixed>
     */
    private function buildRecommendationPlanMission(array $history, string $userMessage): array
    {
        $historyText = collect($history)->map(fn ($m) => ($m['role'] ?? 'unknown').': '.($m['content'] ?? ''))->implode("\n");
        $prompt = <<<PROMPT
أنت مساعد ترشيح فنيين في السعودية.
هذه خطوة تنفيذ خطة البحث بعد تحليل النية.
أعد JSON فقط.

سجل المحادثة:
{$historyText}

رسالة المستخدم:
{$userMessage}

استخرج خطة بحث واضحة:
{
  "search_query": "صياغة بحث مختصرة بالعربي",
  "filters": {
    "city_id": null,
    "category_ids": [],
    "status": "active"
  },
  "limit": 5
}

مهم:
- إذا ما عندك city_id معروف بالنظام، خله null.
- category_ids أرقام لو متاحة وإلا [].
- status الافتراضي active.
PROMPT;

        $json = $this->callGeminiJson($prompt);
        if (! is_array($json)) {
            return [
                'search_query' => $userMessage,
                'filters' => ['city_id' => null, 'category_ids' => [], 'status' => 'active'],
                'limit' => 5,
            ];
        }

        return [
            'search_query' => (string) ($json['search_query'] ?? $userMessage),
            'filters' => is_array($json['filters'] ?? null) ? $json['filters'] : ['city_id' => null, 'category_ids' => [], 'status' => 'active'],
            'limit' => max(1, min(10, (int) ($json['limit'] ?? 5))),
        ];
    }

    /**
     * @param  array<int, array{role?: string, content?: string}>  $history
     * @return array{
     *   normal_answer:bool,
     *   message:string,
     *   service_providers:array<int, array<string,mixed>>,
     *   service_provider_ids:array<int, int>,
     *   intent:string,
     *   ready_to_login:bool,
     *   login_data:array{username:string,email:string,phone:string,password:string,city_id:int|null},
     *   requires_city_selection:bool,
     *   city_options:array<int, array{id:int,name:string}>
     * }
     */
    private function handleLoginHelpMission(AiChatConversation $chat, array $history, string $userMessage, bool $isGuest): array
    {
        $historyText = collect($history)->map(fn ($m) => ($m['role'] ?? 'unknown').': '.($m['content'] ?? ''))->implode("\n");
        $currentMeta = is_array($chat->meta ?? null) ? $chat->meta : [];
        $currentLogin = is_array($currentMeta['login_flow'] ?? null) ? $currentMeta['login_flow'] : [];
        $priorSummary = $this->formatLoginFlowSummaryForPrompt($chat);
        $userHistoryText = $this->flattenUserHistory($history);

        $prompt = <<<PROMPT
أنت مساعد تسجيل دخول لتطبيق صيانة.
هذه خطوة تنفيذ login_help بعد تحليل النية.
أعد JSON فقط.

سجل المحادثة (راجع كل رسائل user لاستخراج البيانات):
{$historyText}

آخر رسالة من المستخدم:
{$userMessage}

ملخص بيانات مدمجة مسبقًا من النظام (قد يكون ناقصًا؛ أكمل من السجل):
{$priorSummary}

المهمة:
- املأ كائن login_data كاملًا من سجل المحادثة بالكامل، وليس من آخر رسالة فقط.
- أي حقل ظهر في السابق (اسم مستخدم، إيميل، جوال سعودي، كلمة مرور، رقم مدينة) لازم يظهر في login_data إذا كان واضحًا من السجل.
- إذا الحقل غير مذكور في السجل اتركه فارغًا أو city_id = null.
- لا تخترع بيانات. لا تعدّ ردود المساعد (ai) حقائق عن المستخدم إلا إذا أعاد نقل كلام المستخدم.
- assistant_message: رد عملي قصير بالعربي السعودي؛ لا تقل إنك «تنفّذ تسجيل الدخول» أو «جاري الدخول» إلا إذا النظام أكد اكتمال البيانات (سيتم التحقق لاحقًا).

الحقول:
- username, email, phone (سعودي فقط), password, city_id (رقم مدينة من التطبيق إن وُجد في السجل)

أعد JSON:
{
  "assistant_message": "رد عربي سعودي",
  "login_data": {
    "username": "",
    "email": "",
    "phone": "",
    "password": "",
    "city_id": null
  }
}
PROMPT;

        $json = $this->callGeminiJson($prompt);
        $extracted = is_array($json['login_data'] ?? null) ? $json['login_data'] : [];

        $username = $this->pickNonEmptyString($extracted['username'] ?? null, (string) ($currentLogin['username'] ?? ''));
        $email = strtolower($this->pickNonEmptyString($extracted['email'] ?? null, (string) ($currentLogin['email'] ?? '')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = '';
        }

        $aiPhone = $this->normalizeSaudiPhone((string) ($extracted['phone'] ?? ''));
        $messagePhone = $this->extractSaudiPhoneFromText($userMessage);
        $historyPhone = $this->extractSaudiPhoneFromText($userHistoryText);
        $storedPhone = $this->normalizeSaudiPhone((string) ($currentLogin['phone'] ?? ''));
        $phone = $aiPhone !== '' ? $aiPhone : ($messagePhone !== '' ? $messagePhone : ($historyPhone !== '' ? $historyPhone : $storedPhone));

        $password = $this->pickNonEmptyString($extracted['password'] ?? null, (string) ($currentLogin['password'] ?? ''));
        $cityId = $this->resolveCityId($extracted['city_id'] ?? null, $currentLogin['city_id'] ?? null, $userMessage, $userHistoryText);
        if ($cityId <= 0 || ! City::query()->whereKey($cityId)->exists()) {
            $cityId = 0;
        }

        $ready = $isGuest && $username !== '' && $email !== '' && $phone !== '' && $password !== '' && $cityId > 0;
        $requiresCitySelection = ! $ready && $cityId <= 0;
        $cityOptions = $requiresCitySelection ? $this->getCityOptions() : [];

        $currentMeta['login_flow'] = [
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'city_id' => $cityId > 0 ? $cityId : null,
            'ready_to_login' => $ready,
        ];
        $chat->meta = $currentMeta;
        $chat->save();

        $message = is_array($json) ? (string) ($json['assistant_message'] ?? '') : '';
        if (! $ready && (
            str_contains($message, 'اكتملت بياناتك')
            || str_contains($message, 'جاري تسجيل الدخول')
            || str_contains($message, 'جاري الدخول')
            || str_contains($message, 'ما أحتاج شي')
        )) {
            $message = '';
        }
        if (trim($message) === '') {
            $message = $ready
                ? 'تمام، كذا بيانات الدخول مكتملة. تقدر تسجل دخولك الآن.'
                : 'أكيد، أرسل بياناتك كاملة (الاسم، الإيميل، رقم الجوال السعودي، كلمة المرور، والمدينة) عشان أجهز تسجيل الدخول.';
        }

        return [
            'normal_answer' => true,
            'intent' => 'login_help',
            'message' => $message,
            'service_providers' => [],
            'service_provider_ids' => [],
            'ready_to_login' => $ready,
            'login_data' => [
                'username' => $username,
                'email' => $email,
                'phone' => $phone,
                'password' => $password,
                'city_id' => $cityId > 0 ? $cityId : null,
            ],
            'requires_city_selection' => $requiresCitySelection,
            'city_options' => $cityOptions,
        ];
    }

    private function normalizeSaudiPhone(string $phone): string
    {
        $raw = trim($phone);
        if ($raw === '') {
            return '';
        }

        $normalized = preg_replace('/[^\d\+]/', '', $raw) ?? '';

        if (preg_match('/^\+9665\d{8}$/', $normalized) === 1) {
            return $normalized;
        }

        if (preg_match('/^009665\d{8}$/', $normalized) === 1) {
            return '+'.substr($normalized, 2);
        }

        if (preg_match('/^9665\d{8}$/', $normalized) === 1) {
            return '+'.$normalized;
        }

        if (preg_match('/^05\d{8}$/', $normalized) === 1) {
            return '+966'.substr($normalized, 1);
        }

        if (preg_match('/^5\d{8}$/', $normalized) === 1) {
            return '+966'.$normalized;
        }

        return '';
    }

    private function extractSaudiPhoneFromText(string $text): string
    {
        preg_match_all('/(?:\+966|00966|966|0)?5\d{8}/u', $text, $matches);
        $candidates = is_array($matches[0] ?? null) ? $matches[0] : [];

        foreach ($candidates as $candidate) {
            $normalized = $this->normalizeSaudiPhone((string) $candidate);
            if ($normalized !== '') {
                return $normalized;
            }
        }

        return '';
    }

    private function resolveCityId(mixed $aiCityId, mixed $storedCityId, string $userMessage, string $historyUserText = ''): int
    {
        $aiCandidate = (int) (is_numeric($aiCityId) ? $aiCityId : 0);
        if ($aiCandidate > 0 && City::query()->whereKey($aiCandidate)->exists()) {
            return $aiCandidate;
        }

        $messageCityId = $this->extractCityIdFromText($userMessage);
        if ($messageCityId > 0) {
            return $messageCityId;
        }

        if ($historyUserText !== '') {
            $historyCityId = $this->extractCityIdFromText($historyUserText);
            if ($historyCityId > 0) {
                return $historyCityId;
            }
        }

        $storedCandidate = (int) (is_numeric($storedCityId) ? $storedCityId : 0);

        return $storedCandidate > 0 ? $storedCandidate : 0;
    }

    /**
     * @param  array<int, array{role?: string, content?: string}>  $history
     */
    private function flattenUserHistory(array $history): string
    {
        return collect($history)
            ->filter(fn ($m) => ($m['role'] ?? '') === 'user')
            ->map(fn ($m) => (string) ($m['content'] ?? ''))
            ->implode("\n");
    }

    private function extractCityIdFromText(string $text): int
    {
        if (preg_match('/\b(\d{1,3})\b/u', $text, $numberMatch) === 1) {
            $candidate = (int) ($numberMatch[1] ?? 0);
            if ($candidate > 0 && City::query()->whereKey($candidate)->exists()) {
                return $candidate;
            }
        }

        $normalizedInput = preg_replace('/\s+/u', ' ', trim($text)) ?? '';
        if ($normalizedInput === '') {
            return 0;
        }

        $cities = City::query()->get(['id', 'name']);
        foreach ($cities as $city) {
            $arabicName = (string) $city->getTranslation('name', 'ar');
            if ($arabicName === '') {
                continue;
            }

            if (mb_stripos($normalizedInput, $arabicName) !== false) {
                return (int) $city->id;
            }
        }

        return 0;
    }

    private function pickNonEmptyString(mixed $candidate, string $fallback): string
    {
        $candidateText = trim((string) ($candidate ?? ''));
        if ($candidateText !== '') {
            return $candidateText;
        }

        return trim($fallback);
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    private function getCityOptions(): array
    {
        return City::query()
            ->orderBy('item_order')
            ->orderBy('id')
            ->get()
            ->map(fn (City $city) => [
                'id' => (int) $city->id,
                'name' => (string) $city->getTranslation('name', 'ar'),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string,mixed>|null
     */
    private function callGeminiJson(string $prompt): ?array
    {
        $apiKey = (string) config('services.gemini.api_key');
        if ($apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY is not set.');
        }

        $model = (string) env('GEMINI_MODEL', 'gemini-2.5-flash');
        $payload = [
            'contents' => [[
                'role' => 'user',
                'parts' => [['text' => $prompt]],
            ]],
            'generationConfig' => [
                'temperature' => 0.2,
                'responseMimeType' => 'application/json',
            ],
        ];

        $response = null;
        $attempts = 5;
        $backoffMs = [400, 1000, 2200, 4500, 8000];

        for ($i = 0; $i < $attempts; $i++) {
            try {
                $response = Http::timeout(120)
                    ->connectTimeout(10)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post(
                        "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                        $payload
                    );
            } catch (Throwable $throwable) {
                Log::warning('Gemini call exception in service provider chat', [
                    'attempt' => $i + 1,
                    'message' => $throwable->getMessage(),
                ]);
                $response = null;
            }

            if ($response && $response->successful()) {
                break;
            }

            $status = $response?->status();
            if ($status !== null && in_array($status, [400, 401, 403, 404], true)) {
                Log::error('Gemini non-retriable error in service provider chat', [
                    'status' => $status,
                    'body' => $response?->body(),
                ]);

                return null;
            }

            if ($status !== null && ($status === 429 || $status === 503 || $status >= 500)) {
                Log::warning('Gemini overloaded or unavailable; retrying', [
                    'attempt' => $i + 1,
                    'status' => $status,
                ]);
            }

            if ($i < $attempts - 1) {
                usleep(($backoffMs[$i] ?? 2000) * 1000);
            }
        }

        if (! $response || $response->failed()) {
            Log::error('Gemini unavailable in service provider chat', [
                'status' => $response?->status(),
                'body' => $response?->body(),
            ]);

            return null;
        }

        $text = $response->json('candidates.0.content.parts.0.text');
        if (! is_string($text) || trim($text) === '') {
            return null;
        }

        $json = json_decode($text, true);

        return is_array($json) ? $json : null;
    }
}
