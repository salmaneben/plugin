
<?php
/**
 * Recipe Instructions Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$instructions = get_post_meta(get_the_ID(), '_recipe_instructions', true);
if (!is_array($instructions) || empty($instructions)) {
    return;
}
?>

<div class="recipe-instructions">
    <h2><?php _e('Instructions', 'recipe-challenge-pro'); ?></h2>
    
    <ol class="instructions-list">
        <?php foreach ($instructions as $index => $instruction) : ?>
            <li class="instruction-item">
                <span class="instruction-number"><?php echo $index + 1; ?></span>
                <div class="instruction-text">
                    <?php echo wpautop(esc_html($instruction)); ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>
</div>