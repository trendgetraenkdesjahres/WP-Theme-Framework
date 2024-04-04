<?php

namespace WP_Framework\Admin\Option;

/**
 * Class OptionsGroup
 * Represents a group of options in WordPress.
 */
class OptionsGroup
{
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
    }

    /**
     * Get the value of a specific option within the options group.
     *
     * @param string $name The name of the option.
     * @return mixed The value of the option.
     */
    public function get_value(string $name): mixed
    {
        if ($this->values === null) {
            $this->values = get_option($this->name);
        }
        return $this->values[$name];
    }
}
