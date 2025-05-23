<?php
/**
 * Recipe AJAX Handler Class
 */

class Recipe_Ajax {
    
    public function __construct() {
        // Public AJAX actions
        add_action('wp_ajax_toggle_recipe_favorite', array($this, 'toggle_favorite'));
        add_action('wp_ajax_nopriv_toggle_recipe_favorite', array($this, 'toggle_favorite'));
        
        add_action('wp_ajax_rate_recipe', array($this, 'rate_recipe'));
        add_action('wp_ajax_nopriv_rate_recipe', array($this, 'rate_recipe'));
        
        add_action('wp_ajax_track_recipe_view', array($this, 'track_view'));
        add_action('wp_ajax_nopriv_track_recipe_view', array($this, 'track_view'));
        
        add_action('wp_ajax_load_more_recipes', array($this, 'load_more_recipes'));
        add_action('wp_ajax_nopriv_load_more_recipes', array($this, 'load_more_recipes'));
        
        add_action('wp_ajax_recipe_quick_search', array($this, 'quick_search'));
        add_action('wp_ajax_nopriv_recipe_quick_search', array($this, 'quick_search'));
    }
    
    /**
     * Toggle Recipe Favorite
     */
    public function toggle_favorite() {
        check_ajax_referer('recipe-ajax-nonce', 'nonce');
        
        $recipe_id = intval($_POST['recipe_id']);
        $user_id = get_current_user_id();
        
        if (!$recipe_id) {
            wp_send_json_error('Invalid recipe ID');
        }
        
        // For logged in users, use user meta
        if ($user_id) {
            $favorites = get_user_meta($user_id, 'recipe_favorites', true);
            if (!is_array($favorites)) {
                $favorites = array();
            }
            
            if (in_array($recipe_id, $favorites)) {
                // Remove from favorites
                $favorites = array_diff($favorites, array($recipe_id));
                $is_favorite = false;
            } else {
                // Add to favorites
                $favorites[] = $recipe_id;
                $is_favorite = true;
            }
            
            update_user_meta($user_id, 'recipe_favorites', $favorites);
        } else {
            // For guests, use cookies
            $cookie_name = 'recipe_favorites';
            $favorites = isset($_COOKIE[$cookie_name]) ? json_decode(stripslashes($_COOKIE[$cookie_name]), true) : array();
            
            if (!is_array($favorites)) {
                $favorites = array();
            }
            
            if (in_array($recipe_id, $favorites)) {
                $favorites = array_diff($favorites, array($recipe_id));
                $is_favorite = false;
            } else {
                $favorites[] = $recipe_id;
                $is_favorite = true;
            }
            
            setcookie($cookie_name, json_encode($favorites), time() + (30 * DAY_IN_SECONDS), '/');
        }
        
        // Update recipe favorite count
        $total_favorites = intval(get_post_meta($recipe_id, '_recipe_favorites_count', true));
        if ($is_favorite) {
            $total_favorites++;
        } else {
            $total_favorites = max(0, $total_favorites - 1);
        }
        update_post_meta($recipe_id, '_recipe_favorites_count', $total_favorites);
        
        wp_send_json_success(array(
            'is_favorite' => $is_favorite,
            'total_favorites' => $total_favorites
        ));
    }
    
    /**
     * Rate Recipe
     */
    public function rate_recipe() {
        check_ajax_referer('recipe-ajax-nonce', 'nonce');
        
        $recipe_id = intval($_POST['recipe_id']);
        $rating = intval($_POST['rating']);
        
        if (!$recipe_id || $rating < 1 || $rating > 5) {
            wp_send_json_error('Invalid data');
        }
        
        $user_id = get_current_user_id();
        $user_ip = $_SERVER['REMOTE_ADDR'];
        
        // Check if user already rated
        $ratings = get_post_meta($recipe_id, '_recipe_ratings', true);
        if (!is_array($ratings)) {
            $ratings = array();
        }
        
        $user_key = $user_id ? 'user_' . $user_id : 'ip_' . $user_ip;
        
        // Update rating
        $ratings[$user_key] = $rating;
        update_post_meta($recipe_id, '_recipe_ratings', $ratings);
        
        // Calculate average rating
        $total_ratings = count($ratings);
        $sum_ratings = array_sum($ratings);
        $average_rating = $total_ratings > 0 ? round($sum_ratings / $total_ratings, 1) : 0;
        
        update_post_meta($recipe_id, '_recipe_average_rating', $average_rating);
        update_post_meta($recipe_id, '_recipe_total_ratings', $total_ratings);
        
        wp_send_json_success(array(
            'average_rating' => $average_rating,
            'total_ratings' => $total_ratings
        ));
    }
    
    /**
     * Track Recipe View
     */
    public function track_view() {
        check_ajax_referer('recipe-ajax-nonce', 'nonce');
        
        $recipe_id = intval($_POST['recipe_id']);
        
        if (!$recipe_id) {
            wp_send_json_error('Invalid recipe ID');
        }
        
        // Increment view count
        $views = intval(get_post_meta($recipe_id, '_recipe_views', true));
        update_post_meta($recipe_id, '_recipe_views', $views + 1);
        
        wp_send_json_success();
    }
    
    /**
     * Load More Recipes
     */
    public function load_more_recipes() {
        check_ajax_referer('recipe-ajax-nonce', 'nonce');
        
        $page = intval($_POST['page']);
        $per_page = intval($_POST['per_page']) ?: 12;
        $cuisine = sanitize_text_field($_POST['cuisine']);
        $course = sanitize_text_field($_POST['course']);
        
        $args = array(
            'post_type' => 'recipe',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'post_status' => 'publish'
        );
        
        // Add taxonomy filters
        $tax_query = array();
        
        if ($cuisine) {
            $tax_query[] = array(
                'taxonomy' => 'recipe_cuisine',
                'field' => 'slug',
                'terms' => $cuisine
            );
        }
        
        if ($course) {
            $tax_query[] = array(
                'taxonomy' => 'recipe_course',
                'field' => 'slug',
                'terms' => $course
            );
        }
        
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }
        
        $recipes = new WP_Query($args);
        
        ob_start();
        
        if ($recipes->have_posts()) {
            while ($recipes->have_posts()) {
                $recipes->the_post();
                // Use a template part for consistency
                ?>
                <article class="recipe-card-archive">
                    <a href="<?php the_permalink(); ?>" class="recipe-card-link">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="recipe-card-image">
                                <?php the_post_thumbnail('medium_large'); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="recipe-card-content">
                            <h2 class="recipe-card-title"><?php the_title(); ?></h2>
                            
                            <div class="recipe-card-meta">
                                <?php
                                $prep_time = get_post_meta(get_the_ID(), '_recipe_prep_time', true);
                                $servings = get_post_meta(get_the_ID(), '_recipe_servings', true);
                                ?>
                                
                                <?php if ($prep_time) : ?>
                                    <span class="meta-item">
                                        <?php echo esc_html($prep_time); ?> <?php _e('mins', 'recipe-challenge-pro'); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($servings) : ?>
                                    <span class="meta-item">
                                        <?php echo esc_html($servings); ?> <?php _e('servings', 'recipe-challenge-pro'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </article>
                <?php
            }
        }
        
        wp_reset_postdata();
        
        $html = ob_get_clean();
        $has_more = $recipes->max_num_pages > $page;
        
        wp_send_json_success(array(
            'html' => $html,
            'has_more' => $has_more
        ));
    }
    
    /**
     * Quick Search
     */
    public function quick_search() {
        $search_term = sanitize_text_field($_POST['search']);
        
        if (strlen($search_term) < 3) {
            wp_send_json_success(array('results' => array()));
        }
        
        $args = array(
            'post_type' => 'recipe',
            's' => $search_term,
            'posts_per_page' => 5,
            'post_status' => 'publish'
        );
        
        $recipes = new WP_Query($args);
        $results = array();
        
        if ($recipes->have_posts()) {
            while ($recipes->have_posts()) {
                $recipes->the_post();
                
                $results[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'url' => get_permalink(),
                    'thumbnail' => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail')
                );
            }
        }
        
        wp_reset_postdata();
        
        wp_send_json_success(array('results' => $results));
    }
}