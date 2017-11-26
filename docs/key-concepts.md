# Understanding Tainacan

The typical workflow when you create a Digital Repository with Tainacan is:

* Create a collection
* Configure which metadata (fields) item in this collections will have
* Configure the collection
* Configure which filters will be used when browsing the collection
* Upload item to the collection or import them from a source

## Collections

A collection is a group of items, that have the same set of metadata. Every item uploaded to your digital repository will be part of a collection. For instance, you could have a "paintings" collections, with metadata such as Title, Author, Country, Tecnique, etc and another collection for "films", with Title, Director, Country and Genre.

For each collection you can set a different set of metadata and they can share common taxonomies, which means you could browse for items in a specific Country, and get both paintings and films in your results.

Collections can also have child collections, which will inherit parent's collection metadata and can add their own set of additional information.

## Items

Items are the actual content of yout repository. The painting, the film, the book and so on. They belong to a collection and have all the metadata configured in the collection.

In WordPress, each item is a post and its post type represents its collection.

## Metadata

Every collection have a set of metadata. They are the description of the items of this collection. 

Each metadata has a set of settings. Is it required? Is it supposed to be unique (an ID number for example)? Does it accept multiple values? What is it Field  Type? (see the complete list of metadata attributes).

(Note: you can import/export presets of metadata)

## Field Types

Field types are the objects that represent the types of metadata that can be used. Examples of Field Types are "Text", "Long text", "Date", "Multiple selection (checkbox)", "Relationship with another item", etc (see full list).

Each field type object have its own settings and web component that will be used to render the interface. 

Field Types can be created via plugins and extend the default set of types shipped with Tainacan.

## Filters

For every collection, you may choose which metadata will be used to filter the results in a faceted search interface. These are the filters.

Filters give the ability to the user to filter items in a collection using a Filter Type.

## Filter Types

Filter types are the different types of interface to filter items in a collections based on one specific Metadata. Examples of Filter Types are "text", "datepicker", "date range picker", "number range slider", etc.

Each Filter Type object have its own settings and web component that will be used to render the interface.

Filter Types can be created via plugins and extend the default set of types shipped with Tainacan. 

## Taxonomies

Taxonomies (or simply categories) that can be created and used to classify items. Typical Taxonomies are Genre, Country, etc.

They are the same WordPress Taxonomies you already know, and they can be shared among many collections.