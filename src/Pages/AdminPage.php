<?php

/**
 * @package WpStorybook
 * @since 1.0
 */

namespace WpStorybook\Pages;

class AdminPage
{
    protected $storybookDir = '';

    public function __construct(string $storybookSrcPath = '')
    {
        $this->storybookDir = get_stylesheet_directory_uri() . '/storybook/storybook-static';
    }

    public function init()
    {
        add_action('admin_menu', [$this, 'settings_menu_adjustment']);
    }

    function settings_menu_adjustment()
    {
        add_options_page(
            __('Storybook', 'wp_storybook'),
            __('Storybook', 'wp_storybook'),
            'manage_options',
            'options_page_slug',
            [$this, 'storybook_page']
        );
    }

    public function storybook_page()
    {
?>
        <?php if (curl_init($this->storybookDir . '/index.html') !== false) : ?>
            <iframe src="<?php echo $this->storybookDir . '/index.html?date=' . date('c'); ?>" style="width: 100%;height: 90vh;"></iframe>
        <?php else : ?>
            <div style="display: flex; justify-content: center; align-items: center; height: 90vh;">
                <h2>Storybook is currently being built. Please check back again in a few minutes.</h2>
            </div>
        <?php endif; ?>

<?php
    }
}
