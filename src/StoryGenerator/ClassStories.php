<?php

/**
 * @package WpStorybook
 * @since 1.0
 */

namespace WpStorybook\StoryGenerator;

use WpStorybook\Helpers\Str;

class ClassStories
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
        $this->storybookStoriesDir = $this->storybookDir . '/class-stories/';
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

        $stories = ['category' => '', 'patterns' => []];
        $classes = StoryRegister::get_registered_story_classes();

        foreach ($classes as $storyName => $class) {
            $stories[$class->category][$class->subcategory]['stories'][] = array(
                'title' => $storyName,
                'slug' => sanitize_title($storyName),
                'content' => $class->render()
            );
        }

        foreach ($stories as $category_name => $category) {
            foreach ($category as $subcategory_name => $subcategory) {
                $this->generate_story($category_name, $subcategory_name, $subcategory['stories']);
            }
        }
    }

    public function generate_story($category_name, $subcategory_name, $stories)
    {
        $full_title = '';
        if (!Str::isNullOrEmptyString($category_name)) {
            $full_title .= "{$category_name}/";
        }
        if (!Str::isNullOrEmptyString($subcategory_name)) {
            $full_title .= "{$subcategory_name}/";
            $filename = Str::upperCamelCase($subcategory_name) . '.stories.js';
        } else {
            $filename = Str::upperCamelCase($category_name) . '.stories.js';
        }
        $full_title .= "{$stories[0]['title']}";

        $examplesOutput = '';
        foreach ($stories as $story) {
            $patternTitle = Str::upperCamelCase($story['title']);
            $examplesOutput .=     'export const ' . $patternTitle . ' = Template.bind({});
                                        ' . $patternTitle . '.args = {
                                        storyName: \'' . $story['title'] . '\',
                                        content: ' . json_encode($story['content']) . ',
                                    };
                                    ';
        }

        $output = 'import ShowBlockHtml from \'../components/ShowBlockHtml.vue\';
				export default {
					title: \'' . $full_title  . '\',
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
