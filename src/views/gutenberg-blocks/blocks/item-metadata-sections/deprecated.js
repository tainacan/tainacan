export default [
    {
        "attributes": {
            "content": {
                "type": "array",
                "source": "query",
                "selector": "div"
            },
            "templateMode": {
                "type": "boolean",
                "default": false
            },
            "isDynamic": {
                "type": "boolean",
                "default": false
            },
            "collectionId": {
                "type": "integer"
            },
            "itemId": {
                "type": "integer"
            },
            "isModalOpen": {
                "type": "boolean",
                "default": false
            },
            "isLoading": {
                "type": "boolean",
                "default": false
            },
            "metadataSectionsRequestSource": {
                "type": "object",
                "default": ""
            },
            "metadataSections": {
                "type": "array",
                "default": []
            },
            "metadataSectionsTemplate": {
                "type": "array",
                "default": []
            },
            "textAlign": {
                "type": "string"
            }
        }
    }
];