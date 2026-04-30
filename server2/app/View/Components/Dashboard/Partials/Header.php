<?php

namespace App\View\Components\Dashboard\Partials;

use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use stdClass;

class Header extends Component
{
    public User $user;

    public stdClass $language;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->language = new stdClass();

        $this->language->name = App::getLocale() == 'ar'
            ? 'English'
            : 'العربية';

        $this->language->code = App::getLocale() == 'ar'
            ? 'en'
            : 'ar';

        $this->user = Auth::user();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dashboard.partials.header');
    }
}
