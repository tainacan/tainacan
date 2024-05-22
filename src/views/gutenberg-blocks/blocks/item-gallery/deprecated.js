export default [
    // Deprecated in version 0.20.4 to add thumbnailsSize option
    {
        "attributes": {
            "blockId": {
                "type": "string",
                "default": ""
            },
            "collectionId": {
                "type": "string",
                "default": ""
            },
            "itemId": {
                "type": "string",
                "default": ""
            },
            "isModalOpen": {
                "type": "boolean",
                "default": false
            },
            "layoutElements": {
                "type": "object",
                "default": {
                    "main": true,
                    "thumbnails": true
                }
            },
            "mediaSources": {
                "type": "object",
                "default": {
                    "document": true,
                    "attachments": true,
                    "metadata": false
                }
            },
            "hideFileNameMain": {
                "type": "boolean",
                "default": true
            },
            "hideFileCaptionMain": {
                "type": "boolean",
                "default": false
            },
            "hideFileDescriptionMain": {
                "type": "boolean",
                "default": true
            },
            "hideFileNameThumbnails": {
                "type": "boolean",
                "default": true
            },
            "hideFileCaptionThumbnails": {
                "type": "boolean",
                "default": true
            },
            "hideFileDescriptionThumbnails": {
                "type": "boolean",
                "default": true
            },
            "hideFileNameLightbox": {
                "type": "boolean",
                "default": false
            },
            "hideFileCaptionLightbox": {
                "type": "boolean",
                "default": false
            },
            "hideFileDescriptionLightbox": {
                "type": "boolean",
                "default": false
            },
            "openLightboxOnClick": {
                "type": "boolean",
                "default": true
            },
            "arrowsSize": {
                "type": "integer",
                "default": 44
            },
            "mainSliderHeight": {
                "type": "integer",
                "default": 60
            },
            "mainSliderWidth": {
                "type": "integer",
                "default": 100
            },
            "thumbnailsCarouselWidth": {
                "type": "integer",
                "default": 100
            },
            "thumbnailsCarouselItemSize": {
                "type": "integer",
                "default": 136
            },
            "showDownloadButtonMain": {
                "type": "boolean",
                "default": false
            },
            "lightboxHasLightBackground": {
                "type": "boolean",
                "default": false
            },
            "templateMode": {
                "type": "boolean",
                "default": false
            }
        }
    },
    {
        "attributes": {
            "blockId": {
                "type": "string",
                "default": ""
            },
            "content": {
                "type": "array",
                "source": "query",
                "selector": "div"
            },
            "collectionId": {
                "type": "string",
                "default": ""
            },
            "itemId": {
                "type": "string",
                "default": ""
            },
            "isModalOpen": {
                "type": "boolean",
                "default": false
            },
            "layoutElements": {
                "type": "object",
                "default": {
                    "main": true,
                    "thumbnails": true
                }
            },
            "mediaSources": {
                "type": "object",
                "default": {
                    "document": true,
                    "attachments": true,
                    "metadata": false
                }
            },
            "hideFileNameMain": {
                "type": "boolean",
                "default": true
            },
            "hideFileCaptionMain": {
                "type": "boolean",
                "default": false
            },
            "hideFileDescriptionMain": {
                "type": "boolean",
                "default": true
            },
            "hideFileNameThumbnails": {
                "type": "boolean",
                "default": true
            },
            "hideFileCaptionThumbnails": {
                "type": "boolean",
                "default": true
            },
            "hideFileDescriptionThumbnails": {
                "type": "boolean",
                "default": true
            },
            "hideFileNameLightbox": {
                "type": "boolean",
                "default": false
            },
            "hideFileCaptionLightbox": {
                "type": "boolean",
                "default": false
            },
            "hideFileDescriptionLightbox": {
                "type": "boolean",
                "default": false
            },
            "openLightboxOnClick": {
                "type": "boolean",
                "default": true
            },
            "arrowsSize": {
                "type": "integer",
                "default": 44
            },
            "mainSliderHeight": {
                "type": "integer",
                "default": 60
            },
            "mainSliderWidth": {
                "type": "integer",
                "default": 100
            },
            "thumbnailsCarouselWidth": {
                "type": "integer",
                "default": 100
            },
            "thumbnailsCarouselItemSize": {
                "type": "integer",
                "default": 136
            },
            "showDownloadButtonMain": {
                "type": "boolean",
                "default": false
            },
            "lightboxHasLightBackground": {
                "type": "boolean",
                "default": false
            },
            "templateMode": {
                "type": "boolean",
                "default": false
            }
        }
    }
];