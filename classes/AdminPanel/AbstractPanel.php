<?php

namespace WP_Framework\AdminPanel;

use WP_Framework\AdminPanel\Table\AbstractTable;

abstract class AbstractPanel
{
    public readonly string $name;

    public string $required_capabilty;

    public function __construct(
        string $name,
        protected string $title,
    ) {
        $this->name = sanitize_key($name);
    }

    public function register(): AbstractPanel
    {
        add_action('admin_menu', function () {
            add_menu_page(
                page_title: $this->title,
                menu_title: $this->title,
                capability: $this->required_capabilty,
                menu_slug: "show_menu_{$this->name}",
                callback: $this->get_screen(),
                icon_url: 'none',
                position: 0
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
            <h1 class='wp-heading-inline'>{$this->title}</h1>
            </div>" . $this->get_body() . "<div class='clear'></div>";
        };
    }

    protected function get_body(): string
    {
        throw new \Error("'protected function get_body(): string' must be implemented.");
    }
}
