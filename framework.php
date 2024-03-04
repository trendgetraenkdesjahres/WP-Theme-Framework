<?php

/**
 * Path of WP Framework directory with trailing slash.
 */
define('FRAMEWORK_DIR', dirname(__FILE__) . '/');

# require main class
require 'classes/Framework.php';

use WP_Framework\Database\Database;
use WP_Framework\Framework;
use WP_Framework\Model\CustomModel;

# get instance & define function to use framework and global var.
function framework(): Framework
{
    if (!isset($GLOBALS['framework'])) {
        $GLOBALS['framework'] = Framework::get_instance();
    }
    return $GLOBALS['framework'];
}
$framework = Framework::get_instance();


/**
 * ALL BELOWL: TO BE MOVED INTO Framework-methods
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
$calender = new CustomModel('abstractibuymsisadf', true, true, false);
$framework->register_model($calender);



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
    add_action('init', [$custom_block, 'register']);
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
