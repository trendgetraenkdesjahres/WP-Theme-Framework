<?php

namespace WP_Framework\Utils;

class JsonFile
{
    /**
     * reads json file into array
     *
     * @param string $path path to json file
     *
     * @return array
     */
    public static function to_array($path): array
    {
        $file_handle = fopen($path, 'r');
        try {
            $array = json_decode(
                json: fread($file_handle, filesize($path)),
                associative: true
            );
        } catch (\Error $e) {
            return  new \WP_Error(
                code: __NAMESPACE__,
                message: "File '$path' is invalid"
            );
        } finally {
            fclose($file_handle);
        }
        return $array;
    }
}
