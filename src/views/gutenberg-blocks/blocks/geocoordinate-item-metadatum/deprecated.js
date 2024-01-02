export default [
    {
        "attributes": {
            "content": {
                "type": "array",
                "source": "query",
                "selector": "div"
            },
            "dataSource": {
                "type": "string",
                "default": "selection"
            },
            "templateMode": {
                "type": "boolean",
                "default": false
            },
            "collectionId": {
                "type": "integer"
            },
            "itemId": {
                "type": "integer"
            },
            "metadatumId": {
                "type": "integer"
            },
            "isModalOpen": {
                "type": "boolean",
                "default": false
            }
        }
    }
];