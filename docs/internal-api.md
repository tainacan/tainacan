# Tainacan Internal API

This page shows how the internal API works and how to create and fetch all kinds of entities in Tainacan: Collections, items, taxnomies, metadata, filters, terms, item metadata and logs.

Its important that you first get familiar with the [key concepts](key-concepts.md) of tainacan. 


## Overview

Tainacan adds a tiny layer of abstraction over WordPress to handle all its entities, but at the end of the day, everything is stored as a post of a specific post type (except terms). So for someone used to the way WordPress works, the data structure have no misteries at all.

This layer is based on a Repository pattern. Each entity Tainacan deals with have a correspondent Repository class and a correspondent Entity class.

Repositories are the classes that comunicate with the database and know where everything is stored and how to find things. It is a singleton class, so it have only one instance available to be used by any part of the application.

```PHP
$metadata_repo = Tainacan\Repositories\Metadata::get_instance();
```
Entities classes are the representation of the individual of each repository. 

This abstraction allows us to easily manipulate entities without worrying how to save or fetch them.

For example, Metadata have many attributes, such as `name` and `required` (indicating wether this metadatum is required or not). As mentioned before, Metadata are stored as posts of a special post type called `tainacan-metadatum`. The name of the metadatum is, of course, the `post_title` and this `required` attribute is stored as a `post_meta`.

However, you dont need to bother about that. This pattern allows you to manipulate a Field entity and it attributes in a transparent way, such as:

```PHP
$metadatum->get_name(); // returns the metadatum name
$metadatum->get_required(); // returns the required value 
$metadatum->set_name('new name');
$metadatum->set_required('yes');
```

Tainacan will automatically map the values of the attributes to and from where they are stored.

When you want to fetch entities from the database, this abstraction layer steps aside and let you use all the power and flexibility of `WP_Query`, which you know and love. For example:

```PHP
Repositories\Metadata::get_instance()->fetch('s=test');
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
$collection = Tainacan\Repositories\Collections::get_instance()->fetch($collection_id);
```

### Fethcing many individuals

To fetch collections (or any other entity) based on a query search, you may call the `fetch` method from the repository and use any paramater of the `WP_Query` class.

> the only exception for this are terms, which are saved as WordPress terms and gets paramaters from the `get_terms()` function instead

Examples:

```PHP
$repo = Tainacan\Repositories\Collections::get_instance();
$items_repo = Tainacan\Repositories\Collections::get_instance();


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
$repo = Tainacan\Repositories\Metadata::get_instance();

$metadata = $repo->fetch([
	'required' => 'yes'
]);

// is the same as

$metadata = $repo->fetch([
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

All repositories have a `insert()` method that gets an Entity as argument and save it in the database. If the entity has an ID, this method will update the entity. (yes, the same way `wp_insert_post` works)

Each repository will get as a parameter an instace of its correspondent entity. For example, Collections repository `insert()` will get an instace of `Tainacan\Entities\Collection` and return the updated entity.

However, before insertion, you must validate the entity, calling the `validate()` method every entity has. You can only insert valid entities.

So this is a typical routine for creating an entity:

```PHP
$collectionsRepo = \Tainacan\Repositories\Collections::get_instance();
$collection = new \Tainacan\Entities\Collection();
$collection->set_name('New Collection');

if ($collection->validate()) {
	$insertedCollection = $collectionsRepo->insert($collection);
	echo 'Now I have an ID! ' . $insertedCollection->get_id();
	
	// Lets update something
	$insertedCollection->set_description('new description');
	if ($insertedCollection->validate()) {
		$insertedCollection = $collectionsRepo->insert($insertedCollection);
		echo 'I still have the same ID! ' . $insertedCollection->get_id();
	} else {
		$errors = $insertedCollection->get_errors();
	}
	
} else {
	$validationErrors = $collection->get_errors();
	// Do something!
}

```
> IMPORTANT: Repositories `insert()` methods do not check for user permissions. If you call it, it will save entities to the database no matter who is logged in (or even if some is logged in). Again, this works the same way WordPress works with its internal functions. All permission checks must be done before you call the insertion methods. See "checking for permissions" section below.

The example above shows how to create and update a Collection, but it applies to every entity. They all work in the very same way.

Well, Item Metadata Entity is slightly different.

### Handling Item Metadata

`Item Metada` is a special kind of entity, because it is not an actual entity itself. Rather, it is the relationship between an Item and a Field. And this relationship has a value.

So imagine a Collection of pens has a Field called "color". This means the an item of this collection will have a relationship with this metadatum, and this relation will have a value. Red, for example.

So the Item Metadata Entity constructor gets to entities: an item and a metadatum. Lets see an example, considering I alredy have a collection with metadata and an item.

```PHP
// Considering $item is an existing Item Entity an $metadatum an existing Field Entity
$itemMetadada = new \Tainacan\Entities\ItemMetadataEntity($item, $metadatum);

$itemMetadata->set_value('Red');

if ($itemMetadata->validate()) {
	$ItemMetadataRepo = \Tainacan\Repositories\ItemMetadata::get_instance();
	$ItemMetadata = $ItemMetadataRepo->insert($ItemMetadata);
} else {
	$errors = $ItemMetadata->get_errors();
}

```

> Note: "Multiple" Metadata, which can have more than one value for the same item, work exactly the same way, with the difference that its value is an array of values, and not just one single value. 

If you want to iterate over all metadata of an item or a collection, there are 2 usefull methods you can use. Metadata repository have a `fetch_by_collection()` method that will fetch all metadata from a given collection and return them in the right order.

Also, ItemMetadata Repository `fetch()` method will return an array of ItemMetadata Entities related to a given item. 


### Handling Compound metadata

Compound metadata are a special type of metadata that support child metadata. It is a group of metadata.

The Compound metadatum itself does not have a value, only its children have. So when you are saving a new value for a child metadatum of a compound metadatum, it will behave as it was a normal metadatum.

However, when you save the value for the second metadatum of that same group, you must inform that it belong to that group. You do this by passing a `parent_meta_id` when initializing the Item Metada Entity. Note that you will only have this ID after you saved the first ItemMetadata of that group, because only then the group was created.

```PHP

$item_metadata1 = new \Tainacan\Entities\Item_Metadata_Entity($i, $child_field1);
$item_metadata1->set_value('Red');

if ($item_metadata1->validate()) {
	$item_metadata1 = $Tainacan_Item_Metadata->insert($item_metadata1);
}

$item_metadata2 = new \Tainacan\Entities\Item_Metadata_Entity($i, $child_field2, null, $item_metadata1->get_parent_meta_id());
$item_metadata2->set_value('Blue');

if ($item_metadata2->validate()) {
	$item_metadata2 = $Tainacan_Item_Metadata->insert($item_metadata2);
}


```

Now you may get the value directly from the Compound Field, and it will return an array of ItemMetadata Entities (with meta_id and parent_meta_id populated).

```PHP

$compoundItemMeta = new \Tainacan\Entities\Item_Metadata_Entity($item, $compoundField);

$compoundValue = $compoundItemMeta->get_value();

// This is an array of ItemMetadata Entities

foreach ($compoundValue as $metadatum_id => $childItemMeta) {
	var_dump( $childItemMeta instanceof \Tainacan\Entities\ItemMetadataEntity ); // true
	var_dump( $metadatum_id == $childItemMeta->get_field()->get_id() ); // true
	echo "Value for metadatum " . $childItemMeta->get_field()->get_name() " (child of " . $compoundItemMeta->get_name() . ") is:" . $childItemMeta->get_value();
	var_dump( $childItemMeta->get_field()->get_parent() == compoundItemMeta->get_field()->get_id() ); // true
	var_dump( is_int($childItemMeta->get_meta_id()) && $childItemMeta->get_parent_meta_id() ); // true. they are allways set when initialized by calling get_value() on the parent ItemMetadataEntity
}


```



### More about validating

TODO: document the validation chains

All validations validate the property with the validation declared in the get_map() method of the repository.

Validate item -> call ItemMetadata->validate() for each metadatum

Validate ItemMetadata -> call $metadatumType->validate() for the Field type of the metadatum.

Validate Field -> call validate_options() of the Field type

## Checking for permissions

Each entity type is stored as a post type and has its own set of capabilities. For example, Collections are posts of the `tainacan-collection` post type, and have associated capabilities such as `edit_tainacan-collections` and `edit_others_tainacan-collections`. 

If you are familiar with WordPress Roles and capabilities and, more specifically, with custom post types capabilities, this is very easy to understand. If you are not, its best if you first learn how WordPress handles custom post types capabilities and you will easily understand how tainacan works with it.

To see a complete list of tainacan capabilities see [Tainacan Permissions](permissions.md).

When you use WordPress custom post types, you dont need to know the exact name of the capabilities of a post type to check for them. The post type object has a property called `cap` that informs you what are the specific capabilities that the post type have for the post type actions.

For example, for a post type called `book`, that have capabilities such as `edit_books`, you could:

```PHP
if (current_user_can('edit_books')) 
	// do something
```
OR
```PHP
$book_cpt = get_post_type_object('book');
if (current_user_can( $book_cpt->cap->edit_posts )) 
	// do something
```

This makes life easier and Tainacan works exacty the same way.

```PHP

$collection = new \Tainacan\Entities\Collection(23);

var_dump( $collection->get_capabilities() ); // all capabilities of the collections post type

```

This is specially usefull when handling Items, because they are posts of dynamic created post types, and it would cost too much to find out the correct capabilties names.

Also, every entity implement 3 methods to check for the Meta Capabilities `edit_post`, `delete_post` and `read_post`, so intead of:

```PHP
current_user_can( $item->get_capabilities()->edit_post, $item->get_id() ); 
```

You can simply
```PHP
$item->can_edit(); 
```

So now you know how to check the permision when a user wants to update an item. Here is the complete code:

```PHP
$collectionsRepo = \Tainacan\Repositories\Collections::get_instance();
$collection = new \Tainacan\Entities\Collection(23);

if ($collection->can_edit()) {
	
	$collection->set_description('new description');
	
	if ($collection->validate()) {
		
		$collection = $collectionsRepo->insert($collection);
		
	} else {
		$validationErrors = $collection->get_errors();
		// Do something!
	}
} else {
	echo 'Sorry, you dont have permission to do that';
}
```

