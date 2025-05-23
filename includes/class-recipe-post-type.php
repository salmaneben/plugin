<?php
class Recipe_Post_Type {
    
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomies'));
        add_filter('single_template', array($this, 'recipe_single_template'));
        add_filter('archive_template', array($this, 'recipe_archive_template'));
    }
    
    public function register_post_type() {
        $labels = array(
            'name' => __('Recipes', 'recipe-challenge-pro'),
            'singular_name' => __('Recipe', 'recipe-challenge-pro'),
            'add_new' => __('Add New Recipe', 'recipe-challenge-pro'),
            'add_new_item' => __('Add New Recipe', 'recipe-challenge-pro'),
            'edit_item' => __('Edit Recipe', 'recipe-challenge-pro'),
            'new_item' => __('New Recipe', 'recipe-challenge-pro'),
            'view_item' => __('View Recipe', 'recipe-challenge-pro'),
            'search_items' => __('Search Recipes', 'recipe-challenge-pro'),
            'not_found' => __('No recipes found', 'recipe-challenge-pro'),
            'not_found_in_trash' => __('No recipes found in trash', 'recipe-challenge-pro'),
            'menu_name' => __('Recipes', 'recipe-challenge-pro')
        );
        
        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'recipe'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-food',
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'comments', 'custom-fields', 'revisions'),
            'show_in_rest' => true,
            'rest_base' => 'recipes',
            'rest_controller_class' => 'WP_REST_Posts_Controller'
        );
        
        register_post_type('recipe', $args);
    }
    
    public function register_taxonomies() {
        // Cuisine Taxonomy
        $cuisine_labels = array(
            'name' => __('Cuisines', 'recipe-challenge-pro'),
            'singular_name' => __('Cuisine', 'recipe-challenge-pro'),
            'search_items' => __('Search Cuisines', 'recipe-challenge-pro'),
            'all_items' => __('All Cuisines', 'recipe-challenge-pro'),
            'edit_item' => __('Edit Cuisine', 'recipe-challenge-pro'),
            'update_item' => __('Update Cuisine', 'recipe-challenge-pro'),
            'add_new_item' => __('Add New Cuisine', 'recipe-challenge-pro'),
            'new_item_name' => __('New Cuisine Name', 'recipe-challenge-pro'),
            'menu_name' => __('Cuisines', 'recipe-challenge-pro')
        );
        
        register_taxonomy('recipe_cuisine', 'recipe', array(
            'labels' => $cuisine_labels,
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'cuisine'),
            'show_in_rest' => true
        ));
        
        // Course Taxonomy
        $course_labels = array(
            'name' => __('Courses', 'recipe-challenge-pro'),
            'singular_name' => __('Course', 'recipe-challenge-pro'),
            'search_items' => __('Search Courses', 'recipe-challenge-pro'),
            'all_items' => __('All Courses', 'recipe-challenge-pro'),
            'edit_item' => __('Edit Course', 'recipe-challenge-pro'),
            'update_item' => __('Update Course', 'recipe-challenge-pro'),
            'add_new_item' => __('Add New Course', 'recipe-challenge-pro'),
            'new_item_name' => __('New Course Name', 'recipe-challenge-pro'),
            'menu_name' => __('Courses', 'recipe-challenge-pro')
        );
        
        register_taxonomy('recipe_course', 'recipe', array(
            'labels' => $course_labels,
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'course'),
            'show_in_rest' => true
        ));
    }
    
    public function recipe_single_template($template) {
        if (is_singular('recipe')) {
            $custom_template = RCP_PLUGIN_DIR . 'templates/single-recipe.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }
    
    public function recipe_archive_template($template) {
        if (is_post_type_archive('recipe')) {
            $custom_template = RCP_PLUGIN_DIR . 'templates/archive-recipe.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }
}