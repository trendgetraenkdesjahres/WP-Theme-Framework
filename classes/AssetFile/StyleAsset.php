<?php

namespace WP_Framework\AssetFile;

use WP_Framework\StyleFont\StyleFont;

/**
 * StyleAsset
 *
 * The StyleAsset class represents a style asset that can be registered and enqueued in WordPress themes. It extends the functionality of AssetFile and implements AssetFileInterface.
 * The general concept involves creating a StyleAsset instance, setting its properties, and then registering and enqueuing the style asset using the register() and enqueue() methods.
 * @throws \Error If unable to register or enqueue the style asset.
 */
class StyleAsset extends AssetFile implements AssetFileInterface
{
    protected string $default_action_hook = 'enqueue_scripts';
    protected string $media = 'all';

    /**
     * Register the style asset with WordPress.
     *
     * This method creates a dummy stylesheet and hooks into the specified action hook to register the style asset.

     * @return StyleAsset The modified StyleAsset instance.
     * @throws \Error If unable to register the style asset with WordPress.
     */
    public function register(): StyleAsset
    {
        add_action($this->action_hook, function () {
            if (false === wp_register_style($this->handle, $this->url, $this->dependencies, $this->version, $this->media)) {
                throw new \Error('Could not register ' . $this->handle);
            }
        });
        return $this;
    }

    /**
     * Add inline styles to the registered style asset.
     *
     * This method allows you to include inline CSS styles along with the registered style asset. The styles will be added using wp_add_inline_style function.

     * @param string $data The inline CSS styles to add.
     * @return StyleAsset The modified StyleAsset instance.
     */
    public function add_inline(string $data): StyleAsset
    {
        add_action($this->action_hook, function () use ($data) {
            wp_add_inline_style($this->handle, $data);
        }, 999);
        return $this;
    }

    /**
     * Add a font asset to the registered style asset.
     *
     * This method associates a FontAsset with the StyleAsset by setting the FontAsset's handle and registering it.
     * It ensures that the font styles are included and enqueued along with the style asset.
     # TODO accepts string, too, and crafts it's own font

     * @param FontAsset $font The FontAsset instance to add.
     * @return StyleAsset The modified StyleAsset instance.
     */
    public function add_font(string|FontAsset $font): StyleAsset
    {
        $font->handle = $this->handle;
        $this->add_inline($font->get_font_face_declaration());
        $this->add_inline($font->get_font_var_declaration());
        return $this;
    }

    public function set_tag_attributes(array $key_value_pairs): StyleAsset
    {
        return $this;
    }

    /**
     * Enqueue the registered style asset.
     *
     * This method enqueues the style asset, making it available for use on the site.
     * It ensures that the associated font styles are also enqueued, providing a complete styling solution.

     * @return StyleAsset The modified StyleAsset instance.
     * @throws \Error If the style asset cannot be enqueued successfully.
     */
    public function enqueue(): StyleAsset
    {
        add_action($this->action_hook, function () {
            if (false === wp_enqueue_style($this->handle, $this->url, $this->dependencies, $this->version, $this->media)) {
                throw new \Error("Could not enqueue '$this->handle' with function add_action('$this->action_hook', function () {\nwp_enqueue_style('$this->handle', '$this->url', ['" . implode("', '", $this->dependencies) . "'], '$this->version', " . ($in_footer ? 'true' : 'false') . ");\n});.");
            }
        });
        return $this;
    }

    public function enqueue_in_editor(): StyleAsset
    {
        add_action('after_setup_theme', function () {
            add_editor_style($this->get_relative_path());
        });
        return $this;
    }
}
