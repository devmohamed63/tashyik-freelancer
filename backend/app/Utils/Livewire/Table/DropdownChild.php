<?php

namespace App\Utils\Livewire\Table;

class DropdownChild
{
    /**
     * Dropdown child data
     */
    protected array $data = [
        'name' => null,
        'wireAction' => null
    ];

    /**
     * Dropdown child data
     *
     * @return void
     */
    public function __construct(array $data)
    {
        // Only Replace Data that was sent to create the column and keep default data as it.
        $this->data = array_merge($this->data, $data);
    }

    /**
     * Child name
     */
    public static function name(string $name, string|null $label = null)
    {
        $label = $label
            ? $label
            : "ui.$name";

        return new static([
            'name' => $name,
            'label' => __($label)
        ]);
    }

    /**
     * Set livewire action for the child
     */
    public function wireAction($action)
    {
        $this->data['wireAction'] = $action;

        return $this;
    }

    /**
     * Get child data
     */
    public function getData()
    {
        return $this->data;
    }
}
