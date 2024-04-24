<?php

namespace WP_Framework\Admin\Option;

/**
 * Class Option
 * Represents an option in WordPress.
 */
class Option
{
    /**
     * @var string The name of the option.
     */
    public string $name;

    /**
     * @var OptionsGroup The options group to which the option belongs.
     */
    public ?OptionsGroup $group;

    /**
     * Option constructor.
     *
     * @param string $name The name of the option.
     * @param OptionsGroup|null $group The options group to which the option belongs.
     */
    public function __construct(string $name, ?OptionsGroup &$group = null)
    {
        $this->name = $name;
        $this->group = $group;
    }

    /**
     * Get the value of the option.
     *
     * @return mixed The value of the option.
     */
    public function get_value(): mixed
    {
        if (!$this->group) {
            return get_option($this->name);
        }
        return $this->group->get_value($this->name);
    }
}
