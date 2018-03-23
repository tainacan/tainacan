# Understanding Tainacan

The typical workflow when you create a Digital Repository with Tainacan is:

* Create a collection
* Choose which fields the item in this collections will have
* Choose which filters will be used when browsing the collection
* Insert items to the collection or import them from an external source

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

(Note: you can use and import/export presets of fields)

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

In WordPress language, they are Taxonomies, and they can be shared among many collections.

Each Category has a set of terms. For example, the category Genre may have terms like "drama" and "comedy".

Terms can have hierarchy, which means that when you browse for items that have a parente term (for instance, "Rock"), the results will include items that have any of the child terms (for instance, "Progressive Rock" and "Classic Rock").

Terms also have a description, may have an icon or image that represents it and may also be linked to a existing concept of an ontology. They can have their own URL in your site, with a page listing all items that are related to them, despite their collection. In that way, they behave as if they were a collection themselves.

## Item types

Item types gives you the abiity to specialize the desription of an item based on its nature. So, inside the same collection you may have items that vary in its nature and, therefore, have a different set of metadata.

For each item type you can choose a group of Fields, in the same way you do for a collection. When you create an item inside a collection, it will have all the fields chosen for that collection plus the fields related to its type.

AN item type can be anything. For example, LPs, Books and paintings are õbvious distinct item types that may have specific fields. But it could also more abstract concepts, such as "financial transactions".

## Under discussion

### Desktop

Desktop holds items that are not part of any collections yet. You might want to use it when you want to upload a bunch of items and organize them afterwards, instead of having to thinh an prepare all the collections beforehand.

Items in Desktop are not publicly visible and have only the fields configured in repository level.

### Thematic Collections

Thematic Collections is another way to organize the items in your repository. In essecen, they are a Category, that can hold items from any collections, and an item can be part of many Thematic Collections.

They work in exactly the same way as categories, the only difference is that you have another way to manage it,..

Another idea here is, intead of having a "fixed category" called thematic collections, we could just have a menu item "Categories" or "Organize by categories" that lets you browse the items by category, instead of by collections, and manipulate them.