# Tainacan Users Permissions

This page explains how permissions are handled in Tainacan. What are the users roles available and what each one of them can do.

Tainacan handles user permissions in the very same way WordPress does, so if you are used to WordPress Roles and Permissions, you wont have any trouble.

Default WordPress roles are assigned with new capabilities to work with Collections, Items and other Tainacan specific operations. Additionaly Tainacan creates new roles, relative to the core WordPress roles, that have the same Tainacan specific capabilities, but dont have acces to the rest of the administrative panel of WordPress. For example, WordPress Editor can manage everything inside Tainacan, and they can also create and publish pages, in the other hand, Tainacan Editors can't.

If you want to change permissions for specific roles or users, or even create new roles, you can allways use one of the many WordPress plugins available for that.

In short, these are the roles and their main characteristics. A detailed description can be found in the next session. 

* Subscriber: Can't really do anything inside tainacan.
* Colaborator / Tainacan Colaborator: Can create collections and items, but not to publish them.
* Author / Tainacan Author: Can create and publish collections and items, but can not edit published items nor edit other user's items.
* Editor / Tainacan Editor: Can create, publish and edit other users's collections and items.
* Administrator: Rules the world.

## Roles and permissions

Here you will find a detailed explanation of what each role can do with each part of Tainacan.

### Collections

These are the capabilities related to collection management.

**Note about Collection moderators**: Collection moderators have the same capabilities an editor has, but only in relation to the collections he or she is moderating. Even if the user is a subscriber, he will act as if he/she was an editor for that specific collection.

|                              | Admin | Editor | Author | Collaborator |
|------------------------------|-------|--------|--------|--------------|
| Edit Collections             | y     | y      | y      |              |
| Delete Collections           | y     | y      | y      |              |
| Publish Collections          | y     | y      | y      |              |
| Edit Published Collections   | y     | y      | y      |              |
| Delete Published Collections | y     | y      | y      |              |
| Edit Others Collections      | y     | y      |        |              |
| Delete Others Collections    | y     | y      |        |              |
| Read Private Collections     | y     | y      |        |              |
| Edit Private Collections     | y     | y      |        |              |
| Delete Private Collections   | y     | y      |        |              |

#### Edit Collections

> Capability name: edit_tainacan-collections
Who's got it: Everyone but subscribers and colaborators

Allows to create and edit one's own collections details. Does not allow to publish them.

#### Edit Others Collections

> Capability name: edit_others_tainacan-collections
Who's got it: Administrators, Editors and Tainacan Editors

Allows to edit other user's Collections details.

#### Edit Published Collections

> Capability name: edit_published_tainacan-collections
Who's got it: Administrators, Editors and Tainacan Editors

Allows to edit collections details after they were published.

#### Edit Private Collections

> Capability name: edit_private_tainacan-collections
Who's got it: Administrators, Editors and Tainacan Editors

Allows to edit details of collections marked as private.

#### Publish Collections

> Capability name: publish_tainacan-collections
Who's got it: Administrators, Editors, Tainacan Editors, Authors and Tainacan Authors

Allows to publish one's own collections.

#### Delete Collections

> Capability name: delete_tainacan-collections
Who's got it: Everyone but subscribers and colaborators

Allows to delete one's own collections.

#### Delete Others Collections

> Capability name: delete_others_tainacan-collections
Who's got it: Administrators, Editors and Tainacan Editors

Allows to delete other user's Collections.

#### Delete Published Collections

> Capability name: delete_published_tainacan-collections
Who's got it: Administrators, Editors and Tainacan Editors

Allows to delete collections after they were published.

#### Delete Private Collections

> Capability name: delete_private_tainacan-collections
Who's got it: Administrators, Editors and Tainacan Editors

Allows to delete collections marked as private.

#### Read Private Collections

> Capability name: read_private_tainacan-collections
Who's got it: Administrators, Editors and Tainacan Editors

Allows to view collections marked as private and its items.

### Items

These are the capabilities related to items management.

Every user in tainacan is granted a set of capabilities, for every collection in the repository, depending on his/her role.

Capabilities are independent for each collection. So a user may be editor in one collection but have no rights whatsoever in another collection.

Permissions to a specific collection may be granted to a user adding him/her as a moderator of the collection. In that case, he/she will have the same rights as an editor, but only for that specific collection.

Also, you may use a WordPress plugin to have granular control of capabilities for each user in each collection.

In the description below you will find the characteristics of all the capabilities that are applied for each collection. Each user and role have a set of all these 10 capabilities for each collection.

|                        | Admin | Editor | Author | Collaborator |
|------------------------|-------|--------|--------|--------------|
| Edit Items             | y     | y      | y      | y            |
| Delete Items           | y     | y      | y      | y            |
| Publish Items          | y     | y      | y      |              |
| Edit Published Items   | y     | y      | y      |              |
| Delete Published Items | y     | y      | y      |              |
| Edit Others Items      | y     | y      |        |              |
| Delete Others Items    | y     | y      |        |              |
| Read Private Items     | y     | y      |        |              |
| Edit Private Items     | y     | y      |        |              |
| Delete Private Items   | y     | y      |        |              |

#### Edit Items

> Capability name: edit_%collection_id%_items
Who's got it: Everyone but subscribers

Allows to create and edit one's own items. Does not allow to publish them.

#### Edit Others Items

> Capability name: edit_others_%collection_id%_items
Who's got it: Administrators, Editors and Tainacan Editors

Allows to edit other user's Items.

#### Edit Published Items

> Capability name: edit_published_%collection_id%_items
Who's got it: Administrators, Editors and Tainacan Editors

Allows to edit Items after they were published.

#### Edit Private Items

> Capability name: edit_private_%collection_id%_items
Who's got it: Administrators, Editors and Tainacan Editors

Allows to edit Items marked as private.

#### Publish Items

> Capability name: publish_%collection_id%_items
Who's got it: Administrators, Editors, Tainacan Editors, Authors and Tainacan Authors

Allows to publish one's own Items.

#### Delete Items

> Capability name: delete_%collection_id%_items
Who's got it: Everyone but subscribers

Allows to delete one's own Items.

#### Delete Others Items

> Capability name: delete_others_%collection_id%_items
Who's got it: Administrators, Editors and Tainacan Editors

Allows to delete other user's Items.

#### Delete Published Items

> Capability name: delete_published_%collection_id%_items
Who's got it: Administrators, Editors and Tainacan Editors

Allows to delete Items after they were published.

#### Delete Private Items

> Capability name: delete_private_%collection_id%_items
Who's got it: Administrators, Editors and Tainacan Editors

Allows to delete Items marked as private.

#### Read Private Items

> Capability name: read_private_%collection_id%_items
Who's got it: Administrators, Editors and Tainacan Editors

Allows to view Items marked as private and its items.


### Categories

### Fields

### Filters



