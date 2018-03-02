# Understanding Tainacan

The typical workflow when you create a Digital Repository with Tainacan is:

* Create a collection
* Configure which fields the item in this collections will have
* Configure the collection
* Configure which filters will be used when browsing the collection
* Upload items to the collection or import them from an external source

## Collections

A collection is a group of items, that share the same set of fields. Every item uploaded to your digital repository will be part of a collection - and only one collection. For instance, you could have a "paintings" collections, with fields such as Title, Author, Country, Tecnique, etc and another collection for "films", with Title, Director, Country and Genre.

For each collection you can set a different set of fields and they can share common categories, which means you could browse for items in a specific Country, and get both paintings and films in your results. 

Collections can also have child collections, which will inherit parent's collection fields and can add their own set of additional information.

## Items

Items are the actual content of yout repository. The painting, the film, the book and so on. They belong to a collection and have all the fields configured in the collection it belongs to.

In WordPress language, each item is a post and its post type represents its collection.

## Fields

Every collection have a set of fields. They are the description of the items of this collection. 

Each field has a set of settings. Is it required? Is it supposed to be unique (an ID number for example)? Does it accept multiple values? What is it Field Type? (TODO: see the complete list of field attributes).

You may have repository-level fields, that will be inherited by all collections of your repository. In the same way, collections inherit fields from their parent collection.

(Note: you can import/export presets of field)

## Field Types

Field types are the objects that represent the types of field that can be used. Examples of Field Types are "Text", "Long text", "Date", "Relationship with another item", etc (TODO: see full list).

Each field type object have its own settings and web component that will be used to render the interface. 

Field Types can be created via plugins and extend the default set of types shipped with Tainacan.

## Filters

For every collection, you may choose which fields will be used to filter the results in a faceted search interface. These are the filters.

Filters give the ability to the user to filter items in a collection using a Filter Type.

## Filter Types

Filter types are the different types of interfaces to filter items in a collections based on one specific Field. Examples of Filter Types are "input text", "datepicker", "date range picker", "number range slider", "list of checkboxes", etc.

Each Filter Type object have its own settings and web component that will be used to render the interface.

Filter Types can be created via plugins and extend the default set of types shipped with Tainacan. 

## Categories

Categories (or Taxonomies) can be created and used to classify items. Typical Taxonomies are Genre, Country, etc.

They are the same WordPress Taxonomies you already know, and they can be shared among many collections.