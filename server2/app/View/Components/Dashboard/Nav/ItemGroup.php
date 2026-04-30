<?php

namespace App\View\Components\Dashboard\Nav;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ItemGroup extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $name,
        public array $children
    ) {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dashboard.nav.item-group');
    }
}
