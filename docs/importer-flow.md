# The Importer flow

This page describes how Tainacan importer works and is a reference to write your own importer. 

## Initializing a new importer

When the user starts a new import process, he/she first choose which import to use.

Once the Importer is chosen, the first thing that happens when is the creation of a new instance of the chosen Importer. This fires the `__construct()` method.


## Choose a collection

After choosing the importer, user will be given the choice to choose the destination collection.

If called from inside a collection, this step is skipped and the current collection is set as destination.

If the importer has the attribute `$import_structure_and_mapping` set to `true`, and the importer is called from repository level,
they will also be able to choose to create a new collection. 

In that cases, a new collection is created, and the importer must implement a method called `create_fields_and_mapping()`, which, as the name says, will create all collections metadata and set the mapping for the importer.


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
		?>
		<label>Foo</label>
		<input type="text" name="options[foo]" value="<?php echo $this->get_options('foo'); ?>" />
		<?php
	}
```

TODO: better html reference and validate_options() method.


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

If the Importer accepts the `file` method, user will be prompted with a file input to upload a file. The file will then be saved and will be accessible via the `$this->tmp_file` attribute.

If the importer accepts the `url` method, user will be prompted with an text input to enter an URL.By default, the importer will fetch any given URL to a file. However, each importer may override the `fetch_from_remote()` method and do whatever it want to create the file. For example, it could make several paged requests. 

From that point forward, the importer will behave just as if it was using the file method.


## Mapping

At this point the user is presented with an interface to map the metadata from the source to the metadata present in the chosen collection.

The Importer class must implement the `get_fields()` method, that will return an array of the metadata found in the source. It can either return an hard coded array or an array that is red from the source file. For example, an importer that fetches data from an api knows beforehand what are the metadata the api will return, however, an importer that reads from a csv, may want to return whatever is found in the first line of the array. 

```
// Example 1: returns a hard coded set of metadata
function get_fields() {
	return [
		'title',
		'description'
		'date',
	];
}

// Example 2: returns the columns of the first line of a CSV file
public function get_fields(){
	$file =  new \SplFileObject( $this->tmp_file, 'r' );
	$file->seek(0 );
	return $file->fgetcsv( $this->get_option('delimiter') );
}
```

## Importing Collection Structure and Mapping

Alternatively, an importer may also create all the metadata and mappings from the source. In that cases, the user does not have to map anything.

First thing an Importer must do to accomplish this is to declare it does so in the `construct()`, by setting `$import_structure_and_mapping` to `true`.

```
function __construct() {
	parent::construct();
	
	$this->import_structure_and_mapping = true;
}
```

Second, the importer must implement the `create_fields_and_mapping()` to populate the collection with metadata and set the mapping.

In order to do this, the Importer will use Tainacan internal API to create the metadata. Please refer to the documentation (TODO: write this documentation).

This method must be aware that even a brand new collection comes with two core metadata (title and description), and use them.

Again, this metadata can come from the file, the URL or may be hardcoded in the function.

Example
```
function create_fields_and_mapping() {
	
	$metadata_repository = \Tainacan\Repositories\Metadata::get_instance();
	
	$newMetadatum1 = new \Tainacan\Entities\Metadatum();
	$newMetadatum1->set_name = 'New Metadatum';
	$newMetadatum1->set_field_type = 'Tainacan\Metadatum_Types\Text';
	$newMetadatum1->set_collection($this->collection);
	
	$newMetadatum1->validate(); // there is no user input here, so we can be sure it will validate.

	$newMetadatum1 = $metadata_repository->insert($newMetadatum1);

	$source_fields = $this->get_fields();
	
	$this->set_mapping([
		$newMetadatum1->get_id() => $source_fields[0]
	]);
	
}

TODO: helpers and explanation on how to fetch the core metadata and map them

```


## Run importer

Finally, everything is ready. The importer runs.

The `run()` method is called, the importer runs a step of the import process, and returns the number of items imported so far. The client (browser) will repeat this request as many times as necessary to complete the process and will give feedback to the user about the progress. 

In order to allow this, the importer must implement the `get_total_items_from_source()` method, which will inform the total number of items present in the source.

All the steps and insertion are handled by the Importer super class. The importer class only have to implement one method (`process_item()`) to handle one single item. It will receive the index of this item and it must return the item in as an array, where each key is the identifier of the source metadatum (the same used in the mapping array), and the values are each metadatum value.

In the end, a report is generated with all the logs generated in the process. 