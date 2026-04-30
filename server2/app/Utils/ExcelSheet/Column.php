<?php

namespace App\Utils\ExcelSheet;

class Column
{
    /**
     * Column data
     */
    protected array $data = [
        'name' => null,
        'label' => null,
        'callback' => null,
        'dateFormat' => null,
        'relation' => null,
        'customValue' => null,
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
    public static function name(string $modelClass, string|null $label = null): static
    {
        $label = $label ?: "validation.attributes.$modelClass";

        return new static([
            'name' => $modelClass,
            'label' => __($label)
        ]);
    }

    /**
     * Set column callback
     */
    public function callback($callback): static
    {
        $this->data['callback'] = $callback;

        return $this;
    }

    /**
     * Make column date format type
     */
    public function dateFormat(): static
    {
        $this->data['dateFormat'] = true;

        return $this;
    }

    /**
     * Add ORM relation table and column to the column
     */
    public function relation($table, $column): static
    {
        $this->data['relation'] = [$table, $column];

        return $this;
    }

    /**
     * Return custom value
     */
    public function customValue($value): static
    {
        $this->data['customValue'] = $value;

        return $this;
    }

    /**
     * Get column data
     */
    public function getData(): array
    {
        return $this->data;
    }
}
