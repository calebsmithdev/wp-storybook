<?php

namespace WpStorybook\StoryGenerator;

class StoryRegister
{
    public static $registered_story_classes = array();

    public static function register($story_name, Stories $stories)
    {
        self::$registered_story_classes[$story_name] = $stories;
    }
    public static function get_registered_story_classes()
    {
        return self::$registered_story_classes;
    }
}
