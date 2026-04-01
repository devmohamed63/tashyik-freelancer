<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Models\Settings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;
use stdClass;

class GenerateProductFeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-product-feed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Products Feed XML';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!is_dir(public_path('feed'))) {
            mkdir(public_path('feed'));
        }

        $settings = Settings::first();

        $products = Service::with([
            'media',
            'category:id,category_id,name',
            'category.parent:id,name'
        ])->get([
            'id',
            'category_id',
            'name',
            'description',
            'price',
        ]);

        // Get all languages
        $locales = config('app.available_locales');

        foreach ($locales as $locale) {

            URL::defaults(['locale' => $locale]);

            app()->setLocale($locale);

            // Get store data for the language
            $store = new stdClass();
            $store->name = $settings->getTranslation('name', $locale == 'ar' ?: 'en');
            $store->url = env('FRONTEND_URL') . "/$locale";
            $store->description = $settings->getTranslation('description', $locale);

            // Create RSS feed for the language
            $feedContent = view('vendor.products-feed', compact('store', 'products'));

            // Store RSS feed file
            file_put_contents(public_path("feed/$locale.rss"), $feedContent);
        }
    }
}
