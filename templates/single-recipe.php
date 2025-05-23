<?php
/**
 * Single Recipe Template
 */

get_header();

while (have_posts()) : the_post();
    $recipe_id = get_the_ID();
    $author_id = get_the_author_meta('ID');
    $author_name = get_the_author();
    
    // Get recipe meta
    $prep_time = get_post_meta($recipe_id, '_recipe_prep_time', true);
    $cook_time = get_post_meta($recipe_id, '_recipe_cook_time', true);
    $total_time = get_post_meta($recipe_id, '_recipe_total_time', true);
    $servings = get_post_meta($recipe_id, '_recipe_servings', true);
    $difficulty = get_post_meta($recipe_id, '_recipe_difficulty', true);
    
    // Get taxonomies
    $cuisines = wp_get_post_terms($recipe_id, 'recipe_cuisine');
    $courses = wp_get_post_terms($recipe_id, 'recipe_course');
    
    // Check if user has favorited
    $is_favorite = rcp_is_recipe_favorite($recipe_id);
    
    // Settings
    $enable_jump = get_option('rcp_enable_jump_to_recipe', 1);
    $enable_print = get_option('rcp_enable_print', 1);
    $enable_favorites = get_option('rcp_enable_favorites', 1);
?>

<div class="recipe-wrapper">
    <?php if ($enable_jump && has_shortcode(get_the_content(), 'recipe_card') === false) : ?>
        <a href="#recipe-card" class="jump-to-recipe"><?php _e('Jump to Recipe', 'recipe-challenge-pro'); ?></a>
    <?php endif; ?>
    
    <article id="recipe-<?php the_ID(); ?>" <?php post_class('recipe-single'); ?>>
        <header class="recipe-header">
            <h1 class="recipe-title"><?php the_title(); ?></h1>
            
            <div class="recipe-meta">
                <div class="recipe-meta-item">
                    <span class="meta-label"><?php _e('Author:', 'recipe-challenge-pro'); ?></span>
                    <span class="meta-value"><?php echo esc_html($author_name); ?></span>
                </div>
                
                <?php if (!empty($cuisines)) : ?>
                    <div class="recipe-meta-item">
                        <span class="meta-label"><?php _e('Cuisine:', 'recipe-challenge-pro'); ?></span>
                        <span class="meta-value">
                            <?php 
                            $cuisine_names = wp_list_pluck($cuisines, 'name');
                            echo esc_html(implode(', ', $cuisine_names)); 
                            ?>
                        </span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($courses)) : ?>
                    <div class="recipe-meta-item">
                        <span class="meta-label"><?php _e('Courses:', 'recipe-challenge-pro'); ?></span>
                        <span class="meta-value">
                            <?php 
                            $course_names = wp_list_pluck($courses, 'name');
                            echo esc_html(implode(', ', $course_names)); 
                            ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (has_post_thumbnail()) : ?>
                <div class="recipe-image">
                    <?php the_post_thumbnail('large', array('class' => 'recipe-featured-image')); ?>
                </div>
            <?php endif; ?>
        </header>
        
        <div class="recipe-content">
            <?php the_content(); ?>
        </div>
        
        <div id="recipe-card" class="recipe-card" data-recipe-id="<?php echo $recipe_id; ?>" data-servings="<?php echo esc_attr($servings); ?>">
            <div class="recipe-actions">
                <?php if ($enable_print) : ?>
                    <button class="recipe-action-btn print-recipe">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect>
                        </svg>
                        <span class="button-text"><?php _e('Print Recipe', 'recipe-challenge-pro'); ?></span>
                    </button>
                <?php endif; ?>
                
                <?php if ($enable_favorites) : ?>
                    <button class="recipe-action-btn add-favorite <?php echo $is_favorite ? 'is-favorite' : ''; ?>" 
                            data-recipe-id="<?php echo $recipe_id; ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="<?php echo $is_favorite ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        <span class="button-text">
                            <?php echo $is_favorite ? __('Remove from Favorites', 'recipe-challenge-pro') : __('Add to Favorites', 'recipe-challenge-pro'); ?>
                        </span>
                    </button>
                <?php endif; ?>
            </div>
            
            <div class="recipe-info-box">
                <?php if ($prep_time) : ?>
                    <div class="info-item">
                        <span class="info-label"><?php _e('Prep Time', 'recipe-challenge-pro'); ?></span>
                        <span class="info-value"><?php echo esc_html($prep_time); ?> <?php _e('mins', 'recipe-challenge-pro'); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($cook_time) : ?>
                    <div class="info-item">
                        <span class="info-label"><?php _e('Cook Time', 'recipe-challenge-pro'); ?></span>
                        <span class="info-value"><?php echo esc_html($cook_time); ?> <?php _e('mins', 'recipe-challenge-pro'); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($total_time) : ?>
                    <div class="info-item">
                        <span class="info-label"><?php _e('Total Time', 'recipe-challenge-pro'); ?></span>
                        <span class="info-value"><?php echo esc_html($total_time); ?> <?php _e('mins', 'recipe-challenge-pro'); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($servings) : ?>
                    <div class="info-item">
                        <span class="info-label"><?php _e('Servings', 'recipe-challenge-pro'); ?></span>
                        <span class="info-value servings-display"><?php echo esc_html($servings); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($difficulty) : ?>
                    <div class="info-item">
                        <span class="info-label"><?php _e('Difficulty', 'recipe-challenge-pro'); ?></span>
                        <span class="info-value"><?php echo ucfirst(esc_html($difficulty)); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php
            // Include template parts
            include RCP_PLUGIN_DIR . 'templates/recipe-ingredients.php';
            include RCP_PLUGIN_DIR . 'templates/recipe-instructions.php';
            ?>
            
            <?php if (comments_open() || get_comments_number()) : ?>
                <div class="recipe-comments">
                    <?php comments_template(); ?>
                </div>
            <?php endif; ?>
        </div>
    </article>
</div>

<?php endwhile; ?>

<?php get_footer(); ?>