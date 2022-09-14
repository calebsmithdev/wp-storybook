<?php

/**
 * @package WpStorybook
 * @since 1.0
 */

namespace WpStorybook\Pages;

use GuzzleHttp\Client;

class SyncSettingsPage
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
        add_action('admin_menu', [$this, 'cpt_menu_addition']);
    }

    function cpt_menu_addition()
    {
        add_submenu_page(
            "edit.php?post_type={$this->postTypeSlug}",
            __('Import SB Stories', 'wp_storybook'),
            __('Import SB Stories', 'wp_storybook'),
            'manage_options',
            'wp_storybook_import',
            [$this, 'storybook_page']
        );
    }

    public function storybook_page()
    {
        if (isset($_POST['submit'])) {
            $site_url = $_POST['sync_sb_stories_settings']['site_url'];
            $site_url = rtrim($site_url, "/");
            $response = wp_remote_get($site_url . '/wp-json/wp-storybook/sync/export');
            if (is_wp_error($response) || (200 != wp_remote_retrieve_response_code($response))) {
                return;
            }

            $exported_data = json_decode($response['body'], true);
            $this->import_data($exported_data);
        }
?>
        <div class="wrap">
            <h1>Import StoryBook Stories</h1>
            <div>
                <h2 style="margin-top: 20px;">Settings </h2>
                <form method="post">
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">Site URL</th>
                                <td>
                                    <input type="text" name="sync_sb_stories_settings[site_url]" id="site_url" class="" placeholder="https://example.com" value="" size="60">
                                    <p class="description">Enter the URL of the site you want to import existing StoryBook Stories from.<br>If the Story already exists here, then it will be updated. Otherwise - it will be created with the categories assigned to it.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <?php submit_button(esc_html__('Import Stories', 'wp_storybook')); ?>
                </form>
            </div>

    <?php
    }

    public function import_data($posts)
    {
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
