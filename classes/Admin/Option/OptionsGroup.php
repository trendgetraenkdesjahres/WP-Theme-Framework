<?php

namespace WP_Framework\Admin\Option;

use WP_Framework\Debug\Debug;
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
    public function get_value(string $option_name, bool $throw_errors = true): mixed
    {
        $this->init_values($throw_errors);
        $option_name = sanitize_title($option_name);
        if (!isset($this->values[$option_name])) {
            if (!$throw_errors) {
                return null;
            }
            throw new \Error("The option '{$option_name}' in group '{$this->name}' does not exist.");
        }
        return $this->values[$option_name];
    }

    public function has_values(): bool
    {
        $this->init_values(false);
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

    public function rename_option(string $old_name, string $new_name): static
    {
        $value = $this->get_value($old_name);
        $this->add_option($new_name, $value)
            ->unset_option($old_name);
        return $this;
    }

    public function unset_option(string $name): bool
    {
        if (!isset($this->values[$name])) {
            return false;
        }
        unset($this->values[$name]);
        return delete_option($this->name, $this->values);
    }

    public function add_option(string $option, mixed $value, bool $throw_errors = true): static
    {
        $this->init_values($throw_errors);
        if (isset($this->values[$option])) {
            throw new \Error("The option '{$this->name}' is already set.");
        }
        $this->values[$option] = $value;
        return $this;
    }

    // does not throw any error
    public function update_option(string $option, mixed $value): static
    {
        $this->init_values(false);
        $this->values[$option] = $value;
        update_option($this->name, $this->values);
        return $this;
    }

    protected function init_values(bool $throw_errors = true): static
    {
        if (is_array($this->values)) {
            return $this;
        }
        if (
            $this->name != 'general' &&
            !is_array($values_array = get_option($this->name))
        ) {
            if ($throw_errors) {
                throw new \Error("Could not find an option group with the name '{$this->name}'");
            }
            return $this;
        }
        $this->values = $values_array;
        return $this;
    }
}
