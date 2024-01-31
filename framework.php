<?php

define('FRAMEWORK_DIR', dirname(__FILE__));

spl_autoload_register(function ($class) {
    $class_name_array = explode("\\", $class);
    if (array_shift($class_name_array) == 'WP_ThemeFramework') {
        $class_name = implode("/", $class_name_array);
        include FRAMEWORK_DIR . "/$class_name.php";
    }
});

/**
 * CustomTaxonomy
 * Adds the custom taxonomies, define in the taxonomies/ folder.
 * put json there, with properties as in the link
 * @link https://developer.wordpress.org/reference/functions/register_taxonomy/
 * */

use WP_ThemeFramework\CustomTaxonomy\CustomTaxonomy;

add_action('init', function () {
    if (!$custom_taxonomie_files = glob(THEME_DIR . "/taxonomies/*.json")) {
        return;
    };
    foreach ($custom_taxonomie_files as $custom_taxonomie_file) {
        if (str_starts_with(
            haystack: basename($custom_taxonomie_file),
            needle: '.example'
        )) continue;
        $custom_taxonomie = new CustomTaxonomy($custom_taxonomie_file);
        $custom_taxonomie->register();
    }
}, 0);
