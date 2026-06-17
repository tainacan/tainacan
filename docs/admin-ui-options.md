# Admin UI Options

Since version `0.19`, Tainacan offers a series of variables that can help you customize the admin panel. From Tainacan `1.0.0`, the majority of these variables [are tweakable in a *per-user-role* basis in the User Role Editing Form](admin-appearance.md). They do mostly hiding elements, controlling certain default features (such as the presence of the WordPress Admin menu) and dashboard cards.

As a developer, you can also pass them directly to the admin URL as query params, but the ideal way is to use a filter for it. For example:

```php
/*
 * Sends params to the Tainacan Admin Options
 */
function my_plugin_tainacan_admin_options($options) {

	$my_admin_options = [
        'forceFullscreenAdminMode' => true,
        'itemEditionPublicationSectionInsideTabs' => true,
        'showOnlyCollectionCardsThatUserCanEdit' => true
    ];
	$options = array_merge($options, $my_admin_options);

	return $options;
};
add_filter('tainacan-admin-ui-options', 'my_plugin_tainacan_admin_options');
```

The same could be achieved for prototyping by accessing your admin URL like this:
`https://<your-site.com>/wp-admin/?forceFullscreenAdminMode=true&itemEditionPublicationSectionInsideTabs=true&showOnlyCollectionCardsThatUserCanEdit=true&page=tainacan_admin#/collections/`

If you want to add and extra option and expose it via UI, so that it can be defined in the [User Role Editing Form](admin-appearance.md), you can use the `tainacan-available-admin-ui-options` filter:

```php
/*
 * Insert and extra myAdminUIOption option inside the User Role Editing form.
 */ 
function my_plugin_tainacan_available_admin_options($tainacan_available_admin_ui_options) {
    $tainacan_available_admin_ui_options['navigation']['items']['myAdminUIOption'] = __('My Admin UI Option', 'my-plugin-slug');
    return $tainacan_available_admin_ui_options;
}
add_filter('tainacan-available-admin-ui-options', 'my_plugin_tainacan_available_admin_options');
```

Notice that the `key => value` option will be the `slug => label` of the option, no value is defined here, since the default is `false` for every option. We're also inserting the option inside the navigation section. You can define new sections by giving them a slug (the array key) and an object with `label`, `description` and of course, an array of `items`.

Later if you want to consume this new option you can call, from inside any page that inherits `\Tainacan\Pages`:

```php
$this->has_admin_ui_option('myAdminUIOption')
```

You can read about an explanation of the available options in the [Admin appearance page](admin-appearance.md). For a complete list of their IDs, check the code reference of the [Admin_UI_Options class](/dev/phpdoc/classes/Tainacan/Traits/Admin_UI_Options.md).