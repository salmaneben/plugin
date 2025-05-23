
<?php
/**
 * Recipe Archive Template
 */

get_header();
?>

<div class="recipe-archive-wrapper">
    <header class="archive-header">
        <h1 class="archive-title">
            <?php
            if (is_post_type_archive('recipe')) {
                _e('All Recipes', 'recipe-challenge-pro');
            } elseif (is_tax('recipe_cuisine')) {
                single_term_title();
                _e(' Recipes', 'recipe-challenge-pro');
            } elseif (is_tax('recipe_course')) {
                single_term_title();
                _e(' Recipes', 'recipe-challenge-pro');
            }
            ?>
        </h1>
        
        <?php if (term_description()) : ?>
            <div class="archive-description">
                <?php echo term_description(); ?>
            </div>
        <?php endif; ?>
    </header>
    
    <div class="recipe-filters">
        <form method="get" class="recipe-filter-form">
            <select name="cuisine" class="filter-select">
                <option value=""><?php _e('All Cuisines', 'recipe-challenge-pro'); ?></option>
                <?php
                $cuisines = get_terms('recipe_cuisine');
                foreach ($cuisines as $cuisine) :
                ?>
                    <option value="<?php echo esc_attr($cuisine->slug); ?>" <?php selected(get_query_var('cuisine'), $cuisine->slug); ?>>
                        <?php echo esc_html($cuisine->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="course" class="filter-select">
                <option value=""><?php _e('All Courses', 'recipe-challenge-pro'); ?></option>
                <?php
                $courses = get_terms('recipe_course');
                foreach ($courses as $course) :
                ?>
                    <option value="<?php echo esc_attr($course->slug); ?>" <?php selected(get_query_var('course'), $course->slug); ?>>
                        <?php echo esc_html($course->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="orderby" class="filter-select">
                <option value="date"><?php _e('Newest First', 'recipe-challenge-pro'); ?></option>
                <option value="title" <?php selected(get_query_var('orderby'), 'title'); ?>><?php _e('Title', 'recipe-challenge-pro'); ?></option>
                <option value="popular" <?php selected(get_query_var('orderby'), 'popular'); ?>><?php _e('Most Popular', 'recipe-challenge-pro'); ?></option>
            </select>
            
            <button type="submit" class="filter-submit"><?php _e('Filter', 'recipe-challenge-pro'); ?></button>
        </form>
    </div>
    
    <?php if (have_posts()) : ?>
        <div class="recipe-grid">
            <?php while (have_posts()) : the_post(); ?>
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
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        <?php echo esc_html($prep_time); ?> <?php _e('mins', 'recipe-challenge-pro'); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($servings) : ?>
                                    <span class="meta-item">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M16 22v-11c0-1.1-.9-2-2-2H10c-1.1 0-2 .9-2 2v11"></path>
                                            <rect x="6" y="2" width="12" height="8" rx="1"></rect>
                                        </svg>
                                        <?php echo esc_html($servings); ?> <?php _e('servings', 'recipe-challenge-pro'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (has_excerpt()) : ?>
                                <div class="recipe-card-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                </article>
            <?php endwhile; ?>
        </div>
        
        <div class="recipe-pagination">
            <?php
            the_posts_pagination(array(
                'mid_size' => 2,
                'prev_text' => __('Previous', 'recipe-challenge-pro'),
                'next_text' => __('Next', 'recipe-challenge-pro'),
            ));
            ?>
        </div>
    <?php else : ?>
        <div class="no-recipes-found">
            <p><?php _e('No recipes found. Try adjusting your filters.', 'recipe-challenge-pro'); ?></p>
        </div>
    <?php endif; ?>
</div>

<style>
.recipe-archive-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.archive-header {
    text-align: center;
    margin-bottom: 40px;
}

.archive-title {
    font-size: 2.5em;
    margin-bottom: 20px;
}

.recipe-filters {
    margin-bottom: 40px;
}

.recipe-filter-form {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.filter-select {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    min-width: 150px;
}

.filter-submit {
    padding: 10px 30px;
    background: #f39c12;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
}

.filter-submit:hover {
    background: #e67e22;
}

.recipe-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.recipe-card-archive {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.recipe-card-archive:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.recipe-card-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.recipe-card-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.recipe-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.recipe-card-content {
    padding: 20px;
}

.recipe-card-title {
    font-size: 1.4em;
    margin-bottom: 10px;
    color: #333;
}

.recipe-card-meta {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    font-size: 0.9em;
    color: #666;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.recipe-card-excerpt {
    color: #666;
    line-height: 1.6;
}

.recipe-pagination {
    text-align: center;
}

.no-recipes-found {
    text-align: center;
    padding: 60px 20px;
    font-size: 1.2em;
    color: #666;
}

@media (max-width: 768px) {
    .recipe-grid {
        grid-template-columns: 1fr;
    }
    
    .recipe-filter-form {
        flex-direction: column;
        align-items: center;
    }
    
    .filter-select {
        width: 100%;
        max-width: 300px;
    }
}
</style>

<?php get_footer(); ?>