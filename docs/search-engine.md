# Search engine

In addition to the [faceted search](faceted-search.md), which allows users to filter items by specific metadata, Tainacan also offers a free textual search.

By default, when using this option to search, WordPress searches only inside the Title (post_title) and Description (post_content). This, of course, is very limited, and this article presents and discusses the approach Tainacan will take to face this issue.

There isn't one silver bullet to solve this problem. In some cases, perhaps for small repositories, a simple change in the way WordPress queries for posts, including relation to metadata and taxonomies, can give users the results they were looking for. In other cases, repository managers may want to use sophisticated solutions such as Elastic Search or Solr to enable Full-Text Search for their users.

An intermediary approach could be creating index tables and tokenizing strings. This would allow even to order results based on relevance. (There is at least one paid WordPress plugin that does that)

Considering all these options, our current approach was to filter the SQL query built by the WordPress WP_Query object and include all the joins and were needed to search also in metadata and taxonomies values. This approach is the same as the "[Search Everything](https://wordpress.org/plugins/search-everything/)" plugin.

This approach might slow down search queries, especially the open keyword search input.

If you want to disable this change to the default WordPress behavior you can do this by adding the following line to you `wp-config.php`. You should do this if you are going to use another plugin for this purpose to avoid conflicts.

```
define('TAINACAN_DISABLE_DEFAULT_SEARCH_ENGINE', true);
```
Our efforts right now are to improve the compatibility with [ElasticPress](https://wordpress.org/plugins/elasticpress/) plugin. Its already fully functional since [version 0.9](https://tainacan.org/blog/2019/05/20/tainacan-beta-0-9-elastic-search-new-gutenberg-block-and-importers/)
*note: supported version elasticsearch 6.1.0+*

Our understanding is that, if a repository gets too big, it might need a more robust infrastructure and Elastic Search is our call.

However, since we made sure to build things in the "WordPress way", and since Tainacan search uses the native `WP_Query` class to make it queries, any plugin that filters its behavior might work with Tainacan. So feel free to try other search plugins for WordPress and please let us know how well they work!
