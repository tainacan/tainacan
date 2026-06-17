# Custom Templates

When using Tainacan plugin with your custom theme enabled, you'll have access to all of [Tainacan pages](tainacan-pages.md) such as Items, Collections and Taxonomy Term Items in the same way that WordPress displays any custom **post_type**. An Item's list, for example, shall have _thumbnail_, _title_ and _description_ with the same appearance that your blog posts, without featuring a _Collection banner_, _filters list_ or the _advanced search_ that is offered by themes such as [Tainacan Interface](https://wordpress.org/themes/tainacan-interface).

Here is an example of an item list in a raw [child theme](https://developer.wordpress.org/themes/advanced-topics/child-themes/) of [TwentyTwenty](https://wordpress.org/themes/twentytwenty/):

![Items list, single item page, and term items list pages on TwentyTwenty theme.](/_assets/images/custom-templates-1.gif)

It looks like a blog, right? You can also see the Item single page, which is pretty much like any post, displaying the _thumbnail_, _document_ and then _metadata_.

## Create your files for the templates

The _WordPress way_ of changing this is by creating **custom templates**. To put it simply, you need `.php` files equivalent to the ones that Tainacan plugin registers by default:

- `/tainacan/archive-items.php` - Lists Collection Items
- `/tainacan/archive-repository.php` - Lists Repository Items (all items from all collections)
- `/tainacan/archive-taxonomy.php` - Lists Items with a Term of a Taxonomy
- `/tainacan/single-items.php` - Single Item Page
- `/tainacan/archive-tainacan-collection.php` - Lists all Collections (not be confused with `archive-collections.php`, which is another template, that WordPress already uses)

Ok, so all you have to do is creating these files and fill them as you wish... but how to fetch and render the content related to Tainacan there?

### Theme Helper functions

For helping you creating your template, exclusive functions are available on your theme once the plugin is activated. You can find all or their definitions on the [Tainacan Template Tags](/dev/template-tags.md) page. We summarize them below.

If you're building a custom `single-items.php`, you might be interested in:

- `tainacan_get_item()` - Returns the Item object according to given ID;
- `tainacan_get_the_document()` or `tainacan_the_document()` - Get an HTML version of the "Item Document", which may be an image, an embed version of PDF, video links, tweets, etc.
- `tainacan_has_document()` - Checks if the current item (on a loop) has a document set or not;
- `tainacan_get_the_metadata_sections` or `tainacan_the_metadata_sections` - Get an HTML list of the metadata sections and their metadata inside. Several paramaters may be passed to tweak its appearance.
- `tainacan_get_the_metadata()` or `tainacan_the_metadata()` - Get an HTML list of item metadata. You can pass arguments to tweak how the list is created, with similar parameters to those available in some WordPress functions for rendering lists;
- `tainacan_get_the_attachments()` - Return an HTML list of attachments of the current item;
- `tainacan_get_attachment_html_url()` - Return an HTML rendered attachment, given its ID;
- `tainacan_the_item_edit_link()` - Renders an HTML link for editing an Item if the user has permission;
- `tainacan_the_item_gallery()` - Renders the "item media gallery", a component with carousel and zoom that can be used to display the document and attachments;
- `tainacan_the_related_items()` - Renders a list of items that has some relation to the current item in its relationship-type metadata;

If you are building any items archive listing, the following functions are essential:

- `tainacan_the_faceted_search()` - Renders an HTML div with ID and parameters for the Vue.js (client-side) instance responsible for an items list with filters menu, search control and view modes. It is applicable for Collection Items, Term Items, and Repository Items. Learn more about it [in The Vue Items List Component section](/dev/the-vue-items-list-component.md);
- `tainacan_register_view_mode()` - Registers a view mode. This is used by themes or plugins that implemented [custom extra view modes](/dev/extra-view-modes.md);
- `tainacan_current_view_displays()` - Checks if a certain metadata or property is to be displayed on this view mode;

If you're building a custom items or collection archive, these functions may help:

- `tainacan_get_collection_id()` - Returns the current collection ID inside an items archive or single;
- `tainacan_get_collection()` - Returns the current collection object inside an items archive or single;
- `tainacan_get_the_collection_name()` or `tainacan_the_collection_name()` - Returns the current collection name inside an items archive or single;
- `tainacan_get_the_collection_description()` or `tainacan_the_collection_description()` - Returns the current collection description inside an items archive or single;
- `tainacan_the_items_carousel` - Template tag for rendering the [Items Carousel Block](/blocks-items.md#carrossel-de-itens);

If you're building a custom term items archive, these functions may help:

- `tainacan_get_term()` - Returns the current term object inside a term items archive;
- `tainacan_get_the_term_name()` or `tainacan_the_term_name()` - Returns the current term name inside a term items archive;
- `tainacan_get_the_term_description()` or `tainacan_the_term_description()` - Returns the current term description inside a term items archive;

And more...

- `tainacan_get_the_mime_type_icon()` - Gets a mime-type icon to a file according to its type. This generates the thumbnails used in the attachments and blocks carousels;
- `tainacan_get_initials()` - A presentation function used by some thumbnails in some themes. It outputs a string version of a name with its initials - for example: "Classic Paintings" would be returned as "CP";

Those are, of course, _helper functions_. If you're not satisfied with the way the rendering is performed by then, you can create your own. Check [the source code](https://github.com/tainacan/tainacan/blob/develop/src/classes/) for a more complete idea of how to fetch Tainacan content. You can also hook into several of this functions using [our actions and filters](/dev/filters.md). There is also [an example](/dev/the-vue-items-list-component) of an `archive-items.php` implementation in the next section.

## Even more specific templates

We mentioned [above](#create-your-files-for-the-templates) templates for the basic four [Tainacan pages](tainacan-pages.md) that the plugin will generate for you. Nevertheless, you are still able to create more specific templates, using the standard [WordPress template file hierarchy](https://developer.wordpress.org/themes/basics/template-hierarchy/).

Examples:

- A template for single items page in the collection with ID 4: `single-tnc_col_4_item.php`;
- A template for a single specific item: `single-tnc_col_4_item-item-name.php`;
- A template for the list of items of the collection with ID 4: `archive-tnc_col_4_item.php`;
- A template for a specific taxonomy: `taxonomy-tnc_tax_123.php`;
- A template for a specific term of a specific taxonomy: `taxonomy-tnc_tax_123-term-name.php`;
