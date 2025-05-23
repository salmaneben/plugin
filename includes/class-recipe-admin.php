<?php
/**
 * Recipe Admin Class
 */

class Recipe_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_filter('manage_recipe_posts_columns', array($this, 'add_recipe_columns'));
        add_action('manage_recipe_posts_custom_column', array($this, 'render_recipe_columns'), 10, 2);
        add_filter('manage_edit-recipe_sortable_columns', array($this, 'make_columns_sortable'));
        add_action('pre_get_posts', array($this, 'sort_recipes_by_column'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_notices', array($this, 'admin_notices'));
    }
    
    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=recipe',
            __('All Recipes', 'recipe-challenge-pro'),
            __('All Recipes', 'recipe-challenge-pro'),
            'manage_options',
            'recipe-list',
            array($this, 'render_recipe_list_page')
        );
        
        add_submenu_page(
            'edit.php?post_type=recipe',
            __('Recipe Settings', 'recipe-challenge-pro'),
            __('Settings', 'recipe-challenge-pro'),
            'manage_options',
            'recipe-settings',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'edit.php?post_type=recipe',
            __('Import/Export', 'recipe-challenge-pro'),
            __('Import/Export', 'recipe-challenge-pro'),
            'manage_options',
            'recipe-import-export',
            array($this, 'render_import_export_page')
        );
    }
    
    /**
     * Add custom columns to recipe list
     */
    public function add_recipe_columns($columns) {
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            if ($key === 'title') {
                $new_columns['recipe_image'] = __('Image', 'recipe-challenge-pro');
                $new_columns['recipe_rating'] = __('Rating', 'recipe-challenge-pro');
                $new_columns['recipe_views'] = __('Views', 'recipe-challenge-pro');
                $new_columns['recipe_favorites'] = __('Favorites', 'recipe-challenge-pro');
            }
        }
        
        return $new_columns;
    }
    
    /**
     * Render custom column content
     */
    public function render_recipe_columns($column, $post_id) {
        switch ($column) {
            case 'recipe_image':
                if (has_post_thumbnail($post_id)) {
                    echo get_the_post_thumbnail($post_id, array(50, 50));
                } else {
                    echo '—';
                }
                break;
                
            case 'recipe_rating':
                $rating = get_post_meta($post_id, '_recipe_average_rating', true);
                $total = get_post_meta($post_id, '_recipe_total_ratings', true);
                
                if ($rating) {
                    echo '<span class="recipe-rating">';
                    echo '<span class="stars">' . $this->get_star_rating_html($rating) . '</span>';
                    echo '<span class="count">(' . intval($total) . ')</span>';
                    echo '</span>';
                } else {
                    echo '—';
                }
                break;
                
            case 'recipe_views':
                $views = get_post_meta($post_id, '_recipe_views', true);
                echo $views ? number_format(intval($views)) : '0';
                break;
                
            case 'recipe_favorites':
                $favorites = get_post_meta($post_id, '_recipe_favorites_count', true);
                echo $favorites ? number_format(intval($favorites)) : '0';
                break;
        }
    }
    
    /**
     * Make columns sortable
     */
    public function make_columns_sortable($columns) {
        $columns['recipe_rating'] = 'recipe_rating';
        $columns['recipe_views'] = 'recipe_views';
        $columns['recipe_favorites'] = 'recipe_favorites';
        return $columns;
    }
    
    /**
     * Sort recipes by custom columns
     */
    public function sort_recipes_by_column($query) {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }
        
        if ($query->get('post_type') !== 'recipe') {
            return;
        }
        
        $orderby = $query->get('orderby');
        
        switch ($orderby) {
            case 'recipe_rating':
                $query->set('meta_key', '_recipe_average_rating');
                $query->set('orderby', 'meta_value_num');
                break;
                
            case 'recipe_views':
                $query->set('meta_key', '_recipe_views');
                $query->set('orderby', 'meta_value_num');
                break;
                
            case 'recipe_favorites':
                $query->set('meta_key', '_recipe_favorites_count');
                $query->set('orderby', 'meta_value_num');
                break;
        }
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('recipe_settings_group', 'rcp_enable_ratings');
        register_setting('recipe_settings_group', 'rcp_enable_favorites');
        register_setting('recipe_settings_group', 'rcp_enable_print');
        register_setting('recipe_settings_group', 'rcp_enable_jump_to_recipe');
        register_setting('recipe_settings_group', 'rcp_default_servings');
        register_setting('recipe_settings_group', 'rcp_recipe_slug');
        register_setting('recipe_settings_group', 'rcp_primary_color');
        register_setting('recipe_settings_group', 'rcp_secondary_color');
    }
    
    /**
     * Render recipe list page
     */
    public function render_recipe_list_page() {
        include RCP_PLUGIN_DIR . 'admin/views/recipe-list.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        include RCP_PLUGIN_DIR . 'admin/views/recipe-settings.php';
    }
    
    /**
     * Render import/export page
     */
    public function render_import_export_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Import/Export Recipes', 'recipe-challenge-pro'); ?></h1>
            
            <div class="recipe-import-export-wrapper">
                <div class="import-section">
                    <h2><?php _e('Import Recipes', 'recipe-challenge-pro'); ?></h2>
                    <p><?php _e('Import recipes from a CSV or JSON file.', 'recipe-challenge-pro'); ?></p>
                    
                    <form method="post" enctype="multipart/form-data" action="">
                        <?php wp_nonce_field('recipe_import', 'recipe_import_nonce'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="import_file"><?php _e('Choose File', 'recipe-challenge-pro'); ?></label>
                                </th>
                                <td>
                                    <input type="file" name="import_file" id="import_file" accept=".csv,.json" />
                                    <p class="description"><?php _e('Supported formats: CSV, JSON', 'recipe-challenge-pro'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" name="import_recipes" class="button button-primary" value="<?php _e('Import Recipes', 'recipe-challenge-pro'); ?>" />
                        </p>
                    </form>
                </div>
                
                <hr />
                
                <div class="export-section">
                    <h2><?php _e('Export Recipes', 'recipe-challenge-pro'); ?></h2>
                    <p><?php _e('Export all recipes to a file.', 'recipe-challenge-pro'); ?></p>
                    
                    <form method="post" action="">
                        <?php wp_nonce_field('recipe_export', 'recipe_export_nonce'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="export_format"><?php _e('Export Format', 'recipe-challenge-pro'); ?></label>
                                </th>
                                <td>
                                    <select name="export_format" id="export_format">
                                        <option value="csv"><?php _e('CSV', 'recipe-challenge-pro'); ?></option>
                                        <option value="json"><?php _e('JSON', 'recipe-challenge-pro'); ?></option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" name="export_recipes" class="button button-primary" value="<?php _e('Export Recipes', 'recipe-challenge-pro'); ?>" />
                        </p>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get star rating HTML
     */
    private function get_star_rating_html($rating) {
        $html = '';
        $full_stars = floor($rating);
        $half_star = ($rating - $full_stars) >= 0.5;
        
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $full_stars) {
                $html .= '★';
            } elseif ($i == $full_stars + 1 && $half_star) {
                $html .= '☆';
            } else {
                $html .= '☆';
            }
        }
        
        return $html;
    }
    
    /**
     * Display admin notices
     */
    public function admin_notices() {
        if (isset($_GET['recipe_imported'])) {
            $count = intval($_GET['recipe_imported']);
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php printf(__('%d recipes imported successfully!', 'recipe-challenge-pro'), $count); ?></p>
            </div>
            <?php
        }
    }
}