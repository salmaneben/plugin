<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Save settings
if (isset($_POST['submit'])) {
    if (wp_verify_nonce($_POST['recipe_settings_nonce'], 'recipe_settings')) {
        update_option('rcp_enable_ratings', isset($_POST['enable_ratings']) ? 1 : 0);
        update_option('rcp_enable_favorites', isset($_POST['enable_favorites']) ? 1 : 0);
        update_option('rcp_enable_print', isset($_POST['enable_print']) ? 1 : 0);
        update_option('rcp_enable_jump_to_recipe', isset($_POST['enable_jump_to_recipe']) ? 1 : 0);
        update_option('rcp_default_servings', sanitize_text_field($_POST['default_servings']));
        update_option('rcp_recipe_slug', sanitize_text_field($_POST['recipe_slug']));
        update_option('rcp_primary_color', sanitize_hex_color($_POST['primary_color']));
        update_option('rcp_secondary_color', sanitize_hex_color($_POST['secondary_color']));
        
        echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'recipe-challenge-pro') . '</p></div>';
    }
}

// Get current settings
$enable_ratings = get_option('rcp_enable_ratings', 1);
$enable_favorites = get_option('rcp_enable_favorites', 1);
$enable_print = get_option('rcp_enable_print', 1);
$enable_jump_to_recipe = get_option('rcp_enable_jump_to_recipe', 1);
$default_servings = get_option('rcp_default_servings', 4);
$recipe_slug = get_option('rcp_recipe_slug', 'recipe');
$primary_color = get_option('rcp_primary_color', '#f39c12');
$secondary_color = get_option('rcp_secondary_color', '#e74c3c');
?>

<div class="wrap">
    <h1><?php _e('Recipe Settings', 'recipe-challenge-pro'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('recipe_settings', 'recipe_settings_nonce'); ?>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php _e('General Settings', 'recipe-challenge-pro'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="enable_ratings" value="1" <?php checked($enable_ratings, 1); ?> />
                                <?php _e('Enable recipe ratings', 'recipe-challenge-pro'); ?>
                            </label>
                            <br />
                            <label>
                                <input type="checkbox" name="enable_favorites" value="1" <?php checked($enable_favorites, 1); ?> />
                                <?php _e('Enable recipe favorites', 'recipe-challenge-pro'); ?>
                            </label>
                            <br />
                            <label>
                                <input type="checkbox" name="enable_print" value="1" <?php checked($enable_print, 1); ?> />
                                <?php _e('Enable print recipe button', 'recipe-challenge-pro'); ?>
                            </label>
                            <br />
                            <label>
                                <input type="checkbox" name="enable_jump_to_recipe" value="1" <?php checked($enable_jump_to_recipe, 1); ?> />
                                <?php _e('Enable "Jump to Recipe" button', 'recipe-challenge-pro'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="default_servings"><?php _e('Default Servings', 'recipe-challenge-pro'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="default_servings" name="default_servings" value="<?php echo esc_attr($default_servings); ?>" min="1" max="20" />
                        <p class="description"><?php _e('Default number of servings for new recipes.', 'recipe-challenge-pro'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="recipe_slug"><?php _e('Recipe URL Slug', 'recipe-challenge-pro'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="recipe_slug" name="recipe_slug" value="<?php echo esc_attr($recipe_slug); ?>" />
                        <p class="description"><?php _e('The URL slug for recipe pages. Default is "recipe".', 'recipe-challenge-pro'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <h2><?php _e('Display Settings', 'recipe-challenge-pro'); ?></h2>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php _e('Color Scheme', 'recipe-challenge-pro'); ?></th>
                    <td>
                        <label>
                            <?php _e('Primary Color', 'recipe-challenge-pro'); ?><br>
                            <input type="color" name="primary_color" value="<?php echo esc_attr($primary_color); ?>" />
                        </label>
                        <br /><br />
                        <label>
                            <?php _e('Secondary Color', 'recipe-challenge-pro'); ?><br>
                            <input type="color" name="secondary_color" value="<?php echo esc_attr($secondary_color); ?>" />
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Settings', 'recipe-challenge-pro'); ?>" />
        </p>
    </form>
    
    <?php do_action('rcp_settings_page_bottom'); ?>
</div>