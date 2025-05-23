
<?php
/**
 * Favorites Page Template
 */

get_header();
?>

<div class="recipe-favorites-page">
    <div class="container">
        <header class="favorites-header">
            <h1 class="favorites-title"><?php _e('My Favorite Recipes', 'recipe-challenge-pro'); ?></h1>
            
            <?php if (!is_user_logged_in()) : ?>
                <div class="favorites-notice">
                    <p><?php _e('You are viewing your favorites as a guest. Log in to save your favorites permanently.', 'recipe-challenge-pro'); ?></p>
                </div>
            <?php endif; ?>
        </header>
        
        <div class="favorites-content">
            <?php echo do_shortcode('[recipe_favorites columns="3"]'); ?>
        </div>
        
        <div class="favorites-actions">
            <a href="<?php echo rcp_get_archive_url(); ?>" class="button browse-recipes">
                <?php _e('Browse More Recipes', 'recipe-challenge-pro'); ?>
            </a>
        </div>
    </div>
</div>

<style>
.recipe-favorites-page {
    padding: 40px 0;
    min-height: 70vh;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.favorites-header {
    text-align: center;
    margin-bottom: 40px;
}

.favorites-title {
    font-size: 2.5em;
    margin-bottom: 20px;
    color: #333;
}

.favorites-notice {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 30px;
    display: inline-block;
}

.favorites-notice p {
    margin: 0;
    color: #856404;
}

.favorites-actions {
    text-align: center;
    margin-top: 40px;
}

.browse-recipes {
    display: inline-block;
    padding: 12px 30px;
    background: #f39c12;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 600;
    transition: background 0.3s ease;
}

.browse-recipes:hover {
    background: #e67e22;
}
</style>

<?php get_footer(); ?>