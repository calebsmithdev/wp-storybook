<?php

/**
 * @package WpStorybook
 * @since 1.0
 */

namespace WpStorybook\GutenbergBlocks;

use WpStorybook\Helpers\Str;

class AdminStories
{
    protected $storybookDir;
    protected $storybookStoriesDir;
    protected $taxonomySlug;
    protected $postTypeSlug;

    public function __construct()
    {
        $this->taxonomySlug = get_option('wpsb_taxonomy_slug');
        $this->postTypeSlug = get_option('wpsb_post_type_slug');
        $this->storybookDir = get_option('wpsb_storybook_path');
        $this->storybookStoriesDir = $this->storybookDir . '/gutenberg-admin-stories/';
    }

    public function create_all_stories()
    {
        // Remove all old stories
        if (file_exists($this->storybookStoriesDir)) {
            array_map('unlink', array_filter((array) glob($this->storybookStoriesDir . "*")));
        }

        // Create the folder
        if (!file_exists($this->storybookStoriesDir)) {
            mkdir($this->storybookStoriesDir, 0777, true);
        }

        $cat_terms = get_terms($this->taxonomySlug);

        // get posts without term
        $query = new \WP_Query([
            'post_type' => $this->postTypeSlug,
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => $this->taxonomySlug,
                    'operator' => 'NOT EXISTS'
                )
            )
        ]);
        if ($query->post_count > 0) {
            $patterns = ['category' => '', 'patterns' => []];
            while ($query->have_posts()) {
                $query->the_post();
                $new_pattern = [
                    'title' => get_the_title(),
                    'slug' => get_post_field('post_name'),
                    'content' => apply_filters('the_content', get_post_field('post_content')),
                    'prevent_testing' => false
                ];

                $prevent_testing = get_post_meta(get_the_ID(), 'prevent_automated_testing', true);
                $new_pattern['prevent_testing'] = $prevent_testing == '1' ? 'true' :  'false';

                $patterns['patterns'][] = $new_pattern;
            }

            $this->generate_story($patterns);
        }

        // get posts grouped by term
        foreach ($cat_terms as $term) {
            // Get all patterns
            $termQuery = new \WP_Query([
                'post_type' => $this->postTypeSlug,
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => $this->taxonomySlug,
                        'field' => 'slug',
                        'terms' => array($term->slug),
                        'operator' => 'IN'
                    )
                )
            ]);
            $patterns = ['category' => $term->name, 'patterns' => []];
            while ($termQuery->have_posts()) {
                $termQuery->the_post();
                $new_pattern = [
                    'title' => get_the_title(),
                    'slug' => get_post_field('post_name'),
                    'content' => apply_filters('the_content', get_post_field('post_content')),
                    'prevent_testing' => false
                ];

                $prevent_testing = get_post_meta(get_the_ID(), 'prevent_automated_testing', true);
                $new_pattern['prevent_testing'] = $prevent_testing == '1' ? 'true' :  'false';

                $patterns['patterns'][] = $new_pattern;
            }
            $this->generate_story($patterns);
        }
    }

    public function create_stories_for_pattern($post_id, $post, $update)
    {
        // Auto-draft before creation, do nothing.
        if (isset($post->post_status) && 'auto-draft' == $post->post_status) {
            return;
        }

        // Autosave, do nothing
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        // AJAX? Not used here
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }

        // Return if it's a post revision
        if (false !== wp_is_post_revision($post_id)) {
            return;
        }

        if ('block_viewer' !== $post->post_type) {
            return;
        }

        $pattern = [
            'title' => $post->post_title,
            'slug' => $post->post_name,
            'content' => $post->post_content,
            'category' => 'Test'
        ];

        // Remove all old stories
        if (file_exists($this->storybookStoriesDir)) {
            array_map('unlink', array_filter((array) glob($this->storybookStoriesDir . "*")));
        }

        $cat_terms = get_terms($this->taxonomySlug);

        // get posts without term
        $query = new \WP_Query([
            'post_type' => $this->postTypeSlug,
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => $this->taxonomySlug,
                    'operator' => 'NOT EXISTS'
                )
            )
        ]);
        if ($query->post_count > 0) {
            $patterns = ['category' => '', 'patterns' => []];
            while ($query->have_posts()) {
                $query->the_post();
                $new_pattern = [
                    'title' => get_the_title(),
                    'slug' => get_post_field('post_name'),
                    'content' => apply_filters('the_content', get_post_field('post_content')),
                    'prevent_testing' => false
                ];

                $prevent_testing = get_post_meta(get_the_ID(), 'prevent_automated_testing', true);
                $new_pattern['prevent_testing'] = $prevent_testing == '1' ? 'true' :  'false';

                $patterns['patterns'][] = $new_pattern;
            }

            $this->generate_story($patterns);
        }

        // get posts grouped by term
        foreach ($cat_terms as $term) {
            // Get all patterns
            $termQuery = new \WP_Query([
                'post_type' => $this->postTypeSlug,
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => $this->taxonomySlug,
                        'field' => 'slug',
                        'terms' => array($term->slug),
                        'operator' => 'IN'
                    )
                )
            ]);
            $patterns = ['category' => $term->name, 'patterns' => []];
            while ($termQuery->have_posts()) {
                $termQuery->the_post();
                $new_pattern = [
                    'title' => get_the_title(),
                    'slug' => get_post_field('post_name'),
                    'content' => apply_filters('the_content', get_post_field('post_content')),
                    'prevent_testing' => false
                ];

                $prevent_testing = get_post_meta(get_the_ID(), 'prevent_automated_testing', true);
                $new_pattern['prevent_testing'] = $prevent_testing == '1' ? 'true' :  'false';

                $patterns['patterns'][] = $new_pattern;
            }
            $this->generate_story($patterns);
        }
    }

    public function generate_story($category)
    {
        $categoryName = Str::isNullOrEmptyString($category['category']) ? 'General' : $category['category'];
        $filename = Str::upperCamelCase($categoryName) . '.stories.js';
        $title = 'Patterns/' . $categoryName;
        $examplesOutput = '';
        foreach ($category['patterns'] as $pattern) {
            $patternTitle = Str::upperCamelCase($pattern['title']);
            $examplesOutput .=     'export const ' . $patternTitle . ' = Template.bind({});
                                        ' . $patternTitle . '.args = {
                                        storyName: \'' . $pattern['title'] . '\',
                                        content: ' . json_encode($pattern['content']) . ',
                                        preventTesting: ' . $pattern['prevent_testing'] . '
                                    };
                                    ';
        }

        $output = 'import ShowBlockHtml from \'../components/ShowBlockHtml.vue\';
				export default {
					title: \'' . $title  . '\',
					parameters: {
                        viewMode: \'story\',
						layout: \'fullscreen\',
                        controls: { hideNoControlsWarning: true, disabled: true },
                        options: { showPanel: false }
					},
					argTypes: {
						storyName: {
							table: {
								disable: true,
							},
						},
						blockId: {
							table: {
								disable: true,
							},
						}
					}
				};

				const Template = (args) => ({
					components: { ShowBlockHtml },
					setup() { return { args }; },
					template: \'<show-block-html v-bind="args" />\',
				});

				' . $examplesOutput;

        file_put_contents($this->storybookStoriesDir . $filename, $output);
    }
}
