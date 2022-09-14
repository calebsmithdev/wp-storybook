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
            'permission_callback' => function () {
                if (WP_DEBUG) {
                    return true;
                }
                return current_user_can('update_core');
            }
        ));

        register_rest_route('wp-storybook/sync', 'import', array(
            'methods'  => 'POST',
            'callback' => [$this, 'import_data'],
            'permission_callback' => function () {
                if (WP_DEBUG) {
                    return true;
                }
                return current_user_can('update_core');
            }
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

    public function import_data(\WP_REST_Request $req)
    {
        // if (!current_user_can('publish_posts')) {
        //     return wp_send_json_error(null, 401);
        // }

        // Prep
        $author_id = get_current_user_id();
        // if (!$author_id) {
        //     return wp_send_json_error(null, 401);
        // }

        $posts = $req->get_json_params();
        $postType = get_option('wpsb_post_type_slug');

        foreach ($posts as $post) {
            $slug = $post['post_name'];
            $title = $post['post_title'];

            unset($post['ID']);
            // If the page doesn't already exist, then create it (by title & slug)
            $existing_post = get_posts(array('name' => $slug, 'post_type' => $postType));
            if (count($existing_post) > 0) {
                $post['ID'] = $existing_post[0]->ID;
            }

            unset($post['guid']);
            unset($post['post_parent']);
            $post['post_type'] = $postType;
            $post_id = wp_insert_post($post);

            $this->sync_terms($post['categories']);
            wp_set_object_terms($post_id, array_column($post['categories'], 'slug'), $this->taxonomySlug);
        }
    }

    public function sync_terms($terms)
    {
        foreach ($terms as $term) {
            $existing_term = term_exists($term['slug'], $this->taxonomySlug);
            if ($existing_term !== 0 && $existing_term !== null) {
                wp_update_term(
                    $existing_term['term_id'],
                    $this->taxonomySlug,
                    array(
                        'name' => $term['name'],
                        'description' => $term['description'],
                    )
                );
            } else {
                wp_insert_term(
                    $term['name'],
                    $this->taxonomySlug,
                    array(
                        'slug' => $term['slug'],
                        'description' => $term['description'],
                    )
                );
            }
        }
    }
}
