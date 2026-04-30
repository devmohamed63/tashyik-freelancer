<?php

namespace App\Utils\Livewire\Table;

use Illuminate\Support\Collection;

class Button
{
    /**
     * Button data
     */
    protected array $data = [
        'name' => null,
        'label' => null,
        'wireAction' => null,
        'wireModel' => null,
        'type' => null,
        'url' => null,
        'view' => null,
        'hidden' => null,
        'target' => null
    ];

    /**
     * Button Data
     *
     * @return void
     */
    public function __construct(array $data)
    {
        // Only Replace Data that was sent to create the column and keep default data as it.
        $this->data = array_merge($this->data, $data);
    }

    /**
     * Column name
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
     * Set button type
     */
    public function type($type)
    {
        $this->data['type'] = $type;

        return $this;
    }

    /**
     * Hide button
     */
    public function hidden($condition)
    {
        $this->data['hidden'] = $condition;

        return $this;
    }

    /**
     * Set button view
     */
    public function view($view)
    {
        $this->data['view'] = view($view, [
            'name' => $this->data['name']
        ]);

        return $this;
    }

    /**
     * Set button url
     */
    public function url($url)
    {
        $this->data['url'] = $url;

        return $this;
    }

    /**
     * Set button target
     */
    public function target($target)
    {
        $this->data['target'] = $target;

        return $this;
    }

    /**
     * Set livewire action for the button
     */
    public function wireAction($action)
    {
        $this->data['wireAction'] = $action;

        return $this;
    }

    /**
     * Set livewire model for the column button
     */
    public function wireModel($model)
    {
        $this->data['wireModel'] = $model;

        return $this;
    }

    /**
     * Get button data
     */
    public function getData()
    {
        return $this->data;
    }
}
