<?php
/**
 * Recipe Widgets
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Popular Recipes Widget
 */
class RCP_Popular_Recipes_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'rcp_popular_recipes',
            __('Popular Recipes', 'recipe-challenge-pro'),
            array('description' => __('Display popular recipes', 'recipe-challenge-pro'))
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $count = !empty($instance['count']) ? $instance['count'] : 5;
        
        $query_args = array(
            'post_type' => 'recipe',
            'posts_per_page' => $count,
            'meta_key' => '_recipe_views',
            'orderby' => 'meta_value_num',
            'order' => 'DESC'
        );
        
        $recipes = new WP_Query($query_args);
        
        if ($recipes->have_posts()) : ?>
            <ul class="popular-recipes-list">
                <?php while ($recipes->have_posts()) : $recipes->the_post(); ?>
                    <li>
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail() && !empty($instance['show_thumbnail'])) : ?>
                                <div class="widget-recipe-thumbnail">
                                    <?php the_post_thumbnail('thumbnail'); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="widget-recipe-content">
                                <h4><?php the_title(); ?></h4>
                                <?php if (!empty($instance['show_views'])) : ?>
                                    <?php $views = get_post_meta(get_the_ID(), '_recipe_views', true); ?>
                                    <span class="recipe-views"><?php echo sprintf(__('%s views', 'recipe-challenge-pro'), number_format($views)); ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php endif;
        
        wp_reset_postdata();
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Popular Recipes', 'recipe-challenge-pro');
        $count = !empty($instance['count']) ? $instance['count'] : 5;
        $show_thumbnail = !empty($instance['show_thumbnail']) ? $instance['show_thumbnail'] : 0;
        $show_views = !empty($instance['show_views']) ? $instance['show_views'] : 1;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php _e('Title:', 'recipe-challenge-pro'); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('count')); ?>">
                <?php _e('Number of recipes:', 'recipe-challenge-pro'); ?>
            </label>
            <input class="tiny-text" 
                   id="<?php echo esc_attr($this->get_field_id('count')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('count')); ?>" 
                   type="number" 
                   value="<?php echo esc_attr($count); ?>" 
                   min="1" 
                   max="10">
        </p>
        
        <p>
            <input class="checkbox" 
                   type="checkbox" 
                   <?php checked($show_thumbnail); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_thumbnail')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_thumbnail')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_thumbnail')); ?>">
                <?php _e('Show thumbnail', 'recipe-challenge-pro'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" 
                   type="checkbox" 
                   <?php checked($show_views); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_views')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_views')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_views')); ?>">
                <?php _e('Show view count', 'recipe-challenge-pro'); ?>
            </label>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['count'] = (!empty($new_instance['count'])) ? absint($new_instance['count']) : 5;
        $instance['show_thumbnail'] = !empty($new_instance['show_thumbnail']) ? 1 : 0;
        $instance['show_views'] = !empty($new_instance['show_views']) ? 1 : 0;
        
        return $instance;
    }
}

/**
 * Recipe Categories Widget
 */
class RCP_Recipe_Categories_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'rcp_recipe_categories',
            __('Recipe Categories', 'recipe-challenge-pro'),
            array('description' => __('Display recipe cuisines or courses', 'recipe-challenge-pro'))
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $taxonomy = !empty($instance['taxonomy']) ? $instance['taxonomy'] : 'recipe_cuisine';
        $show_count = !empty($instance['show_count']) ? true : false;
        $hierarchical = !empty($instance['hierarchical']) ? true : false;
        
        $cat_args = array(
            'taxonomy' => $taxonomy,
            'orderby' => 'name',
            'show_count' => $show_count,
            'hierarchical' => $hierarchical,
            'title_li' => '',
            'hide_empty' => true,
            'echo' => false
        );
        
        $categories = wp_list_categories($cat_args);
        
        if (!empty($categories)) {
            echo '<ul class="recipe-categories-list">' . $categories . '</ul>';
        }
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Recipe Categories', 'recipe-challenge-pro');
        $taxonomy = !empty($instance['taxonomy']) ? $instance['taxonomy'] : 'recipe_cuisine';
        $show_count = !empty($instance['show_count']) ? $instance['show_count'] : 0;
        $hierarchical = !empty($instance['hierarchical']) ? $instance['hierarchical'] : 0;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php _e('Title:', 'recipe-challenge-pro'); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('taxonomy')); ?>">
                <?php _e('Taxonomy:', 'recipe-challenge-pro'); ?>
            </label>
            <select class="widefat" 
                    id="<?php echo esc_attr($this->get_field_id('taxonomy')); ?>" 
                    name="<?php echo esc_attr($this->get_field_name('taxonomy')); ?>">
                <option value="recipe_cuisine" <?php selected($taxonomy, 'recipe_cuisine'); ?>>
                    <?php _e('Cuisines', 'recipe-challenge-pro'); ?>
                </option>
                <option value="recipe_course" <?php selected($taxonomy, 'recipe_course'); ?>>
                    <?php _e('Courses', 'recipe-challenge-pro'); ?>
                </option>
            </select>
        </p>
        
        <p>
            <input class="checkbox" 
                   type="checkbox" 
                   <?php checked($show_count); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_count')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_count')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_count')); ?>">
                <?php _e('Show post count', 'recipe-challenge-pro'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" 
                   type="checkbox" 
                   <?php checked($hierarchical); ?> 
                   id="<?php echo esc_attr($this->get_field_id('hierarchical')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('hierarchical')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('hierarchical')); ?>">
                <?php _e('Show hierarchy', 'recipe-challenge-pro'); ?>
            </label>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['taxonomy'] = (!empty($new_instance['taxonomy'])) ? sanitize_text_field($new_instance['taxonomy']) : 'recipe_cuisine';
        $instance['show_count'] = !empty($new_instance['show_count']) ? 1 : 0;
        $instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
        
        return $instance;
    }
}

/**
 * Recipe Search Widget
 */
class RCP_Recipe_Search_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'rcp_recipe_search',
            __('Recipe Search', 'recipe-challenge-pro'),
            array('description' => __('Search form for recipes', 'recipe-challenge-pro'))
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $placeholder = !empty($instance['placeholder']) ? $instance['placeholder'] : __('Search recipes...', 'recipe-challenge-pro');
        
        ?>
        <form role="search" method="get" class="widget-recipe-search-form" action="<?php echo esc_url(home_url('/')); ?>">
            <div class="search-form-wrapper">
                <input type="search" 
                       class="search-field" 
                       placeholder="<?php echo esc_attr($placeholder); ?>" 
                       value="<?php echo get_search_query(); ?>" 
                       name="s" />
                <input type="hidden" name="post_type" value="recipe" />
                <button type="submit" class="search-submit">
                    <span class="screen-reader-text"><?php _e('Search', 'recipe-challenge-pro'); ?></span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </div>
        </form>
        <?php
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $placeholder = !empty($instance['placeholder']) ? $instance['placeholder'] : __('Search recipes...', 'recipe-challenge-pro');
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php _e('Title:', 'recipe-challenge-pro'); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('placeholder')); ?>">
                <?php _e('Placeholder text:', 'recipe-challenge-pro'); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo esc_attr($this->get_field_id('placeholder')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('placeholder')); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($placeholder); ?>">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['placeholder'] = (!empty($new_instance['placeholder'])) ? sanitize_text_field($new_instance['placeholder']) : '';
        
        return $instance;
    }
}

/**
 * Register widgets
 */
function rcp_register_widgets() {
    register_widget('RCP_Popular_Recipes_Widget');
    register_widget('RCP_Recipe_Categories_Widget');
    register_widget('RCP_Recipe_Search_Widget');
}