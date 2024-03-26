<?php

namespace WP_Framework\Admin\Screen;

use WP_Framework\Admin\Screen\ScreenOption\ScreenOption;
use WP_Framework\Debug\Debug;

trait WP_ScreenTrait
{
    protected string $screen_name;

    protected array $screen_options = [];

    public function set_screen_name(string $name): self
    {
        $this->screen_name = $name;
        return $this;
    }

    public function get_screen_name(): string
    {
        return $this->screen_name;
    }

    public function get_load_hook(): string
    {
        if (!isset($this->screen_name)) {
            throw new \Error("Screenname is not initialized yet. Use 'set_screen_name'.");
        }
        return "load-" . $this->screen_name;
    }

    public function add_screen_option(ScreenOption ...$option): self
    {
        foreach ($option as $option) {
            $this->screen_options[$option->key] = $option;
        }
        return $this;
    }

    public function get_screen_options(): array
    {
        return $this->screen_options;
    }

    public function register_screen_options(): self
    {
        add_action($this->get_load_hook(), function () {
            # exit if we are nt on the right screen
            if (!$this->is_displayed()) {
                return $this;
            }

            foreach ($this->screen_options as $option) {
                add_screen_option($option->key, $option->get_args());
            }
        });
        return $this;
    }

    public function is_displayed(): bool
    {
        $screen = get_current_screen();
        if (!$screen instanceof \WP_Screen) {
            return false;
        }
        if ($screen->id !== $this->get_screen_name()) {
            return false;
        }
        return true;
    }
}
