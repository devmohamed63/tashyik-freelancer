<?php

namespace App\View\Components\Dashboard\Cards;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Overview extends Component
{
    public string $iconColor;

    public string $iconBackgroundColor;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $style,
        public int $index,
        public string $title,
        public string $count,
        public string|null $iconClass,
        public bool|null $authorize,
        public string|null $link,
        public string $viewBox = '0 0 24 24',
    ) {
        $colorsArray = [
            1 => [
                'iconColor' => 'text-red-500 dark:text-red-100',
                'iconBgColor' => 'bg-red-50 dark:bg-red-900/70',
            ],
            2 => [
                'iconColor' => 'text-indigo-500 dark:text-indigo-100',
                'iconBgColor' => 'bg-indigo-50 dark:bg-indigo-900/70',
            ],
            3 => [
                'iconColor' => 'text-green-500 dark:text-green-100',
                'iconBgColor' => 'bg-green-50 dark:bg-green-900/70',
            ],
            4 => [
                'iconColor' => 'text-yellow-500 dark:text-yellow-100',
                'iconBgColor' => 'bg-yellow-50 dark:bg-yellow-900/70',
            ],
            5 => [
                'iconColor' => 'text-indigo-500 dark:text-indigo-100',
                'iconBgColor' => 'bg-indigo-50 dark:bg-indigo-900/70',
            ],
            6 => [
                'iconColor' => 'text-emerald-500 dark:text-emerald-100',
                'iconBgColor' => 'bg-emerald-50 dark:bg-emerald-900/70',
            ],
            7 => [
                'iconColor' => 'text-sky-500 dark:text-sky-100',
                'iconBgColor' => 'bg-sky-50 dark:bg-sky-900/70',
            ],
            8 => [
                'iconColor' => 'text-rose-500 dark:text-rose-100',
                'iconBgColor' => 'bg-rose-50 dark:bg-rose-900/70',
            ],
            9 => [
                'iconColor' => 'text-blue-500 dark:text-blue-100',
                'iconBgColor' => 'bg-blue-50 dark:bg-blue-900/70',
            ],
        ];

        $this->iconColor = $colorsArray[$index]['iconColor'];
        $this->iconBackgroundColor = $colorsArray[$index]['iconBgColor'];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $viewName = $this->style == 'col'
            ? 'column-overview-card'
            : 'row-overview-card';

        return view("components.dashboard.cards.$viewName");
    }
}
