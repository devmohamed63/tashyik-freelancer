<?php

namespace App\Utils\Livewire\Table;

class Column
{
    /**
     * Column data
     */
    protected array $data = [
        'name' => null,
        'label' => null,
        'type' => null,
        'callback' => null,
        'view' => null,
        'url' => null,
        'hidden' => null,
        'wireAction' => null,
        'sortable' => null,
        'target' => null,
        'authorize' => null,
        'dateFormat' => null,
        'relation' => null,
        'customValue' => null
    ];

    /**
     * Column Data
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
    public static function name(string $modelClass, string|null $fieldLabel = null)
    {
        $label = $fieldLabel
            ? $fieldLabel
            : "validation.attributes.$modelClass";

        return new static([
            'name' => $modelClass,
            'label' => __($label)
        ]);
    }

    /**
     * Set column type to checkobx
     */
    public function checkbox()
    {
        $this->data['type'] = 'checkbox';

        return $this;
    }

    /**
     * Set column type to action
     */
    public function action()
    {
        $this->data['type'] = 'action';

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
     * Authorize column
     */
    public function authorize($condition)
    {
        $this->data['authorize'] = $condition;

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
     * Set column link target
     */
    public function target($target)
    {
        $this->data['target'] = $target;

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
     * Make column sortable
     */
    public function sortable()
    {
        $this->data['sortable'] = true;

        return $this;
    }

    /**
     * Make column date format type
     */
    public function dateFormat()
    {
        $this->data['dateFormat'] = true;

        return $this;
    }

    /**
     * Add ORM relation table and column to the column
     */
    public function relation($table, $column)
    {
        $this->data['relation'] = [$table, $column];

        return $this;
    }

    /**
     * Return custom value
     */
    public function customValue($value)
    {
        $this->data['customValue'] = $value;

        return $this;
    }

    /**
     * Get column data
     */
    public function getData()
    {
        return $this->data;
    }
}
