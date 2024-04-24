<?php

namespace WP_Framework\Admin\Screen;

use WP_Framework\Admin\Screen\ScreenOption\ScreenOption;

/**
 * Trait WP_ScreenTrait
 * Provides methods for managing WordPress admin screens.
 */
trait WP_ScreenTrait
{
    /**
     * @var string The name of the screen.
     */
    protected string $screen_name;

    /**
     * @var array The screen options.
     */
    protected array $screen_options = [];

    /**
     * Set the screen name.
     *
     * @param string $name The name of the screen.
     * @return static The modified instance.
     */
    public function set_screen_name(string $name): static
    {
        $this->screen_name = $name;
        return $this;
    }

    /**
     * Get the screen name.
     *
     * @return string The name of the screen.
     */
    public function get_screen_name(): string
    {
        return $this->screen_name;
    }

    /**
     * Get the load hook for the screen.
     *
     * @return string The load hook for the screen.
     * @throws \Error If the screen name is not initialized yet.
     */
    public function get_load_hook(): string
    {
        if (!isset($this->screen_name)) {
            throw new \Error("Screenname is not initialized yet. Use 'set_screen_name'.");
        }
        return "load-" . $this->screen_name;
    }

    /**
     * Add screen options.
     *
     * @param ScreenOption ...$option The screen options to add.
     * @return self The modified instance.
     */
    public function add_screen_option(ScreenOption ...$option): self
    {
        foreach ($option as $option) {
            $this->screen_options[$option->name] = $option;
        }
        return $this;
    }

    /**
     * Get the screen options.
     *
     * @return array The screen options.
     */
    public function get_screen_options(): array
    {
        return $this->screen_options;
    }

    /**
     * Register screen options.
     *
     * @return self The modified instance.
     */
    public function register_screen_options(): self
    {
        add_action($this->get_load_hook(), function () {
            # exit if we are nt on the right screen
            if (!$this->is_displayed()) {
                return $this;
            }

            foreach ($this->screen_options as $option) {
                add_screen_option($option->name, $option->get_args());
            }
        });
        return $this;
    }

    /**
     * Check if the screen is currently displayed.
     *
     * @return bool True if the screen is displayed, false otherwise.
     */
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
