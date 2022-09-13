<?php

/**
 * @package WpStorybook
 * @since 1.0
 */

namespace WpStorybook\AcfBlocks;

use WpStorybook\Helpers\Str;
use WpStorybook\AcfBlocks\Helpers as AcfHelpers;

class Stories
{
    protected $storybookDir;

    public function __construct()
    {
        $this->storybookDir = get_option('wpsb_storybook_path');
    }

    public function init()
    {
        add_action('init', [$this, 'add_cors_http_header']);
    }

    function add_cors_http_header()
    {
        if (WP_DEBUG === null || !WP_DEBUG) {
            return;
        }
        header("Access-Control-Allow-Origin: *");
    }

    public function create_stories_for_all_blocks()
    {
        // Remove all old stories
        if (file_exists($this->storybookDir . '/acf-stories/')) {
            array_map('unlink', array_filter((array) glob($this->storybookDir . '/acf-stories/' . "*")));
        }

        // Create the folder
        if (!file_exists($this->storybookDir . '/acf-stories/')) {
            mkdir($this->storybookDir . '/acf-stories/', 0777, true);
        }

        $blocks =  AcfHelpers::getBlocks();
        foreach ($blocks as $block) {
            $block = AcfHelpers::getBlockMetadata($block['name']);
            $fields = acf_get_block_fields($block);
            $this->generate_story($block, $fields);
        }
    }

    public function generate_story($block, $fields)
    {
        // var_dump($block['example']);die();
        $filename = Str::upperCamelCase($block['title']) . '.stories.js';
        $fieldsOutput = '';
        $examplesOutput = '';
        foreach ($fields as $field) {
            $type = $this->get_control_type($field['type']);
            $fieldsOutput .= "{$field['name']}: {\r\n";
            $fieldsOutput .= "name: '{$field['label']}',\r\n";
            if ($type == 'enum') {
                $option_values = $this->get_field_option_values($field);
                $option_labels = $this->get_field_option_labels($field);
                $fieldsOutput .= "options: ['{$option_values}'],\r\n";
                $fieldsOutput .= "control: {\r\n";
                $fieldsOutput .= "type: 'select',\r\n";
                $fieldsOutput .= "labels: {{$option_labels}},\r\n";
                $fieldsOutput .= "},\r\n";
            } else {
                $fieldsOutput .= "control: '{$type}',\r\n";
            }
            if (isset($field['default_value'])) {
                $fieldsOutput .= "defaultValue: '{$field['default_value']}',\r\n";
            }
            $fieldsOutput .= "},\r\n";
        }

        if (isset($block['example'])) {
            foreach ($block['example'] as $key => $example) {
                $dataFieldOutput = '';
                foreach ($example['data'] as $field => $value) {
                    if ($field == 'is_preview') continue;
                    $dataFieldOutput .= "\r\n{$field}: '{$value}',";
                }
                if ($key == 'attributes') {
                    $examplesOutput .=     'export const Preview = Template.bind({});
									Preview.args = {
									storyName: \'Preview\',' . $dataFieldOutput . '
								};
								';
                } else {
                    $examplesOutput .=     'export const ' . $key . ' = Template.bind({});
									' . $key . '.args = {
									storyName: \'' . $key . '\',
									' . $dataFieldOutput . '
								};
								';
                }
            }
        }

        $defaultOutput = 'export const Default = Template.bind({});';
        $defaultOutput .= 'Default.args = {';
        $defaultOutput .= 'storyName: \'Default\',';
        if (isset($block['default_storybook'])) {
            foreach ($block['default_storybook'] as $field => $value) {
                $defaultOutput .= "\r\n{$field}: '{$value}',";
            }
        }
        $defaultOutput .= ' };';

        $output = 'import GetBlockHtml from \'../components/GetBlockHtml.vue\';
				export default {
					title: \'Blocks/' . $block['title'] . '\',
					parameters: {
                        viewMode: \'story\',
						layout: \'fullscreen\',
                        controls: { hideNoControlsWarning: true },
                        options: { showPanel: false }
					},
					args: {
						blockId: \'' . $block['name'] . '\',
						className: \'\'
					},
					argTypes: {
						storyName: {
							table: {
								disable: true,
							},
						},
						className: {
							table: {
								disable: true,
							},
						},
						blockId: {
							table: {
								disable: true,
							},
						},
						' . $fieldsOutput . '
					}
				};

				const Template = (args) => ({
					components: { GetBlockHtml },
					setup() { return { args }; },
					template: \'<get-block-html v-bind="args" />\',
				});
                ' . $defaultOutput . '
				' . $examplesOutput;

        file_put_contents($this->storybookDir . '/acf-stories/' . $filename, $output);
    }

    private function get_field_option_labels($field)
    {
        $output = '';
        foreach ($field['choices'] as $key => $value) {
            $output .= "'{$key}': '{$value}',";
        }

        return $output;
    }

    private function get_field_option_values($field)
    {
        $keys = implode("', '", array_keys($field['choices']));

        return $keys;
    }

    private function get_control_type($type)
    {
        switch ($type) {
            case 'color_picker':
                return 'color';
            case 'image':
                return 'file';
            case 'select':
                return 'enum';
            default:
                return $type;
        }
    }
}
