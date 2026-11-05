
This is an automatically generated documentation for **Documentation**.

## Namespaces

### \

#### Functions

| Function                                                                                                                   | Description                                                                                                                                                 |
|----------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------|
| [`tainacan_autoload()`](functions/tainacan_autoload.md)                                                                     | Autoloader function for Tainacan classes.                                                                                                                   |
| [`tainacan_collections()`](functions/tainacan_collections.md)                                                               | Retrieve the singleton Collections Repository instance                                                                                                      |
| [`tainacan_filters()`](functions/tainacan_filters.md)                                                                       | Retrieve the singleton Filters Repository instance                                                                                                          |
| [`tainacan_item_metadata()`](functions/tainacan_item_metadata.md)                                                           | Retrieve the singleton Item_Metadata Repository instance                                                                                                    |
| [`tainacan_items()`](functions/tainacan_items.md)                                                                           | Retrieve the singleton Items Repository instance                                                                                                            |
| [`tainacan_logs()`](functions/tainacan_logs.md)                                                                             | Retrieve the singleton Logs Repository instance                                                                                                             |
| [`tainacan_metadata()`](functions/tainacan_metadata.md)                                                                     | Retrieve the singleton Metadata Repository instance                                                                                                         |
| [`tainacan_taxonomies()`](functions/tainacan_taxonomies.md)                                                                 | Retrieve the singleton Taxonomies Repository instance                                                                                                       |
| [`tainacan_terms()`](functions/tainacan_terms.md)                                                                           | Retrieve the singleton Terms Repository instance                                                                                                            |
| [`tainacan_roles()`](functions/tainacan_roles.md)                                                                           | Retrieve the singleton Tainacan Roles instance                                                                                                              |
| [`tainacan_metadata_sections()`](functions/tainacan_metadata_sections.md)                                                   | Retrieve the singleton Metadata Sections Repository instance                                                                                                |
| [`tainacan_maybe_unserialize_array()`](functions/tainacan_maybe_unserialize_array.md)                                       | Decode a stored list/order value into an array without instantiating PHP objects.                                                                           |
| [`tainacan_get_api_postdata()`](functions/tainacan_get_api_postdata.md)                                                     | Retrieves raw data sent to an API endpoint reading the php://input stream                                                                                   |
| [`tainacan_is_post_status_viewable()`](functions/tainacan_is_post_status_viewable.md)                                       |                                                                                                                                                             |
| [`tainacan_enable_dev_wp_interface()`](functions/tainacan_enable_dev_wp_interface.md)                                       | DEV Interface utility, used for debugging.                                                                                                                  |
| [`tainacan_wp_kses()`](functions/tainacan_wp_kses.md)                                                                       | Custom wp_kses function for Tainacan content.                                                                                                               |
| [`tainacan_get_default_allowed_styles()`](functions/tainacan_get_default_allowed_styles.md)                                 | Adds CSS properties to the safe_style_css filter
These properties are needed for the media gallery component (e.g., .media-full-content)                    |
| [`tainacan_get_svg_allowed_html()`](functions/tainacan_get_svg_allowed_html.md)                                             | Returns SVG allowed HTML elements and attributes for wp_kses                                                                                                |
| [`tainacan_load_script_translation_file_for_chunk()`](functions/tainacan_load_script_translation_file_for_chunk.md)         | Filter callback for load_script_translation_file: resolves lazy-loaded chunk handles to the translation JSON.                                               |
| [`tainacan_get_the_metadata()`](functions/tainacan_get_the_metadata.md)                                                     | To be used inside The Loop                                                                                                                                  |
| [`tainacan_the_metadata()`](functions/tainacan_the_metadata.md)                                                             | To be used inside The Loop                                                                                                                                  |
| [`tainacan_get_the_document()`](functions/tainacan_get_the_document.md)                                                     | To be used inside The Loop                                                                                                                                  |
| [`tainacan_get_the_document_raw()`](functions/tainacan_get_the_document_raw.md)                                             | To be used inside The Loop                                                                                                                                  |
| [`tainacan_get_the_item_document_url()`](functions/tainacan_get_the_item_document_url.md)                                   | To be used inside The Loop                                                                                                                                  |
| [`tainacan_get_the_document_type()`](functions/tainacan_get_the_document_type.md)                                           | To be used inside The Loop                                                                                                                                  |
| [`tainacan_get_the_document_mimetype()`](functions/tainacan_get_the_document_mimetype.md)                                   | To be used inside The Loop                                                                                                                                  |
| [`tainacan_get_the_item_document_download_link()`](functions/tainacan_get_the_item_document_download_link.md)               | To be used inside The Loop                                                                                                                                  |
| [`tainacan_the_item_document_download_link()`](functions/tainacan_the_item_document_download_link.md)                       | To be used inside The Loop                                                                                                                                  |
| [`tainacan_get_the_item_attachment_download_link()`](functions/tainacan_get_the_item_attachment_download_link.md)           | Return the item attachment download link as HTML.                                                                                                           |
| [`tainacan_the_item_attachment_download_link()`](functions/tainacan_the_item_attachment_download_link.md)                   | Return the item attachment download link as HTML.                                                                                                           |
| [`tainacan_get_the_media_item_expand_control()`](functions/tainacan_get_the_media_item_expand_control.md)                   | Return the item gallery Expand control as HTML.                                                                                                             |
| [`tainacan_get_the_media_item_actions()`](functions/tainacan_get_the_media_item_actions.md)                                 | Return a slide actions wrapper around Expand, Download, and similar controls.                                                                               |
| [`tainacan_the_document()`](functions/tainacan_the_document.md)                                                             | To be used inside The Loop                                                                                                                                  |
| [`tainacan_get_single_attachment_as_html()`](functions/tainacan_get_single_attachment_as_html.md)                           | To be used inside The Loop                                                                                                                                  |
| [`tainacan_get_attachment_as_html()`](functions/tainacan_get_attachment_as_html.md)                                         | Return HTML display-ready version of an attachment                                                                                                          |
| [`tainacan_has_document()`](functions/tainacan_has_document.md)                                                             | To be used inside The Loop                                                                                                                                  |
| [`tainacan_get_collection_id()`](functions/tainacan_get_collection_id.md)                                                   | When visiting a collection archive or single, returns the current collection id                                                                             |
| [`tainacan_get_collection()`](functions/tainacan_get_collection.md)                                                         | When visiting a collection archive or single, returns the current collection object                                                                         |
| [`tainacan_get_the_collection_name()`](functions/tainacan_get_the_collection_name.md)                                       | When visiting a collection archive or single, returns the collection name                                                                                   |
| [`tainacan_get_adjacent_items()`](functions/tainacan_get_adjacent_items.md)                                                 | When visiting an item single page containing a search query, returns the previous and next items                                                            |
| [`tainacan_the_collection_name()`](functions/tainacan_the_collection_name.md)                                               | When visiting a collection archive or single, prints the collection name                                                                                    |
| [`tainacan_get_the_collection_description()`](functions/tainacan_get_the_collection_description.md)                         | When visiting a collection archive or single, returns the collection description with clickable links                                                       |
| [`tainacan_the_collection_description()`](functions/tainacan_the_collection_description.md)                                 | When visiting a collection archive or single, prints the collection description                                                                             |
| [`tainacan_sanitize_media_thumbs_layout()`](functions/tainacan_sanitize_media_thumbs_layout.md)                             | Sanitize a thumbnails layout slug for the media gallery.                                                                                                    |
| [`tainacan_the_media_component()`](functions/tainacan_the_media_component.md)                                               | Tainacan Gallery component, used to render document, attachments and other files                                                                            |
| [`tainacan_get_the_media_component()`](functions/tainacan_get_the_media_component.md)                                       | Tainacan Media Gallery component, used to render document, attachments and other files                                                                      |
| [`tainacan_get_the_media_component_slide()`](functions/tainacan_get_the_media_component_slide.md)                           | Tainacan Media Item for the Media Gallery component, used to render a single link displayed in the carousel                                                 |
| [`tainacan_get_the_collection_url()`](functions/tainacan_get_the_collection_url.md)                                         | When visiting a collection archive or single, returns the collection url link                                                                               |
| [`tainacan_the_collection_url()`](functions/tainacan_the_collection_url.md)                                                 | When visiting a collection archive or single, prints the collection url link                                                                                |
| [`tainacan_get_the_view_modes()`](functions/tainacan_get_the_view_modes.md)                                                 | Get view modes already filtered by hooks, user preferences and collection settings                                                                          |
| [`tainacan_is_view_mode_enabled()`](functions/tainacan_is_view_mode_enabled.md)                                             | Checks whether a view mode is enabled in the current list instance                                                                                          |
| [`tainacan_the_faceted_search()`](functions/tainacan_the_faceted_search.md)                                                 | Outputs the div used by Vue to render the Items List with a powerful faceted search                                                                         |
| [`tainacan_get_term()`](functions/tainacan_get_term.md)                                                                     | When visiting a term archive, returns the current term object if it belongs to a Tainacan taxonomy                                                          |
| [`tainacan_get_the_term_name()`](functions/tainacan_get_the_term_name.md)                                                   | When visiting a taxonomy archive, returns the term name                                                                                                     |
| [`tainacan_the_term_name()`](functions/tainacan_the_term_name.md)                                                           | When visiting a taxonomy archive, prints the term name                                                                                                      |
| [`tainacan_get_the_term_description()`](functions/tainacan_get_the_term_description.md)                                     | When visiting a taxonomy archive, returns the term description                                                                                              |
| [`tainacan_the_term_description()`](functions/tainacan_the_term_description.md)                                             | When visiting a taxonomy archive, prints the term description                                                                                               |
| [`tainacan_get_the_attachments()`](functions/tainacan_get_the_attachments.md)                                               | To be used inside The Loop                                                                                                                                  |
| [`tainacan_get_attachment_html_url()`](functions/tainacan_get_attachment_html_url.md)                                       | Get the HTML URL for an attachment                                                                                                                          |
| [`tainacan_register_view_mode()`](functions/tainacan_register_view_mode.md)                                                 | Register a view mode for items list                                                                                                                         |
| [`tainacan_get_item()`](functions/tainacan_get_item.md)                                                                     | Gets the Tainacan Item Entity object                                                                                                                        |
| [`tainacan_current_view_displays()`](functions/tainacan_current_view_displays.md)                                           | To be used inside The Loop of a faceted search view mode template.                                                                                          |
| [`tainacan_the_item_edit_link()`](functions/tainacan_the_item_edit_link.md)                                                 | Displays the link to the edit page of an item, if current user have permission                                                                              |
| [`tainacan_get_initials()`](functions/tainacan_get_initials.md)                                                             | Gets the initials from a name.                                                                                                                              |
| [`tainacan_get_the_mime_type_icon()`](functions/tainacan_get_the_mime_type_icon.md)                                         | Gets the icon mime type using our custom plugin thumbnails                                                                                                  |
| [`tainacan_the_items_carousel()`](functions/tainacan_the_items_carousel.md)                                                 | Displays a carousel of items, the same of the gutenberg block                                                                                               |
| [`tainacan_the_terms_carousel()`](functions/tainacan_the_terms_carousel.md)                                                 | Displays a carousel of terms, the same of the Gutenberg block                                                                                               |
| [`tainacan_the_related_items()`](functions/tainacan_the_related_items.md)                                                   | Displays a group of related items lists
For each metatada, the collection name, the metadata name and a button linking
the items list filtered is presented |
| [`tainacan_the_related_items_carousel()`](functions/tainacan_the_related_items_carousel.md)                                 | Displays a group of related items carousels
This is a preset version of tainacan_the_related_items, to keep compatibility with previous versions            |
| [`tainacan_has_related_items()`](functions/tainacan_has_related_items.md)                                                   | Checks if the current item has or not related items                                                                                                         |
| [`tainacan_the_item_gallery()`](functions/tainacan_the_item_gallery.md)                                                     | Renders the content of the item gallery block
using Tainacan template functions that create
a Swiper.js carousel and slider, with a PhotoSwipe.js
lightbox  |
| [`tainacan_get_the_metadata_sections()`](functions/tainacan_get_the_metadata_sections.md)                                   | Render the item metadata sections as a HTML string.                                                                                                         |
| [`tainacan_the_metadata_sections()`](functions/tainacan_the_metadata_sections.md)                                           |                                                                                                                                                             |
| [`tainacan_get_single_taxonomy_content()`](functions/tainacan_get_single_taxonomy_content.md)                               | Render the taxonomy single template HTML string.                                                                                                            |
| [`tainacan_get_taxonomies_orderby()`](functions/tainacan_get_taxonomies_orderby.md)                                         |                                                                                                                                                             |
| [`tainacan_the_taxonomies_orderby()`](functions/tainacan_the_taxonomies_orderby.md)                                         |                                                                                                                                                             |
| [`tainacan_get_taxonomies_search()`](functions/tainacan_get_taxonomies_search.md)                                           |                                                                                                                                                             |
| [`tainacan_the_taxonomies_search()`](functions/tainacan_the_taxonomies_search.md)                                           |                                                                                                                                                             |
| [`tainacan_get_taxonomies_pagination()`](functions/tainacan_get_taxonomies_pagination.md)                                   |                                                                                                                                                             |
| [`tainacan_the_taxonomies_pagination()`](functions/tainacan_the_taxonomies_pagination.md)                                   |                                                                                                                                                             |
| [`tainacan_register_admin_hook()`](functions/tainacan_register_admin_hook.md)                                               |                                                                                                                                                             |
| [`tainacan_blocks_render_geocoordinate_item_metadatum()`](functions/tainacan_blocks_render_geocoordinate_item_metadatum.md) | Renders the content of the item metadata block
using Tainacan template functions                                                                            |
| [`tainacan_blocks_render_item_gallery()`](functions/tainacan_blocks_render_item_gallery.md)                                 | Renders the content of the item gallery block
using Tainacan template functions that create
a Swiper.js carousel and slider, with a PhotoSwipe.js
lightbox  |
| [`tainacan_blocks_render_item_metadata()`](functions/tainacan_blocks_render_item_metadata.md)                               | Renders the content of the item metadata
using Tainacan template functions                                                                                  |
| [`tainacan_blocks_render_metadata_section()`](functions/tainacan_blocks_render_metadata_section.md)                         | Renders the content of the item metadata section
using Tainacan template functions                                                                          |
| [`tainacan_blocks_render_metadata_sections()`](functions/tainacan_blocks_render_metadata_sections.md)                       | Renders the content of the item metadata sections
using Tainacan template functions                                                                         |
| [`tainacan_blocks_render_item_metadatum()`](functions/tainacan_blocks_render_item_metadatum.md)                             | Renders the content of the item metadata block
using Tainacan template functions                                                                            |
| [`tainacan_blocks_render_items_gallery()`](functions/tainacan_blocks_render_items_gallery.md)                               | Renders the content of the items gallery block
using Tainacan template functions that create
a Swiper.js carousel and slider, with a PhotoSwipe.js
lightbox |
| [`tainacan_blocks_render_related_items_list()`](functions/tainacan_blocks_render_related_items_list.md)                     | Renders the content of the related items block                                                                                                              |

### \Tainacan



#### Namespace Diagram

```mermaid
classDiagram
    direction TB
    class Search_Engine { }
    class Cli_Control_Metadata { }
    class Roles_Editor { }
    class Background_Process_Base { }
    class Pages { }
    class Admin_Bar_Items { }
    class Cli_Collection { }
    class Logs_Wp_Posts_Migration { }
    class Cli_Logs { }
    class Private_Files { }
    class Gutenberg_Blocks { }
    class Theme_Helper { }
    class System_Check { }
    class __Bulk_Edit { }
    class Exposers_Handler { }
    class Cli { }
    class Dashboard { }
    class Mappers_Handler { }
    class Cli_Document { }
    class Cli_Garbage_Collector { }
    class Pdf_Extracted_Text_Quality { }
    class Admin { }
    class Embed { }
    class Background_Process_Heartbeat { }
    class Admin_Commands { }
    class Cli_Move_Attachments { }
    class Mobile_App { }
    class Settings { }
    class Background_Process { }
    class Async_Request { }
    class Media { }
    Async_Request <|-- Background_Process_Base
    Background_Process_Base <|-- Background_Process
    Cli_Logs ..> Logs_Wp_Posts_Migration
    Cli_Move_Attachments ..> Private_Files
    Cli_Move_Attachments ..> Theme_Helper
    Exposers_Handler ..> Mappers_Handler
    Gutenberg_Blocks ..> Admin
    Gutenberg_Blocks ..> Theme_Helper
    Media ..> Embed
    Media ..> Pdf_Extracted_Text_Quality
    Pages ..> Exposers_Handler
    Pages ..> Mappers_Handler
    Pages ..> Media
    Pages ..> Settings
    Pages ..> Theme_Helper
    Pages <|-- Admin
    Pages <|-- Dashboard
    Pages <|-- Mobile_App
    Pages <|-- Roles_Editor
    Pages <|-- Settings
    Pages <|-- System_Check
    Private_Files ..> Media
    Private_Files ..> Theme_Helper
    Settings ..> Gutenberg_Blocks
    Settings ..> Media
    System_Check ..> Private_Files
    Theme_Helper ..> Mappers_Handler
    Theme_Helper ..> Media
    Theme_Helper ..> Theme_Helper
```
#### Classes

| Class                                                                             | Description                                                                                               |
|-----------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------|
| [`Async_Request`](classes/Tainacan/Async_Request.md)                               | Abstract base class for asynchronous requests.                                                            |
| [`Background_Process_Base`](classes/Tainacan/Background_Process_Base.md)           | Abstract base class for background processes in Tainacan.                                                 |
| [`Background_Process_Heartbeat`](classes/Tainacan/Background_Process_Heartbeat.md) |                                                                                                           |
| [`Background_Process`](classes/Tainacan/Background_Process.md)                     | Abstract Tainacan\Background_Process class.                                                               |
| [`Background_Exporter`](classes/Tainacan/Background_Exporter.md)                   | Abstract Tainacan\Background_Process class.                                                               |
| [`Exporter_Handler`](classes/Tainacan/Exporter_Handler.md)                         |                                                                                                           |
| [`Background_Generic_Process`](classes/Tainacan/Background_Generic_Process.md)     | Abstract Tainacan\Background_Process class.                                                               |
| [`Generic_Process_Handler`](classes/Tainacan/Generic_Process_Handler.md)           |                                                                                                           |
| [`Background_Importer`](classes/Tainacan/Background_Importer.md)                   | Abstract Tainacan\Background_Process class.                                                               |
| [`Importer_Handler`](classes/Tainacan/Importer_Handler.md)                         |                                                                                                           |
| [`__Bulk_Edit`](classes/Tainacan/__Bulk_Edit.md)                                   | Bulk_Edit class handles bulk item edition                                                                 |
| [`Private_Files`](classes/Tainacan/Private_Files.md)                               | Handles private file management for Tainacan.                                                             |
| [`Roles`](classes/Tainacan/Roles.md)                                               | Manages roles and capabilities for the Tainacan plugin.                                                   |
| [`Search_Engine`](classes/Tainacan/Search_Engine.md)                               | Implements the default Tainacan search engine.                                                            |
| [`Cli_Collection`](classes/Tainacan/Cli_Collection.md)                             | Handles WP-CLI commands for Tainacan collections.                                                         |
| [`Cli_Control_Metadata`](classes/Tainacan/Cli_Control_Metadata.md)                 | Handles WP-CLI commands for Tainacan control metadata operations.                                         |
| [`Cli_Document`](classes/Tainacan/Cli_Document.md)                                 | Handles WP-CLI commands for Tainacan document indexing operations.                                        |
| [`Cli_Garbage_Collector`](classes/Tainacan/Cli_Garbage_Collector.md)               | Handles WP-CLI commands for Tainacan garbage collection operations.                                       |
| [`Logs_Wp_Posts_Migration`](classes/Tainacan/Logs_Wp_Posts_Migration.md)           | Handles migration of legacy Tainacan logs from wp_posts/wp_postmeta
to the dedicated tainacan_logs table. |
| [`Cli_Logs`](classes/Tainacan/Cli_Logs.md)                                         | Manages Tainacan activity log data migration from the legacy wp_posts structure.                          |
| [`Cli_Move_Attachments`](classes/Tainacan/Cli_Move_Attachments.md)                 | Handles WP-CLI commands for Tainacan attachment migration operations.                                     |
| [`Cli`](classes/Tainacan/Cli.md)                                                   | Handles WP-CLI command registration for Tainacan.                                                         |
| [`Exposers_Handler`](classes/Tainacan/Exposers_Handler.md)                         | Load exposers classes                                                                                     |
| [`Mappers_Handler`](classes/Tainacan/Mappers_Handler.md)                           |                                                                                                           |
| [`Embed`](classes/Tainacan/Embed.md)                                               | Handles media embedding functionality for Tainacan.                                                       |
| [`Media`](classes/Tainacan/Media.md)                                               | Handles media functionality for Tainacan.                                                                 |
| [`Pdf_Extracted_Text_Quality`](classes/Tainacan/Pdf_Extracted_Text_Quality.md)     | Basic quality gate for PDF text extracted via Smalot PdfParser.                                           |
| [`Theme_Helper`](classes/Tainacan/Theme_Helper.md)                                 | Theme helper class for Tainacan.                                                                          |
| [`Migrations`](classes/Tainacan/Migrations.md)                                     |                                                                                                           |
| [`Admin`](classes/Tainacan/Admin.md)                                               | Pages is an abstract base class for all Tainacan admin pages.                                             |
| [`Admin_Hooks`](classes/Tainacan/Admin_Hooks.md)                                   |                                                                                                           |
| [`Component_Hooks`](classes/Tainacan/Component_Hooks.md)                           | Class Components_Hooks                                                                                    |
| [`Plugin_Hooks`](classes/Tainacan/Plugin_Hooks.md)                                 | Class Plugins_Hooks                                                                                       |
| [`Admin_Bar_Items`](classes/Tainacan/Admin_Bar_Items.md)                           | Handles WordPress admin bar items for Tainacan.                                                           |
| [`Admin_Commands`](classes/Tainacan/Admin_Commands.md)                             | Handles WordPress Command Palette integration for Tainacan.                                               |
| [`Pages`](classes/Tainacan/Pages.md)                                               | Pages is an abstract base class for all Tainacan admin pages.                                             |
| [`Dashboard`](classes/Tainacan/Dashboard.md)                                       | Pages is an abstract base class for all Tainacan admin pages.                                             |
| [`Gutenberg_Blocks`](classes/Tainacan/Gutenberg_Blocks.md)                         | Handles registration of Tainacan Gutenberg blocks and Query loop variations.                              |
| [`Mobile_App`](classes/Tainacan/Mobile_App.md)                                     | Pages is an abstract base class for all Tainacan admin pages.                                             |
| [`Roles_Editor`](classes/Tainacan/Roles_Editor.md)                                 | Pages is an abstract base class for all Tainacan admin pages.                                             |
| [`Settings`](classes/Tainacan/Settings.md)                                         | Pages is an abstract base class for all Tainacan admin pages.                                             |
| [`System_Check`](classes/Tainacan/System_Check.md)                                 | Pages is an abstract base class for all Tainacan admin pages.                                             |

### \Tainacan\API



#### Namespace Diagram

```mermaid
classDiagram
    direction TB
    class REST_Controller { }
    class endpoints_REST_Sequence_Edit_Controller { }
    class endpoints_REST_Taxonomies_Controller { }
    class endpoints_REST_Filter_Types_Controller { }
    class endpoints_REST_Metadata_Sections_Controller { }
    class endpoints_REST_Metadatum_Mappers_Controller { }
    class endpoints_REST_Items_Controller { }
    class endpoints_REST_Filters_Controller { }
    class endpoints_REST_Roles_Controller { }
    class endpoints_REST_Exporters_Controller { }
    class endpoints_REST_Bulkedit_Controller { }
    class endpoints_REST_Facets_Controller { }
    class endpoints_REST_Oaipmh_Controller { }
    class endpoints_REST_Terms_Controller { }
    class endpoints_REST_Importers_Controller { }
    class endpoints_REST_Exposers_Controller { }
    class endpoints_REST_Metadata_Controller { }
    class endpoints_REST_Logs_Controller { }
    class endpoints_REST_Item_Metadata_Controller { }
    class endpoints_REST_Collections_Controller { }
    class endpoints_REST_Reports_Controller { }
    class endpoints_REST_Background_Processes_Controller { }
    class endpoints_REST_Metadata_Types_Controller { }
    REST_Controller <|-- endpoints_REST_Background_Processes_Controller
    REST_Controller <|-- endpoints_REST_Bulkedit_Controller
    REST_Controller <|-- endpoints_REST_Collections_Controller
    REST_Controller <|-- endpoints_REST_Exporters_Controller
    REST_Controller <|-- endpoints_REST_Exposers_Controller
    REST_Controller <|-- endpoints_REST_Facets_Controller
    REST_Controller <|-- endpoints_REST_Filter_Types_Controller
    REST_Controller <|-- endpoints_REST_Filters_Controller
    REST_Controller <|-- endpoints_REST_Importers_Controller
    REST_Controller <|-- endpoints_REST_Item_Metadata_Controller
    REST_Controller <|-- endpoints_REST_Items_Controller
    REST_Controller <|-- endpoints_REST_Logs_Controller
    REST_Controller <|-- endpoints_REST_Metadata_Controller
    REST_Controller <|-- endpoints_REST_Metadata_Sections_Controller
    REST_Controller <|-- endpoints_REST_Metadata_Types_Controller
    REST_Controller <|-- endpoints_REST_Metadatum_Mappers_Controller
    REST_Controller <|-- endpoints_REST_Oaipmh_Controller
    REST_Controller <|-- endpoints_REST_Reports_Controller
    REST_Controller <|-- endpoints_REST_Roles_Controller
    REST_Controller <|-- endpoints_REST_Sequence_Edit_Controller
    REST_Controller <|-- endpoints_REST_Taxonomies_Controller
    REST_Controller <|-- endpoints_REST_Terms_Controller
```
#### Classes

| Class                                                       | Description                                            |
|-------------------------------------------------------------|--------------------------------------------------------|
| [`REST_Controller`](classes/Tainacan/API/REST_Controller.md) | Abstract base class for Tainacan REST API controllers. |

### \Tainacan\API\EndPoints



#### Namespace Diagram

```mermaid
classDiagram
    direction TB
    class REST_Sequence_Edit_Controller { }
    class REST_Taxonomies_Controller { }
    class REST_Filter_Types_Controller { }
    class REST_Metadata_Sections_Controller { }
    class REST_Metadatum_Mappers_Controller { }
    class REST_Items_Controller { }
    class REST_Filters_Controller { }
    class REST_Roles_Controller { }
    class REST_Exporters_Controller { }
    class REST_Bulkedit_Controller { }
    class REST_Facets_Controller { }
    class REST_Oaipmh_Controller { }
    class REST_Terms_Controller { }
    class REST_Importers_Controller { }
    class REST_Exposers_Controller { }
    class REST_Metadata_Controller { }
    class REST_Logs_Controller { }
    class REST_Item_Metadata_Controller { }
    class REST_Collections_Controller { }
    class REST_Reports_Controller { }
    class REST_Background_Processes_Controller { }
    class REST_Metadata_Types_Controller { }
```
#### Classes

| Class                                                                                                           | Description                                                              |
|-----------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------|
| [`REST_Background_Processes_Controller`](classes/Tainacan/API/EndPoints/REST_Background_Processes_Controller.md) | REST API controller for managing Tainacan background processes.          |
| [`REST_Bulkedit_Controller`](classes/Tainacan/API/EndPoints/REST_Bulkedit_Controller.md)                         | REST API controller for managing Tainacan bulk edit operations.          |
| [`REST_Collections_Controller`](classes/Tainacan/API/EndPoints/REST_Collections_Controller.md)                   | REST API controller for managing Tainacan collections.                   |
| [`REST_Exporters_Controller`](classes/Tainacan/API/EndPoints/REST_Exporters_Controller.md)                       | REST API controller for managing Tainacan exporters.                     |
| [`REST_Exposers_Controller`](classes/Tainacan/API/EndPoints/REST_Exposers_Controller.md)                         | REST API controller for managing Tainacan exposers.                      |
| [`REST_Facets_Controller`](classes/Tainacan/API/EndPoints/REST_Facets_Controller.md)                             | REST API controller for managing Tainacan facets.                        |
| [`REST_Filter_Types_Controller`](classes/Tainacan/API/EndPoints/REST_Filter_Types_Controller.md)                 | REST API controller for managing Tainacan filter types.                  |
| [`REST_Filters_Controller`](classes/Tainacan/API/EndPoints/REST_Filters_Controller.md)                           | REST API controller for managing Tainacan filters.                       |
| [`REST_Importers_Controller`](classes/Tainacan/API/EndPoints/REST_Importers_Controller.md)                       | REST API controller for managing Tainacan importers.                     |
| [`REST_Item_Metadata_Controller`](classes/Tainacan/API/EndPoints/REST_Item_Metadata_Controller.md)               | REST API controller for managing Tainacan item metadata.                 |
| [`REST_Items_Controller`](classes/Tainacan/API/EndPoints/REST_Items_Controller.md)                               | REST API controller for managing Tainacan items.                         |
| [`REST_Logs_Controller`](classes/Tainacan/API/EndPoints/REST_Logs_Controller.md)                                 | REST API controller for managing Tainacan logs.                          |
| [`REST_Metadata_Controller`](classes/Tainacan/API/EndPoints/REST_Metadata_Controller.md)                         | REST API controller for managing Tainacan metadata.                      |
| [`REST_Metadata_Sections_Controller`](classes/Tainacan/API/EndPoints/REST_Metadata_Sections_Controller.md)       | REST API controller for managing Tainacan metadata sections.             |
| [`REST_Metadata_Types_Controller`](classes/Tainacan/API/EndPoints/REST_Metadata_Types_Controller.md)             | REST API controller for managing Tainacan metadata types.                |
| [`REST_Metadatum_Mappers_Controller`](classes/Tainacan/API/EndPoints/REST_Metadatum_Mappers_Controller.md)       | REST API controller for managing Tainacan metadatum mappers.             |
| [`REST_Oaipmh_Controller`](classes/Tainacan/API/EndPoints/REST_Oaipmh_Controller.md)                             | REST controller exposing the repository as an OAI-PMH 2.0 data provider. |
| [`REST_Reports_Controller`](classes/Tainacan/API/EndPoints/REST_Reports_Controller.md)                           | REST API controller for managing Tainacan reports.                       |
| [`REST_Roles_Controller`](classes/Tainacan/API/EndPoints/REST_Roles_Controller.md)                               | REST API controller for managing Tainacan roles and capabilities.        |
| [`REST_Sequence_Edit_Controller`](classes/Tainacan/API/EndPoints/REST_Sequence_Edit_Controller.md)               | REST API controller for managing Tainacan sequence edit operations.      |
| [`REST_Taxonomies_Controller`](classes/Tainacan/API/EndPoints/REST_Taxonomies_Controller.md)                     | REST API controller for managing Tainacan taxonomies.                    |
| [`REST_Terms_Controller`](classes/Tainacan/API/EndPoints/REST_Terms_Controller.md)                               | REST API controller for managing Tainacan taxonomy terms.                |

### \Tainacan\Entities



#### Namespace Diagram

```mermaid
classDiagram
    direction TB
    class Item { }
    class Collection { }
    class Metadata_Section { }
    class Log { }
    class Term { }
    class Taxonomy { }
    class Entity { }
    class Filter { }
    class Metadatum { }
    class Item_Metadata_Entity { }
    Collection ..> Collection
    Collection ..> Metadatum
    Entity ..> Collection
    Entity ..> Entity
    Entity ..> Item
    Entity <|-- Collection
    Entity <|-- Filter
    Entity <|-- Item
    Entity <|-- Item_Metadata_Entity
    Entity <|-- Log
    Entity <|-- Metadata_Section
    Entity <|-- Metadatum
    Entity <|-- Taxonomy
    Entity <|-- Term
    Filter ..> Metadatum
    Item ..> Item_Metadata_Entity
    Item ..> Metadata_Section
    Item ..> Metadatum
    Item_Metadata_Entity ..> Item
    Item_Metadata_Entity ..> Item_Metadata_Entity
    Item_Metadata_Entity ..> Metadatum
    Item_Metadata_Entity ..> Term
    Metadatum ..> Metadata_Section
    Metadatum ..> Metadatum
```
#### Classes

| Class                                                                      | Description                                    |
|----------------------------------------------------------------------------|------------------------------------------------|
| [`Collection`](classes/Tainacan/Entities/Collection.md)                     | Represents a Tainacan Collection entity.       |
| [`Entity`](classes/Tainacan/Entities/Entity.md)                             | Abstract base class for all Tainacan entities. |
| [`Filter`](classes/Tainacan/Entities/Filter.md)                             | Represents a Tainacan Filter entity.           |
| [`Item_Metadata_Entity`](classes/Tainacan/Entities/Item_Metadata_Entity.md) | Represents a Tainacan Item Metadata Entity.    |
| [`Item`](classes/Tainacan/Entities/Item.md)                                 | Represents a Tainacan Item entity.             |
| [`Log`](classes/Tainacan/Entities/Log.md)                                   | Represents a Tainacan Log entity.              |
| [`Metadata_Section`](classes/Tainacan/Entities/Metadata_Section.md)         | Represents a Tainacan Metadata Section entity. |
| [`Metadatum`](classes/Tainacan/Entities/Metadatum.md)                       | Represents a Tainacan Metadatum entity.        |
| [`Taxonomy`](classes/Tainacan/Entities/Taxonomy.md)                         | Represents a Tainacan Taxonomy entity.         |
| [`Term`](classes/Tainacan/Entities/Term.md)                                 | Represents a Tainacan Term entity.             |

### \Tainacan\Exporter



#### Namespace Diagram

```mermaid
classDiagram
    direction TB
    class Background_Exporter { }
    class CSV { }
    class XLSX_Exporter { }
    class Term_Exporter { }
    class Exporter_Handler { }
    class Exporter { }
    class traits_Exporter_Handler_Cell { }
    Exporter ..> Exporter_Handler
    Exporter <|-- CSV
    Exporter <|-- Term_Exporter
    Exporter <|-- XLSX_Exporter
    Exporter_Handler ..> Background_Exporter
    Exporter_Handler ..> Exporter
```
#### Classes

| Class                                                        | Description |
|--------------------------------------------------------------|-------------|
| [`CSV`](classes/Tainacan/Exporter/CSV.md)                     |             |
| [`Exporter`](classes/Tainacan/Exporter/Exporter.md)           |             |
| [`Term_Exporter`](classes/Tainacan/Exporter/Term_Exporter.md) |             |
| [`XLSX_Exporter`](classes/Tainacan/Exporter/XLSX_Exporter.md) |             |

### \Tainacan\Exposers



#### Namespace Diagram

```mermaid
classDiagram
    direction TB
    class Txt { }
    class Exposers_Handler { }
    class Csv { }
    class OAI_PMH { }
    class Html { }
    class JSON_flat { }
    class Xml { }
    class JSON_LD { }
    class Exposer { }
    Exposer <|-- Csv
    Exposer <|-- Html
    Exposer <|-- JSON_LD
    Exposer <|-- JSON_flat
    Exposer <|-- OAI_PMH
    Exposer <|-- Txt
    Exposer <|-- Xml
    Exposers_Handler ..> Exposer
    JSON_LD ..> Exposers_Handler
    Xml ..> Exposers_Handler
```
#### Classes

| Class                                                | Description                                 |
|------------------------------------------------------|---------------------------------------------|
| [`Csv`](classes/Tainacan/Exposers/Csv.md)             | Generate a Csv formated response            |
| [`Exposer`](classes/Tainacan/Exposers/Exposer.md)     | abstract class for implement exposer types  |
| [`Html`](classes/Tainacan/Exposers/Html.md)           | Generate a Html formated response           |
| [`JSON_flat`](classes/Tainacan/Exposers/JSON_flat.md) | Generate a text formated response           |
| [`JSON_LD`](classes/Tainacan/Exposers/JSON_LD.md)     | Generate a text formated response           |
| [`OAI_PMH`](classes/Tainacan/Exposers/OAI_PMH.md)     | Generate a OAI_PMH/oai_dc formated response |
| [`Txt`](classes/Tainacan/Exposers/Txt.md)             | Generate a text formated response           |
| [`Xml`](classes/Tainacan/Exposers/Xml.md)             | Generate a Csv formated response            |

### \Tainacan\Filter_Types

#### Classes

| Class                                                                                                            | Description              |
|------------------------------------------------------------------------------------------------------------------|--------------------------|
| [`Autocomplete`](classes/Tainacan/Filter_Types/Autocomplete.md)                                                   | Class TainacanFilterType |
| [`Checkbox`](classes/Tainacan/Filter_Types/Checkbox.md)                                                           | Class TainacanFilterType |
| [`Date`](classes/Tainacan/Filter_Types/Date.md)                                                                   | Class TainacanFilterType |
| [`Date_Helper`](classes/Tainacan/Filter_Types/Date_Helper.md)                                                     |                          |
| [`Date_Interval`](classes/Tainacan/Filter_Types/Date_Interval.md)                                                 | Class TainacanFilterType |
| [`Date_Interval_Interval_Helper`](classes/Tainacan/Filter_Types/Date_Interval_Interval_Helper.md)                 |                          |
| [`Dates_Intersection`](classes/Tainacan/Filter_Types/Dates_Intersection.md)                                       | Class TainacanFilterType |
| [`Dates_Intersection_Interval_Helper`](classes/Tainacan/Filter_Types/Dates_Intersection_Interval_Helper.md)       |                          |
| [`Filter_Type`](classes/Tainacan/Filter_Types/Filter_Type.md)                                                     | Class TainacanFilterType |
| [`Filter_Type_Helper`](classes/Tainacan/Filter_Types/Filter_Type_Helper.md)                                       | Class FilterTypeHelper   |
| [`Numeric`](classes/Tainacan/Filter_Types/Numeric.md)                                                             | Class TainacanFilterType |
| [`Numeric_Helper`](classes/Tainacan/Filter_Types/Numeric_Helper.md)                                               |                          |
| [`Numeric_Interval`](classes/Tainacan/Filter_Types/Numeric_Interval.md)                                           | Class TainacanFilterType |
| [`Numeric_Interval_Interval_Helper`](classes/Tainacan/Filter_Types/Numeric_Interval_Interval_Helper.md)           |                          |
| [`Numeric_List_Interval_Helper`](classes/Tainacan/Filter_Types/Numeric_List_Interval_Helper.md)                   |                          |
| [`Numeric_List_Interval`](classes/Tainacan/Filter_Types/Numeric_List_Interval.md)                                 | Class TainacanFilterType |
| [`Numerics_Intersection`](classes/Tainacan/Filter_Types/Numerics_Intersection.md)                                 | Class TainacanFilterType |
| [`Numerics_Intersection_Interval_Helper`](classes/Tainacan/Filter_Types/Numerics_Intersection_Interval_Helper.md) |                          |
| [`Selectbox`](classes/Tainacan/Filter_Types/Selectbox.md)                                                         | Class TainacanFilterType |
| [`Taginput`](classes/Tainacan/Filter_Types/Taginput.md)                                                           | Class TainacanFilterType |
| [`TaxonomyCheckbox`](classes/Tainacan/Filter_Types/TaxonomyCheckbox.md)                                           | Class TainacanFilterType |
| [`TaxonomySelectbox`](classes/Tainacan/Filter_Types/TaxonomySelectbox.md)                                         | Class TaxonomySelectbox  |
| [`TaxonomyTaginput`](classes/Tainacan/Filter_Types/TaxonomyTaginput.md)                                           | Class Taginput           |

### \Tainacan\GenericProcess



#### Namespace Diagram

```mermaid
classDiagram
    direction TB
    class Generic_Process { }
    class Background_Generic_Process { }
    class Bulk_Edit_Process { }
    class Generic_Process_Handler { }
    Generic_Process <|-- Bulk_Edit_Process
    Generic_Process_Handler ..> Background_Generic_Process
    Generic_Process_Handler ..> Generic_Process
```
#### Classes

| Class                                                                      | Description |
|----------------------------------------------------------------------------|-------------|
| [`Bulk_Edit_Process`](classes/Tainacan/GenericProcess/Bulk_Edit_Process.md) |             |
| [`Generic_Process`](classes/Tainacan/GenericProcess/Generic_Process.md)     |             |

### \Tainacan\Importer



#### Namespace Diagram

```mermaid
classDiagram
    direction TB
    class Oaipmh_Importer { }
    class Background_Importer { }
    class Importer_Handler { }
    class CSV { }
    class Flickr_Importer { }
    class Term_Importer { }
    class Test_Importer { }
    class Youtube_Importer { }
    class Importer { }
    Importer ..> Importer_Handler
    Importer <|-- CSV
    Importer <|-- Flickr_Importer
    Importer <|-- Oaipmh_Importer
    Importer <|-- Term_Importer
    Importer <|-- Test_Importer
    Importer <|-- Youtube_Importer
    Importer_Handler ..> Background_Importer
    Importer_Handler ..> Importer
```
#### Classes

| Class                                                              | Description   |
|--------------------------------------------------------------------|---------------|
| [`CSV`](classes/Tainacan/Importer/CSV.md)                           |               |
| [`Flickr_Importer`](classes/Tainacan/Importer/Flickr_Importer.md)   |               |
| [`Importer`](classes/Tainacan/Importer/Importer.md)                 |               |
| [`Oaipmh_Importer`](classes/Tainacan/Importer/Oaipmh_Importer.md)   |               |
| [`Term_Importer`](classes/Tainacan/Importer/Term_Importer.md)       |               |
| [`Test_Importer`](classes/Tainacan/Importer/Test_Importer.md)       | Test Importer |
| [`Youtube_Importer`](classes/Tainacan/Importer/Youtube_Importer.md) |               |

### \Tainacan\Integrations



#### Namespace Diagram

```mermaid
classDiagram
    direction TB
    class Elastic_Press_lte4 { }
    class WordPress_AI { }
    class Elastic_Press { }
    Elastic_Press ..> Elastic_Press
    Elastic_Press ..> Elastic_Press_lte4
```
#### Classes

| Class                                                                      | Description                                                                 |
|----------------------------------------------------------------------------|-----------------------------------------------------------------------------|
| [`Elastic_Press_lte4`](classes/Tainacan/Integrations/Elastic_Press_lte4.md) |                                                                             |
| [`Elastic_Press`](classes/Tainacan/Integrations/Elastic_Press.md)           | Class Elastic_Press                                                         |
| [`WordPress_AI`](classes/Tainacan/Integrations/WordPress_AI.md)             | Integration with the WordPress AI plugin (https://github.com/WordPress/ai). |

### \Tainacan\Mappers



#### Namespace Diagram

```mermaid
classDiagram
    direction TB
    class Inbcm_Archive { }
    class Dublin_Core { }
    class Mappers_Handler { }
    class Mapper { }
    class Inbcm_Museological { }
    class Inbcm_Bibliographic { }
    Mapper <|-- Dublin_Core
    Mapper <|-- Inbcm_Archive
    Mapper <|-- Inbcm_Bibliographic
    Mapper <|-- Inbcm_Museological
    Mappers_Handler ..> Mapper
```
#### Classes

| Class                                                                   | Description                                                  |
|-------------------------------------------------------------------------|--------------------------------------------------------------|
| [`Dublin_Core`](classes/Tainacan/Mappers/Dublin_Core.md)                 | Support Dublin Core Mapping
http://purl.org/dc/elements/1.1/ |
| [`Inbcm_Archive`](classes/Tainacan/Mappers/Inbcm_Archive.md)             |                                                              |
| [`Inbcm_Bibliographic`](classes/Tainacan/Mappers/Inbcm_Bibliographic.md) |                                                              |
| [`Inbcm_Museological`](classes/Tainacan/Mappers/Inbcm_Museological.md)   |                                                              |
| [`Mapper`](classes/Tainacan/Mappers/Mapper.md)                           |                                                              |

### \Tainacan\Metadata_Types

#### Classes

| Class                                                                                      | Description                                                                    |
|--------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------|
| [`Compound`](classes/Tainacan/Metadata_Types/Compound.md)                                   | Class TainacanMetadatumType                                                    |
| [`MetadataTypeControlHelper`](classes/Tainacan/Metadata_Types/MetadataTypeControlHelper.md) |                                                                                |
| [`Control`](classes/Tainacan/Metadata_Types/Control.md)                                     | Class TainacanMetadatumType                                                    |
| [`Core_Description`](classes/Tainacan/Metadata_Types/Core_Description.md)                   | Class TainacanMetadatumType                                                    |
| [`Core_Title`](classes/Tainacan/Metadata_Types/Core_Title.md)                               | Class TainacanMetadatumType                                                    |
| [`Date`](classes/Tainacan/Metadata_Types/Date.md)                                           | Class TainacanMetadatumType                                                    |
| [`GeoCoordinate_Helper`](classes/Tainacan/Metadata_Types/GeoCoordinate_Helper.md)           | Joins post meta and appends spatial WHERE clauses for `geoquery` item queries. |
| [`GeoCoordinate`](classes/Tainacan/Metadata_Types/GeoCoordinate.md)                         | Class GeoCoordinate                                                            |
| [`Metadata_Type`](classes/Tainacan/Metadata_Types/Metadata_Type.md)                         | Class TainacanMetadatumType                                                    |
| [`Metadata_Type_Helper`](classes/Tainacan/Metadata_Types/Metadata_Type_Helper.md)           | Class MetadataTypeHelper                                                       |
| [`Numeric`](classes/Tainacan/Metadata_Types/Numeric.md)                                     | Class TainacanMetadatumType                                                    |
| [`Relationship`](classes/Tainacan/Metadata_Types/Relationship.md)                           | Class TainacanMetadatumType                                                    |
| [`Selectbox`](classes/Tainacan/Metadata_Types/Selectbox.md)                                 | Class TainacanMetadatumType                                                    |
| [`Taxonomy`](classes/Tainacan/Metadata_Types/Taxonomy.md)                                   | Class TainacanMetadatumType                                                    |
| [`Text`](classes/Tainacan/Metadata_Types/Text.md)                                           | Class TainacanMetadatumType                                                    |
| [`Textarea`](classes/Tainacan/Metadata_Types/Textarea.md)                                   | Class TainacanMetadatumType                                                    |
| [`URL`](classes/Tainacan/Metadata_Types/URL.md)                                             | Class TainacanMetadatumType                                                    |
| [`User`](classes/Tainacan/Metadata_Types/User.md)                                           | Class TainacanMetadatumType                                                    |

### \Tainacan\OAIPMH



#### Namespace Diagram

```mermaid
classDiagram
    direction TB
    class OAIPMH_Xml_Generator { }
    class OAIPMH_Data_Provider { }
    class OAIPMH_Token_Manager { }
    OAIPMH_Xml_Generator ..> OAIPMH_Xml_Generator
```
#### Classes

| Class                                                                    | Description                                             |
|--------------------------------------------------------------------------|---------------------------------------------------------|
| [`OAIPMH_Data_Provider`](classes/Tainacan/OAIPMH/OAIPMH_Data_Provider.md) | OAI-PMH data provider.                                  |
| [`OAIPMH_Token_Manager`](classes/Tainacan/OAIPMH/OAIPMH_Token_Manager.md) | resumptionToken storage backed by WordPress transients. |
| [`OAIPMH_Xml_Generator`](classes/Tainacan/OAIPMH/OAIPMH_Xml_Generator.md) | Builds OAI-PMH 2.0 XML responses with DOMDocument.      |

### \Tainacan\Repositories



#### Namespace Diagram

```mermaid
classDiagram
    direction TB
    class Logs { }
    class Filters { }
    class Taxonomies { }
    class Terms { }
    class Metadata { }
    class Repository { }
    class Items { }
    class Logs { }
    class Item_Metadata { }
    class Collections { }
    class Metadata_Sections { }
    Collections ..> Metadata
    Filters ..> Collections
    Item_Metadata ..> Items
    Item_Metadata ..> Metadata
    Item_Metadata ..> Taxonomies
    Item_Metadata ..> Terms
    Items ..> Collections
    Items ..> Metadata
    Items ..> Taxonomies
    Logs ..> Repository
    Logs ..> Taxonomies
    Metadata ..> Items
    Metadata ..> Metadata
    Metadata ..> Taxonomies
    Metadata_Sections ..> Metadata
    Repository ..> Collections
    Repository ..> Filters
    Repository ..> Item_Metadata
    Repository ..> Items
    Repository ..> Logs
    Repository ..> Metadata
    Repository ..> Metadata_Sections
    Repository ..> Repository
    Repository ..> Taxonomies
    Repository ..> Terms
    Repository <|-- Collections
    Repository <|-- Filters
    Repository <|-- Item_Metadata
    Repository <|-- Items
    Repository <|-- Logs
    Repository <|-- Metadata
    Repository <|-- Metadata_Sections
    Repository <|-- Taxonomies
    Repository <|-- Terms
    Taxonomies ..> Collections
    Taxonomies ..> Metadata
    Taxonomies ..> Terms
    Terms ..> Taxonomies
```
#### Classes

| Class                                                                    | Description                                            |
|--------------------------------------------------------------------------|--------------------------------------------------------|
| [`Collections`](classes/Tainacan/Repositories/Collections.md)             | Repository for managing Tainacan collections.          |
| [`Filters`](classes/Tainacan/Repositories/Filters.md)                     | Repository for managing Tainacan filters.              |
| [`Item_Metadata`](classes/Tainacan/Repositories/Item_Metadata.md)         | Repository for managing Tainacan item metadata.        |
| [`Items`](classes/Tainacan/Repositories/Items.md)                         | Repository for managing Tainacan items.                |
| [`Logs`](classes/Tainacan/Repositories/Logs.md)                           | Abstract base class for all Tainacan repositories.     |
| [`Logs`](classes/Tainacan/Repositories/Logs.md)                           | Repository for managing Tainacan logs.                 |
| [`Metadata_Sections`](classes/Tainacan/Repositories/Metadata_Sections.md) | Repository for managing Tainacan metadata sections.    |
| [`Metadata`](classes/Tainacan/Repositories/Metadata.md)                   | Repository for managing Tainacan metadata definitions. |
| [`Repository`](classes/Tainacan/Repositories/Repository.md)               | Abstract base class for all Tainacan repositories.     |
| [`Taxonomies`](classes/Tainacan/Repositories/Taxonomies.md)               | Repository for managing Tainacan taxonomies.           |
| [`Terms`](classes/Tainacan/Repositories/Terms.md)                         | Repository for managing Tainacan taxonomy terms.       |

### \Tainacan\Traits



#### Namespace Diagram

```mermaid
classDiagram
    direction TB
    class Entity_Collections_Relation { }
    class Entity_Collection_Relation { }
    class Admin_UI_Options { }
    class Formatter_Text { }
    class SVG_Icon { }
    class Singleton_Instance { }
```
#### Traits

| Trait                                                                                  | Description                                                                                                                                                                                      |
|----------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| [`Exporter_Handler_Cell`](classes/Tainacan/Traits/Exporter_Handler_Cell.md)             |                                                                                                                                                                                                  |
| [`Admin_UI_Options`](classes/Tainacan/Traits/Admin_UI_Options.md)                       | Class for getting Admin UI options passed either via query string in
the URL or via the 'tainacan-admin-ui-options' filter.                                                                      |
| [`Entity_Collection_Relation`](classes/Tainacan/Traits/Entity_Collection_Relation.md)   | Defines Collection and Items relation                                                                                                                                                            |
| [`Entity_Collections_Relation`](classes/Tainacan/Traits/Entity_Collections_Relation.md) |                                                                                                                                                                                                  |
| [`Formatter_Text`](classes/Tainacan/Traits/Formatter_Text.md)                           | Class for formatter texts                                                                                                                                                                        |
| [`Singleton_Instance`](classes/Tainacan/Traits/Singleton_Instance.md)                   | Class for Singleton pattern                                                                                                                                                                      |
| [`SVG_Icon`](classes/Tainacan/Traits/SVG_Icon.md)                                       | Class for getting inline svg icons based on the Tainacan Icon font and new symbols needed
The svg files have a fill of currentColor and width and height of 1em to allow external customization. |
