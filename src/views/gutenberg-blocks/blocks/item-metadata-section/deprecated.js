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
            "metadataSectionRequestSource": {
                "type": "object",
                "default": ""
            },
            "metadataSectionTemplate": {
                "type": "array",
                "default": []
            },
            "sectionId": {
                "type": "string",
                "default": ""
            },
            "sectionName": {
                "type": "string"
            },
            "sectionDescription": {
                "type": "string"
            },
            "sectionMetadata": {
                "type": "array",
                "default": []
            },
            "textAlign": {
                "type": "string"
            }
        }
    }
];