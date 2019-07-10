=== Tainacan ===
Contributors: andrebenedito, daltonmartins, fabianobn, jacsonp, leogermani, weryques, wetah, eduardohumberto, ravipassos, jessicafpx, marinagiolo, omarceloavila, vnmedeiros
Tags: museums, libraries, archives, GLAM, collections, repository
Requires at least: 4.8
Tested up to: 5.2.1
Requires PHP: 5.6
Stable tag: 0.10.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Tainacan is a powerful and flexible repository platform for WordPress. Manage and publish your digital collections just as easily as you post to your blog, having all the tools of a professional repository platform.

== Description ==

Tainacan is a powerful and flexible repository platform for WordPress. Manage and publish your digital collections just as easily as you post to your blog, having all the tools of a professional repository platform.

= Features =

* "Metadata and Filters": Use a metadata standard or choose whatever set of metadata you want to describe the items in your collections. Also, choose which metadata will be used as a filter when browsing collections.

* "Faceted Search". Browse your collection (and let the public browse it) using a faceted search interface with the filters you have chosen.

* "Manage Taxonomies": Manage vocabularies that can be used accross all your collections.

* "Themes": The Tainacan plugin has its own default theme, which helps you to showcase your collections in a beautiful and effective manner. But it can also work with any WordPress theme, so interface developers can easyly add Tainacan specific features to an existing theme.

* "API and Interoperability": Tainacan implements a RESTful API (read and write) to allow other applications to interact with your repository. That way, you can expose your collection in different formats, such as Json, JsonLD, OAI-PMH and others. If your collection has a specific set of metadata, you can map this metadata to match the patterns you want to use.

* "Gutenberg blocks": Tell stories with your collections. Tainacan offers you several Gutenberg blocks so you can present your collections to the public in many different ways!

== Getting Started ==

After installation, you will see a new menu item in your admin panel called "Tainacan". Click on it to open the Tainacan admin interface.

To get an overview of the main concepts of Tainacan, please visit [this page](https://github.com/tainacan/tainacan/blob/develop/docs/key-concepts.md).

= Create a collection = 

Click "New Collection" to create a new collection, use a mapping standard or import using one of our importers.

= Configure your collection = 

Navigate the top menu to set your collection up. Create the metadata that items in this collection will have, and choose, from these metadata, which ones are going to be used as a filter.

= Add items = 

Back to the "Items" screen, click "Add new" to create a new item.

= Manage and browse your collection =

Through this admin interface you can manage your collection and browse its item using the faceted search interface or advanced search interface.

If you want to visit your collections in the public side of your site, using your current theme, visit http://your-site/tainacan-collection and you will get the list of your collections.

= Set up Taxonomies =

You can also have metada as taxonomies, which you can configure with a set of hierarchical terms of your own vocabulary.

= Add links to your menu =

Edit your menu and links directly to your collections. Click "Screen options" at the top of the Menu edition page and enable "Collections".

If you want to add a link to the list of collections, click "View all" tab on the Collections box on the left, and then add the first item named "Collections" to the menu.

= Faceted search in your theme =

We are still working in a way to enable faceted search in any theme. You can try it but it might not work very well depending on your theme.

In order to do this, use the shortcode "tainacan-search" in any page of your site (we recommend a full width template page), informing the ID of the collection you want to display.

Example: [tainacan-search collection-id=4]

== Installation ==

Upload the files to the plugins directory and activate it. You can also install and activate directly from the your admin panel.

If you have Imagick installed in your server, Tainacan will be able to automatically generate a thumbnail from your PDF files. This is desired but not required.

== Screenshots ==
 
1. Manage your repository
2. Set up your collection
3. Choose the metadata and filters for your collection
4. Add items described by your metadata
5. Set up your item as a file, link or text and attach many type of documents
6. Browse your collections with a faceted search interface
7. Navigate through rich filtering interface
8. Explore more with Advanced Search
9. Set up Taxonomies to be used across your repository
10. Bulk edit as many items as you need quickly
11. Expose your collection using Tainacan default theme
12. Use Gutenberg blocks to display your collections in posts and pages
13. Choose which items will be displayed in your block
14. Items displayed using a Gutenberg block 
