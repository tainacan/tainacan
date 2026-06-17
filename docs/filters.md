# Tainacan Filters

WordPress filters are hooks that allow you to modify data at specific points during the WordPress lifecycle. In Tainacan, filters enable developers to extend or modify plugin behavior without altering core files. To use a filter, attach your custom function to a specific hook using the `add_filter()` function:

```php
add_filter( 'tainacan-some-filter', 'your_callback_function', 10, 2 );
function your_callback_function( $arg1, $arg2 ) {
    // Your custom code here
}
```

Refer to the list below for available Tainacan filters and their usage.


## `tainacan-dashboard-cards` <!-- {docsify-ignore} -->

*Use this filter to add or remove dashboard cards.*

Remeber to return an array containing the card objects
with a structure that contains the id, title, description,
content (a callback), icon (an svg or img html tag), color
(one of gray, blue and turquoise) and position (normal, side, column3, column4)

If you remove any card from the array, users won't be able to add it anyway.
If you just remove its id from the 'tainacan_dashboard_disabled_cards' wp option,
users will be able to add it again.


Argument | Type | Description
-------- | ---- | -----------
`$tainacan_dashboard_cards` |  | 

Source: [class-tainacan-dashboard.php](https://github.com/tainacan/tainacan/blob/master/src/views/dashboard/class-tainacan-dashboard.php), [line 223](https://github.com/tainacan/tainacan/blob/master/src/views/dashboard/class-tainacan-dashboard.php#L223-L234)

---------------------------------
<br>

## `tainacan_dashboard_news_feed` <!-- {docsify-ignore} -->

*Creates the display code for the news card,
featuring RSS feed from Tainacan website*


Argument | Type | Description
-------- | ---- | -----------
`$this->default_news_feed_options` |  | 

Source: [class-tainacan-dashboard.php](https://github.com/tainacan/tainacan/blob/master/src/views/dashboard/class-tainacan-dashboard.php), [line 557](https://github.com/tainacan/tainacan/blob/master/src/views/dashboard/class-tainacan-dashboard.php#L557-L565)

---------------------------------
<br>

## `tainacan-dashboard-logo-use-white` <!-- {docsify-ignore} -->

*Tweaks the dashboard logo to use white, monochrome version*


Argument | Type | Description
-------- | ---- | -----------
`false` |  | 

Source: [page.php](https://github.com/tainacan/tainacan/blob/master/src/views/dashboard/page.php), [line 11](https://github.com/tainacan/tainacan/blob/master/src/views/dashboard/page.php#L11-L18)

---------------------------------
<br>

## `tainacan-dashboard-logo` <!-- {docsify-ignore} -->

*Filter the dashboard logo*


Argument | Type | Description
-------- | ---- | -----------
`plugin_dir_url(dirname(__FILE__, 2)) . '/assets/images/' . ($dashboard_logo_use_white ? 'tainacan_logo_dashboard_white.svg' : 'tainacan_logo_dashboard.svg')` |  | 

Source: [page.php](https://github.com/tainacan/tainacan/blob/master/src/views/dashboard/page.php), [line 20](https://github.com/tainacan/tainacan/blob/master/src/views/dashboard/page.php#L20-L30)

---------------------------------
<br>

## `tainacan-dashboard-welcome-message` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$welcome_message` |  | 

Source: [page.php](https://github.com/tainacan/tainacan/blob/master/src/views/dashboard/page.php), [line 41](https://github.com/tainacan/tainacan/blob/master/src/views/dashboard/page.php#L41-L41)

---------------------------------
<br>

## `tainacan-i18n` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`[
    // Comparators
    'is_equal_to' => __('Equal', 'tainacan'),
    'is_not_equal_to' => __('Not equal', 'tainacan'),
    'contains' => __('Contains', 'tainacan'),
    'not_contains' => __('Do not contain', 'tainacan'),
    'greater_than' => __('Greater than', 'tainacan'),
    'less_than' => __('Less than', 'tainacan'),
    'greater_than_or_equal_to' => __('Greater than or equal to', 'tainacan'),
    'less_than_or_equal_to' => __('Less than or equal to', 'tainacan'),
    'before' => __('Before', 'tainacan'),
    'after' => __('After', 'tainacan'),
    'before_or_on_day' => __('Before (inclusive)', 'tainacan'),
    'after_or_on_day' => __('After (inclusive)', 'tainacan'),
    'has_value' => __('Has value', 'tainacan'),
    'has_no_value' => __('Has no value', 'tainacan'),
    // Tainacan common terms
    'repository' => __('Repository', 'tainacan'),
    'collections' => __('Collections', 'tainacan'),
    'items' => __('Items', 'tainacan'),
    /* translators: Plural, a list of metadata */
    'metadata' => __('Metadata', 'tainacan'),
    'metadata_types' => __('Metadata types', 'tainacan'),
    'filters' => __('Filters', 'tainacan'),
    'taxonomies' => __('Taxonomies', 'tainacan'),
    'activities' => __('Activities', 'tainacan'),
    'collection' => __('Collection', 'tainacan'),
    'item' => __('Item', 'tainacan'),
    /* translators: The singular unit of metadata */
    'metadatum' => __('Metadatum', 'tainacan'),
    'filter' => __('Filter', 'tainacan'),
    'taxonomy' => __('Taxonomy', 'tainacan'),
    'activity' => __('Activity', 'tainacan'),
    'term' => __('Term', 'tainacan'),
    'terms' => __('Terms', 'tainacan'),
    'mapping' => __('Mapping', 'tainacan'),
    'importers' => __('Importers', 'tainacan'),
    'processes' => __('Processes', 'tainacan'),
    'sequence' => __('Sequence', 'tainacan'),
    'exporters' => __('Exporters', 'tainacan'),
    'capabilities' => __('Capabilities', 'tainacan'),
    // Actions
    'close' => __('Close', 'tainacan'),
    'edit' => __('Edit', 'tainacan'),
    'settings' => __('Settings', 'tainacan'),
    'new' => __('New', 'tainacan'),
    'add_value' => __('Add value', 'tainacan'),
    'import' => __('Import', 'tainacan'),
    'export' => __('Export', 'tainacan'),
    'cancel' => __('Cancel', 'tainacan'),
    'remove_point' => __('Remove point', 'tainacan'),
    'remove_value' => __('Remove value', 'tainacan'),
    'remove_a_value' => __('Remove a value', 'tainacan'),
    'clear_values' => __('Clear values', 'tainacan'),
    'remove_filter' => __('Remove filter', 'tainacan'),
    'save' => __('Save', 'tainacan'),
    'next' => __('Next', 'tainacan'),
    'previous' => __('Previous', 'tainacan'),
    'back' => __('Back', 'tainacan'),
    'exit' => __('Exit', 'tainacan'),
    'see' => __('View', 'tainacan'),
    'search' => __('Search', 'tainacan'),
    'advanced_search' => __('Advanced search', 'tainacan'),
    'continue' => __('Continue', 'tainacan'),
    'approve_item' => __('Approve', 'tainacan'),
    'not_approve_item' => __('Not approve', 'tainacan'),
    'add_one_item' => __('Add one item', 'tainacan'),
    'add_items_bulk' => __('Bulk add items', 'tainacan'),
    'add_items_external_source' => __('Add items from an external source', 'tainacan'),
    'new_mapped_item' => __('New mapped collection', 'tainacan'),
    'new_blank_collection' => __('New blank collection', 'tainacan'),
    'split' => __('Split', 'tainacan'),
    'unified' => __('Unified', 'tainacan'),
    'add_another_search_criterion' => __('Add another search criterion', 'tainacan'),
    'add_one_search_criterion' => __('Add one search criterion', 'tainacan'),
    'remove_search_criterion' => __('Remove search criterion', 'tainacan'),
    'clear_search' => __('Clear search', 'tainacan'),
    'run' => __('Run', 'tainacan'),
    'edit_search' => __('Edit search', 'tainacan'),
    'apply' => __('Apply', 'tainacan'),
    'add_another_bulk_edit' => __('Add another bulk edit criterion', 'tainacan'),
    'add_one_bulk_edit' => __('Add one bulk edit criterion', 'tainacan'),
    'remove_bulk_edit' => __('Remove bulk edit criterion', 'tainacan'),
    'set_new_value' => __('Set new value', 'tainacan'),
    'replace_value' => __('Replace value', 'tainacan'),
    'copy_value' => __('Copy value from', 'tainacan'),
    'finish' => __('Finish', 'tainacan'),
    'select_to_create' => __('select to create', 'tainacan'),
    'new_bulk_edit_criterion' => __('New bulk edit criterion', 'tainacan'),
    'undo' => __('Undo', 'tainacan'),
    'delete' => __('Delete', 'tainacan'),
    'skip' => __('Skip', 'tainacan'),
    'add' => __('Add', 'tainacan'),
    'show' => __('Show', 'tainacan'),
    // Wordpress Comments Status
    'comments_status_open' => __('Open', 'tainacan'),
    'comments_status_closed' => __('Closed', 'tainacan'),
    // Wordpress Status
    /* translators: The noun for the public/published status */
    'status_publish' => __('Publish', 'tainacan'),
    'status_publish_description' => __('Everyone can see it.', 'tainacan'),
    /* translators: Also the noun for the public/published status */
    'status_public' => __('Public', 'tainacan'),
    /* translators: The noun for the draft status, not the verb */
    'status_draft' => __('Draft', 'tainacan'),
    'status_draft_description' => __('Not ready to be published.', 'tainacan'),
    /* translators: The noun for the trash pending, not the verb */
    'status_pending' => __('Pending', 'tainacan'),
    'status_pending_description' => __('Pending review before publishing.', 'tainacan'),
    'status_private' => __('Private', 'tainacan'),
    'status_private_description' => __('Only you and those with permission can see it.', 'tainacan'),
    /* translators: The noun for the trash status, not the verb */
    'status_trash' => __('Trash', 'tainacan'),
    'status_trash_description' => __('Deleted, but not permanently.', 'tainacan'),
    'status_auto-draft' => __('Automatic draft', 'tainacan'),
    'label_open_access' => __('Open access', 'tainacan'),
    'label_restrict_access' => __('Restrict access', 'tainacan'),
    // Page Titles (used mainly on Router)
    'title_repository_collections_page' => __('Collections', 'tainacan'),
    'title_items_page' => __('All items from all collections', 'tainacan'),
    'title_my_items_page' => __('My items', 'tainacan'),
    'title_repository_metadata_page' => __('Repository Metadata', 'tainacan'),
    'title_repository_filters_page' => __('Repository Filters', 'tainacan'),
    'title_taxonomies_page' => __('Taxonomies', 'tainacan'),
    'title_terms_page' => __('Terms', 'tainacan'),
    'title_repository_activities_page' => __('Repository Activities', 'tainacan'),
    'title_repository_reports_page' => __('Repository Reports', 'tainacan'),
    'title_reports' => __('Reports', 'tainacan'),
    'title_collection_page' => __('Items from Collection', 'tainacan'),
    'title_my_items_collection_page' => __('My items from', 'tainacan'),
    'title_item_page' => __('Item', 'tainacan'),
    'title_metadatum_page' => __('Metadata', 'tainacan'),
    'title_collection_activities' => __('Collection Activities', 'tainacan'),
    'title_filter_page' => __('Filter', 'tainacan'),
    'title_taxonomy_page' => __('Taxonomy', 'tainacan'),
    'title_term_edit' => __('Term edit', 'tainacan'),
    'title_term_creation' => __('Create a new term', 'tainacan'),
    'title_activity_page' => __('Activity', 'tainacan'),
    'title_create_collection' => __('Collection Creation', 'tainacan'),
    'title_create_taxonomy_page' => __('Taxonomy Creation', 'tainacan'),
    'title_create_item_collection' => __('Create Item on Collection', 'tainacan'),
    'title_create_filter' => __('Filter Creation', 'tainacan'),
    'title_collection_settings' => __('Settings of Collection', 'tainacan'),
    'title_edit_item' => __('Edit Item', 'tainacan'),
    'title_taxonomy_edit_page' => __('Edit Taxonomy', 'tainacan'),
    'title_filter_edit' => __('Edit Filter', 'tainacan'),
    'title_metadatum_edit' => __('Edit Metadata', 'tainacan'),
    'title_collection_metadata_edit' => __('Edit Metadata of', 'tainacan'),
    'title_collection_filters_edit' => __('Edit Filters of', 'tainacan'),
    'title_collection_reports' => __('Reports of', 'tainacan'),
    'title_importer_page' => __('Importer', 'tainacan'),
    'title_importer_mapping_page' => __("Metadata Mapping", 'tainacan'),
    'title_importers_page' => __('Importers', 'tainacan'),
    'title_export_collection_page' => __('Export Collection Page', 'tainacan'),
    'title_export_item_page' => __('Export Item', 'tainacan'),
    'title_exporters_page' => __('Exporters', 'tainacan'),
    'title_processes_page' => __('Processes', 'tainacan'),
    'title_item_bulk_add' => __('Bulk Add Items', 'tainacan'),
    'title_exporter_page' => __('Exporter', 'tainacan'),
    'title_collection_capabilities' => __('Capabilities Related to the Collection', 'tainacan'),
    'title_repository_capabilities' => __('Capabilities Related to the Repository', 'tainacan'),
    'title_slides-help-modal' => __('Welcome to the slides view mode', 'tainacan'),
    // Labels (used mainly on Aria Labels and Inputs)
    'label' => __('Label', 'tainacan'),
    /* translators: The verb to clear */
    'label_clean' => __('Clear', 'tainacan'),
    'label_none' => __('None', 'tainacan'),
    'label_clear_filters' => __('Clear filters', 'tainacan'),
    'label_and' => __('and', 'tainacan'),
    'label_selected' => __('Selected', 'tainacan'),
    'label_nothing_selected' => __('Nothing selected', 'tainacan'),
    'label_relationship_new_search' => __('New Search', 'tainacan'),
    'label_relationship_items_found' => __('Items found', 'tainacan'),
    'label_menu' => __('Menu', 'tainacan'),
    'label_main_menu' => __('Main Menu', 'tainacan'),
    'label_collection_menu' => __('Collection Menu', 'tainacan'),
    'label_title' => __('Title', 'tainacan'),
    'label_settings' => __('Settings', 'tainacan'),
    'label_actions' => __('Actions', 'tainacan'),
    'label_name' => __('Name', 'tainacan'),
    'label_description' => __('Description', 'tainacan'),
    'label_status' => __('Status', 'tainacan'),
    'label_slug' => __('Slug', 'tainacan'),
    'label_image' => __('Image', 'tainacan'),
    'label_thumbnail' => __('Thumbnail', 'tainacan'),
    'label_thumbnail_alt' => __('Alternative text', 'tainacan'),
    'label_empty_thumbnail' => __('Empty thumbnail', 'tainacan'),
    'label_empty_term_image' => __('Empty term image', 'tainacan'),
    'label_parent_collection' => __('Parent collection', 'tainacan'),
    'label_no_parent_collection' => __('No parent collection', 'tainacan'),
    'label_button_view' => __('Button View', 'tainacan'),
    'label_button_edit' => __('Button Edit', 'tainacan'),
    'label_button_delete' => __('Button Delete', 'tainacan'),
    'label_button_untrash' => __('Button Remove from Trash', 'tainacan'),
    'label_button_delete_header_image' => __('Button Delete Header Image', 'tainacan'),
    'label_button_edit_thumb' => __('Button Edit Thumbnail', 'tainacan'),
    'label_button_edit_header_image' => __('Button Edit Header Image', 'tainacan'),
    'label_button_edit_document' => __('Button Edit Document', 'tainacan'),
    'label_button_delete_document' => __('Button Delete Document', 'tainacan'),
    'label_choose_thumb' => __('Choose Thumbnail', 'tainacan'),
    'label_button_delete_thumb' => __('Button Delete Thumbnail', 'tainacan'),
    'label_collections_per_page' => __('Collections per Page:', 'tainacan'),
    'label_taxonomies_per_page' => __('Taxonomies per Page:', 'tainacan'),
    'label_terms_per_page' => __('Terms per Page:', 'tainacan'),
    'label_activities_per_page' => __('Activities per Page:', 'tainacan'),
    'label_items_per_page' => __('Items per Page:', 'tainacan'),
    'label_attachments_per_page' => __('Attachments per Page:', 'tainacan'),
    'label_processes_per_page' => __('Processes per Page:', 'tainacan'),
    'label_go_to_page' => __('Go to Page:', 'tainacan'),
    /* translators: 'Active' here refers to a mode that the metadata are, not a verb or action */
    'label_active_metadata' => __('Active Metadata', 'tainacan'),
    'label_available_metadata' => __('Available Metadata', 'tainacan'),
    'label_available_metadata_types' => __('Available Metadata Types', 'tainacan'),
    /* translators: 'Active' here refers to a mode that the filters are, not a verb or action */
    'label_active_filters' => __('Active Filters', 'tainacan'),
    'label_filter_type' => __('Filter Type', 'tainacan'),
    'label_available_filters' => __('Available Filters', 'tainacan'),
    'label_available_filter_types' => __('Available Filter Types', 'tainacan'),
    'label_per_page' => __('per Page', 'tainacan'),
    'label_displayed_metadata' => __('Displayed metadata', 'tainacan'),
    'label_required' => __('Required', 'tainacan'),
    'label_allow_multiple' => __('Allow multiple values', 'tainacan'),
    'label_default_value' => __('Default value', 'tainacan'),
    'label_unique_value' => __('Unique value across items', 'tainacan'),
    'label_yes' => __('Yes', 'tainacan'),
    'label_no' => __('No', 'tainacan'),
    'label_approved' => __('Approved', 'tainacan'),
    'label_collection_related' => __('Collection related', 'tainacan'),
    'label_metadata_for_search' => __('Metadata for search', 'tainacan'),
    'label_select_taxonomy' => __('Select taxonomy', 'tainacan'),
    'label_select_taxonomy_input_type' => __('Input type', 'tainacan'),
    'label_taxonomy_allow_new_terms' => __('Allow new terms', 'tainacan'),
    'label_selectbox_init' => __('Select', 'tainacan'),
    'label_insert_options' => __('Insertion options', 'tainacan'),
    'label_insert_items' => __('Insert items', 'tainacan'),
    'label_available_terms' => __('Available terms', 'tainacan'),
    'label_some_available_terms' => __('Some available terms', 'tainacan'),
    'label_attachments' => __('Attachments', 'tainacan'),
    'label_attachment' => __('Attachment', 'tainacan'),
    'label_enabled' => __('Enabled', 'tainacan'),
    'label_disabled' => __('Disabled', 'tainacan'),
    'label_creation' => __('Creation', 'tainacan'),
    'label_creation_date' => __('Creation date', 'tainacan'),
    'label_modification_date' => __('Modification date', 'tainacan'),
    'label_collection_items' => __('Collection Items', 'tainacan'),
    'label_collection_metadata' => __('Collection Metadata', 'tainacan'),
    'label_collection_filters' => __('Collection Filters', 'tainacan'),
    'label_parent_term' => __('Parent Term', 'tainacan'),
    'label_children_terms' => __('children terms', 'tainacan'),
    'label_new_term' => __('New Term', 'tainacan'),
    'label_create_new_term' => __('Create New Term', 'tainacan'),
    'label_create_and_select' => __('Create and Select', 'tainacan'),
    'label_new_child' => __('New Child', 'tainacan'),
    'label_taxonomy_terms' => __('Taxonomy Terms', 'tainacan'),
    'label_no_parent_term' => __('No parent term', 'tainacan'),
    'label_term_without_name' => __('Term without name', 'tainacan'),
    'label_inherited' => __('Inherited', 'tainacan'),
    /* translators: 'Core' as in 'main' - the most important and default title */
    'label_core_title' => __('Core Title', 'tainacan'),
    /* translators: 'Core' as in 'main' - the most important and default description */
    'label_core_description' => __('Core Description', 'tainacan'),
    /* translators: 'Core' as in 'main' - the most important and default section */
    'label_default_section' => __('Default Section', 'tainacan'),
    /* translators: noun, not the verb. This comes after options to be sorted by */
    'label_sorting' => __('Sorting', 'tainacan'),
    /* translators: noun, not the verb. This comes after options 'ASC' and 'DESC' */
    'label_sorting_direction' => __('Sorting direction', 'tainacan'),
    /* translators: shorter version of the 'sorting' noun label */
    'label_sort' => __('Sort', 'tainacan'),
    'label_activity_date' => __('Activity date', 'tainacan'),
    'label_activity_title' => __('Activity', 'tainacan'),
    'label_header_image' => __('Header Image', 'tainacan'),
    'label_empty_header_image' => __('Empty Header Image', 'tainacan'),
    'label_enable_cover_page' => __('Enable Cover Page', 'tainacan'),
    'label_cover_page' => __('Cover Page', 'tainacan'),
    'label_allow_comments' => __('Allow comments', 'tainacan'),
    'label_comments' => __('Comments', 'tainacan'),
    'label_default_displayed_metadata' => __('Default Displayed Metadata', 'tainacan'),
    'label_display' => __('Display on listing', 'tainacan'),
    'label_display_default' => __('Display by default', 'tainacan'),
    'label_display_never' => __('Never displayed', 'tainacan'),
    'label_not_display' => __('Do not display by default', 'tainacan'),
    'label_no_terms_selected' => __('No terms selected', 'tainacan'),
    'label_attach_to_item' => __('Attach to item', 'tainacan'),
    /* translators: Document is the main content of the Item. It can be a file, a url link or a text */
    'label_document' => __('Document', 'tainacan'),
    'label_document_empty' => __('Empty document', 'tainacan'),
    'label_document_content' => __('Document content', 'tainacan'),
    'label_file' => __('File', 'tainacan'),
    'label_text' => __('Text', 'tainacan'),
    'label_url' => __('URL', 'tainacan'),
    'label_select_file' => __('Select file', 'tainacan'),
    'label_selected_file' => __('Selected file', 'tainacan'),
    /* translators: Label for collapsible, accordeon-like elements */
    'label_expand_all' => __('Expand all', 'tainacan'),
    /* translators: Label for collapsible, accordeon-like elements */
    'label_collapse_all' => __('Collapse all', 'tainacan'),
    /* translators: Label for collapsible, accordeon-like elements */
    'label_expand' => __('Expand', 'tainacan'),
    /* translators: Label for collapsible, accordeon-like elements */
    'label_collapse' => __('Collapse', 'tainacan'),
    'label_view_term' => __('View Term', 'tainacan'),
    /* translators: 'Published' here refers to the sum of public and private items, not including draft or trashed ones. The are visible to those with permission */
    'label_all_items' => __('All items', 'tainacan'),
    'label_all_collections' => __('All collections', 'tainacan'),
    'label_authored_by_me' => __('Authored by me', 'tainacan'),
    'label_show_only_created_by_me' => __('Show only created by me', 'tainacan'),
    'label_collections_that_i_can_edit' => __('Collections that I can edit', 'tainacan'),
    'label_all_taxonomies' => __('All taxonomies', 'tainacan'),
    'label_bulk_actions' => __('Bulk actions', 'tainacan'),
    'label_delete_selected_collections' => __('Delete selected collections', 'tainacan'),
    'label_edit_selected_collections' => __('Edit selected collections', 'tainacan'),
    'label_delete_permanently' => __('Delete permanently', 'tainacan'),
    'label_send_to_trash' => __('Send to trash', 'tainacan'),
    'label_keep_on_trash' => __('Keep on trash', 'tainacan'),
    'label_delete_selected_taxonomies' => __('Delete selected taxonomies', 'tainacan'),
    'label_view_only_selected_items' => __('View only selected items', 'tainacan'),
    'label_bulk_edit_selected_items' => __('Bulk edit selected items', 'tainacan'),
    'label_sequence_edit_selected_items' => __('Edit selected items in sequence', 'tainacan'),
    'label_edit_selected_taxonomies' => __('Edit selected taxonomies', 'tainacan'),
    'label_select_all_collections_page' => __('Select all collections on page', 'tainacan'),
    'label_select_all_items_page' => __('Select all items on page', 'tainacan'),
    'label_select_all_taxonomies_page' => __('Select all taxonomies on page', 'tainacan'),
    'label_select_all_processes_page' => __('Select all processes on page', 'tainacan'),
    'label_select_all_terms' => __('Select all taxonomy terms', 'tainacan'),
    'label_all_terms_selected' => __('All terms selected', 'tainacan'),
    /* translators: this refers to attachments */
    'label_add_or_update' => __('Add or update', 'tainacan'),
    'label_blank_collection' => __('Blank collection', 'tainacan'),
    /* translators: the metadata scheme https://dublincore.org/ */
    'label_dublin_core' => __('Dublin Core', 'tainacan'),
    'label_created_by' => __('Created by', 'tainacan'),
    'label_apply_changes' => __('Apply changes', 'tainacan'),
    /* translators: view here is the noun, not the verb */
    'label_view_mode' => __('View mode', 'tainacan'),
    'label_default_view_mode' => __('Default view mode', 'tainacan'),
    'label_enabled_view_modes' => __('Enabled view modes', 'tainacan'),
    'label_view_modes_available' => __('View modes available', 'tainacan'),
    'label_warning' => __('Warning', 'tainacan'),
    'label_error' => __('Error', 'tainacan'),
    'label_thumbnails' => __('Thumbnails', 'tainacan'),
    /* translators: The 'thumbnails' view mode type, previously named 'Grid' */
    'label_grid' => __('Thumbnails', 'tainacan'),
    'label_table' => __('Table', 'tainacan'),
    'label_cards' => __('Cards', 'tainacan'),
    'label_mosaic' => __('Mosaic', 'tainacan'),
    /* translators: The 'records' view mode, in the sense of a catalog file */
    'label_records' => __('Records', 'tainacan'),
    'label_masonry' => __('Masonry', 'tainacan'),
    /* translators: The 'list' view mode, an horizontal, full width version of the 'records' view mode */
    'label_list' => __('List', 'tainacan'),
    /* translators: label for the view modes dropdown, for example: Visualization - table */
    'label_visualization' => __('Visualization', 'tainacan'),
    /* translators: This should be a short term for 'visualization' such as 'View on - table' referring to the view modes dropdown */
    'label_view_on' => __('View on', 'tainacan'),
    'label_available_importers' => __('Available Importers', 'tainacan'),
    'label_target_collection' => __('Target Collection', 'tainacan'),
    /* translators: The collection into which the contents of a file will be imported */
    'label_source_file' => __('Source file', 'tainacan'),
    /* translators: The file from which the contents will be imported to a collection */
    'label_url_source_link' => __('URL Source link', 'tainacan'),
    /* translators: The link to the file from which the contents will be imported to a collection */
    'label_metadata_mapping' => __('Metadata mapping', 'tainacan'),
    'label_select_metadatum' => __('Select metadatum', 'tainacan'),
    'label_create_metadatum' => __('Create metadatum', 'tainacan'),
    'label_create_repository_metadata' => __('Create repository metadata', 'tainacan'),
    'label_select_metadatum_type' => __('Select a metadatum type', 'tainacan'),
    'label_add_more_metadata' => __('Add more metadata', 'tainacan'),
    /* translators: Header of the column where the metadata columns from file are in the Mapping Metadata Importer screen */
    'label_from_source_collection' => __('From source file', 'tainacan'),
    /* translators: Header of the column where the existing collection metadata are in the Mapping Metadata Importer screen */
    'label_to_target_collection' => __('To target collection', 'tainacan'),
    'label_from_source_mapper' => __('This collection metadata', 'tainacan'),
    'label_to_target_mapper' => __('Will be mapped as this metadata', 'tainacan'),
    'label_add_value' => __('Add value', 'tainacan'),
    'label_remove_value' => __('Remove value', 'tainacan'),
    'label_create_new_page' => __('Create new page', 'tainacan'),
    'label_total_items' => __('Total items', 'tainacan'),
    'label_total_terms' => __('Total terms', 'tainacan'),
    'label_view_all' => __('View all', 'tainacan'),
    'label_until' => __('until', 'tainacan'),
    'label_visibility' => __('Visibility', 'tainacan'),
    'label_discard' => __('Discard', 'tainacan'),
    'label_save_as_draft' => __('Save as draft', 'tainacan'),
    'label_update_draft' => __('Update draft', 'tainacan'),
    'label_send_to_review' => __('Send to review', 'tainacan'),
    'label_update_pending' => __('Update as pending', 'tainacan'),
    'label_return_to_pending' => __('Return to pending', 'tainacan'),
    /* translators: The verb 'to publish' not the 'publish' status */
    'label_verb_publish' => _x('Publish', 'verb', 'tainacan'),
    'label_verb_publish_privately' => _x('Publish privately', 'verb', 'tainacan'),
    /* translators: The status 'publish' not the verb 'to publish' */
    'label_publish' => _x('Publish', 'noun', 'tainacan'),
    'label_update' => __('Update', 'tainacan'),
    'label_mapper_metadata' => __('Metadata Mapper', 'tainacan'),
    'label_add_more_mapper_metadata' => __('Add more metadata mappers', 'tainacan'),
    /* translators: 'Exposer' here is not the same as 'Exporter'. These are links where you can see the items in different file formats such as CSV, JSON, etc, but not a download of a zip file. */
    'label_exposer_urls' => __('Exposer URLs', 'tainacan'),
    'label_exposer_mapper_values' => __('values only, no metadata scheme', 'tainacan'),
    /* translators: 'Exposer' here is not the same as 'Exporter'. These are links where you can see the items in different file formats such as CSV, JSON, etc, but not a download of a zip file. */
    'label_exposer' => __('exposer', 'tainacan'),
    'label_mapper' => __('mapper', 'tainacan'),
    'label_no_details_of_process' => __('There are no details about this process', 'tainacan'),
    'label_queued_on' => __('Queued on:', 'tainacan'),
    'label_last_processed_on' => __('Last processed on:', 'tainacan'),
    'label_progress' => __('Progress', 'tainacan'),
    'label_process_completed' => __('Process completed', 'tainacan'),
    'label_process_completed_with_errors' => __('Process completed with errors', 'tainacan'),
    'label_process_cancelled' => __('Process cancelled by user', 'tainacan'),
    'label_process_paused' => __('Process paused', 'tainacan'),
    'label_process_waiting' => __('Process waiting', 'tainacan'),
    'label_stop_process' => __('Stop process', 'tainacan'),
    'label_delete_process' => __('Delete process', 'tainacan'),
    'label_process_failed' => __('Process failed', 'tainacan'),
    'label_max_options_to_show' => __('Max options to show', 'tainacan'),
    'label_unnamed_process' => __('Unnamed process', 'tainacan'),
    'loading_processes' => __('Loading processes', 'tainacan'),
    'label_semantic_uri' => __('Semantic Uri', 'tainacan'),
    'label_view_collection_on_website' => __('View collection on website', 'tainacan'),
    'label_view_collections_on_website' => __('View collections on website', 'tainacan'),
    'label_view_items_on_website' => __('View items on website', 'tainacan'),
    'label_view_taxonomies_on_website' => __('View taxonomies on website', 'tainacan'),
    'label_view_more' => __('View more', 'tainacan'),
    'label_log_file' => __('Log file', 'tainacan'),
    'label_error_log_file' => __('Error Log file', 'tainacan'),
    'label_import_items' => __('Import items', 'tainacan'),
    'label_hide_filters' => __('Hide filters panel', 'tainacan'),
    'label_show_filters' => __('Show filters panel', 'tainacan'),
    'label_select_all_items' => __('Select all items', 'tainacan'),
    'label_select_all' => __('Select all', 'tainacan'),
    'label_select_item' => __('Select item', 'tainacan'),
    'label_untrash_selected_items' => __('Restore from trash', 'tainacan'),
    'label_value_not_provided' => __('No value provided.', 'tainacan'),
    'label_description_not_provided' => __('No description provided.', 'tainacan'),
    'label_save_goto_metadata' => __('Save and Go to Metadata', 'tainacan'),
    'label_save_goto_filter' => __('Save and Go to Filters', 'tainacan'),
    'label_view_all_collections' => __('View all Collections', 'tainacan'),
    'label_view_on_theme' => __('View on Theme', 'tainacan'),
    'label_view_items_on_theme' => __('View all Items on Theme', 'tainacan'),
    'label_view_collections_on_theme' => __('View all Collections on Theme', 'tainacan'),
    'label_create_collection' => __('Create Collection', 'tainacan'),
    'label_hide_metadata' => __('Hide metadata', 'tainacan'),
    'label_show_metadata' => __('Show metadata', 'tainacan'),
    'label_all_terms' => __('All terms', 'tainacan'),
    'label_selected_terms' => __('Selected terms', 'tainacan'),
    'label_selected_term' => __('Selected term', 'tainacan'),
    'label_%s_selected_items' => __('%s selected items', 'tainacan'),
    'label_selected_item' => __('Selected item', 'tainacan'),
    'label_selected_items' => __('Selected items', 'tainacan'),
    'label_one_selected_item' => __('One selected item', 'tainacan'),
    'label_all_items_selected' => __('All items selected', 'tainacan'),
    /* translators: Here there is a number of items that are selected in this listing */
    'label_%s_on_this_page' => __('%s on this page', 'tainacan'),
    'label_all_metadatum_values' => __('All metadatum values', 'tainacan'),
    'label_selected_metadatum_values' => __('Selected metadatum values', 'tainacan'),
    /* translators: 'n.' here comes from 'number' */
    'label_editing_item_number' => __('Editing item n.', 'tainacan'),
    'label_sequence_editing_item' => __('Sequence editing: Item', 'tainacan'),
    /* translators: The amount of files remaining to be processed */
    'label_%s_files_remaining' => __('%s files remaining.', 'tainacan'),
    'label_one_file_remaining' => __('One file remaining.', 'tainacan'),
    'label_upload_file_prepare_items' => __('Uploading files and preparing items', 'tainacan'),
    'label_bulk_edit_items' => __('Bulk edit items', 'tainacan'),
    'label_sequence_edit_items' => __('Sequence edit items', 'tainacan'),
    'label_documents_upload' => __('Documents upload', 'tainacan'),
    'label_added_items' => __('Added items', 'tainacan'),
    'label_filters_from' => __('Filters from', 'tainacan'),
    'label_available_exporters' => __('Available Exporters', 'tainacan'),
    'label_source_collection' => __('Origin collection', 'tainacan'),
    'label_send_email' => __('Send me an email when done.', 'tainacan'),
    'label_urls' => __('URLs', 'tainacan'),
    'label_page' => __('Page', 'tainacan'),
    'label_item_page' => __('Item page', 'tainacan'),
    'label_activity_description' => __('Activity description', 'tainacan'),
    'label_activity_creation_date' => __('Activity creation date', 'tainacan'),
    'label_activity_author' => __('Activity author', 'tainacan'),
    'label_approbation' => __('Approbation', 'tainacan'),
    'label_urls_for_items_list' => __('URLs for Items List', 'tainacan'),
    'label_urls_for_item_page' => __('URLs for Item Page', 'tainacan'),
    'label_item_page_on_website' => __('Item page on website', 'tainacan'),
    'label_items_list_on_website' => __('Items list on website', 'tainacan'),
    'label_taxonomy_page_on_website' => __('Taxonomy page on website', 'tainacan'),
    'label_term_page_on_website' => __('Term page on website', 'tainacan'),
    'label_copy_link_url' => __('Copy link URL', 'tainacan'),
    'label_open_externally' => __('Open externally', 'tainacan'),
    'label_no_output_info' => __('No output info', 'tainacan'),
    'label_output' => __('Output', 'tainacan'),
    'label_no_mapping' => __('No mapping', 'tainacan'),
    /* translators: The sorting/order option 'desc'. A noun, not a verb */
    'label_descending' => __('Descending', 'tainacan'),
    /* translators: The sorting/order option 'asc'. A noun, not a verb */
    'label_ascending' => __('Ascending', 'tainacan'),
    /* translators: The slides/slideshow fullscreen view mode */
    'label_slides' => __('Slides', 'tainacan'),
    'label_items_list' => __('Items List', 'tainacan'),
    'label_items_list_results' => __('Items list results', 'tainacan'),
    'label_list_pagination' => __('List pagination', 'tainacan'),
    'label_sort_visualization' => __('Sorting and visualization control', 'tainacan'),
    'label_tainacan_api' => __('Tainacan API', 'tainacan'),
    'label_filter_type_preview' => __('Filter type preview', 'tainacan'),
    'label_metadatum_type_preview' => __('Metadatum type preview', 'tainacan'),
    'label_open_item_new_tab' => __('Open item in a new tab', 'tainacan'),
    'label_open_collection_new_tab' => __('Open collection in a new tab', 'tainacan'),
    'label_select_item' => __('Select item', 'tainacan'),
    'label_unselect_item' => __('Unselect item', 'tainacan'),
    'label_select_collection' => __('Select collection', 'tainacan'),
    'label_unselect_collection' => __('Unselect collection', 'tainacan'),
    'label_delete_item' => __('Delete item', 'tainacan'),
    'label_delete_collection' => __('Delete collection', 'tainacan'),
    'label_no_collections_using_taxonomy' => __('There is no collection using this taxonomy', 'tainacan'),
    'label_collections_using' => __('Collections using', 'tainacan'),
    /* translators: The amount of items selected */
    'label_select_all_%s_items' => __('Select all %s items', 'tainacan'),
    'label_next_page' => __('Next page', 'tainacan'),
    'label_previous_page' => __('Previous page', 'tainacan'),
    'label_page' => __('Page', 'tainacan'),
    'label_current_page' => __('Current page', 'tainacan'),
    'label_shrink_menu' => __('Shrink menu', 'tainacan'),
    'label_expand_menu' => __('Expand menu', 'tainacan'),
    'label_document_uploaded' => __('Document uploaded', 'tainacan'),
    /* translators: Filter of the repository, not a repository of filter! */
    'label_repository_filter' => __('Filter inherited from the repository', 'tainacan'),
    /* translators: Metadatum of the repository, not a repository of metadatum! */
    'label_repository_metadatum' => __('Metadatum inherited from the repository', 'tainacan'),
    /* translators: Metadata of the repository, not a repository of metadata! */
    'label_repository_metadata' => __('Repository metadata', 'tainacan'),
    /* translators: Filters of the repository, not a repository of filters! */
    'label_collection_filter' => __('Collection filter', 'tainacan'),
    'label_collection_metadatum' => __('Collection metadatum', 'tainacan'),
    'label_collection_capabilities' => __('Collection capabilities', 'tainacan'),
    'label_recover_from_trash' => __('Recover from trash', 'tainacan'),
    'label_show_children_terms' => __('Show children terms', 'tainacan'),
    'label_begin_slide_transition' => __('Begin slide transition', 'tainacan'),
    'label_pause_slide_transition' => __('Pause slide transition', 'tainacan'),
    'label_next_group_slides' => __('Next group of slides', 'tainacan'),
    'label_previous_group_slides' => __('Previous group of slides', 'tainacan'),
    'label_plugin_home_page' => __('Plugin home page', 'tainacan'),
    'label_wordpress_admin_page' => __('WordPress Admin Page', 'tainacan'),
    /* translators: Number of collections */
    'label_view_all_%s_collections' => __('View all %s collections', 'tainacan'),
    'label_view_collections_list' => __('View collections list', 'tainacan'),
    'label_comparator' => __('Comparator', 'tainacan'),
    'label_comparators' => __('Comparators', 'tainacan'),
    'label_table_of_items' => __('Table of Items', 'tainacan'),
    'label_create_another_item' => __('Create another item', 'tainacan'),
    'label_recent_collections' => __('Recent Collections', 'tainacan'),
    'label_%s_items_copy_success' => __('%s item copies were created with success!', 'tainacan'),
    'label_one_item_copy_success' => __('The item copy was created with success!', 'tainacan'),
    'label_item_copy_failure' => __('Something wrong happened... Item copy failed!', 'tainacan'),
    'label_preset_success' => __('The preset was applied with success!', 'tainacan'),
    'label_create_another_taxonomy' => __('Create another Taxonomy', 'tainacan'),
    'label_make_copies_of_item' => __('Make copies of item', 'tainacan'),
    'label_number_of_copies' => __('Number of copies', 'tainacan'),
    'label_return_to_list' => __('Return to list', 'tainacan'),
    'label_expose_only_displayed_metadata' => __('Expose only displayed metadata', 'tainacan'),
    'label_allowed' => __('Allowed', 'tainacan'),
    'label_not_allowed' => __('Not allowed', 'tainacan'),
    /* translators: Label for the button that opens the Exposers modal */
    'label_view_as' => __('View as...', 'tainacan'),
    'label_day' => __('Day', 'tainacan'),
    'label_month' => __('Month', 'tainacan'),
    'label_year' => __('Year', 'tainacan'),
    'label_related_to' => __('Related to', 'tainacan'),
    'label_user_roles' => __('User roles', 'tainacan'),
    'label_associated_roles' => __('Associated roles', 'tainacan'),
    'label_inherited_roles' => __('Inherited roles', 'tainacan'),
    'label_editing_capability' => __('Editing capability', 'tainacan'),
    'label_default_author_user' => __('Set the item author as default value', 'tainacan'),
    'label_create_new_item' => __('Create new item', 'tainacan'),
    'label_submit' => __('Submit', 'tainacan'),
    'label_know_more' => __('Know more', 'tainacan'),
    'label_request_details' => __('Request details', 'tainacan'),
    'label_root_terms' => __('Root terms', 'tainacan'),
    'label_see_on_fullscreen' => __('See on fullscreen slides', 'tainacan'),
    'label_slides_help' => __('Help with the slides view mode', 'tainacan'),
    /* translators: The 'SPACE' key on keyboard */
    'label_space_key' => __('SPACE', 'tainacan'),
    /* translators: The 'ESC' key on keyboard */
    'label_esc_key' => __('ESC', 'tainacan'),
    'label_view_selected_items_as' => __('View selected items as...', 'tainacan'),
    'label_actions_for_the_selection' => __('Actions for the selection', 'tainacan'),
    'label_upload_custom_thumbnail' => __('Upload custom thumbnail', 'tainacan'),
    'label_switch_document_type' => __('Switch document type', 'tainacan'),
    'label_sending_form' => __('Sending form...', 'tainacan'),
    'label_form_not_loaded' => __('This form could not be loaded', 'tainacan'),
    'label_terms_not_used' => __('Terms not used', 'tainacan'),
    'label_terms_used' => __('Terms used', 'tainacan'),
    'label_number_of_terms' => __('Number of terms', 'tainacan'),
    'label_number_of_items' => __('Number of items', 'tainacan'),
    'label_number_of_metadata' => __('Number of metadata', 'tainacan'),
    'label_usage_of_terms_per_taxonomy' => __('Usage of terms per taxonomy', 'tainacan'),
    'label_items_per_term' => __('Items per term', 'tainacan'),
    'label_items_per_term_from_taxonomy' => __('Items per term from taxonomy:', 'tainacan'),
    'label_items_per_term_from_taxonomy_metadatum' => __('Items per term from taxonomy metadatum:', 'tainacan'),
    'label_items_per_child_terms_of' => __('Items per child terms of:', 'tainacan'),
    'label_items_per_collection' => __('Items per collection', 'tainacan'),
    'label_loading_report' => __('Loading report...', 'tainacan'),
    'label_fill_distribution' => __('Filling distribution', 'tainacan'),
    'label_not_filled' => __('Not filled yet', 'tainacan'),
    'label_filled' => __('Already filled', 'tainacan'),
    /* translators: To be displayed with the number of Taxonomies not used in the collection */
    'label_not_used' => __('Not used', 'tainacan'),
    /* translators: To be displayed with the number of Taxonomies used in the collection */
    'label_select_a_taxonomy' => __('Select a taxonomy', 'tainacan'),
    'label_used' => __('Used', 'tainacan'),
    'label_select_a_taxonomy_metadatum' => __('Select a taxonomy metadatum', 'tainacan'),
    'label_items_with_this_metadatum_value' => __('Items with this metadatum value', 'tainacan'),
    'label_amount_of_items_per_metadatum_value' => __('Amount of items per metadatum value', 'tainacan'),
    'label_activities_during_year' => __('Activities during the year', 'tainacan'),
    'label_compact_list' => __('Compact list', 'tainacan'),
    'label_detailed_list' => __('Detailed list', 'tainacan'),
    'label_view_metadata_details' => __('View metadata details', 'tainacan'),
    'label_filter_by_metadata_type' => __('Filter by metadatum type', 'tainacan'),
    'label_filter_by_type' => __('Filter by type', 'tainacan'),
    'label_pie_chart' => __('Pie chart', 'tainacan'),
    'label_bar_chart' => __('Bar chart', 'tainacan'),
    'label_terms_per_page' => __('Terms per page', 'tainacan'),
    'label_anonymous_user' => __('Anonymous User', 'tainacan'),
    'label_select_a_year' => __('Select a year', 'tainacan'),
    'label_all_users' => __('All users', 'tainacan'),
    'label_activity_per_user' => __('Activities per user', 'tainacan'),
    'label_report_generated_on' => __('Report generated on', 'tainacan'),
    'label_get_latest_report' => __('Get latest report', 'tainacan'),
    'label_decrease' => __('Decrease', 'tainacan'),
    'label_increase' => __('Increase', 'tainacan'),
    'label_set_all_create_metadata' => __('Set every metadata to be created', 'tainacan'),
    'label_manage_collection' => __('Manage collection', 'tainacan'),
    'label_chart_controls' => __('Chart controls', 'tainacan'),
    'label_increase_zoom' => __('Increase zoom', 'tainacan'),
    'label_decrease_zoom' => __('Decrease zoom', 'tainacan'),
    'label_zoom_by_selection' => __('Zoom by selection', 'tainacan'),
    'label_pan_selection' => __('Pan selection', 'tainacan'),
    'label_reset_zoom' => __('Reset zoom', 'tainacan'),
    'label_chart_export_options' => __('Export options', 'tainacan'),
    'label_related_items' => __('Items related to this', 'tainacan'),
    'label_view_all_%s_related_items' => __('View all %s related items', 'tainacan'),
    'label_back_to_related_item' => __('Back to related item', 'tainacan'),
    'label_options_of_the_%s_metadata_type' => __('Options of the %s metadata type', 'tainacan'),
    'label_advanced_metadata_options' => __('Advanced metadata options', 'tainacan'),
    'label_maximum_of_%s_values' => __('maximum of %s values', 'tainacan'),
    'label_document_option_forced_iframe' => __('Render content in iframe', 'tainacan'),
    'label_amount_of_metadata_of_type' => __('Amount of metadata of this type', 'tainacan'),
    'label_document_option_iframe_height' => __('Iframe height (px)', 'tainacan'),
    'label_document_option_iframe_width' => __('Iframe width (px)', 'tainacan'),
    'label_document_option_is_image' => __('Is link to external image', 'tainacan'),
    'label_limit_max_values' => __('Limit the amount of multiple values', 'tainacan'),
    'label_items_selection' => __('Items selection', 'tainacan'),
    'label_default_orderby' => __('Initial default sorting', 'tainacan'),
    'label_focus_mode' => __('Focus mode', 'tainacan'),
    'label_start_focus_mode' => __('Start focus mode', 'tainacan'),
    'label_close_search' => __('Close search', 'tainacan'),
    'label_remove_all_criteria' => __('Remove all criteria', 'tainacan'),
    'label_string_to_search_for' => __('String to search for', 'tainacan'),
    'label_number_to_search_for' => __('Number to search for', 'tainacan'),
    'label_date_to_search_for' => __('Date to search for', 'tainacan'),
    'label_criterion_to_compare' => __('Criterion to compare', 'tainacan'),
    'label_metadata_and_sections' => __('Metadata and Sections', 'tainacan'),
    'label_metadata_%s_and_sections_%s' => __('Metadata (%1$s) and Sections (%2$s)', 'tainacan'),
    'label_view_activity_logs' => __('View activity logs', 'tainacan'),
    'label_item_activities' => __('Item activities', 'tainacan'),
    'label_add_new_section' => __('Add new section', 'tainacan'),
    'label_new_metadata_section' => __('New metadata section', 'tainacan'),
    'label_show_details' => __('Show details', 'tainacan'),
    'label_hide_details' => __('Hide details', 'tainacan'),
    'label_move_up' => __('Move up', 'tainacan'),
    'label_move_down' => __('Move down', 'tainacan'),
    'label_view_modes_public_list' => __('Items view modes in the public list', 'tainacan'),
    'label_default' => __('Default', 'tainacan'),
    'label_tainacan_mobile_panel' => __('Tainacan Mobile Panel', 'tainacan'),
    'label_close_panel' => __('Close panel', 'tainacan'),
    'label_shortcuts' => __('Shortcuts', 'tainacan'),
    'label_all_metadata' => __('All metadata', 'tainacan'),
    'label_document_and_thumbnail' => __('Document and thumbnail', 'tainacan'),
    'label_all_attachments' => __('All attachments', 'tainacan'),
    'label_only_required_metadata' => __('Only required metadata', 'tainacan'),
    'label_update_as_public' => __('Update as public', 'tainacan'),
    'label_update_as_private' => __('Update as private', 'tainacan'),
    'label_change_to_private' => __('Change to private', 'tainacan'),
    'label_change_to_draft' => __('Change to draft', 'tainacan'),
    'label_create_item' => __('Create item', 'tainacan'),
    'label_ready_to_create_item' => __('Ready to create this item?', 'tainacan'),
    'label_only_required' => __('Only required', 'tainacan'),
    'label_create_collection_from_mapper' => __('Create a new collection from a mapper', 'tainacan'),
    'label_create_collection_from_preset' => __('Create a preset collection', 'tainacan'),
    'label_preset_collections' => __('Preset collections', 'tainacan'),
    'label_from_a_mapper' => __('From a metadata mapper', 'tainacan'),
    'label_using_a_preset' => __('Using a preset', 'tainacan'),
    'label_use_search_separated_words' => __('Search each word separatelly', 'tainacan'),
    'label_map' => __('Map', 'tainacan'),
    'label_show_item_location_on_map' => __('Show item location on map', 'tainacan'),
    /* translators: This appears before a select where you choose possible geocoordinate metadata */
    'label_showing_locations_for' => __('Showing locations for:', 'tainacan'),
    'label_one_selected_location' => __('One location selected', 'tainacan'),
    'label_%s_selected_locations' => __('%s locations selected', 'tainacan'),
    'label_update_parent' => __('Change parent term', 'tainacan'),
    'label_select_child_terms_long' => __('Select all child terms', 'tainacan'),
    /* translators: This relates to taxonomy terms selections. It is a shorter version of "select all child terms" */
    'label_select_child_terms_short' => __('All children', 'tainacan'),
    'label_select_root_terms_long' => __('Select all root terms', 'tainacan'),
    /* translators: This relates to taxonomy terms selections. It is a shorter version of "select all root terms" */
    'label_select_root_terms_short' => __('All root terms', 'tainacan'),
    'label_all_root_terms_selected' => __('All root terms selected', 'tainacan'),
    'label_terms_child_of_%s_selected' => __('Terms child of %s selected', 'tainacan'),
    'label_remove_selected_term' => __('Remove only the selected term', 'tainacan'),
    'label_remove_term_and_descendants' => __('Remove term and its descendants', 'tainacan'),
    'label_remove_selected_terms' => __('Remove only selected terms', 'tainacan'),
    'label_remove_terms_and_descendants' => __('Remove terms and their descendants', 'tainacan'),
    'label_%s_selected_terms' => __('%s terms selected', 'tainacan'),
    'label_one_selected_term' => __('One term selected', 'tainacan'),
    'label_no_parent_root_term' => __('No parent (set as root term)', 'tainacan'),
    /* translators: This relates to terms that are in use by some item via a taxonomy. */
    'label_used_by_items' => __('In use by some item', 'tainacan'),
    'label_multiple_terms_insertion' => __('Multiple terms insertion', 'tainacan'),
    'label_multiple_terms' => __('Multiple terms', 'tainacan'),
    'label_multiple' => __('Multiple', 'tainacan'),
    'label_separator' => __('Separator', 'tainacan'),
    'label_loading_items' => __('Loading items...', 'tainacan'),
    'label_items_list_options' => __('Items list options', 'tainacan'),
    'label_item_edition_form_options' => __('Item edition form options', 'tainacan'),
    'label_item_submission_options' => __('Item submission options', 'tainacan'),
    'label_metadata_related_features' => __('Metadata related features', 'tainacan'),
    'label_preview' => __('Preview', 'tainacan'),
    'label_go_to_permalinks' => __('Go to permalinks', 'tainacan'),
    'label_publication_data' => __('Publication data', 'tainacan'),
    'label_authorship' => __('Authorship', 'tainacan'),
    'label_editing_publication_authorship' => __('Publication authorship editing', 'tainacan'),
    'label_range_of_dates' => __('Range of dates', 'tainacan'),
    'label_view_processes' => __('View processes', 'tainacan'),
    'label_collections_taxonomies' => __('Collection taxonomies', 'tainacan'),
    // Instructions. More complex sentences to guide user and placeholders
    'instruction_delete_selected_collections' => __('Delete selected collections', 'tainacan'),
    'instruction_delete_selected_items' => __('Delete selected items', 'tainacan'),
    'instruction_delete_selected_taxonomies' => __('Delete selected taxonomies', 'tainacan'),
    'instruction_image_upload_box' => __('Drop an image here or click to upload.', 'tainacan'),
    'instruction_select_a_status' => __('Select a status:', 'tainacan'),
    'instruction_select_a_status2' => __('Select a status', 'tainacan'),
    'instruction_select_a_comments_status' => __('Select a comments status', 'tainacan'),
    'instruction_click_to_select_a_filter_type' => __('Click to select a filter type:', 'tainacan'),
    'instruction_select_a_parent_term' => __('Select a parent term:', 'tainacan'),
    'instruction_select_a_metadatum' => __('Select a metadatum', 'tainacan'),
    'instruction_cover_page' => __('Search a Page to choose.', 'tainacan'),
    'instruction_type_search_users' => __('Search users...', 'tainacan'),
    'instruction_type_search_users_filter' => __('Search users to filter...', 'tainacan'),
    'instruction_type_search_metadata_filter' => __('Search metadata to filter...', 'tainacan'),
    'instruction_type_search_filter_filter' => __('Search filters to filter...', 'tainacan'),
    'instruction_type_search_roles_filter' => __('Search roles to filter...', 'tainacan'),
    'instruction_select_a_parent_collection' => __('Select a parent collection.', 'tainacan'),
    'instruction_select_collection_thumbnail' => __('Select a thumbnail image for collection', 'tainacan'),
    'instruction_select_item_thumbnail' => __('Select a thumbnail image for item', 'tainacan'),
    'instruction_select_collection_header_image' => __('Select a header image for collection', 'tainacan'),
    'instruction_select_term_header_image' => __('Select a header image for term', 'tainacan'),
    'instruction_select_files_to_attach_to_item' => __('Select files to attach to item', 'tainacan'),
    'instruction_select_document_file_for_item' => __('Select a document file for item', 'tainacan'),
    'instruction_insert_url' => __('Insert URL', 'tainacan'),
    'instruction_write_text' => __('Write Text', 'tainacan'),
    /* translators: The verb to search, used in search box placeholder */
    'instruction_search' => __('Search', 'tainacan'),
    /* translators: The verb to search, used in search box placeholder */
    'instruction_search_in_repository' => __('Search in repository', 'tainacan'),
    'instruction_select_a_target_collection' => __('Select a target collection.', 'tainacan'),
    'instruction_select_a_mapper' => __('Select a mapper', 'tainacan'),
    'instruction_select_an_importer_type' => __('Select an importer from the options below:', 'tainacan'),
    'instruction_drop_file_or_click_to_upload' => __('Drop your source file or click here to upload.', 'tainacan'),
    'instruction_select_metadatum_type' => __('Select a metadatum type', 'tainacan'),
    'instruction_configure_new_metadatum' => __('Configure new metadatum', 'tainacan'),
    'instruction_configure_the_metadatum' => __('Configure the metadatum', 'tainacan'),
    'instruction_configure_new_metadata_section' => __('Configure new metadata section', 'tainacan'),
    'instruction_configure_the_metadata_section' => __('Configure the metadata section', 'tainacan'),
    'instruction_insert_mapper_metadatum_info' => __('Insert the new mapper\'s metadatum info', 'tainacan'),
    'instruction_select_max_options_to_show' => __('Select maximum options to show', 'tainacan'),
    'instruction_select_collection_fetch_items' => __('Select a collection to fetch items', 'tainacan'),
    'instruction_select_a_action' => __('Select an action', 'tainacan'),
    'instruction_parent_term' => __('Type to search a Parent Term to choose.', 'tainacan'),
    'instruction_type_existing_item' => __('Type to add an existing item...', 'tainacan'),
    'instruction_type_existing_term' => __('Type to add an existing term...', 'tainacan'),
    'instruction_select_an_exporter_type' => __('Select an exporter from the options below:', 'tainacan'),
    'instruction_select_a_collection' => __('Select a collection', 'tainacan'),
    'instruction_hover_a_filter_type_to_preview' => __('Hover a filter type to preview', 'tainacan'),
    'instruction_never_show_message_again' => __('Never show me this message again', 'tainacan'),
    'instruction_click_or_drag_filter_create' => __('Click or drag and drop to create a new filter', 'tainacan'),
    'instruction_click_or_drag_metadatum_create' => __('Click or drag and drop to create a new metadatum', 'tainacan'),
    'instruction_drag_and_drop_filter_sort' => __('Drag and drop to change filter order', 'tainacan'),
    'instruction_drag_and_drop_metadatum_sort' => __('Drag and drop to change metadatum order', 'tainacan'),
    'instruction_drag_and_drop_metadata_sections_sort' => __('Drag and drop to change metadata sections order', 'tainacan'),
    'instruction_select_a_date' => __('Select a date', 'tainacan'),
    'instruction_select_a_month' => __('Select a month', 'tainacan'),
    'instruction_type_value_year' => __('Type year value', 'tainacan'),
    'instruction_select_the_amount_of_copies' => __('Select the amount of copies of the item that you want to create', 'tainacan'),
    'instruction_select_a_interval' => __('Select an interval', 'tainacan'),
    'instruction_select_title_mapping' => __('Before running import, consider selecting the title source metadata', 'tainacan'),
    'instruction_click_error_to_go_to_metadata' => __('Click on the error to go to the metadata:', 'tainacan'),
    'instruction_click_to_see_or_search' => __('Click to see options or type to search...', 'tainacan'),
    'instruction_select_one_or_more_collections' => __('Select one or more collections', 'tainacan'),
    'instruction_thumbnail_alt' => __('Type here a descriptive text for the image thumbnail...', 'tainacan'),
    'instruction_click_to_see_%s_child_terms' => __('Click to see %s child terms', 'tainacan'),
    'instruction_click_to_see_%s_child_term' => __('Click to see %s child term', 'tainacan'),
    'instruction_click_to_load_filter' => __('Click to load the filter', 'tainacan'),
    'instruction_collection_description' => __('Enter the collection description here...', 'tainacan'),
    'instruction_collection_name' => __('Enter the collection name here...', 'tainacan'),
    'instruction_click_to_easily_see' => __('Click to easily see', 'tainacan'),
    'instruction_create_item_select_status' => __('Select a status for the item visiblity on the site. Remember, whichever you select will still be restricted by the collection status as well.', 'tainacan'),
    'instruction_edit_item_status' => __('To alter the item status, select a different update strategy in the footer below.', 'tainacan'),
    /* translators: At the end of this sentence there will be a search query typed by the user wrapped in quotes. */
    'instruction_press_enter_to_search_for' => __('Press <kbd>ENTER</kbd> to search for', 'tainacan'),
    'instruction_type_geocoordinate' => __('Type a geo coordinate in the form of lat,lng', 'tainacan'),
    'instruction_click_to_add_a_point' => __('Drag to reposition or click to insert a marker', 'tainacan'),
    'instruction_select_geocoordinate_metadatum' => __('Select a geocoordinate metadatum', 'tainacan'),
    'instruction_multiple_terms_insertion' => __('Type or paste here a list of names using a separator to create multiple terms at once.', 'tainacan'),
    'instruction_select_second_date_to_compare' => __('Select the second date metadatum', 'tainacan'),
    'instruction_select_second_numeric_to_compare' => __('Select the second numeric metadatum', 'tainacan'),
    'instruction_create_item_before_change_author' => __('Please create the item first before changing its author.', 'tainacan'),
    'instruction_create_item_before_change_slug' => __('Please create the item first before changing its slug.', 'tainacan'),
    'instruction_create_item_before_change_status' => __('Please create the item first to define its status.', 'tainacan'),
    'instruction_filter_processes_date' => __('Select the date range for the processes', 'tainacan'),
    'instruction_go_to_metadata_mapping_%s' => __('To use a mapping standard, you must first configure it in the <a href="%s" target="_blank">collection metadata mapping screen</a>.', 'tainacan'),
    'instruction_2_or_more' => __('2 or more', 'tainacan'),
    // Info. Other feedback to user.
    'info_items_tab_all' => __('Every item, except by those sent to trash.', 'tainacan'),
    'info_items_tab_publish' => __('Only items that are visible to everyone.', 'tainacan'),
    'info_items_tab_private' => __('Items visible only to editors.', 'tainacan'),
    'info_items_tab_draft' => __('Draft items, not published.', 'tainacan'),
    'info_items_tab_pending' => __('Pending items, not published.', 'tainacan'),
    'info_items_tab_trash' => __('Items that were sent to trash.', 'tainacan'),
    'info_collections_tab_all' => __('Every published collection, including those visible only to editors.', 'tainacan'),
    'info_collections_tab_publish' => __('Only collections that are visible to everyone.', 'tainacan'),
    'info_collections_tab_private' => __('Collections visible only to editors.', 'tainacan'),
    'info_collections_tab_draft' => __('Draft collections, not published.', 'tainacan'),
    'info_collections_tab_pending' => __('Pending collections, not published.', 'tainacan'),
    'info_collections_tab_trash' => __('Collections that were sent to trash.', 'tainacan'),
    'info_taxonomies_tab_all' => __('Every taxonomy, except by those sent to trash.', 'tainacan'),
    'info_taxonomies_tab_publish' => __('Only taxonomies that are visible to everyone.', 'tainacan'),
    'info_taxonomies_tab_private' => __('Taxonomies visible only to editors.', 'tainacan'),
    'info_taxonomies_tab_draft' => __('Draft taxonomies, not published.', 'tainacan'),
    'info_taxonomies_tab_pending' => __('Pending taxonomies, not published.', 'tainacan'),
    'info_taxonomies_tab_trash' => __('Taxonomies that were sent to trash.', 'tainacan'),
    'info_error_invalid_date' => __('Invalid date', 'tainacan'),
    'info_search_results' => __('Advanced Search Results', 'tainacan'),
    'info_search_criteria' => __('Advanced Search Criteria', 'tainacan'),
    'info_name_is_required' => __('Name is required.', 'tainacan'),
    'info_no_collection_created' => __('No collection was created in this repository.', 'tainacan'),
    'info_no_items_publish' => __('No public items found.', 'tainacan'),
    'info_no_items_private' => __('No private items found.', 'tainacan'),
    'info_no_items_draft' => __('No draft items found.', 'tainacan'),
    'info_no_items_trash' => __('No items found on trash.', 'tainacan'),
    'info_no_items_pending' => __('No pending items found.', 'tainacan'),
    'info_no_collections_publish' => __('No public collections found.', 'tainacan'),
    'info_no_collections_private' => __('No private collections found.', 'tainacan'),
    'info_no_collections_draft' => __('No draft collections found.', 'tainacan'),
    'info_no_collections_pending' => __('No pending collections found.', 'tainacan'),
    'info_no_collections_trash' => __('No collections found on trash.', 'tainacan'),
    'info_no_taxonomies_publish' => __('No public taxonomies found.', 'tainacan'),
    'info_no_taxonomies_private' => __('No private taxonomies found.', 'tainacan'),
    'info_no_taxonomies_draft' => __('No draft taxonomies found.', 'tainacan'),
    'info_no_taxonomies_pending' => __('No pending taxonomies found.', 'tainacan'),
    'info_no_taxonomies_trash' => __('No taxonomies found on trash.', 'tainacan'),
    'info_item_publish' => __('This item is published as public and will be visible to any visitor on the website, as long as its collection is also public.', 'tainacan'),
    'info_item_private' => __('This item is published as private and will be visible only for editors with the required capabilities.', 'tainacan'),
    'info_item_pending' => __('This item is pending review and will be published only for editors with the required capabilities.', 'tainacan'),
    'info_item_draft' => __('This item is a draft and will be visible only for editors with the required capabilities. Also, in this state, no validation for required metadata is performed.', 'tainacan'),
    'info_item_trash' => __('This item is on trash and will not be visible outside of the admin panel.', 'tainacan'),
    'info_no_taxonomy_created' => __('No taxonomy was created in this repository.', 'tainacan'),
    'info_no_terms_created_on_taxonomy' => __('No term was created for this taxonomy.', 'tainacan'),
    'info_no_terms_found' => __('No term was found here', 'tainacan'),
    'info_no_more_terms_found' => __('No more terms found', 'tainacan'),
    'info_no_item_created' => __('No item was created so far.', 'tainacan'),
    'info_no_item_found_by_you' => __('No items authored by you were found.', 'tainacan'),
    'info_no_page_found' => __('No page was found with this name.', 'tainacan'),
    'info_no_user_found' => __('No user was found with this name.', 'tainacan'),
    'info_no_item_found_filter' => __('No item was found here with these filters.', 'tainacan'),
    'info_no_item_found' => __('No item was found.', 'tainacan'),
    'info_no_item_authored_by_you_found' => __('No item authored by you was found.', 'tainacan'),
    'info_item_not_saved' => __('Warning: Item not saved.', 'tainacan'),
    'info_no_associated_role' => __('No associated role.', 'tainacan'),
    'info_error_deleting_collection' => __('Error on deleting collection.', 'tainacan'),
    'info_error_deleting_taxonomy' => __('Error on deleting taxonomy', 'tainacan'),
    'info_error_first_value_greater' => __('First value should be lower than second value', 'tainacan'),
    'info_error_value_must_be_number' => __('Value should be a number', 'tainacan'),
    'info_error_upload' => __('Error while uploading files.', 'tainacan'),
    'info_collection_deleted' => __('Collection deleted.', 'tainacan'),
    'info_item_deleted' => __('Item deleted.', 'tainacan'),
    'info_taxonomy_deleted' => __('Taxonomy deleted', 'tainacan'),
    'info_warning_attachment_delete' => __('Do you really want to delete this attachment?', 'tainacan'),
    'info_warning_collection_delete_%s' => __('Do you really want to permanently delete the collection "%s"?', 'tainacan'),
    'info_warning_collection_trash_%s' => __('Do you really want to trash the collection "%s"?', 'tainacan'),
    'info_warning_filter_delete_%s' => __('Do you really want to delete the filter "%s"?', 'tainacan'),
    'info_warning_item_delete' => __('Do you really want to permanently delete this item?', 'tainacan'),
    'info_warning_item_delete_%s' => __('Do you really want to permanently delete the item "%s"?', 'tainacan'),
    'info_warning_remove_item_from_trash' => __('Do you really want to remove this item from trash?', 'tainacan'),
    'info_warning_item_trash' => __('Do you really want to trash this item?', 'tainacan'),
    'info_warning_item_trash_%s' => __('Do you really want to trash the item "%s"?', 'tainacan'),
    'info_warning_metadatum_delete_%s' => __('Do you really want to permanently delete the metadatum "%s"?', 'tainacan'),
    'info_warning_metadata_section_delete_%s' => __('Do you really want to permanently delete the metadata section "%s"?', 'tainacan'),
    'info_warning_taxonomy_delete_%s' => __('Do you really want to delete the taxonomy "%s"?', 'tainacan'),
    'info_warning_selected_collections_delete' => __('Do you really want to permanently delete the selected collections?', 'tainacan'),
    'info_warning_selected_collections_trash' => __('Do you really want to trash the selected collections?', 'tainacan'),
    'info_warning_selected_items_delete' => __('Do you really want to permanently delete the selected items?', 'tainacan'),
    'info_warning_selected_items_trash' => __('Do you really want to trash the selected items?', 'tainacan'),
    'info_warning_selected_taxonomies_delete' => __('Do you really want to delete the selected taxonomies?', 'tainacan'),
    'info_warning_collection_related' => __('The metadata Collection related is required', 'tainacan'),
    'info_warning_no_metadata_found' => __('No metadata found in this collection', 'tainacan'),
    /* translators: This is displayed before sentences like "Showing items 2 to 8 of 12 */
    'info_showing_items' => __('Showing items ', 'tainacan'),
    'info_showing_attachments' => __('Showing attachments ', 'tainacan'),
    /* translators: This is displayed before sentences like "Showing attachments 2 to 8 of 12 */
    'info_showing_%s' => __('Showing %s ', 'tainacan'),
    'info_showing_collections' => __('Showing collections ', 'tainacan'),
    'info_showing_taxonomies' => __('Showing taxonomies ', 'tainacan'),
    'info_showing_activities' => __('Showing activities ', 'tainacan'),
    'info_showing_processes' => __('Showing processes ', 'tainacan'),
    'info_showing_capabilities' => __('Showing capabilities ', 'tainacan'),
    'info_no_capabilities_found' => __('No capabilities found.', 'tainacan'),
    'info_no_role_associated_capability' => __('No role associated to this capability', 'tainacan'),
    'info_associated_roles' => __('These are the roles that have this capability set. You may add or remove the capability to customize the role.', 'tainacan'),
    'info_inherited_roles' => __('These are the roles that have greater capabilities, which inherit this one. You cannot edit this as it will not have precedence over the greater capability.', 'tainacan'),
    'info_showing_terms' => __('Showing terms ', 'tainacan'),
    'info_warning_remove_from_trash_first' => __('Remove this item from trash first', 'tainacan'),
    /* translators: This is displayed before sentences like "Showing items 2 to 8 of 12 */
    'info_to' => __(' to ', 'tainacan'),
    /* translators: This is displayed before sentences like "Showing items 2 to 8 of 12 */
    'info_of' => __(' of ', 'tainacan'),
    'info_created_by' => __('Created by: ', 'tainacan'),
    'info_by' => __('By: ', 'tainacan'),
    'info_date' => __('Date: ', 'tainacan'),
    'info_modification_date' => __('Last modification date: ', 'tainacan'),
    'info_not_saved' => __('Not saved ', 'tainacan'),
    'info_warning_item_not_saved' => __('Are you sure? The item is not saved, changes will be lost.', 'tainacan'),
    'info_warning_metadata_not_saved' => __('Are you sure? There are metadata not saved, changes will be lost.', 'tainacan'),
    'info_warning_filters_not_saved' => __('Are you sure? There are filters not saved, changes will be lost.', 'tainacan'),
    'info_no_description_provided' => __('No description provided.', 'tainacan'),
    'info_warning_taxonomy_not_saved' => __('Are you sure? The taxonomy is not saved, changes will be lost.', 'tainacan'),
    'info_warning_terms_not_saved' => __('Are you sure? There are terms not saved, changes will be lost.', 'tainacan'),
    'info_no_activities' => __('No activities found.', 'tainacan'),
    'info_logs_before' => __('Before', 'tainacan'),
    'info_logs_after' => __('After', 'tainacan'),
    'info_there_is_no_metadatum' => __('There is no metadata here yet.', 'tainacan'),
    'info_there_is_no_metadata_section' => __('There is no metadata section here yet.', 'tainacan'),
    'info_there_is_no_filter' => __('There is no filter here yet.', 'tainacan'),
    'info_collection_filter_on_repository_level' => __('If there are filters set in the collections, you can also set them to be displayed at repository level.', 'tainacan'),
    'info_changes' => __('Changes', 'tainacan'),
    'info_possible_external_sources' => __('Possible external sources: CSV, Instagram, YouTube, etc.', 'tainacan'),
    'info_help_term_name' => __('The term name', 'tainacan'),
    'info_help_term_description' => __('The description of the Term.', 'tainacan'),
    'info_help_parent_term' => __('The parent term', 'tainacan'),
    'info_no_attachments_on_item_yet' => __('The are no attachments on this item so far.', 'tainacan'),
    /* translators: This is displayed to indicate that there are no attachments yet. The attachments label can be tweked by user. */
    'info_no_%s_on_item_yet' => __('The are no %s on this item so far.', 'tainacan'),
    'info_repository_metadata_inheritance' => __('Repository Metadata will be inherited by all collections.', 'tainacan'),
    'info_repository_filters_inheritance' => __('Repository Filters will be inherited by all collections.', 'tainacan'),
    'info_create_filters' => __('Click or Drag and Drop Metadata here for creating a new Filter.', 'tainacan'),
    'info_create_metadata' => __('Click or Drag and Drop Metadata Types here for creating a new Metadata.', 'tainacan'),
    'info_create_section' => __('Click or Drag and Drop to create a new section for organizing your metadata list.', 'tainacan'),
    'info_create_child_metadata' => __('Drag and Drop Metadata Types here for creating a child Metadata.', 'tainacan'),
    'info_choose_your_metadata' => __('Choose your metadata.', 'tainacan'),
    'info_target_collection_helper' => __('The collection where imported item will be added. Only those that you have permission are listed.', 'tainacan'),
    'info_source_file_upload' => __('The file containing the data to be imported.', 'tainacan'),
    'info_no_metadata_source_file' => __('No metadata was found from the source file.', 'tainacan'),
    'info_no_special_fields_available' => __('No special field was found.', 'tainacan'),
    'info_special_fields_mapped_default' => __('Mapped to default field on collection.', 'tainacan'),
    'info_metadata_mapping_helper' => __('Map each file metadata with the corresponding one in selected collection.', 'tainacan'),
    'info_upload_a_source_to_see_metadata' => __('Upload a source file to load metadata.', 'tainacan'),
    'info_select_collection_to_list_metadata' => __('Select a target collection to list metadata.', 'tainacan'),
    'info_url_source_link_helper' => __('Link to file containing the data to be imported.', 'tainacan'),
    'info_updated_at' => __('Updated at', 'tainacan'),
    'info_editing_metadata_values' => __('Editing metadata values...', 'tainacan'),
    'info_updating_metadata_values' => __('Updating metadata values...', 'tainacan'),
    'info_type_to_add_users' => __('Add users to filter...', 'tainacan'),
    'info_type_to_add_items' => __('Add items to filter...', 'tainacan'),
    'info_type_to_search_items' => __('Search items...', 'tainacan'),
    'info_type_to_add_terms' => __('Add terms to filter...', 'tainacan'),
    'info_type_to_search_metadata' => __('Search metadata...', 'tainacan'),
    'info_type_to_add_metadata' => __('Add metadata to filter...', 'tainacan'),
    'info_visibility_helper' => __('How the item will be available to visualization.', 'tainacan'),
    'info_errors_in_form' => __('There are errors in the form', 'tainacan'),
    'info_no_document_to_item' => __('No document was uploaded to this item.', 'tainacan'),
    'info_unfinished_processes' => __('unfinished processes', 'tainacan'),
    'info_no_process' => __('There are no processes executing.', 'tainacan'),
    'info_unknown_date' => __('Unknown date.', 'tainacan'),
    'info_there_are_no_metadata_to_search' => __('There are no metadata to search', 'tainacan'),
    'info_there_are_no_metadata_in_repository_level' => __('There are no metadata in repository level', 'tainacan'),
    'info_import_collection' => __('Import from external sources.', 'tainacan'),
    'info_import_items' => __('Import items from external sources.', 'tainacan'),
    'info_bulk_add_items' => __('Bulk add documents from your computer as items.', 'tainacan'),
    'info_editing_items_in_bulk' => __('Bulk edit items', 'tainacan'),
    'info_by_inner' => __('by', 'tainacan'),
    'info_bulk_edit_process_added' => __('Bulk edit added to process queue.', 'tainacan'),
    'info_no_parent_term_found' => __('No valid parent term was found with this name.', 'tainacan'),
    'info_warning_changing_parent_term' => __('Warning! Changing parent term will reload the terms list, thus unchecking any selection.', 'tainacan'),
    'info_warning_selected_items_remove_from_trash' => __('Do you really want to remove from trash the selected items?', 'tainacan'),
    'info_no_options_available_filtering' => __('No options for this filtering.', 'tainacan'),
    'info_no_options_found' => __('No options found.', 'tainacan'),
    'info_all_files_uploaded' => __('All files uploaded.', 'tainacan'),
    'info_there_are_%s_items_being_edited' => __('There are %s items being edited.', 'tainacan'),
    'info_there_is_one_item_being_edited' => __('There is one item being edited.', 'tainacan'),
    'info_no_preview_found' => __('No preview was found.', 'tainacan'),
    'info_leaving_bulk_edit' => __('You are leaving the bulk edit now.', 'tainacan'),
    'info_current_view_mode_metadata_not_allowed' => __('Current view mode does not allow displayed metadata selection.', 'tainacan'),
    'info_cant_select_metadata_without_items' => __('Can not select displayed metadata without items on list.', 'tainacan'),
    'info_process_status_finished' => __('Finished', 'tainacan'),
    'info_process_status_finished_errors' => __('Finished with errors', 'tainacan'),
    'info_process_status_errored' => __('Failed', 'tainacan'),
    'info_process_status_cancelled' => __('Cancelled', 'tainacan'),
    'info_process_status_paused' => __('Paused', 'tainacan'),
    'info_process_status_running' => __('Running', 'tainacan'),
    'info_warning_process_delete' => __('Are you sure? This process will be deleted.', 'tainacan'),
    'info_warning_process_cancelled' => __('Are you sure? This process will be cancelled.', 'tainacan'),
    'info_empty' => __('empty', 'tainacan'),
    'info_url_copied' => __('URL link copied', 'tainacan'),
    'info_other_options' => __('Other options: ', 'tainacan'),
    'info_other_item_listing_options' => __('Other items listing options: ', 'tainacan'),
    'info_send_email' => __('The exporter may take a while. Check this option to receive an e-mail when the process is done. You can also check the process status visiting the', 'tainacan'),
    'info_tainacan_api' => __('Tainacan API on JSON format.', 'tainacan'),
    'info_items_hidden_due_sorting' => __('When ordering by metadata value, items that have no value for the chosen metadata will not be listed. This list may have less elements than the total existing for current search criteria.', 'tainacan'),
    'info_sorting_by_metadata_value_%s' => __('Showing only items that have value for metadata %s.', 'tainacan'),
    'info_sorting_by_metadata_value_%s_empty_list' => __('No item found, but only items with values for metadata %s are shown. Try sorting by other metadata.', 'tainacan'),
    'info_await_while_item_copy' => __('Please wait while copy is being created...', 'tainacan'),
    'info_await_while_item_copies' => __('Please wait while copies are being created. This may take a while...', 'tainacan'),
    'info_expose_only_displayed_metadata' => __('By checking this option, only metadata that are displayed on the current list will be exposed', 'tainacan'),
    'info_initial_value' => __('Initial value', 'tainacan'),
    'info_final_value' => __('Final value', 'tainacan'),
    'info_show_interval_on_tag' => __('Show applied interval on tags', 'tainacan'),
    'info_title_mapping' => __('The title is the most relevant metadata, that shall identify your item on lists for different view modes. Select the title source metadata first, or skip to run importer as it is.', 'tainacan'),
    'info_can_not_edit_collection' => __('You are not allowed to edit this collection.', 'tainacan'),
    'info_can_not_edit_taxonomy' => __('You are not allowed to edit this taxonomy.', 'tainacan'),
    'info_can_not_edit_filters' => __('You are not allowed to edit filters.', 'tainacan'),
    'info_can_not_edit_metadata' => __('You are not allowed to edit metadata.', 'tainacan'),
    'info_can_not_edit_capabilities' => __('You are not allowed to edit capabilities.', 'tainacan'),
    'info_can_not_read_activities' => __('You are not allowed to read activities.', 'tainacan'),
    'info_can_not_edit_item' => __('You are not allowed to edit this item.', 'tainacan'),
    'info_can_not_bulk_edit_items_collection' => __('You are not allowed to bulk edit items from this collection.', 'tainacan'),
    'info_not_allowed_change_order_metadata' => __('Cannot change metadata order here.', 'tainacan'),
    'info_not_allowed_change_order_metadata_sections' => __('Cannot change metadata sections order here.', 'tainacan'),
    'info_not_allowed_change_order_filters' => __('Cannot change filters order here.', 'tainacan'),
    'info_no_value_compound_metadata' => __('No value has been added to this compound metadata.', 'tainacan'),
    /* translators: Refers to the hierarchy of compound metadata. Like in 'Metadata X (child of Metadata Y) */
    'info_child_of' => __('child of', 'tainacan'),
    /* translators: Refers to the hierarchy of taxonomy terms. Like in 'Macro (child of Photography) */
    'info_children_of_%s' => __('Children of %s', 'tainacan'),
    'info_slides_help_introduction' => __('Use the following commands to navigate through the items', 'tainacan'),
    'info_slides_previous_item' => __('to go to the previous item', 'tainacan'),
    'info_slides_next_item' => __('to go to the next item', 'tainacan'),
    'info_slides_hide_controls' => __('to hide the controls and focus on the document', 'tainacan'),
    'info_slides_start_transition' => __('to start or pause automatic transition every 3s', 'tainacan'),
    'info_slides_exit' => __('to leave the fullscreen slides view mode', 'tainacan'),
    'info_slides_help_end' => __('At any moment, you can also check the item metadata list by clicking on the metadata icon (%1$s) or go directly to the item page, where you will have all its details, by clicking on the eye icon (%2$s).', 'tainacan'),
    'info_thumbnail_custom' => __('Upload the desired image for the thumbnail', 'tainacan'),
    'info_thumbnail_default_from_document' => __('A thumbnail will be automatically generated from the submitted document file', 'tainacan'),
    'info_submission_processing' => __('Please wait while the submission is being processed', 'tainacan'),
    'info_submission_uploading' => __('Please wait while files are uploaded', 'tainacan'),
    'info_thumbnail_alt' => __('The alternative text of the thumbnail is visible only by screen readers and should be useful for users with visual impairments.', 'tainacan'),
    'info_edit_attachments' => __('Order, title or alternative text of the attachments, are edited via the WordPress media modal.', 'tainacan'),
    'info_recaptcha_link_%s' => __('Remember to configure your website reCAPTCHA keys on <a href="%s" target="_blank">the item submission repository page</a>.', 'tainacan'),
    'info_form_not_loaded' => __('There are probably not enough permissions to display it here.', 'tainacan'),
    'info_validating_slug' => __('Validating slug...', 'tainacan'),
    'info_no_taxonomy_metadata_created' => __('No taxonomy metadata created yet', 'tainacan'),
    'info_child_terms_chart' => __('Click on the term bar on the chart aside to see its child terms (if any) in this panel', 'tainacan'),
    'info_nothing_like_this_so_far' => __('Nothing like this so far.', 'tainacan'),
    'info_metadata_autocomplete_suggestions' => __('Some values already used on this metadatum:', 'tainacan'),
    'info_related_items' => __('These are items that are related to this item via their own relationship type metadata. You can edit such relation on their pages.', 'tainacan'),
    'info_document_option_forced_iframe' => __('Attempt to use an iframe to embed url content on the item page. You may use this option if the autoembed does not work.', 'tainacan'),
    'info_document_option_is_image' => __('If you are linking directly to an external image, use this option so it can be properly embedded.', 'tainacan'),
    'info_%s_applied_filters' => __('<strong>%s</strong> filters applied', 'tainacan'),
    'info_items_%s_found' => __('<strong>%s</strong> items found', 'tainacan'),
    'info_%s_applied_filter' => __('<strong>%s</strong> filter applied', 'tainacan'),
    'info_item_%s_found' => __('<strong>%s</strong> item found', 'tainacan'),
    'info_iframe_dimensions' => __('The dimension values will be passed to the iframe, but it\'s rendering may change according to the theme display settings. It is still important to keep an approximate aspect ratio to the inner content.', 'tainacan'),
    'info_metadata_mapper_helper' => __('Select the corresponding metadata so they can be exposed according to the mapper', 'tainacan'),
    'info_default_orderby' => __('These settings only affect the initial state of the items sorting. After changed, the value used will be the latest selected by the user.', 'tainacan'),
    'info_collection_thumbnail_and_header' => __('The thumbnail is a squared image that will represent the collection in listings. The header image is a complementary, decorative image that may or not be displayed by your theme in the items list. Keep in mind that it might be cropped.', 'tainacan'),
    'info_preset_collections' => __('Use mappers or standards as pre configuration', 'tainacan'),
    'info_create_collection_from_mapper' => __('Have the metadata preset by an installed mapper, such as Dublin core, then set the rest manually.', 'tainacan'),
    'info_create_collection_from_preset' => __('Have metadata, taxonomies, terms and related collections preset according to a standard.', 'tainacan'),
    'info_use_search_separated_words' => __('You may wrap the words with quotes to group them.', 'tainacan'),
    /* translators: At the end of this sentence there will be a link for the advanced search */
    'info_for_more_metadata_search_options_use' => __('For more options of metadata search, use the', 'tainacan'),
    'info_you_searched_for_%s' => __('You searched for %s', 'tainacan'),
    'info_try_enabling_search_by_word' => __('Try enabling the search by words.', 'tainacan'),
    'info_try_disabling_search_by_word' => __('Try disabling the search by words, to search for the complete phrase.', 'tainacan'),
    'info_try_empting_the_textual_search' => __('Try empting the textual search.', 'tainacan'),
    'info_try_selecting_all_collections_in_filter' => __('Try selecting All Collections in the filter above.', 'tainacan'),
    'info_details_about_search_by_word' => __('They may be located on different metadata and order, but you will still be able to use quotes to group them.', 'tainacan'),
    'info_item_submission_draft_status' => __('Warning: draft items may be submitted even without filling all required metadata.', 'tainacan'),
    'info_empty_geocoordinate_metadata_list' => __('No geocoordinate metadata was found. Try enabling it in the "displayed metadata" dropdown.', 'tainacan'),
    'info_non_located_item' => __('This item does not have any location based on this metadata.', 'tainacan'),
    'info_metadata_section_hidden_conditional' => __('Section disabled due to a conditional metadatum value.', 'tainacan'),
    'info_create_select_metadatum_for_conditional_section' => __('For configuring conditional sections, first create one select type metadatum to use its values as rules for displaing this section. The metadatum should be inside another metadatum section.', 'tainacan'),
    'info_taxonomy_terms_list' => __('The list of terms that are managed by this taxonomy. They will be used as values for the taxonomy metadata.', 'tainacan'),
    'info_no_child_term_of_%s_found' => __('No child term of %s was found.', 'tainacan'),
    'info_warning_term_with_child' => __('This term has child terms. Per default, if you remove a parent term, its child terms will be moved one level up in the hierarchy.', 'tainacan'),
    'info_warning_selected_term_delete' => __('Do you really want to permanently delete the selected term?', 'tainacan'),
    'info_warning_some_terms_with_child' => __('When removing multiple terms at once, it is possible that some of the terms contain child terms. Per default, if you remove a parent term, its child terms will be moved one level up in the hierarchy.', 'tainacan'),
    'info_%s_terms_created' => __('%s terms created with success.', 'tainacan'),
    'info_terms_creation_failed_due_to_value_%s' => __('Terms creation failed due to value: %s.', 'tainacan'),
    'info_terms_creation_failed_due_to_values_%s' => __('Terms creation failed due to values: %s.', 'tainacan'),
    'info_autodraft_updated' => __('Autodraft updated. Please create the item to keep your changes.', 'tainacan'),
    'info_intersection_explainer' => __('Will show items if the selected value is:', 'tainacan'),
    'info_intersection_rules' => __('The value must match both rules to appear in the filter.', 'tainacan'),
    'info_editing_publication_authorship' => __('Warning! By changhing the item author, you may loose access to editing it. Certain types of users can only edit items that are authored by theirselves.', 'tainacan'),
    /* translators: The first string is the current author name and the second is the future author name */
    'info_change_author_from_%s_to_%s' => __('Are you sure you want to change the authorship of this item from <em>%1$s</em> to <em>%2$s</em>?', 'tainacan'),
    'info_authorship' => __('The user who is credited as creator of this item and who generally have capabilities to edit it, besides administrators.', 'tainacan'),
    'info_comment_status' => __('Allow or disallow users to write comments in the item public page.', 'tainacan'),
    'info_publication_data' => __('This section gathers information related to the item publication on the website.', 'tainacan'),
    'info_publication_data_editing' => __('You may tweak some of the options available here in the collection settings.', 'tainacan'),
    /* translators: The first string is the sorting direction (order) and will be an noun such as 'ascending'. The second one will be the metadata name (orderby). */
    'info_sorting_%s_by_%s' => __('Sorting <em>%1$s</em> by <em>%2$s</em>.', 'tainacan'),
    'info_manage_collections' => __('Manage which are available in the <a href="%s" target="_blank">collections list</a> page.', 'tainacan'),
    'info_manage_taxonomies' => __('Manage which are available in the <a href="%s" target="_blank">taxonomies list</a> page.', 'tainacan'),
    'info_error_date_smaller_than_min_%s' => __('Date should be after %s.', 'tainacan'),
    'info_error_date_greater_than_max_%s' => __('Date should be before %s.', 'tainacan'),
    /* Activity actions */
    'action_update-metadata-value' => __('Item Metadata Value Updates', 'tainacan'),
    'action_update' => __('General Updates', 'tainacan'),
    'action_create' => __('General Creations', 'tainacan'),
    'action_update-metadata-order' => __('Metadata Order Updates', 'tainacan'),
    'action_trash' => __('Trashing', 'tainacan'),
    'action_new-attachment' => __('Addition of Attachments', 'tainacan'),
    'action_update-filters-order' => __('Filter Order Updates', 'tainacan'),
    'action_update-document' => __('Document Updates', 'tainacan'),
    'action_delete' => __('General Deletions', 'tainacan'),
    'action_delete-attachment' => __('Deletion of Attachments', 'tainacan'),
    'action_update-thumbnail' => __('Thumbnail Updates', 'tainacan'),
    'action_others' => __('Other Actions', 'tainacan'),
    // Datepicker months
    'datepicker_month_january' => __('January', 'tainacan'),
    'datepicker_month_february' => __('February', 'tainacan'),
    'datepicker_month_march' => __('March', 'tainacan'),
    'datepicker_month_april' => __('April', 'tainacan'),
    'datepicker_month_may' => __('May', 'tainacan'),
    'datepicker_month_june' => __('June', 'tainacan'),
    'datepicker_month_july' => __('July', 'tainacan'),
    'datepicker_month_august' => __('August', 'tainacan'),
    'datepicker_month_september' => __('September', 'tainacan'),
    'datepicker_month_october' => __('October', 'tainacan'),
    'datepicker_month_november' => __('November', 'tainacan'),
    'datepicker_month_december' => __('December', 'tainacan'),
    // Datepicker week days
    /* translators: This refers to the short label that will appear on datepickers for Sunday */
    'datepicker_short_sunday' => __('Su', 'tainacan'),
    /* translators: This refers to the short label that will appear on datepickers for Monday */
    'datepicker_short_monday' => __('M', 'tainacan'),
    /* translators: This refers to the short label that will appear on datepickers for Tuesday */
    'datepicker_short_tuesday' => __('Tu', 'tainacan'),
    /* translators: This refers to the short label that will appear on datepickers for Wednesday */
    'datepicker_short_wednesday' => __('W', 'tainacan'),
    /* translators: This refers to the short label that will appear on datepickers for Thursday */
    'datepicker_short_thursday' => __('Th', 'tainacan'),
    /* translators: This refers to the short label that will appear on datepickers for Friday */
    'datepicker_short_friday' => __('F', 'tainacan'),
    /* translators: This refers to the short label that will appear on datepickers for Saturday */
    'datepicker_short_saturday' => __('Sa', 'tainacan'),
    /* Errors displayed on the interface bottom notifications */
    'error_connectivity_label' => __('Connectivity issue', 'tainacan'),
    'error_connectivity' => __('It is possible that you are disconnected or the server is not working properly.', 'tainacan'),
    'error_permalinks_label' => __('Permalinks issue', 'tainacan'),
    'error_400' => __('Some request went wrong due to invalid syntax.', 'tainacan'),
    'error_401' => __('You must authenticate to access this information. Try logging in again on the WordPress Admin panel.', 'tainacan'),
    'error_403' => __('It seems that you are not allowed to access this content.', 'tainacan'),
    'error_404' => __('A wrong request was made or this information does not exist.', 'tainacan'),
    'error_408' => __('This request exceeded the server expected timeout.', 'tainacan'),
    'error_413' => __('This request size exceeded the server size limit', 'tainacan'),
    'error_500' => __('An internal server error occurred. Try contacting the administrator.', 'tainacan'),
    'error_502' => __('A communication between servers went wrong. Try contacting the administrator.', 'tainacan'),
    'error_503' => __('Some service is not available now. Try again later and if it persists, contact the administrator.', 'tainacan'),
    'error_504' => __('The server communication exceeded the expected timeout. Try contacting the administrator.', 'tainacan'),
    'error_511' => __('You must authenticate to get access this information. Try logging in again on the WordPress Admin panel.', 'tainacan'),
    'error_other' => __('Something went wrong here. You may want to try again or contact the Administrator.', 'tainacan'),
    'error_connectivity_detail' => __('The WordPress Heartbit API sends requests periodically to the server to update some information. The latest request failed for some reason. It can be the case of a lost connection or bad communication between the browser and the server.', 'tainacan'),
    'error_permalinks_detail' => __('Tainacan requires your Permalink settings to be configured. Please visit <a href="%s" target="_blank">Permalink settings</a> and define it to an option such as "postname".', 'tainacan'),
    'error_400_detail' => __('The server could not understand the request due to invalid syntax. This is possibly an issue with Tainacan and should be reported to its developers.', 'tainacan'),
    'error_401_detail' => __('You must authenticate to get access this information. Even if you have access to the Tainacan admin panel, it may be the case that your session cookies were lost. Try reloading the page or logging again on the WordPress Admin panel.', 'tainacan'),
    'error_403_detail' => __('It seems that you are not allowed to access this content. Your user might have a role with insufficient capabilities. If that is not the case, check if you are correctly logged in on the WordPress Admin panel.', 'tainacan'),
    'error_404_detail' => __('A wrong request was made or this information does not exist. This can either mean some connection error occurred just now or the content that you are looking for was requested wrongly. In that case, it might be worth to report the issue to Tainacan developers.', 'tainacan'),
    'error_408_detail' => __('This request exceeded the server expected timeout. This can be caused by a slow connection or connectivity issues. If it is not something noticeable in other pages, try contacting the administrator.', 'tainacan'),
    'error_413_detail' => __('This request exceeded the server size limit. This can be caused by lack of configurations in the server side. Try contacting the administrator.', 'tainacan'),
    'error_500_detail' => __('An internal server error occurred. This error can occur for a variety of reasons, and a more detailed description can be found on the server logs. Try contacting the administrator and provide information of the moment when the error occurred.', 'tainacan'),
    'error_502_detail' => __('This error response means that the server, while working as a gateway to get a response needed to handle the request, got an invalid response. Try contacting the administrator.', 'tainacan'),
    'error_503_detail' => __('The server might be unavailable due to multiple access, some instability or connection issues. Try again later and if it persists, contact the administrator.', 'tainacan'),
    'error_504_detail' => __('This error response is given when the server is acting as a gateway and cannot get a response in time. Try contacting the administrator.', 'tainacan'),
    'error_511_detail' => __('You must authenticate to get access this information. Even if you have access to the Tainacan admin panel, your session cookies might have gotten lost. Try reloading the page or logging in again on the WordPress Admin panel.', 'tainacan'),
    'error_other_detail' => __('Something went wrong here. Please try again or contact the administrator.', 'tainacan'),
]` |  | 

Source: [tainacan-i18n.php](https://github.com/tainacan/tainacan/blob/master/src/views/tainacan-i18n.php), [line 5](https://github.com/tainacan/tainacan/blob/master/src/views/tainacan-i18n.php#L5-L1206)

---------------------------------
<br>

## `tainacan-admin-extra-request-options` <!-- {docsify-ignore} -->

*get_admin_js_localization_params is used to build the JS tainacan_plugin global object that serves as a
bridge between PHP and JS. Not every page needs it but they can call it to add their own data to the object.*


Argument | Type | Description
-------- | ---- | -----------
`$admin_request_options` |  | 

Source: [class-tainacan-pages.php](https://github.com/tainacan/tainacan/blob/master/src/views/class-tainacan-pages.php), [line 213](https://github.com/tainacan/tainacan/blob/master/src/views/class-tainacan-pages.php#L213-L332)

---------------------------------
<br>

## `tainacan-navigation-logo-use-white` <!-- {docsify-ignore} -->

*Tweaks the navigation logo to use white, monochrome version*


Argument | Type | Description
-------- | ---- | -----------
`false` |  | 

Source: [class-tainacan-pages.php](https://github.com/tainacan/tainacan/blob/master/src/views/class-tainacan-pages.php), [line 472](https://github.com/tainacan/tainacan/blob/master/src/views/class-tainacan-pages.php#L472-L479)

---------------------------------
<br>

## `tainacan-navigation-logo` <!-- {docsify-ignore} -->

*Filter the navigation logo*


Argument | Type | Description
-------- | ---- | -----------
`plugin_dir_url(__DIR__) . '/assets/images/' . ($navigation_logo_use_white ? 'tainacan_logo_header_white.svg' : 'tainacan_logo_header.svg')` |  | 

Source: [class-tainacan-pages.php](https://github.com/tainacan/tainacan/blob/master/src/views/class-tainacan-pages.php), [line 481](https://github.com/tainacan/tainacan/blob/master/src/views/class-tainacan-pages.php#L481-L491)

---------------------------------
<br>

## `tainacan-navigation-logo-icon` <!-- {docsify-ignore} -->

*Filter the navigation logo icon*


Argument | Type | Description
-------- | ---- | -----------
`plugin_dir_url(__DIR__) . '/assets/images/' . ($navigation_logo_use_white ? 'tainacan_logo_symbol.svg' : 'tainacan_logo_icon.svg')` |  | 

Source: [class-tainacan-pages.php](https://github.com/tainacan/tainacan/blob/master/src/views/class-tainacan-pages.php), [line 493](https://github.com/tainacan/tainacan/blob/master/src/views/class-tainacan-pages.php#L493-L503)

---------------------------------
<br>

## `tainacan_admin_breadcrumbs` <!-- {docsify-ignore} -->

*Allows external plugins to add breadcrumbs to the Tainacan admin pages.*


Argument | Type | Description
-------- | ---- | -----------
`$breadcrumbs` |  | 

Source: [class-tainacan-pages.php](https://github.com/tainacan/tainacan/blob/master/src/views/class-tainacan-pages.php), [line 622](https://github.com/tainacan/tainacan/blob/master/src/views/class-tainacan-pages.php#L622-L625)

---------------------------------
<br>

## `tainacan-admin-ui-options` <!-- {docsify-ignore} -->

*admin_init_ui_options is a filter that sets the admin UI options for the current user,
based on his/her role.*


Argument | Type | Description
-------- | ---- | -----------
`array_merge(self::$admin_ui_options, $_GET)` |  | 

Source: [class-tainacan-pages.php](https://github.com/tainacan/tainacan/blob/master/src/views/class-tainacan-pages.php), [line 704](https://github.com/tainacan/tainacan/blob/master/src/views/class-tainacan-pages.php#L704-L733)

---------------------------------
<br>

## `editable_slug` <!-- {docsify-ignore} -->

*This filter is documented in wp-admin/edit-tag-form.php*


Argument | Type | Description
-------- | ---- | -----------
`$uri` |  | 
`$post` |  | 

Source: [class-tainacan-admin.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/class-tainacan-admin.php), [line 362](https://github.com/tainacan/tainacan/blob/master/src/views/admin/class-tainacan-admin.php#L362-L363)

---------------------------------
<br>

## `editable_slug` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$post->post_name` |  | 
`$post` |  | 

Source: [class-tainacan-admin.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/class-tainacan-admin.php), [line 370](https://github.com/tainacan/tainacan/blob/master/src/views/admin/class-tainacan-admin.php#L370-L370)

---------------------------------
<br>

## `tainacan-status-require-validation` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`['publish', 'future', 'private']` |  | 

Source: [class-tainacan-numeric.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/filter-types/numeric/class-tainacan-numeric.php), [line 94](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/filter-types/numeric/class-tainacan-numeric.php#L94-L94)

---------------------------------
<br>

## `tainacan-status-require-validation` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`['publish', 'future', 'private']` |  | 

Source: [class-tainacan-numerics-intersection.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/filter-types/numerics-intersection/class-tainacan-numerics-intersection.php), [line 87](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/filter-types/numerics-intersection/class-tainacan-numerics-intersection.php#L87-L87)

---------------------------------
<br>

## `tainacan-status-require-validation` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`['publish', 'future', 'private']` |  | 

Source: [class-tainacan-date.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/filter-types/date/class-tainacan-date.php), [line 82](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/filter-types/date/class-tainacan-date.php#L82-L82)

---------------------------------
<br>

## `tainacan-status-require-validation` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`['publish', 'future', 'private']` |  | 

Source: [class-tainacan-dates-intersection.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/filter-types/dates-intersection/class-tainacan-dates-intersection.php), [line 76](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/filter-types/dates-intersection/class-tainacan-dates-intersection.php#L76-L76)

---------------------------------
<br>

## `tainacan-status-require-validation` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`['publish', 'future', 'private']` |  | 

Source: [class-tainacan-selectbox.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/selectbox/class-tainacan-selectbox.php), [line 54](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/selectbox/class-tainacan-selectbox.php#L54-L54)

---------------------------------
<br>

## `tainacan-status-require-validation` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`['publish', 'future', 'private']` |  | 

Source: [class-tainacan-user.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/user/class-tainacan-user.php), [line 139](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/user/class-tainacan-user.php#L139-L139)

---------------------------------
<br>

## `tainacan-item-get-author-name` <!-- {docsify-ignore} -->

*Get the value as a HTML string*


Argument | Type | Description
-------- | ---- | -----------
`$name` |  | 
`$this` |  | 

Source: [class-tainacan-user.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/user/class-tainacan-user.php), [line 149](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/user/class-tainacan-user.php#L149-L165)

---------------------------------
<br>

## `tainacan-item-metadata-get-value-as-html--type-user` <!-- {docsify-ignore} -->

*Filter the HTML representation of the value of a user metadatum*


Argument | Type | Description
-------- | ---- | -----------
`$return` | `string` | The HTML representation of the value
`$item_metadata` | `\Tainacan\Entities\Item_Metadata_Entity` | The Item_Metadata_Entity object

Source: [class-tainacan-user.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/user/class-tainacan-user.php), [line 171](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/user/class-tainacan-user.php#L171-L179)

---------------------------------
<br>

## `tainacan-item-metadata-get-value-as-html--type-textarea` <!-- {docsify-ignore} -->

*Filter the HTML representation of the value of a textarea metadatum*


Argument | Type | Description
-------- | ---- | -----------
`force_balance_tags($return)` |  | 
`$item_metadata` | `\Tainacan\Entities\Item_Metadata_Entity` | The Item_Metadata_Entity object

Source: [class-tainacan-textarea.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/textarea/class-tainacan-textarea.php), [line 80](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/textarea/class-tainacan-textarea.php#L80-L88)

---------------------------------
<br>

## `tainacan-item-metadata-get-value-as-html--type-compound` <!-- {docsify-ignore} -->

*Filter the HTML representation of the value of a compound metadatum*


Argument | Type | Description
-------- | ---- | -----------
`$return` | `string` | The HTML representation of the value
`$item_metadata` | `\Tainacan\Entities\Item_Metadata_Entity` | The Item_Metadata_Entity object

Source: [class-tainacan-compound.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/compound/class-tainacan-compound.php), [line 243](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/compound/class-tainacan-compound.php#L243-L251)

---------------------------------
<br>

## `tainacan-item-metadata-get-value-as-string--type-compound` <!-- {docsify-ignore} -->

*Filter the STRING representation of the value of a compound metadatum*


Argument | Type | Description
-------- | ---- | -----------
`$return` | `string` | The STRING representation of the value
`$item_metadata` | `\Tainacan\Entities\Item_Metadata_Entity` | The Item_Metadata_Entity object

Source: [class-tainacan-compound.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/compound/class-tainacan-compound.php), [line 286](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/compound/class-tainacan-compound.php#L286-L294)

---------------------------------
<br>

## `tainacan-get-child-item-metadatum-as-html-before-label` <!-- {docsify-ignore} -->

*Class TainacanMetadatumType*


Argument | Type | Description
-------- | ---- | -----------
`'<h4 class="label child-metadatum-label">'` |  | 
`$meta` |  | 

Source: [class-tainacan-compound.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/compound/class-tainacan-compound.php), [line 10](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/compound/class-tainacan-compound.php#L10-L306)

---------------------------------
<br>

## `tainacan-get-child-item-metadatum-as-html-after-label` <!-- {docsify-ignore} -->

*Class TainacanMetadatumType*


Argument | Type | Description
-------- | ---- | -----------
`'</h4>'` |  | 
`$meta` |  | 

Source: [class-tainacan-compound.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/compound/class-tainacan-compound.php), [line 10](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/compound/class-tainacan-compound.php#L10-L307)

---------------------------------
<br>

## `tainacan-get-child-item-metadatum-as-html-before-value` <!-- {docsify-ignore} -->

*Class TainacanMetadatumType*


Argument | Type | Description
-------- | ---- | -----------
`'<p class="child-metadatum-value">'` |  | 
`$meta` |  | 

Source: [class-tainacan-compound.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/compound/class-tainacan-compound.php), [line 10](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/compound/class-tainacan-compound.php#L10-L311)

---------------------------------
<br>

## `tainacan-get-child-item-metadatum-as-html-after-value` <!-- {docsify-ignore} -->

*Class TainacanMetadatumType*


Argument | Type | Description
-------- | ---- | -----------
`'</p>'` |  | 
`$meta` |  | 

Source: [class-tainacan-compound.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/compound/class-tainacan-compound.php), [line 10](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/compound/class-tainacan-compound.php#L10-L312)

---------------------------------
<br>

## `tainacan-status-require-validation` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`['publish', 'future', 'private']` |  | 

Source: [class-tainacan-relationship.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/relationship/class-tainacan-relationship.php), [line 164](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/relationship/class-tainacan-relationship.php#L164-L164)

---------------------------------
<br>

## `tainacan-item-metadata-get-value-as-html--type-relationship` <!-- {docsify-ignore} -->

*Filter the HTML representation of the value of a relationship metadatum*


Argument | Type | Description
-------- | ---- | -----------
`$return` | `string` | The HTML representation of the value
`$item_metadata` | `\Tainacan\Entities\Item_Metadata_Entity` | The Item_Metadata_Entity object

Source: [class-tainacan-relationship.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/relationship/class-tainacan-relationship.php), [line 251](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/relationship/class-tainacan-relationship.php#L251-L259)

---------------------------------
<br>

## `tainacan-item-metadata-relationship-get-item-thumbnail` <!-- {docsify-ignore} -->

*Filter the image size of the thumbnail to be displayed*


Argument | Type | Description
-------- | ---- | -----------
`'tainacan-small'` |  | 

Source: [class-tainacan-relationship.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/relationship/class-tainacan-relationship.php), [line 332](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/relationship/class-tainacan-relationship.php#L332-L339)

---------------------------------
<br>

## `tainacan-item-metadata-get-value-as-string--type-geocoordinate` <!-- {docsify-ignore} -->

*Filter the STRING representation of the value of a geocoordinate metadatum*


Argument | Type | Description
-------- | ---- | -----------
`$return` | `string` | The STRING representation of the value
`$item_metadata` | `\Tainacan\Entities\Item_Metadata_Entity` | The Item_Metadata_Entity object

Source: [class-tainacan-geocoordinate.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/geocoordinate/class-tainacan-geocoordinate.php), [line 131](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/geocoordinate/class-tainacan-geocoordinate.php#L131-L139)

---------------------------------
<br>

## `tainacan-item-metadata-get-value-as-html--type-geocoordinate` <!-- {docsify-ignore} -->

*Filter the HTML representation of the value of a geocoordinate metadatum*


Argument | Type | Description
-------- | ---- | -----------
`$return` | `string` | The HTML representation of the value
`$item_metadata` | `\Tainacan\Entities\Item_Metadata_Entity` | The Item_Metadata_Entity object

Source: [class-tainacan-geocoordinate.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/geocoordinate/class-tainacan-geocoordinate.php), [line 214](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/geocoordinate/class-tainacan-geocoordinate.php#L214-L222)

---------------------------------
<br>

## `tainacan-status-require-validation` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`['publish', 'future', 'private']` |  | 

Source: [class-tainacan-geocoordinate.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/geocoordinate/class-tainacan-geocoordinate.php), [line 234](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/geocoordinate/class-tainacan-geocoordinate.php#L234-L234)

---------------------------------
<br>

## `tainacan-status-require-validation` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`['publish', 'future', 'private']` |  | 

Source: [class-tainacan-control.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/control/class-tainacan-control.php), [line 100](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/control/class-tainacan-control.php#L100-L100)

---------------------------------
<br>

## `tainacan-item-metadata-get-value-as-html--type-control` <!-- {docsify-ignore} -->

*Filter the HTML representation of the value of a control metadatum*


Argument | Type | Description
-------- | ---- | -----------
`$return` | `string` | The HTML representation of the value
`$item_metadata` | `\Tainacan\Entities\Item_Metadata_Entity` | The Item_Metadata_Entity object

Source: [class-tainacan-control.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/control/class-tainacan-control.php), [line 171](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/control/class-tainacan-control.php#L171-L179)

---------------------------------
<br>

## `tainacan-item-metadata-get-value-as-string--type-control` <!-- {docsify-ignore} -->

*Filter the STRING representation of the value of a control metadatum*


Argument | Type | Description
-------- | ---- | -----------
`$return` | `string` | The STRING representation of the value
`$item_metadata` | `\Tainacan\Entities\Item_Metadata_Entity` | The Item_Metadata_Entity object

Source: [class-tainacan-control.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/control/class-tainacan-control.php), [line 219](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/control/class-tainacan-control.php#L219-L227)

---------------------------------
<br>

## `tainacan-item-metadata-get-value-as-html--type-url` <!-- {docsify-ignore} -->

*Filter the HTML representation of the value of a url metadatum*


Argument | Type | Description
-------- | ---- | -----------
`$return` | `string` | The HTML representation of the value
`$item_metadata` | `\Tainacan\Entities\Item_Metadata_Entity` | The Item_Metadata_Entity object

Source: [class-tainacan-url.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/url/class-tainacan-url.php), [line 102](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/url/class-tainacan-url.php#L102-L110)

---------------------------------
<br>

## `tainacan-item-metadata-get-value-as-string--type-url` <!-- {docsify-ignore} -->

*Filter the STRING representation of the value of a url metadatum*


Argument | Type | Description
-------- | ---- | -----------
`strip_tags($return)` |  | 
`$item_metadata` | `\Tainacan\Entities\Item_Metadata_Entity` | The Item_Metadata_Entity object

Source: [class-tainacan-url.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/url/class-tainacan-url.php), [line 212](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/url/class-tainacan-url.php#L212-L220)

---------------------------------
<br>

## `tainacan-status-require-validation` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`['publish', 'future', 'private']` |  | 

Source: [class-tainacan-core-description.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/core-description/class-tainacan-core-description.php), [line 75](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/core-description/class-tainacan-core-description.php#L75-L75)

---------------------------------
<br>

## `tainacan-item-metadata-get-value-as-html--type-description` <!-- {docsify-ignore} -->

*Filter the HTML representation of the value of a core description metadatum*


Argument | Type | Description
-------- | ---- | -----------
`$return` | `string` | The HTML representation of the value
`$item_metadata` | `\Tainacan\Entities\Item_Metadata_Entity` | The Item_Metadata_Entity object

Source: [class-tainacan-core-description.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/core-description/class-tainacan-core-description.php), [line 116](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/core-description/class-tainacan-core-description.php#L116-L124)

---------------------------------
<br>

## `tainacan-item-metadata-get-value-as-html--type-date` <!-- {docsify-ignore} -->

*Filter the HTML representation of the value of a date metadatum*


Argument | Type | Description
-------- | ---- | -----------
`$return` | `string` | The HTML representation of the value
`$item_metadata` | `\Tainacan\Entities\Item_Metadata_Entity` | The Item_Metadata_Entity object

Source: [class-tainacan-date.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/date/class-tainacan-date.php), [line 102](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/date/class-tainacan-date.php#L102-L110)

---------------------------------
<br>

## `tainacan-item-metadata-get-value-as-html--type-text` <!-- {docsify-ignore} -->

*Filter the HTML representation of the value of a text metadatum*


Argument | Type | Description
-------- | ---- | -----------
`force_balance_tags($return)` |  | 
`$item_metadata` | `\Tainacan\Entities\Item_Metadata_Entity` | The Item_Metadata_Entity object

Source: [class-tainacan-text.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/text/class-tainacan-text.php), [line 82](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/text/class-tainacan-text.php#L82-L90)

---------------------------------
<br>

## `tainacan-status-require-validation` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`['publish', 'future', 'private']` |  | 

Source: [class-tainacan-core-title.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/core-title/class-tainacan-core-title.php), [line 74](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/core-title/class-tainacan-core-title.php#L74-L74)

---------------------------------
<br>

## `tainacan-status-require-validation` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`['publish', 'future', 'private']` |  | 

Source: [class-tainacan-taxonomy.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/taxonomy/class-tainacan-taxonomy.php), [line 232](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/taxonomy/class-tainacan-taxonomy.php#L232-L232)

---------------------------------
<br>

## `tainacan-item-metadata-get-value-as-html--type-taxonomy` <!-- {docsify-ignore} -->

*Filter the HTML representation of the value of a taxonomy metadatum*


Argument | Type | Description
-------- | ---- | -----------
`$return` | `string` | The HTML representation of the value
`$item_metadata` | `\Tainacan\Entities\Item_Metadata_Entity` | The Item_Metadata_Entity object

Source: [class-tainacan-taxonomy.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/taxonomy/class-tainacan-taxonomy.php), [line 400](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/taxonomy/class-tainacan-taxonomy.php#L400-L408)

---------------------------------
<br>

## `tainacan-terms-hierarchy-html-separator` <!-- {docsify-ignore} -->

*Class TainacanMetadatumType*


Argument | Type | Description
-------- | ---- | -----------
`'<span class="hierarchy-separator"> > </span>'` |  | 

Source: [class-tainacan-taxonomy.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/taxonomy/class-tainacan-taxonomy.php), [line 11](https://github.com/tainacan/tainacan/blob/master/src/views/admin/components/metadata-types/taxonomy/class-tainacan-taxonomy.php#L11-L425)

---------------------------------
<br>

## `tainacan-admin-hooks-positions` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`['begin-left', 'begin-right', 'end-left', 'end-right']` |  | 

Source: [class-tainacan-admin-hooks.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/classes/hooks/class-tainacan-admin-hooks.php), [line 19](https://github.com/tainacan/tainacan/blob/master/src/views/admin/classes/hooks/class-tainacan-admin-hooks.php#L19-L19)

---------------------------------
<br>

## `tainacan-admin-hooks-contexts` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`['collection', 'metadatum', 'item', 'taxonomy', 'term', 'filter', 'role', 'metadataSection']` |  | 

Source: [class-tainacan-admin-hooks.php](https://github.com/tainacan/tainacan/blob/master/src/views/admin/classes/hooks/class-tainacan-admin-hooks.php), [line 23](https://github.com/tainacan/tainacan/blob/master/src/views/admin/classes/hooks/class-tainacan-admin-hooks.php#L23-L23)

---------------------------------
<br>

## `https_local_ssl_verify` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`false` |  | 

Source: [class-tainacan-async-request.php](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/class-tainacan-async-request.php), [line 136](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/class-tainacan-async-request.php#L136-L136)

---------------------------------
<br>

## `tainacan-exporter-step-length-items` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`20` |  | 
`$this->get_current_step()` |  | 

Source: [class-tainacan-exporter.php](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/exporter/class-tainacan-exporter.php), [line 261](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/exporter/class-tainacan-exporter.php#L261-L261)

---------------------------------
<br>

## `tainacan-the-modified-author` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$last_user->display_name` |  | 

Source: [class-tainacan-exporter-handler-cell.php](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/exporter/traits/class-tainacan-exporter-handler-cell.php), [line 87](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/exporter/traits/class-tainacan-exporter-handler-cell.php#L87-L87)

---------------------------------
<br>

## `{$this->identifier}_queue_lock_time` <!-- {docsify-ignore} -->

*Lock process*

Lock the process so that multiple instances can't run simultaneously.
Override if applicable, but the duration should be greater than that
defined in the time_exceeded() method.


Argument | Type | Description
-------- | ---- | -----------
`$lock_duration` |  | 

Source: [class-tainacan-background-process-base.php](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/class-tainacan-background-process-base.php), [line 266](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/class-tainacan-background-process-base.php#L266-L278)

---------------------------------
<br>

## `{$this->identifier}_memory_exceeded` <!-- {docsify-ignore} -->

*Memory exceeded*

Ensures the batch process never exceeds 90%%
of the maximum WordPress memory.


Argument | Type | Description
-------- | ---- | -----------
`$return` |  | 

Source: [class-tainacan-background-process-base.php](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/class-tainacan-background-process-base.php), [line 386](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/class-tainacan-background-process-base.php#L386-L403)

---------------------------------
<br>

## `{$this->identifier}_default_time_limit` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`20` |  | 

Source: [class-tainacan-background-process-base.php](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/class-tainacan-background-process-base.php), [line 436](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/class-tainacan-background-process-base.php#L436-L436)

---------------------------------
<br>

## `{$this->identifier}_time_exceeded` <!-- {docsify-ignore} -->

*Time exceeded.*

Ensures the batch never exceeds a sensible time limit.
A timeout limit of 30s is common on shared hosting.


Argument | Type | Description
-------- | ---- | -----------
`$return` |  | 

Source: [class-tainacan-background-process-base.php](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/class-tainacan-background-process-base.php), [line 427](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/class-tainacan-background-process-base.php#L427-L443)

---------------------------------
<br>

## `{$this->identifier}_cron_interval` <!-- {docsify-ignore} -->

*Schedule cron healthcheck*


Argument | Type | Description
-------- | ---- | -----------
`5` |  | 

Source: [class-tainacan-background-process-base.php](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/class-tainacan-background-process-base.php), [line 457](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/class-tainacan-background-process-base.php#L457-L465)

---------------------------------
<br>

## `{$this->identifier}_cron_interval` <!-- {docsify-ignore} -->

*Schedule cron healthcheck*


Argument | Type | Description
-------- | ---- | -----------
`$this->cron_interval` |  | 

Source: [class-tainacan-background-process-base.php](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/class-tainacan-background-process-base.php), [line 457](https://github.com/tainacan/tainacan/blob/master/src/classes/background-process/class-tainacan-background-process-base.php#L457-L468)

---------------------------------
<br>

## `tainacan-collection-to-string` <!-- {docsify-ignore} -->

*Represents a Tainacan Collection entity.*

Collections are the main organizational units in Tainacan, containing
items and their associated metadata, filters, and display settings.


Argument | Type | Description
-------- | ---- | -----------
`$this->get_name()` |  | 
`$this` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-collection.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-collection.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-collection.php#L7-L92)

---------------------------------
<br>

## `tainacan-collection-to-array` <!-- {docsify-ignore} -->

*Represents a Tainacan Collection entity.*

Collections are the main organizational units in Tainacan, containing
items and their associated metadata, filters, and display settings.


Argument | Type | Description
-------- | ---- | -----------
`$array_collection` |  | 
`$this` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-collection.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-collection.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-collection.php#L7-L105)

---------------------------------
<br>

## `tainacan-collection-get-attachments` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$attachments` |  | 
`$exclude` | `null` | 
`$this` |  | 

Source: [class-tainacan-collection.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-collection.php), [line 229](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-collection.php#L229-L252)

---------------------------------
<br>

## `tainacan-collection-get-author-name` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$name` |  | 
`$this` |  | 

Source: [class-tainacan-collection.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-collection.php), [line 255](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-collection.php#L255-L260)

---------------------------------
<br>

## `tainacan-collection-get-thumbnail` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$thumbs` |  | 
`$this` |  | 

Source: [class-tainacan-collection.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-collection.php), [line 263](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-collection.php#L263-L276)

---------------------------------
<br>

## `tainacan-collection-get-header-image` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$header_image` |  | 
`$this` |  | 

Source: [class-tainacan-collection.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-collection.php), [line 279](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-collection.php#L279-L284)

---------------------------------
<br>

## `tainacan-status-require-validation` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`['publish', 'future', 'private', 'pending']` |  | 

Source: [class-tainacan-collection.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-collection.php), [line 1178](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-collection.php#L1178-L1183)

---------------------------------
<br>

## `tainacan-term-to-string` <!-- {docsify-ignore} -->

*Represents a Tainacan Term entity.*

Terms are individual entries within taxonomies, representing
specific categories or classifications for organizing items.


Argument | Type | Description
-------- | ---- | -----------
`$this->get_name()` |  | 
`$this` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-term.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-term.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-term.php#L7-L62)

---------------------------------
<br>

## `tainacan-terms-hierarchy-html-separator` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`'>'` |  | 

Source: [class-tainacan-term.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-term.php), [line 70](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-term.php#L70-L70)

---------------------------------
<br>

## `tainacan-term-to-array` <!-- {docsify-ignore} -->

*Represents a Tainacan Term entity.*

Terms are individual entries within taxonomies, representing
specific categories or classifications for organizing items.


Argument | Type | Description
-------- | ---- | -----------
`$term_array` |  | 
`$this` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-term.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-term.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-term.php#L7-L83)

---------------------------------
<br>

## `tainacan-term-get-thumbnail` <!-- {docsify-ignore} -->

*Gets the thumbnail*

Each size is represented as an array in the format returned by


Argument | Type | Description
-------- | ---- | -----------
`$thumbs` |  | 
`$this` |  | 

Source: [class-tainacan-term.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-term.php), [line 171](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-term.php#L171-L192)

---------------------------------
<br>

## `tainacan-term-to-html` <!-- {docsify-ignore} -->

*Represents a Tainacan Term entity.*

Terms are individual entries within taxonomies, representing
specific categories or classifications for organizing items.


Argument | Type | Description
-------- | ---- | -----------
`$return` |  | 
`$this` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-term.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-term.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-term.php#L7-L309)

---------------------------------
<br>

## `tainacan-item-to-string` <!-- {docsify-ignore} -->

*Represents a Tainacan Item entity.*

Items are the main content entities in Tainacan, containing
metadata values, attachments, and relationships within collections.


Argument | Type | Description
-------- | ---- | -----------
`$this->get_title()` |  | 
`$this` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L7-L56)

---------------------------------
<br>

## `tainacan-item-to-array` <!-- {docsify-ignore} -->

*Represents a Tainacan Item entity.*

Items are the main content entities in Tainacan, containing
metadata values, attachments, and relationships within collections.


Argument | Type | Description
-------- | ---- | -----------
`$array_item` |  | 
`$this` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L7-L68)

---------------------------------
<br>

## `tainacan-item-get-attachments` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$attachments` |  | 
`$exclude` | `null` | 
`$this` |  | 

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 85](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L85-L113)

---------------------------------
<br>

## `tainacan-item-get-author-name` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$name` |  | 
`$this` |  | 

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 118](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L118-L123)

---------------------------------
<br>

## `tainacan-item-get-author-login` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$name` |  | 
`$this` |  | 

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 126](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L126-L131)

---------------------------------
<br>

## `tainacan-item-get-thumbnail` <!-- {docsify-ignore} -->

*Gets the thumbnail list of files*

Each size is represented as an array in the format returned by


Argument | Type | Description
-------- | ---- | -----------
`$thumbs` |  | 
`$this` |  | 

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 134](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L134-L155)

---------------------------------
<br>

## `comments_open` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$comment_status == 'open'` |  | 
`$this->get_id()` |  | 

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 334](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L334-L334)

---------------------------------
<br>

## `tainacan-status-require-validation` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`['publish', 'future', 'private', 'pending']` |  | 

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 496](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L496-L501)

---------------------------------
<br>

## `tainacan-status-require-validation` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`['publish', 'future', 'private', 'pending']` |  | 

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 542](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L542-L547)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-filter-args` <!-- {docsify-ignore} -->

*Filter the arguments passed to the get_item_metadatum_as_html function*


Argument | Type | Description
-------- | ---- | -----------
`$args` | `array` | The arguments passed to the function
`$item_metadatum` |  | 

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 806](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L806-L811)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-before` <!-- {docsify-ignore} -->

*Return a single item metadata as a HTML string to be used as output.*

Each metadata is a label with the metadatum name and the value.

This function expects a $item_metadatum object. For a more generic approach, check the get_metadata_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$before` |  | 
`$item_metadatum` | `object` | The Item Metadatum object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 755](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L755-L833)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-before--type-{$metadata_type}` <!-- {docsify-ignore} -->

*Return a single item metadata as a HTML string to be used as output.*

Each metadata is a label with the metadatum name and the value.

This function expects a $item_metadatum object. For a more generic approach, check the get_metadata_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$before` |  | 
`$item_metadatum` | `object` | The Item Metadatum object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 755](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L755-L834)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-before--id-{$metadatum_id}` <!-- {docsify-ignore} -->

*Return a single item metadata as a HTML string to be used as output.*

Each metadata is a label with the metadatum name and the value.

This function expects a $item_metadatum object. For a more generic approach, check the get_metadata_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$before` |  | 
`$item_metadatum` | `object` | The Item Metadatum object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 755](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L755-L835)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-before--index-{$metadatum_index}` <!-- {docsify-ignore} -->

*Return a single item metadata as a HTML string to be used as output.*

Each metadata is a label with the metadatum name and the value.

This function expects a $item_metadatum object. For a more generic approach, check the get_metadata_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$before` |  | 
`$item_metadatum` | `object` | The Item Metadatum object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 755](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L755-L837)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-before-title` <!-- {docsify-ignore} -->

*Return a single item metadata as a HTML string to be used as output.*

Each metadata is a label with the metadatum name and the value.

This function expects a $item_metadatum object. For a more generic approach, check the get_metadata_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$metadatum_title_before` |  | 
`$item_metadatum` | `object` | The Item Metadatum object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 755](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L755-L845)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-after-title` <!-- {docsify-ignore} -->

*Return a single item metadata as a HTML string to be used as output.*

Each metadata is a label with the metadatum name and the value.

This function expects a $item_metadatum object. For a more generic approach, check the get_metadata_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$metadatum_title_after` |  | 
`$item_metadatum` | `object` | The Item Metadatum object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 755](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L755-L854)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-before-value` <!-- {docsify-ignore} -->

*Return a single item metadata as a HTML string to be used as output.*

Each metadata is a label with the metadatum name and the value.

This function expects a $item_metadatum object. For a more generic approach, check the get_metadata_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$metadatum_value_before` |  | 
`$item_metadatum` | `object` | The Item Metadatum object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 755](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L755-L859)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-after-value` <!-- {docsify-ignore} -->

*Return a single item metadata as a HTML string to be used as output.*

Each metadata is a label with the metadatum name and the value.

This function expects a $item_metadatum object. For a more generic approach, check the get_metadata_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$metadatum_value_after` |  | 
`$item_metadatum` | `object` | The Item Metadatum object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 755](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L755-L861)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-after--index-{$metadatum_index}` <!-- {docsify-ignore} -->

*Return a single item metadata as a HTML string to be used as output.*

Each metadata is a label with the metadatum name and the value.

This function expects a $item_metadatum object. For a more generic approach, check the get_metadata_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$after` |  | 
`$item_metadatum` | `object` | The Item Metadatum object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 755](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L755-L868)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-after--id-{$metadatum_id}` <!-- {docsify-ignore} -->

*Return a single item metadata as a HTML string to be used as output.*

Each metadata is a label with the metadatum name and the value.

This function expects a $item_metadatum object. For a more generic approach, check the get_metadata_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$after` |  | 
`$item_metadatum` | `object` | The Item Metadatum object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 755](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L755-L872)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-after--type-{$metadata_type}` <!-- {docsify-ignore} -->

*Return a single item metadata as a HTML string to be used as output.*

Each metadata is a label with the metadatum name and the value.

This function expects a $item_metadatum object. For a more generic approach, check the get_metadata_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$after` |  | 
`$item_metadatum` | `object` | The Item Metadatum object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 755](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L755-L873)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-after` <!-- {docsify-ignore} -->

*Return a single item metadata as a HTML string to be used as output.*

Each metadata is a label with the metadatum name and the value.

This function expects a $item_metadatum object. For a more generic approach, check the get_metadata_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$after` |  | 
`$item_metadatum` | `object` | The Item Metadatum object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 755](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L755-L874)

---------------------------------
<br>

## `tainacan-item-get-document-as-html` <!-- {docsify-ignore} -->

*Gets the document as a html. May be a text, link, iframe, image, audio.*

..


Argument | Type | Description
-------- | ---- | -----------
`wp_kses_tainacan($output)` |  | 
`$img_size` |  | 
`$this` |  | 

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 884](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L884-L931)

---------------------------------
<br>

## `tainacan-item-get-attachment-as-html` <!-- {docsify-ignore} -->

*Gets the attachment as a html. May be an iframe, image, audio.*

..


Argument | Type | Description
-------- | ---- | -----------
`wp_kses_tainacan($output)` |  | 
`$img_size` |  | 
`$this` |  | 

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 934](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L934-L964)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-filter-args` <!-- {docsify-ignore} -->

*Filter the arguments passed to the get_metadata_section_as_html function*


Argument | Type | Description
-------- | ---- | -----------
`$args` | `array` | The arguments passed to the function
`$metadata_section` | `object` | The Metadata Section object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 1272](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L1272-L1277)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-before` <!-- {docsify-ignore} -->

*Return a single item metadata section as a HTML string to be used as output.*

A metadata section is a label with the list of its metadata name and value.

This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$before` |  | 
`$metadata_section` | `\Tainacan\Entities\Metadata_Section` | The Metadata Section object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 1178](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L1178-L1295)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-before--id-{$section_id}` <!-- {docsify-ignore} -->

*Return a single item metadata section as a HTML string to be used as output.*

A metadata section is a label with the list of its metadata name and value.

This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$before` |  | 
`$metadata_section` | `\Tainacan\Entities\Metadata_Section` | The Metadata Section object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 1178](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L1178-L1296)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-before--index-{$section_index}` <!-- {docsify-ignore} -->

*Return a single item metadata section as a HTML string to be used as output.*

A metadata section is a label with the list of its metadata name and value.

This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$before` |  | 
`$metadata_section` | `\Tainacan\Entities\Metadata_Section` | The Metadata Section object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 1178](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L1178-L1298)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-before-name` <!-- {docsify-ignore} -->

*Return a single item metadata section as a HTML string to be used as output.*

A metadata section is a label with the list of its metadata name and value.

This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$before_name` |  | 
`$metadata_section` | `\Tainacan\Entities\Metadata_Section` | The Metadata Section object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 1178](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L1178-L1313)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-before-name--id-{$section_id}` <!-- {docsify-ignore} -->

*Return a single item metadata section as a HTML string to be used as output.*

A metadata section is a label with the list of its metadata name and value.

This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$before_name` |  | 
`$metadata_section` | `\Tainacan\Entities\Metadata_Section` | The Metadata Section object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 1178](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L1178-L1314)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-before-name--index-{$section_index}` <!-- {docsify-ignore} -->

*Return a single item metadata section as a HTML string to be used as output.*

A metadata section is a label with the list of its metadata name and value.

This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$before_name` |  | 
`$metadata_section` | `\Tainacan\Entities\Metadata_Section` | The Metadata Section object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 1178](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L1178-L1316)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-after-name` <!-- {docsify-ignore} -->

*Return a single item metadata section as a HTML string to be used as output.*

A metadata section is a label with the list of its metadata name and value.

This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$after_name` |  | 
`$metadata_section` | `\Tainacan\Entities\Metadata_Section` | The Metadata Section object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 1178](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L1178-L1323)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-after-name--id-{$section_id}` <!-- {docsify-ignore} -->

*Return a single item metadata section as a HTML string to be used as output.*

A metadata section is a label with the list of its metadata name and value.

This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$after_name` |  | 
`$metadata_section` | `\Tainacan\Entities\Metadata_Section` | The Metadata Section object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 1178](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L1178-L1324)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-after-name--index-{$section_index}` <!-- {docsify-ignore} -->

*Return a single item metadata section as a HTML string to be used as output.*

A metadata section is a label with the list of its metadata name and value.

This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$after_name` |  | 
`$metadata_section` | `\Tainacan\Entities\Metadata_Section` | The Metadata Section object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 1178](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L1178-L1326)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-before-metadata-list` <!-- {docsify-ignore} -->

*Return a single item metadata section as a HTML string to be used as output.*

A metadata section is a label with the list of its metadata name and value.

This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$before_description` |  | 
`$metadata_section` | `\Tainacan\Entities\Metadata_Section` | The Metadata Section object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 1178](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L1178-L1345)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-before-metadata-list--id-{$section_id}` <!-- {docsify-ignore} -->

*Return a single item metadata section as a HTML string to be used as output.*

A metadata section is a label with the list of its metadata name and value.

This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$before_description` |  | 
`$metadata_section` | `\Tainacan\Entities\Metadata_Section` | The Metadata Section object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 1178](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L1178-L1346)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-before-metadata-list--index-{$section_index}` <!-- {docsify-ignore} -->

*Return a single item metadata section as a HTML string to be used as output.*

A metadata section is a label with the list of its metadata name and value.

This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$before_description` |  | 
`$metadata_section` | `\Tainacan\Entities\Metadata_Section` | The Metadata Section object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 1178](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L1178-L1348)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-after-metadata-list` <!-- {docsify-ignore} -->

*Return a single item metadata section as a HTML string to be used as output.*

A metadata section is a label with the list of its metadata name and value.

This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$after_description` |  | 
`$metadata_section` | `\Tainacan\Entities\Metadata_Section` | The Metadata Section object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 1178](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L1178-L1380)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-after-metadata-list--id-{$section_id}` <!-- {docsify-ignore} -->

*Return a single item metadata section as a HTML string to be used as output.*

A metadata section is a label with the list of its metadata name and value.

This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$after_description` |  | 
`$metadata_section` | `\Tainacan\Entities\Metadata_Section` | The Metadata Section object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 1178](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L1178-L1381)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-after-metadata-list--index-{$section_index}` <!-- {docsify-ignore} -->

*Return a single item metadata section as a HTML string to be used as output.*

A metadata section is a label with the list of its metadata name and value.

This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$after_description` |  | 
`$metadata_section` | `\Tainacan\Entities\Metadata_Section` | The Metadata Section object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 1178](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L1178-L1383)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-after--index-{$section_index}` <!-- {docsify-ignore} -->

*Return a single item metadata section as a HTML string to be used as output.*

A metadata section is a label with the list of its metadata name and value.

This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$after` |  | 
`$metadata_section` | `\Tainacan\Entities\Metadata_Section` | The Metadata Section object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 1178](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L1178-L1394)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-after--id-{$section_id}` <!-- {docsify-ignore} -->

*Return a single item metadata section as a HTML string to be used as output.*

A metadata section is a label with the list of its metadata name and value.

This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$after` |  | 
`$metadata_section` | `\Tainacan\Entities\Metadata_Section` | The Metadata Section object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 1178](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L1178-L1396)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-after` <!-- {docsify-ignore} -->

*Return a single item metadata section as a HTML string to be used as output.*

A metadata section is a label with the list of its metadata name and value.

This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function


Argument | Type | Description
-------- | ---- | -----------
`$after` |  | 
`$metadata_section` | `\Tainacan\Entities\Metadata_Section` | The Metadata Section object

Source: [class-tainacan-item.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php), [line 1178](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item.php#L1178-L1397)

---------------------------------
<br>

## `tainacan-log-to-string` <!-- {docsify-ignore} -->

*Represents a Tainacan Log entity.*

Logs track changes and operations within Tainacan, providing
an audit trail for entity modifications and system activities.


Argument | Type | Description
-------- | ---- | -----------
`$this->get_title()` |  | 
`$this` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-log.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-log.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-log.php#L7-L49)

---------------------------------
<br>

## `tainacan-log-to-array` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$array_log` |  | 
`$this` |  | 

Source: [class-tainacan-log.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-log.php), [line 52](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-log.php#L52-L60)

---------------------------------
<br>

## `tainacan-log-set-title` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$value` |  | 

Source: [class-tainacan-log.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-log.php), [line 186](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-log.php#L186-L186)

---------------------------------
<br>

## `tainacan-metadatum-to-string` <!-- {docsify-ignore} -->

*Represents a Tainacan Metadatum entity.*

Metadata definitions that specify the structure and validation
rules for item metadata within collections.


Argument | Type | Description
-------- | ---- | -----------
`$this->get_name()` |  | 
`$this` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-metadatum.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-metadatum.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-metadatum.php#L7-L59)

---------------------------------
<br>

## `tainacan-filter-to-string` <!-- {docsify-ignore} -->

*Represents a Tainacan Filter entity.*

Filters define search and filtering capabilities for collections,
allowing users to narrow down item results based on metadata criteria.


Argument | Type | Description
-------- | ---- | -----------
`$this->get_name()` |  | 
`$this` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-filter.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-filter.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-filter.php#L7-L42)

---------------------------------
<br>

## `tainacan-filter-to-array` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$filter_array` |  | 
`$this` |  | 

Source: [class-tainacan-filter.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-filter.php), [line 45](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-filter.php#L45-L66)

---------------------------------
<br>

## `tainacan-item-metadata-get-multivalue-prefix` <!-- {docsify-ignore} -->

*Gets the string used before each value when concatenating multiple values
to display item metadata value as html or string*


Argument | Type | Description
-------- | ---- | -----------
`$value` |  | 
`$this` |  | 

Source: [class-tainacan-item-metadata-entity.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item-metadata-entity.php), [line 73](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item-metadata-entity.php#L73-L92)

---------------------------------
<br>

## `tainacan-item-metadata-get-multivalue-suffix` <!-- {docsify-ignore} -->

*Gets the string used after each value when concatenating multiple values
to display item metadata value as html or string*


Argument | Type | Description
-------- | ---- | -----------
`$value` |  | 
`$this` |  | 

Source: [class-tainacan-item-metadata-entity.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item-metadata-entity.php), [line 95](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item-metadata-entity.php#L95-L114)

---------------------------------
<br>

## `tainacan-item-metadata-get-multivalue-separator` <!-- {docsify-ignore} -->

*Gets the string used in between each value when concatenating multiple values
to display item metadata value as html or string*


Argument | Type | Description
-------- | ---- | -----------
`$value` |  | 
`$this` |  | 

Source: [class-tainacan-item-metadata-entity.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item-metadata-entity.php), [line 117](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item-metadata-entity.php#L117-L136)

---------------------------------
<br>

## `tainacan-item-metadata-get-value-as-html` <!-- {docsify-ignore} -->

*Filter the item metadatum value as HTML*


Argument | Type | Description
-------- | ---- | -----------
`$return` | `string` | the item metadatum value HTML string
`$this` | `\Tainacan\Entities\Item_Metadata_Entity` | the item metadatum entity

Source: [class-tainacan-item-metadata-entity.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item-metadata-entity.php), [line 183](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item-metadata-entity.php#L183-L191)

---------------------------------
<br>

## `tainacan-item-metadata-get-value-as-string` <!-- {docsify-ignore} -->

*Filter the item metadatum value as string*


Argument | Type | Description
-------- | ---- | -----------
`strip_tags($this->get_value_as_html())` |  | 
`$this` | `\Tainacan\Entities\Item_Metadata_Entity` | the item metadatum entity

Source: [class-tainacan-item-metadata-entity.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item-metadata-entity.php), [line 210](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item-metadata-entity.php#L210-L218)

---------------------------------
<br>

## `tainacan-item-metadata-to-array` <!-- {docsify-ignore} -->

*Convert the object to an Array*


Argument | Type | Description
-------- | ---- | -----------
`$as_array` |  | 
`$this` |  | 

Source: [class-tainacan-item-metadata-entity.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item-metadata-entity.php), [line 300](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item-metadata-entity.php#L300-L328)

---------------------------------
<br>

## `tainacan-status-require-validation` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$validation_statuses` |  | 

Source: [class-tainacan-item-metadata-entity.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item-metadata-entity.php), [line 502](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-item-metadata-entity.php#L502-L502)

---------------------------------
<br>

## `tainacan-taxonomy-to-string` <!-- {docsify-ignore} -->

*Represents a Tainacan Taxonomy entity.*

Taxonomies define hierarchical classification systems for organizing
and categorizing items within Tainacan collections.


Argument | Type | Description
-------- | ---- | -----------
`$this->get_name()` |  | 
`$this` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-taxonomy.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-taxonomy.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-taxonomy.php#L7-L54)

---------------------------------
<br>

## `tainacan-status-require-validation` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`['publish', 'future', 'private', 'pending']` |  | 

Source: [class-tainacan-taxonomy.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-taxonomy.php), [line 262](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-taxonomy.php#L262-L267)

---------------------------------
<br>

## `tainacan-entity-get-property` <!-- {docsify-ignore} -->

*return the value for a mapped property*


Argument | Type | Description
-------- | ---- | -----------
`$value` |  | 
`$prop` | `string` | id of property
`$this` |  | 

Source: [class-tainacan-entity.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-entity.php), [line 185](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-entity.php#L185-L199)

---------------------------------
<br>

## `tainacan-entity-set-property` <!-- {docsify-ignore} -->

*set the value of a mapped property*

This is a protected method. If you want to set an entity prop
using the prop name dynamically, use the set() method


Argument | Type | Description
-------- | ---- | -----------
`$value` | `mixed` | the value to be setted
`$prop` | `string` | id of the property
`$this` |  | 

Source: [class-tainacan-entity.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-entity.php), [line 202](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-entity.php#L202-L213)

---------------------------------
<br>

## `tainacan-set-post-status` <!-- {docsify-ignore} -->

*set the status of the entity*


Argument | Type | Description
-------- | ---- | -----------
`$value` | `string` | 

Source: [class-tainacan-entity.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-entity.php), [line 252](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-entity.php#L252-L257)

---------------------------------
<br>

## `tainacan-get-post-status` <!-- {docsify-ignore} -->

*Abstract base class for all Tainacan entities.*

Provides common functionality for all Tainacan entities including
validation, error handling, and WordPress post type integration.


Argument | Type | Description
-------- | ---- | -----------
`$value` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-entity.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-entity.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-entity.php#L7-L346)

---------------------------------
<br>

## `{$hook_prefix}-to-array` <!-- {docsify-ignore} -->

*Abstract base class for all Tainacan entities.*

Provides common functionality for all Tainacan entities including
validation, error handling, and WordPress post type integration.


Argument | Type | Description
-------- | ---- | -----------
`$attributes` |  | 
`$this` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-entity.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-entity.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-entity.php#L7-L406)

---------------------------------
<br>

## `tainacan-metadata-section-to-string` <!-- {docsify-ignore} -->

*Represents a Tainacan Metadata Section entity.*

Metadata sections organize metadata fields into logical groups
within collections, improving the user interface and organization.


Argument | Type | Description
-------- | ---- | -----------
`$this->get_name()` |  | 
`$this` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-metadata-section.php](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-metadata-section.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/classes/entities/class-tainacan-metadata-section.php#L7-L40)

---------------------------------
<br>

## `tainacan-available-admin-ui-options` <!-- {docsify-ignore} -->

*Lists a translatable and grouped version of the available admin ui options*


Argument | Type | Description
-------- | ---- | -----------
`array('navigation' => array('label' => __('Navigation', 'tainacan'), 'description' => __('Options related to the overall plugin navigation such as sidemenu and fullscreen mode.', 'tainacan'), 'items' => array('forceFullscreenAdminMode' => __('Force Tainacan to always overlap WordPress admin menu and sidebar', 'tainacan'), 'hideBreadcrumbs' => __('Hide breadcrumbs', 'tainacan'), 'hideWordPressShorcutButton' => __('Hide WordPress shortcut button', 'tainacan'), 'hideSiteShorcutButton' => __('Hide site shortcut button', 'tainacan'), 'hideFullscreenTogglerButton' => __('Hide fullscreen toggler button', 'tainacan'), 'hideMenuCollapserButton' => __('Hide menu collapser button', 'tainacan'), 'hideNavigationSidebar' => __('Hide entire navigation side menu', 'tainacan'), 'hideNavigationHomeButton' => __('Hide home button in side menu', 'tainacan'), 'hideNavigationRepositoryMenu' => __('Hide "Repository" menu button in side menu', 'tainacan'), 'hideNavigationTaxonomiesButton' => __('Hide taxonomies button in repository submenu', 'tainacan'), 'hideNavigationMetadataButton' => __('Hide metadata button in repository submenu', 'tainacan'), 'hideNavigationFiltersButton' => __('Hide filters button in repository submenu', 'tainacan'), 'hideNavigationImportersButton' => __('Hide importers button in repository submenu', 'tainacan'), 'hideNavigationExportersButton' => __('Hide exporters button in repository submenu', 'tainacan'), 'hideNavigationActivitiesButton' => __('Hide activities button in repository submenu', 'tainacan'), 'hideNavigationCapabilitiesButton' => __('Hide permissions button in repository submenu', 'tainacan'), 'hideNavigationProcessesButton' => __('Hide processes button in repository submenu', 'tainacan'), 'hideNavigationReportsButton' => __('Hide reports button in repository submenu', 'tainacan'), 'hideNavigationCollectionsMenu' => __('Hide "Collections" menu button in side menu', 'tainacan'), 'hideNavigationCollectionsButton' => __('Hide collections list button in collections submenu', 'tainacan'), 'hideNavigationItemsButton' => __('Hide "All items" button in collections submenu', 'tainacan'), 'hideNavigationMyItemsButton' => __('Hide "My items" button in collections submenu', 'tainacan'), 'hideNavigationCollectionName' => __('Hide collection name in current collection submenu', 'tainacan'), 'hideNavigationCollectionItemsButton' => __('Hide "All items" button in current collection submenu', 'tainacan'), 'hideNavigationCollectionMyItemsButton' => __('Hide "My items" button in current collection submenu', 'tainacan'), 'hideNavigationCollectionSettingsButton' => __('Hide settings button in current collection submenu', 'tainacan'), 'hideNavigationCollectionMetadataButton' => __('Hide metadata button in current collection submenu', 'tainacan'), 'hideNavigationCollectionFiltersButton' => __('Hide filters button in current collection submenu', 'tainacan'), 'hideNavigationCollectionExportersButton' => __('Hide exporters button in current collection submenu', 'tainacan'), 'hideNavigationCollectionActivitiesButton' => __('Hide activities button in current collection submenu', 'tainacan'), 'hideNavigationCollectionCapabilitiesButton' => __('Hide permissions button in current collection submenu', 'tainacan'), 'hideNavigationCollectionReportsButton' => __('Hide reports button in current collection submenu', 'tainacan'), 'hideExternalEntityLinks' => __('Hide external site links for item, collection, taxonomies and other public pages', 'tainacan'), 'hideNavigationOtherMenu' => __('Hide "Other" menu button in side menu', 'tainacan'), 'hideNavigationSettingsButton' => __('Hide "Settings" button in side menu', 'tainacan'), 'hideNavigationRolesButton' => __('Hide "Roles" button in side menu', 'tainacan'), 'hideNavigationSystemCheckButton' => __('Hide "System Check" button in side menu', 'tainacan'))), 'dashboard' => array('label' => __('Dashboard', 'tainacan'), 'description' => __('Options related to the Dashboard page and its cards. Notice that each user may still hide the cards that remain in the screen.', 'tainacan'), 'items' => array('disableDashboardCardsSorting' => __('Disable dashboard cards sorting', 'tainacan'), 'hideDashboardRepositoryCard' => __('Hide repository card', 'tainacan'), 'hideDashboardRepositoryCardTaxonomiesButton' => __('Hide repository card taxonomies button', 'tainacan'), 'hideDashboardRepositoryCardMetadataButton' => __('Hide repository card metadata button', 'tainacan'), 'hideDashboardRepositoryCardFiltersButton' => __('Hide repository card filters button', 'tainacan'), 'hideDashboardRepositoryCardImportersButton' => __('Hide repository card importers button', 'tainacan'), 'showDashboardRepositoryCardExportersButton' => __('Show repository card exporters button', 'tainacan'), 'showDashboardRepositoryCardProcessesButton' => __('Show repository card processes button', 'tainacan'), 'showDashboardRepositoryCardActivitiesButton' => __('Show repository card activities button', 'tainacan'), 'showDashboardRepositoryCardCapabilitiesButton' => __('Show repository card capabilities button', 'tainacan'), 'showDashboardRepositoryCardReportsButton' => __('Show repository card reports button', 'tainacan'), 'hideDashboardCollectionsCard' => __('Hide collections card', 'tainacan'), 'hideDashboardCollectionsCardCollectionsListButton' => __('Hide collections card collections list button', 'tainacan'), 'hideDashboardCollectionsCardNewCollectionButton' => __('Hide collections card new collection button', 'tainacan'), 'hideDashboardCollectionsCardItemsListButton' => __('Hide collections card items list button', 'tainacan'), 'hideDashboardCollectionsCardMyItemsListButton' => __('Hide collections card "My items list" button', 'tainacan'), 'hideDashboardCollectionCards' => __('Hide collection cards', 'tainacan'), 'showOnlyCollectionCardsThatUserCanEdit' => __('Show only collections that user can edit items', 'tainacan'), 'showOnlyCollectionCardsAuthoredByUser' => __('Show only collections authored by the user', 'tainacan'), 'hideDashboardCollectionCardsItemsButton' => __('Hide collection cards items button', 'tainacan'), 'hideDashboardCollectionCardsExternalLinkButton' => __('Hide collection cards external link button', 'tainacan'), 'showDashboardCollectionCardsMyItemsButton' => __('Show collection cards "My items" button', 'tainacan'), 'hideDashboardCollectionCardsMetadataButton' => __('Hide collection cards metadata button', 'tainacan'), 'showDashboardCollectionCardsFiltersButton' => __('Show collection cards filters button', 'tainacan'), 'showDashboardCollectionCardsImportersButton' => __('Show collection cards importers button', 'tainacan'), 'showDashboardCollectionCardsExportersButton' => __('Show collection cards exporters button', 'tainacan'), 'showDashboardCollectionCardsActivitiesButton' => __('Show collection cards activities button', 'tainacan'), 'showDashboardCollectionCardsCapabilitiesButton' => __('Show collection cards capabilities button', 'tainacan'), 'showDashboardCollectionCardsReportsButton' => __('Show collection cards reports button', 'tainacan'), 'hideDashboardInfoCard' => __('Hide info card', 'tainacan'), 'hideDashboardInfoCardForumButton' => __('Hide info card user\'s forum button', 'tainacan'), 'hideDashboardInfoCardFAQButton' => __('Hide info card FAQ button', 'tainacan'), 'hideDashboardInfoCardWikiButton' => __('Hide info card wiki button', 'tainacan'), 'hideDashboardInfoCardSourceCodeButton' => __('Hide info card source code button', 'tainacan'), 'showDashboardInfoCardVideosButton' => __('Show info card videos button', 'tainacan'), 'hideDashboardNewsCard' => __('Hide news card', 'tainacan'))), 'items-list' => array('label' => __('Items list', 'tainacan'), 'description' => __('Options related to the admin pages that display the faceted search with items list.', 'tainacan'), 'items' => array('hideItemsListPageTitle' => __('Hide page title', 'tainacan'), 'hideItemsListBulkActionsButton' => __('Hide bulk actions button', 'tainacan'), 'hideItemsListMultipleSelection' => __('Hide multiple item selection', 'tainacan'), 'hideItemsListSelection' => __('Hide individual item selection', 'tainacan'), 'hideItemsListExposersButton' => __('Hide "View as..." button', 'tainacan'), 'hideItemsListViewModesButton' => __('Hide view mode selector button', 'tainacan'), 'hideDisplayedMetadataDropdown' => __('Hide displayed metadata dropdown', 'tainacan'), 'hideItemsListAdvancedSearch' => __('Hide advanced search', 'tainacan'), 'hideItemsListStatusTabs' => __('Hide status tabs', 'tainacan'), 'hideItemsListStatusTabsTotalItems' => __('Hide total items in status tabs', 'tainacan'), 'hideItemsListCreationDropdownBulkAdd' => __('Hide bulk add button in creation dropdown', 'tainacan'), 'hideItemsListCreationDropdownImport' => __('Hide import button in creation dropdown', 'tainacan'), 'hideItemsListContextMenu' => __('Hide right-click context menu', 'tainacan'), 'hideItemsListFilterCreationButton' => __('Hide create filters button', 'tainacan'), 'hideItemsListGoToPageButton' => __('Hide "Go to page" button', 'tainacan'), 'hideItemsListItemsPerPageButton' => __('Hide "Items per page" button', 'tainacan'))), 'item-editing-page' => array('label' => __('Item editing page', 'tainacan'), 'description' => __('Options related to the item edition form. Some of this settings may also be achieved via collection settings, but doing here will override any option.', 'tainacan'), 'items' => array('hideItemEditionPageTitle' => __('Hide page title', 'tainacan'), 'itemEditionPublicationSectionInsideTabs' => __('Show publication section inside tabs', 'tainacan'), 'itemEditionDocumentInsideTabs' => __('Show document entry inside tabs', 'tainacan'), 'itemEditionAttachmentsInsideTabs' => __('Show attachments inside tabs', 'tainacan'), 'hideItemEditionPublicationSection' => __('Hide publication section', 'tainacan'), 'hideItemEditionStatusOption' => __('Hide status options', 'tainacan'), 'hideItemEditionStatusPublishOption' => __('Hide public status option', 'tainacan'), 'hideItemEditionStatusPrivateOption' => __('Hide private status option', 'tainacan'), 'hideItemEditionStatusPendingOption' => __('Hide pending status option', 'tainacan'), 'hideItemEditionCommentsToggle' => __('Hide comments option', 'tainacan'), 'hideItemEditionDocument' => __('Hide document entry completely', 'tainacan'), 'hideItemEditionDocumentFileInput' => __('Hide file type document entry', 'tainacan'), 'hideItemEditionDocumentTextInput' => __('Hide text type document entry', 'tainacan'), 'hideItemEditionDocumentUrlInput' => __('Hide URL type document entry', 'tainacan'), 'hideItemEditionThumbnail' => __('Hide thumbnail', 'tainacan'), 'hideItemEditionAttachments' => __('Hide attachments', 'tainacan'), 'itemEditionStatusOptionOnFooterDropdown' => __('Show status option in footer dropdown', 'tainacan'), 'allowItemEditionModalInsideModal' => __('Allow item creation modal inside another modal (experimental)', 'tainacan'))), 'item-page' => array('label' => __('Item page', 'tainacan'), 'description' => __('Options related to the item page inside the admin', 'tainacan'), 'items' => array('hideItemSinglePageTitle' => __('Hide page title', 'tainacan'), 'hideItemSingleCurrentStatus' => __('Hide status', 'tainacan'), 'hideItemSingleCurrentVisibility' => __('Hide visibility status', 'tainacan'), 'hideItemSingleCommentsOpen' => __('Hide comments condition', 'tainacan'), 'hideItemSingleDocument' => __('Hide document', 'tainacan'), 'hideItemSingleThumbnail' => __('Hide thumbnail', 'tainacan'), 'hideItemSingleAttachments' => __('Hide attachments', 'tainacan'), 'hideItemSingleActivities' => __('Hide activities', 'tainacan'), 'hideItemSingleExposers' => __('Hide "View as..." button', 'tainacan'))))` |  | 

Source: [class-tainacan-admin-ui-options.php](https://github.com/tainacan/tainacan/blob/master/src/classes/traits/class-tainacan-admin-ui-options.php), [line 15](https://github.com/tainacan/tainacan/blob/master/src/classes/traits/class-tainacan-admin-ui-options.php#L15-L178)

---------------------------------
<br>

## `tainacan-svg-icons-folder-path` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$icons_folder_path` |  | 

Source: [class-tainacan-svg-icon.php](https://github.com/tainacan/tainacan/blob/master/src/classes/traits/class-tainacan-svg-icon.php), [line 13](https://github.com/tainacan/tainacan/blob/master/src/classes/traits/class-tainacan-svg-icon.php#L13-L22)

---------------------------------
<br>

## `tainacan-get-map-{$entity}` <!-- {docsify-ignore} -->

*{@inheritDoc}*


Argument | Type | Description
-------- | ---- | -----------
`['name' => ['map' => 'post_title', 'title' => __('Name', 'tainacan'), 'type' => 'string', 'description' => __('Name of the metadata section', 'tainacan'), 'on_error' => __('The name should be a text value and not empty', 'tainacan'), 'validation' => v::stringType()->notEmpty()], 'slug' => ['map' => 'post_name', 'title' => __('Slug', 'tainacan'), 'type' => 'string', 'description' => __('A unique and sanitized string representation of the metadata sction', 'tainacan')], 'status' => ['map' => 'post_status', 'title' => __('Status', 'tainacan'), 'type' => 'string', 'default' => 'publish', 'description' => __('Status for control of visibility and access.', 'tainacan')], 'description' => ['map' => 'post_content', 'title' => __('Description', 'tainacan'), 'type' => 'string', 'description' => __('The metadata section description.', 'tainacan'), 'default' => ''], 'description_bellow_name' => [
    'map' => 'meta',
    'title' => __('Description below name', 'tainacan'),
    'type' => 'string',
    'description' => __('Whether the section metadata description should be displayed below the name instead of inside a tooltip.', 'tainacan'),
    'on_error' => __('Please set the "Description below name" value as "yes" or "no"', 'tainacan'),
    'enum' => ['yes', 'no'],
    'validation' => v::stringType()->in(['yes', 'no']),
    // yes or no
    'default' => 'no',
], 'collection_id' => ['map' => 'meta', 'title' => __('Collection', 'tainacan'), 'type' => ['integer', 'string'], 'description' => __('The collection ID', 'tainacan')], 'is_conditional_section' => ['map' => 'meta', 'title' => __('Enable conditional section', 'tainacan'), 'type' => 'string', 'description' => __('Binds this section visibility to a set of rules related to some metadata values.', 'tainacan'), 'on_error' => __('Value should be "yes" or "no"', 'tainacan'), 'enum' => ['yes', 'no'], 'validation' => v::stringType()->in(['yes', 'no']), 'default' => 'no'], 'conditional_section_rules' => ['map' => 'meta', 'title' => __('Conditional section rules', 'tainacan'), 'type' => ['object', 'array'], 'description' => __('The conditions that will allow this section to be displayed, based on metadata values.', 'tainacan')]]` |  | 

Source: [class-tainacan-metadata-sections.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata-sections.php), [line 26](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata-sections.php#L26-L93)

---------------------------------
<br>

## `tainacan-fetch-args` <!-- {docsify-ignore} -->

*fetch metadata section based on ID or WP_Query args*

metadata section are stored as posts. Check WP_Query docs
to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
appropriate WP_Query argument

If a number is passed to $args, it will return a \Tainacan\Entities\Metadata_Section object. But if the post is not found or
does not match the entity post type, it will return an empty array


Argument | Type | Description
-------- | ---- | -----------
`$args` | `array` | WP_Query args \|\| int $args the metadata section id
`'metadata-section'` |  | 

Source: [class-tainacan-metadata-sections.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata-sections.php), [line 178](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata-sections.php#L178-L215)

---------------------------------
<br>

## `tainacan-get-map-{$entity}` <!-- {docsify-ignore} -->

*Repository for managing Tainacan logs.*

Implements a comprehensive logging system for tracking changes
and operations within Tainacan including entity modifications.


Argument | Type | Description
-------- | ---- | -----------
`[
    'title' => ['map' => 'post_title', 'title' => __('Title', 'tainacan'), 'type' => 'string', 'description' => __('The title of the log', 'tainacan'), 'on_error' => __('The title should be a text value and not empty', 'tainacan'), 'validation' => ''],
    'date' => ['map' => 'post_date', 'title' => __('Log date', 'tainacan'), 'type' => 'string', 'description' => __('The moment when the log was registered', 'tainacan')],
    'description' => ['map' => 'post_content', 'title' => __('Description', 'tainacan'), 'type' => 'string', 'description' => __('The log description', 'tainacan'), 'default' => '', 'validation' => ''],
    'slug' => ['map' => 'post_name', 'title' => __('Slug', 'tainacan'), 'type' => 'string', 'description' => __('The log slug', 'tainacan'), 'validation' => ''],
    'user_id' => ['map' => 'post_author', 'title' => __('User ID', 'tainacan'), 'type' => 'integer', 'description' => __('Unique identifier', 'tainacan'), 'validation' => ''],
    'item_id' => ['map' => 'meta', 'title' => __('Item ID', 'tainacan'), 'description' => __('Item ID', 'tainacan'), 'type' => 'integer'],
    // 'value'          => [
    // 	'map'         => 'meta',
    // 	'title'       => __( 'Actual value', 'tainacan' ),
    // 	'type'        => 'string',
    // 	'description' => __( 'The actual log value' ),
    // 	'validation'  => ''
    // ],
    'log_diffs' => [
        // deprecated
        'map' => 'meta',
        'title' => __('Log differences', 'tainacan'),
        'description' => __('Differences between old and new versions of object', 'tainacan'),
        'type' => 'string',
    ],
    'collection_id' => ['map' => 'meta', 'title' => __('Log collection relationship', 'tainacan'), 'description' => __('The ID of the collection that this log is related to', 'tainacan'), 'type' => 'string'],
    'object_id' => ['map' => 'meta', 'title' => __('Log item relationship', 'tainacan'), 'description' => __('The ID of the object that this log is related to', 'tainacan'), 'type' => ['string', 'integer']],
    'object_type' => ['map' => 'meta', 'title' => __('Log item relationship', 'tainacan'), 'description' => __('The type of the object that this log is related to', 'tainacan'), 'type' => 'string'],
    'old_value' => ['map' => 'meta', 'title' => __('Old value', 'tainacan'), 'description' => __('Value of the field previous to the edition registered by the log.', 'tainacan'), 'type' => 'string'],
    'new_value' => ['map' => 'meta', 'title' => __('New value', 'tainacan'), 'description' => __('Value of the field after the edition registered by the log.', 'tainacan'), 'type' => 'string'],
    'action' => ['map' => 'meta', 'title' => __('Action', 'tainacan'), 'description' => __('Type of action registered by the log.', 'tainacan'), 'type' => 'string'],
]` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-logs.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-logs.php), [line 10](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-logs.php#L10-L136)

---------------------------------
<br>

## `tainacan-fetch-args` <!-- {docsify-ignore} -->

*fetch logs based on ID or WP_Query args*

Logs are stored as posts. Check WP_Query docs
to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
appropriate WP_Query argument

If a number is passed to $args, it will return a \Tainacan\Entities\Log object.  But if the post is not found or
does not match the entity post type, it will return an empty array


Argument | Type | Description
-------- | ---- | -----------
`$args` | `array` | WP_Query args \|\| int $args the log id
`'logs'` |  | 

Source: [class-tainacan-logs.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-logs.php), [line 184](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-logs.php#L184-L223)

---------------------------------
<br>

## `tainacan-entity-diff` <!-- {docsify-ignore} -->

*Compare two repository entities and sets the current_diff property to be used in the insert hook*


Argument | Type | Description
-------- | ---- | -----------
`$diff` |  | 
`$unsaved` | `\Tainacan\Entities\Entity` | The new entity that is going to be saved
`$old` |  | 

Source: [class-tainacan-logs.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-logs.php), [line 376](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-logs.php#L376-L443)

---------------------------------
<br>

## `tainacan-entity-diff` <!-- {docsify-ignore} -->

*Repository for managing Tainacan logs.*

Implements a comprehensive logging system for tracking changes
and operations within Tainacan including entity modifications.


Argument | Type | Description
-------- | ---- | -----------
`$diff` |  | 
`$unsaved` |  | 
`$old` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-logs.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-logs.php), [line 10](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-logs.php#L10-L470)

---------------------------------
<br>

## `tainacan-get-map-{$entity}` <!-- {docsify-ignore} -->

*{@inheritDoc}*


Argument | Type | Description
-------- | ---- | -----------
`['name' => ['map' => 'post_title', 'title' => __('Name', 'tainacan'), 'type' => 'string', 'description' => __('Name of the metadata', 'tainacan'), 'on_error' => __('The name should be a text value and not empty', 'tainacan'), 'validation' => v::stringType()->notEmpty()], 'slug' => ['map' => 'post_name', 'title' => __('Slug', 'tainacan'), 'type' => 'string', 'description' => __('A unique and sanitized string representation of the metadata', 'tainacan')], 'order' => ['map' => 'menu_order', 'title' => __('Order', 'tainacan'), 'type' => ['string', 'integer'], 'description' => __('Metadata order. This metadata will be used if collections were manually ordered.', 'tainacan'), 'on_error' => __('The menu order should be a numeric value', 'tainacan')], 'parent' => ['map' => 'post_parent', 'title' => __('Parent', 'tainacan'), 'type' => 'integer', 'description' => __('Parent metadata', 'tainacan'), 'default' => 0], 'description' => ['map' => 'post_content', 'title' => __('Description', 'tainacan'), 'type' => 'string', 'description' => __('The metadatum description. This may provide information on how to fill this metadatum, which will appear inside a tooltip alongside the input label, or below it.', 'tainacan'), 'default' => ''], 'description_bellow_name' => [
    'map' => 'meta',
    'title' => __('Description below name', 'tainacan'),
    'type' => 'string',
    'description' => __('Whether the metadatum description should be displayed below the input label instead of inside a tooltip.', 'tainacan'),
    'on_error' => __('Please set the "Description below name" value as "yes" or "no"', 'tainacan'),
    'validation' => v::stringType()->in(['yes', 'no']),
    // yes or no
    'enum' => ['yes', 'no'],
    'default' => 'no',
], 'placeholder' => ['map' => 'meta', 'title' => __('Placeholder', 'tainacan'), 'type' => 'string', 'description' => __('The metadatum placeholder. This is a simple message that will appear inside textual input and may indicate to the user what kind of information is expected.', 'tainacan'), 'default' => ''], 'metadata_type' => ['map' => 'meta', 'title' => __('Type', 'tainacan'), 'type' => 'string', 'description' => __('The metadata type class name, such as Tainacan\\Metadata_Types\\Core_Title', 'tainacan'), 'on_error' => __('Metadata type is empty', 'tainacan'), 'validation' => v::stringType()->notEmpty()], 'required' => [
    'map' => 'meta',
    'title' => __('Required', 'tainacan'),
    'type' => 'string',
    'description' => __('The metadata is required. All items in this collection must fill this field', 'tainacan'),
    'on_error' => __('The metadata content is invalid', 'tainacan'),
    'validation' => v::stringType()->in(['yes', 'no']),
    // yes or no
    'enum' => ['yes', 'no'],
    'default' => 'no',
], 'collection_key' => [
    'map' => 'meta',
    'title' => __('Unique value', 'tainacan'),
    'type' => 'string',
    'description' => __('Metadata value should be unique accross all items in this collection', 'tainacan'),
    'on_error' => __('You cannot have two items with the same value for this metadatum', 'tainacan'),
    'validation' => v::stringType()->in(['yes', 'no']),
    // yes or no
    'enum' => ['yes', 'no'],
    'default' => 'no',
], 'multiple' => [
    'map' => 'meta',
    'title' => __('Multiple', 'tainacan'),
    'type' => 'string',
    'description' => __('Allow items to have more than one value for this metadatum', 'tainacan'),
    'on_error' => __('Invalid multiple metadata', 'tainacan'),
    'validation' => v::stringType()->in(['yes', 'no']),
    // yes or no. It cant be multiple if its collection_key
    'enum' => ['yes', 'no'],
    'default' => 'no',
], 'cardinality' => ['map' => 'meta', 'title' => __('Maximum number of values', 'tainacan'), 'type' => ['string', 'number'], 'description' => __('Limit the amount of possible metadata values', 'tainacan'), 'on_error' => __('This number of multiple metadata is not allowed', 'tainacan')], 'default_value' => ['map' => 'meta', 'title' => __('Default value', 'tainacan'), 'type' => 'string', 'description' => __('The default value for the metadata', 'tainacan')], 'metadata_type_options' => [
    // not showed in form
    'map' => 'meta',
    'title' => __('Metadata type options', 'tainacan'),
    'type' => ['array', 'object'],
    'items' => ['type' => ['array', 'string', 'integer', 'object']],
    'description' => __('Specific options for metadata type', 'tainacan'),
], 'collection_id' => [
    // not showed in form
    'map' => 'meta',
    'title' => __('Collection', 'tainacan'),
    'type' => ['integer', 'string'],
    'description' => __('The collection ID', 'tainacan'),
], 'accept_suggestion' => ['map' => 'meta', 'title' => __('Metadata Value Accepts Suggestions', 'tainacan'), 'type' => 'boolean', 'description' => __('Allow community to suggest different values for the metadata', 'tainacan'), 'default' => false, 'validation' => v::boolType()], 'exposer_mapping' => [
    'map' => 'meta',
    'title' => __('Relationship metadata mapping', 'tainacan'),
    'type' => ['array', 'object', 'string'],
    'items' => ['type' => ['array', 'string', 'integer', 'object']],
    'description' => __('The metadata mapping options. Metadata can be configured to match another type of data distribution.', 'tainacan'),
    'on_error' => __('Invalid Metadata Mapping', 'tainacan'),
    //'validation' =>  v::arrayType(),
    'default' => [],
], 'display' => ['map' => 'meta', 'title' => __('Display', 'tainacan'), 'type' => 'string', 'validation' => v::stringType()->in(['yes', 'no', 'never']), 'enum' => ['yes', 'no', 'never'], 'description' => __('Display by default on listing or do not display or never display.', 'tainacan'), 'default' => 'no'], 'allow_advanced_search' => ['map' => 'meta', 'title' => __('Allow advanced search', 'tainacan'), 'type' => 'string', 'validation' => v::stringType()->in(['yes', 'no']), 'enum' => ['yes', 'no'], 'description' => __('Allow this metadata to be offered as an option for advanced search', 'tainacan'), 'default' => 'yes'], 'semantic_uri' => ['map' => 'meta', 'title' => __('The semantic metadatum description URI', 'tainacan'), 'type' => 'string', 'validation' => v::optional(v::url()), 'description' => __('The semantic metadatum description URI like: ', 'tainacan') . 'https://schema.org/URL', 'default' => ''], 'repository_level' => [
    'map' => 'meta',
    'title' => __('Repository metadata', 'tainacan'),
    'type' => 'string',
    'description' => __('Makes this metadatum a repository level metadatum instead of collection metadatum', 'tainacan'),
    'on_error' => __('Invalid value for repository metadata', 'tainacan'),
    'validation' => v::stringType()->in(['yes', 'no']),
    'enum' => ['yes', 'no'],
    // yes or no. It cant be multiple if its collection_key
    'default' => 'no',
], 'metadata_section_id' => ['map' => 'meta_multi', 'title' => __('Metadata section', 'tainacan'), 'type' => ['integer', 'string', 'array'], 'description' => __('The metadata section ID', 'tainacan'), 'default' => \Tainacan\Entities\Metadata_Section::$default_section_slug]]` |  | 

Source: [class-tainacan-metadata.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata.php), [line 42](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata.php#L42-L237)

---------------------------------
<br>

## `tainacan-fetch-args` <!-- {docsify-ignore} -->

*fetch metadatum based on ID or WP_Query args*

metadatum are stored as posts. Check WP_Query docs
to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
appropriate WP_Query argument

If a number is passed to $args, it will return a \Tainacan\Entities\Metadatum object.  But if the post is not found or
does not match the entity post type, it will return an empty array


Argument | Type | Description
-------- | ---- | -----------
`$args` | `array` | WP_Query args \|\| int $args the metadatum id
`'metadata'` |  | 

Source: [class-tainacan-metadata.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata.php), [line 331](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata.php#L331-L380)

---------------------------------
<br>

## `tainacan-fetch-all-metadatum-values` <!-- {docsify-ignore} -->

*Return all possible values for a metadatum*

Each metadata is a label with the metadatum name and the value.

If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in the 'metadata' argument, it returns only one metadata, otherwise
it returns all metadata


Argument | Type | Description
-------- | ---- | -----------
`null` |  | 
`$metadatum` |  | 
`$args` | `array\|string` | {<br>    Optional. Array or string of arguments.<br><br>	@type mixed		 $collection_id				The collection ID you want to consider or null for all collections. If a collectoin is set<br>												then only values applied to items in this collection will be returned<br><br>    @type int		 $number					The number of values to return (for pagination). Default empty (unlimited)<br><br>    @type int		 $offset					The offset (for pagination). Default 0<br><br>    @type array\|bool $items_filter				Array in the same format used in @see \Tainacan\Repositories\Items::fetch(). It will filter the results to only return values used in the items inside this criteria. If false, it will return all values, even unused ones. Defatul [] (all items)<br><br>    @type array		 $include					Array if ids to be included in the result. Default [] (nothing)<br><br>    @type array		 $search					String to search. It will only return values that has this string. Default '' (nothing)<br><br>    @type array		 $parent_id					Used by taxonomy metadata. The ID of the parent term to retrieve terms from. Default 0<br><br>    @type bool		 $count_items				Include the count of items that can be found in each value (uses $items_filter as well). Default false<br><br>    @type string   $last_term				The last term returned when using a elasticsearch for calculates the facet.<br><br>}

Source: [class-tainacan-metadata.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata.php), [line 1154](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata.php#L1154-L1245)

---------------------------------
<br>

## `tainacan-terms-hierarchy-html-separator` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`'>'` |  | 

Source: [class-tainacan-metadata.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata.php), [line 1395](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata.php#L1395-L1395)

---------------------------------
<br>

## `tainacan-item-get-author-name` <!-- {docsify-ignore} -->

*Return all possible values for a metadatum*

Each metadata is a label with the metadatum name and the value.

If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in the 'metadata' argument, it returns only one metadata, otherwise
it returns all metadata


Argument | Type | Description
-------- | ---- | -----------
`$name` |  | 

Source: [class-tainacan-metadata.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata.php), [line 1154](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-metadata.php#L1154-L1485)

---------------------------------
<br>

## `tainacan-get-map-{$entity}` <!-- {docsify-ignore} -->

*Repository for managing Tainacan items.*

Handles all database operations for items including creation,
updates, deletion, and querying with proper validation and logging.


Argument | Type | Description
-------- | ---- | -----------
`['title' => ['map' => 'post_title', 'title' => __('Title', 'tainacan'), 'type' => 'string', 'description' => __('Title of the item', 'tainacan'), 'on_error' => __('The title should be a text value and not empty', 'tainacan')], 'status' => ['map' => 'post_status', 'title' => __('Status', 'tainacan'), 'type' => 'string', 'default' => 'draft', 'description' => __('The current situation of the item. Notice that the item visibility also depends on the collection status.', 'tainacan')], 'description' => ['map' => 'post_content', 'title' => __('Description', 'tainacan'), 'type' => 'string', 'description' => __('The item description', 'tainacan'), 'default' => '', 'validation' => ''], 'collection_id' => ['map' => 'meta', 'title' => __('Collection', 'tainacan'), 'type' => 'integer', 'description' => __('The collection ID', 'tainacan'), 'validation' => ''], 'author_id' => ['map' => 'post_author', 'title' => __('Author', 'tainacan'), 'type' => 'string', 'description' => __('The item author\'s user ID (numeric string)', 'tainacan')], 'slug' => ['map' => 'post_name', 'title' => __('Slug', 'tainacan'), 'type' => 'string', 'description' => __('Slug is the editable portion of a page link, located a the end of an URL. Usually presents the title of the item without special characters, separated by hiphen instead of spaces.', 'tainacan')], 'creation_date' => ['map' => 'post_date', 'title' => __('Creation Date', 'tainacan'), 'type' => 'string', 'description' => __('The item creation date', 'tainacan')], 'modification_date' => ['map' => 'post_modified', 'title' => __('Modification Date', 'tainacan'), 'type' => 'string', 'description' => __('The item modification date', 'tainacan')], 'terms' => ['map' => 'terms', 'title' => __('Term IDs', 'tainacan'), 'type' => 'array', 'description' => __('The item term IDs', 'tainacan'), 'items' => ['type' => ['string', 'integer']]], 'document_type' => ['map' => 'meta', 'title' => __('Document Type', 'tainacan'), 'type' => 'string', 'description' => __('The document type, can be a local attachment, an external URL or a text', 'tainacan'), 'on_error' => __('Invalid document type', 'tainacan'), 'enum' => ['attachment', 'url', 'text', 'empty'], 'validation' => v::stringType()->in(['attachment', 'url', 'text', 'empty']), 'default' => 'empty'], 'document' => ['map' => 'meta', 'title' => __('Document', 'tainacan'), 'type' => 'string', 'description' => __('The item main content. May be a file attached, an URL or a text depending on the type of the document.', 'tainacan'), 'on_error' => __('Invalid document', 'tainacan'), 'default' => ''], 'document_options' => ['map' => 'meta', 'title' => __('Document options', 'tainacan'), 'type' => 'object', 'description' => __('Object of options related to the document display.', 'tainacan'), 'on_error' => __('Invalid document options', 'tainacan'), 'properties' => array('forced_iframe' => array('description' => __('Render content in iframe', 'tainacan'), 'type' => 'boolean', 'context' => array('view', 'edit', 'embed'), 'default' => false), 'is_image' => array('title' => __('Is link to external image', 'tainacan'), 'description' => __('Is link to external image', 'tainacan'), 'type' => 'boolean', 'context' => array('view', 'edit', 'embed'), 'default' => false), 'forced_iframe_height' => array('description' => __('Iframe height (px)', 'tainacan'), 'type' => 'number', 'context' => array('view', 'edit', 'embed'), 'default' => 450), 'forced_iframe_width' => array('description' => __('Iframe width (px)', 'tainacan'), 'type' => 'number', 'context' => array('view', 'edit', 'embed'), 'default' => 600))], '_thumbnail_id' => ['map' => 'meta', 'title' => __('Thumbnail', 'tainacan'), 'description' => __('Squared reduced-size version of a picture that helps recognizing and organizing files', 'tainacan'), 'type' => ['integer', 'string']], 'comment_status' => ['map' => 'comment_status', 'title' => __('Comment Status', 'tainacan'), 'type' => 'string', 'description' => __('Item comment status: "open" means comments are allowed, "closed" means comments are not allowed.', 'tainacan'), 'default' => get_default_comment_status(Entities\Collection::get_post_type()), 'enum' => ['open', 'closed'], 'validation' => v::optional(v::stringType()->in(['open', 'closed']))]]` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-items.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-items.php), [line 11](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-items.php#L11-L165)

---------------------------------
<br>

## `tainacan-fetch-args` <!-- {docsify-ignore} -->

*fetch items based on ID or WP_Query args*

Items are stored as posts. Check WP_Query docs
to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
appropriate WP_Query argument

If a number is passed to $args, it will return a \Tainacan\Entities\Item object.  But if the post is not found or
does not match the entity post type, it will return an empty array

The second paramater specifies from which collections item should be fetched.
You can pass the Collection ID or object, or an Array of IDs or collection objects


Argument | Type | Description
-------- | ---- | -----------
`$args` | `array` | WP_Query args \|\| int $args the item id
`'items'` |  | 

Source: [class-tainacan-items.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-items.php), [line 239](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-items.php#L239-L376)

---------------------------------
<br>

## `tainacan-get-thumbnail-id-from-document` <!-- {docsify-ignore} -->

*Hook to get thumbnail from document*


Argument | Type | Description
-------- | ---- | -----------
`null` |  | 
`$item` |  | 

Source: [class-tainacan-items.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-items.php), [line 522](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-items.php#L522-L525)

---------------------------------
<br>

## `tainacan_add_related_item` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$item_arr` |  | 

Source: [class-tainacan-items.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-items.php), [line 674](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-items.php#L674-L674)

---------------------------------
<br>

## `tainacan-get-map-{$entity}` <!-- {docsify-ignore} -->

*{@inheritDoc}*


Argument | Type | Description
-------- | ---- | -----------
`['name' => ['map' => 'post_title', 'title' => __('Name', 'tainacan'), 'type' => 'string', 'description' => __('The title of the collection', 'tainacan'), 'validation' => v::stringType()->notEmpty()], 'status' => ['map' => 'post_status', 'title' => __('Status', 'tainacan'), 'type' => 'string', 'default' => '', 'description' => __('The current situation of the collection. It also affects the visibility of the collection items, as public items from private collections do not appear in the site.', 'tainacan')], 'author_id' => ['map' => 'post_author', 'title' => __('Author ID', 'tainacan'), 'type' => 'string', 'description' => __('The collection author\'s user ID (numeric string)', 'tainacan')], 'creation_date' => ['map' => 'post_date', 'title' => __('Creation Date', 'tainacan'), 'type' => 'string', 'description' => __('The collection creation date', 'tainacan')], 'modification_date' => ['map' => 'post_modified', 'title' => __('Modification Date', 'tainacan'), 'type' => 'string', 'description' => __('The collection modification date', 'tainacan')], 'order' => ['map' => 'order', 'title' => __('Order', 'tainacan'), 'type' => 'string', 'description' => __('Collection order. This metadata is used if collections are manually ordered.', 'tainacan')], 'parent' => ['map' => 'post_parent', 'title' => __('Parent Collection', 'tainacan'), 'type' => 'integer', 'description' => __('Original collection from which features are inherited', 'tainacan')], 'description' => ['map' => 'post_content', 'title' => __('Description', 'tainacan'), 'type' => 'string', 'description' => __('An introductory text describing the collection', 'tainacan'), 'default' => ''], 'slug' => ['map' => 'post_name', 'title' => __('Slug', 'tainacan'), 'type' => 'string', 'description' => __('An unique and sanitized string representation of the collection, used to build the collection URL. It must not contain any special characters or spaces.', 'tainacan')], 'default_orderby' => ['map' => 'meta', 'title' => __('Default order metadata', 'tainacan'), 'type' => ['string', 'array', 'object'], 'description' => __('Default property that items in this collections will be ordered by', 'tainacan'), 'default' => 'date'], 'default_order' => ['map' => 'meta', 'title' => __('Default order', 'tainacan'), 'description' => __('Default order for items in this collection. ASC or DESC', 'tainacan'), 'type' => 'string', 'default' => 'ASC', 'enum' => ['ASC', 'DESC'], 'validation' => v::stringType()->in(['ASC', 'DESC'])], 'default_displayed_metadata' => ['map' => 'meta', 'title' => __('Default Displayed Metadata', 'tainacan'), 'type' => ['array', 'object', 'string'], 'items' => ['type' => ['array', 'string', 'integer', 'object']], 'default' => [], 'description' => __('List of collection properties that will be displayed in the table view', 'tainacan')], 'default_view_mode' => ['map' => 'meta', 'title' => __('Default view mode', 'tainacan'), 'type' => 'string', 'description' => __('Collection default visualization mode', 'tainacan'), 'default' => 'table'], 'enabled_view_modes' => ['map' => 'meta', 'title' => __('Enabled view modes', 'tainacan'), 'type' => 'array', 'description' => __('Which visualization modes will be available for the public to choose from', 'tainacan'), 'default' => ['table', 'cards', 'masonry'], 'items' => ['type' => 'string']], 'metadata_section_order' => ['map' => 'meta', 'title' => __('Metadata order', 'tainacan'), 'type' => 'array', 'items' => ['type' => 'object', 'properties' => ['id' => ['description' => __('Metadata Section ID', 'tainacan'), 'type' => ['integer', 'string']], 'enabled' => ['description' => __('Whether the metadata section is enabled or not.', 'tainacan'), 'type' => 'boolean'], 'metadata_order' => ['type' => 'array', 'description' => __('Array containing the metadata order inside the section', 'tainacan'), 'items' => ['type' => 'object', 'properties' => ['id' => ['description' => __('Metadata ID', 'tainacan'), 'type' => 'integer'], 'enabled' => ['description' => __('Whether the metadata is enabled or not.', 'tainacan'), 'type' => 'boolean']]]]]], 'description' => __('The order of the metadata section in the collection', 'tainacan')], 'metadata_order' => ['map' => 'meta', 'title' => __('Metadata order', 'tainacan'), 'type' => 'array', 'description' => __('The order of the metadata in the collection', 'tainacan'), 'items' => ['type' => 'object', 'properties' => ['id' => ['description' => __('Metadata ID', 'tainacan'), 'type' => 'integer'], 'enabled' => ['description' => __('Whether the metadata is enabled or not.', 'tainacan'), 'type' => 'boolean']]]], 'filters_order' => ['map' => 'meta', 'title' => __('Filters order', 'tainacan'), 'type' => 'array', 'description' => __('The order of the filters in the collection', 'tainacan'), 'items' => ['type' => 'object', 'properties' => ['id' => ['description' => __('Filter ID', 'tainacan'), 'type' => 'integer'], 'enabled' => ['description' => __('Whether the filter is enabled or not.', 'tainacan'), 'type' => 'boolean']]]], 'enable_cover_page' => [
    'map' => 'meta',
    'title' => __('Enable Cover Page', 'tainacan'),
    'type' => 'string',
    'description' => __('To use this page as the home page of this collection', 'tainacan'),
    'on_error' => __('Value should be yes or no', 'tainacan'),
    'enum' => ['yes', 'no'],
    'validation' => v::stringType()->in(['yes', 'no']),
    // yes or no
    'default' => 'no',
], 'cover_page_id' => [
    'map' => 'meta',
    'title' => __('Cover Page ID', 'tainacan'),
    'type' => ['integer', 'string'],
    'description' => __('If enabled, this custom page will be used as cover for this collection, instead of default items list.', 'tainacan'),
    'on_error' => __('Invalid page', 'tainacan'),
    //'validation' => v::numeric(),
    'default' => '',
], 'header_image_id' => [
    'map' => 'meta',
    'title' => __('Header Image', 'tainacan'),
    'type' => 'string',
    'description' => __('The image to be used in collection header, if the theme has one.', 'tainacan'),
    'on_error' => __('Invalid image', 'tainacan'),
    //'validation' => v::numeric(),
    'default' => '',
], '_thumbnail_id' => ['map' => 'meta', 'title' => __('Thumbnail', 'tainacan'), 'description' => __('Squared reduced-size version of a picture that helps recognizing and organizing files', 'tainacan'), 'type' => ['integer', 'string']], 'comment_status' => ['map' => 'comment_status', 'title' => __('Comment Status', 'tainacan'), 'type' => 'string', 'description' => __('Collection comment status: "open" means comments are allowed, "closed" means comments are not allowed.', 'tainacan'), 'default' => get_default_comment_status(Entities\Collection::get_post_type()), 'enum' => ['open', 'closed'], 'validation' => v::optional(v::stringType()->in(['open', 'closed']))], 'allow_comments' => ['map' => 'meta', 'title' => __('Allow enabling comments on items', 'tainacan'), 'type' => 'string', 'description' => __('If this option is enabled, items of this collection can be set to enable a comments section on their page. "open" means comments are allowed, "closed" means comments are not allowed.', 'tainacan'), 'default' => 'closed', 'enum' => ['open', 'closed'], 'validation' => v::optional(v::stringType()->in(['open', 'closed']))], 'allow_item_slug_editing' => ['map' => 'meta', 'title' => __('Allow changing the item slug', 'tainacan'), 'type' => 'string', 'description' => __('If this option is enabled, users will be able to modify the item original slug, resulting in changes to the item permalink.', 'tainacan'), 'default' => 'no', 'enum' => ['yes', 'no'], 'validation' => v::stringType()->in(['yes', 'no'])], 'allow_item_author_editing' => ['map' => 'meta', 'title' => __('Allow changing the item author', 'tainacan'), 'type' => 'string', 'description' => __('If this option is enabled, users will be able to modify the item original authorship, resulting in changes to who can edit it and who is credited as creator of it.', 'tainacan'), 'default' => 'no', 'enum' => ['yes', 'no'], 'validation' => v::stringType()->in(['yes', 'no'])], 'submission_anonymous_user' => ['map' => 'meta', 'title' => __('Allows submission by anonymous user', 'tainacan'), 'type' => 'string', 'description' => __('If enabled, allows submission by anonymous users, whose does not have to be logged in with permissions on the WordPress system.', 'tainacan'), 'default' => 'no', 'on_error' => __('Value should be yes or no', 'tainacan'), 'enum' => ['yes', 'no'], 'validation' => v::stringType()->in(['yes', 'no'])], 'submission_default_status' => ['map' => 'meta', 'title' => __('Default submission item status', 'tainacan'), 'type' => 'string', 'description' => __('The default status of the item that will be created in the collection after submission.', 'tainacan'), 'default' => 'draft'], 'allows_submission' => ['map' => 'meta', 'title' => __('Allows item submission', 'tainacan'), 'type' => 'string', 'description' => __('If enabled, the collection allows item submission, for example via the Item Submission block.', 'tainacan'), 'default' => 'no', 'enum' => ['yes', 'no'], 'on_error' => __('Value should be yes or no', 'tainacan'), 'validation' => v::stringType()->in(['yes', 'no'])], 'hide_items_thumbnail_on_lists' => [
    'map' => 'meta',
    'title' => __('Hide items thumbnail on lists', 'tainacan'),
    'type' => 'string',
    'description' => __('Enable this option to never display the item thumbnail on the items list. This is ment for collections made of mainly textual content.', 'tainacan'),
    'on_error' => __('Value should be yes or no', 'tainacan'),
    'enum' => ['yes', 'no'],
    'validation' => v::stringType()->in(['yes', 'no']),
    // yes or no
    'default' => 'no',
], 'submission_use_recaptcha' => ['map' => 'meta', 'title' => __('Use reCAPTCHA verification on submission form', 'tainacan'), 'type' => 'string', 'description' => __('If enabled, the collection allows item submission using a reCAPTCHA', 'tainacan'), 'default' => 'no', 'on_error' => __('Value should be yes or no', 'tainacan'), 'enum' => ['yes', 'no'], 'validation' => v::stringType()->in(['yes', 'no'])], 'default_metadata_section_properties' => ['map' => 'meta', 'title' => __('Default metadata section properties', 'tainacan'), 'type' => 'object', 'description' => __('The default metadata section properties', 'tainacan'), 'properties' => ['name' => ['type' => 'string', 'description' => __('The name of the default metadata section', 'tainacan')], 'description' => ['type' => 'string', 'description' => __('The description of the default metadata section', 'tainacan')], 'description_bellow_name' => ['type' => 'string', 'description' => __('Whether the description should appear bellow the metadata section.', 'tainacan'), 'enum' => ['yes', 'no']]]], 'item_enabled_document_types' => ['map' => 'meta', 'title' => __('Enabled document types', 'tainacan'), 'type' => 'object', 'description' => __('The document types that are available in the item edition form.', 'tainacan'), 'items' => ['type' => 'object', 'properties' => ['enabled' => ['description' => __('Whether the document type is enabled or not.', 'tainacan'), 'type' => 'string', 'enum' => ['yes', 'no']], 'label' => ['description' => __('The label that will represent the document type.', 'tainacan'), 'type' => 'string'], 'icon' => ['description' => __('The slug of the icon that will represent the document type.', 'tainacan'), 'type' => 'string']]], 'default' => ['attachment' => ['enabled' => 'yes', 'label' => __('File', 'tainacan'), 'icon' => 'attachments'], 'url' => ['enabled' => 'yes', 'label' => __('URL', 'tainacan'), 'icon' => 'url'], 'text' => ['enabled' => 'yes', 'label' => __('Text', 'tainacan'), 'icon' => 'text']]], 'item_publication_label' => ['map' => 'meta', 'title' => __('Publication area label', 'tainacan'), 'type' => 'string', 'description' => __('The label for the publication section in the item edition form, where author, status, slug and comments options appear', 'tainacan'), 'default' => __('Publication data', 'tainacan')], 'item_document_label' => ['map' => 'meta', 'title' => __('Main document label', 'tainacan'), 'type' => 'string', 'description' => __('The label for the main document section in the item edition form', 'tainacan'), 'default' => __('Document', 'tainacan')], 'item_thumbnail_label' => ['map' => 'meta', 'title' => __('Thumbnail label', 'tainacan'), 'type' => 'string', 'description' => __('The label for the thumbnail section in the item edition form', 'tainacan'), 'default' => __('Thumbnail', 'tainacan')], 'item_enable_thumbnail' => ['map' => 'meta', 'title' => __('Item thumbnail', 'tainacan'), 'type' => 'string', 'description' => __('If enabled, each item can have a thumbnail customized instead of the one automatically generated based on the item document.', 'tainacan'), 'default' => 'yes', 'on_error' => __('Value should be yes or no', 'tainacan'), 'enum' => ['yes', 'no'], 'validation' => v::stringType()->in(['yes', 'no'])], 'item_attachment_label' => ['map' => 'meta', 'title' => __('Attachments label', 'tainacan'), 'type' => 'string', 'description' => __('The label for the attachments section in the item edition form', 'tainacan'), 'default' => __('Attachments', 'tainacan')], 'item_enable_attachments' => ['map' => 'meta', 'title' => __('Item attachments', 'tainacan'), 'type' => 'string', 'description' => __('If enabled, each item can have a set of files attached to it, complementary to the item document.', 'tainacan'), 'default' => 'yes', 'on_error' => __('Value should be yes or no', 'tainacan'), 'enum' => ['yes', 'no'], 'validation' => v::stringType()->in(['yes', 'no'])], 'item_enable_metadata_focus_mode' => ['map' => 'meta', 'title' => __('Metadata focus mode', 'tainacan'), 'type' => 'string', 'description' => __('If enabled, a button can start a special navigation mode, that focus one metadatum per time in the item edition form.', 'tainacan'), 'default' => 'yes', 'on_error' => __('Value should be yes or no', 'tainacan'), 'enum' => ['yes', 'no'], 'validation' => v::stringType()->in(['yes', 'no'])], 'item_enable_metadata_required_filter' => ['map' => 'meta', 'title' => __('Metadata required filter', 'tainacan'), 'type' => 'string', 'description' => __('If enabled, a switch can be toggled to display only required metadata in the item edition form.', 'tainacan'), 'default' => 'yes', 'on_error' => __('Value should be yes or no', 'tainacan'), 'enum' => ['yes', 'no'], 'validation' => v::stringType()->in(['yes', 'no'])], 'item_enable_metadata_searchbar' => ['map' => 'meta', 'title' => __('Metadata search bar', 'tainacan'), 'type' => 'string', 'description' => __('If enabled, a search bar can be used for filtering the list of metadata in the item edition form.', 'tainacan'), 'default' => 'yes', 'on_error' => __('Value should be yes or no', 'tainacan'), 'enum' => ['yes', 'no'], 'validation' => v::stringType()->in(['yes', 'no'])], 'item_enable_metadata_collapses' => ['map' => 'meta', 'title' => __('Metadata collapses', 'tainacan'), 'type' => 'string', 'description' => __('If enabled, each metadata in the item form will be wrapped in a collapsable component.', 'tainacan'), 'default' => 'yes', 'on_error' => __('Value should be yes or no', 'tainacan'), 'enum' => ['yes', 'no'], 'validation' => v::stringType()->in(['yes', 'no'])], 'item_enable_metadata_enumeration' => ['map' => 'meta', 'title' => __('Metadata enumeration', 'tainacan'), 'type' => 'string', 'description' => __('If enabled, the metadata sections and their metadata in the item form will be enumerated automatically.', 'tainacan'), 'default' => 'no', 'on_error' => __('Value should be yes or no', 'tainacan'), 'enum' => ['yes', 'no'], 'validation' => v::stringType()->in(['yes', 'no'])]]` |  | 

Source: [class-tainacan-collections.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-collections.php), [line 67](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-collections.php#L67-L536)

---------------------------------
<br>

## `tainacan-fetch-args` <!-- {docsify-ignore} -->

*fetch collection based on ID or WP_Query args*

Collections are stored as posts. Check WP_Query docs
to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
appropriate WP_Query argument

If a number is passed to $args, it will return a \Tainacan\Entities\Collection object.  But if the post is not found or
does not match the entity post type, it will return an empty array


Argument | Type | Description
-------- | ---- | -----------
`$args` | `array` | WP_Query args \|\| int $args the collection id
`'collections'` |  | 

Source: [class-tainacan-collections.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-collections.php), [line 620](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-collections.php#L620-L664)

---------------------------------
<br>

## `tainacan-status-require-validation` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$required_validation_statuses` |  | 

Source: [class-tainacan-repository.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php), [line 165](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-repository.php#L165-L165)

---------------------------------
<br>

## `tainacan-get-map-{$entity}` <!-- {docsify-ignore} -->

*Repository for managing Tainacan filters.*

Handles all database operations for collection filters including creation,
updates, deletion, and querying with proper validation and logging.


Argument | Type | Description
-------- | ---- | -----------
`['name' => ['map' => 'post_title', 'title' => __('Name', 'tainacan'), 'type' => 'string', 'description' => __('Name of the filter', 'tainacan'), 'on_error' => __('The filter name should be a text value and not empty', 'tainacan'), 'validation' => v::stringType()->notEmpty()], 'order' => ['map' => 'menu_order', 'title' => __('Order', 'tainacan'), 'type' => ['string', 'integer'], 'description' => __('Filter order. This metadata is used if filters were manually ordered.', 'tainacan'), 'validation' => ''], 'description' => ['map' => 'post_content', 'title' => __('Description', 'tainacan'), 'type' => 'string', 'description' => __('The filter description', 'tainacan'), 'validation' => '', 'default' => ''], 'filter_type_options' => ['map' => 'meta', 'title' => __('Filter type options', 'tainacan'), 'type' => ['array', 'object', 'string'], 'items' => ['type' => ['array', 'string', 'integer', 'object']], 'description' => __('The filter type options', 'tainacan'), 'validation' => ''], 'filter_type' => ['map' => 'meta', 'title' => __('Type', 'tainacan'), 'type' => 'string', 'description' => __('The filter type class name, such as filter_type: Tainacan\\Filter_Types\\Checkbox', 'tainacan'), 'validation' => ''], 'begin_with_filter_collapsed' => [
    'map' => 'meta',
    'title' => __('Begin with filter collapsed', 'tainacan'),
    'type' => 'string',
    'description' => __('With this option enabled, the filter will appear as a button with an add icon, that should be pressed prior to loading any facet information.', 'tainacan'),
    'on_error' => __('Please set the "Begin with filter collapsed" value as "yes" or "no"', 'tainacan'),
    'validation' => v::stringType()->in(['yes', 'no']),
    // yes or no
    'enum' => ['yes', 'no'],
    'default' => 'no',
], 'display_in_repository_level_lists' => [
    'map' => 'meta',
    'title' => __('Display in repository level lists', 'tainacan'),
    'type' => 'string',
    'description' => __('With this option enabled, the filter will appear even in repository level items lists, such as the complete items list and the term items list.', 'tainacan'),
    'on_error' => __('Please set the "Display in repository level lists" value as "yes" or "no"', 'tainacan'),
    'validation' => v::stringType()->in(['yes', 'no']),
    // yes or no
    'enum' => ['yes', 'no'],
    'default' => 'no',
], 'collection_id' => ['map' => 'meta', 'title' => __('Collection', 'tainacan'), 'type' => ['integer', 'string'], 'description' => __('The collection ID', 'tainacan'), 'validation' => ''], 'metadatum_id' => ['map' => 'meta', 'title' => __('Metadata', 'tainacan'), 'type' => 'integer', 'description' => __('Filter metadata', 'tainacan'), 'validation' => ''], 'max_options' => ['map' => 'meta', 'title' => __('Max of options', 'tainacan'), 'type' => ['integer', 'string'], 'description' => __('The maximum number of options to be loaded by default on the filter.', 'tainacan'), 'validation' => '', 'default' => 4], 'placeholder' => ['map' => 'meta', 'title' => __('Placeholder', 'tainacan'), 'type' => 'string', 'description' => __('The filter input placeholder. This is a simple message that will appear inside textual input and may indicate to the user what kind of information is expected.', 'tainacan'), 'default' => ''], 'description_bellow_name' => [
    'map' => 'meta',
    'title' => __('Description below name', 'tainacan'),
    'type' => 'string',
    'description' => __('Whether the filter description should be displayed below the input label instead of inside a tooltip.', 'tainacan'),
    'on_error' => __('Please set the "Description below name" value as "yes" or "no"', 'tainacan'),
    'validation' => v::stringType()->in(['yes', 'no']),
    // yes or no
    'enum' => ['yes', 'no'],
    'default' => 'no',
]]` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-filters.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-filters.php), [line 10](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-filters.php#L10-L129)

---------------------------------
<br>

## `tainacan-fetch-args` <!-- {docsify-ignore} -->

*fetch filter based on ID or WP_Query args*

Filters are stored as posts. Check WP_Query docs
to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
appropriate WP_Query argument

If a number is passed to $args, it will return a \Tainacan\Entities\Filter object.  But if the post is not found or
does not match the entity post type, it will return an empty array


Argument | Type | Description
-------- | ---- | -----------
`$args` | `array` | WP_Query args \|\| int $args the filter id
`'filters'` |  | 

Source: [class-tainacan-filters.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-filters.php), [line 226](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-filters.php#L226-L265)

---------------------------------
<br>

## `tainacan-get-map-{$entity}` <!-- {docsify-ignore} -->

*Repository for managing Tainacan taxonomy terms.*

Handles all database operations for taxonomy terms including creation,
updates, deletion, and querying with proper validation and logging.


Argument | Type | Description
-------- | ---- | -----------
`['term_id' => ['map' => 'term_id', 'title' => __('ID', 'tainacan'), 'type' => 'integer', 'description' => __('Unique identifier', 'tainacan')], 'name' => ['map' => 'name', 'title' => __('Name', 'tainacan'), 'type' => 'string', 'description' => __('Name of the term', 'tainacan'), 'on_error' => __('The name is empty', 'tainacan'), 'validation' => v::stringType()->notEmpty()], 'parent' => ['map' => 'parent', 'title' => __('Parent', 'tainacan'), 'type' => 'integer', 'description' => __('The parent of the term', 'tainacan'), 'default' => 0], 'description' => ['map' => 'description', 'title' => __('Description', 'tainacan'), 'type' => 'string', 'description' => __('The term description', 'tainacan'), 'default' => ''], 'taxonomy' => ['map' => 'taxonomy', 'title' => __('Taxonomy', 'tainacan'), 'type' => 'string', 'description' => __('The term taxonomy', 'tainacan'), 'on_error' => __('The taxonomy is empty', 'tainacan'), 'validation' => v::stringType()->notEmpty()], 'user' => ['map' => 'termmeta', 'title' => __('User', 'tainacan'), 'type' => 'integer', 'description' => __('The term creator', 'tainacan'), 'on_error' => __('The user is empty or invalid', 'tainacan'), 'default' => get_current_user_id()], 'header_image_id' => [
    'map' => 'termmeta',
    'title' => __('Header Image', 'tainacan'),
    'type' => 'string',
    'description' => __('The image to be used in term header', 'tainacan'),
    'on_error' => __('Invalid image', 'tainacan'),
    //'validation' => v::numeric(),
    'default' => '',
], 'hide_empty' => ['map' => 'hide_empty', 'title' => __('Hide empty', 'tainacan'), 'type' => 'boolean', 'description' => __('Hide empty terms', 'tainacan')]]` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-terms.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-terms.php), [line 11](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-terms.php#L11-L93)

---------------------------------
<br>

## `tainacan-get-map-{$entity}` <!-- {docsify-ignore} -->

*Repository for managing Tainacan taxonomies.*

Handles all database operations for taxonomies including creation,
updates, deletion, and querying with proper validation and logging.


Argument | Type | Description
-------- | ---- | -----------
`['name' => ['map' => 'post_title', 'title' => __('Name', 'tainacan'), 'type' => 'string', 'description' => __('Name of the taxonomy', 'tainacan'), 'on_error' => __('The taxonomy name should be a text value and should not be empty.', 'tainacan'), 'validation' => v::stringType()->notEmpty()], 'description' => ['map' => 'post_content', 'title' => __('Description', 'tainacan'), 'type' => 'string', 'description' => __('The taxonomy description', 'tainacan'), 'default' => '', 'validation' => ''], 'slug' => ['map' => 'post_name', 'title' => __('Slug', 'tainacan'), 'type' => 'string', 'description' => __('The taxonomy slug', 'tainacan'), 'validation' => ''], 'allow_insert' => [
    'map' => 'meta',
    'title' => __('Allow insert', 'tainacan'),
    'type' => 'string',
    'description' => __('Allow/Deny the creation of new terms in the taxonomy', 'tainacan'),
    'on_error' => __('Invalid insertion, allowed values are ( yes/no )', 'tainacan'),
    'validation' => v::stringType()->in(['yes', 'no']),
    // yes or no
    'enum' => ['yes', 'no'],
    'default' => 'yes',
], 'hierarchical' => [
    'map' => 'meta',
    'title' => __('Allow terms hierarchy', 'tainacan'),
    'type' => 'string',
    'description' => __('Allow/Deny the existence of terms children to build a hierarchy', 'tainacan'),
    'on_error' => __('Invalid insertion, allowed values are ( yes/no )', 'tainacan'),
    'validation' => v::stringType()->in(['yes', 'no']),
    // yes or no
    'enum' => ['yes', 'no'],
    'default' => 'yes',
], 'enabled_post_types' => ['map' => 'meta_multi', 'title' => __('Enabled for post types', 'tainacan'), 'type' => ['array', 'string'], 'description' => __('Also enable this taxonomy for other WordPress post types', 'tainacan'), 'on_error' => __('Error enabling this taxonomy for post types', 'tainacan'), 'validation' => '', 'default' => []], 'collections_ids' => ['map' => 'meta_multi', 'title' => __('Collections', 'tainacan'), 'type' => 'string', 'description' => __('The IDs of collection where the taxonomy is used', 'tainacan'), 'validation' => '']]` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-taxonomies.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-taxonomies.php), [line 11](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-taxonomies.php#L11-L91)

---------------------------------
<br>

## `tainacan-fetch-args` <!-- {docsify-ignore} -->

*fetch taxonomies based on ID or WP_Query args*

Taxonomies are stored as posts. Check WP_Query docs
to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
appropriate WP_Query argument

If a number is passed to $args, it will return a \Tainacan\Entities\Taxonomy object.  But if the post is not found or
does not match the entity post type, it will return an empty array


Argument | Type | Description
-------- | ---- | -----------
`$args` | `array` | WP_Query args \| int $args the taxonomy id
`'taxonomies'` |  | 

Source: [class-tainacan-taxonomies.php](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-taxonomies.php), [line 162](https://github.com/tainacan/tainacan/blob/master/src/classes/repositories/class-tainacan-taxonomies.php#L162-L203)

---------------------------------
<br>

## `tainacan-terms-hierarchy-html-separator` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`'>'` |  | 

Source: [class-tainacan-elastic-press-lte-4.7.2.php](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-elastic-press-lte-4.7.2.php), [line 756](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-elastic-press-lte-4.7.2.php#L756-L756)

---------------------------------
<br>

## `tainacan-terms-hierarchy-html-separator` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`'>'` |  | 

Source: [class-tainacan-elastic-press-lte-4.7.2.php](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-elastic-press-lte-4.7.2.php), [line 837](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-elastic-press-lte-4.7.2.php#L837-L837)

---------------------------------
<br>

## `tainacan-get-the-document` <!-- {docsify-ignore} -->

*To be used inside The Loop*

Return the item document as a HTML string to be used as output.


Argument | Type | Description
-------- | ---- | -----------
`$item->get_document_as_html($item_id, $img_size)` |  | 
`$item` |  | 

Source: [template-tags.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php), [line 75](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php#L75-L91)

---------------------------------
<br>

## `tainacan_get_the_document_raw` <!-- {docsify-ignore} -->

*To be used inside The Loop*

Return the item document in raw form (ID if an Attachment, textual content if URL or Text)


Argument | Type | Description
-------- | ---- | -----------
`$item->get_document($item_id)` |  | 
`$item` |  | 

Source: [template-tags.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php), [line 94](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php#L94-L109)

---------------------------------
<br>

## `tainacan_get_the_item_document_url` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$item->get_document_download_url()` |  | 
`$item` |  | 

Source: [template-tags.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php), [line 118](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php#L118-L118)

---------------------------------
<br>

## `tainacan_get_the_document_type` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$item->get_document_type()` |  | 
`$item` |  | 

Source: [template-tags.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php), [line 127](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php#L127-L127)

---------------------------------
<br>

## `tainacan-get-collection-name` <!-- {docsify-ignore} -->

*When visiting a collection archive or single, returns the collection name*


Argument | Type | Description
-------- | ---- | -----------
`esc_html($name)` |  | 
`$collection` |  | 

Source: [template-tags.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php), [line 216](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php#L216-L227)

---------------------------------
<br>

## `tainacan-get-collection-description` <!-- {docsify-ignore} -->

*When visiting a collection archive or single, returns the collection description with clickable links*


Argument | Type | Description
-------- | ---- | -----------
`wp_kses_post($description)` |  | 
`$collection` |  | 

Source: [template-tags.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php), [line 252](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php#L252-L267)

---------------------------------
<br>

## `tainacan-get-collection-url` <!-- {docsify-ignore} -->

*When visiting a collection archive or single, returns the collection url link*


Argument | Type | Description
-------- | ---- | -----------
`esc_url($url)` |  | 
`$collection` |  | 

Source: [template-tags.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php), [line 634](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php#L634-L646)

---------------------------------
<br>

## `tainacan-get-term-name` <!-- {docsify-ignore} -->

*When visiting a taxonomy archive, returns the term name*


Argument | Type | Description
-------- | ---- | -----------
`esc_html($name)` |  | 
`$term` |  | 

Source: [template-tags.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php), [line 765](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php#L765-L776)

---------------------------------
<br>

## `tainacan-get-term-description` <!-- {docsify-ignore} -->

*When visiting a taxonomy archive, returns the term description*


Argument | Type | Description
-------- | ---- | -----------
`wp_kses_post($description)` |  | 
`$term` |  | 

Source: [template-tags.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php), [line 788](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php#L788-L804)

---------------------------------
<br>

## `tainacan-get-the-attachments` <!-- {docsify-ignore} -->

*To be used inside The Loop*

Return the list of attachments of the current item (by default, excluding the document and the thumbnail)


Argument | Type | Description
-------- | ---- | -----------
`$item->get_attachments($exclude)` |  | 
`$item` |  | 

Source: [template-tags.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php), [line 819](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php#L819-L834)

---------------------------------
<br>

## `tainacan-get-initials` <!-- {docsify-ignore} -->

*Gets the initials from a name.*

By default, returns 2 uppercase letters representing the name. The first letter from the first name and the first letter from the last.


Argument | Type | Description
-------- | ---- | -----------
`$result` |  | 
`$string` | `string` | The name to extract the initials from
`$one` | `bool` | whether to return only the first letter, instead of two

Source: [template-tags.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php), [line 924](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php#L924-L961)

---------------------------------
<br>

## `tainacan-get-the-mime-type-icon` <!-- {docsify-ignore} -->

*Filter the image source for the empty thumbnail placeholder.*


Argument | Type | Description
-------- | ---- | -----------
`$images_path . $icon_file . $image_size . '.png'` |  | 
`$mime_type` |  | 
`$image_size` |  | 

Source: [template-tags.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php), [line 1046](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php#L1046-L1054)

---------------------------------
<br>

## `tainacan_single_taxonomy_terms_query` <!-- {docsify-ignore} -->

*Render the taxonomy single template HTML string.*

This works as an archive of the taxonomy terms, and uses the CPT tainacan-taxonomy.

It should display the list of terms, and it is used in the the_content filter of the theme helper to override the cpt single.


Argument | Type | Description
-------- | ---- | -----------
`$terms_query_args` |  | 
`$post` | `object` | The original tainacan-taxonomy post object. It contains the $post->ID, which can be used to query the taxonomy of slug tnc_tax_<$post-id>

Source: [template-tags.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php), [line 1268](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php#L1268-L1416)

---------------------------------
<br>

## `tainacan-terms-hierarchy-html-separator` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`'>'` |  | 

Source: [template-tags.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php), [line 1428](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php#L1428-L1428)

---------------------------------
<br>

## `tainacan_get_single_taxonomy_content` <!-- {docsify-ignore} -->

*Render the taxonomy single template HTML string.*

This works as an archive of the taxonomy terms, and uses the CPT tainacan-taxonomy.

It should display the list of terms, and it is used in the the_content filter of the theme helper to override the cpt single.


Argument | Type | Description
-------- | ---- | -----------
`['content' => $content, 'total_terms' => $total_terms]` |  | 
`$post` | `object` | The original tainacan-taxonomy post object. It contains the $post->ID, which can be used to query the taxonomy of slug tnc_tax_<$post-id>

Source: [template-tags.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php), [line 1268](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php#L1268-L1581)

---------------------------------
<br>

## `tainacan_get_taxonomies_orderby` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$html` |  | 

Source: [template-tags.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php), [line 1645](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php#L1645-L1645)

---------------------------------
<br>

## `tainacan_get_taxonomies_search` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$html` |  | 

Source: [template-tags.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php), [line 1704](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php#L1704-L1704)

---------------------------------
<br>

## `tainacan_get_taxonomies_pagination` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$html` |  | 

Source: [template-tags.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php), [line 1739](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/template-tags.php#L1739-L1739)

---------------------------------
<br>

## `tainacan_single_item_content` <!-- {docsify-ignore} -->

*A basic content for a Tainacan Single page content.*

This may be used as an example for theme developers who
will implement their own tainacan/single-items.php template


Argument | Type | Description
-------- | ---- | -----------
`$content` |  | 
`$item` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 755](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L755-L787)

---------------------------------
<br>

## `tainacan_repository_archive_template_hierarchy` <!-- {docsify-ignore} -->

*Theme helper class for Tainacan.*

Provides theme integration functionality including template overrides,
view modes, template tags, and theme compatibility features.


Argument | Type | Description
-------- | ---- | -----------
`['tainacan/archive-repository.php', 'index.php']` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L9-L975)

---------------------------------
<br>

## `tainacan-default-view-mode-for-themes` <!-- {docsify-ignore} -->

*Get the default view mode which can be tweaked in the settings page.*


Argument | Type | Description
-------- | ---- | -----------
`get_option('tainacan_option_default_view_mode', 'masonry')` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 1083](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L1083-L1089)

---------------------------------
<br>

## `tainacan-enabled-view-modes-for-themes` <!-- {docsify-ignore} -->

*Get the enabled view modes which can be tweaked in the settings page.*


Argument | Type | Description
-------- | ---- | -----------
`$enabled_view_modes` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 1092](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L1092-L1110)

---------------------------------
<br>

## `tainacan-default-order-for-themes` <!-- {docsify-ignore} -->

*Get the default order for items lists, which can be tweaked in the settings page.*


Argument | Type | Description
-------- | ---- | -----------
`get_option('tainacan_option_default_order', 'DESC')` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 1113](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L1113-L1119)

---------------------------------
<br>

## `tainacan-default-orderby-for-themes` <!-- {docsify-ignore} -->

*Get the default order by for items lists, which can be tweaked in the settings page.*


Argument | Type | Description
-------- | ---- | -----------
`get_option('tainacan_option_default_orderby', 'date')` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 1122](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L1122-L1128)

---------------------------------
<br>

## `tainacan-enable-item-link-query-params-for-themes` <!-- {docsify-ignore} -->

*Get whether query parameters should be included in item links from faceted search.*

This can be tweaked in the settings page.


Argument | Type | Description
-------- | ---- | -----------
`get_option('tainacan_option_enable_item_link_query_params', true)` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 1131](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L1131-L1138)

---------------------------------
<br>

## `tainacan-swiper-main-options` <!-- {docsify-ignore} -->

*Returns an item gallery, containing document,
attachments and other information in a slider, carousel and lightbox*


Argument | Type | Description
-------- | ---- | -----------
`$extra_swiper_main_options` |  | 
`$item` |  | 
`$args` | `array` | {<br>    Optional. Array of arguments.<br>     @type string  $item_id						  The Item ID<br>	   @type string	 $blockId 						  A unique identifier for the gallery, will be generated automatically if not provided,<br>   @type bool    $isBlock						  An identifier if we're comming from a block renderer, to avois using functions not available outside of the gutenberg scope;<br>	   @type array 	 $layoutElements 				  Array of elements present in the gallery. Possible values are 'main' and 'carousel'<br>	   @type array 	 $mediaSources 					  Array of sources for the gallery. Possible values are 'document' and 'attachments'<br>	   @type bool 	 $hideFileNameMain 				  Hides the Main slider file name<br>	   @type bool 	 $hideFileCaptionMain 			  Hides the Main slider file caption<br>	   @type bool 	 $hideFileDescriptionMain		  Hides the Main slider file description<br>	   @type bool 	 $hideFileNameThumbnails 		  Hides the Thumbnails carousel file name<br>	   @type bool 	 $hideFileCaptionThumbnails 	  Hides the Thumbnails carousel file caption<br>	   @type bool 	 $hideFileDescriptionThumbnails   Hides the Thumbnails carousel file description<br>	   @type bool 	 $hideFileNameLightbox 			  Hides the Lightbox file name<br>	   @type bool 	 $hideFileCaptionLightbox 		  Hides the Lightbox file caption<br>	   @type bool 	 $hideFileDescriptionLightbox	  Hides the Lightbox file description<br>	   @type bool 	 $openLightboxOnClick 			  Enables the behaviour of opening a lightbox with zoom when clicking on the media item<br>   @type bool	 $showDownloadButtonMain		  Displays a download button below the Main slider<br>   @type bool	 $lightboxHasLightBackground      Show a light background instead of dark in the lightbox<br>   @type bool    $showArrowsAsSVG				  Decides if the swiper carousel arrows will be an SVG icon or font icon<br>   @type string  $thumbnailsSize				  Media size for the thumbnail images. Defaults to 'tainacan-medium'<br>   @type bool  	 $thumbsHaveFixedHeight			  If thumbs should have a fixed height and auto widht. Defaults to false.<br>}

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 1878](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L1878-L2174)

---------------------------------
<br>

## `tainacan-swiper-thumbs-options` <!-- {docsify-ignore} -->

*Returns an item gallery, containing document,
attachments and other information in a slider, carousel and lightbox*


Argument | Type | Description
-------- | ---- | -----------
`$extra_swiper_thumbs_options` |  | 
`$item` |  | 
`$args` | `array` | {<br>    Optional. Array of arguments.<br>     @type string  $item_id						  The Item ID<br>	   @type string	 $blockId 						  A unique identifier for the gallery, will be generated automatically if not provided,<br>   @type bool    $isBlock						  An identifier if we're comming from a block renderer, to avois using functions not available outside of the gutenberg scope;<br>	   @type array 	 $layoutElements 				  Array of elements present in the gallery. Possible values are 'main' and 'carousel'<br>	   @type array 	 $mediaSources 					  Array of sources for the gallery. Possible values are 'document' and 'attachments'<br>	   @type bool 	 $hideFileNameMain 				  Hides the Main slider file name<br>	   @type bool 	 $hideFileCaptionMain 			  Hides the Main slider file caption<br>	   @type bool 	 $hideFileDescriptionMain		  Hides the Main slider file description<br>	   @type bool 	 $hideFileNameThumbnails 		  Hides the Thumbnails carousel file name<br>	   @type bool 	 $hideFileCaptionThumbnails 	  Hides the Thumbnails carousel file caption<br>	   @type bool 	 $hideFileDescriptionThumbnails   Hides the Thumbnails carousel file description<br>	   @type bool 	 $hideFileNameLightbox 			  Hides the Lightbox file name<br>	   @type bool 	 $hideFileCaptionLightbox 		  Hides the Lightbox file caption<br>	   @type bool 	 $hideFileDescriptionLightbox	  Hides the Lightbox file description<br>	   @type bool 	 $openLightboxOnClick 			  Enables the behaviour of opening a lightbox with zoom when clicking on the media item<br>   @type bool	 $showDownloadButtonMain		  Displays a download button below the Main slider<br>   @type bool	 $lightboxHasLightBackground      Show a light background instead of dark in the lightbox<br>   @type bool    $showArrowsAsSVG				  Decides if the swiper carousel arrows will be an SVG icon or font icon<br>   @type string  $thumbnailsSize				  Media size for the thumbnail images. Defaults to 'tainacan-medium'<br>   @type bool  	 $thumbsHaveFixedHeight			  If thumbs should have a fixed height and auto widht. Defaults to false.<br>}

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 1878](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L1878-L2195)

---------------------------------
<br>

## `get_tainacan_item_gallery` <!-- {docsify-ignore} -->

*Filters the Media Component HTML*


Argument | Type | Description
-------- | ---- | -----------
`tainacan_get_the_media_component('tainacan-item-gallery-block_id-' . $block_id, $layout_elements['thumbnails'] ? $media_items_thumbnails : null, $layout_elements['main'] ? $media_items_main : null, array('wrapper_attributes' => $wrapper_attributes, 'class_main_div' => '', 'class_thumbs_div' => '', 'class_thumbs_li' => $thumbs_have_fixed_height ? 'has-fixed-height' : '', 'swiper_main_options' => $swiper_main_options, 'swiper_thumbs_options' => $swiper_thumbs_options, 'swiper_arrows_as_svg' => $show_arrows_as_svg, 'disable_lightbox' => !$open_lightbox_on_click, 'hide_media_name' => $hide_file_name_lightbox, 'hide_media_caption' => $hide_file_caption_lightbox, 'hide_media_description' => $hide_file_description_lightbox, 'lightbox_has_light_background' => $lightbox_has_light_background))` |  | 
`$args` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 2209](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L2209-L2234)

---------------------------------
<br>

## `tainacan-swiper-main-options` <!-- {docsify-ignore} -->

*Returns an items gallery, displaying a list of items in a slider, carousel and lightbox*


Argument | Type | Description
-------- | ---- | -----------
`$extra_swiper_main_options` |  | 
`$items_ids` |  | 
`$args` | `array` | {<br>    Optional. Array of arguments.<br>  @type string   $collectionId					  The Collection ID<br>  @type string   $searchURL						  A query string to fetch items from, if load strategy is 'search'<br>  @type array    $searchParams					  An array of query params to fetch items from, if load strategy is 'search'<br>  @type array    $selectedItems					  An array of item IDs to fetch items from, if load strategy is 'selection' and an array of items, if the load strategy is 'parent'<br>  @type string   $loadStrategy					  Either 'search' or 'selection', to determine how items will be fetch<br>  @type integer  $maxItemsNumber				  Maximum number of items to be fetch<br>  @type string	 $blockId 						  A unique identifier for the gallery, will be generated automatically if not provided,<br>  @type bool     $isBlock						  An identifier if we're comming from a block renderer, to avois using functions not available outside of the gutenberg scope;<br>	  @type array 	 $layoutElements 				  Array of elements present in the gallery. Possible values are 'main' and 'carousel'<br>	  @type bool 	 $hideItemTitleMain 			  Hides the Main slider item title<br>	  @type bool 	 $hideItemLinkMain 			  	  Hides the Main slider item link<br>	  @type bool 	 $hideItemDescriptionMain		  Hides the Main slider item description<br>	  @type bool 	 $hideItemTitleThumbnails 		  Hides the Thumbnails carousel item title<br>	  @type bool 	 $hideItemTitleLightbox 		  Hides the Lightbox item title<br>	  @type bool 	 $hideItemLinkLightbox 		  	  Hides the Lightbox item link<br>	  @type bool 	 $hideItemDescriptionLightbox	  Hides the Lightbox file description<br>	  @type bool 	 $openLightboxOnClick 			  Enables the behaviour of opening a lightbox with zoom when clicking on the media item<br>  @type bool	 $lightboxHasLightBackground      Show a light background instead of dark in the lightbox<br>  @type bool     $showArrowsAsSVG				  Decides if the swiper carousel arrows will be an SVG icon or font icon<br>  @type string   $thumbnailsSize				  Media size for the thumbnail images. Defaults to 'tainacan-medium'<br>  @type bool  	 $thumbsHaveFixedHeight			  If thumbs should have a fixed height and auto widht. Defaults to false.<br>}

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 2237](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L2237-L2490)

---------------------------------
<br>

## `tainacan-swiper-thumbs-options` <!-- {docsify-ignore} -->

*Returns an items gallery, displaying a list of items in a slider, carousel and lightbox*


Argument | Type | Description
-------- | ---- | -----------
`$extra_swiper_thumbs_options` |  | 
`$items_ids` |  | 
`$args` | `array` | {<br>    Optional. Array of arguments.<br>  @type string   $collectionId					  The Collection ID<br>  @type string   $searchURL						  A query string to fetch items from, if load strategy is 'search'<br>  @type array    $searchParams					  An array of query params to fetch items from, if load strategy is 'search'<br>  @type array    $selectedItems					  An array of item IDs to fetch items from, if load strategy is 'selection' and an array of items, if the load strategy is 'parent'<br>  @type string   $loadStrategy					  Either 'search' or 'selection', to determine how items will be fetch<br>  @type integer  $maxItemsNumber				  Maximum number of items to be fetch<br>  @type string	 $blockId 						  A unique identifier for the gallery, will be generated automatically if not provided,<br>  @type bool     $isBlock						  An identifier if we're comming from a block renderer, to avois using functions not available outside of the gutenberg scope;<br>	  @type array 	 $layoutElements 				  Array of elements present in the gallery. Possible values are 'main' and 'carousel'<br>	  @type bool 	 $hideItemTitleMain 			  Hides the Main slider item title<br>	  @type bool 	 $hideItemLinkMain 			  	  Hides the Main slider item link<br>	  @type bool 	 $hideItemDescriptionMain		  Hides the Main slider item description<br>	  @type bool 	 $hideItemTitleThumbnails 		  Hides the Thumbnails carousel item title<br>	  @type bool 	 $hideItemTitleLightbox 		  Hides the Lightbox item title<br>	  @type bool 	 $hideItemLinkLightbox 		  	  Hides the Lightbox item link<br>	  @type bool 	 $hideItemDescriptionLightbox	  Hides the Lightbox file description<br>	  @type bool 	 $openLightboxOnClick 			  Enables the behaviour of opening a lightbox with zoom when clicking on the media item<br>  @type bool	 $lightboxHasLightBackground      Show a light background instead of dark in the lightbox<br>  @type bool     $showArrowsAsSVG				  Decides if the swiper carousel arrows will be an SVG icon or font icon<br>  @type string   $thumbnailsSize				  Media size for the thumbnail images. Defaults to 'tainacan-medium'<br>  @type bool  	 $thumbsHaveFixedHeight			  If thumbs should have a fixed height and auto widht. Defaults to false.<br>}

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 2237](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L2237-L2511)

---------------------------------
<br>

## `get_tainacan_items_gallery` <!-- {docsify-ignore} -->

*Filters the Media Component HTML*


Argument | Type | Description
-------- | ---- | -----------
`tainacan_get_the_media_component('tainacan-items-gallery-block_id-' . $block_id, $layout_elements['thumbnails'] ? $media_items_thumbnails : null, $layout_elements['main'] ? $media_items_main : null, array('wrapper_attributes' => $wrapper_attributes, 'class_main_div' => '', 'class_thumbs_div' => '', 'class_thumbs_li' => $thumbs_have_fixed_height ? 'has-fixed-height' : '', 'swiper_main_options' => $swiper_main_options, 'swiper_thumbs_options' => $swiper_thumbs_options, 'swiper_arrows_as_svg' => $show_arrows_as_svg, 'disable_lightbox' => !$open_lightbox_on_click, 'hide_media_name' => $hide_item_title_lightbox, 'hide_media_caption' => $hide_item_link_lightbox, 'hide_media_description' => $hide_item_description_lightbox, 'lightbox_has_light_background' => $lightbox_has_light_background))` |  | 
`$args` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 2525](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L2525-L2550)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-before` <!-- {docsify-ignore} -->

*To be used inside Gutenberg editor side preview of template blocks*

Return the metadata with placeholder item metadata values as a HTML string to be used as output.

Each metadata is a label with the metadatum name and the placeholder value.

If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in 'metadata' parameter, it returns only one metadata, otherwise
it returns all metadata


Argument | Type | Description
-------- | ---- | -----------
`$before` |  | 
`$item_metadatum` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 2665](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L2665-L2761)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-before--type-{$metadata_type}` <!-- {docsify-ignore} -->

*To be used inside Gutenberg editor side preview of template blocks*

Return the metadata with placeholder item metadata values as a HTML string to be used as output.

Each metadata is a label with the metadatum name and the placeholder value.

If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in 'metadata' parameter, it returns only one metadata, otherwise
it returns all metadata


Argument | Type | Description
-------- | ---- | -----------
`$before` |  | 
`$item_metadatum` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 2665](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L2665-L2762)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-before--id-{$metadatum_id}` <!-- {docsify-ignore} -->

*To be used inside Gutenberg editor side preview of template blocks*

Return the metadata with placeholder item metadata values as a HTML string to be used as output.

Each metadata is a label with the metadatum name and the placeholder value.

If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in 'metadata' parameter, it returns only one metadata, otherwise
it returns all metadata


Argument | Type | Description
-------- | ---- | -----------
`$before` |  | 
`$item_metadatum` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 2665](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L2665-L2763)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-before-title` <!-- {docsify-ignore} -->

*To be used inside Gutenberg editor side preview of template blocks*

Return the metadata with placeholder item metadata values as a HTML string to be used as output.

Each metadata is a label with the metadatum name and the placeholder value.

If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in 'metadata' parameter, it returns only one metadata, otherwise
it returns all metadata


Argument | Type | Description
-------- | ---- | -----------
`$metadatum_title_before` |  | 
`$item_metadatum` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 2665](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L2665-L2770)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-after-title` <!-- {docsify-ignore} -->

*To be used inside Gutenberg editor side preview of template blocks*

Return the metadata with placeholder item metadata values as a HTML string to be used as output.

Each metadata is a label with the metadatum name and the placeholder value.

If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in 'metadata' parameter, it returns only one metadata, otherwise
it returns all metadata


Argument | Type | Description
-------- | ---- | -----------
`$metadatum_title_after` |  | 
`$item_metadatum` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 2665](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L2665-L2772)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-before-value` <!-- {docsify-ignore} -->

*To be used inside Gutenberg editor side preview of template blocks*

Return the metadata with placeholder item metadata values as a HTML string to be used as output.

Each metadata is a label with the metadatum name and the placeholder value.

If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in 'metadata' parameter, it returns only one metadata, otherwise
it returns all metadata


Argument | Type | Description
-------- | ---- | -----------
`$metadatum_value_before` |  | 
`$item_metadatum` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 2665](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L2665-L2777)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-after-value` <!-- {docsify-ignore} -->

*To be used inside Gutenberg editor side preview of template blocks*

Return the metadata with placeholder item metadata values as a HTML string to be used as output.

Each metadata is a label with the metadatum name and the placeholder value.

If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in 'metadata' parameter, it returns only one metadata, otherwise
it returns all metadata


Argument | Type | Description
-------- | ---- | -----------
`$metadatum_value_after` |  | 
`$item_metadatum` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 2665](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L2665-L2779)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-after--id-{$metadatum_id}` <!-- {docsify-ignore} -->

*To be used inside Gutenberg editor side preview of template blocks*

Return the metadata with placeholder item metadata values as a HTML string to be used as output.

Each metadata is a label with the metadatum name and the placeholder value.

If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in 'metadata' parameter, it returns only one metadata, otherwise
it returns all metadata


Argument | Type | Description
-------- | ---- | -----------
`$after` |  | 
`$item_metadatum` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 2665](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L2665-L2787)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-after--type-{$metadata_type}` <!-- {docsify-ignore} -->

*To be used inside Gutenberg editor side preview of template blocks*

Return the metadata with placeholder item metadata values as a HTML string to be used as output.

Each metadata is a label with the metadatum name and the placeholder value.

If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in 'metadata' parameter, it returns only one metadata, otherwise
it returns all metadata


Argument | Type | Description
-------- | ---- | -----------
`$after` |  | 
`$item_metadatum` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 2665](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L2665-L2788)

---------------------------------
<br>

## `tainacan-get-item-metadatum-as-html-after` <!-- {docsify-ignore} -->

*To be used inside Gutenberg editor side preview of template blocks*

Return the metadata with placeholder item metadata values as a HTML string to be used as output.

Each metadata is a label with the metadatum name and the placeholder value.

If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in 'metadata' parameter, it returns only one metadata, otherwise
it returns all metadata


Argument | Type | Description
-------- | ---- | -----------
`$after` |  | 
`$item_metadatum` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 2665](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L2665-L2789)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-before` <!-- {docsify-ignore} -->

*Theme helper class for Tainacan.*

Provides theme integration functionality including template overrides,
view modes, template tags, and theme compatibility features.


Argument | Type | Description
-------- | ---- | -----------
`$before` |  | 
`$metadata_section` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L9-L2961)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-before--id-{$section_id}` <!-- {docsify-ignore} -->

*Theme helper class for Tainacan.*

Provides theme integration functionality including template overrides,
view modes, template tags, and theme compatibility features.


Argument | Type | Description
-------- | ---- | -----------
`$before` |  | 
`$metadata_section` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L9-L2962)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-before--index-{$section_index}` <!-- {docsify-ignore} -->

*Theme helper class for Tainacan.*

Provides theme integration functionality including template overrides,
view modes, template tags, and theme compatibility features.


Argument | Type | Description
-------- | ---- | -----------
`$before` |  | 
`$metadata_section` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L9-L2964)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-before-name` <!-- {docsify-ignore} -->

*Theme helper class for Tainacan.*

Provides theme integration functionality including template overrides,
view modes, template tags, and theme compatibility features.


Argument | Type | Description
-------- | ---- | -----------
`$before_name` |  | 
`$metadata_section` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L9-L2979)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-before-name--id-{$section_id}` <!-- {docsify-ignore} -->

*Theme helper class for Tainacan.*

Provides theme integration functionality including template overrides,
view modes, template tags, and theme compatibility features.


Argument | Type | Description
-------- | ---- | -----------
`$before_name` |  | 
`$metadata_section` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L9-L2980)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-before-name--index-{$section_index}` <!-- {docsify-ignore} -->

*Theme helper class for Tainacan.*

Provides theme integration functionality including template overrides,
view modes, template tags, and theme compatibility features.


Argument | Type | Description
-------- | ---- | -----------
`$before_name` |  | 
`$metadata_section` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L9-L2982)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-after-name` <!-- {docsify-ignore} -->

*Theme helper class for Tainacan.*

Provides theme integration functionality including template overrides,
view modes, template tags, and theme compatibility features.


Argument | Type | Description
-------- | ---- | -----------
`$after_name` |  | 
`$metadata_section` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L9-L2989)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-after-name--id-{$section_id}` <!-- {docsify-ignore} -->

*Theme helper class for Tainacan.*

Provides theme integration functionality including template overrides,
view modes, template tags, and theme compatibility features.


Argument | Type | Description
-------- | ---- | -----------
`$after_name` |  | 
`$metadata_section` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L9-L2990)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-after-name--index-{$section_index}` <!-- {docsify-ignore} -->

*Theme helper class for Tainacan.*

Provides theme integration functionality including template overrides,
view modes, template tags, and theme compatibility features.


Argument | Type | Description
-------- | ---- | -----------
`$after_name` |  | 
`$metadata_section` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L9-L2992)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-before-metadata-list` <!-- {docsify-ignore} -->

*Theme helper class for Tainacan.*

Provides theme integration functionality including template overrides,
view modes, template tags, and theme compatibility features.


Argument | Type | Description
-------- | ---- | -----------
`$before_description` |  | 
`$metadata_section` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L9-L3011)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-before-metadata-list--id-{$section_id}` <!-- {docsify-ignore} -->

*Theme helper class for Tainacan.*

Provides theme integration functionality including template overrides,
view modes, template tags, and theme compatibility features.


Argument | Type | Description
-------- | ---- | -----------
`$before_description` |  | 
`$metadata_section` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L9-L3012)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-before-metadata-list--index-{$section_index}` <!-- {docsify-ignore} -->

*Theme helper class for Tainacan.*

Provides theme integration functionality including template overrides,
view modes, template tags, and theme compatibility features.


Argument | Type | Description
-------- | ---- | -----------
`$before_description` |  | 
`$metadata_section` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L9-L3014)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-after-metadata-list` <!-- {docsify-ignore} -->

*Theme helper class for Tainacan.*

Provides theme integration functionality including template overrides,
view modes, template tags, and theme compatibility features.


Argument | Type | Description
-------- | ---- | -----------
`$after_description` |  | 
`$metadata_section` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L9-L3043)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-after-metadata-list--id-{$section_id}` <!-- {docsify-ignore} -->

*Theme helper class for Tainacan.*

Provides theme integration functionality including template overrides,
view modes, template tags, and theme compatibility features.


Argument | Type | Description
-------- | ---- | -----------
`$after_description` |  | 
`$metadata_section` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L9-L3044)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-after-metadata-list--index-{$section_index}` <!-- {docsify-ignore} -->

*Theme helper class for Tainacan.*

Provides theme integration functionality including template overrides,
view modes, template tags, and theme compatibility features.


Argument | Type | Description
-------- | ---- | -----------
`$after_description` |  | 
`$metadata_section` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L9-L3046)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-after--index-{$section_index}` <!-- {docsify-ignore} -->

*Theme helper class for Tainacan.*

Provides theme integration functionality including template overrides,
view modes, template tags, and theme compatibility features.


Argument | Type | Description
-------- | ---- | -----------
`$after` |  | 
`$metadata_section` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L9-L3057)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-after--id-{$section_id}` <!-- {docsify-ignore} -->

*Theme helper class for Tainacan.*

Provides theme integration functionality including template overrides,
view modes, template tags, and theme compatibility features.


Argument | Type | Description
-------- | ---- | -----------
`$after` |  | 
`$metadata_section` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L9-L3059)

---------------------------------
<br>

## `tainacan-get-metadata-section-as-html-after` <!-- {docsify-ignore} -->

*Theme helper class for Tainacan.*

Provides theme integration functionality including template overrides,
view modes, template tags, and theme compatibility features.


Argument | Type | Description
-------- | ---- | -----------
`$after` |  | 
`$metadata_section` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 9](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L9-L3060)

---------------------------------
<br>

## `tainacan-default-taxonomy-terms-order` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`'ASC'` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 3227](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L3227-L3227)

---------------------------------
<br>

## `tainacan-default-taxonomy-terms-orderby` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`'name'` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 3228](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L3228-L3228)

---------------------------------
<br>

## `tainacan-default-taxonomy-terms-perpage` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`12` |  | 

Source: [class-tainacan-theme-helper.php](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php), [line 3230](https://github.com/tainacan/tainacan/blob/master/src/classes/theme-helper/class-tainacan-theme-helper.php#L3230-L3230)

---------------------------------
<br>

## `tainacan-get-mapper-from-request` <!-- {docsify-ignore} -->

*Check if there is a mapper*


Argument | Type | Description
-------- | ---- | -----------
`$return_mapper` |  | 
`$request` | `\WP_REST_Request` | 

Source: [class-tainacan-mappers-handler.php](https://github.com/tainacan/tainacan/blob/master/src/classes/mappers/class-tainacan-mappers-handler.php), [line 139](https://github.com/tainacan/tainacan/blob/master/src/classes/mappers/class-tainacan-mappers-handler.php#L139-L165)

---------------------------------
<br>

## `tainacan-exporser-type-mappers` <!-- {docsify-ignore} -->

*Return list of supported mappers for this type*


Argument | Type | Description
-------- | ---- | -----------
`$this->mappers` |  | 
`$this` |  | 

Source: [class-tainacan-exposer.php](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-exposer.php), [line 83](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-exposer.php#L83-L88)

---------------------------------
<br>

## `tainacan-exposer-txt` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$txt` |  | 

Source: [class-tainacan-txt.php](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-txt.php), [line 25](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-txt.php#L25-L25)

---------------------------------
<br>

## `tainacan-exposer-numeric-item-prefix` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`__('item', 'tainacan') . '-'` |  | 
`get_class($this)` |  | 

Source: [class-tainacan-txt.php](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-txt.php), [line 39](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-txt.php#L39-L39)

---------------------------------
<br>

## `tainacan-exposer-jsonld` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$jsonld` |  | 

Source: [class-tainacan-json-ld.php](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-json-ld.php), [line 47](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-json-ld.php#L47-L47)

---------------------------------
<br>

## `tainacan-exposer-numeric-item-prefix` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`__('item', 'tainacan') . '-'` |  | 
`get_class($this)` |  | 

Source: [class-tainacan-xml.php](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-xml.php), [line 59](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-xml.php#L59-L59)

---------------------------------
<br>

## `tainacan-exposer-html` <!-- {docsify-ignore} -->

*{@inheritDoc}*


Argument | Type | Description
-------- | ---- | -----------
`$html` |  | 

Source: [class-tainacan-html.php](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-html.php), [line 23](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-html.php#L23-L85)

---------------------------------
<br>

## `tainacan-exposer-numeric-item-prefix` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`__('item', 'tainacan') . '-'` |  | 
`get_class($this)` |  | 

Source: [class-tainacan-html.php](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-html.php), [line 102](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-html.php#L102-L102)

---------------------------------
<br>

## `tainacan-exposer-head` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`'<?xml version="1.0" encoding="UTF-8"?>
			<oai_dc:dc 
    			xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" 
    			xmlns:dc="http://purl.org/dc/elements/1.1/" 
    			xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
    			xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ 
    			http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
			</oai_dc:dc>'` |  | 

Source: [class-tainacan-oai-pmh.php](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-oai-pmh.php), [line 25](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-oai-pmh.php#L25-L33)

---------------------------------
<br>

## `tainacan-oai-pmh-namespace` <!-- {docsify-ignore} -->

*{@inheritDoc}*


Argument | Type | Description
-------- | ---- | -----------
`\Tainacan\Exposers\Mappers\Dublin_Core::XML_DC_NAMESPACE` |  | 

Source: [class-tainacan-oai-pmh.php](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-oai-pmh.php), [line 18](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-oai-pmh.php#L18-L34)

---------------------------------
<br>

## `tainacan-oai-pmh-root` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$xml` |  | 

Source: [class-tainacan-oai-pmh.php](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-oai-pmh.php), [line 35](https://github.com/tainacan/tainacan/blob/master/src/classes/exposers/class-tainacan-oai-pmh.php#L35-L35)

---------------------------------
<br>

## `tainacan-api-role-prepare-for-response` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$return` |  | 
`$request` | `\WP_REST_Request` | 

Source: [class-tainacan-rest-roles-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-roles-controller.php), [line 389](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-roles-controller.php#L389-L405)

---------------------------------
<br>

## `tainacan-api-response-metadatum-meta` <!-- {docsify-ignore} -->

*Use this filter to add additional post_meta to the api response
Use the $request object to get the context of the request and other variables
For example, id context is edit, you may want to add your meta or not.*

Also take care to do any permissions verification before exposing the data


Argument | Type | Description
-------- | ---- | -----------
`[]` |  | 
`$request` |  | 

Source: [class-tainacan-rest-metadata-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-metadata-controller.php), [line 349](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-metadata-controller.php#L349-L356)

---------------------------------
<br>

## `tainacan-api-response-item-meta` <!-- {docsify-ignore} -->

*Use this filter to add additional post_meta to the api response
Use the $request object to get the context of the request and other variables
For example, id context is edit, you may want to add your meta or not.*

Also take care to do any permissions verification before exposing the data


Argument | Type | Description
-------- | ---- | -----------
`[]` |  | 
`$request` |  | 

Source: [class-tainacan-rest-items-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php), [line 264](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php#L264-L271)

---------------------------------
<br>

## `tainacan-api-items-prepare-for-response` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$item_arr` |  | 
`$item` | `mixed` | 
`$request` | `\WP_REST_Request` | 

Source: [class-tainacan-rest-items-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php), [line 255](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php#L255-L359)

---------------------------------
<br>

## `tainacan-rest-response` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$response` |  | 
`$request` |  | 

Source: [class-tainacan-rest-items-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php), [line 386](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php#L386-L386)

---------------------------------
<br>

## `the_content` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$post->post_content` |  | 

Source: [class-tainacan-rest-items-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php), [line 435](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php#L435-L435)

---------------------------------
<br>

## `tainacan-rest-response` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$attachments` |  | 
`$request` |  | 

Source: [class-tainacan-rest-items-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php), [line 451](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php#L451-L451)

---------------------------------
<br>

## `tainacan-item-get-author-name` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$name` |  | 

Source: [class-tainacan-rest-items-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php), [line 596](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php#L596-L596)

---------------------------------
<br>

## `{$filter_name}` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`array('filter' => $f, 'metadatum' => $m, 'arg_type' => $arg_type, 'value' => $meta_value, 'label' => $meta_label, 'compare' => isset($meta['compare']) ? $meta['compare'] : '=', 'type' => $meta_type)` |  | 

Source: [class-tainacan-rest-items-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php), [line 460](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php#L460-L620)

---------------------------------
<br>

## `tainacan-api-get-items-alternate` <!-- {docsify-ignore} -->

*allow plugins to hijack the process.*

If it returns a \WP_REST_Response, the method will return it and ignore the rest of the script


Argument | Type | Description
-------- | ---- | -----------
`false` |  | 
`$request` |  | 

Source: [class-tainacan-rest-items-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php), [line 651](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php#L651-L656)

---------------------------------
<br>

## `tainacan-api-items-filters-response` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`[]` |  | 
`$request` | `\WP_REST_Request` | 

Source: [class-tainacan-rest-items-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php), [line 637](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php#L637-L706)

---------------------------------
<br>

## `tainacan-api-items-filters-arguments-response` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$filters_args` |  | 
`$args` |  | 

Source: [class-tainacan-rest-items-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php), [line 637](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php#L637-L707)

---------------------------------
<br>

## `tainacan-api-items-response` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$response` |  | 
`$request` | `\WP_REST_Request` | 

Source: [class-tainacan-rest-items-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php), [line 637](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php#L637-L772)

---------------------------------
<br>

## `tainacan-submission-item-data` <!-- {docsify-ignore} -->

*REST API controller for managing Tainacan items.*

Handles all REST API endpoints for item operations including
creation, updates, deletion, and querying of items within collections.


Argument | Type | Description
-------- | ---- | -----------
`$item` |  | 
`$metadata` |  | 

**Changelog**

Version | Description
------- | -----------
`1.0.0` | 

Source: [class-tainacan-rest-items-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php), [line 11](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-items-controller.php#L11-L1244)

---------------------------------
<br>

## `tainacan-api-response-metadata-section-meta` <!-- {docsify-ignore} -->

*Use this filter to add additional post_meta to the api response
Use the $request object to get the context of the request and other variables
For example, id context is edit, you may want to add your meta or not.*

Also take care to do any permissions verification before exposing the data


Argument | Type | Description
-------- | ---- | -----------
`[]` |  | 
`$request` |  | 

Source: [class-tainacan-rest-metadata-sections-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-metadata-sections-controller.php), [line 198](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-metadata-sections-controller.php#L198-L205)

---------------------------------
<br>

## `tainacan-api-response-metadatum-meta` <!-- {docsify-ignore} -->

*Use this filter to add additional post_meta to the api response
Use the $request object to get the context of the request and other variables
For example, id context is edit, you may want to add your meta or not.*

Also take care to do any permissions verification before exposing the data


Argument | Type | Description
-------- | ---- | -----------
`[]` |  | 
`$request` |  | 

Source: [class-tainacan-rest-metadata-sections-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-metadata-sections-controller.php), [line 251](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-metadata-sections-controller.php#L251-L258)

---------------------------------
<br>

## `tainacan-api-response-term-meta` <!-- {docsify-ignore} -->

*Use this filter to add additional term_meta to the api response
Use the $request object to get the context of the request and other variables
For example, id context is edit, you may want to add your meta or not.*

Also take care to do any permissions verification before exposing the data


Argument | Type | Description
-------- | ---- | -----------
`[]` |  | 
`$request` |  | 

Source: [class-tainacan-rest-terms-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-terms-controller.php), [line 561](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-terms-controller.php#L561-L568)

---------------------------------
<br>

## `tainacan-api-response-filter-meta` <!-- {docsify-ignore} -->

*Use this filter to add additional post_meta to the api response
Use the $request object to get the context of the request and other variables
For example, id context is edit, you may want to add your meta or not.*

Also take care to do any permissions verification before exposing the data


Argument | Type | Description
-------- | ---- | -----------
`[]` |  | 
`$request` |  | 

Source: [class-tainacan-rest-filters-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-filters-controller.php), [line 345](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-filters-controller.php#L345-L352)

---------------------------------
<br>

## `tainacan-api-response-collection-meta` <!-- {docsify-ignore} -->

*Use this filter to add additional post_meta to the api response
Use the $request object to get the context of the request and other variables
For example, id context is edit, you may want to add your meta or not.*

Also take care to do any permissions verification before exposing the data


Argument | Type | Description
-------- | ---- | -----------
`[]` |  | 
`$request` |  | 

Source: [class-tainacan-rest-collections-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-collections-controller.php), [line 422](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-collections-controller.php#L422-L429)

---------------------------------
<br>

## `tainacan-rest-response` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$prepared_item` |  | 
`$request` |  | 

Source: [class-tainacan-rest-item-metadata-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-item-metadata-controller.php), [line 190](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-item-metadata-controller.php#L190-L190)

---------------------------------
<br>

## `tainacan-rest-response` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$prepared_item` |  | 
`$request` |  | 

Source: [class-tainacan-rest-item-metadata-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-item-metadata-controller.php), [line 216](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-item-metadata-controller.php#L216-L216)

---------------------------------
<br>

## `tainacan-api-response-taxonomy-meta` <!-- {docsify-ignore} -->

*Use this filter to add additional post_meta to the api response
Use the $request object to get the context of the request and other variables
For example, id context is edit, you may want to add your meta or not.*

Also take care to do any permissions verification before exposing the data


Argument | Type | Description
-------- | ---- | -----------
`[]` |  | 
`$request` |  | 

Source: [class-tainacan-rest-taxonomies-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-taxonomies-controller.php), [line 194](https://github.com/tainacan/tainacan/blob/master/src/classes/api/endpoints/class-tainacan-rest-taxonomies-controller.php#L194-L201)

---------------------------------
<br>

## `tainacan-api-prepare-items-args` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`$args` |  | 
`$request` | `mixed` | 

Source: [class-tainacan-rest-controller.php](https://github.com/tainacan/tainacan/blob/master/src/classes/api/class-tainacan-rest-controller.php), [line 77](https://github.com/tainacan/tainacan/blob/master/src/classes/api/class-tainacan-rest-controller.php#L77-L172)

---------------------------------
<br>

## `tainacan-terms-hierarchy-html-separator` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`'>'` |  | 

Source: [class-tainacan-elastic-press.php](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-elastic-press.php), [line 982](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-elastic-press.php#L982-L982)

---------------------------------
<br>

## `tainacan-terms-hierarchy-html-separator` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`'>'` |  | 

Source: [class-tainacan-elastic-press.php](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-elastic-press.php), [line 1123](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-elastic-press.php#L1123-L1123)

---------------------------------
<br>

## `tainacan-oai-maxrecords` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`100` |  | 

Source: [class-tainacan-oaipmh-expose.php](https://github.com/tainacan/tainacan/blob/master/src/classes/oaipmh/class-tainacan-oaipmh-expose.php), [line 79](https://github.com/tainacan/tainacan/blob/master/src/classes/oaipmh/class-tainacan-oaipmh-expose.php#L79-L79)

---------------------------------
<br>

## `tainacan-oai-token-valid` <!-- {docsify-ignore} -->


Argument | Type | Description
-------- | ---- | -----------
`24 * 3600` |  | 

Source: [class-tainacan-oaipmh-expose.php](https://github.com/tainacan/tainacan/blob/master/src/classes/oaipmh/class-tainacan-oaipmh-expose.php), [line 82](https://github.com/tainacan/tainacan/blob/master/src/classes/oaipmh/class-tainacan-oaipmh-expose.php#L82-L82)

---------------------------------
<br>

## `tainacan-extract-pdf-cover` <!-- {docsify-ignore} -->

*Extract an image from the first page of a pdf file*


Argument | Type | Description
-------- | ---- | -----------
`null` |  | 
`$filepath` | `string` | The pdf filepath in the server

Source: [class-tainacan-media.php](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-media.php), [line 295](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-media.php#L295-L302)

---------------------------------
<br>

## `tainacan-index-pdf` <!-- {docsify-ignore} -->

*Handles media functionality for Tainacan.*

Provides methods for managing images, attachments, and media-related features
including custom image sizes, attachment pages, and content indexing.


Argument | Type | Description
-------- | ---- | -----------
`null` |  | 
`$file` |  | 
`$item_id` |  | 

**Changelog**

Version | Description
------- | -----------
`0.1.0` | 

Source: [class-tainacan-media.php](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-media.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-media.php#L7-L393)

---------------------------------
<br>

## `tainacan-default-image-blurhash` <!-- {docsify-ignore} -->

*Handles media functionality for Tainacan.*

Provides methods for managing images, attachments, and media-related features
including custom image sizes, attachment pages, and content indexing.


Argument | Type | Description
-------- | ---- | -----------
`"V4P?:h00Rj~qM{of%MRjWBRjD%%MRjayofj[%M-;RjRj"` |  | 

**Changelog**

Version | Description
------- | -----------
`0.1.0` | 

Source: [class-tainacan-media.php](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-media.php), [line 7](https://github.com/tainacan/tainacan/blob/master/src/classes/class-tainacan-media.php#L7-L491)

---------------------------------
<br>


<p align="center"><a href="https://github.com/pronamic/wp-documentor"><img src="https://cdn.jsdelivr.net/gh/pronamic/wp-documentor@main/logos/pronamic-wp-documentor.svgo-min.svg" alt="Pronamic WordPress Documentor" width="32" height="32"></a><br><em>Generated by <a href="https://github.com/pronamic/wp-documentor">Pronamic WordPress Documentor</a> <code>1.2.0</code></em><p>

