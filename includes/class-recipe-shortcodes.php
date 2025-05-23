<?php
/**
 * Recipe Shortcodes Class
 */

class Recipe_Shortcodes {
    
    public function __construct() {
        add_shortcode('recipe_card', array($this, 'recipe_card_shortcode'));
        add_shortcode('recipe_list', array($this, 'recipe_list_shortcode'));
        add_shortcode('recipe_search', array($this, 'recipe_search_shortcode'));
        add_shortcode('popular_recipes', array($this, 'popular_recipes_shortcode'));
        add_shortcode('recent_recipes', array($this, 'recent_recipes_shortcode'));
        add_shortcode('recipe_categories', array($this, 'recipe_categories_shortcode'));
    }
    
    /**
     * Recipe Card Shortcode
     */
    public function recipe_card_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'style' => 'default'
        ), $atts);
        
        if (empty($atts['id'])) {
            return '';
        }
        
        $recipe = get_post($atts['id']);
        if (!$recipe || $recipe->post_type !== 'recipe') {
            return '';
        }
        
        ob_start();
        ?>
        <div class="recipe-shortcode-card <?php echo esc_attr($atts['style']); ?>">
            <?php if (has_post_thumbnail($recipe->ID)) : ?>
                <div class="recipe-sc-image">
                    <?php echo get_the_post_thumbnail($recipe->ID, 'medium'); ?>
                </div>
            <?php endif; ?>
            
            <div class="recipe-sc-content">
                <h3 class="recipe-sc-title">
                    <a href="<?php echo get_permalink($recipe->ID); ?>">
                        <?php echo esc_html($recipe->post_title); ?>
                    </a>
                </h3>
                
                <div class="recipe-sc-meta">
                    <?php
                    $prep_time = get_post_meta($recipe->ID, '_recipe_prep_time', true);
                    $servings = get_post_meta($recipe->ID, '_recipe_servings', true);
                    ?>
                    
                    <?php if ($prep_time) : ?>
                        <span class="sc-meta-item">
                            <?php echo esc_html($prep_time); ?> <?php _e('mins', 'recipe-challenge-pro'); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($servings) : ?>
                        <span class="sc-meta-item">
                            <?php echo esc_html($servings); ?> <?php _e('servings', 'recipe-challenge-pro'); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Recipe List Shortcode
     */
    public function recipe_list_shortcode($atts) {
        $atts = shortcode_atts(array(
            'count' => 5,
            'cuisine' => '',
            'course' => '',
            'orderby' => 'date',
            'order' => 'DESC',
            'columns' => 1
        ), $atts);
        
        $args = array(
            'post_type' => 'recipe',
            'posts_per_page' => intval($atts['count']),
            'orderby' => $atts['orderby'],
            'order' => $atts['order']
        );
        
        // Add taxonomy filters
        $tax_query = array();
        
        if (!empty($atts['cuisine'])) {
            $tax_query[] = array(
                'taxonomy' => 'recipe_cuisine',
                'field' => 'slug',
                'terms' => $atts['cuisine']
            );
        }
        
        if (!empty($atts['course'])) {
            $tax_query[] = array(
                'taxonomy' => 'recipe_course',
                'field' => 'slug',
                'terms' => $atts['course']
            );
        }
        
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }
        
        $recipes = new WP_Query($args);
        
        if (!$recipes->have_posts()) {
            return '<p>' . __('No recipes found.', 'recipe-challenge-pro') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="recipe-list-shortcode columns-<?php echo esc_attr($atts['columns']); ?>">
            <?php while ($recipes->have_posts()) : $recipes->the_post(); ?>
                <div class="recipe-list-item">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="recipe-list-image">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('thumbnail'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="recipe-list-content">
                        <h4 class="recipe-list-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h4>
                        
                        <div class="recipe-list-meta">
                            <?php
                            $prep_time = get_post_meta(get_the_ID(), '_recipe_prep_time', true);
                            if ($prep_time) {
                                echo '<span>' . esc_html($prep_time) . ' ' . __('mins', 'recipe-challenge-pro') . '</span>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <?php
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Recipe Search Shortcode
     */
    public function recipe_search_shortcode($atts) {
        $atts = shortcode_atts(array(
            'placeholder' => __('Search recipes...', 'recipe-challenge-pro')
        ), $atts);
        
        ob_start();
        ?>
        <form class="recipe-search-form" method="get" action="<?php echo esc_url(home_url('/')); ?>">
            <input type="hidden" name="post_type" value="recipe" />
            <div class="recipe-search-wrapper">
                <input type="search" 
                       name="s" 
                       class="recipe-search-input" 
                       placeholder="<?php echo esc_attr($atts['placeholder']); ?>" 
                       value="<?php echo get_search_query(); ?>" />
                <button type="submit" class="recipe-search-button">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Popular Recipes Shortcode
     */
    public function popular_recipes_shortcode($atts) {
        $atts = shortcode_atts(array(
            'count' => 5,
            'style' => 'list'
        ), $atts);
        
        $args = array(
            'post_type' => 'recipe',
            'posts_per_page' => intval($atts['count']),
            'meta_key' => '_recipe_views',
            'orderby' => 'meta_value_num',
            'order' => 'DESC'
        );
        
        $recipes = new WP_Query($args);
        
        if (!$recipes->have_posts()) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="popular-recipes-widget style-<?php echo esc_attr($atts['style']); ?>">
            <h3><?php _e('Popular Recipes', 'recipe-challenge-pro'); ?></h3>
            <ul class="popular-recipes-list">
                <?php while ($recipes->have_posts()) : $recipes->the_post(); ?>
                    <li class="popular-recipe-item">
                        <a href="<?php the_permalink(); ?>">
                            <?php if ($atts['style'] === 'grid' && has_post_thumbnail()) : ?>
                                <div class="popular-recipe-image">
                                    <?php the_post_thumbnail('thumbnail'); ?>
                                </div>
                            <?php endif; ?>
                            <div class="popular-recipe-content">
                                <h4><?php the_title(); ?></h4>
                                <?php
                                $views = get_post_meta(get_the_ID(), '_recipe_views', true);
                                if ($views) {
                                    echo '<span class="recipe-views">' . sprintf(__('%s views', 'recipe-challenge-pro'), number_format($views)) . '</span>';
                                }
                                ?>
                            </div>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
        <?php
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Recent Recipes Shortcode
     */
    public function recent_recipes_shortcode($atts) {
        $atts = shortcode_atts(array(
            'count' => 5,
            'show_date' => 'yes'
        ), $atts);
        
        $args = array(
            'post_type' => 'recipe',
            'posts_per_page' => intval($atts['count']),
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        $recipes = new WP_Query($args);
        
        if (!$recipes->have_posts()) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="recent-recipes-widget">
            <h3><?php _e('Recent Recipes', 'recipe-challenge-pro'); ?></h3>
            <ul class="recent-recipes-list">
                <?php while ($recipes->have_posts()) : $recipes->the_post(); ?>
                    <li class="recent-recipe-item">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        <?php if ($atts['show_date'] === 'yes') : ?>
                            <span class="recipe-date"><?php echo get_the_date(); ?></span>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
        <?php
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Recipe Categories Shortcode
     */
    public function recipe_categories_shortcode($atts) {
        $atts = shortcode_atts(array(
            'taxonomy' => 'recipe_cuisine',
            'style' => 'list',
            'show_count' => 'yes'
        ), $atts);
        
        $terms = get_terms(array(
            'taxonomy' => $atts['taxonomy'],
            'hide_empty' => true
        ));
        
        if (is_wp_error($terms) || empty($terms)) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="recipe-categories-widget style-<?php echo esc_attr($atts['style']); ?>">
            <h3>
                <?php 
                echo $atts['taxonomy'] === 'recipe_cuisine' 
                    ? __('Recipe Cuisines', 'recipe-challenge-pro') 
                    : __('Recipe Courses', 'recipe-challenge-pro'); 
                ?>
            </h3>
            <ul class="recipe-categories-list">
                <?php foreach ($terms as $term) : ?>
                    <li class="recipe-category-item">
                        <a href="<?php echo get_term_link($term); ?>">
                            <?php echo esc_html($term->name); ?>
                            <?php if ($atts['show_count'] === 'yes') : ?>
                                <span class="category-count">(<?php echo $term->count; ?>)</span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }
}