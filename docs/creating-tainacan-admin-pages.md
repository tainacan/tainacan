# Creating Tainacan Admin Pages

Tainacan 1.0.0 introduces a system for developers to create custom admin pages that integrate with the Tainacan admin interface. This system follows WordPress best practices and provides a familiar development experience for plugin developers.

## Sample Plugin

For a complete working example, refer to the [Tainacan Sample Plugin](https://github.com/tainacan/tainacan-sample-plugin), which demonstrates all the concepts covered in this documentation. It creates a page inside the Tainacan Admin and even has basic build system to compile some `.js` and `.scss` in the frontend using the modern `wp-scripts`.

![Image with the page created via the sample plugin.](/_assets/images/creating-tainacan-admin-pages.png)

## Overview

The Tainacan admin page system is built around the abstract `\Tainacan\Pages` class, which provides a foundation for creating custom admin interfaces. When you extend this class, your page automatically inherits:

- Tainacan's navigation sidebar and layout
- Consistent styling and theming
- User preference management
- Asset loading and management
- WordPress admin integration

## Key Concepts

### 1. The Pages Base Class

The `\Tainacan\Pages` abstract class serves as the foundation for all Tainacan admin pages. It provides:

- **Navigation Integration**: Automatic integration with Tainacan's sidebar navigation
- **Asset Management**: Built-in CSS and JavaScript loading
- **User Preferences**: Access to Tainacan's user preference system
- **Admin UI Options**: Control over various admin interface elements
- **WordPress Integration**: Seamless integration with WordPress admin hooks

### 2. Required Methods

Every class extending `\Tainacan\Pages` must implement:

- `get_page_slug()`: Returns a unique identifier for the page
- `render_page_content()`: Renders the main content of the page

### 3. Optional Methods

You can override these methods to customize behavior:

- `add_admin_menu()`: Register menu items
- `admin_enqueue_css()`: Load custom CSS
- `admin_enqueue_js()`: Load custom JavaScript
- `load_page()`: Handle page-specific loading logic

## Implementation Guide

### Step 1: Plugin Structure

Create a plugin with the following structure:

```
your-plugin/
├── your-plugin.php
├── class-your-plugin.php
├── page.php
├── src/
│   ├── index.js
│   └── index.scss
├── build/
├── package.json
└── build.sh
```

### Step 2: Main Plugin File

```php
<?php
/*
Plugin Name: Your Tainacan Plugin
Plugin URI: https://your-site.com/
Description: Your plugin description
Author: Your Name
Version: 1.0.0
Requires at least: 6.5
Tested up to: 6.7
Requires PHP: 7.0
Stable tag: 1.0.0
Text Domain: your-plugin
License: GPLv2 or later
Requires Plugins: tainacan
*/

if (!defined('ABSPATH')) {
    exit; // Avoid direct access
}

add_action('plugins_loaded', function() {
    if (class_exists('\Tainacan\Pages')) {
        require_once(__DIR__ . '/class-your-plugin.php');
        $Your_Plugin = \Tainacan\Your_Plugin::get_instance();
    } else {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error is-dismissible"><p>' . 
                 __('Your Plugin requires Tainacan plugin with version greater than or equal to 1.0.0 to be installed and activated.', 'your-plugin') . 
                 '</p></div>';
        });
    }
});
```

### Step 3: Main Class Implementation

```php
<?php

namespace Tainacan;

class Your_Plugin extends \Tainacan\Pages {
    use \Tainacan\Traits\Singleton_Instance;

    /**
     * Define the unique page slug for your admin page
     */
    protected function get_page_slug() : string {
        return 'your_plugin_page';
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        parent::init();
        // Add your custom initialization logic here
    }

    /**
     * Register the admin menu item
     */
    public function add_admin_menu() {
        $page_suffix = add_submenu_page(
            $this->tainacan_root_menu_slug,        // Parent menu (Tainacan root)
            __('Your Plugin', 'your-plugin'),      // Page title
            '<span class="icon">' . $this->get_svg_icon('star') . '</span>' . 
            '<span class="menu-text">' . __('Your Plugin', 'your-plugin') . '</span>', // Menu text
            'read',                                // Capability required
            $this->get_page_slug(),                // Menu slug
            array(&$this, 'render_page'),          // Callback function
            3                                      // Position in menu
        );
        
        // Hook to load page-specific assets
        add_action('load-' . $page_suffix, array(&$this, 'load_page'));
    }

    /**
     * Load page-specific CSS
     */
    function admin_enqueue_css() {
        $asset_file = include(plugin_dir_path(__FILE__) . 'build/index.asset.php');
        
        wp_enqueue_style(
            'your-plugin-page',
            plugins_url('build/index.css', __FILE__),
            $asset_file['dependencies'],
            $asset_file['version']
        );
    }

    /**
     * Load page-specific JavaScript
     */
    function admin_enqueue_js() {
        $asset_file = include(plugin_dir_path(__FILE__) . 'build/index.asset.php');
        
        wp_enqueue_script(
            'your-plugin-page',
            plugins_url('build/index.js', __FILE__),
            $asset_file['dependencies'],
            $asset_file['version'],
            true
        );
    }

    /**
     * Render the main page content
     */
    public function render_page_content() {
        require_once('page.php');
    }
}
```

### Step 4: Page Template

```php
<div class="wrap tainacan-page-container-content">
    <div class="tainacan-fixed-subheader">
        <h1 class="tainacan-page-title">
            <?php _e('Your Plugin Page', 'your-plugin'); ?>
        </h1>
    </div>
    
    <div class="your-plugin-content">
        <?php _e('This is your custom plugin page content.', 'your-plugin'); ?>
        
        <!-- Add your custom HTML, forms, tables, etc. here -->
        <div class="your-custom-section">
            <h2><?php _e('Custom Section', 'your-plugin'); ?></h2>
            <p><?php _e('This is where you can add your custom functionality.', 'your-plugin'); ?></p>
        </div>
    </div>
</div>
```

### Step 5: Frontend Assets

#### SCSS (src/index.scss)

```scss
/**
 * Your Plugin Styles
 */
.your-plugin-content {
    padding: 20px;
    
    .your-custom-section {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 20px;
        margin-top: 20px;
        
        h2 {
            margin-top: 0;
            color: #333;
        }
    }
}
```

#### JavaScript (src/index.js)
```javascript
import './index.scss';

document.addEventListener("DOMContentLoaded", () => {
    console.log("Your Plugin Loaded");
    
    // Add your custom JavaScript functionality here
    // You can interact with Tainacan's global objects:
    // - tainacan_user: User data and capabilities
    // - tainacan_plugin: Plugin configuration and settings
});
```

### Step 6: Build Configuration

#### package.json
```json
{
  "name": "your-tainacan-plugin",
  "version": "1.0.0",
  "description": "Your Tainacan plugin description",
  "scripts": {
    "build": "wp-scripts build",
    "start": "wp-scripts start",
    "lint": "wp-scripts lint-js",
    "format": "wp-scripts format-js"
  },
  "devDependencies": {
    "@wordpress/scripts": "^30.11.0"
  }
}
```

#### build.sh
```bash
echo "Building Your Plugin..."
cd ./your-plugin
npm install
npm run build
cd ../

if [ -z "$1" ]
then
    echo "Build complete!"
else
    echo "Moving files to destination folder: $1"
    rm -rf $1/your-plugin
    cp -r ./your-plugin $1
    echo "Cleaning build files..."
    rm -f $1/your-plugin/package.json
    rm -f $1/your-plugin/package-lock.json
    rm -rf $1/your-plugin/node_modules
    rm -rf $1/your-plugin/src
    echo "Build complete!"
fi
```

## Advanced Features

### 1. Menu Positioning

Control where your page appears in the Tainacan admin menu:

```php
public function add_admin_menu() {
    // Add to root level (main Tainacan menu)
    $page_suffix = add_submenu_page(
        $this->tainacan_root_menu_slug,    // Root level
        __('Your Plugin', 'your-plugin'),
        __('Your Plugin', 'your-plugin'),
        'read',
        $this->get_page_slug(),
        array(&$this, 'render_page'),
        3  // Position: 1=Home, 2=Repository, 3=Collections, etc.
    );
    
    // Or add to "Others" menu (collapsible section)
    $page_suffix = add_submenu_page(
        $this->tainacan_other_links_slug,  // Others menu
        __('Your Plugin', 'your-plugin'),
        __('Your Plugin', 'your-plugin'),
        'read',
        $this->get_page_slug(),
        array(&$this, 'render_page')
    );
}
```

### 2. Custom Icons

Use Tainacan's built-in SVG icons or add your own:

```php
// Use built-in icons
$this->get_svg_icon('star')      // Star icon
$this->get_svg_icon('home')      // Home icon
$this->get_svg_icon('settings')  // Settings icon

// Add custom icons via filter
add_filter('tainacan-svg-icons-folder-path', function($path) {
    return plugin_dir_path(__FILE__) . 'assets/icons/';
});
```

## Best Practices

### Naming Conventions

- Use descriptive class names: `class Tainacan_Your_Plugin extends \Tainacan\Pages`
- Use consistent file naming: `class-tainacan-your-plugin.php`
- Use unique page slugs: `your_plugin_page`

For other WordPress development best practices, refer to the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/) and [Plugin Development Handbook](https://developer.wordpress.org/plugins/).

## Troubleshooting

### Common Issues

1. **Page not appearing in menu**: Check if Tainacan is active and version >= 1.0.0
2. **Assets not loading**: Verify the `load_page()` hook is properly set up
3. **Permission denied**: Check user capabilities and nonce verification
4. **Styling issues**: Ensure your CSS follows Tainacan's class naming conventions

## Conclusion

The Tainacan admin page system provides a way to extend the Tainacan admin interface. By following the patterns and best practices outlined in this documentation, you can create admin pages that integrate with the Tainacan experience.

Remember to:
- Always extend the `\Tainacan\Pages` class
- Implement required abstract methods
- Follow WordPress and Tainacan coding standards
- Test thoroughly with different user roles and capabilities
- Provide proper documentation for your users

For additional help and examples, visit the [Tainacan documentation wiki](https://wiki.tainacan.org/) or join the [Tainacan community](https://tainacan.org/community/).
