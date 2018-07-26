# Search engine

In addition to the faceted search, which allows users to filter items by specific metadata, Tainacan also offers a free textual search.

By default, when using this option to search, WordPress searches only inside the Title (post_title) and Description (post_content). This, of course, is very limited, and this article presents and discusses the approach Tainacan will take to face this issue.

There is'nt one silver bullet to solve this problem. In some cases, perhaps for small repositories, a simple change in the way WordPress queries for posts, including relation to metadata and taxonomies, can give users the results they were looking for. In other cases, repository managers may want to use sophisticated solutions such as Elastic Search or Solr to enable Full Text Search for their users.

An intermediary approach could be creating index tables and tokenizing strings. This would allow even to order results based on relevance. (There is at least one paid WordPress plugin that does that)

Considering all these options, our current approach was to filter the SQL query built by the WordPress WP_Query object and include all the joins and wheres needed to search also in metadata and taxonomies values. This approach is the same of the "Search Everything" plugin we mention below.

If you want to disable this change to the default WordPress behavior you can do this by adding the following line to you `wp-config.php`. You should do this if you are going to use another plugin for this purpose to avoid conflicts.

```
define('TAINACAN_DISABLE_DEFAULT_SEARCH_ENGINE', true);
```

Eventually we will develop our own search engine plugins, to replace this initial approach, but for now we are investigating existing plugins that could work well with Tainacan. Since we made sure to build things in the "WordPress way", and since Tainacan search uses the native `WP_Query` class to make it queries, any plugin that filters its behavior might work with Tainacan.

We are only starting this investigation, and we will keep this page updated with our findings. This is not (yet) a list of recommendation.

* [Search Everything](https://wordpress.org/plugins/search-everything/): Expands the native WordPress search to also search in taxonomies and metadata. It does so by joining tables in `WP_Query` and therefore might have performance issues for large repositories. Its core funcionality is already present in Tainacan, but it does work very well with our plugin.

* [ElasticPress](https://wordpress.org/plugins/elasticpress/): integrates WordPress with an Elastic Search server. We are starting to test Tainacan with this plugin.
