<?php
/**
 * Recipe Blocks Support
 */

class Recipe_Blocks {
    
    public function __construct() {
        add_action('init', array($this, 'register_block_patterns'));
        add_filter('allowed_block_types_all', array($this, 'allowed_block_types'), 10, 2);
        add_action('rest_api_init', array($this, 'register_rest_fields'));
    }
    
    public function register_block_patterns() {
        // Register recipe pattern category
        if (function_exists('register_block_pattern_category')) {
            register_block_pattern_category(
                'recipe-patterns',
                array('label' => __('Recipe Patterns', 'recipe-challenge-pro'))
            );
        }
    }
    
    public function allowed_block_types($allowed_blocks, $editor_context) {
        if (!empty($editor_context->post) && 'recipe' === $editor_context->post->post_type) {
            // Allow all blocks for recipes
            return true;
        }
        return $allowed_blocks;
    }
    
    public function register_rest_fields() {
        register_rest_field('recipe', 'recipe_meta', array(
            'get_callback' => array($this, 'get_recipe_meta'),
            'update_callback' => array($this, 'update_recipe_meta'),
            'schema' => array(
                'type' => 'object',
                'properties' => array(
                    'prep_time' => array('type' => 'integer'),
                    'cook_time' => array('type' => 'integer'),
                    'total_time' => array('type' => 'integer'),
                    'servings' => array('type' => 'integer'),
                    'difficulty' => array('type' => 'string')
                )
            )
        ));
        
        register_rest_field('recipe', 'recipe_ingredients', array(
            'get_callback' => array($this, 'get_recipe_ingredients'),
            'schema' => array(
                'type' => 'array',
                'items' => array(
                    'type' => 'object',
                    'properties' => array(
                        'amount' => array('type' => 'string'),
                        'unit' => array('type' => 'string'),
                        'ingredient' => array('type' => 'string'),
                        'notes' => array('type' => 'string')
                    )
                )
            )
        ));
        
        register_rest_field('recipe', 'recipe_instructions', array(
            'get_callback' => array($this, 'get_recipe_instructions'),
            'schema' => array(
                'type' => 'array',
                'items' => array('type' => 'string')
            )
        ));
    }
    
    public function get_recipe_meta($post) {
        return array(
            'prep_time' => get_post_meta($post['id'], '_recipe_prep_time', true),
            'cook_time' => get_post_meta($post['id'], '_recipe_cook_time', true),
            'total_time' => get_post_meta($post['id'], '_recipe_total_time', true),
            'servings' => get_post_meta($post['id'], '_recipe_servings', true),
            'difficulty' => get_post_meta($post['id'], '_recipe_difficulty', true)
        );
    }
    
    public function get_recipe_ingredients($post) {
        $ingredients = get_post_meta($post['id'], '_recipe_ingredients', true);
        return is_array($ingredients) ? $ingredients : array();
    }
    
    public function get_recipe_instructions($post) {
        $instructions = get_post_meta($post['id'], '_recipe_instructions', true);
        return is_array($instructions) ? $instructions : array();
    }
    
    public function update_recipe_meta($value, $post) {
        if (isset($value['prep_time'])) {
            update_post_meta($post->ID, '_recipe_prep_time', intval($value['prep_time']));
        }
        if (isset($value['cook_time'])) {
            update_post_meta($post->ID, '_recipe_cook_time', intval($value['cook_time']));
        }
        if (isset($value['total_time'])) {
            update_post_meta($post->ID, '_recipe_total_time', intval($value['total_time']));
        }
        if (isset($value['servings'])) {
            update_post_meta($post->ID, '_recipe_servings', intval($value['servings']));
        }
        if (isset($value['difficulty'])) {
            update_post_meta($post->ID, '_recipe_difficulty', sanitize_text_field($value['difficulty']));
        }
        return true;
    }
}