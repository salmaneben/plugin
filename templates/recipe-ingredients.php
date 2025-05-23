
<?php
/**
 * Recipe Ingredients Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$ingredients = get_post_meta(get_the_ID(), '_recipe_ingredients', true);
if (!is_array($ingredients) || empty($ingredients)) {
    return;
}
?>

<div class="recipe-ingredients">
    <h2><?php _e('Ingredients', 'recipe-challenge-pro'); ?></h2>
    
    <div class="recipe-scale">
        <label><?php _e('Scale:', 'recipe-challenge-pro'); ?></label>
        <div class="scale-buttons">
            <button class="scale-btn" data-scale="0.5">1/2x</button>
            <button class="scale-btn active" data-scale="1">1x</button>
            <button class="scale-btn" data-scale="2">2x</button>
            <button class="scale-btn" data-scale="3">3x</button>
        </div>
    </div>
    
    <ul class="ingredients-list">
        <?php foreach ($ingredients as $index => $ingredient) : ?>
            <li class="ingredient-item">
                <input type="checkbox" 
                       class="ingredient-checkbox" 
                       id="ingredient-<?php echo $index; ?>"
                       data-index="<?php echo $index; ?>" />
                
                <label for="ingredient-<?php echo $index; ?>">
                    <?php if (!empty($ingredient['amount'])) : ?>
                        <span class="ingredient-amount"><?php echo esc_html($ingredient['amount']); ?></span>
                    <?php endif; ?>
                    
                    <?php if (!empty($ingredient['unit'])) : ?>
                        <span class="ingredient-unit"><?php echo esc_html($ingredient['unit']); ?></span>
                    <?php endif; ?>
                    
                    <span class="ingredient-name"><?php echo esc_html($ingredient['ingredient']); ?></span>
                    
                    <?php if (!empty($ingredient['notes'])) : ?>
                        <span class="ingredient-notes">(<?php echo esc_html($ingredient['notes']); ?>)</span>
                    <?php endif; ?>
                </label>
            </li>
        <?php endforeach; ?>
    </ul>
</div>