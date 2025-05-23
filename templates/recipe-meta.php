
<?php
/**
 * Recipe Meta Information Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$prep_time = get_post_meta(get_the_ID(), '_recipe_prep_time', true);
$cook_time = get_post_meta(get_the_ID(), '_recipe_cook_time', true);
$total_time = get_post_meta(get_the_ID(), '_recipe_total_time', true);
$servings = get_post_meta(get_the_ID(), '_recipe_servings', true);
$difficulty = get_post_meta(get_the_ID(), '_recipe_difficulty', true);

$cuisines = wp_get_post_terms(get_the_ID(), 'recipe_cuisine', array('fields' => 'names'));
$courses = wp_get_post_terms(get_the_ID(), 'recipe_course', array('fields' => 'names'));
?>

<div class="recipe-meta">
    <?php if ($prep_time) : ?>
        <div class="recipe-meta-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span><?php _e('Prep:', 'recipe-challenge-pro'); ?> <?php echo esc_html($prep_time); ?> <?php _e('mins', 'recipe-challenge-pro'); ?></span>
        </div>
    <?php endif; ?>
    
    <?php if ($cook_time) : ?>
        <div class="recipe-meta-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2v10l4 2"></path>
                <circle cx="12" cy="12" r="10"></circle>
            </svg>
            <span><?php _e('Cook:', 'recipe-challenge-pro'); ?> <?php echo esc_html($cook_time); ?> <?php _e('mins', 'recipe-challenge-pro'); ?></span>
        </div>
    <?php endif; ?>
    
    <?php if ($servings) : ?>
        <div class="recipe-meta-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 22v-11c0-1.1-.9-2-2-2H10c-1.1 0-2 .9-2 2v11"></path>
                <rect x="6" y="2" width="12" height="8" rx="1"></rect>
            </svg>
            <span><?php _e('Servings:', 'recipe-challenge-pro'); ?> <span class="servings-display"><?php echo esc_html($servings); ?></span></span>
        </div>
    <?php endif; ?>
    
    <?php if ($difficulty) : ?>
        <div class="recipe-meta-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M8 12h8"></path>
            </svg>
            <span><?php _e('Difficulty:', 'recipe-challenge-pro'); ?> <?php echo ucfirst(esc_html($difficulty)); ?></span>
        </div>
    <?php endif; ?>
</div>

<div class="recipe-taxonomy-meta">
    <?php if (!empty($cuisines)) : ?>
        <div class="recipe-cuisines">
            <strong><?php _e('Cuisine:', 'recipe-challenge-pro'); ?></strong>
            <?php echo implode(', ', array_map('esc_html', $cuisines)); ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($courses)) : ?>
        <div class="recipe-courses">
            <strong><?php _e('Course:', 'recipe-challenge-pro'); ?></strong>
            <?php echo implode(', ', array_map('esc_html', $courses)); ?>
        </div>
    <?php endif; ?>
</div>