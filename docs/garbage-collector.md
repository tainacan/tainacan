# Garbage Collector 

There is a first, experimental version of a WP CLI command for that:

```
wp tainacan garbage-collector 
``` 

For more info: 
```
wp help tainacan garbage-collector 
``` 

It will clean:

* items from deleted collections (and its comments and metadata)
* documents and attachments from deleted items
* deleted metadata (currently there is an interface to untrash them)
* post_meta of deleted metadata
* orphan terms (with a taxonomy that does not exist)
* tnc_bulk postmeta (temporary metadata used to group items for bulk edit operations)
