export default [
    {
        "attributes": {
            "content": {
                "type": "array",
                "source": "query",
                "selector": "div"
            },
            "sectionId": {
                "type": "string",
                "default": ""
            },
            "sectionDescription": {
                "type": "string",
                "default": ""
            },
            "textAlign": {
                "type": "string"
            }
        }
    }
];