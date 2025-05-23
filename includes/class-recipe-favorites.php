
<?php
/**
 * Recipe Favorites Class
 */

class Recipe_Favorites {
    
    public function __construct() {
        add_shortcode('recipe_favorites', array($this, 'favorites_shortcode'));
        add_shortcode('favorite_button', array($this, 'favorite_button_shortcode'));
        add_action('init', array($this, 'handle_favorites_page'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'favorites_template_redirect'));
    }
    
    /**
     * Recipe Favorites Shortcode
     */
    public function favorites_shortcode($atts) {
        $atts = shortcode_atts(array(
            'count' => -1,
            'columns' => 3
        ), $atts);
        
        $favorites = $this->get_user_favorites();
        
        if (empty($favorites)) {
            return '<p>' . __('No favorite recipes yet. Start adding some!', 'recipe-challenge-pro') . '</p>';
        }
        
        $args = array(
            'post_type' => 'recipe',
            'post__in' => $favorites,
            'posts_per_page' => $atts['count'],
            'orderby' => 'post__in'
        );
        
        $recipes = new WP_Query($args);
        
        if (!$recipes->have_posts()) {
            return '<p>' . __('No favorite recipes found.', 'recipe-challenge-pro') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="recipe-favorites-grid columns-<?php echo esc_attr($atts['columns']); ?>">
            <?php while ($recipes->have_posts()) : $recipes->the_post(); ?>
                <div class="favorite-recipe-item">
                    <a href="<?php the_permalink(); ?>" class="favorite-recipe-link">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="favorite-recipe-image">
                                <?php the_post_thumbnail('medium'); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="favorite-recipe-content">
                            <h3 class="favorite-recipe-title"><?php the_title(); ?></h3>
                            
                            <div class="favorite-recipe-meta">
                                <?php
                                $prep_time = get_post_meta(get_the_ID(), '_recipe_prep_time', true);
                                $servings = get_post_meta(get_the_ID(), '_recipe_servings', true);
                                ?>
                                
                                <?php if ($prep_time) : ?>
                                    <span><?php echo esc_html($prep_time); ?> <?php _e('mins', 'recipe-challenge-pro'); ?></span>
                                <?php endif; ?>
                                
                                <?php if ($servings) : ?>
                                    <span><?php echo esc_html($servings); ?> <?php _e('servings', 'recipe-challenge-pro'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                    
                    <button class="remove-favorite" data-recipe-id="<?php echo get_the_ID(); ?>">
                        <?php _e('Remove', 'recipe-challenge-pro'); ?>
                    </button>
                </div>
            <?php endwhile; ?>
        </div>
        
        <style>
        .recipe-favorites-grid {
            display: grid;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .recipe-favorites-grid.columns-2 {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .recipe-favorites-grid.columns-3 {
            grid-template-columns: repeat(3, 1fr);
        }
        
        .recipe-favorites-grid.columns-4 {
            grid-template-columns: repeat(4, 1fr);
        }
        
        .favorite-recipe-item {
            position: relative;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .favorite-recipe-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .favorite-recipe-image {
            width: 100%;
            height: 200px;
            overflow: hidden;
        }
        
        .favorite-recipe-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .favorite-recipe-content {
            padding: 15px;
        }
        
        .favorite-recipe-title {
            font-size: 1.2em;
            margin-bottom: 10px;
        }
        
        .favorite-recipe-meta {
            display: flex;
            gap: 15px;
            font-size: 0.9em;
            color: #666;
        }
        
        .remove-favorite {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.9);
            border: 1px solid #ddd;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
            font-size: 0.9em;
        }
        
        .remove-favorite:hover {
            background: #fff;
            border-color: #e74c3c;
            color: #e74c3c;
        }
        
        @media (max-width: 768px) {
            .recipe-favorites-grid {
                grid-template-columns: 1fr;
            }
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('.remove-favorite').on('click', function(e) {
                e.preventDefault();
                var $button = $(this);
                var recipeId = $button.data('recipe-id');
                var $item = $button.closest('.favorite-recipe-item');
                
                $.ajax({
                    url: recipe_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'toggle_recipe_favorite',
                        recipe_id: recipeId,
                        nonce: recipe_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $item.fadeOut(function() {
                                $item.remove();
                                
                                // Check if no favorites left
                                if ($('.favorite-recipe-item').length === 0) {
                                    $('.recipe-favorites-grid').html('<p><?php _e('No favorite recipes yet. Start adding some!', 'recipe-challenge-pro'); ?></p>');
                                }
                            });
                        }
                    }
                });
            });
        });
        </script>
        <?php
        
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Favorite Button Shortcode
     */
    public function favorite_button_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => get_the_ID(),
            'style' => 'default'
        ), $atts);
        
        if (!get_post($atts['id']) || get_post_type($atts['id']) !== 'recipe') {
            return '';
        }
        
        $is_favorite = rcp_is_recipe_favorite($atts['id']);
        
        ob_start();
        ?>
        <button class="recipe-favorite-button <?php echo esc_attr($atts['style']); ?> <?php echo $is_favorite ? 'is-favorite' : ''; ?>" 
                data-recipe-id="<?php echo esc_attr($atts['id']); ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="<?php echo $is_favorite ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
            <span class="button-text">
                <?php echo $is_favorite ? __('Favorited', 'recipe-challenge-pro') : __('Add to Favorites', 'recipe-challenge-pro'); ?>
            </span>
        </button>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get user favorites
     */
    private function get_user_favorites() {
        $user_id = get_current_user_id();
        
        if ($user_id) {
            $favorites = get_user_meta($user_id, 'recipe_favorites', true);
        } else {
            $cookie_name = 'recipe_favorites';
            $favorites = isset($_COOKIE[$cookie_name]) ? json_decode(stripslashes($_COOKIE[$cookie_name]), true) : array();
        }
        
        return is_array($favorites) ? $favorites : array();
    }
    
    /**
     * Handle favorites page
     */
    public function handle_favorites_page() {
        // Create favorites page if it doesn't exist
        if (!get_option('rcp_favorites_page_created')) {
            $page_data = array(
                'post_title' => __('My Favorite Recipes', 'recipe-challenge-pro'),
                'post_content' => '[recipe_favorites]',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => 1
            );
            
            $page_id = wp_insert_post($page_data);
            
            if ($page_id && !is_wp_error($page_id)) {
                update_option('rcp_favorites_page_id', $page_id);
                update_option('rcp_favorites_page_created', true);
            }
        }
    }
    
    /**
     * Add query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'recipe_favorites';
        return $vars;
    }
    
    /**
     * Template redirect for favorites
     */
    public function favorites_template_redirect() {
        if (get_query_var('recipe_favorites')) {
            include RCP_PLUGIN_DIR . 'templates/favorites-template.php';
            exit;
        }
    }
}