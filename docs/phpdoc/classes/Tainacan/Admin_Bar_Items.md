# Admin_Bar_Items


Handles WordPress admin bar items for Tainacan.

Adds contextual edit links to the WordPress admin bar for Tainacan items,
collections, taxonomies, and terms when users have appropriate permissions.

***

* Full name: `\Tainacan\Admin_Bar_Items`

## Class Diagram

```mermaid
classDiagram
    direction TB
    class Admin_Bar_Items {
        -init()
        +add_admin_bar_items_styles()
        +add_admin_bar_items(admin_bar)
    }
```

## Methods

### init

Initializes the admin bar items functionality.

```php
private init(): void
```

***

### add_admin_bar_items_styles

Enqueues styles for admin bar items.

```php
public add_admin_bar_items_styles(): void
```

***

### add_admin_bar_items

Adds contextual edit links to the WordPress admin bar.

```php
public add_admin_bar_items(\WP_Admin_Bar $admin_bar): void
```

Adds edit links for Tainacan items, collections, taxonomies, and terms
when users have appropriate permissions and are viewing relevant pages.

**Parameters:**

| Parameter    | Type              | Description                     |
|--------------|-------------------|---------------------------------|
| `$admin_bar` | **\WP_Admin_Bar** | The WordPress admin bar object. |

***

## Inherited methods

### get_instance

```php
public static get_instance(): mixed
```

* This method is **static**.
***

### __construct

```php
private __construct(): mixed
```

***
