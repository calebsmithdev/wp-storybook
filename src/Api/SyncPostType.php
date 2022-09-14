<?php

/**
 * @package WpStorybook
 * @since 1.0
 */

namespace WpStorybook\Api;

use WP_Query;
use WP_REST_Server;

class SyncPostType
{
    protected $postTypeSlug;
    protected $taxonomySlug;

    public function __construct()
    {
        $this->postTypeSlug = get_option('wpsb_post_type_slug');
        $this->taxonomySlug = get_option('wpsb_taxonomy_slug');
    }

    public function init()
    {
        add_action('rest_api_init', [$this, 'define_rest_endpoints']);
    }

    public function define_rest_endpoints()
    {
        register_rest_route('wp-storybook/sync', 'export', array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_export'],
            'permission_callback' => '__return_true',
        ));
    }

    public function get_export()
    {
        $posts = get_posts([
            'post_type' => $this->postTypeSlug,
            'posts_per_page' => -1
        ]);

        foreach ($posts as $post) {
            $post->categories = wp_get_object_terms($post->ID, $this->taxonomySlug);
        }

        return $posts;
    }
}
