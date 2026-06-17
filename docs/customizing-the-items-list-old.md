# Customizing the Items List (previous to 0.16)

> [!NOTE]
> WARNING: this page was made before 0.16 release, which means it is outdated. For the latest content on how to customize the items list, please visit [this page](/dev/customizing-the-items-list).

A lot can be done for adding Tainacan support to your theme using the template hierarchy logic mentioned in the section [Custom Templates](/dev/custom-templates.md). While this might be enough you may be interested in tweaking a bit more of the appearance of the most complex page that Tainacan offers to you: **The Items List Page**. Check the example bellow, of the child theme of [TwentyTwenty](https://wordpress.org/themes/twentytwenty/ ":ignore") that implemented a basic `archive-items.php`:

![Template added to the items list page.](/_assets/images/the-vue-items-list-component-1.png)

Looks like a powerful faceted search interface, but we definitelly could make some font adjustments to match better with our parent theme. It turns out that this page works in a much different strategy, which is explained in details in [the "Vue Items List Component" section](/dev/the-vue-items-list-component.md).

> To summarize, **the Items List returned by _tainacan_the_faceted_search()_ is acually a Vue.js component**, rendered on the client side. Most of its code is shared with the admin panel version of the items list.

So, in order to customize it, we should take a look at where is the source code of each part of the list, so that if you need to change CSS for example, you can see where to look for class definitions.

## The Vue component file structure

The file tree bellow contains only the elements that are in somehow imported to the ThemeSearch bundle - other files related to the admin panel are hidden for simplicity:

```
/src/views
    ├── admin
    │   ├── components
    │   │   ├── filter-types
    │   │   │   ├── autocomplete
    │   │   │   │   ├── Autocomplete.vue
    │   │   │   │   └── class-tainacan-autocomplete.php
    │   │   │   ├── checkbox
    │   │   │   │   ├── Checkbox.vue
    │   │   │   │   └── class-tainacan-checkbox.php
    │   │   │   ├── date
    │   │   │   │   ├── class-tainacan-date.php
    │   │   │   │   └── Date.vue
    │   │   │   ├── date-interval
    │   │   │   │   ├── class-tainacan-date-interval.php
    │   │   │   │   └── DateInterval.vue
    │   │   │   ├── filter-type
    │   │   │   │   └── class-tainacan-filter-type.php
    │   │   │   ├── numeric
    │   │   │   │   ├── class-tainacan-numeric.php
    │   │   │   │   └── Numeric.vue
    │   │   │   ├── numeric-interval
    │   │   │   │   ├── class-tainacan-numeric-interval.php
    │   │   │   │   └── NumericInterval.vue
    │   │   │   ├── numeric-list-interval
    │   │   │   │   ├── class-tainacan-numeric-list-interval.php
    │   │   │   │   └── NumericListInterval.vue
    │   │   │   ├── selectbox
    │   │   │   │   ├── class-tainacan-selectbox.php
    │   │   │   │   └── Selectbox.vue
    │   │   │   ├── taginput
    │   │   │   │   ├── class-tainacan-taginput.php
    │   │   │   │   └── Taginput.vue
    │   │   │   ├── tainacan-filter-item.vue
    │   │   │   └── taxonomy
    │   │   │       ├── Checkbox.vue
    │   │   │       ├── class-tainacan-taxonomycheckbox.php
    │   │   │       ├── class-tainacan-taxonomytaginput.php
    │   │   │       └── Taginput.vue
    │   │   ├── modals
    │   │   │   ├── checkbox-radio-modal.vue
    │   │   │   └── exposers-modal.vue
    │   │   ├── other
    │   │   │   └── collection-filter.vue
    │   │   └── search
    │   │       ├── advanced-search.vue
    │   │       ├── filters-items-list.vue
    │   │       ├── filters-tags-list.vue
    │   │       └── pagination.vue
    │   ├── js
    │   │   ├── axios.js
    │   │   ├── event-bus-search.js
    │   │   ├── filter-types-mixin.js
    │   │   ├── mixins.js
    │   │   ├── store
    │   │   │   ├── modules
    │   │   │   │   ├── collection
    │   │   │   │   │   ├── actions.js
    │   │   │   │   │   ├── getters.js
    │   │   │   │   │   ├── index.js
    │   │   │   │   │   └── mutations.js
    │   │   │   │   ├── exposer
    │   │   │   │   │   ├── actions.js
    │   │   │   │   │   ├── getters.js
    │   │   │   │   │   ├── index.js
    │   │   │   │   │   └── mutations.js
    │   │   │   │   ├── filter
    │   │   │   │   │   ├── actions.js
    │   │   │   │   │   ├── getters.js
    │   │   │   │   │   ├── index.js
    │   │   │   │   │   └── mutations.js
    │   │   │   │   ├── item
    │   │   │   │   │   ├── actions.js
    │   │   │   │   │   ├── getters.js
    │   │   │   │   │   ├── index.js
    │   │   │   │   │   └── mutations.js
    │   │   │   │   ├── metadata
    │   │   │   │   │   ├── actions.js
    │   │   │   │   │   ├── getters.js
    │   │   │   │   │   ├── index.js
    │   │   │   │   │   └── mutations.js
    │   │   │   │   ├── search
    │   │   │   │   │   ├── actions.js
    │   │   │   │   │   ├── getters.js
    │   │   │   │   │   ├── index.js
    │   │   │   │   │   └── mutations.js
    │   │   │   │   └── taxonomy
    │   │   │   │       ├── actions.js
    │   │   │   │       ├── getters.js
    │   │   │   │       ├── index.js
    │   │   │   │       └── mutations.js
    │   │   │   └── store.js
    │   │   ├── utilities.js
    │   └── scss
    │       ├── _buttons.scss
    │       ├── _checkboxes.scss
    │       ├── _dropdown-and-autocomplete.scss
    │       ├── _filters-menu-modal.scss
    │       ├── _inputs.scss
    │       ├── _modals.scss
    │       ├── _notices.scss
    │       ├── _pagination.scss
    │       ├── _radios.scss
    │       ├── _repository-level-overrides.scss
    │       ├── _selects.scss
    │       ├── _switches.scss
    │       ├── _tables.scss
    │       ├── _tabs.scss
    │       ├── _tags.scss
    │       ├── _tainacan-form.scss
    │       ├── _tooltips.scss
    │       ├── _variables.scss
    │       ├── _view-mode-cards.scss
    │       ├── _view-mode-grid.scss
    │       ├── _view-mode-masonry.scss
    │       └── _view-mode-records.scss
    └── theme-search
        ├── components
        │   ├── circular-counter.vue
        │   ├── view-mode-cards.vue
        │   ├── view-mode-masonry.vue
        │   ├── view-mode-records.vue
        │   ├── view-mode-slideshow.vue
        │   └── view-mode-table.vue
        ├── js
        │   ├── theme-main.js
        │   └── theme-router.js
        ├── pages
        │   └── theme-items-page.vue
        ├── scss
        │   ├── theme-basics.sass
        │   └── _view-mode-slideshow.scss
        └── theme-search.vue
```

As you can see, there is a similar structure on both `/src/view/theme-search` and `/src/view/admin` folders, but the last one is considerably more complex. The stuff that is used by both is always inside "admin" as it is the main "module" of the plugin. Because of that, if you want to override any css class, for example, you will probably find its definition inside `/src/view/admin/scss/`. But Vue components have a particularity of allowing `scoped css` inside their code, which means you may find what you are looking for inside one of the `.vue` files across the code as well.

Nevertheless, we can mention some of the "main" classes that you may be interested on:

- `.theme-items-list`: the parent container;
  - `.filters-menu`: the sidebar container where textual search and filters list lives in;
  - `.items-list-area`: the container where the search control, the items list itself and the pagination is inside;
    - `.search-control`: the bar where sorting, displayed metadata and view mode options are available;
    - `.filter-tags-list`: a container for the filter tags list, only existing when some is applyied;
    - `.above-search-control`: a container for everything bellow the search control;
      - `.table-container>.table-wrapper`: where the items list result is rendered with different view modes;
      - `.pagination-area`: the footer bar with pagination-related stuff;

## CSS Customizations to the Vue component

So here are a few examples of customizations that you can do, now that you understand more of the Items list component:

1. **Hide or change the order of elements**. Let us suppose that you wish to tweak the _pagination_ section a bit. You don't want the "Items per Page select" to appear and the "Go to Page" select should probably be after the pagination links. A look into the Pagination [component](https://github.com/tainacan/tainacan/blob/develop/src/views/admin/components/search/pagination.vue ":ignore") and [scss](https://github.com/tainacan/tainacan/blob/develop/src/views/admin/scss/_pagination.scss ":ignore") may give us a hint on what do do:
   ```css
   /* Hide this one, please */
   .pagination-area .items-per-page:not(.go-to-page) {
     display: none;
     visibility: hidden;
   }
   /* Move this to end of .pagination-area */
   .pagination-area .go-to-page {
     order: 2;
   }
   ```
2. **Increase the font size of some element**. _Tainacan Interface_ font sizes are rather small due to its formal styling, but you can tweak that too looking for _font-size_ definitions of your elements. Here are some adjustments made to Items list Search Control (the region where the Sorting option and View Mode selection are available). We may find definitions that affect this [here](), [here]() and [here]():
   ```css
   /* This shall increase labels */
   .search-control-item .label {
     font-size: 1.5rem;
   }
   /* This will make dropdown buttons and other buttons bigger*/
   .button:not(.is-small):not(.is-medium):not(.is-large) {
     font-size: 1.5rem !important;
   }
   /* This one is for dropdown list items */
   .dropdown .dropdown-menu .dropdown-content .dropdown-item {
     font-size: 1.25rem;
   }
   /* Finally, for the icon font */
   .theme-items-list .search-control .gray-icon i::before,
   .theme-items-list .search-control .gray-icon .icon i::before {
     font-size: 1.5rem;
   }
   ```
   Of course, you need to take care of margins and paddings here too, depending on how much you are changing these values;
3. **Ajust the list container according to site margin**. In many themes, a page has a lateral margin, that keeps content inside a container of a certain `max-width`, which can leave the items list a bit...tight. The best way to handle this is to have a [Custom Template](/dev/custom-templates.md) for the items list that provides proper container classes for the list section. If you are not able to do that, you can still solve it with negative margins and a bit of work:
   ```css
   /* Replace $max-width by your container max-width. Remember that it may change according to the screen size, which can be adapted with media queries. /*
   /* The items list container */
   .theme-items-list {
     width: 100%;
     margin-left: calc(- (100vw - $max-width) / 2);
     margin-right: calc(- (100vw - $max-width) / 2);
   }
   ```
4. **Change width of filters list**. Here is a more tricky one. Because of its scroll logic, the `#filters-desktop-aside` element has a position absolute (you can check this [here]()). This means that changing its width will alson require an adjustment to `#items-list-area`, which is right aside it:
   ```css
   #filters-desktop-aside {
     min-width: 15%;
     width: 15%;
   }
   #items-list-area {
     margin-left: 15%;
   }
   ```
   We use the _id_ `#filters-desktop-aside` instead of the _class_ `.filters-menu` because that would also affect the filters modal that appear on the mobile version.

After applying some of the suggestions mentioned, our items list looks a lot better on the TwentyTwenty child theme:

![TwentyTwenty items list page with css fixes.](/_assets/images/the-vue-items-list-component-3.png)

Much better, right? :wink:
