<?php

namespace WP_ThemeFramework\CustomTaxonomy;

use WP_ThemeFramework\Utils\JsonFile;

/**
 * CustomTaxonomyFile
 * Representation of a JSON File which can produce WP_Taxomies objects
 * File needs to contain args as in linked documentation in JSON Format
 *
 * @link https://developer.wordpress.org/reference/functions/register_taxonomy/
 */

class CustomTaxonomyFile extends JsonFile
{
    public string $taxonomy_name;
    public array $taxonomy_args;
    public string|array $taxonomy_object;

    /**
     * Representation of a JSON File which can produce WP_Taxomies objects
     * File needs to contain args as in linked documentation in JSON Format
     *
     * @param string $path path to json file containing the args.
     *
     * @link https://developer.wordpress.org/reference/functions/register_taxonomy/
     * @return void
     */
    function __construct(public string $path)
    {
        $taxonomy = basename($path, '.json');
        if (empty($taxonomy) || strlen($taxonomy) > 32) {
            _doing_it_wrong(__FUNCTION__, __('Taxonomy names must be between 1 and 32 characters in length.'), '4.2.0');
            return new \WP_Error('taxonomy_length_invalid', __('Taxonomy names must be between 1 and 32 characters in length.'));
        }
        $this->taxonomy_name = basename($path, '.json');
        $this->taxonomy_args = self::to_array($path);
        $this->taxonomy_object = $this->taxonomy_args['object_type'];
    }

    /**
     * Returns the WP Core class used for interacting with taxonomies.
     *
     * @return \WP_Taxonomy
     */
    public function get_taxonomy_object(): \WP_Taxonomy
    {
        try {
            return new \WP_Taxonomy(
                $this->taxonomy_name,
                $this->taxonomy_object,
                $this->taxonomy_args
            );
        } catch (\Error $e) {
            throw new \Error('oopsie');
        }
    }
}
