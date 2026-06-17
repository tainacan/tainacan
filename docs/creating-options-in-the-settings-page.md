# Creating Options in the Settings Page

Tainacan (version >= 1.0.0) provides a streamlined way for developers to add custom options to the Tainacan Settings Page. The system handles both WordPress option registration and UI creation automatically, making it easy to extend the settings without duplicating code.

![Image with settings registered in this tutorial appearing in the Settings Page.](/_assets/images/creating-options-in-the-settings-page.png)

## How It Works

The Tainacan Settings system is built around the `\Tainacan\Settings` class, which can be called via a singletone. The `create_tainacan_setting()` method internally handles:

1. **WordPress Option Registration**: Calls `register_setting()` to create the database option
2. **Settings Field Creation**: Calls `add_settings_field()` to add the form field
3. **UI Rendering**: Uses built-in callbacks to render the form elements
4. **Data Sanitization**: Applies sanitization callbacks to user input

To understand deeply how WordPress handles option registration and settings fields, refer to the official documentation:

- [Settings API Overview](https://developer.wordpress.org/plugins/settings/settings-api/)
- [register_setting()](https://developer.wordpress.org/reference/functions/register_setting/)
- [add_settings_field()](https://developer.wordpress.org/reference/functions/add_settings_field/)

> [!NOTE]
> These are general purpose options, that usually must be available accross the plugin or even the entire website, which is why they are stored using WordPress Options API. However you may be interested on storing extra data binded to some of the Tainacan entities, such as collections, items, metadata, etc. In that case, read the [Admin Form Hook](/dev/admin-form-hooks.md) documentation to see how you can add fields to each of their Admin fields.

## Basic Usage

### Step 1: Hook into Settings Initialization

```php
add_action('admin_init', 'your_plugin_settings_init');

function your_plugin_settings_init() {
    // Your settings will be added here
}
```

### Step 2: Create Your Settings Section

You may skip this if you want to use one of the existing sections (`tainacan_settings_search_and_performance`, `tainacan_settings_theme_templates`, `tainacan_settings_items_list_defaults`...)

```php
function your_plugin_settings_init() {
    // Add a new section to the Tainacan settings page
    add_settings_section(
        'your_plugin_settings_section',            // Section ID
        __('Your Plugin Settings', 'your-plugin'), // Section title
        'your_section_description_callback',       // Description callback
        'tainacan_settings'                        // Page (always 'tainacan_settings')
    );
    
    // Add your settings here
    // ...
}
```

### Step 3: Register Your Settings

```php
function your_plugin_settings_init() {
    // Section 'your_plugin_settings_section' added above
    //...
    
    // Get the Tainacan Settings instance
    $tainacan_settings = \Tainacan\Settings::get_instance();
    
    // Register a simple text field
    $tainacan_settings->create_tainacan_setting([
        'id' => 'your_text_option',
        'title' => __('Your Text Option', 'your-plugin'),
        'section' => 'your_plugin_settings_section',
        'type' => 'string',
        'input_type' => 'text',
        'description' => __('Description of what this option does.', 'your-plugin'),
        'default' => 'default value'
    ]);
    
    // Register a checkbox
    $tainacan_settings->create_tainacan_setting([
        'id' => 'your_checkbox_option',
        'title' => __('Enable Feature', 'your-plugin'),
        'section' => 'your_plugin_settings_section',
        'type' => 'boolean',
        'input_type' => 'checkbox',
        'label' => __('Check this to enable the feature', 'your-plugin'),
        'description' => __('This will enable additional functionality.', 'your-plugin'),
        'default' => false
    ]);
}
```
> [!NOTE] Notice that `type` refers to the data type and `input_type` is the HTML element type. More on this [here](#available-setting-types).

### Step 4: Add Section Description (Optional)

```php
function your_section_description_callback() {
    echo '<p class="settings-section-description">';
    _e('Configure your plugin settings below. These options will affect how your plugin behaves.', 'your-plugin');
    echo '</p>';
}
```

## Complete Example

Here's a complete working example that adds custom settings to Tainacan:

```php
<?php
/*
Plugin Name: Your Tainacan Plugin
Description: Adds custom settings to Tainacan
Version: 1.0.0
*/

if (!defined('ABSPATH')) {
    exit;
}

class Your_Tainacan_Plugin_Settings {
    
    public function __construct() {
        add_action('admin_init', [$this, 'init_settings']);
    }
    
    public function init_settings() {
        // Add custom section
        add_settings_section(
            'your_plugin_settings',
            __('Your Plugin Settings', 'your-plugin'),
            [$this, 'section_description'],
            'tainacan_settings'
        );
        
        // Get Tainacan Settings instance
        $tainacan_settings = \Tainacan\Settings::get_instance();
        
        // Text field example
        $tainacan_settings->create_tainacan_setting([
            'id' => 'custom_api_endpoint',
            'title' => __('Custom API Endpoint', 'your-plugin'),
            'section' => 'your_plugin_settings',
            'type' => 'string',
            'input_type' => 'url',
            'description' => __('Enter the URL for your custom API endpoint.', 'your-plugin'),
            'default' => 'https://api.example.com'
        ]);
        
        // Number field example
        $tainacan_settings->create_tainacan_setting([
            'id' => 'max_collections_per_page',
            'title' => __('Maximum Collections Per Page', 'your-plugin'),
            'section' => 'your_plugin_settings',
            'type' => 'integer',
            'input_type' => 'number',
            'input_attrs' => 'min=1 max=100',
            'description' => __('Set the maximum number of collections to display per page (1-100).', 'your-plugin'),
            'default' => 20
        ]);
        
        // Select field example
        $tainacan_settings->create_tainacan_setting([
            'id' => 'display_mode',
            'title' => __('Display Mode', 'your-plugin'),
            'section' => 'your_plugin_settings',
            'type' => 'string',
            'input_type' => 'select',
            'input_inner_html' => '
                <option value="grid">Grid View</option>
                <option value="list">List View</option>
                <option value="table">Table View</option>
            ',
            'description' => __('Choose how items should be displayed by default.', 'your-plugin'),
            'default' => 'grid'
        ]);
        
        // More complex, multiple checkbox example
        $tainacan_settings->create_tainacan_setting([
            'id' => 'enabled_features',
            'title' => __('Enabled Features', 'your-plugin'),
            'section' => 'your_plugin_settings',
            'type' => 'array',
            'input_type' => 'checkbox',
            'label' => [
                'search' => __('Advanced Search', 'your-plugin'),
                'filters' => __('Custom Filters', 'your-plugin'),
                'export' => __('Data Export', 'your-plugin'),
                'analytics' => __('Usage Analytics', 'your-plugin')
            ],
            'default' => [ 'search', 'filters' ],
            'description' => __('Select which features should be enabled.', 'your-plugin'),
            'sanitize_callback' => function( $input ) {
				return is_array( $input )
					? array_map( 
						'sanitize_text_field', 
						array_filter( 
							$input, 
							function($value) { 
								return in_array($value,  [ 'search', 'filters', 'export', 'analytics' ]);
							} 
						) 
					)
					: [];
			},
        ]);
    }
    
    public function section_description() {
        echo '<p class="settings-section-description">';
        _e('Configure your plugin settings. These options will customize how your plugin integrates with Tainacan.', 'your-plugin');
        echo '</p>';
    }
}

// Initialize the settings
new Your_Tainacan_Plugin_Settings();
```

## Available Setting Types

<div class="two-columns-list">

### Input Types

- **`text`** - Single line text input
- **`textarea`** - Multi-line text input
- **`email`** - Email address input
- **`url`** - URL input
- **`number`** - Numeric input
- **`password`** - Password input
- **`checkbox`** - Single checkbox or multiple checkboxes
- **`radio`** - Radio button group
- **`select`** - Dropdown select
- **`color`** - Color picker
- **`date`** - Date picker
- **`range`** - Range slider

</div>

<div class="two-columns-list">

### Data Types

- **`string`** - Text data
- **`boolean`** - True/false values
- **`integer`** - Whole numbers
- **`number`** - Decimal numbers
- **`array`** - Array of values
- **`object`** - Object data

</div>

## Advanced Features

### Conditional Fields

You can disable fields based on conditions, such as based on other settings values, using `input_disabled`:

```php
$tainacan_settings->create_tainacan_setting([
    'id' => 'conditional_option',
    'title' => __('Conditional Option', 'your-plugin'),
    'section' => 'your_plugin_settings',
    'type' => 'string',
    'input_type' => 'text',
    'input_disabled' => get_option('tainacan_option_something', 'no'), // That 'something' option is set to no, so we don't want to enable changing this.
    'description' => __('This field is only editable by administrators.', 'your-plugin')
]);
```

### Forced Values

You can override user input with predefined values, such as when you want a constant to have priority, by combining `input_disabled` with `forced_value`:

```php
$tainacan_settings->create_tainacan_setting([
    'id' => 'readonly_option',
    'title' => __('Read-only Option', 'your-plugin'),
    'section' => 'your_plugin_settings',
    'type' => 'string',
    'input_type' => 'text',
    'input_disabled' => defined('MY_CONSTANT_WINS_OVER_USER'),
    'default' => defined('MY_CONSTANT_WINS_OVER_USER') ? MY_CONSTANT_WINS_OVER_USER : '',
    'forced_value' => defined('MY_CONSTANT_WINS_OVER_USER') ? MY_CONSTANT_WINS_OVER_USER : '',
    'description' => __('This option may be override by the system.', 'your-plugin')
]);
```

### Custom Sanitization

Use custom sanitization callbacks:

```php
$tainacan_settings->create_tainacan_setting([
    'id' => 'custom_sanitized_option',
    'title' => __('Custom Sanitized Option', 'your-plugin'),
    'section' => 'your_plugin_settings',
    'type' => 'string',
    'input_type' => 'text',
    'sanitize_callback' => function($input) {
        // Custom sanitization logic
        return strtoupper(sanitize_text_field($input));
    },
    'description' => __('This value will be converted to uppercase.', 'your-plugin')
]);
```

## Retrieving Settings

### Get Option Value

> **Important: All options registered via `create_tainacan_setting()` are automatically prefixed with `'tainacan_option_'` in the database.** 
> This means:
> - If you register an option with `'id' => 'my_setting'`
> - The actual database option, stored in `wp_options` table will be named `'tainacan_option_my_setting'`
> - You must use the full prefixed name when calling `get_option()`, `update_option()`, etc.

```php
// Get the option value (note the prefix!)
$api_endpoint = get_option('tainacan_option_custom_api_endpoint', 'default_value');

// Get with default fallback
$max_collections = get_option('tainacan_option_max_collections_per_page', 20);

// Get array option
$enabled_features = get_option('tainacan_option_enabled_features', []);
```

## Troubleshooting

### Common Issues

1. **Settings not appearing**: Ensure you're hooking into `admin_init` and using the correct page slug (`tainacan_settings`)

2. **Values not saving**: Check that your sanitization callback returns valid data. Remember, you have to call `get_option` prefixing the setting ID with `'tainacan_option_'` 

3. **UI not rendering**: Verify that the Tainacan Settings class is available before calling `create_tainacan_setting()`

4. **Permission errors**: Ensure users have the `manage_options` capability to access settings


## Conclusion

The Tainacan Settings system provides a powerful and flexible way to extend the Tainacan admin interface. By using `create_tainacan_setting()`, you get professional-looking settings forms with minimal code, while maintaining full compatibility with WordPress standards.

Key benefits:
- **Automatic UI generation** - No need to write HTML forms
- **Built-in validation** - WordPress sanitization functions included
- **Consistent styling** - Matches Tainacan's admin interface
- **Easy maintenance** - Centralized settings management

For more information, refer to the [WordPress Settings API documentation](https://developer.wordpress.org/plugins/settings/) and the [Tainacan core Settings class](https://github.com/tainacan/tainacan/blob/master/src/views/settings/class-tainacan-settings.php).