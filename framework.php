<?php

/**
 * Path of WP Framework directory with trailing slash.
 */
define('FRAMEWORK_DIR', dirname(__FILE__) . '/');

# require main class
require 'classes/Framework.php';

# get instance
use WP_Framework\Framework;

$framework = Framework::get_instance();


/**
 * TO BE MOVED INTO Framework-methods
 */


/**
 * Util Functions
 */

use WP_Framework\Utils\Validation;

function str_validate(string $needle, string ...$haystack)
{
    try {
        (new Validation(...$haystack))->test($needle);
    } catch (\Error $error) {
        throw new \Error($error->getMessage());
    }
    return $needle;
}

/**
 * Custom Taxonomies
 * Adds the custom taxonomies, define in the taxonomies/ folder.
 * put json there, with properties as in the link
 * the slug for the taxonomy type goes by it's file name
 * @link https://developer.wordpress.org/reference/functions/register_taxonomy/
 * */

$framework->register_object_types_from_json_in_folder('Taxonomy', 'taxonomies');

/**
 * Custom Post Types
 * Adds the custom Post Types, define in the post-types/ folder.
 * put json of the args there, with properties as in the link
 * the slug for the post type goes by it's file name
 * @link https://developer.wordpress.org/plugins/post-types/registering-custom-post-types/
 * */
/*
$framework->get_model('post')->register_type_from_json_folder('post-types'); */
$framework->register_object_types_from_json_in_folder('PostType', 'post-types');

/* blocks */

use WP_Framework\CustomBlock\CustomBlock;
use WP_Framework\AssetFile\ScriptAsset;

if (!$custom_block_folders = glob(THEME_DIR . "blocks/*", GLOB_ONLYDIR)) {
    return;
};
foreach ($custom_block_folders as $custom_block_folder) {
    if (!file_exists($custom_block_folder . "/block.json")) {
        return new WP_Error("No block.json found in '$custom_block_folder'.");
    }
    $custom_block = new CustomBlock($custom_block_folder);
    add_action('init', [$custom_block, 'register']);;
}

$custom_blocks_script = new ScriptAsset(
    path: "assets/js/custom-block/register.js",
    handle: 'custom-blocks',
    action_hook: 'enqueue_block_editor_assets'
);
$custom_blocks_script
    ->add_dependencies('react', 'wp-blocks', 'wp-block-editor', 'wp-i18n')
    ->set_tag_attributes(['type' => 'module'])
    ->add_data_hook('customBlocksData')
    ->enqueue();
