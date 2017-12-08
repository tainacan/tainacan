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
        
#### Items

1. Route `wp-json/tainacan/v2/items/collection/(?P<collection_id>[\d]+)`

    1.1. Endpoints supported:
    
    1.1.1 GET (Fetch all items from a collection)
    
    1.1.2 POST (Create a item in a collection)
    
2. Route `wp-json/tainacan/v2/items/(?P<item_id>[\d]+)`

    2.1. Endpoints supported:
    
    2.1.1 GET (Fetch a item)
    
    2.1.2 DELETE (Delete or Trash a item and all your dependencies)
    ```
     To delete pass in body of a requisition the parameter is_permanently as true.
     To only trash pass false.
    ```
