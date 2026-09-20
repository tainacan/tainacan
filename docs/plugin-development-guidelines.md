# Guidelines for Developing Plugins for Tainacan

This document aims to offer a set of suggestions for developers interested in creating a plugin to integrate or expand the Tainacan plugin. It is a map of *when* and *where* to extend Tainacan, not a tutorial. The pages linked below cover the actual APIs.

A working starting point is the [Tainacan Sample Plugin](https://github.com/tainacan/tainacan-sample-plugin ":ignore"), which shows admin pages, `Requires Plugins: tainacan`, and a `wp-scripts` build.

## Core vs. Plugin

The first thing to have in mind is: *should I really be developing a new plugin?*

AI tools have made it easier than ever to generate working code quickly, at least at first glance, and creating a new WordPress plugin may be much easier than opening a pull request and waiting for others to review your idea. But sometimes, a small and focused feature may have its place on what we call “Tainacan Core”. It may even take time to be released, but if it is something that you feel that shall be useful for the majority of the Tainacan users and that won’t add a lot of maintenance burden, then it might be worth discussing it with the core maintainers. It may even be your first opportunity to become a contributor to the code! See our [Contributing Guidelines](/dev/CONTRIBUTING.md).

But there are of course situations where a separate plugin is more appropriate. Usually it is the case when:

- Your feature is too specific for your institution/client;
- You depend on some third party API or library, especially paid ones;
- Your feature will only work with your data, for example metadata or taxonomies settings;
- Your feature may have a technical complexity that won’t be feasible for most server settings (consider WordPress hosting environments are highly diverse and may be more limited than your local or institutional infrastructure);
- Your feature is too dependent on specific theme settings;
- Your plugin is not really much about Tainacan; it is a broader topic related to WordPress overall.

## General Best Practices

No matter what you are building for Tainacan, from day one, you are building a WordPress plugin. The [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/ ":ignore") is therefore **essential** reading. You should follow its recommendations. If you ever plan on releasing this plugin in the WordPress.org plugins repository, it is even more critical to follow these guidelines.

We add to these our own recommendations:

- **Namespacing and plugin dependency**: we usually prefix Tainacan plugins with `tainacan-your-plugin-slug`. We tend to use that order for plugin name, plugin folder and plugin text domain. It makes the relationship with Tainacan obvious and helps avoid collisions. Prefix your functions with `tainacan_your_plugin_slug_` (PHP) or `TainacanYourPluginSlug` (JS) for the same reason.

  In your **main plugin file** header, don’t forget to add `Requires Plugins: tainacan`. This way people won’t be able to uninstall Tainacan if your plugin is activated, or try to activate your plugin without Tainacan.

  `Requires Plugins` (and the `tainacan-` folder prefix) do **not** guarantee load order. WordPress still loads plugins from the `active_plugins` list, which is sorted alphabetically, so `tainacan-your-plugin` can load *before* `tainacan/tainacan.php`. Hook into `plugins_loaded` and check `defined('TAINACAN_VERSION')` (or `class_exists()` for a specific Tainacan class) before calling Tainacan APIs.

- **Internationalization**: unless you don’t plan on releasing the plugin for the WordPress repository and really don’t see use of your plugin for other locales, write in English and always wrap user-facing strings in translation functions. Tainacan is written in English, even though most of our developers are Brazilians.

- **Get to know Tainacan APIs**: there are some Tainacan APIs mentioned ahead in this document that are basically wrappers around WordPress APIs. Many things that you might be tempted to write a script to change may be tweakable via existing [Hooks](/dev/hooks.md), for example.

- **Respect capabilities**: Tainacan adds its own [roles and capabilities](/dev/roles-capabilities.md) on top of WordPress. Any write operation (REST, AJAX, form save) should check the relevant capability *and* a nonce. Do not assume that being in `wp-admin` is enough.

- **Be careful with data storage**: if you plan on storing some data, think twice before creating a new database table! You might be writing a lot of insecure, heavy code for something that can already be stored in existing, appropriate tables of the [WordPress database](https://drawsql.app/templates/wordpress ":ignore"), such as `wp_options`, `wp_usermeta`, `wp_postmeta`, etc. If you reuse these tables, you’ll be gaining ready-to-use caching solutions, API methods for querying them, maybe even automatic integration with WordPress plugins like backup solutions.

- **Assets management**: avoid loading scripts and styles globally when they are only needed inside Tainacan interfaces. Your plugin should not be enqueuing CSS and JS outside of its own context.

- **Use modern tooling**: when building WordPress plugins, it is always nice to keep an eye on recent APIs. Despite being known for its long-time support, WordPress is very active and there have been several new APIs in recent versions. This means that things that used to be done in a certain way may change in the future, and it is always a good idea to be up to date. For example, not everything in WordPress nowadays is PHP. There are several React components being developed for the Admin Modernization that is gradually happening in every release. Also check modern development tooling like the [wp-scripts package](https://developer.wordpress.org/block-editor/getting-started/devenv/get-started-with-wp-scripts/ ":ignore") and [wp-env](https://developer.wordpress.org/block-editor/getting-started/devenv/get-started-with-wp-env/ ":ignore").

- **Plugin Check**: another recommendation is to use [Plugin Check](https://wordpress.org/plugins/plugin-check/ ":ignore") to see if your code is following the best security, accessibility, *etc* practices. It is also available as a [CLI command](https://github.com/WordPress/plugin-check/blob/trunk/docs/CLI.md ":ignore").

## UI for Storing General Plugin Options

If your plugin is going to store some option that has a plugin-wide effect, you are possibly looking for something that can be stored as a WordPress site-wide option. These are usually stored in WordPress `wp_options` table and manipulated either in the site general Settings page or a plugin dedicated settings page.

In that case, read: [Creating options in the Settings Page](/dev/creating-options-in-the-settings-page.md) — a guide for registering and displaying options inside the Tainacan Settings Page. This way your option setting UI will feel more native and be in a place where Tainacan administrators are more familiar.

Typical use cases for this type of setting might be:

- Storing an API URL and Key (Tainacan does it with ReCaptcha, for example);
- Numeric values that define limits such as maximum items per request;
- Booleans that define which option to use among a set of behaviors that the plugin may have.

However, it might be the case where your plugin has too many settings, too many information, and maybe even require your own small inner navigation. If that is the case and you feel that your feature deserves a dedicated page, read: [Creating Tainacan Admin Pages](/dev/creating-tainacan-admin-pages.md) — a guide for creating your own Pages inside the Tainacan Admin.

## Programmatically Manipulating Tainacan Data

When your plugin is to edit data that already exists in Tainacan, you usually take one of the following:

1. Use the internal [Tainacan PHP API](/dev/internal-api.md) methods for manipulating data. The flow usually starts by accessing one repository singleton instance and invoking its getter or setter methods. Care must be taken here to work with try-catches, knowing the Entity methods and sanitizing data.

2. Use calls to the [Tainacan REST API](https://redocly.github.io/redoc/?url=https://raw.githubusercontent.com/tainacan/tainacan/refs/heads/develop/docs/openapi.json ":ignore"). The entire Tainacan Admin UI is a Single Page Application so most of what you can do there can be done from your code, as long as appropriate authentication headers are passed.

## Creating Fields on Existing Tainacan Data

If your setting is directly related to a collection, item or metadata (basically anything that we call a Tainacan Entity — the things that you manage in your repository), then storing in `wp_options` is not a good idea. The user might create and edit those entities, and you’ll end up having to store an array of options, besides building a UI to load and select them... it is not ideal. If there is a setting that changes for each entity, then it should be stored in that entity. This usually means storing in `wp_postmeta`, but besides that you’ll need to add a new field in the respective form inside the Tainacan UI. For that, take a read on: [Using Admin Form Hooks](/dev/admin-form-hooks.md) — how to insert extra options to entity forms such as collection and items forms. This is the same even if you want to add things that won’t change that entity but should appear in the form.

Common cases for that include:

- Adding an extra field in the Collection form, to help classify collections by some criteria or define if a certain feature is enabled for it;
- Adding an extra field on an item, maybe something that is not manually edited by the user as metadata do, but derived by something that the plugin provides;
- Adding an extra option in the metadata options form, so you can later query that and customize the appearance of the metadata value on the theme-side.

There are more complex topics that may be plugin territory. These are rare but have dedicated APIs for developers to expand Tainacan existing functionalities:

- [Creating a new Metadata Type](/dev/creating-metadata-type.md) — a guide for creating your custom Metadata Type.
- [Creating a new Filter Type](/dev/creating-filters-type.md) — a guide for creating your custom Filter Type.
- How to create your own [Exporters](/dev/exporter-flow.md), [Importers](/dev/importer-flow.md) and [Exposers](/dev/exposers.md).

One thing to mention is that Tainacan does not have a dedicated UI to edit User Data. There is the User Role form, which extension is mentioned in the aforementioned Admin Form Hooks documentation. But if you want to store an option that is singular for each user, then you must extend the existing WordPress User Editing Form, using existing WordPress functions for that.

## Advanced Tweaks

When developing plugins that integrate with Tainacan, you may find quick ways to do some things that seem to work well, but that may have more appropriate flows. Here are some cases:

- [Registering New Vue Components](/dev/registering-custom-vue-components.md) — if you are building client-side UI, you may want to register new Vue components that can be used by your plugin, such as metadata and filter types or [extra view modes](/dev/extra-view-modes.md). These will allow you to reuse some of the same [Buefy](https://buefy.org/ ":ignore") components (Buefy is the Vue/Bulma component framework historically used by Tainacan), already gaining styles, making your plugin feel even more native and avoid building your own logic for that. Think of Autocompletes, Taginputs, Modal and Dialogs, for example.

- [Using React Selection Components](/dev/react-selection-modules.md) — if your plugin has complex flows where an item selection or filtering is needed, then you might need a UI with built-in filtering, pagination, etc. Tainacan itself already has this made for its Gutenberg blocks, which are made in React. Luckily, you can use these React selection components in your plugin to offer those advanced items selection flows.

- [Tweaking the Admin UI](/dev/admin-ui-options.md) — hide or force admin-interface behaviors per user role, without forking Tainacan templates.

- [Reloading the Item Editing Form](/dev/reloading-the-item-editing-form.md) — if your plugin updates an item or its metadata via the REST or PHP API and the user needs on-screen feedback, dispatch the `TainacanReloadItemMetadataForm` window event instead of manipulating the form DOM.

## AI-Assisted Development

If you are using LLMs or AI coding assistants:

- Prefer prompting the model with official WordPress and Tainacan APIs instead of asking for generic PHP solutions. If you are using Cursor, how about checking [these rules](https://cursor.directory/plugins/wordpress ":ignore")?;
- Explicitly mention whether the solution should use hooks, repositories, REST API calls, Vue components, or React components;
- Review generated code carefully for security, sanitization, nonce validation, and capability checks;
- Avoid accepting generated code that directly manipulates Tainacan database tables without using APIs, or even JavaScript code that uses hacky ways to manipulate the UI.
