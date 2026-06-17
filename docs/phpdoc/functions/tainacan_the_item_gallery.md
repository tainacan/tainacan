# tainacan_the_item_gallery


Renders the content of the item gallery block
using Tainacan template functions that create
a Swiper.js carousel and slider, with a PhotoSwipe.js
lightbox

***

* Full name: `tainacan_the_item_gallery`
* Defined in: `classes/theme-helper/template-tags.php`

## Parameters

| Parameter | Type      | Description                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
|-----------|-----------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$args`   | **array** | {
    Optional. Array of arguments.
     @type string  $item_id						  The Item ID
	   @type string	 $blockId 						  A unique identifier for the gallery, will be generated automatically if not provided,
	   @type array 	 $layoutElements 				  Array of elements present in the gallery. Possible values are 'main' and 'carousel'
	   @type array 	 $mediaSources 					  Array of sources for the gallery. Possible values are 'document' and 'attachments'
	   @type bool 	 $hideFileNameMain 				  Hides the Main slider file name
	   @type bool 	 $hideFileCaptionMain 			  Hides the Main slider file caption
	   @type bool 	 $hideFileDescriptionMain		  Hides the Main slider file description
	   @type bool 	 $hideFileNameThumbnails 		  Hides the Thumbnails carousel file name
	   @type bool 	 $hideFileCaptionThumbnails 	  Hides the Thumbnails carousel file caption
	   @type bool 	 $hideFileDescriptionThumbnails   Hides the Thumbnails carousel file description
	   @type bool 	 $hideFileNameLightbox 			  Hides the Lightbox file name
	   @type bool 	 $hideFileCaptionLightbox 		  Hides the Lightbox file caption
	   @type bool 	 $hideFileDescriptionLightbox	  Hides the Lightbox file description
	   @type bool 	 $openLightboxOnClick 			  Enables the behaviour of opening a lightbox with zoom when clicking on the media item
   @type bool	 $showDownloadButtonMain		  Displays a download button below the Main slider
   @type bool	 $lightboxHasLightBackground      Show a light background instead of dark in the lightbox
   @type bool    $showArrowsAsSVG			      Decides if the swiper carousel arrows will be an SVG icon or font icon
   @type string  $thumbnailsSize				  Media size for the thumbnail images. Defaults to 'tainacan-medium'
   @type bool  	 $thumbsHaveFixedHeight			  If thumbs should have a fixed height and auto widht. Defaults to false.
} |

## Return Value

**void**
