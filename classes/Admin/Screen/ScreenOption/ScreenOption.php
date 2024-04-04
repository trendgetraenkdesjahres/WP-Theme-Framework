<?php

namespace WP_Framework\Admin\Screen\ScreenOption;


/**
 * Class ScreenOption
 * Represents a screen option configuration.
 */
class ScreenOption
{
    /**
     * @var string $name The key of the screen option.
     */
    public string $name;

    /**
     * @var string $label The label of the screen option.
     */
    public string $label;

    /**
     * @var string|int $default The default value of the screen option.
     */
    public string|int $default;

    /**
     * Constructor for the ScreenOption class.
     *
     * @param string $label The label for the screen option.
     * @param string|int $default The default value for the screen option.
     * @param string|null $name The key for the screen option. If not provided, a sanitized version of the label will be used.
     */
    public function __construct(string $label, string|int $default, ?string $name = null)
    {
        if (!$name) {
            $name = sanitize_key(str_replace(' ', '_', $label));
        }
        $this->name = $name;
        $this->label = $label;
        $this->default = $default;
    }

    /**
     * Get the arguments array for registering the screen option.
     *
     * @return array The arguments array for registering the screen option.
     */
    public function get_args(): array
    {
        return [
            'label' => $this->label,
            'default' => $this->default,
            'option' => $this->name,
        ];
    }
}
