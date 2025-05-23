<?php
/**
 * Recipe Helper Functions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if recipe is favorite
 */
function rcp_is_recipe_favorite($recipe_id) {
    $user_id = get_current_user_id();
    
    if ($user_id) {
        $favorites = get_user_meta($user_id, 'recipe_favorites', true);
    } else {
        $cookie_name = 'recipe_favorites';
        $favorites = isset($_COOKIE[$cookie_name]) ? json_decode(stripslashes($_COOKIE[$cookie_name]), true) : array();
    }
    
    if (!is_array($favorites)) {
        $favorites = array();
    }
    
    return in_array($recipe_id, $favorites);
}

/**
 * Get total recipe views
 */
function rcp_get_total_recipe_views() {
    global $wpdb;
    
    $total = $wpdb->get_var(
        "SELECT SUM(meta_value) 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_recipe_views'"
    );
    
    return intval($total);
}

/**
 * Get recipe views
 */
function rcp_get_recipe_views($recipe_id) {
    $views = get_post_meta($recipe_id, '_recipe_views', true);
    return intval($views);
}

/**
 * Track recipe view
 */
function rcp_track_recipe_view($recipe_id) {
    if (!is_single() || !is_main_query()) {
        return;
    }
    
    // Don't count views from logged in users viewing their own recipes
    if (is_user_logged_in() && get_current_user_id() == get_post_field('post_author', $recipe_id)) {
        return;
    }
    
    $views = intval(get_post_meta($recipe_id, '_recipe_views', true));
    update_post_meta($recipe_id, '_recipe_views', $views + 1);
}

/**
 * Get total favorites
 */
function rcp_get_total_favorites() {
    global $wpdb;
    
    $total = $wpdb->get_var(
        "SELECT SUM(meta_value) 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_recipe_favorites_count'"
    );
    
    return intval($total);
}

/**
 * Get recipe favorites count
 */
function rcp_get_recipe_favorites($recipe_id) {
    $favorites = get_post_meta($recipe_id, '_recipe_favorites_count', true);
    return intval($favorites);
}

/**
 * Format recipe time
 */
function rcp_format_time($minutes) {
    if ($minutes < 60) {
        return sprintf(__('%d mins', 'recipe-challenge-pro'), $minutes);
    }
    
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    
    if ($mins == 0) {
        return sprintf(_n('%d hour', '%d hours', $hours, 'recipe-challenge-pro'), $hours);
    }
    
    return sprintf(__('%d hr %d mins', 'recipe-challenge-pro'), $hours, $mins);
}

/**
 * Get recipe difficulty label
 */
function rcp_get_difficulty_label($difficulty) {
    $labels = array(
        'easy' => __('Easy', 'recipe-challenge-pro'),
        'medium' => __('Medium', 'recipe-challenge-pro'),
        'hard' => __('Hard', 'recipe-challenge-pro')
    );
    
    return isset($labels[$difficulty]) ? $labels[$difficulty] : '';
}

/**
 * Get recipe rating stars HTML
 */
function rcp_get_rating_stars($rating, $show_count = true) {
    $output = '<div class="recipe-rating-stars">';
    
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($rating)) {
            $output .= '<span class="star filled">★</span>';
        } elseif ($i == ceil($rating) && $rating != floor($rating)) {
            $output .= '<span class="star half">★</span>';
        } else {
            $output .= '<span class="star empty">☆</span>';
        }
    }
    
    if ($show_count) {
        $count = get_post_meta(get_the_ID(), '_recipe_total_ratings', true);
        if ($count) {
            $output .= '<span class="rating-count">(' . intval($count) . ')</span>';
        }
    }
    
    $output .= '</div>';
    
    return $output;
}

/**
 * Get related recipes
 */
function rcp_get_related_recipes($recipe_id, $count = 3) {
    $cuisines = wp_get_post_terms($recipe_id, 'recipe_cuisine', array('fields' => 'ids'));
    $courses = wp_get_post_terms($recipe_id, 'recipe_course', array('fields' => 'ids'));
    
    $args = array(
        'post_type' => 'recipe',
        'posts_per_page' => $count,
        'post__not_in' => array($recipe_id),
        'orderby' => 'rand'
    );
    
    $tax_query = array('relation' => 'OR');
    
    if (!empty($cuisines)) {
        $tax_query[] = array(
            'taxonomy' => 'recipe_cuisine',
            'field' => 'term_id',
            'terms' => $cuisines
        );
    }
    
    if (!empty($courses)) {
        $tax_query[] = array(
            'taxonomy' => 'recipe_course',
            'field' => 'term_id',
            'terms' => $courses
        );
    }
    
    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }
    
    return new WP_Query($args);
}

/**
 * Recipe search form
 */
function rcp_search_form($echo = true) {
    $form = '<form role="search" method="get" class="recipe-search-form" action="' . esc_url(home_url('/')) . '">
        <input type="search" class="recipe-search-field" placeholder="' . esc_attr__('Search recipes...', 'recipe-challenge-pro') . '" value="' . get_search_query() . '" name="s" />
        <input type="hidden" name="post_type" value="recipe" />
        <button type="submit" class="recipe-search-submit">' . esc_html__('Search', 'recipe-challenge-pro') . '</button>
    </form>';
    
    if ($echo) {
        echo $form;
    } else {
        return $form;
    }
}

/**
 * Get recipe archive URL
 */
function rcp_get_archive_url() {
    return get_post_type_archive_link('recipe');
}

/**
 * Get cuisine archive URL
 */
function rcp_get_cuisine_url($cuisine_slug) {
    $term = get_term_by('slug', $cuisine_slug, 'recipe_cuisine');
    if ($term) {
        return get_term_link($term);
    }
    return '';
}

/**
 * Get course archive URL
 */
function rcp_get_course_url($course_slug) {
    $term = get_term_by('slug', $course_slug, 'recipe_course');
    if ($term) {
        return get_term_link($term);
    }
    return '';
}

/**
 * Recipe breadcrumbs
 */
function rcp_breadcrumbs() {
    if (!is_singular('recipe') && !is_post_type_archive('recipe') && !is_tax('recipe_cuisine') && !is_tax('recipe_course')) {
        return;
    }
    
    $output = '<nav class="recipe-breadcrumbs">';
    $output .= '<a href="' . home_url() . '">' . __('Home', 'recipe-challenge-pro') . '</a>';
    $output .= '<span class="separator"> › </span>';
    $output .= '<a href="' . rcp_get_archive_url() . '">' . __('Recipes', 'recipe-challenge-pro') . '</a>';
    
    if (is_tax('recipe_cuisine') || is_tax('recipe_course')) {
        $term = get_queried_object();
        $output .= '<span class="separator"> › </span>';
        $output .= '<span class="current">' . esc_html($term->name) . '</span>';
    } elseif (is_singular('recipe')) {
        $cuisines = wp_get_post_terms(get_the_ID(), 'recipe_cuisine');
        if (!empty($cuisines)) {
            $output .= '<span class="separator"> › </span>';
            $output .= '<a href="' . get_term_link($cuisines[0]) . '">' . esc_html($cuisines[0]->name) . '</a>';
        }
        $output .= '<span class="separator"> › </span>';
        $output .= '<span class="current">' . get_the_title() . '</span>';
    }
    
    $output .= '</nav>';
    
    echo $output;
}

/**
 * Export recipes
 */
function rcp_export_recipes($format = 'json') {
    $args = array(
        'post_type' => 'recipe',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    );
    
    $recipes = get_posts($args);
    $export_data = array();
    
    foreach ($recipes as $recipe) {
        $recipe_data = array(
            'title' => $recipe->post_title,
            'content' => $recipe->post_content,
            'excerpt' => $recipe->post_excerpt,
            'author' => get_the_author_meta('display_name', $recipe->post_author),
            'date' => $recipe->post_date,
            'ingredients' => get_post_meta($recipe->ID, '_recipe_ingredients', true),
            'instructions' => get_post_meta($recipe->ID, '_recipe_instructions', true),
            'prep_time' => get_post_meta($recipe->ID, '_recipe_prep_time', true),
            'cook_time' => get_post_meta($recipe->ID, '_recipe_cook_time', true),
            'total_time' => get_post_meta($recipe->ID, '_recipe_total_time', true),
            'servings' => get_post_meta($recipe->ID, '_recipe_servings', true),
            'difficulty' => get_post_meta($recipe->ID, '_recipe_difficulty', true),
            'cuisines' => wp_get_post_terms($recipe->ID, 'recipe_cuisine', array('fields' => 'names')),
            'courses' => wp_get_post_terms($recipe->ID, 'recipe_course', array('fields' => 'names')),
            'featured_image' => get_the_post_thumbnail_url($recipe->ID, 'full')
        );
        
        $export_data[] = $recipe_data;
    }
    
    if ($format === 'json') {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="recipes-export-' . date('Y-m-d') . '.json"');
        echo json_encode($export_data, JSON_PRETTY_PRINT);
    } elseif ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="recipes-export-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, array(
            'Title', 'Content', 'Excerpt', 'Author', 'Date',
            'Prep Time', 'Cook Time', 'Total Time', 'Servings',
            'Difficulty', 'Cuisines', 'Courses', 'Featured Image'
        ));
        
        // CSV data
        foreach ($export_data as $recipe) {
            fputcsv($output, array(
                $recipe['title'],
                $recipe['content'],
                $recipe['excerpt'],
                $recipe['author'],
                $recipe['date'],
                $recipe['prep_time'],
                $recipe['cook_time'],
                $recipe['total_time'],
                $recipe['servings'],
                $recipe['difficulty'],
                implode(', ', $recipe['cuisines']),
                implode(', ', $recipe['courses']),
                $recipe['featured_image']
            ));
        }
        
        fclose($output);
    }
    
    exit;
}

/**
 * Import recipes
 */
function rcp_import_recipes($file) {
    $imported = 0;
    $file_content = file_get_contents($file);
    
    if (empty($file_content)) {
        return false;
    }
    
    $data = json_decode($file_content, true);
    
    if (!is_array($data)) {
        return false;
    }
    
    foreach ($data as $recipe_data) {
        $post_data = array(
            'post_title' => sanitize_text_field($recipe_data['title']),
            'post_content' => wp_kses_post($recipe_data['content']),
            'post_excerpt' => sanitize_textarea_field($recipe_data['excerpt']),
            'post_type' => 'recipe',
            'post_status' => 'publish'
        );
        
        $post_id = wp_insert_post($post_data);
        
        if ($post_id && !is_wp_error($post_id)) {
            // Update meta data
            update_post_meta($post_id, '_recipe_ingredients', $recipe_data['ingredients']);
            update_post_meta($post_id, '_recipe_instructions', $recipe_data['instructions']);
            update_post_meta($post_id, '_recipe_prep_time', $recipe_data['prep_time']);
            update_post_meta($post_id, '_recipe_cook_time', $recipe_data['cook_time']);
            update_post_meta($post_id, '_recipe_total_time', $recipe_data['total_time']);
            update_post_meta($post_id, '_recipe_servings', $recipe_data['servings']);
            update_post_meta($post_id, '_recipe_difficulty', $recipe_data['difficulty']);
            
            // Set taxonomies
            if (!empty($recipe_data['cuisines'])) {
                wp_set_object_terms($post_id, $recipe_data['cuisines'], 'recipe_cuisine');
            }
            
            if (!empty($recipe_data['courses'])) {
                wp_set_object_terms($post_id, $recipe_data['courses'], 'recipe_course');
            }
            
            // Set featured image if URL provided
            if (!empty($recipe_data['featured_image'])) {
                rcp_set_featured_image_from_url($post_id, $recipe_data['featured_image']);
            }
            
            $imported++;
        }
    }
    
    return $imported;
}

/**
 * Set featured image from URL
 */
function rcp_set_featured_image_from_url($post_id, $image_url) {
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);
    $filename = basename($image_url);
    
    if (wp_mkdir_p($upload_dir['path'])) {
        $file = $upload_dir['path'] . '/' . $filename;
    } else {
        $file = $upload_dir['basedir'] . '/' . $filename;
    }
    
    file_put_contents($file, $image_data);
    
    $wp_filetype = wp_check_filetype($filename, null);
    
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    
    $attach_id = wp_insert_attachment($attachment, $file, $post_id);
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $file);
    wp_update_attachment_metadata($attach_id, $attach_data);
    set_post_thumbnail($post_id, $attach_id);
}

/**
 * Recipe share buttons
 */
function rcp_share_buttons($recipe_id = null) {
    if (!$recipe_id) {
        $recipe_id = get_the_ID();
    }
    
    $url = get_permalink($recipe_id);
    $title = get_the_title($recipe_id);
    $image = get_the_post_thumbnail_url($recipe_id, 'full');
    
    ?>
    <div class="recipe-share-buttons">
        <span class="share-label"><?php _e('Share:', 'recipe-challenge-pro'); ?></span>
        
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($url); ?>" 
           target="_blank" 
           class="share-button share-facebook"
           title="<?php _e('Share on Facebook', 'recipe-challenge-pro'); ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
        </a>
        
        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($url); ?>&text=<?php echo urlencode($title); ?>" 
           target="_blank" 
           class="share-button share-twitter"
           title="<?php _e('Share on Twitter', 'recipe-challenge-pro'); ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
            </svg>
        </a>
        
        <a href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode($url); ?>&media=<?php echo urlencode($image); ?>&description=<?php echo urlencode($title); ?>" 
           target="_blank" 
           class="share-button share-pinterest"
           title="<?php _e('Share on Pinterest', 'recipe-challenge-pro'); ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 01.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/>
            </svg>
        </a>
        
        <a href="mailto:?subject=<?php echo urlencode($title); ?>&body=<?php echo urlencode($url); ?>" 
           class="share-button share-email"
           title="<?php _e('Share via Email', 'recipe-challenge-pro'); ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
        </a>
    </div>
    <?php
}

/**
 * Recipe print URL
 */
function rcp_get_print_url($recipe_id = null) {
    if (!$recipe_id) {
        $recipe_id = get_the_ID();
    }
    
    return add_query_arg('print-recipe', '1', get_permalink($recipe_id));
}

/**
 * Recipe nutrition label
 */
function rcp_nutrition_label($recipe_id = null) {
    if (!$recipe_id) {
        $recipe_id = get_the_ID();
    }
    
    $nutrition = get_post_meta($recipe_id, '_recipe_nutrition', true);
    
    if (!is_array($nutrition) || empty($nutrition)) {
        return;
    }
    
    ?>
    <div class="recipe-nutrition-label">
        <h3 class="nutrition-title"><?php _e('Nutrition Facts', 'recipe-challenge-pro'); ?></h3>
        <div class="nutrition-serving">
            <?php 
            $servings = get_post_meta($recipe_id, '_recipe_servings', true);
            echo sprintf(__('Per Serving (1 of %s)', 'recipe-challenge-pro'), $servings);
            ?>
        </div>
        <div class="nutrition-facts">
            <?php if (!empty($nutrition['calories'])) : ?>
                <div class="nutrition-item calories">
                    <span class="label"><?php _e('Calories', 'recipe-challenge-pro'); ?></span>
                    <span class="value"><?php echo esc_html($nutrition['calories']); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($nutrition['fat'])) : ?>
                <div class="nutrition-item">
                    <span class="label"><?php _e('Total Fat', 'recipe-challenge-pro'); ?></span>
                    <span class="value"><?php echo esc_html($nutrition['fat']); ?>g</span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($nutrition['saturated_fat'])) : ?>
                <div class="nutrition-item sub-item">
                    <span class="label"><?php _e('Saturated Fat', 'recipe-challenge-pro'); ?></span>
                    <span class="value"><?php echo esc_html($nutrition['saturated_fat']); ?>g</span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($nutrition['cholesterol'])) : ?>
                <div class="nutrition-item">
                    <span class="label"><?php _e('Cholesterol', 'recipe-challenge-pro'); ?></span>
                    <span class="value"><?php echo esc_html($nutrition['cholesterol']); ?>mg</span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($nutrition['sodium'])) : ?>
                <div class="nutrition-item">
                    <span class="label"><?php _e('Sodium', 'recipe-challenge-pro'); ?></span>
                    <span class="value"><?php echo esc_html($nutrition['sodium']); ?>mg</span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($nutrition['carbohydrates'])) : ?>
                <div class="nutrition-item">
                    <span class="label"><?php _e('Total Carbohydrates', 'recipe-challenge-pro'); ?></span>
                    <span class="value"><?php echo esc_html($nutrition['carbohydrates']); ?>g</span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($nutrition['fiber'])) : ?>
                <div class="nutrition-item sub-item">
                    <span class="label"><?php _e('Dietary Fiber', 'recipe-challenge-pro'); ?></span>
                    <span class="value"><?php echo esc_html($nutrition['fiber']); ?>g</span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($nutrition['sugar'])) : ?>
                <div class="nutrition-item sub-item">
                    <span class="label"><?php _e('Sugars', 'recipe-challenge-pro'); ?></span>
                    <span class="value"><?php echo esc_html($nutrition['sugar']); ?>g</span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($nutrition['protein'])) : ?>
                <div class="nutrition-item">
                    <span class="label"><?php _e('Protein', 'recipe-challenge-pro'); ?></span>
                    <span class="value"><?php echo esc_html($nutrition['protein']); ?>g</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}