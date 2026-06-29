# Roles and Capabilities

This document describes how Tainacan roles and capabilities are implemented from version 0.15 on. For earlier versions, check the old [permissions](/dev/permissions.md) page.

It serves as a reference to developers to understand how this works and also as the basis for writing the documentation for end-users.

It also explains how WordPress handles capability checks, as it is not a very well documented topic.

## Tainacan Capabilities 

Tainacan introduces several capabilities to WordPress. They are divided in two groups:

### General Capabilities

Capabilities relative to the entire Repository

```php
'manage_tainacan' => [
    'display_name' => __('Manage Tainacan', 'tainacan'),
    'description' => __('Manage all Tainacan features and all Collections', 'tainacan')
],
'tnc_rep_edit_users' => [
    'display_name' => __('Manage Users', 'tainacan'),
    'description' => __('Manage users roles and permissions', 'tainacan')
],
'tnc_rep_edit_collections' => [
    'display_name' => __('Create Collections', 'tainacan'),
    'description' => __('Create new collections to the repository and edit its details', 'tainacan'),
    'dependencies' => [
        'upload_files'
    ]
],
'tnc_rep_delete_collections' => [
    'display_name' => __('Delete Collections', 'tainacan'),
    'description' => __('Delete their own collections from the repository', 'tainacan')
],
'tnc_rep_edit_taxonomies' => [
    'display_name' => __('Create and edit taxonomies', 'tainacan'),
    'description' => __('Create new taxonomies and edit its terms', 'tainacan')
],
'tnc_rep_edit_others_taxonomies' => [
    'display_name' => __('Edit all Taxonomies', 'tainacan'),
    'description' => __('Edit all taxonomies and terms, including taxonomies created by other users', 'tainacan')
],
'tnc_rep_delete_taxonomies' => [
    'display_name' => __('Delete Taxonomies', 'tainacan'),
    'description' => __('Delete taxonomies', 'tainacan')
],
'tnc_rep_delete_others_taxonomies' => [
    'display_name' => __('Delete all Taxonomies', 'tainacan'),
    'description' => __('Delete all taxonomies and terms, including taxonomies created by other users', 'tainacan')
],
'tnc_rep_edit_metadata' => [
    'display_name' => __('Manage Repository Metadata', 'tainacan'),
    'description' => __('Create/edit metadata in repository level', 'tainacan')
],
'tnc_rep_edit_filters' => [
    'display_name' => __('Manage Repository Filters', 'tainacan'),
    'description' => __('Create/edit filters in repository level', 'tainacan')
],
'tnc_rep_delete_metadata' => [
    'display_name' => __('Delete Repository Metadata', 'tainacan'),
    'description' => __('Delete metadata in repository level', 'tainacan')
],
'tnc_rep_delete_filters' => [
    'display_name' => __('Delete Repository Filters', 'tainacan'),
    'description' => __('Delete filters in repository level', 'tainacan')
],
'tnc_rep_read_private_collections' => [
    'display_name' => __('View private collections', 'tainacan'),
    'description' => __('Access to view and browse private collections', 'tainacan')
],
'tnc_rep_read_private_taxonomies' => [
    'display_name' => __('View private taxonomies', 'tainacan'),
    'description' => __('Access to private taxonomies information', 'tainacan')
],
'tnc_rep_read_private_metadata' => [
    'display_name' => __('View private repository metadata', 'tainacan'),
    'description' => __('Access to private metadata in repository level', 'tainacan')
],
'tnc_rep_read_private_filters' => [
    'display_name' => __('View private repository filters', 'tainacan'),
    'description' => __('Access to private filters in repository level', 'tainacan')
],
'tnc_rep_read_logs' => [
    'display_name' => __('View Logs', 'tainacan'),
    'description' => __('Access to activities logs. Note that activity logs might contain information on private collections, items and metadata.', 'tainacan')
],
```
*(list extracted from `src/classes/class-tainacan-roles.php)*

### Collection Capabilities

Each collection has its own set of capabilities, where `%d` is the collection ID.

```php
'manage_tainacan_collection_%d' => [
    'display_name' => __('Manage Collection', 'tainacan'),
    'description' => __('Manage all collection settings, items, metadata, filters, etc.', 'tainacan')
],
'tnc_col_%d_edit_users' => [
    'display_name' => __('Edit users permissions', 'tainacan'),
    'description' => __('Configure which roles and users have permission to perform actions in this collection', 'tainacan')
],
'tnc_col_%d_bulk_edit' => [
    'display_name' => __('Bulk edit items', 'tainacan'),
    'description' => __('Access to the Bulk edit items feature.', 'tainacan')
],
'tnc_col_%d_edit_metadata' => [
    'display_name' => __('Manage metadata', 'tainacan'),
    'description' => __('Create/edit metadata in this collection', 'tainacan')
],
'tnc_col_%d_edit_filters' => [
    'display_name' => __('Manage filters', 'tainacan'),
    'description' => __('Create/edit filters in this collection', 'tainacan')
],
'tnc_col_%d_delete_metadata' => [
    'display_name' => __('Delete metadata', 'tainacan'),
    'description' => __('Delete metadata in this collection', 'tainacan')
],
'tnc_col_%d_delete_filters' => [
    'display_name' => __('Delete filters', 'tainacan'),
    'description' => __('Delete filters in this collection', 'tainacan')
],
'tnc_col_%d_read_private_metadata' => [
    'display_name' => __('View private metadata', 'tainacan'),
    'description' => __('Access private metadata in this collection', 'tainacan')
],
'tnc_col_%d_read_private_filters' => [
    'display_name' => __('View private filters', 'tainacan'),
    'description' => __('Access private filters in this collection', 'tainacan')
],
'tnc_col_%d_read_private_items' => [
    'display_name' => __('View private items', 'tainacan'),
    'description' => __('Access to view private items in this collection', 'tainacan')
],
'tnc_col_%d_edit_items' => [
    'display_name' => __('Edit items', 'tainacan'),
    'description' => __('Create and edit items in this collection', 'tainacan'),
    'dependencies' => [
        'upload_files'
    ]
],
'tnc_col_%d_publish_items' => [
    'display_name' => __('Publish items', 'tainacan'),
    'description' => __('Publish items in this collection', 'tainacan'),
    'dependencies' => [
        'upload_files'
    ]
],
'tnc_col_%d_edit_others_items' => [
    'display_name' => __('Edit others items', 'tainacan'),
    'description' => __('Edit items created by other users in this collection', 'tainacan'),
    'dependencies' => [
        'upload_files'
    ]
],
'tnc_col_%d_edit_published_items' => [
    'display_name' => __('Edit published items', 'tainacan'),
    'description' => __('Edit items in this collection after they are published', 'tainacan'),
    'dependencies' => [
        'upload_files'
    ]
],
'tnc_col_%d_delete_items' => [
    'display_name' => __('Delete items', 'tainacan'),
    'description' => __('Delete items in this collection', 'tainacan'),
],
'tnc_col_%d_delete_others_items' => [
    'display_name' => __('Delete others items', 'tainacan'),
    'description' => __('Delete items created by other users in this collection', 'tainacan'),
],
'tnc_col_%d_delete_published_items' => [
    'display_name' => __('Delete published items', 'tainacan'),
    'description' => __('Delete items in this collection after they are published', 'tainacan'),
],

```
*(list extracted from `src/classes/class-tainacan-roles.php)*

### Super capabilities

There a few capabilities that have super powers and override others capabilities:

* `manage_tainacan` -> If this capability is present, check for any capability starting with `tnc_` will return `true`. This includes repository and collection capabilities. A user or role with this capability is considered a "Tainacan administrator"
* `manage_tainacan_collection_%d` -> If this capability is present for a collection, check for any capability starting with `tnc_col_%s` will return `true`. Where `%d` is the collection ID. A user or role with this capability is a Collection manager and can do everything in this collection.
* `tnc_col_all_*` All collections capabilities -> For every capability starting with `tnc_col_%d` there is a relative `tnc_col_all` capability that will apply for every collection. For example, if `tnc_col_all_edit_items` is present, checking for `tnc_col_%d_edit_items` with ANY number replacing `%d` will return `true`. This is a way to assign a capability to all collections, including collections yet to be created.
* `manage_tainacan_collection_all` -> the same idea of the above. If this capability is present any check to any collection capability of any collection ID will return `true`. A user or role with this capability will be able to do everything in all collections, but might not have other repositories capabilities.
* Collection owner -> When checking for any `tnc_col_%d` capability, Tainacan will verify if the user is the owner (author) of the collection. If he/she is, the check will also return `true`, even if the user does not have the capability. In short, it is as if every user automatically was granted `manage_tainacan_collection_%d` for their collections.

Also, any user with the WordPress native `edit_users` capability will always have `tnc_rep_edit_users`.

## Default Roles

By default, WordPress **Administrators** and **Editors** are granted `manage_tainacan` capability.

Also, Tainacan creates 3 roles with the following capabilities:

```php

'tainacan-administrator' => [
    'slug' => 'tainacan-administrator',
    'display_name' => 'Tainacan Administrator',
    'caps' => [
        'manage_tainacan' => true
    ]
],
'tainacan-editor' => [
    'slug' => 'tainacan-editor',
    'display_name' => 'Tainacan Editor',
    'caps' => [
        'tnc_rep_edit_collections' => true,
        'tnc_rep_delete_collections' => true,
        'tnc_rep_edit_taxonomies' => true,
        'tnc_rep_edit_others_taxonomies' => true,
        'tnc_rep_delete_taxonomies' => true,
        'tnc_rep_delete_others_taxonomies' => true,
        'tnc_rep_edit_metadata' => true,
        'tnc_rep_edit_filters' => true,
        'tnc_rep_delete_metadata' => true,
        'tnc_rep_delete_filters' => true,
        'tnc_rep_read_private_collections' => true,
        'tnc_rep_read_private_taxonomies' => true,
        'tnc_rep_read_private_metadata' => true,
        'tnc_rep_read_private_filters' => true,
        'tnc_rep_read_logs' => true,
        'manage_tainacan_collection_all' => true
    ]
],
'tainacan-author' => [
    'slug' => 'tainacan-author',
    'display_name' => 'Tainacan Author',
    'caps' => [
        'tnc_rep_edit_collections' => true,
        'tnc_rep_edit_taxonomies' => true,
        'tnc_rep_read_private_collections' => true,
        'tnc_rep_read_private_taxonomies' => true,
        'tnc_rep_read_private_metadata' => true,
        'tnc_rep_read_private_filters' => true,
    ]
],

```

## Checking permissions

Every entity (instances of `Tainacan\Entities\Entity`) have a set of method to check for permissions:

* `can_edit([$user])` -> checks if user can edit the entity.
* `can_delete([$user])` -> checks if user can delete the entity.
* `can_read([$user])` -> checks if user can read/view the entity.
* `can_publish([$user])` -> checks if user can publish the entity.

All these methods get an optional `$user` argument. If `$user` is omitted, it will check against the current user. If `$user` is informed, it will check if the informed user can perform the action.

These methods are all intelligent and consider all Tainacan rules. For example:

* If an item is public, it will also check whether the item's collection is public or private and if the user has the proper `read_private_*` capability
* It will check whether a filter is a repository or a collection filter and check the appropriate capability to decide whether the user can view or edit the filter.

So this is the preferred way of checking for capabilities. Also because you don't have to bother knowing the capability name.

For example, if `$item` is an Item entity:

```php
// this
current_user_can( 'tnc_col_' . $item->get_collection_id() . '_edit_item', $item->get_id() );

// is the same as:
current_user_can( $item->get_capabilities()->edit_post, $item->get_id() );

// which is the same as simply
$item->can_edit();

```

Another advantage of using Entities methods is that it works with anonymous users. `current_user_can()` will always return `false` if the user is not logged in, even if you are checking if the user can read a public item. `$item->can_read()` will return `true` if the item is public and the user is not logged in.

## !!! Important notice on permission checks !!!

It is very important to notice that repository methods such as `insert` or `fetch` **WILL NOT VERIFY USER PERMISSIONS**. If you fetch items from a repository or insert items to it, Tainacan will not check and may return the private item or insert items even if a user is not logged in.

So if you are fetching, inserting, updating, creating or deleting something, **YOUR CODE MUST CHECK FOR PERMISSIONS** before performing the action.

API Endpoints check for permissions correctly.

**THERE ARE TWO EXCEPTIONS:**

If you fetch anything (item, collections, etc...) and do not inform a `post_status` in your query, WordPress will check if the current user can `read_private_post` for the post type and return only the public posts, and include or not private posts depending on user permissions.

So, in short:

* If you query for entities without informing the `post_status` you want, it will check for permissions and return only posts the current user can read. This is the proper way to return everything the user can view
* If you query for entities and inform a `post_status` it will return the posts no matter which the permissions of the current user are. So if you want to query for private posts, you must check user permission before. (API Endpoints are smart and block the request if you inform a private post_status and do not have the proper permissions)

The SECOND exception is when you fetch items without informing neither `post_status` nor a `collection`. This happens when you want to get all items from all collections in a repository view.

In these cases, Tainacan will individually check permissions for each collection and only return items that the current user can read. (considering public/private items and public items inside private collections).

Again, if you explicitly ask for one `post_status` it will return all items with this status without performing any checks.

## How this is implemented

All Tainacan entities (collections, items, metadata, filters, logs, and taxonomies), except for Terms, are stored as posts of a certain custom post types.

The set of capabilities for each post type is available via the `get_capabilities()` method of each Entity.

Some of them are explicitly declared, and some of them are automatically generated passing the `capability_type` argument to `register_post_type()` WordPress function.

For example Collections capabilities are declared in the `get_capabilities()` method, while Metadata capabilities are automatically generated using the `capability_type` attribute of the class.

If you are not familiar with capabilities for custom post types, read the [register_post_type documentation](https://developer.wordpress.org/reference/functions/register_post_type/) and [this blog post](http://justintadlock.com/archives/2010/07/10/meta-capabilities-for-custom-post-types). It's a good start.

Let's have a look at taxonomies capabilities (from `classes/entities/class-tainacan-taxonomy.php`):

```php
    public function get_capabilities() {

		return (object) [
			// meta
			'edit_post' => "tnc_rep_edit_taxonomy",
			'read_post' => "tnc_rep_read_taxonomy",
			'delete_post' => "tnc_rep_delete_taxonomy",

			// primitive
			'edit_posts' => "tnc_rep_edit_taxonomies",
			'edit_others_posts' => "tnc_rep_edit_others_taxonomies",
			'publish_posts' => "tnc_rep_edit_taxonomies",
			'read_private_posts' => "tnc_rep_read_private_taxonomies",
			'read' => "read",
			'delete_posts' => "tnc_rep_delete_taxonomies",
			'delete_private_posts' => "tnc_rep_delete_taxonomies",
			'delete_published_posts' => "tnc_rep_delete_taxonomies",
			'delete_others_posts' => "tnc_rep_delete_others_taxonomies",
			'edit_private_posts' => "tnc_rep_edit_taxonomies",
			'edit_published_posts' => "tnc_rep_edit_taxonomies",
			'create_posts' => "tnc_rep_edit_taxonomies"
		];

	}
```

Notice how many capabilities are set as the same `tnc_rep_edit_taxonomies`. This is because we are simplifying it a bit and allowing anyone with this capability to not only edit but publish and edit published taxonomies. Currently, this is not a need for Tainacan. In the future, if we find this is important, permissions may be different for each case.

Items capabilities are dynamically generated, as they include the ID of the collection the item is part of. So this is declared in the Collection entity:

```php
	function get_items_capabilities() {

		$id = $this->get_id();

		return (object) [
			// meta
			'edit_post' => "tnc_col_{$id}_edit_item",
			'read_post' => "tnc_col_{$id}_read_item",
			'delete_post' => "tnc_col_{$id}_delete_item",

			// primitive
			'edit_posts' => "tnc_col_{$id}_edit_items",
			'edit_others_posts' => "tnc_col_{$id}_edit_others_items",
			'publish_posts' => "tnc_col_{$id}_publish_items",
			'read_private_posts' => "tnc_col_{$id}_read_private_items",
			'read' => "read",
			'delete_posts' => "tnc_col_{$id}_delete_items",
			'delete_private_posts' => "tnc_col_{$id}_delete_items",
			'delete_published_posts' => "tnc_col_{$id}_delete_published_items",
			'delete_others_posts' => "tnc_col_{$id}_delete_others_items",
			'edit_private_posts' => "tnc_col_{$id}_edit_others_items",
			'edit_published_posts' => "tnc_col_{$id}_edit_published_items",
			'create_posts' => "tnc_col_{$id}_edit_items"
		];
	}
```

## How tainacan hooks into permission checks

`src/classes/class-tainacan-roles.php` holds most of the rules. 

It has 2 major hooks.

1. Hook into `user_has_cap` -> When `user_can()` or `current_user_can()` functions are called, WordPress checks if the user has the capabilities being checked. This hook modifies this check to implement the Super capabilities described above. For example, this is the place where it will check if the user can `manage_tainacan` and, if so, return true for any check for any capability starting with `tnc_`.

2. Hook into `map_meta_cap` -> This hooks into the check for meta capabilities and applies the rules to check caps on filter and metadata. It checks if filter/metadatum is repository or collection level and then maps it to the corresponding capability. For example `tnc_rep_edit_filters` if in repository level, or `tnc_col_12_edit_filters` if belonging to collection with an ID of 12.

Items repository (`src/classes/repository/class-tainacan-items.php`) also add a hook into the `map_meta_cap` filter. This hook improves the `can_read` meta capability and checks the visibility of the collection the item belongs to. So, even if an item is public, it might be inside a private collection. This hook ensures the `can_read` check considers it.


## WordPress permission check workflow

This section is to help developers to start understanding how WordPress checks permission and hopefully will help to understand the hooks we used above.

First, let's understand what a meta capability is.

### Meta Capabilities

**Meta capabilities (or metacaps)** are capabilities used to check for permission-based on a context. Nobody and no role have this capability. This capability is only used to check the context and then return the real (primitive) capabilities that need to be checked.

Example:

Lets's say you have a `$post_ID `and you want to know if current user can edit it, you will ask:

```php
current_user_can('edit_post', $post_ID);
```

Notice that when you check for a metacap, you always give one or more additional parameters. This is the context.

What WordPress will do with it is ask a few questions:

* Is the current user author of this post?
* Is this post already published?
* Is this post private?

Depending on the answer to those questions, WordPress will check for the relative primitive capabilities:

* Is the current user author of this post? check for capability `edit_posts` (note the plural) if yes, or `edit_other_posts` if no.
* Is this post already published? check for capability `edit_published_posts` if yes
* Is this post private? check for capability `edit_private_posts`  if yes

So, in short, meta capabilities are **inexistent capabilities** that are **mapped** to real **primitive** capabilities **depending on the context**.

Every custom post type have 3 metacaps:

* read_post
* edit_post
* delete_post

### The workflow

Ok, now that we understand what metacaps are we can follow the workflow:

Whenever `current_user_can()` or `user_can()` are called, WordPress end up calling [`WP_User::has_cap`](https://developer.wordpress.org/reference/classes/wp_user/has_cap/).

The first thing this method does is pass the required `$cap` that is being checked through the [`map_meta_cap`](https://developer.wordpress.org/reference/functions/map_meta_cap/) function. This function then checks if `$cap` is a meta_cap and then asks the questions considering the context passed in additional parameters. It then returns `one or more` capabilities to be checked.

When you register a new custom post type with the option `map_meta_cap` as `true` (all Tainacan post types are registered this way), WordPress will then detect that this is a metacap from a registered post type and call `map_meta_cap` again passing the generic metacap as the argument.

For example, if you registered a post type called `movies`, with the metacap `edit_post => edit_movie`, it will call `map_meta_cap` again passing `edit_post` as argument.

Once it calls `map_meta_cap` again, it will then ask all those questions: "is the user the author? is the post published?" and then, knowing that the post being checked belong to a custom post type, it will get the registered capabilities for that post type and finally return the relative primitive capabilities to `WP_User::has_cap`.

So in the case of `movies`, it could be `edit_others_movies`. In the case of our Taxonomy post type, as you saw in the declaration above, what is registered in `edit_others_posts` is `tnc_rep_edit_others_taxonomies`. 

At the end of the `map_meta_cap` function, WordPress will also pass its return through a filter called `map_meta_cap`. This allows us to add more metacaps if we want or to change the behavior of native metacaps. This is what we do for filters, metadata and items caps.

Finally, once `WP_User::has_cap` has the return of `map_meta_cap`, it will check if all the capabilities that need to be checked are present if the user array of capabilities. This goes through the `user_has_cap` filter and allows us to modify this check and return whatever we want. This is where we hook to make Super capabilities work and return true even if the user does not have the queried capability.

**Workflow summary:**

1.  `current_user_can($cap, $context)` is called. `$context` usually is the object ID

2. it calls `WP_User::has_cap()`

3. It passes `$cap` to `map_meta_cap()` function

4. It checks if `$cap` is metacap and returns an array of primitive capabilities to be checked depending on the context. 
4.1 If $cap is a custom post type meta capability, `map_meta_cap` will call itself again passing one of the post type metacaps (`read_post`, `edit_post` or `delete_post`)
4.2 In this second call, it will do the regular checks for these metacaps and return the post type's respective primitive capabilities.

5. The return of `map_meta_cap` goes through the `map_meta_cap` filter.

6. `WP_User::has_cap()` finishes the check, checking if all capabilities returned by `map_meta_cap` exist in the user list of capabilities. This check goes through the `user_has_cap` filter.
