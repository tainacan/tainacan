# Registering Custom Vue Components

When authoring plugins for extending Tainacan functionalities, you may have to face some Vue.js code, which is the JS framework adopted by our community for implementing the Tainacan Admin Panel. Most of the time, you shall have the tools necessary for doing the work using the libraries that our plugin already includes. But in the situations where that is not enough, comes the need for registering **extra VueJS components**. Here a few examples:

* You may need a [Map component](https://github.com/vue-leaflet/Vue2Leaflet ':ignore') for creating a [new Metadata Type](/dev/creating-metadata-type.md) of geo-location;
* You may want to use a [Color Picker component](https://github.com/xiaokaike/vue-color ':ignore') for registering a [new Filter Type](/dev/creating-filters-type.md) based on colors, or even adding an extra form hook to increment your Collection settings;
* You may want some [fancy Gallery component](https://github.com/KitchenStories/vue-gallery-slideshow ':ignore') to create a new fullscreen [Extra View Mode](/dev/extra-view-modes);

We here take a look into the details of:

* How [Global components registration](#global-components-registration) to the Tainacan Vue Instance works.
* How to [register your custom component](#registering-your-our-custom-component).
* How to [register third party components](#registering-third-party-components).

## Global components registration

Every global component in our Vue.JS code is registered in the `/src/views/admin/js/main.js` file (for the Administrative panel) or inside the `/src/views/theme-search/theme-main.js` (for the bundle that renders the [Items List](/dev/the-vue-items-list-component.md)). These files have usually three sections:

1. The imports, that load libraries from the plugin bundle, such as `import VueMasonry from 'vue-masonry-css';` and `import FilterNumeric from '../../admin/components/filter-types/numeric/Numeric.vue';`;
2. The components and plugins registrations to the global Vue instance, such as `Vue.use(VueMasonry);` and `Vue.component('tainacan-filter-numeric', FilterNumeric);`;
3. The `new Vue(...)` instantiation, passing all the necessary settings.

Because of this process, tags such as `<masonry>` or `<component is="tainacan-filter-numeric">` are understood by the Vue components across the source code and the registered components are rendered. In order register our own components, we need to *hook into* the steps 1 and 2 described above. That is where the following routine (present on lines 73-78 `/src/views/admin/js/main.js` code and lines 46-51 of `/src/views/theme-search/theme-main.js`) comes in handy:

```javascript
/* Registers Extra Vue Components passed to the window.tainacan_extra_components  */
if (typeof window.tainacan_extra_components != "undefined") {
    for (let [extraVueComponentName, extraVueComponentObject] of Object.entries(window.tainacan_extra_components))
        Vue.component(extraVueComponentName, extraVueComponentObject);
}
```

You see, Vue Components can be exported as mere JS objects, containing their configuration. So here we're registering any components that are passed to this global array *window.tainacan_extra_components*. For adding any component to this array, we have a special Helper method, available for plugin developers: the `tainacan-register-vuejs-component()`. Let's see how to use it.

## Registering your our Custom Component

Consider the following PHP file, that shall be the main file of your plugin:

```php
<?php
/*
Plugin Name: Some Tainacan Plugin
Plugin URI: https://tainacan.org/new
Description: "Some Tainacan Plugin that registers a third party VueJS component."
Author: "Your Name Here"
Version: 0.1
Text Domain: some-tainacan-plugin
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/
add_action("tainacan-register-vuejs-component", "register_vuejs_component");

function register_vuejs_component($helper) {
	$handle = 'some-tainacan-plugin-component';
	$component_script_url = plugin_dir_url(__FILE__) . '/some-tainacan-plugin-component.js';

	$helper->register_vuejs_component($handle, $component_script_url);
}
?>
```

Simple as it looks, this plugin tells Tainacan where is the JS plugin code that should be load before the plugin `main.js` or `theme-main.js` scripts. Here is the content for `some-tainacan-plugin-component.js`:

```javascript
// Getting existing or creating the window.tainacan_extra_components object
var window.tainacan_extra_components = typeof window.tainacan_extra_components != "undefined" ? window.tainacan_extra_components : {};

// Creating your own VueJS component and passing it
const SomeTainacanPluginComponent = {
	name: "SomeTainacanPluginComponent",
	data: {
        // ...
    },
    methods: {
        // ...
    },
	template: `
     <!-- Here goes the template, including any components that we desire -->
	`
}
window.tainacan_extra_components["some-tainacan-plugin-component"] = SomeTainacanPluginComponent;
```

Thanks to the routine seen before the `<some-tainacan-plugin-component>` component shall be available. 

> [!WARNING]
> Warning: You MUST keep the `window.tainacan_extra_components` name, as it is the one used by the plugin to load custom components, and be careful to don't override it completely. Other plugins might have registered their components there too!

The good news is that, as the component is registered in the same *Vue Instance* of all the Tainacan bundle, any other globally registered plugin or component will be available. That means that, inside your *SomeTainacanPluginComponent* you have access to all of [Buefy](https://buefy.org/ ':ignore') components, to plugins such as [VTooltip](https://akryum.github.io/v-tooltip/#/ ':ignore'), [VueMasonry](https://github.com/paulcollett/vue-masonry-css ':ignore') and [VueDraggable](https://github.com/SortableJS/Vue.Draggable ':ignore'). You can even use some of our utility plugins made for Tainacan, such as the [UserPrefsPlugin](https://github.com/tainacan/tainacan/blob/develop/src/views/admin/js/utilities.js#L126 ':ignore'), which exposes methos for getting and setting user preferences ]]][UserCapabilitiesPlugin](https://github.com/tainacan/tainacan/blob/develop/src/views/admin/js/utilities.js#L346 ':ignore'), which helps us check user capabilities.

## Registering Third Party Components

That seems good, but what if you want some third party components? The process gets a bit more complicated depending on how the plugin is available. If you can't load its content from a CDN to just `require()` it in your JS, you will need some extra work to pass it to the global array. Ideally, it would be like this:

```javascript
// Getting existing or creating the window.tainacan_extra_components object
var window.tainacan_extra_components = window.tainacan_extra_components ? window.tainacan_extra_components : {};

// Importing and Passing a Third Party Component ...
import SomeThirdPartyComponent from 'some-third-party-component';
window.tainacan_extra_components["some-third-party-component"] = SomeThirdPartyComponent;
```

But the '*some-third-party-component*' should be available before, probably thanks to a `npm install some-third-party-component`. This requires our project to have a Bundler and Package Management files. While this can be done with many different libraries, we'll use NPM as *package manager* and Webpack as *bundler*. Your project should have a **package.json** file or you can create one by calling `npm init .`. Now open it and set up some basic dependencies:

```json
{
  "name": "third-party-component",
  "version": "1.0.0",
  "description": "",
  "scripts": {
    "build": "npx webpack some-plugin-component.js" // You can decide the build strategy that you prefer here
  },
  "author": "",
  "license": "ISC",
  "dependencies": {
    "some-third-party-component": "^0.1.0" // Any third party vue component that you wish
  },
  "devDependencies": {
	"@babel/core": "^7.8.4",
    "babel-loader": "^8.0.6",
    "webpack": "^4.41.6",
    "webpack-cli": "^3.3.11",
    "vue-loader": "^15.9.0",
    "vue-template-compiler": "^2.6.11",
    "webpack-dev-server": "^3.10.3"
  }
}
```

You can either run `npm install` to download the specified dependencies to the recently created `node_modules` folder or manually install one by one of these dependencies (passing `--save-dev` for the devDependencies). Notice that in our example we already installed the desired `some-third-party-component`, which should be de vue component of your preference.

Now, to be able to bundle the JS when running `npx webpack some-plugin-component.js` via `npm run build` we need to configure Webpack. That is usually done in a `webpack.config.js` file:

```js
let path = require('path');
let webpack = require('webpack');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

module.exports = {
    entry: './some-plugin-component.js',
	output: {
		path: path.resolve(__dirname, 'dist'),
		filename: 'some-plugin-component.bundle.js'
	}
	module:{
		rules: [
            {
                test: /\.vue$/,
                loader: 'vue-loader'
            },
            {
                test: /\.js$/,
                loader: 'babel-loader',
            }
		]
	},
	plugins: [
		new VueLoaderPlugin()
	]
}
```

The packages and loaders listed here are completely dependent on the component that you are trying to build. Some components may require, for example, some SASS or LESS loader.

Finally, after executing `npm run build` a `/dist/some-plugin-component.bundle.js` file will be created. It is this file that you now must reference as the `$component_script_url` on the `$helper->register_vuejs_component($handle, $component_script_url)` function.

## Wrapping up

To register Vue components to the same instance of Tainacan plugin, all you have to do is pass a Vue component object to the *window.tainacan_extra_components* global array. This can be referenced from a JS file set up in your plugin using the `tainacan-register-vuejs-component()` Helper method. For registering third party components, although, you need to handle how the import to your JS file will be bundled.

You can see more examples of using this strategy by checking the documentation of:

* How to create a [new Metadata Type](/dev/creating-metadata-type.md);
* How to create a [new Filter Type](/dev/creating-filters-type.md);
* How to create an [Extra View Mode](/dev/extra-view-modes);