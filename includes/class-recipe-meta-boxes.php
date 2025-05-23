<?php
class Recipe_Meta_Boxes {
    
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_recipe_meta_boxes'));
        add_action('save_post_recipe', array($this, 'save_recipe_meta'), 10, 2);
    }
    
    public function add_recipe_meta_boxes() {
        add_meta_box(
            'recipe_details',
            __('Recipe Details', 'recipe-challenge-pro'),
            array($this, 'render_recipe_details_meta_box'),
            'recipe',
            'normal',
            'high'
        );
        
        add_meta_box(
            'recipe_ingredients',
            __('Recipe Ingredients', 'recipe-challenge-pro'),
            array($this, 'render_ingredients_meta_box'),
            'recipe',
            'normal',
            'high'
        );
        
        add_meta_box(
            'recipe_instructions',
            __('Recipe Instructions', 'recipe-challenge-pro'),
            array($this, 'render_instructions_meta_box'),
            'recipe',
            'normal',
            'high'
        );
    }
    
    public function render_recipe_details_meta_box($post) {
        wp_nonce_field('recipe_meta_box', 'recipe_meta_box_nonce');
        
        $prep_time = get_post_meta($post->ID, '_recipe_prep_time', true);
        $cook_time = get_post_meta($post->ID, '_recipe_cook_time', true);
        $total_time = get_post_meta($post->ID, '_recipe_total_time', true);
        $servings = get_post_meta($post->ID, '_recipe_servings', true);
        $difficulty = get_post_meta($post->ID, '_recipe_difficulty', true);
        ?>
        <div class="recipe-meta-box">
            <p>
                <label for="recipe_prep_time"><?php _e('Prep Time (minutes)', 'recipe-challenge-pro'); ?></label>
                <input type="number" id="recipe_prep_time" name="recipe_prep_time" value="<?php echo esc_attr($prep_time); ?>" min="0" />
            </p>
            <p>
                <label for="recipe_cook_time"><?php _e('Cook Time (minutes)', 'recipe-challenge-pro'); ?></label>
                <input type="number" id="recipe_cook_time" name="recipe_cook_time" value="<?php echo esc_attr($cook_time); ?>" min="0" />
            </p>
            <p>
                <label for="recipe_total_time"><?php _e('Total Time (minutes)', 'recipe-challenge-pro'); ?></label>
                <input type="number" id="recipe_total_time" name="recipe_total_time" value="<?php echo esc_attr($total_time); ?>" min="0" />
            </p>
            <p>
                <label for="recipe_servings"><?php _e('Servings', 'recipe-challenge-pro'); ?></label>
                <input type="number" id="recipe_servings" name="recipe_servings" value="<?php echo esc_attr($servings); ?>" min="1" />
            </p>
            <p>
                <label for="recipe_difficulty"><?php _e('Difficulty', 'recipe-challenge-pro'); ?></label>
                <select id="recipe_difficulty" name="recipe_difficulty">
                    <option value="easy" <?php selected($difficulty, 'easy'); ?>><?php _e('Easy', 'recipe-challenge-pro'); ?></option>
                    <option value="medium" <?php selected($difficulty, 'medium'); ?>><?php _e('Medium', 'recipe-challenge-pro'); ?></option>
                    <option value="hard" <?php selected($difficulty, 'hard'); ?>><?php _e('Hard', 'recipe-challenge-pro'); ?></option>
                </select>
            </p>
        </div>
        <?php
    }
    
    public function render_ingredients_meta_box($post) {
        $ingredients = get_post_meta($post->ID, '_recipe_ingredients', true);
        if (!is_array($ingredients)) {
            $ingredients = array();
        }
        ?>
        <div class="recipe-ingredients-wrapper">
            <div id="recipe-ingredients-list">
                <?php if (!empty($ingredients)) : ?>
                    <?php foreach ($ingredients as $index => $ingredient) : ?>
                        <div class="ingredient-item">
                            <input type="text" name="recipe_ingredients[<?php echo $index; ?>][amount]" 
                                   placeholder="<?php _e('Amount', 'recipe-challenge-pro'); ?>" 
                                   value="<?php echo isset($ingredient['amount']) ? esc_attr($ingredient['amount']) : ''; ?>" />
                            <input type="text" name="recipe_ingredients[<?php echo $index; ?>][unit]" 
                                   placeholder="<?php _e('Unit', 'recipe-challenge-pro'); ?>" 
                                   value="<?php echo isset($ingredient['unit']) ? esc_attr($ingredient['unit']) : ''; ?>" />
                            <input type="text" name="recipe_ingredients[<?php echo $index; ?>][ingredient]" 
                                   placeholder="<?php _e('Ingredient', 'recipe-challenge-pro'); ?>" 
                                   value="<?php echo isset($ingredient['ingredient']) ? esc_attr($ingredient['ingredient']) : ''; ?>" />
                            <input type="text" name="recipe_ingredients[<?php echo $index; ?>][notes]" 
                                   placeholder="<?php _e('Notes (optional)', 'recipe-challenge-pro'); ?>" 
                                   value="<?php echo isset($ingredient['notes']) ? esc_attr($ingredient['notes']) : ''; ?>" />
                            <button type="button" class="remove-ingredient button"><?php _e('Remove', 'recipe-challenge-pro'); ?></button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button type="button" id="add-ingredient" class="button button-primary"><?php _e('Add Ingredient', 'recipe-challenge-pro'); ?></button>
            
            <script type="text/template" id="ingredient-template">
                <div class="ingredient-item">
                    <input type="text" name="recipe_ingredients[{{index}}][amount]" placeholder="<?php _e('Amount', 'recipe-challenge-pro'); ?>" />
                    <input type="text" name="recipe_ingredients[{{index}}][unit]" placeholder="<?php _e('Unit', 'recipe-challenge-pro'); ?>" />
                    <input type="text" name="recipe_ingredients[{{index}}][ingredient]" placeholder="<?php _e('Ingredient', 'recipe-challenge-pro'); ?>" />
                    <input type="text" name="recipe_ingredients[{{index}}][notes]" placeholder="<?php _e('Notes (optional)', 'recipe-challenge-pro'); ?>" />
                    <button type="button" class="remove-ingredient button"><?php _e('Remove', 'recipe-challenge-pro'); ?></button>
                </div>
            </script>
        </div>
        <?php
    }
    
    public function render_instructions_meta_box($post) {
        $instructions = get_post_meta($post->ID, '_recipe_instructions', true);
        if (!is_array($instructions)) {
            $instructions = array();
        }
        ?>
        <div class="recipe-instructions-wrapper">
            <div id="recipe-instructions-list">
                <?php if (!empty($instructions)) : ?>
                    <?php foreach ($instructions as $index => $instruction) : ?>
                        <div class="instruction-item">
                            <span class="step-number"><?php echo $index + 1; ?>.</span>
                            <textarea name="recipe_instructions[]" rows="3"><?php echo esc_textarea($instruction); ?></textarea>
                            <button type="button" class="remove-instruction button"><?php _e('Remove', 'recipe-challenge-pro'); ?></button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button type="button" id="add-instruction" class="button button-primary"><?php _e('Add Step', 'recipe-challenge-pro'); ?></button>
            
            <script type="text/template" id="instruction-template">
                <div class="instruction-item">
                    <span class="step-number">{{number}}.</span>
                    <textarea name="recipe_instructions[]" rows="3"></textarea>
                    <button type="button" class="remove-instruction button"><?php _e('Remove', 'recipe-challenge-pro'); ?></button>
                </div>
            </script>
        </div>
        <?php
    }
    
    public function save_recipe_meta($post_id, $post) {
        if (!isset($_POST['recipe_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['recipe_meta_box_nonce'], 'recipe_meta_box')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save recipe details
        if (isset($_POST['recipe_prep_time'])) {
            update_post_meta($post_id, '_recipe_prep_time', sanitize_text_field($_POST['recipe_prep_time']));
        }
        
        if (isset($_POST['recipe_cook_time'])) {
            update_post_meta($post_id, '_recipe_cook_time', sanitize_text_field($_POST['recipe_cook_time']));
        }
        
        if (isset($_POST['recipe_total_time'])) {
            update_post_meta($post_id, '_recipe_total_time', sanitize_text_field($_POST['recipe_total_time']));
        }
        
        if (isset($_POST['recipe_servings'])) {
            update_post_meta($post_id, '_recipe_servings', sanitize_text_field($_POST['recipe_servings']));
        }
        
        if (isset($_POST['recipe_difficulty'])) {
            update_post_meta($post_id, '_recipe_difficulty', sanitize_text_field($_POST['recipe_difficulty']));
        }
        
        // Save ingredients
        if (isset($_POST['recipe_ingredients']) && is_array($_POST['recipe_ingredients'])) {
            $ingredients = array();
            foreach ($_POST['recipe_ingredients'] as $ingredient) {
                if (!empty($ingredient['ingredient'])) {
                    $ingredients[] = array(
                        'amount' => sanitize_text_field($ingredient['amount']),
                        'unit' => sanitize_text_field($ingredient['unit']),
                        'ingredient' => sanitize_text_field($ingredient['ingredient']),
                        'notes' => sanitize_text_field($ingredient['notes'])
                    );
                }
            }
            update_post_meta($post_id, '_recipe_ingredients', $ingredients);
        } else {
            delete_post_meta($post_id, '_recipe_ingredients');
        }
        
        // Save instructions
        if (isset($_POST['recipe_instructions']) && is_array($_POST['recipe_instructions'])) {
            $instructions = array();
            foreach ($_POST['recipe_instructions'] as $instruction) {
                if (!empty($instruction)) {
                    $instructions[] = sanitize_textarea_field($instruction);
                }
            }
            update_post_meta($post_id, '_recipe_instructions', $instructions);
        } else {
            delete_post_meta($post_id, '_recipe_instructions');
        }
    }
}