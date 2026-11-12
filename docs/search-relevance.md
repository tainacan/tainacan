# Search relevance

While a textual search is active, Relevance is the sort Tainacan uses unless the visitor has chosen another one. The ranking is the one WordPress computes for `orderby=relevance`. It compares the core title (`post_title`), the core description (`post_content`) and the excerpt. A match that exists only in other metadata stays in the tiebreak, which is creation date.

## How the query is built

WordPress applies this ranking when `orderby` is the string `relevance`. `Items::fetch()` stores every other sort as an array, with the item ID as a second key, so a request ordered by date, title or a metadatum does not receive the ranking.

`maybe_order_search_by_relevance()` in `class-tainacan-items.php` runs for a non-empty search that returns items rather than IDs and whose `orderby` is `relevance`. It passes that string through to WordPress, then replaces the creation-date suffix WordPress appends with the tiebreak below.

The REST API accepts `relevance` as an `orderby` value. The default is `date`. A client that omits `relevance` is ordered by the sort it sent.

## What the ranking compares

For a search that WordPress splits into more than one term, the order is a `CASE`, best match first:

1. The whole phrase is in the title.
2. Every term is in the title.
3. Any term is in the title.
4. The whole phrase is in the excerpt.
5. The whole phrase is in the description.
6. Anything else.

WordPress builds branches 2 and 3 only when there are two to six terms. With more than six terms, only the whole phrase is ranked, in the title, the excerpt and the description. Tainacan items usually leave the excerpt empty.

For a single term, WordPress orders by whether the title contains that term and leaves every other match in the tiebreak. A phrase search is this case: the whole query is one term, so a title that contains the phrase comes first. The description has a rank of its own when the search is split into several terms.

"Search each word separately" is what produces several terms. The relevance sort is selected for any textual search, phrase or per word. Quotes keep a group of words as one term, for both the match and the ranking. The terms are combined with OR.

Other metadata, taxonomies and the indexed document text decide which items match, through `class-tainacan-search-engine.php`. They fall into the last group of this order and then follow creation date.

## Tiebreak and direction

Inside a rank, items are ordered by creation date, in the direction of the request (`ASC` or `DESC`), and then by item ID descending. The ranks stay best match first. Ascending reverses the date order among items that share a rank.

The direction is the list's current ascending or descending setting.

## When the interface selects it

The admin item list and the faceted-search block share one items request. Both show a Relevance entry in the sort menu while the search box has a value, and both label it through the orderby helper in `utilities.js`.

`setSearchQuery()` on the search event bus selects it:

- When the search goes from empty to a value, the current sort is kept aside in memory and `orderby` becomes `relevance`. The saved preference is left as it was.
- Creation date, Title or a metadatum replaces Relevance. That choice updates the URL and the saved preference.
- Clearing the search while Relevance is still selected restores the sort that was set aside. Another sort chosen during the search stays in place.
- A link that contains both a search and a sort keeps that sort. A link that has a search and no sort is filled by the same branch that supplies a missing `orderby`, and that value is then Relevance.

The saved preference, the URL, the collection default and metadata sorts are resolved as for any other sort. Relevance is one of the values those screens send.

## Facets and autocomplete

A facet request copies the items query as `current_query`, which may include `orderby=relevance`. That query collects the IDs of items that match the current search and filters. `fetch_all_metadatum_values()` sets `fields` to `ids`, and the relevance gate skips ID queries.

The suggestions are distinct metadata values, or term names, ordered by the value (`meta_value`, or `t.name` for a taxonomy). The text typed in an autocomplete is the facet `search` parameter. It selects values that contain those characters. It is an items search only as a filter on which values exist, and the values themselves are ordered alphabetically.

## ElasticPress

When ElasticPress answers the items query, the MySQL `ORDER BY` is not used. `orderby=relevance` is sorted by `_score`.

`add_items_args()` in `class-tainacan-elastic-press.php` searches the title, the description, the excerpt, every metadatum and the taxonomies, so the score includes metadata. Title and creation date replace that score when they are the requested `orderby`.

The SQL creation-date tiebreak is not appended after `_score`. The ascending or descending direction applies to the score. A list set to ascending shows the weakest ElasticPress matches first while Relevance is selected.

## Metadata and the SQL score

The SQL order does not count how many search words matched in postmeta, and it does not weigh a short note against a long document. The metadata filter records that some value matched and stops at the first hit. A count would be computed again while sorting every match, before the page is cut down. The core title is also stored as postmeta, and `document_content_index` is a long text in the same table.

A score with field weights and term frequency is the search index. Elasticsearch provides it. The SQL path uses the title and description check WordPress runs, and leaves every other match in creation-date order.
