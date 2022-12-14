<?php

namespace WpStorybook\PostTypes;

/**
 * @package WpStorybook
 * @since 1.0
 */
class StoryViewer
{
    protected $taxonomySlug;
    protected $postTypeSlug;

    public function __construct()
    {
        $this->taxonomySlug = get_option('wpsb_taxonomy_slug');
        $this->postTypeSlug = get_option('wpsb_post_type_slug');
    }
    /**
     * Initialize the actions and filters for the class.
     **/
    public function init()
    {
        add_action('init', array($this, 'setup_post_type'), 0);
        add_action('init', array($this, 'register_category_taxonomy'), 0);
        add_action('save_post', [$this, 'save_post_meta']);
        add_filter('block_type_metadata', [$this, 'add_post_type_to_block_meta_data']);
    }

    /**
     * Register the post type
     */
    public function setup_post_type()
    {
        $labels = array(
            'name'                  => _x('SB Stories', 'Post Type General Name', 'wp_storybook'),
            'singular_name'         => _x('SB Story', 'Post Type Singular Name', 'wp_storybook'),
            'menu_name'             => __('SB Stories', 'wp_storybook'),
            'name_admin_bar'        => __('SB Story', 'wp_storybook'),
            'archives'              => __('SB Story Archives', 'wp_storybook'),
            'attributes'            => __('SB Story Attributes', 'wp_storybook'),
            'parent_item_colon'     => __('Parent Item:', 'wp_storybook'),
            'all_items'             => __('All SB Stories', 'wp_storybook'),
            'add_new_item'          => __('Add New SB Story', 'wp_storybook'),
            'add_new'               => __('Add New', 'wp_storybook'),
            'new_item'              => __('New SB Story', 'wp_storybook'),
            'edit_item'             => __('Edit SB Story', 'wp_storybook'),
            'update_item'           => __('Update SB Story', 'wp_storybook'),
            'view_item'             => __('View SB Story', 'wp_storybook'),
            'view_items'            => __('View SB Stories', 'wp_storybook'),
            'search_items'          => __('Search SB Story', 'wp_storybook'),
            'not_found'             => __('Not found', 'wp_storybook'),
            'not_found_in_trash'    => __('Not found in Trash', 'wp_storybook'),
            'featured_image'        => __('Featured Image', 'wp_storybook'),
            'set_featured_image'    => __('Set featured image', 'wp_storybook'),
            'remove_featured_image' => __('Remove featured image', 'wp_storybook'),
            'use_featured_image'    => __('Use as featured image', 'wp_storybook'),
            'insert_into_item'      => __('Insert into SB Story', 'wp_storybook'),
            'uploaded_to_this_item' => __('Uploaded to this SB Story', 'wp_storybook'),
            'items_list'            => __('SB Stories list', 'wp_storybook'),
            'items_list_navigation' => __('SB Stories list navigation', 'wp_storybook'),
            'filter_items_list'     => __('Filter SB Stories list', 'wp_storybook'),
        );
        $args   = array(
            'label'               => __('SB Story', 'wp_storybook'),
            'description'         => __('Add Storybook Stories built with Gutenberg blocks', 'wp_storybook'),
            'labels'              => $labels,
            'supports'            => array('title', 'editor'),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 40,
            'menu_icon'           => 'dashicons-editor-table',
            'show_in_admin_bar'   => false,
            'show_in_nav_menus'   => false,
            'can_export'          => true,
            'has_archive'         => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => true,
            'rewrite'             => false,
            'register_meta_box_cb' => array($this, 'post_type_meta_meta_box'),
            'capabilities'        => array(
                'edit_post'          => 'update_core',
                'read_post'          => 'update_core',
                'delete_post'        => 'update_core',
                'edit_posts'         => 'update_core',
                'edit_others_posts'  => 'update_core',
                'delete_posts'       => 'update_core',
                'publish_posts'      => 'update_core',
                'read_private_posts' => 'update_core',
            ),
            'show_in_rest'        => true,
            'taxonomies'          => array($this->taxonomySlug),
        );
        register_post_type($this->postTypeSlug, $args);
    }

    // Register Block Viewer Category Taxonomy
    public function register_category_taxonomy()
    {

        $labels = array(
            'name'                       => _x('Categories', 'Taxonomy General Name', 'wp_storybook'),
            'description'                   => __('Grouping of stories', 'wp_storybook'),
            'singular_name'              => _x('Category', 'Taxonomy Singular Name', 'wp_storybook'),
            'menu_name'                  => __('Category', 'wp_storybook'),
            'all_items'                  => __('All Categories', 'wp_storybook'),
            'parent_item'                => __('Parent Category', 'wp_storybook'),
            'parent_item_colon'          => __('Parent Category:', 'wp_storybook'),
            'new_item_name'              => __('New Category Name', 'wp_storybook'),
            'add_new_item'               => __('Add New Category', 'wp_storybook'),
            'edit_item'                  => __('Edit Category', 'wp_storybook'),
            'update_item'                => __('Update Category', 'wp_storybook'),
            'view_item'                  => __('View Category', 'wp_storybook'),
            'separate_items_with_commas' => __('Separate categories with commas', 'wp_storybook'),
            'add_or_remove_items'        => __('Add or remove categories', 'wp_storybook'),
            'choose_from_most_used'      => __('Choose from the most used', 'wp_storybook'),
            'popular_items'              => __('Popular Categories', 'wp_storybook'),
            'search_items'               => __('Search Categories', 'wp_storybook'),
            'not_found'                  => __('Not Found', 'wp_storybook'),
            'no_terms'                   => __('No Categories', 'wp_storybook'),
            'items_list'                 => __('Categories list', 'wp_storybook'),
            'items_list_navigation'      => __('Categories list navigation', 'wp_storybook'),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => false,
            'show_tagcloud'              => false,
            'show_in_rest' => true
        );
        register_taxonomy($this->taxonomySlug, array($this->postTypeSlug), $args);
    }

    public function post_type_meta_meta_box()
    {
        add_meta_box(
            'wp-storybook-options',
            __('Story Options', 'wp-storybook'),
            [$this, 'render_meta_box'],
            $this->postTypeSlug,
            'side',
        );
    }

    public function render_meta_box($post)
    {
        wp_nonce_field('wp_storybook_meta_nonce', 'wp_storybook_meta_nonce');

        $testingMeta = get_post_meta($post->ID, 'prevent_automated_testing', true);

?>
        <input type="checkbox" name="prevent_automated_testing" id="prevent_automated_testing" value="1" <?php if (isset($testingMeta)) checked($testingMeta, '1'); ?> />Prevent Automated Testing
<?php
    }

    /**
     * When the post is saved, saves our custom data.
     *
     * @param int $post_id
     */
    function save_post_meta($post_id)
    {
        // Check if our nonce is set.
        if (!isset($_POST['wp_storybook_meta_nonce'])) {
            return;
        }

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($_POST['wp_storybook_meta_nonce'], 'wp_storybook_meta_nonce')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        /* OK, it's safe for us to save the data now. */
        if (isset($_POST['prevent_automated_testing'])) {
            update_post_meta($post_id, 'prevent_automated_testing', '1');
        } else {
            update_post_meta($post_id, 'prevent_automated_testing', '0');
        }
    }

    public function add_post_type_to_block_meta_data($metadata)
    {
        if (isset($metadata['acf']['postTypes'])) {
            $metadata['acf']['postTypes'][] = $this->postTypeSlug;
        }

        return $metadata;
    }
}
