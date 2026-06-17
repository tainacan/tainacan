# tainacan_get_the_metadata


To be used inside The Loop

Return the item metadata as a HTML string to be used as output.

Each metadata is a label with the metadatum name and the value.

If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in 'metadata' parameter, it returns only one metadata, otherwise
it returns all metadata

***

* Full name: `tainacan_get_the_metadata`
* Defined in: `classes/theme-helper/template-tags.php`

## Parameters

| Parameter  | Type              | Description                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           |
|------------|-------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$args`    | **array\|string** | {
    Optional. Array or string of arguments.

	   @type mixed		 $metadata					Metadatum object, ID or slug to retrieve only one metadatum. empty returns all metadata
    @type array		 $metadata__in				Array of metadata IDs or Slugs to be retrieved. Default none
    @type array		 $metadata__not_in			Array of metadata IDs (slugs not accepted) to excluded. Default none
    @type bool		 $exclude_title				Exclude the Core Title Metadata from result. Default false
    @type bool		 $exclude_description		Exclude the Core Description Metadata from result. Default false
    @type bool		 $exclude_core				Exclude Core Metadata (title and description) from result. Default false
    @type bool        $hide_empty                Whether to hide or not metadata the item has no value to
                                                 Default: true
    @type string      $empty_value_message       Message string to display if $hide_empty is false and there is not metadata value. Default ''
    @type string      $before                    String to be added before each metadata block
                                                 Default '<div class="metadata-type-$type">' where $type is the metadata type slug
    @type string      $after		                String to be added after each metadata block
                                                 Default '</div>'
    @type string      $before_title              String to be added before each metadata title
                                                 Default '<h3>'
    @type string      $after_title               String to be added after each metadata title
                                                 Default '</h3>'
    @type string      $before_value              String to be added before each metadata value
                                                 Default '<p>'
    @type string      $after_value               String to be added after each metadata value
                                                 Default '</p>'
} |
| `$item_id` | **int\|string**   | (Optional) The item ID to retrive the metadatum as a HTML string to be used as output. Default is the global $post                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |

## Return Value

**string**

The HTML output
