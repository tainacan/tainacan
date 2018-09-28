# The Importer flow

This page describes how Tainacan importer works and is a reference to write your own importer. 

This documentation is still in construction. A very effective way to learn more on how to build an importer is to look at the source code of the Test Importer and the CSV Importer classes that are included in the tainacan package.

## Introduction

Importers can import items inside a collection, or even create a bunch of collections, taxonomies and items all at once.

In order to create an Importer, you have to extend the `\Tainacan\Importer` Class and register it using the global `Tainacan_Importer_Handler->register_importer()` method.

This method takes an array as argument, with the defintion of your importer. These are the expected attributes.

```
	 	   @type string		 $name					The name of the importer. e.g. 'Example Importer'
	 	   @type string		 $slug					A unique slug for the importer. e.g. 'example-importer'
	 	   @type string		 $description			The importer description. e.g. 'This is an example importer description'
	 	   @type string		 $class_name			The Importer Class. e.g. '\Tainacan\Importer\Test_Importer'
	 	   @type bool		 $manual_mapping		Wether Tainacan must present the user with an interface to manually map 
	 												the metadata from the source to the target collection.
	 	 											If set to true, Importer Class must implement the method 
	 												get_source_metadata() to return the metadata found in the source.
	 											
	 												Note that this will only work when importing items to one single collection.
	 
	 	   @type bool		 $manual_collection		Wether Tainacan will let the user choose a destination collection.
	 	 											If set to true, Tainacan will handle Collection creation and will assign it to 
	 												the importer object using add_collection() method.
	 											
	 												Otherwise, the child importer class must create the collections and add them to the collections property also using add_collection()
```

Note that depending on the value of `manual_mapping` and `manual_collection` you will have to implement some methods in your importer class.

## Initializing a new importer

When the user starts a new import process, he/she first choose which import to use.

Once the Importer is chosen, the first thing that happens is the creation of a new instance of the chosen Importer. This fires the `__construct()` method.


## Choose a collection (if manual_mapping is true)

After choosing the importer, user will be given the choice to choose the destination collection.

If called from inside a collection, this step is skipped and the current collection is set as destination.

## Set options

Now its time to set the importer options. Each importer may have its own set of options, that will be used during the import process. It could be anything, from the delimiter character in a CSV importer, to an API key for a importer that fetches something from an API.

Importer classes should declare the default values for its options in the `__construct()` method, by calling `set_default_options()`.

```
namespace Tainacan\Importer;

class MyImporter extends Importer {
	function __construct() {
		parent::construct();
		$this->set_default_options(['
			'foo' => 'bar'
		']);
	}
}
```

The Importer classes must also implement the `options_form` method, in which it will echo the form that the user will interact to set the options.

```
	function options_form() {
		$form = '<div class="field">';
        $form .= '<label class="label">' . __('My Importer Option 1', 'tainacan') . '</label>';
		$form .= '<div class="control">';
		$form .= '<input type="text" class="input" name="my_importer_option_1" value="' . $this->get_option('my_importer_option_1') . '" />';
		$form .= '</div>';
		$form .= '</div>';

		return $form;
	}
```

## Fetch source

Next, users will choose the source. Each importer declares the kind of sources they accpet: URL, file, or both.

Importers do this by calling the `add_import_method()` and `remove_import_method` in its construction. By default, importers will support only `file` method. Here is an example of what an importer thar accepts only URLs should do:

```
	function __construct() {
		parent::construct();
		
		$this->add_import_method('url');
		$this->remove_import_method('file');
		
	}
```

If the Importer accepts the `file` method, user will be prompted with a file input to upload a file. The file will then be saved and will be accessible via the `$this->get_tmp_file()` method.

If the importer accepts the `url` method, user will be prompted with an text input to enter an URL. By default, the importer will fetch any given URL to the same `file` attrribute, as if the user had uploaded it. However, each importer may override the `fetch_from_remote()` method and do whatever it want to create the file. For example, it could make several paged requests. 

From that point forward, the importer will behave just as if it was using the file method.


## Mapping

At this point, if the Importer definition has `manual_mapping` set to `true`, the user is presented with an interface to map the metadata from the source to the metadata present in the chosen collection.

The Importer class must implement the `get_source_metadata()` method, that will return an array of the metadata found in the source. It can either return an hard coded array or an array that is read from the source file. For example, an importer that fetches data from an api knows beforehand what are the metadata the api will return, however, an importer that reads from a csv, may want to return whatever is found in the first line of the array. 

```
// Example 1: returns a hard coded set of metadata
function get_source_metadata() {
	return [
		'title',
		'description'
		'date',
	];
}

// Example 2: returns the columns of the first line of a CSV file
public function get_source_metadata(){
	$file = new \SplFileObject( $this->tmp_file, 'r' );
	$file->seek(0);
	return $file->fgetcsv( $this->get_option('delimiter') );
}
```

## Importer steps

An Importer may have several steps, that will handle different parts of the process. Each step will be handled by a different callback in the Importer class.

First, lets have a look at a simple CSV importer, that only have one steps, in which it imports the items from the source into a chosen collection. After that we will have a look on how to create custom steps.

### Simple Importer - One step that imports items

By default, the only method an Importer class must implement to functino is the `process_item()` class.

This method gets two parameters, the `$index` of the item to be inserted, and the `$collection_definition`, with information on the target collection.

Inside this metho you must fetch the item from the source and format it according to the `mapping` definition of the collection.

The `mapping` defines how the item metadata from the source should be mapped to the metadata present in the target collection. It was created either manually, by the user, or programatically by the importer in an earlier step (see advanced importers below). This is an array where the keys are the `metadata IDs` and the values are the `identifers` found in source.

All this method should do is return the item as an associative array, where the keys are the metadata `identifiers`, and the values are the values tha should be stored.

And that's it. Behind the scenes the Importer super class is handling everyhting and it will call `process_item()` as many times as needed to import all items into the collection

### Advanced Importer - Many steps

By default, Tainacan Importer super class is registering one single step to the importer:

```
[
	'name' => 'Import Items',
	'progress_label' => 'Importing Items',
	'callback' => 'process_collections'
]
```

This step will lopp though all the collections added to the importer (manuall or programatically) and add the items to it.

You may register as many steps and callbacks as you want in your importer, but you should consider keeping this default step at some point to handle the items insertion. For example, see how the Test Importer adds other steps before and after but keeps this default step in the middle:

```
class Test_Importer extends Importer {
	
	protected $steps = [
		
		[
			'name' => 'Create Taxonomies',
			'progress_label' => 'Creating taxonomies',
			'callback' => 'create_taxonomies'
		],
		[
			'name' => 'Create Collections',
			'progress_label' => 'Creating Collections',
			'callback' => 'create_collections'
		],
		
		// we keep the default step
		[
			'name' => 'Import Items',
			'progress_label' => 'Importing items',
			'callback' => 'process_collections'
		],
		
		[
			'name' => 'Post-configure taxonomies',
			'progress_label' => 'post processing taxonomies',
			'callback' => 'close_taxonomies'
		],
		[
			'name' => 'Finalize',
			'progress_label' => 'Finalizing',
			'callback' => 'finish_processing',
			'total' => 5
		]
		
	];

	//...

```

#### Steps callbacks

Each step has its own callback. The callback may do anything necessary, just keep in mind that you should allow the importer to break very long processes into several requests.

In order to that, your step callback might be called several times, and each time run a part of the process and returnt its current status, until its done.

When you run the importer, Tainacan will automatically iterate over your steps. If a step callback returns `false`, it assumes the step is over and it will pass to the next step in the next iteration. If the step callback returns an integer, it will keep the pointer in this step and call the same step again in the next iteration. The current position, which is the integer returned the last time the callback was invoked, will be accessible via the `$this->get_in_step_count()` method.

See this example found in the Test Importer:

```
public function finish_processing() {
		
	// Lets just pretend we are doing something really important
	$important_stuff = 5;
	$current = $this->get_in_step_count();
	if ($current <= $important_stuff) {
		// This is very important
		sleep(5);
		$current ++;
		return $current;
	} else {
		return false;
	}
	
}
```

#### Adding collections

If your importer does not use the `manual_collection` option, you might have to create the collection on your own.

You will do this using the [Tainacan internal API](internal-api.md).

After you've created one or more collections, you will have to add them to the importer queue, registering some information about them. This only if you want (and most likely you should) rely on the default step for processing items into the collections.

To add or remove a collection from the queue, use the `add_collection()` and `remove_collection()` methods passing the collection definition.

The collection definition is an array with their IDs, an identifier from the source, the total number of items to be imported, the mapping array from the source structure to the ID of the metadata metadata in tainacan.

The format of the map is an array where the keys are the metadata IDs of the destination collection and the values are the identifier from the source. This could be an ID or a string or whatever the importer finds appropriate to handle.

The source_id can be anyhting you like, that helps you relate this collection to your source. You will use it in you `process_item` method to know where to fetch the item from.

Example of the structure of this propery for one collection:

```
[
	'id' => 12,
	'mapping' => [
	  30 => 'column1'
	  31 => 'column2'
	],
	'total_items' => 1234,
	'source_id' => 55
]
```


#### Handling user feedback

There are two information Tainacan Importers give to the user about the status of the process while it is running in feedback. the `progress label` and the `progress value`. 

The `progress label` is a string that can be anything that tells the user what is going on. By default, it is the Step Name, but you can inform a specific `progress_label` attribute when registering the steps.

The `progress value` is a number between 0 and 100 that indicates the progress of the current step or the whole importer, Thats up to you. By default, it calculates it automatically using the `total` attribute registered with the steps, against the `$this->get_in_step_count()` value. In the case of the default Process Items callback, it calculates based on the number of items found in each collection.

Remember the `finish_processing` dummy callback we saw in the Test Importer. You might have also noticed that when we registered the step, we informed a `total` attribute to this step with the value of 5. This will tell Tainacan that the total number iterations this step need to complete is 5 and allow it to calculate the progress.

If it is no possible to know `total` of a step beforehand, you can set it at any time, even inside the step callback itself, using the `set_current_step_total($value)` or `set_step_total($step, $value)` methods.

#### Logs

There are two useful methods to write information to the logs: `add_log()` and `add_error_log()`. These are written into a log file related to the importer background process and a link to it will be presented to the user.



## Run importer

Finally, everything is ready. The importer runs.

This will trigger a Background Process (documentation needed) and the importer will run through as many background requests as needed.
