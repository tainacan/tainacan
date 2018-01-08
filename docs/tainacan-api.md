# Tainacan Application Programming Interface

### Brief Description

A REST API for Tainacan Plugin. This API uses the Wordpress REST API.

### Routes and Endpoints

#### Collections

1. Route `wp-json/tainacan/v2/collections/(?P<collection_id>[\d]+)`

    1.1. Endpoints supported:

    1.1.1 GET (Fetch a collection)
      
    1.1.2 DELETE (Delete or Trash a collection and all your dependencies)
     
     ```
     To delete pass in body of a requisition the parameter is_permanently as true.
     To only trash pass false.
     ```
2. Route `wp-json/tainacan/v2/collections`

    2.1. Endpoints supported:
    
    2.1.1 GET (Fetch all collections)
    
    2.1.2 POST (Create a collection).
    
    Example of JSON passed in body for creating a collection:
    
```javascript
  {
    "name": "string",
    "description": "string",
    "status": "publish",
    "order": "string",
    "parent": "integer",
    "slug": "string",
    "default_orderby": "string",
    "default_order": "string",
    "columns": "string",
    "default_view_mode": "string"
  }
```
#### Items

1. Route `wp-json/tainacan/v2/items/collection/(?P<collection_id>[\d]+)`

    1.1. Endpoints supported:
    
    1.1.1 GET (Fetch all items from a collection)
    
    1.1.2 POST (Create a item in a collection)
    
    Example of JSON passed in body for creating a item:
    
```javascript
  {
    "title": "string",
    "description": "string",
    "status": "publish",
  }
```
    
2. Route `wp-json/tainacan/v2/items/(?P<item_id>[\d]+)`

    2.1. Endpoints supported:
    
    2.1.1 GET (Fetch a item)
    
    2.1.2 DELETE (Delete or Trash a item and all your dependencies)
    
    ```
     To delete pass in body of a requisition the parameter is_permanently as true.
     To only trash pass false.
    ```

#### Metadata

1. Route `wp-json/tainacan/v2/metadata/collection/(?P<collection_id>[\d]+)`
    
    1.1. Endpoints supported:
    
    1.1.1 POST (Create a metadata in collection and all your items)
    
    In body of requisition pass a JSON with the attributes of metadata like:
   
```javascript
    {
       "name": "string", 
       "description": "string",
       "field_type": "string",
       "order": "string",
       "parent": "integer",
       "required": "string",
       "collection_key": "string",
       "multiple": "string",
       "cardinality": "string",
       "privacy": "string",
       "mask": "string",
       "default_value": "string",
       "field_type_options": "string",
    }
```
    
    1.1.2 GET (Fetch all collection metadata)
    
2. Route `wp-json/tainacan/v2/metadata/item/(?P<item_id>[\d]+)`

    2.1. Endpoints supported:
    
    2.1.1 POST (Set a value of item metadata)
    
    In body of requisition pass a JSON with value e and id of metadata like:
   
```javascript
    {
       "metadata_id": "integer",
       "values": "[any, type]"
    }
    
```
    
    2.1.2 GET (Fetch all item metadata, with your values)
    
#### Taxonomies

1. Route `wp-json/tainacan/v2/taxonomies`

    1.1. Endpoints supported:
    
    1.1.1. GET (Fetch all taxonomies)
    
    1.1.2. POST (Create a taxonomy)
    
    Example of JSON passed in body for creating a taxonomy:
    
```javascript
  {
    "name": "string",
    "description": "string",
    "status": "publish",
    "parent": "string",
    "slug": "string",
    "allow_insert": "string",
    "collections_ids": "array"
  }
```

2. Route `wp-json/tainacan/v2/taxonomies/(?P<taxonomy_id>[\d]+)`

    2.1. Endpoints supported:
    
    2.1.1 GET (Fetch a taxonomy)
    
    2.1.2 DELETE (Delete or trash a taxonomy)
    
```
 To delete pass in body of requisition the parameter is_permanently as true.
 To only trash pass false.
```
