<?php

namespace App\View\Components\Layouts;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;
use stdClass;

class Dashboard extends Component
{
    public string $locale;

    public string $dir;

    public stdClass $app;

    public string $title = '';

    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $page,
        $title = null
    ) {
        $this->locale = App::getLocale();

        $this->dir = $this->locale == 'ar'
            ? 'rtl'
            : 'ltr';

        $settings = Cache::get('settings');

        $icon = Cache::get('icon');

        $this->app = new stdClass();
        $this->app->name = $settings?->name;
        $this->app->description = $settings?->description;
        $this->app->icon = $icon;

        $pageTitle = $title ?? __("ui.$page");

        $this->title = "$pageTitle | " . __('ui.dashboard');
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('layouts.dashboard');
    }
}
