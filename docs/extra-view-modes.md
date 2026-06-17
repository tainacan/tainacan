# Extra View Modes

Extra View Modes are a way of creating your own templates for items list visualization. By default, Tainacan offers the following view modes:

- Table
- Cards
- Records
- Masonry
- Thumbnails (registered by the Tainacan Interface theme)
- Slideshow
- List

Each has it's specificities, but in case you're not satisfied with them, a developer can easily create a plugin to offer a custom template for displaying items list.

## Creating your extra view mode

As shown in [our post for extra view modes](http://tainacan.org/2018/06/13/custom-view-modes-how-will-the-world-see-your-collection/), we've created [a sample plugin](https://wordpress.org/plugins/tainacan-extra-view-modes/) with some inspirational ideas for custom view modes, such as this one:

![Image with an example of an extra view mode, showing borders around items thumbnail.](/_assets/images/Example_of_an_Extra_View_Mode.jpeg ':class=alignwide')

We here describe the process to create such a plugin. There are two strategies:

1. Using a [PHP template](#template-strategy) for creating the Items List;
2. Using a [Vue.js component](#component-strategy) for displaying the Items List;

The last one is more complex, but also gives you more customization and interaction options.

> [!WARNING]
> The following content can be better understood using the source code from two **sample projects that we have made [available here](https://github.com/tainacan/tainacan-extra-view-mode-sample ":ignore")**.

### Template Strategy

For registering via template, you will need three files:

1. The _.php_ file for registering the plugin and view mode;
2. The _.php_ file with the items list template;
3. The _.css_ file with the styling for your template;

#### Registering your plugin

As in any WordPress Plugin, you'll first need to create a php header as follows:

```php
<?php
/*
Plugin Name: Tainacan Extra View Mode Demo Template
Plugin URI: https://tainacan.org/
Description: Adds one extra viewmode to be used by your theme
Author: your-name-here
Version: 0.0.1
Text Domain: tainacan-extra-viewmode-demo-template
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/** Plugin version */
const TAINACAN_EXTRA_VIEWMODE_DEMO_TEMPLATE_PLUGIN_VERSION = '0.0.1';
```

Only Plugin Name is obligatory. Save the file with a name unique to your plugin (a good practice is to use the plugin name, such as `name-of-your-extra-view-mode-plugin.php`).

The rest of the file will contain the register function for your view mode. We discuss it in details ahead:

```php

/**
 * Here we register the new view modes using the Tainacan plugin
 * function 'tainacan_register_view_mode'. Checking if it exists also
 * is a way of making sure that the Tainacan itself is active.
 */
add_action( 'after_setup_theme', 'tainacan_extra_viewmode_template_demo_register_templates' );
function tainacan_extra_viewmode_template_demo_register_templates() {

	if ( function_exists( 'tainacan_register_view_mode' ) ) {

		// Registering the view modes
		tainacan_register_view_mode('demo-1', [
			'label' 			=> 'Demo 1',
			'description' 		=> __('A boring template demo view mode.', 'tainacan-extra-viewmode-template-demo'),
			'icon' 				=> '<span class="icon"><i><svg fill="var(--tainacan-info-color, #555758)" xmlns="http://www.w3.org/2000/svg" height="24" width="24" viewBox="0 0 46 46"><path d="..."/></svg></i></span>',
			'dynamic_metadata' 	=> false,
			'template' 			=> __DIR__ . '/templates/view-mode-demo-1.php',

		]);
	}
};

/**
 * Template view modes have their style separated from the php file
 * so we enqueue them here.
 */
add_action( 'wp_print_scripts', 'tainacan_extra_viewmode_template_demo_enqueue_styles' );
function tainacan_extra_viewmode_template_demo_enqueue_styles() {

	// Enqueue template view mode styles
	$baseurl =  plugins_url('', __FILE__);
	wp_enqueue_style( 'tainacan-extra-viewmodes-view-mode-demo-1', $baseurl . '/css/_view-mode-demo-1.css', [], TAINACAN_EXTRA_VIEWMODE_DEMO_TEMPLATE_PLUGIN_VERSION );
};

?>

> [!NOTE]
> /* End of file */
```

The function `tainacan_register_view_mode` is part of Tainacan's plugin. Its first parameter is a unique _slug_ that will be used to identify your view mode. Then follows an array of parameters:

| Type   | Name                | Description                                                                                                                                                                               | Default                                     |
| ------ | ------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------- |
| string | label               | Label, visible to users on the view modes dropdown.                                                                                                                                       | Same of the variable `$slug`                |
| string | icon                | HTML that outputs an icon that represents the view mode. Visible to users on view modes dropdown.                                                                                         | None                                        |
| string | description         | Description, visible only to editors in the admin.                                                                                                                                        | None                                        |
| string | type                | Type. Accepted values are 'template' or 'component'.                                                                                                                                      | _template_                                  |
| string | template            | Full path to the template file to be used. Required if $type is set to template.                                                                                                          | _theme-path/tainacan/view-mode-`$slug`.php_ |
| string | component           | Component tag name. The web component js must be included and must accept two props: 1) items: the list of items to be rendered; 2) displayed-metadata: list of metadata to be displayed; | _view-mode-`$slug`_                         |
| string | thumbnail           | Full URL to a thumbnail that represents the view mode. Displayed only in Admin.                                                                                                           | None                                        |
| string | skeleton_template   | HTML that outputs a preview of the items to be loaded, such as gray blocks, mimicking the shape of the items.                                                                             | None                                        |
| bool   | show_pagination     | Whether to display or not pagination controls.                                                                                                                                            | `true`                                      |
| bool   | full_screen         | Whether the view mode will display full screen or not.                                                                                                                                    | `false`                                     |
| bool   | dynamic_metadata    | Whether to display or not (and use or not) the "displayed metadata" selector.                                                                                                             | `false`                                     |
| bool   | implements_skeleton | Whether the view modes take care of showing it's own Skeleton/Ghost css classes for loading items.                                                                                        | `false`                                     |

The `type` parameter is one of the most relevant here. When passing a template, the file path should be provided.


![Enabled Metadata Dropdown](/_assets/images/Enabled_Metadata_Dropdown.png ':class=alignright')

View modes as Cards and Grid do not allow users to choose which metadata should be displayed, but rather decide that only certain will be visible. For this kind of view mode, it is used the `dynamic_metadata` parameter as `false`.

By default, a spinning circle loading animation is shown when items are being fetched. Most of our official view modes override this by implementing a so-called skeleton/ghost list, that mimics a list of gray blocks in the shape of items before they arrived. If your view mode does that, you should set `implements_skeleton` to true and provide an HTML template with such skeleton to `skeleton_template`. Tainacan will take care of rendering this skeleton while items are being loaded.

### Making an extra view mode template

The file path indicated on the register above should point to the .php file where your template lives. It will probably have some structure similar to the following sample:

```php
<?php if (have_posts()):  ?>

    <div class="tainacan-demo-1-container">

        <?php while (have_posts()): the_post(); ?>

                <div class="tainacan-demo-1">
                    <a href="<?php the_permalink(); ?>">
                        <?php if ( has_post_thumbnail() ): ?>
                            <?php the_post_thumbnail('tainacan-medium-full'); ?>
                        <?php else: ?>
                            <?php echo '<img alt="Thumbnail placeholder" src="' . plugins_url('', __FILE__ ) . '/thumbnail_placeholder.png">'?>
                        <?php endif; ?>
                    </a>
                    <a href="<?php the_permalink(); ?>">
                        <p class="metadata-title"><?php the_title(); ?></p>
                    </a>
                </div>

        <?php endwhile; ?>

    </div>

<?php else: ?>
    <div class="tainacan-demo-1-container">
        <section class="section">
            <div class="content has-text-gray4 has-text-centered">
                <p>
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-48px tainacan-icon-items"></i>
                    </span>
                </p>
                <p><?php echo__('No item found', 'tainacan-extra-viewmode-template-demo') ?></p>
            </div>
        </section>
    </div>
<?php endif; ?>
```

The classes `tainacan-demo-1-container` and `tainacan-demo-1`, and so forth should be implemented by you on your style file. Other classes as the `skeleton`, `section` are part of Tainacan's plugin CSS, and can be used if you wish to keep a standard.

Thumbnail is obtained via the function `the_post_thumbnail()`, which accepts as first parameter any of the following:

- `tainacan-small` (40px width, 40px height, cropped);
- `tainacan-medium` (275px width, 275 height, cropped);
- `tainacan-medium-full`, (max. 205px width, max. 1500px height, not cropped );

We are also loading a _placeholder_ image, stored inside the plugin folder, to display in case there is no thumbnail for that item.

You can see that this view mode only uses the Title metadatum. If you want to render the other metadata, you can use the `tainacan_the_metadata()` helper function.

### Component Strategy

The component strategy requires at least two files:

1. The _.php_ file for registering the plugin, the view mode and the component;
2. The _.js_ file with the definition and registration of the _vuejs_ component;

But, due to the advantages of having a separate file for the vue component that would need to be bundled into pure javascript, we often face the following configuration:

1. The _.php_ file for registering the plugin, the view mode and the component;
2. The _.js_ source file with the registration of the component;
3. The _.bundle.js_ bundled file with the registration of the component;
4. The _.vue_ source file, with the list template and any logic or style;
5. The _.css_ file, with the component style in case you don't use it inside the vue file;
6. The _.package.json_ file for handling dependencies and third party npm libraries;
7. The _.webpack.config.js_ for defining bundle strategy.

Let us take this step by step:

```php
<?php
/*
Plugin Name: Tainacan Extra View Mode Component Demo
Plugin URI: https://tainacan.org/new
Description: Add one extra viewmode to be used by your theme
Author: your-name-here
Version: 0.0.1
Text Domain: tainacan-extra-viewmode-component-demo
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/** Plugin version */
const TAINACAN_EXTRA_VIEWMODES_COMPONENT_DEMO_PLUGIN_VERSION = '0.0.1';

/**
 * View modes that are made of Vuejs components instead of php
 * templates have to be registered first on the plugin
 */
add_action("tainacan-register-vuejs-component", "tainacan_extra_viewmode_component_demo_register_components");
function tainacan_extra_viewmode_component_demo_register_components($helper) {

    if ( function_exists( 'tainacan_register_view_mode' ) ) {

		// Enqueues necessary third party or modified libraries to this view mode
		$baseurl =  plugin_dir_url(__FILE__);

        // Registering the Vue Component
        $handle = 'tainacan-demo-2-viewmode';
        $component_script_url = $baseurl . 'components/demo-2-view-mode.bundle.js';
		$helper->register_vuejs_component($handle, $component_script_url, [ 'public' => true, 'deps' => ['wp-i18n'] ], null, true);

		// Registering the view mode
        tainacan_register_view_mode('demo-2', [
            'label' 				=> 'Demo 2',
			'description' 			=> __('A boring component demo view mode.', 'tainacan-extra-viewmode-component-demo'),
			'icon' 					=> '<span class="icon"><i><svg fill="var(--tainacan-info-color, #555758)" xmlns="http://www.w3.org/2000/svg" height="24" width="24"><path d="..."/></svg></i></span>',
            'type' 					=> 'component',
			'component' 			=> 'view-mode-demo-2',
			'dynamic_metadata' 		=> true,
			'implements_skeleton' 	=> true
        ]);
    }
}

/**
 * Template view modes have their style separated from the php file
 * so we enqueue them here.
 */
add_action( 'wp_print_scripts', 'tainacan_extra_viewmode_component_demo_enqueue_styles' );
function tainacan_extra_viewmode_component_demo_enqueue_styles() {

	// Enqueue template view mode styles
	$baseurl =  plugins_url('', __FILE__);
	wp_enqueue_style( 'tainacan-extra-viewmodes-view-mode-demo-2', $baseurl . '/css/_view-mode-demo-2.css', [], TAINACAN_EXTRA_VIEWMODES_COMPONENT_DEMO_PLUGIN_VERSION );

};

?>
```

The register function is slightly more complicated. Here we need to reference the bundled _.js_ using the `register_vuejs_component`. This function is a wrapper to the `wp_enqueue_script()` that is inside the Tainacan plugin. That is why you can also pass dependencies like the `wp-i18n` package, which will give us access to WordPress translation functions on the client side.

Our component tag name is passed to the `'component'` options of the register function, and we also pass `'type' => 'component'`. Now, if you are familiar with vue js components, you know that they need to be registered on the `Vue` instance, which in this case is declared by the Tainacan plugin itself. So how does that work? Let us look inside the main `.js` file, which in our code is named `demo-2-view-mode.js`:

```js
import ViewModeDemo2 from "./demo-2-view-mode.vue";

window.tainacan_extra_components =
  typeof window.tainacan_extra_components != "undefined"
    ? window.tainacan_extra_components
    : {};
window.tainacan_extra_components["view-mode-demo-2"] = ViewModeDemo2;
```

This simple file is accessing the `window.tainacan_extra_components` and telling which is the code for the view mode component of tag `view-mode-demo-2`, so that the `Vue` instance inside the Tainacan plugin can access and register it on the client side as well.

> [!WARNING]
> You MUST keep the `window.tainacan_extra_components` name, as it is the one used by the plugin to load custom components, and be careful to don't override it completely. Other plugins might have registered their components there too!

The component itself is imported from the `.vue` file, that we show bellow:

```vue
<template>
  <div class="demo-2-view-mode grid-demo-2">
    <!-- Empty result placeholder -->
    <section v-if="!isLoading && items.length <= 0" class="section">
      <div class="content has-text-gray4 has-text-centered">
        <p>
          <span class="icon is-large">
            <i class="tainacan-icon tainacan-icon-36px tainacan-icon-items" />
          </span>
        </p>
        <p>
          {{ __("No item found", "tainacan-extra-viewmode-component-demo") }}
        </p>
      </div>
    </section>

    <!-- SKELETON LOADING -->
    <ul v-if="isLoading" class="grid tainacan-masonry-container">
      <li
        :key="item"
        v-for="item in 12"
        :style="{ 'min-height': randomHeightForMasonryItem() + 'px' }"
        class="skeleton tainacan-masonry-item"
      />
    </ul>

    <ul v-if="!isLoading && items.length > 0" class="grid">
      <li v-for="(item, index) of items" :key="index">
        <a target="_blank" :href="getItemLink(item.url, index)">
          <figure>
            <img
              :alt="
                item.thumbnail_alt
                  ? item.thumbnail_alt
                  : $i18n.get('label_thumbnail')
              "
              v-if="item.thumbnail != undefined"
              :src="
                item['thumbnail']['tainacan-medium-full']
                  ? item['thumbnail']['tainacan-medium-full'][0]
                  : item['thumbnail'].medium
                  ? item['thumbnail'].medium[0]
                  : thumbPlaceholderPath
              "
            />
            <figcaption>
              <h3>
                <span
                  v-for="(column, metadatumIndex) in displayedMetadata"
                  :key="metadatumIndex"
                  v-if="
                    column.display &&
                    column.metadata_type_object != undefined &&
                    column.metadata_type_object.related_mapped_prop == 'title'
                  "
                  v-html="
                    item.metadata != undefined && collectionId
                      ? renderMetadata(item.metadata, column)
                      : item.title
                      ? item.title
                      : `<span class='has-text-gray3 is-italic'>` +
                        $i18n.get('label_value_not_provided') +
                        `</span>`
                  "
                />
              </h3>
              <a
                v-if="isSlideshowViewModeEnabled"
                @click.prevent="starSlideshowFromHere(index)"
              >
                <span class="icon slideshow-icon">
                  <i
                    class="tainacan-icon tainacan-icon-1-125em tainacan-icon-viewgallery"
                  />
                </span>
              </a>
            </figcaption>
          </figure>
        </a>
      </li>
    </ul>
  </div>
</template>

<script>
import qs from "qs";

export default {
  name: "ViewModeDemo2",
  data() {
    return {
      thumbPlaceholderPath:
        tainacan_plugin.base_url + "/assets/images/placeholder_square.png",
      isSlideshowViewModeEnabled: false,
    };
  },
  props: {
    collectionId: Number,
    displayedMetadata: Array,
    items: {
      type: Array,
      default: () => [],
      required: true,
    },
    isLoading: false,
    totalItems: Number,
    isFiltersMenuCompressed: Boolean,
    enabledViewModes: Array,
  },
  computed: {
    __() {
      return wp.i18n ? wp.i18n.__ : (str, ctx) => str;
    },
    queries() {
      let currentQueries = JSON.parse(JSON.stringify(this.$route.query));
      if (currentQueries) {
        delete currentQueries["view_mode"];
        delete currentQueries["fetch_only"];
        delete currentQueries["fetch_only_meta"];
      }
      return currentQueries;
    },
  },
  mounted() {
    console.log(this.items);
    this.isSlideshowViewModeEnabled =
      this.enabledViewModes.findIndex((viewMode) => viewMode == "slideshow") >=
      0;
  },
  methods: {
    getItemLink(itemUrl, index) {
      if (this.queries) {
        // Inserts information necessary for item by item navigation on single pages
        this.queries["pos"] =
          (this.queries["paged"] - 1) * this.queries["perpage"] + index;
        this.queries["source_list"] = this.$root.termId
          ? "term"
          : !this.$root.collectionId || this.$root.collectionId == "default"
          ? "repository"
          : "collection";
        this.queries["ref"] = this.$route.path;
        return itemUrl + "?" + qs.stringify(this.queries);
      }
      return itemUrl;
    },
    renderMetadata(itemMetadata, metadatum) {
      let metadata =
        itemMetadata && itemMetadata[metadatum.slug] != undefined
          ? itemMetadata[metadatum.slug]
          : false;

      if (!metadata) return "";
      else return metadata.value_as_html;
    },
    randomHeightForMasonryItem() {
      let min = 120;
      let max = 380;

      return Math.floor(Math.random() * (max - min + 1) + min);
    },
    starSlideshowFromHere(index) {
      this.$router
        .replace({
          query: { ...this.$route.query, ...{ "slideshow-from": index } },
        })
        .catch((error) => this.$console.log(error));
    },
  },
};
</script>
```

Notice that this component handles much more features than the template version of the view mode. We here access props such as `displayedMetadata`, `isLoading`, `isFiltersMenuCompressed`, etc... But the most important remains being the `items` array, which you will find a way to render and later style with css.

Finally, we have to bundle the main _.js_. For that, we have two important files. First, the _package.json_, which contains our dependencies and defined the build command:

```json
{
  "name": "tainacan-extra-viewmode-component-demo",
  "version": "0.0.1",
  "description": "",
  "scripts": {
    "build": "npx webpack demo-2-view-mode.js"
  },
  "author": "",
  "license": "ISC",
  "dependencies": {
    "qs": "^6.9.6"
  },
  "devDependencies": {
    "@babel/core": "^7.8.4",
    "babel-loader": "^8.0.6",
    "vue-loader": "^15.9.0",
    "vue-template-compiler": "^2.6.11",
    "webpack": "^4.44.2",
    "webpack-cli": "^3.3.12",
    "webpack-dev-server": "^3.10.3"
  }
}
```

Depending on your view mode, you will want more third party npm packages that can be listed here, and installed via `npm install`. Notice that we are defining the build script and passing the main `.js` file there.

At last, we use webpack to configure our loaders and compilers:

```js
let path = require("path");
let webpack = require("webpack");
const VueLoaderPlugin = require("vue-loader/lib/plugin");

module.exports = {
  output: {
    path: path.resolve(__dirname, ""),
    filename: "demo-2-view-mode.bundle.js",
  },
  module: {
    rules: [
      {
        test: /\.vue$/,
        loader: "vue-loader",
      },
      {
        test: /\.js$/,
        loader: "babel-loader",
      },
    ],
  },
  node: {
    fs: "empty",
  },
  plugins: [new VueLoaderPlugin()],
};
```

Here it is defined the bundled file name, which is called in our registration _.php_ file.

In our sample repository, we also have a `build.sh` script, that takes care of calling the install and build process, besides moving the necessary files to your plugin directory if you wish. Take a look there to learn more ;)

## Conclusion

There are lots of things that you can do to improve your items list visualization. Hopefully our [sample files](https://github.com/tainacan/tainacan-extra-view-mode-samples ":ignore") and our [demo plugin](https://wordpress.org/plugins/tainacan-extra-view-modes/ ":ignore") will set you up for creating your own. Feel free to reach us in the [Tainacan Discourse Forum](https://tainacan.discourse.group ":ignore") to discuss any issues!
