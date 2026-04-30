<?php

namespace App\View\Components\Layouts;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;
use stdClass;

class Guest extends Component
{
    public string $locale;

    public string $dir;

    public stdClass $language;

    public stdClass $app;

    public string $title;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $page,
    ) {
        $this->locale = App::getLocale();

        $this->dir = $this->locale == 'ar'
            ? 'rtl'
            : 'ltr';

        $this->language = new stdClass();

        $this->language->name = $this->locale == 'ar'
            ? 'English'
            : 'العربية';

        $this->language->code = $this->locale == 'ar'
            ? 'en'
            : 'ar';

        $settings = Cache::get('settings');
        $icon = Cache::get('icon');

        if (!$settings || !$icon) {
            $settingsModel = \App\Models\Settings::with('media')->first();
            if ($settingsModel) {
                $settingsModel->updateCache();
                $settings = Cache::get('settings');
                $icon = Cache::get('icon');
            }
        }

        $this->app = new stdClass();
        $this->app->name = $settings?->name;
        $this->app->description = $settings?->description;
        $this->app->keywords = $settings?->keywords;
        $this->app->icon = $icon;

        $pageTitle = __('auth.' . $page . '.title');

        $this->title = "$pageTitle | {$this->app->name}";
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('layouts.guest');
    }
}
