# Admin Form Hooks

<div style="position: relative; z-index: 1;">

![Image of the collection form with extra options registered by the Tainacan Interface theme.](/_assets/images/admin-form-hooks-1.png ':size=450 :class=alignright')

</div>

Admin Form Hooks are a powerful tool for extenders who want to add functionality to *Tainacan entities*.

> [!NOTE]
> You may find a similar concept, the [Settings options](/dev/creating-options-in-the-settings-page.md), which are a way to add options in the Settings Page using WordPress Options API. That will create and store options available for the entire plugin, actually even for the entire site.

But when we talk about **Tainacan Admin Form Hooks**, we're dealing with storing data bind to one of *Tainacan entities*, that is storing new information about Collections, Taxonomies, Items, etc. The data will be available via the REST API of each entity and will be editable via their respective forms inside the Tainacan Admin panel, in a special gray box.

> A tipical use case for Admin Form Hooks is Theme Options. The **Tainacan Interface** theme, for example, registers extra options in the Collection form, to allow users to customize the Metadata Sections Layout and Header Banner Color of each Collection Page. **These are options that belong to each collection but are defined and consumed by the theme code**.

## Existing Form Hooks

Before we explain how to use, let us see which Form Hooks are available in the Tainacan Admin:

| Entity Name | Entity Form | Available Form Locations |
|-------------|-------------|---------------|
| Collection  | `collection` | `begin-left` `begin-right` `end-left` `end-right` |
| Item  | `item` | `begin-left` `begin-right` `end-left` `end-right`  |
| Metadata Section  | `metadataSection` | `begin-left` `end-left` |
| Metadatum  | `metadatum` | `begin-left` `end-left` |
| Filter  | `filter` | `begin-left` `end-left` |
| Taxonomy  | `taxonomy` | `begin-left` `end-left` |
| Taxonomy Term  | `term` | `begin-left` `end-left` |
| User Role  | `role` | `begin-left` `begin-right` `end-left` `end-right` |

The locations themselves are hard coded. For example, if you insert options in the Collection `begin-left` location, it will be between the Name and Description fields of the Collection form.

## How It Works

The Form Hooks system consists of four main components:

1. **Hook Registration** - Register your form hook with Tainacan
2. **Form Rendering** - Create the HTML form fields (values are auto-populated by the Admin UI)
3. **Data Saving** - Handle saving the custom data when the form is submitted
4. **API Integration** - Expose custom fields through the REST API

> **Important**: The form fields you create are rendered in the Tainacan Admin Vue.js interface. When editing existing entities, the Admin UI automatically populates your form fields with current values from the database. You don't need to manually retrieve or set form values in your HTML.

## Basic Implementation

### Step 1: Create Your Hook Class

```php
<?php
/**
 * Example: Adding a custom field to collections
 */
class My_Collection_Hooks {
    
    // Define your custom field meta key
    public $custom_field_key = 'my_custom_collection_field';
    
    public function __construct() {
        // Register the form hook
        add_action('tainacan-register-admin-hooks', array($this, 'register_hook'));
        
        // Save data when collection is created/updated
        add_action('tainacan-insert-tainacan-collection', array($this, 'save_data'));
        
        // Add custom field to REST API response
        add_filter('tainacan-api-response-collection-meta', array($this, 'add_meta_to_response'), 10, 2);
    }
    
    // Continue with other methods...
}
```
You may or may not prefer using a class-based approach for this. Just remember that in case you are not using classes, the reference for the function names will be direct, such as `add_action('tainacan-register-admin-hooks', 'register_hook');`. 

### Step 2: Register Your Form Hook

```php
public function register_hook() {
    if (function_exists('tainacan_register_admin_hook')) {
        tainacan_register_admin_hook(
            'collection',                    // Entity type: 'collection', 'item', 'taxonomy', etc.
            array($this, 'form'),           // Callback function that renders the form
            'end-right'                     // Position: 'end-right', 'end-left', 'begin-right', 'begin-left'
        );
    }
}
```
As long as Tainacan is activated, the `tainacan_register_admin_hook` should be publicly available. See bellow an advanced usage of this function with [conditionals](#conditional-hooks).

### Step 3: Create Your Form Fields

```php
<?php

public function form() {
    if (!function_exists('tainacan_get_api_postdata')) {
        return '';
    }
    
    ob_start();
    ?>
    <div class="field tainacan-collection--section-header">
        <h4><?php _e('My Custom Settings', 'my-plugin'); ?></h4>
        <hr>
    </div>
    
    <div class="field">
        <label class="label"><?php _e('Custom Field', 'my-plugin'); ?></label>
        <div class="control">
            <input 
                type="text" 
                name="<?php echo esc_attr($this->custom_field_key); ?>"
                class="input"
                placeholder="<?php esc_attr_e('Enter your custom value', 'my-plugin'); ?>"
            />
        </div>
        <p class="help"><?php _e('This is a custom field for collections.', 'my-plugin'); ?></p>
    </div>
    <?php
    
    return ob_get_clean();
}
```
It is not required, but a good and recommended practice to use this template with those classes.

When you add a header to the Admin Form Hook it makes clear to the user that those fields are there because of your plugin or theme and if it is disabled, the options will disappear. It also helps clarify that this fields may behave a bit different from the rest of the form (the Item form for example saves metadatum values while typing, which is not the case with extra fields).

On the other hand, using [Bulma](https://bulma.io/) classes such as  `field`, `control` and `input` you make your form feel more native to the Tainacan Admin UI.

> [!NOTE]
> One not such obvious thing to notice is that we're not defining the value of the `<input>` element. This is because **we have no access to the Collection object at this context**. This is merely an HTML template that will be passed to the Tainacan Admin Vue component, which will render this HTML and populate with the correct values based on the field `name` attribute.

### Step 4: Save Your Custom Data

```php
public function save_data($object) {
    if (!function_exists('tainacan_get_api_postdata')) {
        return;
    }
    
    $post = tainacan_get_api_postdata();
    
    // Check if user can edit this collection
    if ($object->can_edit() && isset($post->{$this->custom_field_key})) {
        update_post_meta(
            $object->get_id(), 
            $this->custom_field_key, 
            $post->{$this->custom_field_key}
        );
    }
}
```
You can decide where do you want to save the data. The most common approach is to use the `'tainacan-insert-tainacan-<entity-name>'` action to store the data as a `post_meta`. Despite the name, that action runs every time you create and update the entity. This is also where you may wish to perform some sanitization of the received value before passing it to the third parameter of `update_post_meta`. To learn more about WordPress Post Meta, take a look at [this article](https://developer.wordpress.org/plugins/metadata/managing-post-metadata/).

> [!NOTE]
> One example of a situation where you won't use `post_meta` is in the User Role form. As there is no standard table to store custom data about Roles you may define a `wp_option` with an array that contains a list of roles and store the new fields for your current saved role there. 

### Step 5: Expose Data Through REST API

```php
public function add_meta_to_response($extra_meta, $request) {
    $extra_meta[] = $this->custom_field_key;
    return $extra_meta;
}
```
This step may feel optional if you're not planning on fetching data from the REST API (you may just access things via `get_post_meta()`), but it is actually important to assure that the Tainacan Admin UI will be able to find your extra field when fetching the entity to populate the existing form. So don't skip it and make sure that you are pasing the field name just like you did in the previous steps!    

### Step 6: Initialize Your Hook

```php
// Initialize the hook class
new My_Collection_Hooks();
```
Finally if you are using a class-based approach you would initialize it here. It is also common to make this type of class a Singletone just to make sure that the code on it is not executed more than once.

## Complete Working Example

Here's a complete example that adds a "Collection Is Favorite" field to collections:

```php
<?php
/*
Plugin Name: Tainacan Collection Is Favorite Hook
Description: Adds a field to Tainacan Collections that allows to define them as Favorite.
Version: 1.0.0
*/

if (!defined('ABSPATH')) {
    exit;
}

class Tainacan_Collection_Favorite_Hook {
    
    public $favorite_field_key = 'collection_is_favorite';
    
    public function __construct() {
        add_action('tainacan-register-admin-hooks', array($this, 'register_hook'));
        add_action('tainacan-insert-tainacan-collection', array($this, 'save_data'));
        add_filter('tainacan-api-response-collection-meta', array($this, 'add_meta_to_response'), 10, 2);
    }
    
    public function register_hook() {
        if (function_exists('tainacan_register_admin_hook')) {
            tainacan_register_admin_hook(
                'collection',
                array($this, 'form'),
                'end-right'
            );
        }
    }
    
    public function form() {
        if (!function_exists('tainacan_get_api_postdata')) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="field tainacan-collection--section-header">
            <h4><?php _e('Collection Favorites', 'my-plugin'); ?></h4>
            <hr>
        </div>
        
        <div class="field">
            <label class="checkbox">
                <input 
                    type="checkbox" 
                    name="<?php echo esc_attr($this->favorite_field_key); ?>"
                />
                <?php _e('Mark this collection as favorite', 'my-plugin'); ?>
            </label>
            <p class="help"><?php _e('Favorite collections will be highlighted in the admin interface.', 'my-plugin'); ?></p>
        </div>
        
        <?php
        
        return ob_get_clean();
    }
    

    
    public function save_data($object) {
        if (!function_exists('tainacan_get_api_postdata')) {
            return;
        }
        
        $post = tainacan_get_api_postdata();
        
        if ($object->can_edit() && isset($post->{$this->favorite_field_key})) {
            update_post_meta(
                $object->get_id(),
                $this->favorite_field_key,
                $post->{$this->favorite_field_key}
            );
        }
    }
    
    public function add_meta_to_response($extra_meta, $request) {
        $extra_meta[] = $this->favorite_field_key;
        return $extra_meta;
    }
}

// Initialize the hook
new Tainacan_Collection_Favorite_Hook();
```

## Data Retrieval

### In Your Theme or Plugin
```php
// Get the custom field value
$is_favorite = get_post_meta($collection_id, 'collection_is_favorite', true);

// Use in your template
if (!empty($is_favorite)) {
    echo '<div class="favorite-badge">⭐ Favorite Collection</div>';
}
```
This obviously depends on where you stored the field in [Step 4](#step-4-save-your-custom-data), so take a look to make sure that you are looking on the right place.

## Advanced Features

### Conditional Hooks

The `tainacan_register_admin_hook` has a *4th* parameter, that allows you to define an array of conditionals. If the current entity does not match the criteria in the conditionals, the form won't be displayed.

For example, if you want to register a form hook that appear only for Relationship Metadata, you could do this:
```php
public function register_hook() {
    if (function_exists('tainacan_register_admin_hook')) {
        tainacan_register_admin_hook(
            'metadatum',                    // Entity type: 'collection', 'item', 'taxonomy', etc.
            array($this, 'form'),           // Callback function that renders the form
            'end-right'                     // Position: 'end-right', 'end-left', 'begin-right', 'begin-left',
            [ 'metadata_type' => 'Tainacan\Metadata_Types\Relationship' ] // Conditional: must be of this 'metadata_type'
        );
    }
}
```
To be sure of which fields are available, you should take a look at the API response of the desired entity.

> [!WARNING]
> Multiple conditionals are only available on Tainacan version >= `1.0.0`.

### Custom Styling

You can add custom `.css` (and even `.js`) using the `admin_enqueue_script` hook. Just remember to target the class `.form-hook-region` or, if you need more specific, the ID `#form-<entity>-<location>` (for example `#form-collection-end-right`).

```php
public function __construct() {
    // Add your existing hooks...
    
    // Enqueue custom CSS
    add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
}

public function enqueue_styles() {
    wp_enqueue_style(
        'my-hook-styles',
        plugin_dir_url(__FILE__) . 'css/hook-styles.css',
        array(),
        '1.0.0'
    );
}
```

## Troubleshooting

### Common Issues

1. **Form not appearing**: Ensure you're calling `tainacan_register_admin_hook()` correctly
2. **Data not saving**: Check if `tainacan_get_api_postdata()` is available
3. **Fields not in API**: Verify you're adding the field key to `add_meta_to_response()`
4. **Styling issues**: Check if your CSS is being loaded properly


## Conclusion

Tainacan Admin Form Hooks provide a powerful and flexible way to extend the admin interface. You can learn more about extending the Plugin by reading related topics:

- [Creating options in the Settings Page](/dev/creating-options-in-the-settings-page.md) - A guide for registering and displaying options inside the Tainacan Settings Page
- [Creating Tainacan Admin Pages](/dev/creating-tainacan-admin-pages.md) - A guide for creating your own Pages inside the Tainacan Admin
- [Creating a new Metadata Type](/dev/creating-metadata-type.md) - A guide for creating your custom Metadata Type
- [Creating a new Filters Type](/dev/creating-filters-type.md) - A guide for creating your custom Filters Type
