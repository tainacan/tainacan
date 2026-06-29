# The Exporter flow

This page describes how Tainacan exporters work and is a reference to write your custom exporter. 

This documentation is still in construction. A very effective way to learn more about how to build an exporter is to look at the source code of the CSV Exporter class that is included in the tainacan package.

## Introduction

Exporters can export items from a single collection, or even create a bunch of collections, taxonomies, and items all at once.

To create an Exporter, you have to extend the `\Tainacan\Exporter` Class and register it using the global `Tainacan\Export_Handler->register_exporter()` method.

This method takes an array as argument, with the definition of your exporter. These are the expected attributes.

```php
	@type string	$name					The name of the exporter. e.g. 'Example exporter'
	@type string	$slug					A unique slug for the exporter. e.g. 'example-exporter'
	@type string	$description			The exporter description. e.g. 'This is an example exporter description'
	@type string	$class_name				The Exporter Class. e.g. '\Tainacan\Exporter\Test_Exporter'
	@type bool		$manual_mapping			Wether Tainacan must present the user with an interface to manually choose 
											a mapping standard. This will allow them to export the items mapped to a
											chosen standard instead of in its original form.
											
											The exporter will also be able to inform what mappers it supports. See `set_accepted_mapping_methods`
											and `accept_no_mapping` below.
										
											Note that this will only work when exporting items from one single collection.

	@type bool		$manual_collection		Wether Tainacan will let the user choose the source collection.
											If set to true, Tainacan give the user a select box from where he/she will Choose
											one (and only one) Collection to export items from.
										
											Otherwise, the child exporter class must choose the collections somehow
											(it could be in its options form) and add them to the collections property using `add_collection()`
```

Note that depending on the value of `manual_mapping` and `manual_collection` you will have to implement some methods in your exporter class.

## Initializing a new exporter

When the user starts a new export process, he/she first choose which export to use.

Once the Exporter is chosen, the first thing that happens is the creation of a new instance of the chosen Exporter. This fires the `__construct()` method.


## Choose a collection (if `manual_collection` is true)

After choosing the exporter, the user will be given the choice to choose the source collection.

If called from inside a collection, this step is skipped and the current collection is set as the source.

## Set options

Now its time to set the exporter options. Each exporter may have its own set of options, that will be used during the export process. It could be anything, from the delimiter character in a CSV exporter to an API key for an exporter that sends data to an external API.

exporter classes should declare the default values for its options in the `__construct()` method, by calling `set_default_options()`.

```php
namespace Tainacan\exporter;

class Myexporter extends Exporter {
	function __construct() {
		parent::construct();
		$this->set_default_options(['
			'foo' => 'bar'
		']);
	}
}
```

The exporter classes must also implement the `options_form` method, in which it will echo the form that the user will interact to set the options.

```php
	function options_form() {
		$form = '<div class="field">';
        $form .= '<label class="label">' . __('My exporter Option 1', 'tainacan') . '</label>';
		$form .= '<div class="control">';
		$form .= '<input type="text" class="input" name="my_exporter_option_1" value="' . $this->get_option('my_exporter_option_1') . '" />';
		$form .= '</div>';
		$form .= '</div>';

		return $form;
	}
```

## Accepting mappers 

If you set `manual_mapping` as true and want to give users the ability to export a mapped version of their collections, you can also 
define which mappers you accept. 

```php
public function __construct($attributes = array()) {
	parent::__construct($attributes);
	$this->set_accepted_mapping_methods('any'); // set all method to mapping
	$this->accept_no_mapping = true;
}
```

`$this->set_accepted_mapping_methods()` lets you do that. If you set it to "any", all mappers will be available. If you set it to "list", you can then pass a second argument with the list of mappers you want to be available for the users.

```php
public function __construct($attributes = array()) {
	parent::__construct($attributes);
	$this->set_accepted_mapping_methods('list', [ "dublin-core" ]); // set specific list of methods to mapping
}
```

Finally, `$this->accept_no_mapping = true;` informs that you also allow users to export items in their original form, without any mapping. In other words, to choose a Mapper is not mandatory if this is set to true.

## exporter steps

An exporter may have several steps, that will handle different parts of the process. Each step will be handled by a different callback in the exporter class.

First, let us have a look at a simple CSV exporter, that only have one step, in which it exports the items from the source collection into a CSV file. After that, we will have a look at how to create custom steps.

### Simple exporter - One step that exports items

By default, exporters must implement 3 methods: `output_header`, `output_footer` and `process_item`.

`output_header` and `output_footer` are invoked at the beginning and end of each collection being exported. It can be useful, for example, to write the header and footer of a csv or xml file.

Inside these methods, you may do whatever you want. It could be a POST to an API or to write a new file (see handling files below).

The `process_item` method will be invoked for every item being exported. Tainacan will automatically loop through the collections in the queue, fetch and prepare the items, and then send them, one at a time, to this method. (see adding collections below).

This method gets two parameters, the `$item` object (instance of \Tainacan\Entities\Item), and its `$metadata`, with an array of '\Tainacan\Entities\Item_Metadata_Entity' objects.

Note that, in this method, you don't need to bother about mapping. If a mapper was chosen by the user, you will receive this array of metadata already mapped, which means they will be only the ones defined in the mapper. (Note that, if a collection fail to map one of its metadata to a metadatum expected by the chosen mapper, they will be empty elements in this array).

Now you can loop through the metadata and access any property you want from the item to do whatever you like. In the case of the CSV exporter, it will add one line to the CSV file.

If you need to access the Mapper object, with everything about the chosen mapper standard, you can do so by calling `$this->get_current_mapper()`. If a object is not returned, it means no mapper was selected by the user.

#### Adding collections

If your exporter does not use the `manual_collection` option, you might want to programmatically add collections to be exported to the queue.

To add or remove a collection from the queue, use the `add_collection()` and `remove_collection()` methods passing the collection definition.

The collection definition is an array with their IDs and the total number of items to be exported.


Example of the structure of this property for one collection:

```php
[
	'id' => 12,
	'total_items' => 1234,
]
```

#### Handling files 

Your export may generate one or more files, that will be downloaded by the user as a package at the end.

To create and write content to a file, use the `append_to_file()` method.

It takes 2 arguments: `$key` and `$data`. `$key` is the file identifier and will serve as the filename, prepended with the ID of the process. If a file with this key does not exist yet, it will be created. `$data` is the content to be appended to the file.

Don't forget to add a link to the generated file at the output at the end of the process. This will be the only way users will have to get them. See "Final output" below).

TODO: If more than one file was created, Tainacan will create a zip of them at the end. Yet to be implemented.

#### Using transients

Since exporters are handled as background processes, they will run across multiple requests. For that reason, you can not simply add properties to your class and trust their values will be kept during all the time the process is running.

If you want a value to persist so you can use it across all methods of your exporter, at any time, you should use `transients` to store them in the database.

This is as simple as calling `set_transient()` and `get_transient()`. See the example below:


```php
function callback_for_step_1() {
	
	$this->add_transient('time_step_1', time());
	
}
// ...

function callback_for_step_5() {
	
	$time_step_1 = get_transient('time_step_1');
	
}
```


#### Handling user feedback

There are two information Tainacan exporters give to the user about the status of the process while it is running in feedback. the `progress label` and the `progress value`. 

The `progress label` is a string that can be anything that tells the user what is going on. By default, it is the Step Name, but you can inform a specific `progress_label` attribute when registering the steps.

The `progress value` is a number between 0 and 100 that indicates the progress of the current step or the whole exporter, That's up to you. By default, it calculates it automatically using the `total` attribute registered with the steps, against the `$this->get_in_step_count()` value. In the case of the default Process Items callback, it calculates based on the number of items found in each collection.

See the `finish_processing` dummy callback in the Test Importer. You will notice that when we registered the step, we informed a `total` attribute to this step with the value of 5. This will tell Tainacan that the total number iterations this step needs to complete is 5 and allow it to calculate the progress.

If it is no possible to know `total` of a step beforehand, you can set it at any time, even inside the step callback itself, using the `set_current_step_total($value)` or `set_step_total($step, $value)` methods.


##### Final output 

When the process finishes, Background processes define an "output", which is a final report to the user of what happened.

This could be simply a message saying "All good", or could be a report with the names and links to the collections created. HTML is welcome.

Remember that for a more detailed and technical report, you should use Logs (see Logs below). This output is meant as a short but descriptive user-friendly message.

To achieve this, exporters must implement a method called `get_output()` that returns a string.

This method will be called only once when the exporter ends, so you might need to save information using `transients` during the process and use them in `get_output()` to compose your message.

To get a list of the generated files to display to users, use `$this->get_output_files()`.

#### Logs

There are two useful methods to write information to the logs: `add_log()` and `add_error_log()`. These are written into a log file related to the exporter background process and a link to it will be presented to the user.


## Run exporter

Finally, everything is ready. The exporter runs.

This will trigger a Background Process (documentation needed) and the exporter will run through as many background requests as needed.
