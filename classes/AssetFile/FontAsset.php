<?php

namespace WP_Framework\AssetFile;

/**
 * FontAsset
 *
 * The general concept involves creating a FontAsset instance, setting its properties using methods like set_name(), set_styles(), and others, and then registering and enqueuing the font asset using the register() and enqueue() methods.
 * Registration involves creating a dummy stylesheet, adding font information to the theme.json data, and hooking into the wp_theme_json_data_theme filter.
 * Enqueuing involves adding styles to the specified action hook, and if applicable, adding the font-face declaration inline using wp_add_inline_style().
 * Instead of Enqueuing and Registration it is possible to add the FontAsset-object to a StyleAsset with the add_font() method.
 * Note: It's crucial to set the 'font_family_name' property using set_name() before registration and enqueueing.
 */
class FontAsset extends AbstractAsset implements AssetFileInterface
{
    protected string $default_action_hook = 'enqueue_scripts';

    protected ?string $font_family_name = null;
    protected ?string $font_fallback_family = null;

    protected ?string $font_type_name = null;
    protected ?string $font_type_slug = null;

    protected ?string $remote_url = null;
    protected bool $google_font = false;

    protected string $strategy = 'optional';
    protected ?string $unicode_range = null;
    protected ?string $unicode_subset = null;

    protected array $styles = [
        "regular" => [400]
    ];

    /**
     * Register the font asset by adding styles to the dummy stylesheet and updating theme.json data.
     *
     * @return FontAsset The modified FontAsset instance.
     *
     * @throws \Error If 'font_family_name' is not defined or if registration of the font asset fails.
     */
    public function register(): FontAsset
    {
        if (!$this->font_family_name) {
            throw new \Error("This 'font_family_name' is not defined.");
        }

        # register dummy-stylesheet
        add_action($this->action_hooks, function () {
            if (false === wp_register_style($this->handle, false, $this->dependencies, $this->version)) {
                throw new \Error('Could not register ' . $this->handle);
            }
        });

        # add to theme.json data
        add_filter('wp_theme_json_data_theme', function ($theme_json) {
            $font_faces = [];
            foreach ($this->styles as $style => $style_weights) {
                array_push($font_faces, [
                    'fontFamily' => $this->font_family_name,
                    'fontWeight' => $style_weights,
                    'fontStyle' => $style,
                    'src' => ["file:./" .  str_replace(THEME_DIR, '', $this->absolute_path)]
                ]);
            }
            return $theme_json->update_with([
                'settings' => [
                    'typography' => [
                        'fontFamilies' => [
                            [
                                'name' => 'Primary',
                                'slug' => 'primary',
                                'fontFamily' => "'$this->font_family_name'" . ($this->font_fallback_family ?? " ,$this->font_fallback_family"),
                                'fontFace' => $font_faces
                            ]
                        ]
                    ]
                ]
            ]);
        });
        return $this;
    }

    /**
     * Enqueue the font asset by adding style-tag to html-head with @font-face.
     *
     * @return FontAsset The modified FontAsset instance.
     *
     * @throws \Error If 'font_family_name' is not defined or if enqueuing of the font asset fails.
     */
    public function enqueue(): FontAsset
    {
        if (!$this->font_family_name) {
            throw new \Error("This 'font_family_name' is not defined.");
        }
        add_action($this->action_hooks, function () {
            if (false === wp_enqueue_style($this->handle, false, $this->dependencies, $this->version)) {
                throw new \Error("Could not enqueue '$this->handle' with function add_action('$this->action_hooks', function () {\nwp_enqueue_style('$this->handle', '$this->url', ['" . implode("', '", $this->dependencies) . "'], '$this->version', );\n});.");
            }
            wp_add_inline_style($this->handle, $this->get_font_face_declaration());
        });
        return $this;
    }

    /**
     * Include the font asset from Google Fonts by adding necessary link tags to the wp_head. Font Family name needs to be set for this.
     *
     * @return FontAsset The modified FontAsset instance.
     *
     * @throws \Error If 'font_family_name' is not defined
     */
    public function include_from_google(): FontAsset
    {
        if (!$this->font_family_name) {
            throw new \Error("This 'font_family_name' is not defined.");
        }
        if (!has_filter('google_font_query')) {
            add_action('wp_head', function () {
                echo "<link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">\n";
                echo "<link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>\n";
                echo "<link rel=\"stylesheet\" href=\"https://fonts.googleapis.com/css2?" . rtrim(apply_filters('google_font_query', ''), "&") . "\" crossorigin>\n";
            }, 0);
        }
        add_action('google_font_query', function ($query) {
            return $query . $this->get_google_font_query() . "&";
        });

        return $this;
    }

    /**
     * Set the name and properties of the font asset, including font family, fallback family, and type family name.
     *
     * @param string|null $font_family_name The primary font family name. For OpenType and TrueType fonts, <font-face-name> is used to match either the Postscript name or the full font name in the name table of locally available fonts.
     * @param string|null $font_fallback_family The fallback font family name, including a generic family.
     * @param string|null $font_type_family_name The type family name for generating the slug and dynamic property name.
     *
     * @return FontAsset The modified FontAsset instance.
     *
     * @throws \Error If 'font_fallback_family' does not contain a generic family name.
     */
    public function set_name(?string $font_family_name = null, ?string $font_fallback_family = null, ?string $font_type_family_name = null): FontAsset
    {
        # font family
        if ($font_family_name) {
            $this->font_family_name = $font_family_name;
        }

        # font family fallback (needs to contain a generic)
        if ($font_fallback_family) {
            $generic_families = [
                'serif',
                'sans-serif',
                'monospace',
                'cursive',
                'fantasy'
            ];
            $font_fallback_family = strtolower($font_fallback_family);
            $contains_generic = false;
            foreach (explode(',', $font_fallback_family) as $fallback_name) {
                if (in_array(trim($fallback_name), $generic_families)) {
                    $contains_generic = true;
                }
            }
            if (!$contains_generic) {
                throw new \Error("'font_fallback_family' does not contain a generic family name.");
            }
            $this->font_fallback_family = $font_fallback_family;
        }

        # the type name generates the slug and css dynamic property name
        if ($font_type_family_name) {
            $this->font_type_name = $font_type_family_name;
            $this->font_type_slug = strtolower(str_replace(" ", "_", $font_type_family_name));
            $this->handle = get_stylesheet() . "-font-" . $this->font_type_slug;
        }

        return $this;
    }

    /**
     * Set the font styles, including regular and italic font weights, for the FontAsset.
     *
     * @param array|null $regular_font_weights An array of regular font weights.
     * @param array|null $italic_font_weights An array of italic font weights.
     *
     * @return FontAsset The modified FontAsset instance.
     *
     * @throws \Error If any font weight is not divisible by 100 or falls outside the range of 0 to 1000.
     */
    public function set_styles(?array $regular_font_weights = null, ?array $italic_font_weights = null): FontAsset
    {
        if ($regular_font_weights) {
            array_reduce($regular_font_weights, function ($carry, $item) {
                $divisible = $carry && ($item % 100 === 0);
                if (!$divisible || ($item < 0 || $item > 1000)) {
                    throw new \Error("'$item' is not a font-weight.");
                }
                return $divisible;
            }, true);
            $this->styles['regular'] = $regular_font_weights;
        }

        if ($italic_font_weights) {
            array_reduce($italic_font_weights, function ($carry, $item) {
                $divisible = $carry && ($item % 100 === 0);
                if (!$divisible || ($item < 0 || $item > 1000)) {
                    throw new \Error("'$item' is not a font-weight.");
                }
                return $divisible;
            }, true);
            $this->styles['italic'] = $italic_font_weights;
        }
        return $this;
    }

    /**
     * Add a local font to the StyleFont instance.
     *
     * @param string $path The path to the local font file.
     *
     * @return StyleFont The modified StyleFont instance.
     *
     * @throws \Error If the specified file does not exist.
     */
    public function add_remote_font(string $url): FontAsset
    {
        $this->remote_url = $url;
        return $this;
    }

    /**
     * Set the Unicode range for the font, allowing fine-grained control over supported character ranges.
     *
     * @param string $range The Unicode range or a predefined range name.
     *
     * @return StyleFont The modified StyleFont instance.
     *
     * @throws \Error If the provided range is neither a name for a range nor a range itself.
     */
    public function set_unicode_range(string $range): FontAsset
    {
        $unicode_ranges = [
            'latin' => 'U+0-7F',
            'latin-ext' => 'U+80-2FF',
            'greek' => 'U+370-3FF',
            'cyrillic' => 'U+400-4FF',
            'arabic' => 'U+600-6FF',
            'devanagari' => 'U+900-97F',
            'chinese' => 'U+4E00-9FFF',
            'hiragana' => 'U+3040-309F',
            'katakana' => 'U+30A0-30FF',
            'emoji' => 'U+1F600-1F64F'
        ];

        # $range is a name for a range
        if (key_exists($range, $unicode_ranges)) {
            $this->unicode_subset = $range;
            $this->unicode_range = $unicode_ranges[$range];
            return $this;
        }
        # $range is a range itself
        if (str_starts_with($range, "U+")) {
            $this->unicode_range = $range;
            return $this;
        }
        throw new \Error("'$range' is neither a name for a range or a range itself.");
    }

    /**
     * Set the font display strategy for the font.
     *
     * @param string $font_display_strategy The font display strategy, e.g., 'auto', 'block', 'swap', 'fallback', 'optional'.
     *
     * @return StyleFont The modified StyleFont instance.
     *
     * @throws \Error If the provided font display strategy is not valid.
     */

    public function set_strategy(string $font_display_strategy): FontAsset
    {
        $strategies = [
            'auto',
            'block',
            'swap',
            'fallback',
            'optional'
        ];
        if (!in_array($font_display_strategy, $strategies)) {
            throw new \Error("'$font_display_strategy' is not a valid 'font-display'-strategy");
        }
        $this->strategy = $font_display_strategy;
        return $this;
    }

    /**
     * Get the Google Font query for including the font in a web page with a link-tag.
     * @link https://developers.google.com/fonts/docs/getting_started
     * @link https://developers.google.com/fonts/docs/css2
     *
     * @return string The query for the Google Font.
     */
    private function get_google_font_query(): string
    {
        $query = "family=" . str_replace(" ", "+", $this->font_family_name) . ":";
        if (isset($this->styles['italic'])) {
            $query .= "ital,";
        }
        $query .= "wght@";

        if (count($this->styles) > 1 || isset($this->styles['italic'])) {
            foreach ($this->styles as $style => $weights) {
                if ($style == 'regular') $i = 1;
                if ($style == 'italic') $i = 0;
                foreach ($weights as $weight) {
                    $query .= "$i,$weight;";
                }
            }
        } else {
            foreach ($this->styles['regular'] as $weight) {
                $query .= "$weight;";
            }
        }
        $query = rtrim($query, ";");
        if ($this->unicode_subset) {
            $query .= "&subset=$this->unicode_subset";
        }
        return "$query&display=$this->strategy";
    }

    /**
     * Get the font-face declaration for the custom font.
     *
     * @return string The CSS font-face declaration.
     */
    public function get_font_face_declaration(): string
    {
        $declaration = "  font-family: '$this->font_family_name'" . ($this->font_fallback_family ? ", $this->font_fallback_family" : '') . ";\n";
        if ($this->unicode_range) {
            $declaration .= "  unicode-range: $this->unicode_range;\n";
        }
        $declaration .= "  font-display: $this->strategy;\n";
        $declaration .= "  font-weight: " . implode(' ', $this->styles['regular']) . ";\n";
        $declaration .= "  src:\n    local(\"$this->font_family_name\"),\n";
        if ($this->remote_url) {
            $declaration .= "    url($this->remote_url) format('" . $this->get_font_format($this->remote_url) . "'),\n";
        }
        $declaration .= "    url(" . get_theme_file_uri(str_replace(THEME_DIR, '', $this->absolute_path)) . ") format('" . $this->get_font_format() . "');\n";
        return "@font-face {\n$declaration}";
    }

    public function get_font_var_declaration(string $selector = ":root"): string
    {
        $declaration = "--font-family-{$this->font_type_slug}: \"{$this->font_family_name}\", {$this->font_fallback_family};";
        return "$selector { $declaration }";
    }

    /**
     * Method get_font_format
     * if url is given, it will check it's suffix instead of this file's one.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/CSS/@font-face/src#font_formats
     *
     * @return string
     */
    private function get_font_format(string $url = ''): string
    {
        $formats = [
            'otc' => 'collection',
            'ttc' => 'collection',
            'eot' => 'embedded-opentype',
            'otf' => 'opentype',
            'ttf' => 'opentype',
            'svg' => 'svg',
            'svgz' => 'svg',
            'ttf' => 'truetype',
            'woff' => 'woff',
            'woff2' => 'woff2'
        ];
        $check_me = $url ? $url : $this->absolute_path;
        $suffix = pathinfo($check_me, PATHINFO_EXTENSION);
        if (key_exists($suffix,            $formats)) {
            return $formats[$suffix];
        }
        throw new \Error(".$suffix is not an extension of a valid font format");
    }
}
