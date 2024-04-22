export default [
    {
        "content": {
            "type": "array",
            "source": "query",
            "selector": "div"
        },
        "sectionId": {
            "type": "string",
            "default": ""
        },
        "sectionName": {
            "type": "string",
            "default": ""
        },
        "labelLevel": {
			"type": "number",
			"default": 2
		},
        "textAlign": {
			"type": "string"
        }
    }
];