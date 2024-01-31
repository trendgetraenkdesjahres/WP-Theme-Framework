<?php

namespace WP_ThemeFramework\CustomPostType;

/**
 * CustomPostTypeFile
 * Representation of a JSON File which can produce WP_PostTypes objects
 * File needs to contain args as in linked documentation in JSON Format
 *
 * @link https://developer.wordpress.org/plugins/post-types/registering-custom-post-types/
 */

class CustomPostTypeFile
{
    public string $posttype_name;
    public array $posttype_args;

    /**
     * Representation of a JSON File which can produce WP_PostTypes objects
     * File needs to contain args as in linked documentation in JSON Format
     *
     * @param string $path path to json file containing the args.
     *
     * @link https://developer.wordpress.org/plugins/post-types/registering-custom-post-types/
     * @return void
     */
    function __construct(public string $path)
    {
        $posttype = basename($path, 'php');
        if (empty($posttype) || strlen($posttype) > 20) {
            _doing_it_wrong(__FUNCTION__, __('PostType names must be between 1 and 20 characters in length.'), '4.2.0');
            return new \WP_Error('posttype_length_invalid', __('PostType names must be between 1 and 20 characters in length.'));
        }
        $this->posttype_name = basename($path, '.json');
        $this->posttype_args = self::json_file_to_array($path);
    }

    /**
     * Returns the WP Core class used for interacting with posttypes.
     *
     * @return \WP_PostType
     */
    public function get_posttype_object(): \WP_Post_Type
    {
        try {
            return new \WP_Post_Type(
                $this->posttype_name,
                $this->posttype_args
            );
        } catch (\Error $e) {
            throw new \Error('oopsie');
        }
    }


    /**
     * reads json file into array
     *
     * @param string $path path to json file
     *
     * @return array
     */
    private static function json_file_to_array($path): array
    {
        $file_handle = fopen($path, 'r');
        try {
            $array = json_decode(
                json: fread($file_handle, filesize($path)),
                associative: true
            );
        } catch (\Error $e) {
            throw new \WP_Error(
                code: __NAMESPACE__,
                message: "File '$path' is invalid"
            );
        } finally {
            fclose($file_handle);
        }
        return $array;
    }
}
