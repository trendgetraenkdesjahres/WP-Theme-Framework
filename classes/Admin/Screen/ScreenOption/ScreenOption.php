<?php

namespace WP_Framework\Admin\Screen\ScreenOption;

class ScreenOption
{
    public string $key;
    public string $label;
    public string|int $default;

    public function __construct(string $label, string|int $default, ?string $key = null)
    {
        if (!$key) {
            $key = sanitize_key(str_replace(' ', '_', $label));
        }
        $this->key = $key;
        $this->label = $label;
        $this->default = $default;
    }

    public function get_args(): array
    {
        return [
            'label' => $this->label,
            'default' => $this->default,
            'option' => $this->key,
        ];
    }
}
