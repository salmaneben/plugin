<?php
/**
 * Plugin Name: Recipe Challenge Pro
 * Plugin URI: https://recipeschallenge.net
 * Description: A comprehensive recipe management plugin for WordPress
 * Version: 1.0.0
 * Author: Recipe Challenge
 * Author URI: https://recipeschallenge.net
 * Text Domain: recipe-challenge-pro
 * Domain Path: /languages
 * License: GPL v2 or later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RCP_VERSION', '1.0.0');
define('RCP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RCP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RCP_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include necessary files
require_once RCP_PLUGIN_DIR . 'includes/class-recipe-post-type.php';
require_once RCP_PLUGIN_DIR . 'includes/class-recipe-meta-boxes.php';
require_once RCP_PLUGIN_DIR . 'includes/class-recipe-shortcodes.php';
require_once RCP_PLUGIN_DIR . 'includes/class-recipe-ajax.php';
require_once RCP_PLUGIN_DIR . 'includes/class-recipe-admin.php';
require_once RCP_PLUGIN_DIR . 'includes/class-recipe-frontend.php';
require_once RCP_PLUGIN_DIR . 'includes/class-recipe-favorites.php';
require_once RCP_PLUGIN_DIR . 'includes/class-recipe-widgets.php';
require_once RCP_PLUGIN_DIR . 'includes/class-recipe-blocks.php';
require_once RCP_PLUGIN_DIR . 'includes/recipe-functions.php';

// Initialize plugin
class Recipe_Challenge_Pro {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init_plugin'));
    }
    
    public function init_plugin() {
        $this->load_textdomain();
        $this->init_hooks();
        
        // Initialize classes
        new Recipe_Post_Type();
        new Recipe_Meta_Boxes();
        new Recipe_Shortcodes();
        new Recipe_Ajax();
        new Recipe_Admin();
        new Recipe_Frontend();
        new Recipe_Favorites();
        new Recipe_Blocks();
    }
    
    public function init_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('recipe-challenge-pro', false, dirname(RCP_PLUGIN_BASENAME) . '/languages');
    }
    
    public function enqueue_frontend_assets() {
        if (is_singular('recipe') || is_post_type_archive('recipe') || is_tax('recipe_cuisine') || is_tax('recipe_course')) {
            wp_enqueue_style('recipe-frontend', RCP_PLUGIN_URL . 'assets/css/recipe-frontend.css', array(), RCP_VERSION);
            wp_enqueue_script('recipe-scaling', RCP_PLUGIN_URL . 'assets/js/recipe-scaling.js', array('jquery'), RCP_VERSION, true);
            wp_enqueue_script('recipe-frontend', RCP_PLUGIN_URL . 'assets/js/recipe-frontend.js', array('jquery'), RCP_VERSION, true);
            
            wp_localize_script('recipe-frontend', 'recipe_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('recipe-ajax-nonce')
            ));
        }
    }
    
    public function enqueue_admin_assets($hook) {
        global $post_type;
        
        if ('recipe' === $post_type || (isset($_GET['post_type']) && $_GET['post_type'] === 'recipe')) {
            wp_enqueue_style('recipe-admin', RCP_PLUGIN_URL . 'assets/css/recipe-admin.css', array(), RCP_VERSION);
            wp_enqueue_script('recipe-admin', RCP_PLUGIN_URL . 'assets/js/recipe-admin.js', array('jquery', 'jquery-ui-sortable'), RCP_VERSION, true);
            
            wp_localize_script('recipe-admin', 'recipe_admin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('recipe-admin-nonce')
            ));
        }
    }
}

// Initialize plugin
function rcp_init() {
    return Recipe_Challenge_Pro::get_instance();
}
add_action('plugins_loaded', 'rcp_init');

// Activation hook
register_activation_hook(__FILE__, 'rcp_activate');
function rcp_activate() {
    // Flush rewrite rules
    $recipe_post_type = new Recipe_Post_Type();
    $recipe_post_type->register_post_type();
    $recipe_post_type->register_taxonomies();
    flush_rewrite_rules();
    
    // Set default options
    add_option('rcp_enable_ratings', 1);
    add_option('rcp_enable_favorites', 1);
    add_option('rcp_enable_print', 1);
    add_option('rcp_enable_jump_to_recipe', 1);
    add_option('rcp_default_servings', 4);
    add_option('rcp_recipe_slug', 'recipe');
    add_option('rcp_primary_color', '#f39c12');
    add_option('rcp_secondary_color', '#e74c3c');
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'rcp_deactivate');
function rcp_deactivate() {
    flush_rewrite_rules();
}