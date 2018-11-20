# Faceted Search 

Tainacan implements a faceted search for your collections.

Filters are displayed in the left side of the screen and allow users to filter the results.

By default, tainacan will filter the available values in each facet depending on the current filters selected. It means it will only display values that will bring at least one item in the results. Everytime the user selects on value from one filter, all the other values from all other filters are reduced. This helps users to refine their filters.

If you want to disable this behavior, and always display all possible values for a filter, even if there is no items that will meet the criteria, add this to you `wp-config.php`:

```
define('TAINACAN_FACETS_DISABLE_FILTER_ITEMS', true);
```

Tainacan will also add the number of items found for each value of each filter. This, when used without a Elastic Search* integration, might have performance issues. So if you want to remove the items count from the filters, add this to your `wp-config.php`:

```
define('TAINACAN_FACETS_DISABLE_COUNT_ITEMS', true);
```

\* Note: Elastic Search integration for this feature is still under development