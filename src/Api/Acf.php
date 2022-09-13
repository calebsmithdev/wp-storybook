<?php

/**
 * @package WpStorybook
 * @since 1.0
 */

namespace WpStorybook\Api;

use WP_Query;
use WP_REST_Server;

class Acf
{
    protected $postTypeSlug;

    public function __construct()
    {
        $this->postTypeSlug = get_option('wpsb_post_type_slug');
    }

    public function init()
    {
        add_action('rest_api_init', [$this, 'define_rest_endpoints']);
    }

    public function define_rest_endpoints()
    {
        register_rest_route('wp-storybook/acf', 'all', array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_blocks'],
            'permission_callback' => '__return_true',
        ));

        register_rest_route('wp-storybook/acf', 'metadata', array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_block_metadata'],
            'permission_callback' => '__return_true',
        ));

        register_rest_route('wp-storybook/acf', 'by-id', array(
            'methods'  => 'POST',
            'callback' => [$this, 'get_block_by_id'],
            'permission_callback' => '__return_true',
        ));

        register_rest_route('wp-storybook/acf', 'all-patterns', array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_patterns'],
            'permission_callback' => '__return_true',
        ));
    }

    public function get_patterns()
    {
        $query = new WP_Query([
            'post_type' => $this->postTypeSlug,
            'posts_per_page' => -1
        ]);

        $output = [];
        while ($query->have_posts()) {
            $query->the_post();
            $output = [
                'title' => get_the_title(),
                'slug' => get_post_field('post_name'),
                'content' => get_the_content(),
                'category' => 'Test'
            ];
        }

        return $output;
    }

    public function get_blocks()
    {
        $blocks = acf_get_block_types();
        wp_send_json($blocks);
    }

    public function get_block_metadata(\WP_REST_Request $request)
    {
        $blockId = $request->get_param('id');
        $blocks = acf_get_block_types();
        $block = $blocks[$blockId];

        $enqueuedStyles = [];
        $block['allStyles'] = apply_filters('wpsb_add_style_link', $enqueuedStyles);

        wp_send_json($block);
    }

    public function get_block_by_id(\WP_REST_Request $request)
    {
        $blockId = $request->get_param('id');
        $block = $request->get_param('block');
        // Get request args.
        $args = acf_request_args(
            array(
                'block'   => false,
                'post_id' => 0,
                'query'   => array(),
                'context' => array(),
            )
        );

        $block       = $block;
        $query       = $args['query'];
        $post_id     = 15;

        // Bail early if no block.
        if (!$block) {
            wp_send_json_error();
        }

        // Prepare block ensuring all settings and attributes exist.
        if (!$block = acf_prepare_block($block)) {
            wp_send_json_error();
        }

        // Load field defaults when first previewing a block.
        if (!empty($query['preview']) && !$block['data']) {
            $fields = acf_get_block_fields($block);
            foreach ($fields as $field) {
                $block['data']["_{$field['name']}"] = $field['key'];
            }
        }

        // Setup postdata allowing form to load meta.
        acf_setup_meta($block['data'], $block['id'], true);

        // Setup main postdata for post_id.
        global $post;
        $post = get_post($post_id);
        setup_postdata($post);

        // Vars.
        $response = array();

        // Query form.
        if (!empty($query['form'])) {

            // Load fields for form.
            $fields = acf_get_block_fields($block);

            // Prefix field inputs to avoid multiple blocks using the same name/id attributes.
            acf_prefix_fields($fields, "acf-{$block['id']}");

            // Start Capture.
            ob_start();

            // Render.
            echo '<div class="acf-block-fields acf-fields">';
            acf_render_fields($fields, $block['id'], 'div', 'field');
            echo '</div>';

            // Store Capture.
            $response['form'] = ob_get_contents();
            ob_end_clean();
        }

        // Render_callback vars.
        $content    = '';
        $is_preview = true;

        return acf_rendered_block($block, $content, $is_preview, $post_id, null, false);
    }
}
