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
            "metadatumType": {
                "type": "string",
                "default": ""
            },
            "isModalOpen": {
                "type": "boolean",
                "default": false
            },
            "labelLevel": {
                "type": "number",
                "default": 3
            },
            "textAlign": {
                "type": "string"
            }
        }
    }
];