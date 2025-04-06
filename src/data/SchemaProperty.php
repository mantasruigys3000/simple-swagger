<?php

namespace Mantasruigys3000\SimpleSwagger\data;

use function PHPUnit\Framework\isInstanceOf;

class SchemaProperty
{
    public string $name;
    public string $type;
    public ?string $format;
    public bool $nullable = false;

    public ?int $minLength;
    public ?int $maxLength;

    public ?int $min;
    public ?int $max;

    public bool $required = false;
    public mixed $example = '';
    public string $description;
    public array $refs = [];
    public string $resource;
    public SchemaFactory $schema;
    public $items = [];
    public $enum = [];

    /**
     * Mark field as required
     *
     * @return $this
     */
    public function required() : self
    {
        $this->required = true;
        return $this;
    }

    /**
     * Set min length of string
     *
     * @return void
     */
    public function minLength(int $min) : self
    {
        $this->minLength = $min;
        return $this;
    }

    /**
     * Set max length of string
     *
     * @param int $max
     * @return void
     */
    public function maxLength(int $max) : self
    {
        $this->maxLength = $max;
        return $this;
    }

    /**
     * Set min boundary of a number type
     *
     * @param int $min
     * @return $this
     */
    public function min(int $min) : self
    {
        $this->min = $min;
        return $this;
    }

    /**
     * Set max boundary of a number type
     *
     * @param int $min
     * @return $this
     */
    public function max(int $max) : self
    {
        $this->max = $max;
        return $this;
    }

    /**
     * Make this an enum type
     *
     * @param array $values
     * @return $this
     */
    public function enum(array $values) : self
    {
        $this->enum = $values;
        return $this;
    }

    /**
     * @param bool $nullable
     * @return $this
     */
    public function nullable(bool $nullable = true) : self
    {
        $this->nullable = $nullable;
        return $this;
    }

    /**
     * Format Helpers
     */

    /**
     * Manually set format
     *
     * @param string $format
     * @return $this
     */
    public function format(string $format) : self
    {
        $this->format = $format;
        return $this;
    }

    /**
     * UUID format
     *
     * @return $this
     */
    public function uuid() : self
    {
        return $this->format('uuid');
    }

    /**
     * Email format
     *
     * @return $this
     */
    public function email() : self
    {
        return $this->format('email');
    }

    /**
     * Overwrite example
     *
     * @param mixed $example
     * @return $this
     */
    public function example(mixed $example) : self
    {
        $this->example = $example;
        return $this;
    }

    /**
     * Converts the constructed property type into an array type
     *
     * @return $this
     */
    public function array()
    {
        $arrayType = $this->type;

        $this->type = 'array';
        $this->items = [
            'type' => $arrayType,
        ];

        if (isset($this->format)){
            $this->items['format'] = $this->format;
        }

        $this->format = null;
        $this->example = [$this->example];

        if (isset($this->schema))
        {
            $this->items['properties'] = $this->schema->getPropertiesArray();
            $this->example = [
                $this->schema->getExampleArray('')
            ];
        }

        unset($this->schema);
        return $this;
    }

}
