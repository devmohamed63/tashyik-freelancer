<?php

namespace App\Utils\Livewire\Table;

class Dropdown
{
    /**
     * Dropdown data
     */
    protected array $data = [
        'name' => null,
        'id' => null,
        'children' => null
    ];

    /**
     * Dropdown data
     *
     * @return void
     */
    public function __construct(array $data)
    {
        // Only Replace Data that was sent to create the column and keep default data as it.
        $this->data = array_merge($this->data, $data);
    }

    /**
     * Dropdown name
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
     * Set dropdown id
     */
    public function id($id)
    {
        $this->data['id'] = $id;

        return $this;
    }

    /**
     * Set dropdown children
     */
    public function children($children)
    {
        $this->data['children'] = $children;

        return $this;
    }

    /**
     * Set column callback
     */
    public function callback($callback)
    {
        $this->data['callback'] = $callback;

        return $this;
    }

    /**
     * Set column button view
     */
    public function view($view)
    {
        $this->data['view'] = view($view);

        return $this;
    }

    /**
     * Hide column
     */
    public function hidden($condition)
    {
        $this->data['hidden'] = $condition;

        return $this;
    }

    /**
     * Set column button url
     */
    public function url($url)
    {
        $this->data['url'] = $url;

        return $this;
    }

    /**
     * Set livewire action for the column button
     */
    public function wireAction($action)
    {
        $this->data['wireAction'] = $action;

        return $this;
    }

    /**
     * Get column data
     */
    public function getData()
    {
        $data = array_map(function ($item) {

            // Children array
            if (is_array($item)) {
                $childrenArray = [];

                foreach ($item as $key => $child) {
                    array_push($childrenArray, $child->getData());
                }

                return $childrenArray;
            }

            return $item;
        }, $this->data);

        return $data;
    }
}
