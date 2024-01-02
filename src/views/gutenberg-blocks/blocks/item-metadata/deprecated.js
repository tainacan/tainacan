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
            "itemMetadataRequestSource": {
                "type": "object",
                "default": ""
            },
            "itemMetadata": {
                "type": "array",
                "default": []
            },
            "metadata": {
                "type": "array",
                "default": []
            },
            "itemMetadataTemplate": {
                "type": "array",
                "default": []
            },
            "sectionId": {
                "type": "string",
                "default": ""
            },
            "textAlign": {
                "type": "string"
            }
        }
    }
];