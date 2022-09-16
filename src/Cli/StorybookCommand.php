<?php

namespace WpStorybook\Cli;

use WpStorybook\AcfBlocks\Stories as AcfStories;
use WpStorybook\GutenbergBlocks\AdminStories;
use WpStorybook\Helpers\Str;
use WpStorybook\StoryGenerator\ClassStories;

class StorybookCommand
{
    public function acf()
    {
        $allowCreateStories = get_option('wpsb_create_acf_stories');
        if (!$allowCreateStories) {
            return \WP_CLI::log('You have disabled ACF Stories.');
        }

        if (class_exists('ACF')) {
            (new AcfStories())->create_stories_for_all_blocks();
            \WP_CLI::log('ACF stories have been generated.');
        } else {
            \WP_CLI::log('You must have ACF installed to use this command.');
        }
    }

    public function admin_stories()
    {
        (new AdminStories())->create_all_stories();
        \WP_CLI::log('WP admin created stories have been generated.');
    }

    public function class_stories()
    {
        (new ClassStories())->create_all_stories();
        \WP_CLI::log('Class based created stories have been generated.');
    }
}

if (class_exists('WP_CLI')) {
    $instance = new StorybookCommand();
    \WP_CLI::add_command('wp-storybook', $instance);
}
