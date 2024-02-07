<?php

namespace WP_ThemeFramework\CustomBlock;

use WP_ThemeFramework\Utils\JsonFile;

/**
 * CustomBlockFile
 * Representation of a JSON File which can produce WP_Block_Type objects
 * Has some logic to produce many values by it's own.
 */
class CustomBlockFile extends JsonFile
{
    public string $block_name;
    public array $block_args;

    /**
     * CustomBlockFile
     * Representation of a JSON File which can produce WP_Block_Type objects
     * Has some logic to produce many values by it's own.
     */
    function __construct(public string $path)
    {
        $this->block_name = str_replace(" ", "-", basename($path));
        $this->block_args = self::json_file_to_array($path . "/block.json");
    }
}
