=== Tainacan ===
Contributors: fabianobn, jacsonp, leogermani, weryques
Tags: museums, libraries, archives, GLAM, collections, repository
Requires at least: 4.8
Tested up to: 4.9
Requires PHP: 5.6
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Tainacan is a powerful and flexible repository platform for WordPress. Manage and publish you digital collections as easily as publishing a post to your blog, while having all the tools of a professional repository platform.

== Description ==

Tainacan is a powerful and flexible repository platform for WordPress. Manage and publish you digital collections as easily as publishing a post to your blog, while having all the tools of a professional repository platform.

Please note: This is an (super) early release of this plugin, and we are working hard to release 1.0 soon, please refer to the [project's website](http://tainacan.org/new) for more information and road map.

= Features =

* "Metadata and Filters": Use a metadata standard or choose whatever set of metadata you want to describe the items in your collections. Also, choose which metadata will be used as a filter when browsing the collection
Faceted Search. Browse your collection (and let the public browse it) using a faceted search interface with the filters you have chosen.

* "Manage Taxonomies": Manage vocabularies that can be used accross all your collections.

* "Themes": Tainacan has its own default theme, that helps you present your collections in a beautiful and effective way, but will also work with any WordPress theme. For developers, it will be easy to add tainacan specific features to an existing theme.

* "API and Interoperability": Tainacan implements a RESTful API (read and write) to allow other applications to interact with your repository. Expose you collection in different formats, such as Json, JsonLD, OAI-PMH and others. If your collection have a specific set of metadata, you can map your fields to match the standards you want to use.

== Getting Started ==

After installation, you will see a new menu item in your admin panel called "Tainacan". Click on it to open the Tainacan admin interface.

To get an overview of the main concepts of Tainacan, please visit [this page](https://github.com/tainacan/tainacan/blob/develop/docs/key-concepts.md).

= Create a collection = 

Click "New Collection" to create a collection and choose a name for it.

= Configure your collection = 

Navigate the top menu to set your collection up. Create the metadata item in this collection will have, and choose, from these metadata, which ones are going to be used as a filter.

= Add items = 

Back to the "Items" screen, click "Add new" to create a new item.

= Manage and browse your collection =

Through this admin interface you can manage your collection and browse its item using the faceted search interface.

If you want to visit your collections in the public side of your site, using your current theme, visit http://your-site/tainacan-collection and you will get the list of your collections.

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
 
1. Manage collections
2. Set up your collection
3. Choose the metadata your collection will use
4. Add items to your collections
5. Browse your collections with a faceted search interface
