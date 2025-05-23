
<?php
/**
 * Recipe Frontend Class
 */

class Recipe_Frontend {
    
    public function __construct() {
        add_action('wp_head', array($this, 'add_schema_markup'));
        add_filter('body_class', array($this, 'add_body_classes'));
        add_action('wp_footer', array($this, 'add_print_styles'));
        add_shortcode('recipe_nutrition', array($this, 'nutrition_shortcode'));
        add_action('init', array($this, 'handle_recipe_print'));
        add_filter('the_content', array($this, 'add_recipe_buttons'), 15);
    }
    
    /**
     * Add Schema markup for recipes
     */
    public function add_schema_markup() {
        if (!is_singular('recipe')) {
            return;
        }
        
        global $post;
        
        $ingredients = get_post_meta($post->ID, '_recipe_ingredients', true);
        $instructions = get_post_meta($post->ID, '_recipe_instructions', true);
        $prep_time = get_post_meta($post->ID, '_recipe_prep_time', true);
        $cook_time = get_post_meta($post->ID, '_recipe_cook_time', true);
        $total_time = get_post_meta($post->ID, '_recipe_total_time', true);
        $servings = get_post_meta($post->ID, '_recipe_servings', true);
        $rating = get_post_meta($post->ID, '_recipe_average_rating', true);
        $rating_count = get_post_meta($post->ID, '_recipe_total_ratings', true);
        
        $schema = array(
            '@context' => 'https://schema.org/',
            '@type' => 'Recipe',
            'name' => get_the_title(),
            'description' => get_the_excerpt(),
            'author' => array(
                '@type' => 'Person',
                'name' => get_the_author()
            ),
            'datePublished' => get_the_date('c'),
            'image' => get_the_post_thumbnail_url($post->ID, 'full')
        );
        
        // Add ingredients
        if (is_array($ingredients)) {
            $schema['recipeIngredient'] = array();
            foreach ($ingredients as $ingredient) {
                $text = '';
                if (!empty($ingredient['amount'])) {
                    $text .= $ingredient['amount'] . ' ';
                }
                if (!empty($ingredient['unit'])) {
                    $text .= $ingredient['unit'] . ' ';
                }
                $text .= $ingredient['ingredient'];
                if (!empty($ingredient['notes'])) {
                    $text .= ' (' . $ingredient['notes'] . ')';
                }
                $schema['recipeIngredient'][] = trim($text);
            }
        }
        
        // Add instructions
        if (is_array($instructions)) {
            $schema['recipeInstructions'] = array();
            foreach ($instructions as $index => $instruction) {
                $schema['recipeInstructions'][] = array(
                    '@type' => 'HowToStep',
                    'text' => $instruction,
                    'position' => $index + 1
                );
            }
        }
        
        // Add times
        if ($prep_time) {
            $schema['prepTime'] = 'PT' . $prep_time . 'M';
        }
        if ($cook_time) {
            $schema['cookTime'] = 'PT' . $cook_time . 'M';
        }
        if ($total_time) {
            $schema['totalTime'] = 'PT' . $total_time . 'M';
        }
        
        // Add yield
        if ($servings) {
            $schema['recipeYield'] = $servings . ' servings';
        }
        
        // Add rating
        if ($rating && $rating_count) {
            $schema['aggregateRating'] = array(
                '@type' => 'AggregateRating',
                'ratingValue' => $rating,
                'ratingCount' => $rating_count
            );
        }
        
        // Add categories
        $cuisines = wp_get_post_terms($post->ID, 'recipe_cuisine', array('fields' => 'names'));
        if (!empty($cuisines)) {
            $schema['recipeCuisine'] = implode(', ', $cuisines);
        }
        
        $courses = wp_get_post_terms($post->ID, 'recipe_course', array('fields' => 'names'));
        if (!empty($courses)) {
            $schema['recipeCategory'] = implode(', ', $courses);
        }
        
        ?>
        <script type="application/ld+json">
        <?php echo wp_json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?>
        </script>
        <?php
    }
    
    /**
     * Add body classes
     */
    public function add_body_classes($classes) {
        if (is_singular('recipe')) {
            $classes[] = 'single-recipe';
        }
        
        if (is_post_type_archive('recipe')) {
            $classes[] = 'recipe-archive';
        }
        
        return $classes;
    }
    
    /**
     * Add print styles
     */
    public function add_print_styles() {
        if (!is_singular('recipe')) {
            return;
        }
        ?>
        <style id="recipe-print-styles">
        @media print {
            body * {
                visibility: hidden;
            }
            
            .recipe-wrapper,
            .recipe-wrapper * {
                visibility: visible;
            }
            
            .recipe-wrapper {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            
            .recipe-actions,
            .jump-to-recipe,
            .recipe-scale,
            .ingredient-checkbox,
            .recipe-comments,
            header.site-header,
            footer.site-footer,
            .site-navigation,
            .sidebar,
            .related-recipes {
                display: none !important;
            }
            
            .recipe-card {
                box-shadow: none;
                border: 1px solid #ddd;
                page-break-inside: avoid;
            }
            
            .recipe-title {
                font-size: 24pt;
            }
            
            .instruction-item {
                page-break-inside: avoid;
            }
        }
        </style>
        <?php
    }
    
    /**
     * Handle recipe print
     */
    public function handle_recipe_print() {
        if (!isset($_GET['print-recipe']) || !is_singular('recipe')) {
            return;
        }
        
        // Add print window JavaScript
        add_action('wp_footer', function() {
            ?>
            <script>
            window.onload = function() {
                window.print();
            };
            </script>
            <?php
        });
    }
    
    /**
     * Add recipe buttons to content
     */
    public function add_recipe_buttons($content) {
        if (!is_singular('recipe') || !in_the_loop() || !is_main_query()) {
            return $content;
        }
        
        $enable_jump = get_option('rcp_enable_jump_to_recipe', 1);
        
        if (!$enable_jump) {
            return $content;
        }
        
        // Check if recipe card is present in content
        if (strpos($content, 'recipe-card') === false) {
            return $content;
        }
        
        // Add jump to recipe button at the beginning
        $jump_button = '<div class="recipe-jump-wrapper">';
        $jump_button .= '<a href="#recipe-card" class="jump-to-recipe-inline">';
        $jump_button .= __('Jump to Recipe', 'recipe-challenge-pro');
        $jump_button .= '</a>';
        $jump_button .= '</div>';
        
        return $jump_button . $content;
    }
    
    /**
     * Nutrition Information Shortcode
     */
    public function nutrition_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => get_the_ID(),
            'style' => 'table'
        ), $atts);
        
        $nutrition = get_post_meta($atts['id'], '_recipe_nutrition', true);
        
        if (!is_array($nutrition) || empty($nutrition)) {
            return '';
        }
        
        ob_start();
        
        if ($atts['style'] === 'table') {
            ?>
            <div class="recipe-nutrition-table">
                <h3><?php _e('Nutrition Information', 'recipe-challenge-pro'); ?></h3>
                <table>
                    <tbody>
                        <?php foreach ($nutrition as $key => $value) : ?>
                            <?php if (!empty($value)) : ?>
                                <tr>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $key)); ?></td>
                                    <td><?php echo esc_html($value); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
        } else {
            ?>
            <div class="recipe-nutrition-list">
                <h3><?php _e('Nutrition Information', 'recipe-challenge-pro'); ?></h3>
                <ul>
                    <?php foreach ($nutrition as $key => $value) : ?>
                        <?php if (!empty($value)) : ?>
                            <li>
                                <strong><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</strong>
                                <?php echo esc_html($value); ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php
        }
        
        return ob_get_clean();
    }
}