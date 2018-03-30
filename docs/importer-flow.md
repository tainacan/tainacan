
Create an instance of the Importer

First of all, user will be given the choice to choose the destination collection.

If called from inside a collection, this step is skipped and the current collection is set as destination.

If the importer has the attribute `$import_structure_and_mapping` set to `true`, and the importer is called from repository level,
they will also be able to choose to create a new collection. 

In that cases, a new collection is created with the status of `auto-draft`, and the importer will implement a method called `create_fields_and_mapping()`, which, as the name says, will create all collections fields and set the mapping for the importer.


Set options

Now its time to set the importer options. Each importer may have its own set of options, that will be used during the import process. It could be anything, from the delimiter character in a CSV importer, to some query parameter for a importer that fetches something from an API.

The importer handles options by implementing three methods: `get_options()`, `get_default_options()` and `options_form()`.


Fetch file

Next, users will choose the source. Each importer declares the kind of sources they accpet: URL, file, or both.

If its a file, upload it. If its an URL, fetch it into a file.

By default, the importer will fetch any given URL to a file. However, each importer may override the `fetch_from_remote()` method
and do whatever it want to create the file. For example, it could make several paged requests.


Mapping

If the importer has the attribute `$import_structure_and_mapping` set to `true`, the importer will automatically create all the fields and set the mapping for the collection. This is done by calling the `create_fields_and_mapping()` method of the importer.

If not, the user will be asked to map the fields that were identified in the source file (by calling the `get_fields()` method) with the fields present in the chosen collection.


Run importer

Finally, everything is ready. The importer runs.

The `run()` method is called, the importer runs a step of the import process, and returns the number of items imported so far. The client (browser) will repeat this request as many times as necessary to complete the process and will give feedback to the user about the progress. 

In order to allow this, the importer must implement the `get_total_items()` method, which will inform the total number of items present in the source file.

All the steps and insertion are handled by the Importer super class. The importer class only have to implement one method (`process_item()`) to handle one single item. It will receive the index of this item and it must return the item in the format of a mapped array, where the key is the identifier of the source field (the same used in the mapping array), and the value is the field value.

In the end, a report is generated with all the logs generated in the process. If the collection was set to `auto-draft`, it is now published.