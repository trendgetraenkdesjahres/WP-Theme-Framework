<?php

namespace WP_Framework\AdminPanel;

abstract class AbstractPanel
{
    public string $name;
    public readonly string $sanitized_singular_name;
    public readonly string $sanitized_plural_name;

    public string $required_capabilty;

    public function __construct(
        public readonly string $singular_name,
        public readonly string $plural_name,
        ?string $sanitized_singular_name = null,
        ?string $sanitized_plural_name = null,
    ) {
        if (!$sanitized_singular_name) {
            $this->sanitized_singular_name = sanitize_key($singular_name);
        }
        if (!$sanitized_plural_name) {
            $this->sanitized_plural_name = sanitize_key($plural_name);
        }
        $this->name = $this->sanitized_plural_name;
    }

    public function register(): AbstractPanel
    {
        add_action('admin_menu', function () {
            add_menu_page(
                page_title: $this->plural_name,
                menu_title: $this->plural_name,
                capability: $this->required_capabilty,
                menu_slug: "show_menu_{$this->sanitized_plural_name}",
                callback: $this->get_screen(),
                icon_url: 'none',
                position: 30 # if two menu items use the same position attribute, one of the items may be overwritten so that only one item displays!
            );
            add_submenu_page(
                parent_slug: "show_menu_{$this->sanitized_plural_name}",
                page_title: "Create new {$this->singular_name}",
                menu_title: "Create new {$this->singular_name}",
                capability: $this->required_capabilty,
                menu_slug: "show_menu_new_{$this->sanitized_singular_name}",
                callback: $this->get_create_new_screen(),
                position: 1
            );
        });
        return $this;
    }

    public function unregister(): AbstractPanel
    {
        return $this;
    }

    private function get_screen(): callable
    {
        return function () {
            echo "<div class='wrap'>
            <h1 class='wp-heading-inline'>{$this->plural_name}</h1>
            </div>" . $this->get_body() . "<div class='clear'></div>";
        };
    }

    protected function get_body(): string
    {
        throw new \Error("'protected function get_body(): string' must be implemented.");
    }

    private function get_create_new_screen(): callable
    {
        return function () {
            echo "<div class='wrap'>
            <h1 class='wp-heading-inline'>Create new {$this->singular_name}</h1>
            </div>" . $this->get_editor_body() . "<div class='clear'></div>";
        };
    }

    protected function get_editor_body(): string
    {
        throw new \Error("'protected function get_editor_body(): string' must be implemented.");
    }
}
