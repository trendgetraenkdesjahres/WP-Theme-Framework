<?php

/**
 * Path of WP Framework directory with trailing slash.
 */
define('FRAMEWORK_DIR', dirname(__FILE__) . '/');

# require main class
require 'classes/Framework.php';

# get instance
use WP_Framework\Framework;
use WP_Framework\Model\DataModel;


# define function to use framework and global var.
function framework(): Framework
{
    return Framework::get_instance();
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

$calender = DataModel::create(
    name: 'appointment',
    has_meta: true,
    owner_type: 'client'
);

$calender
    ->add_property('time', 'datetime', is_indexable: true)
    ->add_property('duration', 'int(11) unsigned')
    ->add_property('status', 'varchar(20)', is_indexable: true)
    ->add_property('notes', 'text', true);

$framework->register_model($calender)->get_model('appointment');
var_dump($framework);


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
