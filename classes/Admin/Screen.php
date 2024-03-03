<?php

namespace WP_Framework\Admin;

class Screen
{
    static function register()
    {
        add_action('admin_menu', function () {
            add_menu_page('News Page Title', 'News Option', 'manage_options', 'newspage', 'show_menu_news', get_home_url() . '/wp-content/themes/my_theme/assets/img/logo.png');
        });

        function show_menu_news()
        {
            echo 'News content';
        }
    }
}
