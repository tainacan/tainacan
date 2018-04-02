# Tainacan Internal API

This page shows how the internal API works and how to create and fetch all kinds of entities in Tainacan: Collections, items, taxnomies, fields, filters, terms, item metadata and logs.

Its important that you first get familiar with the [key concepts](key-concepts.md) of tainacan. 

This page gives an overview of the API. Detailed documentation and reference on each entity can be found below:

* [Collections Reference](class-reference-collections.md)
* [Items Reference](class-reference-items.md)
* [Item Metadata Reference](class-reference-item-metadata.md)
* [Fields Reference](class-reference-fields.md)
* [Filters Reference](class-reference-filters.md)
* [Taxonomies Reference](class-reference-taxonomies.md)
* [Terms Reference](class-reference-terms.md)
* [Logs Reference](class-reference-logs.md)

## Overview

Tainacan adds a tiny layer of abstraction over WordPress to handle all its entities, but at the end of the day, everything is stored as a post of a specific post type (except terms). So for someone used to the way WordPress works, the data structure have no misteries at all.

This layer is based on a Repository pattern. Each entity Tainacan deals with have a correspondent Repository class and a correspondent Entity class.

Repositories are the classes that comunicate with the database and know where everything is stored and how to find things. It is a singleton class, so it have only one instance available to be used by any part of the application.

```PHP
$fields_repo = Tainacan\Repositories\Fields::getInstance();
```
Entities classes are the representation of the individual of each repository. 

This abstraction allows us to easily manipulate entities without worrying how to save or fetch them.

For example, Fields have many attributes, such as `name` and `required` (indicating wether this field is required or not). As mentioned before, Fields are stored as posts of a special post type called `tainacan-field`. The name of the field is, of course, the `post_title` and this `required` attribute is stored as a `post_meta`.

However, you dont need to bother about that. This pattern allows you to manipulate a Field entity and it attributes in a transparent way, such as:

```PHP
$field->get_name(); // returns the field name
$field->get_required(); // returns the required value 
$field->set_name('new name');
$field->set_required('yes');
```

Tainacan will automatically map the values of the attributes to and from where they are stored.

When you want to fetch entities from the database, this abstraction layer steps aside and let you use all the power and flexibility of `WP_Query`, which you know and love. For example:

```PHP
Repositories\Fields::getInstance()->fetch('s=test');
```

The `fetch` method from the repositories accept exactly the same arguments accepted by `WP_Query` and uses it internally. In fact, you could use `WP_Query` directly if you prefer, but using the repository class gives you some advantages. You dont have to know the name of the post type, you can also fetch by some mapped attribute calling it directly, withour having to use `meta_query` (or even know wether a property is stored as a post attribute or post_meta). See more details in the Fetch section below.


## Fetching data

Every repository have a `fetch()` method to fetch data from the database. Some repositories may have other fetch methods, such as `fetch_by_collection`, please refer to their reference to find out.

### Getting one single individual

If you have the ID or the `WP_Post` object, you can get the item by initializing a new instance of the Entity class:

```PHP
$collection = new Tainacan\Entities\Collection($collection_post);
$collection = new Tainacan\Entities\Collection($collection_id);
```

This will have the same effect as calling the `fetch` method from the repository passing an integer as argument. THe repository will assume it is the collection ID.

```PHP
$collection = Tainacan\Repositories\Collections::getInstance()->fetch($collection_id);
```

### Fethcing many individuals

To fetch collections (or any other entity) based on a query search, you may call the `fetch` method from the repository and use any paramater of the `WP_Query` class.

> the only exception for this are terms, which are saved as WordPress terms and gets paramaters from the `get_terms()` function instead

Examples:

```PHP
$repo = Tainacan\Repositories\Collections::getInstance();
$items_repo = Tainacan\Repositories\Collections::getInstance();


$collections = $repo->fetch(); // get all public collections (or any private collections current user can view. It works exactly the same way WP_Query)

/**
 * fetch all items with title equal to 'test'
 */
$items = $items_repo->fetch([
	'post_title' => 'test'
]);

/**
 * fetch all items with title equal to 'test'
 * but using the mapped property name instead of the real name
 */
$items = $items_repo->fetch([
	'title' => 'test'
]);

```

Note that you can use the mapped attribute names to build your query, but it is just fine if you want to use the native WordPress names. The same can be achievied with attributes stored as post_meta:

```PHP
$repo = Tainacan\Repositories\Fields::getInstance();

$fields = $repo->fetch([
	'required' => 'yes'
]);

// is the same as

$fields = $repo->fetch([
	'meta_query' => [
		[
			'key' => 'required',
			'value' => 'yes'
		]
	]
]);
```

### Output

Fetch methods accept an attribute to choose how you want your output.

By default, it is a `WP_Query` object, which you can use to build your loop just as if you had called `WP_Query` your self.

But it also can be an array of Taincan Entities objects. This is very useful when you want to manipulate them.

## Inserting


### More about validating


## Checking for permissions