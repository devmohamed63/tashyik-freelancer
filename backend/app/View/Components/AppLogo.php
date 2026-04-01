<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

class AppLogo extends Component
{
    public string|null $lightMode;

    public string|null $darkMode;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->lightMode = Cache::get('light_mode_logo');

        $this->darkMode = Cache::get('dark_mode_logo');
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.app-logo');
    }
}
