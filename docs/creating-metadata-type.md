# Creating a Custom Metadata Type

Metadata types are the objects that represent the types of metadata that can be used. Examples of Metadata Types are "Text", "Long text", "Date", "Relationship with another item", etc. Each metadata type object has its settings and web component that will be used to render the interface.

Developers can create custom Metadata Types via plugins starting from Tainacan 0.16. This article shows how to do this, divided in the following sections:

- [Registering](#registering-your-metadata-type) your Metadata Type
- Creating the [PHP Class](#creating-the-php-class)
- Creating the [Vue Web Component](#creating-vue-web-component)
- Creating Vue Web Component for the [Metadata Form](#creating-vue-web-component-for-the-metadata-form)
- Advanced usage of third party [Vue Components](#advanced-usage-of-vue-components)

> If you want to play with the sample used here, it is all available in [this Github repo](https://github.com/tainacan/custom-metadata-type-samples ":ignore") :wink:

## Registering your Metadata Type

First of all, you have to register your Metadata Type class. You do this by calling the `register_metadata_type` method of the `Metadata Type Helper`, available if the Tainacan plugin is installed. For the simplicity of this article, let us suppose that you are creating a custom version of the existing _Numeric_ Metadata Type, which you'll call "Custom Metadata Type". You might have the main plugin file, named `custom-metadata-type.php` with the following content:

```php
<?php
/*
Plugin Name: Custom Metadata Type
Plugin URI: https://tainacan.org/new
Description: "A Custom Metadata Type for Tainacan, that does pretty much what the Numeric metadata type does"
Author: "Your Name Here"
Version: 0.1
Text Domain: custom-metadata-type
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/
add_action("tainacan-register-metadata-type", "register_metadata_type");
function register_metadata_type($helper) {

    // Registering the Class
    require_once( plugin_dir_path(__FILE__) . 'metadata_type/metadata-type.php' );

    // Registering the Vue Component
    $handle = 'custom-metadata-type';
    $class_name = 'Custom_Metadata_Type';
    $metadata_script_url = plugin_dir_url(__FILE__) . 'metadata_type/metadata-type.js';
    $helper->register_metadata_type($handle, $class_name, $metadata_script_url);
}

add_action("tainacan-register-vuejs-component", "register_metadata_type_form");
function register_metadata_type_form($helper) {

    // Registering the Vue Component for the Metadata Options Form
    $handle2 = 'custom-metadata-type-form';
    $component_script_url = plugin_dir_url(__FILE__) . 'metadata_type/metadata-type-form.js';
    $helper->register_vuejs_component($handle2, $component_script_url);
}
?>
```

You can see that we have a folder named "metadata_type" to keep the class and component separate, which is up to you as you prefer to organize the plugin file structure. Now we'll understand better what each of the registered parts means.

## Creating the PHP Class

Now you have to create the class you just registered, and use its constructor to set the basic features of your Metadata Type. Let's go slowly by checking the content of `metadata_type/metadata-type.php`:

```php
<?php

/**
 * Class Custom_Metadata_Type
 * This is inside "metadata_type/metadata-type.php"
 */
class Custom_Metadata_Type extends \Tainacan\Metadata_Types\Metadata_Type {

	function __construct() {

		parent::__construct();

		// Basic options
		$this->set_name( __('Custom Metadata Type', 'custom-metadata-type') );
		$this->set_description( __('A numeric value, integer or float', 'custom-metadata-type') );
		$this->set_primitive_type(['float']);
		$this->set_component('tainacan-metadata-type-custom');
		$this->set_preview_template('
			<div>
                <div class="control is-clearfix">
                    <input type="number" placeholder="3,1415" class="input">
                </div>
            </div>
		');

		// For custom Metadata Type Options
		$this->set_form_component('tainacan-metadata-form-type-custom');
		$this->set_default_options([
            'step' => 1
        ]);
	}
	// ... More to bellow ...
}
?>
```

These are the methods you have to call to set up your Metadata Type:

- **set_name(string \$name)** - Sets the name of the Metadata type. This is the name the user will see in the front end and should be internationalized with your text domain (`custom-metadata-type`).
- **set_description(string \$description)** - Sets the description of the Metadata type. This is the text the user will see in the front end and should be internationalized with your text domain (`custom-metadata-type`).
- **set_primitive(string \$type)** - Inform what is the raw type of data that will be stored. This is used mainly by Filter Types to decide what kind of Filters will be available for each Metadata Type. Current used primitive types are: `string`, `date`, `float`, `item`, `term`, and `long_string` (you can create new ones if you want, just note that not all Filter Types may be available to them).
- **set_component(string \$component_name)** - The name of the vue component that will be created on the next session. We recommend adding the prefix _tainacan-metadata-type-_ to avoid collisions.
- **set_preview_template(string \$template)** - An HTML preview of how the component will look like. This is seen by users inside the tooltip "Metadata Type Preview" when hovering by the Metadata Type button. If you can use [Bulma](https://bulma.io ":ignore") and [Buefy](https://buefy.org/ ":ignore") classes here. This parameter is not obligatory but can improve user experience.

The last two (**set_form_component** and **set_default_options**) are only needed as we describe bellow:

### Metadata type Options

When you register a new Metadata Type, it will be automatically added as an option in the Metadata configuration screen. It will also have the default metadata configuration form, where you set whether the metadata is _required_ or not, accept _multiple values_ or not, and so on.

However, your metadata type may have specific options you want to give to the users. For example: In the Select Metadata type, you can set which will be the options in your Select. In the Numeric Metadata Type, we ant to allow users to decide the _step_ for increasing values.

To do this, you have to declare what are the options your metadata type has, and prepare another web component to be rendered in the metadata form:

- **set_form_component(string \$component_name)** - The name of the vue component that will be rendered inside the metadata edition form with extra options related to this metadata type. We recommend adding the prefix _tainacan-metadata-form-type-_ to avoid collisions.

- **set_default_options(string \$component_name)** - An array with custom metadata type options, having their `slug` and initial value. This reflects to the inputs that we'll exist on the Metadata Form Component.

Your form Metadata Form Component shall also include labels that need to be translated. For that, you can use the `get_form_labels` method:

```php
class Custom_Metadata_Type extends \Tainacan\Metadata_Types\Metadata_Type {

	// ... More above ...
	public function get_form_labels(){
        return [
            'step' => [
                'title' => __( 'Step', 'tainacan' ),
                'description' => __( 'The amount to be increased or decreased when clicking on filter control buttons.', 'tainacan' ),
            ]
        ];
    }
	// ... More below ...
}
```

Where the title is used as form label and the description is shown inside a tooltip, seen by the user when hovering a **?** icon.

Optionally you can implement the `validate_options` method to validate the form before it gets saved:

```php
class Custom_Metadata_Type extends \Tainacan\Metadata_Types\Metadata_Type {

	// ... More above ...
	public function validate_options( \Tainacan\Entities\Metadatum $metadatum ) {

		$option = $this->get_option('step');

		if (is_numeric($option)) { // Or any other validation condition
			return true; // validated!
		} else {
			return ['step' => __('The option "step" is invald. Must be a number!', 'custom-metadata-type')];
		}
	}
	// ... More below ...
}
```

`validate_options` is expected to return `true` if valid, and an array where the keys are the option `slug` and the values are the error messages.

### Additional Methods

There are a few other methods you can implement that can change how the items interact with metadata depending on the metadata type:

#### **validate( Item_Metadata_Entity \$item_metadata)**

This method will override the validation of the Item Metadata Entity, which means every time Tainacan saves a value for metadata of this type, it will call this method.

For example, the Date Metadata Type overrides [this method](https://github.com/tainacan/tainacan/blob/develop/src/views/admin/components/metadata-types/date/class-tainacan-date.php#L30 ":ignore") to make sure the date is in the correct format. Notice that it takes care of checking whether the value is a string (single value) or an array (multiple values).

#### **get_value_as_html( Item_Metadata_Entity \$item_metadata)**

This method will change the way value is converted to HTML for metadata of this metadata type. For example, Taxonomy and Relationship Metadata Type use [this](https://github.com/tainacan/tainacan/blob/develop/src/views/admin/components/metadata-types/taxonomy/class-tainacan-taxonomy.php#L197 ":ignore") and [this](https://github.com/tainacan/tainacan/blob/develop/src/views/admin/components/metadata-types/relationship/class-tainacan-relationship.php#L111 ":ignore"), respectivelly, to add links to the related term/item in the HTML output.

## Creating Vue Web Component

The Vue component is the chunk that will be rendered inside the Item Edition form so the user can edit its metadata of your custom type.

Our [Vue.js](vuejs.org/ ":ignore") components use the Options API, which means they are defined by an object with options, usually including:

- `template` - With its HTML content with vue notation for binding data and events;
- `data` - A function that returns an object with your data, or local variables;
- `props` - The variables that will be passed from the [Tainacan Form Item](https://github.com/tainacan/tainacan/blob/develop/src/views/admin/components/metadata-types/tainacan-form-item.vue ":ignore") parent component. We'll learn more about them below;
- `methods` - The array of functions that are evoked inside this component;
- `computed` - Array of functions that return local data that are derived from some processing of other variables;
- `watch` - Array of functions that are listening to some variable change to perform some update.
- And others such as lifecycle hooks, which you can check in the [Vue.js](vuejs.org/ ":ignore") documentation.

This object should be in that path referenced in our [registering process](#registering-your-metadata-type), so `metadata_type/metadata-type.js` in our Custom Metadata Type example, which is a copy of the Numeric Metadata Type:

```javascript
window.tainacan_extra_components =
  typeof window.tainacan_extra_components != "undefined"
    ? window.tainacan_extra_components
    : {};

const TainacanMetadataTypeCustom = {
  name: "TainacanMetadataTypeCustom",
  props: {
    itemMetadatum: Object,
    value: [String, Number, Array],
    disabled: false,
  },
  computed: {
    getStep: function () {
      if (
        this.itemMetadatum &&
        this.itemMetadatum.metadatum.metadata_type_options &&
        this.itemMetadatum.metadatum.metadata_type_options.step
      )
        return this.itemMetadatum.metadatum.metadata_type_options.step;
      else return 0.01;
    },
  },
  methods: {
    onInput: function (value) {
      this.$emit("input", value);
    },
    onBlur: function () {
      this.$emit("blur");
    },
  },
  template: `
	<b-input
            :disabled="disabled"
            :id="itemMetadatum.metadatum.metadata_type_object.component + '-' + itemMetadatum.metadatum.slug"
            :value="value"
            @input="onInput($event)"
            @blur="onBlur"
            type="number"
            lang="en"
            :step="getStep"/>
	`,
};

window.tainacan_extra_components["tainacan-metadata-type-custom"] =
  TainacanMetadataTypeCustom;
```

The first and last lines are an important step for registering custom components to the plugin JS bundle.

> [!WARNING]
> You MUST keep the `window.tainacan_extra_components` name, as it is the one used by the plugin to load custom components, and be careful to don't override it completely. Other plugins might have registered their components there too!

The `slug` passed to the array in the last line is the same used by the _set_component_ method previously in our [registration process](#registering-your-metadata-type).

### Understanding the component logic

#### Props: what the component receives from the parent component.

Notice first the `props` on the component. They are passed to every metadata:

- `itemMetadatum` is the item metadatum object itself, which contains important data as the `metadatum.id` and the `metadatum.metadata_type_options`;
- `value` is the value used for binding whatever is the content value of this metadatum;
- `disabled` is a boolean handled by the Item's form, which can be used to disable any inner component in case the options are not loaded and other situations that might be desired;

Prop's values are not to be modified by the component. If you want to perform changes to some local data before sending it to the API, you should probably use the `data` component option with a copy of the input value, passed during the `created()` lifecycle.

#### Methods: where we send values back to the parent component

.
The `methods` here simply delegate the blur and input events to the default parent component, which is responsible for passing these values to the Item's form.

> [!NOTE]
> Every metadatum component must emit an input value, passing the updated value that they received from the props.

#### Getting the Metatada Type Options on a Computed function

That might not be your case, but if your metadata component has registered a Metadata Form Component with extra options then you can access them via the `this.itemMetadatum.metadatum.metadata_type_options` object. In the code above, we access this value with a Computed function.

Finally, in this example, a custom component from [Buefy](https://buefy.github.io/), `b-input` is used. We recommend checking it out, as this library is already loaded on Tainacan, and most of its classes are styled to match our default style. But if you want to use another Vue.js library or component, you can check the session of [Registering Custom Vue JS](/dev/registering-custom-vue-components.md) for understanding how to use it, but the process is much alike what we have done to register the Metadata Form Component.

## Creating Vue Web Component for the Metadata Form

Registering the Metadata Form Component follows similar steps. You need to take care of using the path registered before, in our case `metadata_type/metadata-form-type.js` and care of using the same slug from the registration step: `tainacan-metadata-form-type-custom`. We are now registering not a metadata type, but an isolated vuejs component, thats why in the `PHP` file we're calling the `register_vuejs_component` function. Here is our considerably longer `.js` file:

```js
window.tainacan_extra_components =
  typeof window.tainacan_extra_components != "undefined"
    ? window.tainacan_extra_components
    : {};

const TainacanMetadataFormCustomType = {
  name: "TainacanMetadataFormTypeCustom",
  props: {
    value: [String, Number, Array],
  },
  data: function () {
    return {
      step: [Number, String],
      showEditStepOptions: false,
    };
  },
  created: function () {
    this.step = this.value && this.value.step ? this.value.step : 1;
  },
  methods: {
    onUpdateStep: function (value) {
      this.$emit("input", { step: value });
    },
  },
  template: `
	<div>
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-filter-numeric', 'step') }}<span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-filter-numeric', 'step')"
                        :message="$i18n.getHelperMessage('tainacan-filter-numeric', 'step')"/>
            </label>
            <div
                    v-if="!showEditStepOptions"
                    class="is-flex">
                <b-select
                        name="step_options"
                        v-model="step"
                        @input="onUpdateStep">
                    <option value="0.001">0.001</option>
                    <option value="0.01">0.01</option>
                    <option value="0.1">0.1</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="100">100</option>
                    <option value="1000">1000</option>
                    <option
                            v-if="step && ![0.001,0.01,0.1,1,2,5,10,100,1000].find( function(element) { return element == step })"
                            :value="step">
                        {{ step }}</option>
                </b-select>
                <button
                        class="button is-white is-pulled-right"
                        :aria-label="$i18n.get('edit')"
                        @click.prevent="showEditStepOptions = true">
                    <span
                            v-tooltip="{
                                content: $i18n.get('edit'),
                                autoHide: true,
                                placement: 'bottom'
                            }"
                            class="icon">
                        <i class="tainacan-icon tainacan-icon-18px tainacan-icon-edit has-text-secondary"/>
                    </span>
                </button>
            </div>
            <div
                    v-if="showEditStepOptions"
                    class="is-flex">
                <b-input
                        name="max_options"
                        v-model="step"
                        @input="onUpdateStep"
                        type="number"
                        step="1" />
                <button
                        @click.prevent="showEditStepOptions = false"
                        class="button is-white is-pulled-right">
                    <span
                            v-tooltip="{
                                content: $i18n.get('close'),
                                autoHide: true,
                                placement: 'bottom'
                            }"
                            class="icon">
                        <i class="tainacan-icon tainacan-icon-18px tainacan-icon-close has-text-secondary"/>
                    </span>
                </button>
            </div>
        </b-field>
    </div>
	`,
};
window.tainacan_extra_components["tainacan-metadata-form-type-custom"] =
  TainacanMetadataFormCustomType;
```

Some observation here:

- The form components always receive a `value` prop from its parent, that is an object containing any option registered on the Metadata Type Class;
- You don't need to wrap your template with a `<form>` tag, as the parent component of this will be the default Metadata Edition Form;
- In the example above, we store a local copy of the `value.step`, obtained on the `created()` lifecycle. That is because we want to change the value internally and also emit the updated version to the parent form. Notice, when `emit()` is performed, that the value goes inside an object, the same way that it was received from the parent.

## Advanced usage of Vue Components

The strategy mentioned above may not be sufficient in your case, more specifically in two cases:

1. If you want to write your component in a `.vue` file, avoiding the `template` option which may not be good for organizing complex components;
2. If you need extra third party Vue plugins or components that are not loaded by default in Tainacan's plugin bundle. For example, if you wish to use some kind of Date Picker plugin in case you are not satisfied with Buefy's one.

In any of those cases, you would need a _bundler_ to convert your Vue files and import logic into a single `js` file, to be referenced in your PHP class. While there are different bundlers out there that can be used, we will use [Webpack](https://webpack.js.org/ ":ignore") as an example and [NPM](https://www.npmjs.com/ ":ignore") as our package manager. Here is what you'll need to have, in your `metadata_type` folder:

- A `package.json` file, to hold your npm dependencies and script instructions;
- A `webpack.config.js` file, containing the bundler settings;
- Your new `metadata-type.vue` file, with the logic that one was put on the `.js` file, and your extra stuff;
- A modified version of the old `metadata-type.js` file that we created earlier in this tutorial.

And of course, after all set you'll need to [build it](#build-it!). The settings will depend a lot on what you are building on, but here we have some example:

### Minimal _package.json_ example

```json
{
  "name": "metadata-type",
  "version": "1.0.0",
  "description": "",
  "scripts": {
    "build": "npx webpack metadata-type.js"
  },
  "author": "",
  "license": "ISC",
  "dependencies": {
    "some-third-party-component": "^0.0.1"
  },
  "devDependencies": {
    "@babel/core": "^7.8.4",
    "babel-loader": "^8.0.6",
    "vue-loader": "^15.9.0",
    "vue-template-compiler": "^2.6.11",
    "webpack": "^4.41.6",
    "webpack-cli": "^3.3.12",
    "webpack-dev-server": "^3.10.3"
  }
}
```

Notice how we already offer a build command and an example of how a third party component would be included as a dependency. The usual way to add these is by running `npm install some-third-party-component`, if there is a package published on _npm_ repository.

### Basic _webpack.config.js_ example

```js
let path = require("path");
let webpack = require("webpack");
const VueLoaderPlugin = require("vue-loader/lib/plugin");

module.exports = {
  output: {
    path: path.resolve(__dirname, "dist"),
    filename: "metadata-type.bundle.js",
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
  plugins: [new VueLoaderPlugin()],
};
```

> [!WARNING]
> [!NOTE] Notice that the generated bundle(`metadata-type.bundle.js`) has a different name than the source file(`metadata-type.js`) and is inside a `build` folder, as defined by the [Webpack config file](#basic-webpackconfigjs-example). This means that you will have to update your [registration PHP](#registering-your-metadata-type) file mentioned earlier to provide the proper `metadata_script_url`.

### Your .vue file

Now, instead of using the "_object-with-options_" logic for [creating the Vue component](#creating-vue-web-component-for-the-metadata-form), we can actually take advantage of Vue's "_single-file-component_" logic and even _import_ some third party components if needed:

```vue
<template>
    <div v-if="itemMetadatum">
        <b-input
            :disabled="disabled"
            :id="
            itemMetadatum.metadatum.metadata_type_object.component +
            '-' +
            itemMetadatum.metadatum.slug
            "
            :value="value"
            @input="onInput($event)"
            @blur="onBlur"
            type="number"
            lang="en"
            :step="getStep"
        />
        <some-third-party-component />
  </did>
</template>

<script>
import SomeThirdPartyComponent from "some-third-party-component";

export default {
  name: "TainacanMetadataTypeCustom",
  components: {
    //SomeThirdPartyComponent,
  },
  props: {
    itemMetadatum: Object,
    value: [String, Number, Array],
    disabled: false,
  },
  computed: {
    getStep: function () {
      if (
        this.itemMetadatum &&
        this.itemMetadatum.metadatum.metadata_type_options &&
        this.itemMetadatum.metadatum.metadata_type_options.step
      )
        return this.itemMetadatum.metadatum.metadata_type_options.step;
      else return 0.01;
    },
  },
  methods: {
    onInput: function (value) {
      this.$emit("input", value);
    },
    onBlur: function () {
      this.$emit("blur");
    },
  },
};
</script>

<style>
/* How about some custom style here? */
</style>
```

### Updating the .js file

Now the previous `.js` file only have to import the component and pass it to the `window.tainacan_extra_components` global variable.

```js
import TainacanMetadataTypeCustom from "./metadata-type.vue";

window.tainacan_extra_components =
  typeof window.tainacan_extra_components != "undefined"
    ? window.tainacan_extra_components
    : {};
window.tainacan_extra_components["tainacan-metadata-type-custom"] =
  TainacanMetadataTypeCustom;
```

### Build it!

With these four files set up you are ready to build your small vue.js project. Open the terminal, enter the `metadata_type` folder and execute:

```
npm install
npm run build
```

If everything goes well, you shall have a new folder `node_modules` and a new file `package.lock.json` generated on your folder.

## Wrapping up

Creating your custom Metadata Type for Tainacan requires following some _naming conventions_ and understanding a bit of the structure existing on the plugin code. We here summarized how to [Register](#registering-your-metadata-type), create the [Class](#creating-the-php-class) and the [Custom Vue Components](#creating-vue-web-component) related to it. But you can learn a lot more by studying the [source code of some more complex examples](https://github.com/tainacan/tainacan/tree/develop/src/views/admin/components/metadata-types ":ignore"). Feel free to reach us at the GitHub repo or at the [community forum](https://tainacan.discourse.group ":ignore") :wink:

> Oh, and BTW. Are you ready to also create a [Custom Filter Type](/dev/creating-filters-type) for your new Metadata Type?
