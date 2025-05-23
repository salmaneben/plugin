// Frontend scripts
javascript
jQuery(document).ready(function($) {
    // Jump to Recipe
    $('.jump-to-recipe').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: $('#recipe-card').offset().top - 100
        }, 500);
    });
    
    // Print Recipe
    $('.print-recipe').on('click', function(e) {
        e.preventDefault();
        window.print();
    });
    
    // Add to Favorites
    $('.add-favorite').on('click', function(e) {
        e.preventDefault();
        var $button = $(this);
        var recipeId = $button.data('recipe-id');
        
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
                    $button.toggleClass('is-favorite');
                    if (response.data.is_favorite) {
                        $button.find('.button-text').text('Remove from Favorites');
                    } else {
                        $button.find('.button-text').text('Add to Favorites');
                    }
                }
            }
        });
    });
    
    // Ingredient Checkboxes
    $('.ingredient-checkbox').on('change', function() {
        var $item = $(this).closest('.ingredient-item');
        if ($(this).is(':checked')) {
            $item.addClass('checked');
        } else {
            $item.removeClass('checked');
        }
        
        // Save to localStorage
        var checkedIngredients = [];
        $('.ingredient-checkbox:checked').each(function() {
            checkedIngredients.push($(this).data('index'));
        });
        
        var recipeId = $('#recipe-card').data('recipe-id');
        localStorage.setItem('recipe_' + recipeId + '_checked', JSON.stringify(checkedIngredients));
    });
    
    // Restore checked ingredients from localStorage
    var recipeId = $('#recipe-card').data('recipe-id');
    var savedChecked = localStorage.getItem('recipe_' + recipeId + '_checked');
    if (savedChecked) {
        var checkedIngredients = JSON.parse(savedChecked);
        checkedIngredients.forEach(function(index) {
            $('.ingredient-checkbox[data-index="' + index + '"]')
                .prop('checked', true)
                .closest('.ingredient-item')
                .addClass('checked');
        });
    }
    
    // Recipe Rating (if enabled)
    $('.recipe-rating-star').on('click', function() {
        var rating = $(this).data('rating');
        var recipeId = $(this).closest('.recipe-rating').data('recipe-id');
        
        $.ajax({
            url: recipe_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'rate_recipe',
                recipe_id: recipeId,
                rating: rating,
                nonce: recipe_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update star display
                    $('.recipe-rating-star').each(function() {
                        if ($(this).data('rating') <= rating) {
                            $(this).addClass('filled');
                        } else {
                            $(this).removeClass('filled');
                        }
                    });
                    
                    // Update average rating display
                    if (response.data.average_rating) {
                        $('.recipe-average-rating').text(response.data.average_rating);
                        $('.recipe-rating-count').text('(' + response.data.total_ratings + ')');
                    }
                }
            }
        });
    });
    
    // Smooth scroll for anchor links
    $('a[href^="#"]').on('click', function(e) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });
    
    // Track recipe views
    if ($('#recipe-card').length) {
        var recipeId = $('#recipe-card').data('recipe-id');
        $.ajax({
            url: recipe_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'track_recipe_view',
                recipe_id: recipeId,
                nonce: recipe_ajax.nonce
            }
        });
    }
});