<?php

namespace WP_Framework\Admin\Option;

use WP_Framework\Utils\Traits\CollectionPropertyTrait;

/**
 * Class OptionsGroup
 * Represents a group of options in WordPress.
 * Iteratable.
 */
class OptionsGroup implements \Iterator
{
    use CollectionPropertyTrait;
    /**
     * @var string The name of the options group.
     */
    public string $name;

    /**
     * @var array|null The cached values of the options group.
     */
    protected ?array $values = null;

    /**
     * OptionsGroup constructor.
     *
     * @param string $name The name of the options group.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        static::define_iteratable('values');
        $this->add_iteration_action('rewind', function () {
            $this->init_values();
        });
    }

    /**
     * Get the value of a specific option within the options group.
     *
     * @param string $option_name The name of the option.
     * @return mixed The value of the option.
     */
    public function get_value(string $option_name): mixed
    {
        $this->init_values();
        $option_name = sanitize_title($option_name);
        if (!isset($this->values[$option_name])) {
            throw new \Error("The option '{$option_name}' in group '{$this->name}' does not exist.");
        }
        return $this->values[$option_name];
    }

    public function has_values(): bool
    {
        $this->init_values();
        return (bool) $this->values;
    }

    /**
     * Get the value of a specific option within the options group.
     *
     * @param string $name The name of the option.
     * @return mixed The value of the option.
     */
    public function get_values(): array
    {
        return $this->values;
    }

    public function get_option(string $name): Option
    {
        return new Option($name, $this);
    }

    protected function init_values(): static
    {
        if (is_array($this->values)) {
            return $this;
        }
        if (!is_array(
            $values_array = get_option($this->name)
        )) {
            throw new \Error("Could not find an option group with the name '{$this->name}'");
        }
        $this->values = $values_array;
        return $this;
    }
}
