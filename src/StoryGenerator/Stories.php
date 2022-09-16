<?php

namespace WpStorybook\StoryGenerator;

class Stories
{
	/**
	 * Defaults to 'Global'.
	 */
	public $category = 'Global';

	/**
	 * If none is added, then the story will be part of the category only.
	 */
	public $subcategory = '';

	protected $content = '';

	public function render()
	{
	}

	public function load_template_part($template_name, $part_name = null)
	{
		ob_start();
		get_template_part($template_name, $part_name);
		$var = ob_get_contents();
		ob_end_clean();
		return $var;
	}
}
