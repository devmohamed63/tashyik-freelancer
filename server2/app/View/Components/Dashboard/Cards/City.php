<?php

namespace App\View\Components\Dashboard\Cards;

use App\Models\City as ModelsCity;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class City extends Component
{
    public string $color;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public ModelsCity $city,
    ) {

        $colorsArray = [
            'text-indigo-500 dark:text-indigo-100 bg-indigo-50 dark:bg-indigo-900/70',
            'text-green-500 dark:text-green-100 bg-green-50 dark:bg-green-900/70',
            'text-emerald-500 dark:text-emerald-100 bg-emerald-50 dark:bg-emerald-900/70',
            'text-sky-500 dark:text-sky-100 bg-sky-50 dark:bg-sky-900/70',
        ];

        $this->color = $colorsArray[array_rand($colorsArray)];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dashboard.cards.city');
    }
}
