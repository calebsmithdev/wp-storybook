<?php

/**
 * @package WpStorybook
 * @since 1.0
 */

namespace WpStorybook\AcfBlocks;

class Helpers
{
    public static function getBlocks()
    {
        $blocks = acf_get_block_types();

        return $blocks;
    }

    public static function getBlockMetadata(string $blockId)
    {
        $blocks = acf_get_block_types();
        $block = $blocks[$blockId];

        $enqueuedStyles = [];
        $block['allStyles'] = apply_filters('wpsb_add_style_link', $enqueuedStyles);

        return $block;
    }
}
