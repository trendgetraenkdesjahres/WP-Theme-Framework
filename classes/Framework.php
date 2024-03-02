<?php

namespace WP_Framework;

use WP_Framework\Model\AbstractModel;

class Framework
{
    private static $instance;

    # Private constructor to prevent direct instantiation
    private function __construct()
    {
    }

    public static function get_instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    private function init()
    {
        /**
         * Register the function to autoload classes from the 'WP_Framework' namespace
         */
        spl_autoload_register(function ($class) {
            $class_name_array = explode("\\", $class);
            if (array_shift($class_name_array) == 'WP_Framework') {
                $class_name = implode("/", $class_name_array);
                include FRAMEWORK_DIR . "classes/$class_name.php";
            }
        });
    }

    /**
     * Method get_data_object
     *
     * @param string $object_type_name [explicite description]
     *
     * @return AbstractModel
     */
    public function get_model(string $object_name): AbstractModel
    {
        # check the Object string, if it's actually a Object class.
        $object_name = "WP_Framework\Model\\" . $object_name;
        if (!is_subclass_of($object_name, 'WP_Framework\Model\ObjectInterface')) {
            throw new \Error("'$object_name' is not implementing WP_Framework\Model\ObjectInterface");
        }
        return new $object_name();
    }

    /**
     * Register Custom Object Types from .json-files in the $folder.
     * Adds the custom Term taxonomies, Post Posttypes, or Custom-Object Types.
     * The slug for the object type goes by it's file name
     *
     * @param string $object_type A name of a class implementing WP_Framework\Model\Type\TypeInterface (without the namespace)
     * @param string $folder the folder to glob for jsons in.
     *k
     * @return Framework
     * */
    public function register_object_types_from_json_in_folder(string $object_type_name, string $folder): Framework
    {
        # check the ObjectType string, if it's actually a ObjectType class.
        $object_type_name = "WP_Framework\Model\Type\\" . $object_type_name;
        if (!is_subclass_of($object_type_name, 'WP_Framework\Model\Type\TypeInterface')) {
            throw new \Error("'$object_type_name' is not implementing WP_Framework\Model\Type\TypeInterface");
        }

        # check for json files.
        if (!$custom_object_type_files = glob(THEME_DIR . "$folder/*.json")) {
            throw new \Error("'" . THEME_DIR . "$folder' does not exist or is contains no json");
        };

        # register the Object Types (ignore '.example')
        foreach ($custom_object_type_files as $custom_object_type_file) {
            if (str_starts_with(
                haystack: basename($custom_object_type_file),
                needle: '.example'
            )) continue;
            $object_type = $object_type_name::create_from_json($custom_object_type_file);
            $object_type->register();
        }
        return $this;
    }
}
