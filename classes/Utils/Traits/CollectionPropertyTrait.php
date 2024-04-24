<?php

namespace WP_Framework\Utils\Traits;

/**
 * Trait CollectionPropertyTrait
 * Provides functionality to work with collection properties and allows implement the Iterator interface
 * ( `... implements \Iterator` ).
 */
trait CollectionPropertyTrait
{
    /**
     * @var string The name of the property that holds the collection to iterate over.
     */
    private static string $iteratable_property;

    /**
     * @var array Holds the iteration actions mapped to their corresponding iterator methods.
     */
    private array $iteration_actions = [];

    /**
     * Check if a collection property has a member with the given key.
     *
     * @param string $collection_property_name The name of the collection property.
     * @param string $member_key The key of the member to check for.
     * @param bool $throw_errors Whether to throw errors if checks fail.
     * @return bool True if the collection property has the member with the given key, false otherwise.
     */
    protected function has_collection_with_member(string $collection_property_name, string $member_key, bool $throw_errors = false): bool
    {
        if (!self::has_property($collection_property_name, $throw_errors)) {
            return false;
        }
        if (!$this->has_array_property($collection_property_name, $throw_errors)) {
            return false;
        }
        if (!isset($this->$collection_property_name[$member_key])) {
            if ($throw_errors) {
                throw new \Error("The collection '$collection_property_name' has no member named '$member_key'.");
            }
            return false;
        }
        return true;
    }

    /**
     * Check if a property exists in the class.
     *
     * @param string $property_name The name of the property to check.
     * @param bool $throw_errors Whether to throw errors if checks fail.
     * @return bool True if the property exists, false otherwise.
     */
    protected static function has_property(string $property_name, bool $throw_errors = false): bool
    {
        if (!property_exists(static::class, $property_name)) {
            if ($throw_errors) {
                throw new \Error("A property named '$property_name' does not exist in '" . static::class . "'.");
            }
            return false;
        }
    }

    /**
     * Check if a property is an array.
     *
     * @param string $property_name The name of the property to check.
     * @param bool $throw_errors Whether to throw errors if checks fail.
     * @return bool True if the property is an array, false otherwise.
     */
    protected function has_array_property(string $property_name, bool $throw_errors = false): bool
    {
        if (!is_array($this->{$property_name})) {
            if ($throw_errors) {
                throw new \Error("(" . static::class . ") \$this->$property_name is not an array");
            }
            return false;
        }
        return true;
    }

    /**
     * Define the property to be iterated over.
     *
     * @param string $property_name The name of the property to iterate over.
     * @return void
     */
    protected static function define_iteratable(string $property_name): void
    {
        static::$iteratable_property = $property_name;
    }

    /**
     * Validate the iteratable property.
     *
     * @return static The current instance.
     * @throws \Error If the iteratable property is not defined or is not an array.
     */
    private function validate_iteratable(): static
    {
        if (!isset(static::$iteratable_property)) {
            throw new \Error("To use '" . static::class . "' as \Iterator, call '" . static::class . "->define_iteratable().'");
        }
        $this->has_array_property(static::$iteratable_property, true);
        return $this;
    }

    /**
     * Add an iteration action for a specific iterator method.
     *
     * @param string $iterator_method The name of the iterator method.
     * @param callable $function_to_add The function to add.
     * @return static The current instance.
     */
    protected function add_iteration_action(string $iterator_method, callable $function_to_add): static
    {
        $this->iteration_actions[$iterator_method] = $function_to_add;
        return $this;
    }

    /**
     * Execute the iteration action for a specific iterator method.
     *
     * @param string $method_name The name of the iterator method.
     * @return static The current instance.
     */
    private function do_iteration_action(string $method_name): static
    {
        if (isset($this->iteration_actions['all'])) {
            call_user_func($this->iteration_actions[$method_name]);
        }
        if (isset($this->iteration_actions[$method_name])) {
            call_user_func($this->iteration_actions[$method_name]);
        }
        return $this;
    }

    /**
     * Rewind the iterator to the first element.
     *
     * @return void
     */
    public function rewind(): void
    {
        $this
            ->do_iteration_action('rewind')
            ->validate_iteratable();
        reset($this->{static::$iteratable_property});
    }

    /**
     * Return the key of the current element.
     *
     * @return string|int|null The key of the current element.
     */
    public function current(): mixed
    {
        $this->do_iteration_action('current');
        return current($this->{static::$iteratable_property});
    }

    /**
     * Return the key of the current element.
     *
     * @return string|int|null The key of the current element.
     */
    public function key(): string|int|null
    {
        $this->do_iteration_action('key');
        return key($this->{static::$iteratable_property});
    }

    /**
     * Move to the next element.
     *
     * @return void
     */
    public function next(): void
    {
        $this->do_iteration_action('next');
        next($this->{static::$iteratable_property});
    }

    /**
     * Check if there is a current element after calls to rewind() or next().
     *
     * @return bool True if the current element is valid, false otherwise.
     */
    public function valid(): bool
    {
        $this->do_iteration_action('valid');
        return $this->key() !== null;
    }
}
