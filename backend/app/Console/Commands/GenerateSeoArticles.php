<?php

namespace App\Console\Commands;

use App\Jobs\GenerateSeoArticleForServiceJob;
use App\Models\Article;
use App\Models\Service;
use App\Models\Settings;
use Illuminate\Console\Command;

class GenerateSeoArticles extends Command
{
    protected $signature = 'app:generate-seo-articles
                            {--limit=1 : Max jobs to dispatch now}
                            {--force : Run even if automation is off and ignore daily/monthly article caps}';

    protected $description = 'Dispatch AI blog generation jobs per service. Use --force for a mandatory run (ignores automation toggle and limits).';

    public function handle(): int
    {
        $settings = Settings::first();

        if (!$settings) {
            $this->info('Settings record not found.');
            return self::SUCCESS;
        }

        if (!$this->option('force') && !$settings->ai_blog_automation_enabled) {
            $this->info('AI blog automation disabled.');
            return self::SUCCESS;
        }

        $limit = max(1, (int) $this->option('limit'));
        $dispatched = 0;

        $services = Service::with('category')
            ->latest('id')
            ->get();

        $servicesWithoutMonthlyArticle = $services->filter(function (Service $service) {
            return !Article::where('service_id', $service->id)
                ->where('generated_by_ai', true)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->exists();
        })->values();

        $recycle = $servicesWithoutMonthlyArticle->isEmpty();

        $servicesToProcess = $recycle
            ? $services->shuffle()
            : $servicesWithoutMonthlyArticle;

        if ($recycle) {
            $this->info('All services already have an AI article this month; dispatching recycle run (random services).');
        }

        foreach ($servicesToProcess as $service) {
            if ($dispatched >= $limit) {
                break;
            }

            GenerateSeoArticleForServiceJob::dispatch($service->id, (bool) $this->option('force'), $recycle);
            $dispatched++;
        }
   //comment
        $this->info("Dispatched {$dispatched} AI SEO article job(s).");

        return self::SUCCESS;
    }
}
