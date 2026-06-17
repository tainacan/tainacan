# SVG_Icon


Class for getting inline svg icons based on the Tainacan Icon font and new symbols needed
The svg files have a fill of currentColor and width and height of 1em to allow external customization.

***

* Full name: `\Tainacan\Traits\SVG_Icon`

## Class Diagram

```mermaid
classDiagram
    direction TB
    class SVG_Icon {
        +get_svg_icon(icon_slug)
    }
```

## Methods

### get_svg_icon

```php
public get_svg_icon(mixed $icon_slug): string
```

**Parameters:**

| Parameter    | Type      | Description |
|--------------|-----------|-------------|
| `$icon_slug` | **mixed** |             |

**Return Value:**

icon_slug with that points to the icon file

***
