# Search engine

In addition to the faceted search, which allows users to filter items by specific metadata, Tainacan also offers a free textual search.

By default, when using this option to search, WordPress searches only inside the Title (post_title) and Description (post_content). This, of course, is very limited, and this article presents and discusses the approach Tainacan will take to face this issue.

There is'nt one silver bullet to solve this problem. In some cases, perhaps for small repositories, a simple change in the way WordPress queries for posts, including relation to metadata and taxonomies, can give users the results they were looking for. In other cases, repository managers may want to use sophisticated solutions such as Elastic Search or Solr to enable Full Text Search for their users.

An intermediary approach could be creating index tables and tokenizing strings. This would allow even to order results based on relevance.

Considering all these options, our current approach is not to touch in the way WordPress handles the search, and let it be overtaken by plugins.

Eventually we will develop our own search engine plugins, to replace the limited native WordPress approach, but for now we are investigating existing plugins that could work well with Tainacan. Since we made sure to build things in the "WordPress way", and since Tainacan search uses the native `WP_Query` class to make it queries, any plugin that filters its behavior might work with Tainacan.

We are only starting this investigation, and we will keep this page updated with our findings. This is not (yet) a list of recommendation.

* [Search Everything](https://wordpress.org/plugins/search-everything/): Expands the native WordPress search to also search in taxonomies and metadata. It does so by joining tables in `WP_Query` and therefore might have performance issues for large repositories.

* [ElasticPress](https://wordpress.org/plugins/elasticpress/): integrates WordPress with an Elastic Search server. We are starting to test Tainacan with this plugin.