<?php

namespace WP_Framework\CLI;

class CLI
{
    private static $instance;

    # Private constructor to prevent direct instantiation
    private function __construct()
    {
    }

    public static function get_instance(): self|false
    {
        if (self::$instance === null) {
            if (!\class_exists('WP_CLI')) {
                return false;
            }
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    private static function init()
    {
    }
    public static function register_command(string $name, $callable, string $wp_cli_hook = 'after_wp_load',  $args = [])
    {
        $args['when'] = $wp_cli_hook;
        \WP_CLI::add_command($name, $callable, $args);
    }
}
