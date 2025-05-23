# Recipe Challenge Pro

A comprehensive WordPress plugin for managing and displaying recipes with a modern, user-friendly interface.

## Features

### Recipe Management
- Custom Recipe post type with dedicated admin interface
- Ingredients management with scaling functionality
- Step-by-step instructions
- Recipe metadata (prep time, cook time, servings, difficulty)
- Custom taxonomies for Cuisines and Courses
- Featured images and gallery support

### Frontend Features
- Beautiful recipe cards with responsive design
- Recipe scaling (1/2x, 1x, 2x, 3x)
- Interactive ingredient checkboxes
- Print-friendly recipes
- Jump to Recipe button
- Social sharing buttons
- Recipe ratings and reviews
- Favorites system (works for both logged-in and guest users)
- Recipe search functionality
- Related recipes

### SEO & Performance
- Schema.org structured data for recipes
- Optimized for search engines
- Lazy loading for images
- Minimal CSS and JavaScript

### Customization Options
- Multiple recipe card styles
- Color scheme customization
- Enable/disable features via settings
- Translation ready
- RTL support

### Shortcodes

1. **Recipe Card**: `[recipe_card id="123" style="default"]`
2. **Recipe List**: `[recipe_list count="5" cuisine="italian" course="main-dish" columns="2"]`
3. **Recipe Search**: `[recipe_search placeholder="Search recipes..."]`
4. **Popular Recipes**: `[popular_recipes count="5" style="grid"]`
5. **Recent Recipes**: `[recent_recipes count="5" show_date="yes"]`
6. **Recipe Categories**: `[recipe_categories taxonomy="recipe_cuisine" style="list" show_count="yes"]`
7. **Recipe Favorites**: `[recipe_favorites columns="3"]`
8. **Favorite Button**: `[favorite_button id="123" style="default"]`
9. **Recipe Nutrition**: `[recipe_nutrition id="123" style="table"]`

### Widgets

- Popular Recipes Widget
- Recipe Categories Widget
- Recipe Search Widget

### Developer Features

- Extensive hooks and filters
- Well-documented code
- Import/Export functionality
- REST API support
- Custom templates support

## Installation

1. Upload the `recipe-challenge-pro` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure settings under Recipes > Settings
4. Start creating recipes!

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

## Usage

### Creating a Recipe

1. Go to **Recipes > Add New** in your WordPress admin
2. Enter the recipe title and description
3. Add ingredients in the Ingredients meta box
4. Add step-by-step instructions
5. Set recipe details (prep time, cook time, servings, etc.)
6. Select appropriate cuisines and courses
7. Add a featured image
8. Publish!

### Using Shortcodes

Display a specific recipe: