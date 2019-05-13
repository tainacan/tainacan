# Tainacan for developers

As you know, Tainacan is a WordPress plugin and is built on the top of this very well known platform. If you know WordPress it will be very easy for you to understand how Tainacan is organized, how it interacts with the database and how to build your own features on the top of it.

## Well, but I'm new to WordPress

If you don't have expirience with WordPress and would like to develop a plugin for Tainacan, or to contribute to the Tainacan plugin, it's a good idea to get to know some WordPress fundamentals. Those will be useful to everything you will deal with while working with Tainacan.

This is a non-exhaustive list of the most important topics you should look into:

* [WP_Query](https://codex.wordpress.org/Class_Reference/WP_Query) class - This is the heart of WordPress, the class that gives you the interface fo query for posts in the database. All interaction with the database in Tainacan uses this class.
* [Custom Post types](https://codex.wordpress.org/Post_Types) and [taxonomies](https://codex.wordpress.org/Taxonomies) - All Tainacan entities, such as collections, metadata, filters and items, are WordPress Custom post types. To understand how WordPress handles custom post types and custom taxonomies is very helpful.
* [The Loop](https://codex.wordpress.org/The_Loop) - One of the main WordPress elements used to interact through posts. Useful specially if you are tweaking with themes.
* [Template Tags](https://codex.wordpress.org/Template_Tags) - Simple functions used by theme developers to display dynamic content. Usually these function are used inside "The Loop" and Tainacan implements [it's own Template tags](../src/theme-helper/template-tags.php).
* [Template Hierarchy](https://developer.wordpress.org/themes/basics/template-hierarchy/) - Crucial if working with themes.
* Custom queries and loops - Based on the topics above, it is good to understand how to build custom queries and loops to list posts.

## Resources

### Development Resources

* [Key Concepts](key-concepts.md) - First things first. Let's understand what is what in Tainacan.
* [Tainacan internals](internal-api.md) - Reference on Tainacan main classes and how to use them.
* [Setting up local environment](setup-local.md) - If you want to contribute to Tainacan core, you must set up your local environment. Alternatively, you can use our [Docker repository](https://github.com/tainacan/tainacan-docker). **If you want to develop themes or plugins, you don't need this**.
* [Tainacan Custom templates](custom-templates.md) - Custom templates that Tainacan add to WordPress Template Hierarchy
* [Tainacan Template Tags](../src/theme-helper/template-tags.php) - Template tags useful to use in templates 
* Tainacan Hooks - soon 
* [Tainacan API](https://tainacan.org/api-docs/) - (Under construction)
* [Exporting and Exposing](exporting-and-exposing.md)
* [Creating a new Metadata Type](creating-metadata-type.md)
* [How to create Exporters](exporter-flow.md)
* [How to create Importers](importer-flow.md)
* [How to create Exposers](exposers.md)
* [Creating new View Modes](https://wiki.tainacan.org/index.php?title=Extra_View_Modes)

### Configuration and performance

* [Faceted Search](faceted-search.md)
* [Search Engine](search-engine.md)
* [Garbage Collector](garbage-collector.md)

### Other

* [Tainacan release process](release.md)

