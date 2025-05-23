
<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get all recipes
$args = array(
    'post_type' => 'recipe',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC'
);

$recipes = new WP_Query($args);
?>

<div class="wrap">
    <h1><?php _e('All Recipes', 'recipe-challenge-pro'); ?></h1>
    
    <div class="recipe-admin-stats">
        <div class="stat-box">
            <h3><?php _e('Total Recipes', 'recipe-challenge-pro'); ?></h3>
            <p class="stat-number"><?php echo $recipes->found_posts; ?></p>
        </div>
        
        <div class="stat-box">
            <h3><?php _e('Total Views', 'recipe-challenge-pro'); ?></h3>
            <p class="stat-number"><?php echo rcp_get_total_recipe_views(); ?></p>
        </div>
        
        <div class="stat-box">
            <h3><?php _e('Total Favorites', 'recipe-challenge-pro'); ?></h3>
            <p class="stat-number"><?php echo rcp_get_total_favorites(); ?></p>
        </div>
    </div>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Recipe Title', 'recipe-challenge-pro'); ?></th>
                <th><?php _e('Author', 'recipe-challenge-pro'); ?></th>
                <th><?php _e('Cuisine', 'recipe-challenge-pro'); ?></th>
                <th><?php _e('Course', 'recipe-challenge-pro'); ?></th>
                <th><?php _e('Views', 'recipe-challenge-pro'); ?></th>
                <th><?php _e('Favorites', 'recipe-challenge-pro'); ?></th>
                <th><?php _e('Date', 'recipe-challenge-pro'); ?></th>
                <th><?php _e('Actions', 'recipe-challenge-pro'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($recipes->have_posts()) : ?>
                <?php while ($recipes->have_posts()) : $recipes->the_post(); ?>
                    <tr>
                        <td>
                            <strong><a href="<?php echo get_edit_post_link(); ?>"><?php the_title(); ?></a></strong>
                        </td>
                        <td><?php the_author(); ?></td>
                        <td><?php echo get_the_term_list(get_the_ID(), 'recipe_cuisine', '', ', '); ?></td>
                        <td><?php echo get_the_term_list(get_the_ID(), 'recipe_course', '', ', '); ?></td>
                        <td><?php echo rcp_get_recipe_views(get_the_ID()); ?></td>
                        <td><?php echo rcp_get_recipe_favorites(get_the_ID()); ?></td>
                        <td><?php echo get_the_date(); ?></td>
                        <td>
                            <a href="<?php the_permalink(); ?>" class="button button-small" target="_blank"><?php _e('View', 'recipe-challenge-pro'); ?></a>
                            <a href="<?php echo get_edit_post_link(); ?>" class="button button-small"><?php _e('Edit', 'recipe-challenge-pro'); ?></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="8"><?php _e('No recipes found.', 'recipe-challenge-pro'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php wp_reset_postdata(); ?>
</div>