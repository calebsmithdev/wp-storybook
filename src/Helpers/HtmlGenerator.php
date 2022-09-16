<?php

namespace WpStorybook\Helpers;

class HtmlGenerator
{
    public static function load_head()
    {
        ob_start();
?>
        <div data-target="head">
            <?php do_action('wp_head'); ?>
        </div>
<?php
        $var = ob_get_contents();
        ob_end_clean();

        return $var;
    }

    public static function load_footer()
    {
        ob_start();
        wp_footer();
        $var = ob_get_contents();
        ob_end_clean();
        return $var;
    }
}
