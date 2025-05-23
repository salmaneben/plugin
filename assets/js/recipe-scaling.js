// Recipe scaling functionality
javascript
(function($) {
    'use strict';
    
    var RecipeScaling = {
        originalServings: 1,
        currentScale: 1,
        ingredients: [],
        
        init: function() {
            this.cacheElements();
            this.bindEvents();
            this.parseIngredients();
        },
        
        cacheElements: function() {
            this.$scaleButtons = $('.scale-btn');
            this.$ingredientAmounts = $('.ingredient-amount');
            this.$servingsDisplay = $('.servings-display');
            this.originalServings = parseInt($('#recipe-card').data('servings')) || 4;
        },
        
        bindEvents: function() {
            var self = this;
            
            this.$scaleButtons.on('click', function() {
                var scale = parseFloat($(this).data('scale'));
                self.scaleRecipe(scale);
            });
        },
        
        parseIngredients: function() {
            var self = this;
            
            this.$ingredientAmounts.each(function(index) {
                var $amount = $(this);
                var originalText = $amount.text().trim();
                var parsed = self.parseAmount(originalText);
                
                self.ingredients[index] = {
                    $element: $amount,
                    original: originalText,
                    amount: parsed.amount,
                    unit: parsed.unit,
                    isRange: parsed.isRange,
                    rangeMin: parsed.rangeMin,
                    rangeMax: parsed.rangeMax
                };
            });
        },
        
        parseAmount: function(text) {
            var result = {
                amount: 0,
                unit: '',
                isRange: false,
                rangeMin: 0,
                rangeMax: 0
            };
            
            // Check for range (e.g., "1-2 cups")
            var rangeMatch = text.match(/^(\d+\.?\d*)\s*-\s*(\d+\.?\d*)\s*(.*)$/);
            if (rangeMatch) {
                result.isRange = true;
                result.rangeMin = parseFloat(rangeMatch[1]);
                result.rangeMax = parseFloat(rangeMatch[2]);
                result.unit = rangeMatch[3].trim();
                return result;
            }
            
            // Check for fractions
            var fractionMatch = text.match(/^(\d+)?\s*(\d+)\/(\d+)\s*(.*)$/);
            if (fractionMatch) {
                var whole = fractionMatch[1] ? parseInt(fractionMatch[1]) : 0;
                var numerator = parseInt(fractionMatch[2]);
                var denominator = parseInt(fractionMatch[3]);
                result.amount = whole + (numerator / denominator);
                result.unit = fractionMatch[4].trim();
                return result;
            }
            
            // Check for decimals
            var decimalMatch = text.match(/^(\d+\.?\d*)\s*(.*)$/);
            if (decimalMatch) {
                result.amount = parseFloat(decimalMatch[1]);
                result.unit = decimalMatch[2].trim();
                return result;
            }
            
            // If no number found, return original text as unit
            result.unit = text;
            return result;
        },
        
        scaleRecipe: function(scale) {
            this.currentScale = scale;
            
            // Update scale buttons
            this.$scaleButtons.removeClass('active');
            this.$scaleButtons.filter('[data-scale="' + scale + '"]').addClass('active');
            
            // Update servings display
            var newServings = Math.round(this.originalServings * scale);
            this.$servingsDisplay.text(newServings);
            
            // Update ingredients
            this.updateIngredients();
        },
        
        updateIngredients: function() {
            var self = this;
            
            this.ingredients.forEach(function(ingredient) {
                if (ingredient.amount === 0 && !ingredient.isRange) {
                    // No amount to scale (e.g., "Salt to taste")
                    return;
                }
                
                var newText = '';
                
                if (ingredient.isRange) {
                    var newMin = self.scaleAmount(ingredient.rangeMin);
                    var newMax = self.scaleAmount(ingredient.rangeMax);
                    newText = self.formatAmount(newMin) + '-' + self.formatAmount(newMax);
                    if (ingredient.unit) {
                        newText += ' ' + ingredient.unit;
                    }
                } else {
                    var newAmount = self.scaleAmount(ingredient.amount);
                    newText = self.formatAmount(newAmount);
                    if (ingredient.unit) {
                        newText += ' ' + ingredient.unit;
                    }
                }
                
                ingredient.$element.text(newText);
            });
        },
        
        scaleAmount: function(amount) {
            return amount * this.currentScale;
        },
        
        formatAmount: function(amount) {
            // Round to reasonable precision
            if (amount < 1) {
                // Convert to fraction if possible
                var fraction = this.decimalToFraction(amount);
                if (fraction) {
                    return fraction;
                }
            }
            
            // Round to 2 decimal places
            var rounded = Math.round(amount * 100) / 100;
            
            // Remove trailing zeros
            return parseFloat(rounded.toFixed(2)).toString();
        },
        
        decimalToFraction: function(decimal) {
            var tolerance = 0.01;
            var fractions = [
                {decimal: 0.125, fraction: '1/8'},
                {decimal: 0.25, fraction: '1/4'},
                {decimal: 0.333, fraction: '1/3'},
                {decimal: 0.375, fraction: '3/8'},
                {decimal: 0.5, fraction: '1/2'},
                {decimal: 0.625, fraction: '5/8'},
                {decimal: 0.666, fraction: '2/3'},
                {decimal: 0.75, fraction: '3/4'},
                {decimal: 0.875, fraction: '7/8'}
            ];
            
            for (var i = 0; i < fractions.length; i++) {
                if (Math.abs(decimal - fractions[i].decimal) < tolerance) {
                    return fractions[i].fraction;
                }
            }
            
            return null;
        }
    };
    
    $(document).ready(function() {
        if ($('#recipe-card').length) {
            RecipeScaling.init();
        }
    });
    
})(jQuery);