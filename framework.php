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
 * Custom Taxonomies
 * Adds the custom taxonomies, define in the taxonomies/ folder.
 * put json there, with properties as in the link
 * the slug for the taxonomy type goes by it's file name
 * @link https://developer.wordpress.org/reference/functions/register_taxonomy/
 * */

use WP_ThemeFramework\CustomTaxonomy\CustomTaxonomy;

add_action('init', function () {
    if (!$custom_taxonomy_files = glob(THEME_DIR . "/taxonomies/*.json")) {
        return;
    };
    foreach ($custom_taxonomy_files as $custom_taxonomy_file) {
        if (str_starts_with(
            haystack: basename($custom_taxonomy_file),
            needle: '.example'
        )) continue;
        $custom_taxonomy = new CustomTaxonomy($custom_taxonomy_file);
        $custom_taxonomy->register();
    }
}, 0);

/**
 * Custom Post Types
 * Adds the custom Post Types, define in the post-types/ folder.
 * put json of the args there, with properties as in the link
 * the slug for the post type goes by it's file name
 * @link https://developer.wordpress.org/plugins/post-types/registering-custom-post-types/
 * */

use WP_ThemeFramework\CustomPostType\CustomPostType;

add_action('init', function () {
    if (!$custom_postype_files = glob(THEME_DIR . "/post-types/*.json")) {
        return;
    };
    foreach ($custom_postype_files as $custom_postype_file) {
        if (str_starts_with(
            haystack: basename($custom_postype_file),
            needle: '.example'
        )) continue;
        $custom_taxonomie = new CustomPostType($custom_postype_file);
        $custom_taxonomie->register();
    }
}, 0);

/* blocks */

use WP_ThemeFramework\CustomBlock\CustomBlock;

if (!$custom_block_folders = glob(THEME_DIR . "/blocks/*", GLOB_ONLYDIR)) {
    return;
};
$register_block_script =
    "
    const { registerBlockType } = wp.blocks;
    const { serverSideRender: ServerSideRender } = wp;
    const { Fragment } = wp.element;
    ";
foreach ($custom_block_folders as $custom_block_folder) {
    if (!file_exists($custom_block_folder . "/block.json")) {
        return new WP_Error("No block.json found in '$custom_block_folder'.");
    }
    $custom_block = new CustomBlock($custom_block_folder);
    add_action('init', [$custom_block, 'register']);
    $register_block_script .= "registerBlockType('$custom_block->name', " . json_encode($custom_block->args) . ")\n";
}
add_action(
    'enqueue_block_editor_assets',
    function () use ($custom_block, $register_block_script) {
        wp_register_script("$custom_block->name", '');
        wp_enqueue_script(
            "$custom_block->name",
            '',
            ['wp', 'wp-blocks', 'wp-block-editor', 'wp-i18n'],
            time(),
            true
        );
        wp_add_inline_script(
            "$custom_block->name",
            $register_block_script,
            'before'
        );
    }
);
