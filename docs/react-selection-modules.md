# React Selection Modules

While most of Tainacan's frontend is written in [Vue](https://vuejs.org/), there are some components developed in [React](https://react.dev/) to be used in the Gutenberg blocks. Part of these components are exposed to extenders in the form of React modules that can be imported and reused, particularly parts that can be useful if you want to offer to users a way to pick one or more items from a variety of possible contexts.

These are the currently available ones:

- `tainacan_multiple_item_selection_modal`: A modal for performing the selection of multiple items, based either on a manual selection or on a search query. The user can interact with available filters and advanced search to configure the desired result.
- `tainacan_single_item_selection_modal`: A modal for performing a single item selection. The user can interact with available filters and advanced search to find the desired item.
- `tainacan_single_item_metadatum_selection_modal`: A modal for performing a single metadatum value selection.
- `tainacan_single_item_metadata_section_selection_modal`: A modal for performing a single metadata section value selection.

## Why Use Them?

Building selection flows is a relatively easy task if you're familiar with our [REST API](https://redocly.github.io/redoc/?url=https://github.com/tainacan/tainacan-wiki/raw/master/dev/openapi.json). However, offering the user a powerful UI do select items or metadata can be challenging if you have a big range of metadata and items to filter. Our modal components reuse the Admin Faceted Search UI, which means you'll have filters, facets, pagination and sorting options ready for you. This is big win because it will take out the burden of caring about pagination and other performance challenges.

> [!WARNING]
> Remember that collections can have thousand of items with thousand of metadata so displaying all of them in a giant select component is never an option.

## How It Works

As an example we'll be demonstrating the usage of the `tainacan_multiple_item_selection_modal`, since it's the most complex one. Users will be familiar with this component if they ever used the [Items List](../blocks-items.md#lista-de-itens-da-coleção) or the [Carousel Items List](../blocks-items.md#carrossel-de-itens) Gutenberg blocks.

## Setup

### 1. Enqueue the Module Script

First, you need to enqueue the React selection module script in your plugin:

```php
function your_plugin_admin_enqueue_scripts() {
    // Enqueue the React selection module, already registered by Tainacan
    wp_enqueue_script('tainacan-multiple-item-selection-modal');
    
    // Your other scripts...
    wp_enqueue_script(
        'your-plugin-script',
        plugins_url('build/index.js', __DIR__),
        array('tainacan-multiple-item-selection-modal'), // Include as dependency
        '1.0.0',
        true
    );
}
add_action( 'admin_enqueue_scripts', 'your_plugin_admin_enqueue_scripts');
```

### 2. Configure Webpack Externals

In your `webpack.config.js`, you need to configure the external dependency so webpack knows how to handle the module:

```javascript
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
    ...defaultConfig,
    externals: {
        ...defaultConfig.externals,
        'tainacan-multiple-item-selection-modal': 'TainacanModules.tainacan_multiple_item_selection_modal'
    }
};
```

## Usage Example

The `tainacan_multiple_item_selection_modal` provides two load strategies:

1. **Manual Selection**: Users can manually select specific items from a collection
2. **Search Query**: Users can configure a search query that will dynamically load items

To demonstrate, well be creating a React component that creates a "Group" of items. Notice we're using WordPress components, which in case you're not familiar, take a read [on this article](https://developer.wordpress.org/news/2024/03/how-to-use-wordpress-react-components-for-plugin-pages/).

Here's a complete example of how to implement it:

```jsx
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { Button, Flex, FlexItem } from '@wordpress/components';

import TainacanMultipleItemSelectionModal from 'tainacan-multiple-item-selection-modal';

const ItemSelectionToolbar = ({ onCreateGroup, isLoading }) => {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [collectionId, setCollectionId] = useState('');
    const [loadStrategy, setLoadStrategy] = useState('');
    const [searchURL, setSearchURL] = useState('');
    const [selectedItems, setSelectedItems] = useState([]);

    const openModal = (strategy) => {
        setLoadStrategy(strategy);
        setIsModalOpen(true);
    };

    return (
        <Flex justify="center">
            <FlexItem>
                <Button 
                    isPrimary 
                    onClick={() => openModal('selection')} 
                    disabled={isLoading}
                >
                    {__('Manual selection group', 'tainacan')}
                </Button>
            </FlexItem>
            <FlexItem>
                <Button 
                    isPrimary 
                    onClick={() => openModal('search')} 
                    disabled={isLoading}
                >
                    {__('Search query group', 'tainacan')}
                </Button>
            </FlexItem>
            
            {isModalOpen && (
                <TainacanMultipleItemSelectionModal
                    loadStrategy={loadStrategy}
                    existingCollectionId={collectionId}
                    existingSearchURL={loadStrategy === 'search' ? searchURL : false}
                    onSelectCollection={(selectedCollectionId) => {
                        if (collectionId !== selectedCollectionId) {
                            setSelectedItems([]);
                        }
                        setCollectionId(selectedCollectionId);
                    }}
                    onApplySearchURL={(aSearchURL) => {
                        setSearchURL(aSearchURL);
                        setLoadStrategy('search');
                        setIsModalOpen(false);
                        onCreateGroup({ 
                            name: 'New group', 
                            use_query: aSearchURL 
                        });
                    }}
                    onApplySelectedItems={(aSelectionOfItems) => {
                        setSelectedItems(selectedItems.concat(aSelectionOfItems));
                        setLoadStrategy('selection');
                        setIsModalOpen(false);
                        onCreateGroup({ 
                            name: 'New group', 
                            items_ids: aSelectionOfItems 
                        });
                    }}
                    onCancelSelection={() => setIsModalOpen(false)}
                />
            )}
        </Flex>
    );
};

export default ItemSelectionToolbar;
```

## Understanding the TainacanMultipleItemSelectionModal

### The Component Props

| Prop | Type | Description |
|------|------|-------------|
| `loadStrategy` | `string` | Either `'selection'` or `'search'` to determine the modal behavior |
| `existingCollectionId` | `string\|number` | ID of the collection to load items from |
| `existingSearchURL` | `string\|false` | Existing search URL if using search strategy |
| `onSelectCollection` | `function` | Callback when a collection is selected |
| `onApplySearchURL` | `function` | Callback when search query is applied |
| `onApplySelectedItems` | `function` | Callback when items are manually selected |
| `onCancelSelection` | `function` | Callback when selection is cancelled |

### The Modal Behavior

#### Collection Selection Phase

1. **Initial State**: Modal shows a list of available collections
2. **Search**: Users can search for collections by name
3. **Ordering**: Collections can be ordered by date or name
4. **Pagination**: Load more collections as needed
5. **Selection**: User selects a collection to proceed

#### Items Phase

The Items phase brings the Tainacan Admin UI embedded in an Iframe with [some special URL](#available-selection-mode-parameters) params set. This hides the entire Admin UI, making things cleaner and loading only the necessary content to filter the desired items. One important consequence of this is that users will only see items that they are authorized to, as well as metadata and filters.

##### Manual Selection Strategy
- Shows an `<iframe>` with the collection's items in selection mode
- Users can manually select/deselect items, even across pages
- Filters and search are available within the iframe
- Selected items are returned via `onApplySelectedItems`

##### Search Query Strategy
- Shows an `<iframe>` with the collection's items in search mode
- Users can configure filters, search terms, and sorting
- The final search URL is returned via `onApplySearchURL`
- This URL can be used to dynamically load items later

> [!NOTE]
> In the Search Selection mode, the amount of items loaded is the default used in the Admin page (usually 12). To increase this number, the user can change the "Items per page" in the bottom of the items list, but they will be limited by a set of options. As the ideia is that this mode offers dynamic results, you can implement yourself, outside of the selection flow, an option to override the `perpage` param, as it is done by the Gutenberg blocks.

### Advanced Usage

#### Conditional Collection Loading

You can pre-select a collection and skip the collection selection phase:

```jsx
<TainacanMultipleItemSelectionModal
    loadStrategy="selection"
    existingCollectionId="123" // Pre-selected collection
    onApplySelectedItems={handleItems}
    onCancelSelection={handleCancel}
/>
```

#### Handling Search URLs

When using the search strategy, the returned URL contains all the search parameters:

```jsx
const handleSearchURL = (searchURL) => {
    // searchURL will contain filters, search terms, sorting, etc.
    // Example: /collections/123/items/?search=keyword&orderby=title&order=asc
    
    // Store this URL to recreate the same search later
    setStoredSearchURL(searchURL);
    
    // Or parse it to extract specific parameters
    const url = new URL(searchURL);
    const searchTerm = url.searchParams.get('search');
    const orderBy = url.searchParams.get('orderby');
};
```

### Styling and Customization

The modal uses WordPress admin styles by default, but you can customize both the modal itself and the content inside the iframe.

#### Modal Styling

```css
/* Target the modal container */
.tainacan-multiple-item-selection-modal {
    /* Your custom styles */
}

/* Target the iframe content */
.tainacan-multiple-item-selection-modal iframe {
    border: 1px solid #ddd;
    border-radius: 4px;
}

/* Customize modal footer buttons */
.tainacan-multiple-item-selection-modal .modal-footer-area {
    background: #f9f9f9;
    padding: 15px;
}
```

#### Iframe Content Customization

When the selection modal is active, Tainacan adds specific URL parameters to identify the selection mode. You can use these to conditionally enqueue styles that will affect the content inside the iframe.

In your plugin's main class, add a method to conditionally enqueue styles:

```php
function admin_enqueue_css() {
    // Check if we're in items multiple selection mode
    if (
        isset($_GET['itemsMultipleSelectionMode']) && $_GET['itemsMultipleSelectionMode'] ||
        isset($_GET['itemsSearchSelectionMode']) && $_GET['itemsSearchSelectionMode']
    ) {
        wp_enqueue_style(
            'your-plugin-selection-mode-styles',
            plugins_url('assets/css/selection-mode.css', __DIR__),
            array(),
            '1.0.0'
        );
    }
    
    // Your other styles...
    wp_enqueue_style(
        'your-plugin-main-styles',
        plugins_url('assets/css/main.css', __DIR__),
        array(),
        '1.0.0'
    );
}
```

Then create a dedicated stylesheet for the selection mode:

```css
/* assets/css/selection-mode.css */

/* Customize the items list admin targeting only items selection scenario */
.tainacan-pages-container--iframe-mode #items-list-area {
    --tainacan-item-background-color: var(--tainacan-background-color);
    --tainacan-item-hover-background-color: var(--tainacan-gray1);
    --tainacan-item-heading-hover-background-color: var(--tainacan-gray2);
    --tainacan-item-border-radius: 4px;
}
```

#### Available Selection Mode Parameters

These are the URL parameters you can check to customize different selection modes:

- `itemsSingleSelectionMode`
- `itemsMultipleSelectionMode`
- `itemsSearchSelectionMode`

## Conclusion

Tainacan's React Selection Modules provide a powerful way to integrate item selection functionality into your plugins. By following the patterns outlined in this documentation, you can create intuitive interfaces for users to select items, configure searches, and manage dynamic content.

For more advanced usage patterns and examples, refer to the [Tainacan core codebase](https://github.com/tainacan/tainacan) and explore how these modules are used in the built-in Gutenberg blocks.


