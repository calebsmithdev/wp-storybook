<?php

namespace WpStorybook;

use WpStorybook\AcfBlocks\Stories;
use WpStorybook\Api\Acf;
use WpStorybook\Api\SyncPostType;
use WpStorybook\Cli\StorybookCommand;
use WpStorybook\PostTypes\StoryViewer;

/**
 * @package WpStorybook
 * @since 1.0
 */
class Setup
{
    /**
     * Package settings.
     *
     * @var array Associative array of settings as detailed in the constructor PHPDoc.
     */
    private $args = array();

    /**
     * Package constructor.
     * 
     * @param array|string $args    Array of arguments.
     * @type string        $postTypeSlug         Set the slug for the SB Stories custom post type.
     * @type string        $taxonomySlug         Set the slug for the category for SB Stories.
     * @type bool        $showPostType         Show the SB Stories Custom Post Type.
     */
    public function __construct($args = array())
    {
        $defaults = array(
            'postTypeSlug'    => 'wp_stories',
            'taxonomySlug'          => 'wp_stories_cat',
            'showPostType' => true,
            'createAcfStories' => true
        );

        $parsed_args = array_merge($defaults, $args);

        $this->args = $parsed_args;
        $this->save_arg_settings();
    }

    public function init()
    {
        $this->initialize_required_classes();
    }

    public function initialize_required_classes()
    {
        if ($this->args['showPostType']) {
            (new StoryViewer())->init();
        }

        if (class_exists('ACF') && $this->args['createAcfStories']) {
            (new Acf())->init();
        }

        (new StorybookCommand());

        // TODO: Need an IF check if can create ACF stories. This may not be needed if we move the http function.
        (new Stories())->init();
        (new SyncPostType())->init();
    }

    public function save_arg_settings()
    {
        update_option('wpsb_post_type_slug', $this->args['postTypeSlug']);
        update_option('wpsb_taxonomy_slug', $this->args['taxonomySlug']);
        update_option('wpsb_create_acf_stories', $this->args['createAcfStories']);
        update_option('wpsb_storybook_path', get_stylesheet_directory() . '/storybook/src'); // TODO: Allow user to set this
    }
}
