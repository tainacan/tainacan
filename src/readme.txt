=== Tainacan ===
Contributors: alanargomes, andrebenedito, ateneagarcia123, ccaio, clarandreozzi, daltonmartins, eduardohumberto, fabianobn, jacsonp, jessicafpx, leogermani, marinagiolo, omarceloavila, ravipassos, rodrigo0freire, suelanesilva, tainacan, vnmedeiros, weryques, wetah
Tags: museums, archives, GLAM, collections, repository
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.0
Stable tag: 1.0.3
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A powerful and flexible open-source repository platform that brings digital collection management to WordPress.

== Description ==

[Tainacan](https://tainacan.org/) is an [open-source](https://github.com/tainacan/tainacan) repository platform that turns WordPress into a powerful and flexible environment for managing and publishing digital collections — as easily as writing a blog post.

Designed for cultural institutions, research projects, archives, museums, and any kind of digital collection, Tainacan integrates seamlessly with the WordPress block editor and takes full advantage of its ecosystem.

**Key Features:**


* **Free and open source** – Licensed under GPLv3: use, modify, and share freely
* **WordPress based** - Built to last taking advantage of the power of the WordPress ecosystem and features
* **Compatible with any theme** - Use the Tainacan Interface theme or adapt any WordPress theme
* **Easy management** - Create collections, define metadata, manage users, and publish content effortlessly
* **Highly customizable** - Configure metadata, taxonomies, and filters to match your project’s needs
* **Faceted search** - Offer advanced browsing with intuitive custom filters
* **Importing and exporting** - Import bulk data from spreadsheets, export in CSV, XLSX, JSON, and other formats
* **API and interoperability** - Complete RESTful API with support for metadata mapping to standards such as Dublin Core
* **Gutenberg blocks** - Tell stories about your digital archive using a variety of blocks anywhere in your site

== Installation ==

1. Upload the `tainacan` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. You will see a new menu item called "Tainacan" in your admin panel
4. Click on it to open the Tainacan admin interface and start creating your collections

**Requirements:**

* WordPress 5.9 or higher
* PHP 7.0 or higher
* For automatic PDF thumbnail generation, Imagick is recommended (but not required)

== Frequently Asked Questions ==

= How do I get started? =

After installation, click on the "Tainacan" menu item in your WordPress admin panel. You can create your first collection by clicking "New Collection", configure its metadata and filters, and start adding items to your collection.

For an overview of the main concepts, visit our [documentation](https://tainacan.github.io/tainacan-wiki/#/general-concepts).

= Can I use Tainacan with any WordPress theme? =

Yes! Tainacan works with any WordPress theme, but developers can improve their integration. If you want more dedicated page templates we recommend using the [Tainacan Interface](https://wordpress.org/themes/tainacan-interface) theme, which is our classic theme that meets the demands of most collections. But you can also try using an FSE theme such as [Tainá](https://wordpress.org/themes/taina/), or install an [integration plugin](https://wordpress.org/plugins/tainacan-blocksy/) for a popular theme such as [Blocksy](https://wordpress.org/themes/blocksy/).

= How do I browse my collections on the public side? =

Visit `https://your-site/collections` to see the list of your collections. You can also add links to the collections in your menu by editing it and enabling "Collections" in the Screen options.

= Can I import data from other systems? =

Yes! Tainacan supports importing bulk data from CSV spreadsheets. You can also use bulk editing tools to perform adaptations from your system modeling to the one you created in Tainacan.

= Does Tainacan have an API? =

Yes! Tainacan implements a complete RESTful API (read and write) that allows other applications to interact with your repository. You can expose your collection in different formats such as JSON, CSV, and HTML.

= What metadata standards are supported? =

Tainacan allows you to define your own metadata standards by adopting a variety of types (text, relationship, numeric, date, geocoordinate etc.), but also supports predefined ones including Dublin Core and INBCM. Even when creating your own, you can still define mappers to other standards, allowing you to export to other systems.

= What are taxonomies and how do I use them? =

Taxonomies in Tainacan are vocabularies that can be used across all your collections. You can configure taxonomy metadata with a set of hierarchical terms of your own vocabulary. This allows you to create controlled vocabularies that can be reused throughout your repository, ensuring consistency across different collections.

= How do I use Gutenberg blocks with Tainacan? =

Tainacan offers several Gutenberg blocks so you can present your collections in many different ways! You can display items, collections, search interfaces, and more. Simply add the blocks to your posts and pages through the WordPress block editor.

= Where do I report security bugs found in this plugin? =

We take security very seriously. Please report any security bugs found in the source code of the Tainacan plugin through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/tainacan). The Patchstack team will assist you with verification, CVE assignment, and notify the developers of this plugin.

= I'm getting 404 errors or blank pages when accessing my collection =

After site migration, plugin updates, or new installations, you may need to rebuild WordPress permalinks. If collections or pages are returning 404 errors despite already having items on it, go to **Settings** -> **Permalinks** in your WordPress admin panel, ensure "Post Name" or a Custom Structure with /%postname%/ is selected, then click **Save Changes** (even if nothing changed). This will rebuild the permalink structure.

== Screenshots ==

1. Manage your repository
2. Set up your collection
3. Choose the metadata and filters for your collection
4. Add items described by your metadata
5. Set up your item as a file, link or text and attach many types of documents
6. Browse your collections with a faceted search interface
7. Navigate through the rich filtering interface
8. Explore more with Advanced Search
9. Set up Taxonomies to be used across your repository
10. Bulk edit as many items as you need quickly
11. Expose your collection using Tainacan default theme
12. Use Gutenberg blocks to display your collections in posts and pages
13. Choose which items will be displayed in your block
14. Items displayed using a Gutenberg block

== Changelog ==

To see the changelog, please visit the [GitHub Releases](https://github.com/tainacan/tainacan/releases) page.

== Support ==

**Need help?**
Find documentation, community support, and development resources at:

* **Website**: [https://tainacan.org/](https://tainacan.org/)
* **Documentation Wiki**: [https://wiki.tainacan.org/](https://wiki.tainacan.org/)
* **GitHub**: [https://github.com/tainacan/tainacan](https://github.com/tainacan/tainacan)
* **User Forum**: [https://tainacan.discourse.group/](https://tainacan.discourse.group/)
