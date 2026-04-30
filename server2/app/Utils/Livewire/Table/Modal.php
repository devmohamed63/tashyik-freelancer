<?php

namespace App\Utils\Livewire\Table;

class Modal
{
    /**
     * Modal data
     */
    protected array $data = [
        'id' => null,
        'view' => null,
        'hidden' => null,
    ];

    /**
     * Modal data
     *
     * @return void
     */
    public function __construct(array $data)
    {
        // Only Replace Data that was sent to create the column and keep default data as it.
        $this->data = array_merge($this->data, $data);
    }

    /**
     * Modal id
     */
    public static function id($id)
    {
        return new static([
            'id' => $id,
        ]);
    }

    /**
     * Hide modal
     */
    public function hidden($condition)
    {
        $this->data['hidden'] = $condition;

        return $this;
    }

    /**
     * Set modal view
     */
    public function view($view)
    {
        $this->data['view'] = $view;

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
