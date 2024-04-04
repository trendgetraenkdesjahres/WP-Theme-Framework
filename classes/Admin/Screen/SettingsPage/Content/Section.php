<?php

namespace WP_Framework\Admin\Screen\SettingsPage\Content;

/**
 * Class Section
 * Represents a section for a settings page.
 */
class Section
{
    /**
     * @var string The name of the section.
     */
    public string $name;

    /**
     * @var string The title of the section.
     */
    private string $title;

    /**
     * @var string The content of the section.
     */
    private string $content;

    /**
     * @var string|null The class of the section.
     */
    private ?string $class;

    /**
     * Section constructor.
     *
     * @param string $title The title of the section.
     * @param string $content The content of the section.
     * @param string|null $class The class of the section.
     */
    public function __construct($title, string $content = '', ?string $class = null)
    {
        $this->name = sanitize_title($title);
        $this->title = $title;
        $this->class = $class;
        $this->content = esc_html($content);
    }

    /**
     * Get the title of the section.
     *
     * @return string The title of the section.
     */
    public function get_title(): string
    {
        return $this->title;
    }

    /**
     * Get the display callback for the section.
     *
     * @return callable The display callback for the section.
     */
    public function get_display_callback(): callable
    {
        return function () {
            echo $this->content ? "<p>{$this->content}</p>" : '';
        };
    }

    /**
     * Get the register-options for displaying the section.
     *
     * @return array The options for displaying the section.
     */
    public function get_section_options(): array
    {
        $options = [];
        if ($this->class) {
            $options = [
                'before_section' => '<section class="%s">',
                'after_section'  => '</section>',
                'section_class'  => $this->class,
            ];
        }
        return $options;
    }
}
