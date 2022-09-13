<?php

namespace WpStorybook\Cli;

use WpStorybook\AcfBlocks\Stories as AcfStories;
use WpStorybook\GutenbergBlocks\AdminStories;
use WpStorybook\Helpers\Str;

class StorybookCommand
{
    public function acf()
    {
        (new AcfStories())->create_stories_for_all_blocks();
        \WP_CLI::log('ACF stories have been generated.');
    }

    public function admin_stories()
    {
        (new AdminStories())->create_all_stories();
        \WP_CLI::log('WP admin created stories have been generated.');
    }
}

if (class_exists('WP_CLI')) {
    $instance = new StorybookCommand();
    \WP_CLI::add_command('wp-storybook', $instance);
}
