<?php

namespace WP_ThemeFramework\AssetFile;

abstract class AssetFile
{
    public string $absolute_path;
    public string $handle;
    public string $url;
    public string $version;
    public array $dependencies = [];
    public ?string $action_hook = null;

    protected string $default_action_hook;

    /**
     * Constructor for generic AssetFile.
     *
     * @param string $path The path to the asset
     * @param ?string $handle the handle for the asset. if ommited, but already used somewhere else, it will override the asset with this handle. if not omitted, a random handle will be generated.
     * @param ?string $action_hook defaults to a hook_name specified in a implementation of AssetFile
     * @param string $use_in the context of the asset: 'wp', 'login' or 'admin'
     */
    final function __construct(string $path, ?string $handle = null, ?string $action_hook = null, public string $use_in = 'wp')
    {
        $this->absolute_path = self::get_verified_absolute_path($path, true);
        $this->handle = $handle ? $handle : self::get_unique_handle($path);
        $this->action_hook = self::get_verified_action_hook_name($use_in, $this->default_action_hook, $action_hook, true);
        $this->use_in = self::get_verified_use_in_param($use_in, true);

        $this->url = get_theme_file_uri($this->get_relative_path());
        $this->version = filectime($this->absolute_path);
    }

    /**
     * Set the version of the asset
     *
     * @param string $version [explicite description]
     *
     * @return AssetFile
     */
    public function set_version(string $version): AssetFile
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Adds dependencies to the asset.
     *
     * @param string ...$dependency Dependency names.
     * @return AssetFile
     */
    public function add_dependencies(string ...$dependency): AssetFile
    {
        $this->dependencies = array_merge($this->dependencies, $dependency);
        return $this;
    }

    /**
     * Returns a WPDefinedAsset array
     *
     * @return array
     */
    public function get_wp_defined_asset(): array
    {
        return ["file:./" . basename($this->absolute_path), $this->handle];
    }

    /**
     * Returns the relative path from Theme Directory
     *
     * @return string
     */
    public function get_relative_path(): string
    {
        return substr($this->absolute_path, strlen(THEME_DIR));
    }

    /**
     * Tries to find the absolute path in many ways.
     *
     * @param string $path_or_url Accepts absolute, (some) relative paths and URLs.
     * @param bool $throw_error Method throws Error on failure if true
     *
     * @return string|false the absolute path on success and false on failure
     */
    protected static function get_verified_absolute_path(string $path_or_url, bool $throw_error = false): string|false
    {
        /* absolut path */
        if (str_starts_with($path_or_url, "/")) {
            if (self::verify_path($path_or_url)) {
                return $path_or_url;
            }
            /* maybe the '/' is there by mistake? let's remove it, to allow further checks. */
            $path_or_url = substr($path_or_url, 1);
        }
        /* url */
        if (str_starts_with($path_or_url, get_home_url())) {
            $path_or_url = ABSPATH . parse_url($path_or_url, PHP_URL_PATH);
            if (self::verify_path($path_or_url)) {
                return $path_or_url;
            }
            /* there is no way to help myself*/
            if ($throw_error) {
                throw new \Error("Could not find a file with the url '$path_or_url'");
            }
            return false;
        }

        /* relative path from theme-dir */
        if (self::verify_path(THEME_DIR . $path_or_url)) {
            return THEME_DIR . $path_or_url;
        }

        /* relative path from framework-dir */
        if (self::verify_path(FRAMEWORK_DIR . $path_or_url)) {
            return FRAMEWORK_DIR . $path_or_url;
        }

        /* relative path from wordpress-dir */
        if (self::verify_path(ABSPATH . $path_or_url)) {
            return ABSPATH . $path_or_url;
        }

        /* we have failed. */
        if ($throw_error) {
            throw new \Error("Could not find a file with the path '$path_or_url'");
        }
        return false;
    }

    /**
     *  Runs file_exists, but can also throw Errors.
     *
     * @param string $path the path of the file to check
     * @param bool $throw_error method throw error if true
     *
     * @return bool file_exists
     */
    private static function verify_path(string $path, bool $throw_error = false): bool
    {
        if (file_exists($path)) {
            return true;
        }
        if ($throw_error) {
            throw new \Error("File '$path' does not exist.");
        }
        return false;
    }

    /**
     * Returns a unquie handle for the asset, based on the theme-name and on its filename and a random string.
     *
     * @param $path
     *
     * @return string
     */
    protected static function get_unique_handle($path): string
    {
        return get_stylesheet() . "-" . strtolower(pathinfo($path, PATHINFO_FILENAME)) . "-" . bin2hex(random_bytes(6));
    }

    /**
     * Checks if the 'use_in' parameter is valid.
     *
     * @param string $use_in
     * @param bool $throw_error method throw error if true
     *
     * @return string|false the parameter on success, false on failure.
     */
    private static function get_verified_use_in_param(string $use_in, bool $throw_error = false): string|false
    {
        $valid_in_use_values = [
            'wp',
            'admin',
            'login'
        ];
        if (!in_array($use_in, $valid_in_use_values)) {
            if ($throw_error) {
                throw new \Error("'$use_in' is not valid.");
            }
            return false;
        }
        return $use_in;
    }

    /**
     * Composes and varifies hook_names for add_action() function
     * Hook name will be either
     * - "{$use_in_hook_name_part}_{$default_hook_name_part}"
     * - $hook_name (if $hook_name is string)
     *
     * @param string $use_in_hook_name_part 'wp', 'admin' or 'login'
     * @param string $default_hook_name_part eg: 'enqueue_scripts'
     * @param ?string $hook_name [optional] complete hookname which overrides the composing
     * @param bool $throw_error method throw error if true
     *
     * @return string|false
     */
    private static function get_verified_action_hook_name(string $use_in_hook_name_part, string $default_hook_name_part, ?string $hook_name = null, bool $throw_error = false): string|false
    {
        if (!$hook_name) {
            $hook_name = $use_in_hook_name_part . "_" . $default_hook_name_part;
        }
        if (!has_action($hook_name)) {
            if ($throw_error) {
                throw new \Error("Action '$hook_name' does not exists.");
            }
            return false;
        }
        return $hook_name;
    }
}
