jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize ingredient index
    var ingredientIndex = $('#recipe-ingredients-list .ingredient-item').length;
    
    // Add Ingredient
    $('#add-ingredient').on('click', function(e) {
        e.preventDefault();
        
        var template = $('#ingredient-template').html();
        if (template) {
            var newIngredient = template.replace(/{{index}}/g, ingredientIndex);
            $('#recipe-ingredients-list').append(newIngredient);
            ingredientIndex++;
        }
    });
    
    // Remove Ingredient
    $(document).on('click', '.remove-ingredient', function(e) {
        e.preventDefault();
        $(this).closest('.ingredient-item').fadeOut(300, function() {
            $(this).remove();
            reindexIngredients();
        });
    });
    
    // Reindex ingredients after removal
    function reindexIngredients() {
        ingredientIndex = 0;
        $('#recipe-ingredients-list .ingredient-item').each(function() {
            $(this).find('input').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    name = name.replace(/\[\d+\]/, '[' + ingredientIndex + ']');
                    $(this).attr('name', name);
                }
            });
            ingredientIndex++;
        });
    }
    
    // Add Instruction
    $('#add-instruction').on('click', function(e) {
        e.preventDefault();
        
        var template = $('#instruction-template').html();
        if (template) {
            var instructionNumber = $('#recipe-instructions-list .instruction-item').length + 1;
            var newInstruction = template.replace(/{{number}}/g, instructionNumber);
            $('#recipe-instructions-list').append(newInstruction);
        }
    });
    
    // Remove Instruction
    $(document).on('click', '.remove-instruction', function(e) {
        e.preventDefault();
        $(this).closest('.instruction-item').fadeOut(300, function() {
            $(this).remove();
            reindexInstructions();
        });
    });
    
    // Reindex instructions after removal
    function reindexInstructions() {
        $('#recipe-instructions-list .instruction-item').each(function(index) {
            $(this).find('.step-number').text((index + 1) + '.');
        });
    }
    
    // Auto-calculate total time
    $('#recipe_prep_time, #recipe_cook_time').on('change keyup', function() {
        var prepTime = parseInt($('#recipe_prep_time').val()) || 0;
        var cookTime = parseInt($('#recipe_cook_time').val()) || 0;
        $('#recipe_total_time').val(prepTime + cookTime);
    });
    
    // Make ingredients sortable
    if ($.fn.sortable) {
        $('#recipe-ingredients-list').sortable({
            items: '.ingredient-item',
            handle: 'input',
            placeholder: 'ingredient-placeholder',
            update: function() {
                reindexIngredients();
            }
        });
        
        $('#recipe-instructions-list').sortable({
            items: '.instruction-item',
            handle: '.step-number',
            placeholder: 'instruction-placeholder',
            update: function() {
                reindexInstructions();
            }
        });
    }
});