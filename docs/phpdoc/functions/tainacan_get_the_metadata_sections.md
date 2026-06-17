# tainacan_get_the_metadata_sections


Render the item metadata sections as a HTML string.

Each metadata section is a label with the list of its metadata name and value.

If an ID, a slug or a Tainacan\Entities\Metadata_Section object is passed in the 'metadata_section' argument, it returns only one metadata section, otherwise
it returns all metadata section

***

* Full name: `tainacan_get_the_metadata_sections`
* Defined in: `classes/theme-helper/template-tags.php`

## Parameters

| Parameter  | Type              | Description                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
|------------|-------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$args`    | **array\|string** | {
    Optional. Array or string of arguments.

	  @type mixed		 $metadata_section				Metadatum object, ID or slug to retrieve only one metadatum. empty returns all metadata_sections

    @type array		 $metadata_sections__in			Array of metadata_sections IDs or Slugs to be retrieved. Default none

    @type array		 $metadata_sections__not_in		Array of metadata_sections IDs (slugs not accepted) to excluded. Default none

    @type bool		 $hide_name						Do not display the Metadata Section name. Default false

    @type bool		 $hide_description				Do not display the Metadata Section description. Default true

    @type bool        $hide_empty                	Whether to hide or not metadata sections if there are no metadata list or they are empty
                                                 	Default: true
    @type string      $empty_metadata_list_message 	Message string to display if $hide_empty is false and there is not metadata section metadata list.
                                                 	Default: ''
    @type bool        $display_slug_as_class     	Show metadata slug as a class in the div before the metadata block
                                                 	Default: true
    @type string      $before                    	String to be added before each metadata section block
                                                 	Default '<section $id>'
    @type string      $after		                String to be added after each metadata section block
                                                 	Default '</section>'
    @type string      $before_name              	String to be added before each metadata section name
                                                 	Default '<h2>'
    @type string      $after_name               	String to be added after each metadata section name
                                                 	Default '</h2>'
	  @type string      $before_description         String to be added before each metadata section description
                                                 	Default '<p>'
    @type string      $after_description          String to be added after each metadata section description
                                                 	Default '</p>'
    @type string      $before_metadata_list      	String to be added before each metadata section inner metadata list
                                                 	Default '<div class="metadata-section__metadata-list">'
    @type string      $after_metadata_list       	String to be added after each metadata section inner metadata list
                                                 	Default '</div>'
  @type array		$metadata_list_args			Arguments to be passed to the get_metadata_as_html function when calling section metadata
} |
| `$item_id` | **mixed**         |                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |

## Return Value

**string**

The HTML output
