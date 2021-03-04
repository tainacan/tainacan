// TAINACAN MEDIA GALLERY --------------------------------------------------------
//
// Counts on some markup to make a list of media link be displayed
// as a carousel with a lightbox. It can be used in two modes:

/*

-- MODE 1 ---- Carousel of thumbnails only ----------------------------------------

<div class="swiper-container-thumbs swiper-container">
  <ul class="swiper-wrapper">
    <li class="swiper-slide">
      <a href="link-to-full-image-or-file">
        <img href="link-to-thumbnail" alt..>
        <span class="swiper-slide-name>File name</span>
      </a>
    </li>
  </ul>
</div>

new TainacanMediaGallery(.swiper-container-thumbs, null, {...});


-- MODE 2 ---- Carousel of thumbnails with main slider ----------------------------

<div class="swiper-container-main swiper-container">
  <ul class="swiper-wrapper">
    <li class="swiper-slide">
      <a href="link-to-full-image-or-file">
        <img href="link-to-medium-or-large" alt..>
        <span class="swiper-slide-name>File name</span>
        
      </a>
    </li>
  </ul>
</div>
<div class="swiper-container-thumbs swiper-container">
  <ul class="swiper-wrapper">
    <li class="swiper-slide">
      <img href="link-to-thumbnail" alt..>
      <span class="swiper-slide-name>File name</span>
    </li>
  </ul>
</div>

new TainacanMediaGallery(.swiper-container-thumbs, .swiper-container-main, {...});

*/

class TainacanMediaGallery {

    /**
     * constructor initializes the instance
     * @param  {String}  thumbnailsGallerySelector      html element to be queried containing the thumbnails list
     * @param  {String}  mainGallerySelector            html element to be queried containing the main list
     * @param  {Object}  options                        several options to be tweaked
     * @param  {Boolean} options.autoPlay               sets swiper to autoplay
     * @param  {Number}  options.autoPlayDelay          sets swiper to autoplay dealy in milisecs
     * @param  {Boolean} options.showCarouselArrows     shows swiper navigation arrows
     * @param  {Boolean} options.showCarouselDots       shows swiper naviagation dots
     * @param  {Boolean} options.showShareButton        shows share button on lightbox
     * @param  {Boolean} options.showTitle              shows file title on lightbox
     * @param  {Boolean} options.showCaption            shows file caption on lightbox
     * @param  {Boolean} options.showDescription        shows file description on 
     * 
     * @return {Object}                                 TainacanMediaGallery instance
     */
    constructor(thumbnailsGallerySelector, mainGallerySelector, options) {
        this.thumbnailsGallerySelector = thumbnailsGallerySelector;
        this.mainGallerySelector = mainGallerySelector;
        this.thumbsSwiper = null;
        this.mainSwiper = null;
        this.options = options;
    
        this.initializeSwiper();
        
        if (this.mainGallerySelector)
            this.initPhotoSwipeFromDOM(this.mainGallerySelector + " .swiper-wrapper");
        else if (this.thumbnailsGallerySelector)
            this.initPhotoSwipeFromDOM(this.thumbnailsGallerySelector + " .swiper-wrapper");
    }
  
    /* Initializes Swiper JS instances of carousels */
    initializeSwiper() {
  
        let autoPlay = false;
    
        if (this.options.autoPlay) {
            autoPlay = {
            delay: this.options.autoPlayDelay ? this.options.autoPlayDelay : 3000
            };
        }
        
        if (this.thumbnailsGallerySelector) {
    
            const thumbnailSwiperOptions = {
            spaceBetween: 16,
            slidesPerView: 'auto',
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            autoplay: autoPlay,
            centerInsufficientSlides: true
            };
            this.thumbsSwiper = new Swiper(this.thumbnailsGallerySelector, thumbnailSwiperOptions);
        }
    
        if (this.mainGallerySelector) {
    
            let mainSwiperOptions = {
            slidesPerView: 1,
            slidesPerGroup: 1,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            autoplay: autoPlay,
            };
            if (this.thumbsSwiper) {
            mainSwiperOptions.thumbs = {
                swiper: this.thumbsSwiper,
                autoScrollOffset: 1
            }
            }
            this.mainSwiper = new Swiper(this.mainGallerySelector, mainSwiperOptions);
    
        }
    }
  
    initPhotoSwipeFromDOM (gallerySelector) {
        // loop through all gallery elements and bind events
        var galleryElements = document.querySelectorAll(gallerySelector);
        
        for (var i = 0, l = galleryElements.length; i < l; i++) {
            galleryElements[i].setAttribute("data-pswp-uid", i + 1);
            galleryElements[i].onclick = (event) => this.onThumbnailsClick(event, this);
        }
        
        // Parse URL and open gallery if it contains #&pid=3&gid=1
        var hashData = this.photoswipeParseHash();
        
        if (hashData.pid && hashData.gid) {
            this.openPhotoSwipe(hashData.pid, galleryElements[hashData.gid - 1], true, true);
        }
  
    }
  
    // parse slide data (url, title, size ...) from DOM elements
    // (children of gallerySelector)
    parseThumbnailElements(el) {
        var thumbElements = el.childNodes,
            numNodes = thumbElements.length,
            items = [],
            figureEl,
            linkEl,
            imgWidth,
            imgHeight,
            item;
    
        for (var i = 0; i < numNodes; i++) {
            figureEl = thumbElements[i]; // <figure> element
    
            // include only element nodes
            if (figureEl.nodeType !== 1) {
            continue;
            }
            
            linkEl = figureEl.children[0]; // <a> element
            
            if (linkEl.classList.contains('attachment-without-image')) {
            item = {
                html: '<div class="attachment-without-image"><iframe id="tainacan-attachment-iframe" src="' + linkEl.href +  '"></iframe></div>'
            }
            } else {
            imgWidth = linkEl.children[0] && linkEl.children[0].attributes.getNamedItem('width') ? linkEl.children[0].attributes.getNamedItem('width').value : 140;
            imgHeight = linkEl.children[0] && linkEl.children[0].attributes.getNamedItem('height') ? linkEl.children[0].attributes.getNamedItem('height').value : 140;
            
            // create slide object
            item = {
                src: linkEl.getAttribute("href"),
                w: parseInt(imgWidth, 10),
                h: parseInt(imgHeight, 10)
            };
            
            if (linkEl.children[1] && linkEl.children[1].classList.contains('swiper-slide-name')) {
                item.title = linkEl.children[1].innerText;
            }
            }
    
            item.el = figureEl; // save link to element for getThumbBoundsFn
            items.push(item);
        }
    
        return items;
    };
  
    openPhotoSwipe(
      index,
      galleryElement,
      disableAnimation,
      fromURL
    ) {
        var pswpElement = document.querySelectorAll(".pswp")[0],
            gallery,
            options,
            items;
    
        items = this.parseThumbnailElements(galleryElement);
    
        // Photoswipe options
        // https://photoswipe.com/documentation/options.html //
        options = {
            showHideOpacity: false,
            loop: false,
            // Buttons/elements
            closeEl: true,
            captionEl: true,
            fullscreenEl: true,
            zoomEl: true,
            shareEl: this.options.showShareButton ? this.options.showShareButton : false,
            counterEl: true,
            arrowEl: true,
            preloaderEl: true,
            bgOpacity: 0.85,
            // define gallery index (for URL)
            galleryUID: galleryElement.getAttribute("data-pswp-uid"),
            getThumbBoundsFn: function(index) {
            // See Options -> getThumbBoundsFn section of documentation for more info
            var thumbnail = items[index].el,
                pageYScroll =
                window.pageYOffset || document.documentElement.scrollTop,
                rect = thumbnail.getBoundingClientRect();
    
            return { x: rect.left, y: rect.top + pageYScroll, w: rect.width };
            }
        };
    
        // PhotoSwipe opened from URL
        if (fromURL) {
            if (options.galleryPIDs) {
            // parse real index when custom PIDs are used
            // http://photoswipe.com/documentation/faq.html#custom-pid-in-url
            for (var j = 0; j < items.length; j++) {
                if (items[j].pid == index) {
                options.index = j;
                break;
                }
            }
            } else {
            // in URL indexes start from 1
            options.index = parseInt(index, 10) - 1;
            }
        } else {
            options.index = parseInt(index, 10);
        }
    
        // exit if index not found
        if (isNaN(options.index)) {
            return;
        }
    
        if (disableAnimation) {
            options.showAnimationDuration = 0;
        }
    
        // Pass data to PhotoSwipe and initialize it
        gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, items, options);
        gallery.init();
    
        /* Updates PhotoSwiper instance  from Swiper */
        var swiperInstance = this.mainSwiper ? this.mainSwiper : this.thumbsSwiper;
    
        gallery.listen("unbindEvents", function() {
            // This is index of current photoswipe slide
            var getCurrentIndex = gallery.getCurrentIndex();
            // Update position of the slider
            swiperInstance.slideTo(getCurrentIndex, 0, false);
            // Start swiper autoplay (on close - if swiper autoplay is true)
            swiperInstance.autoplay.start();
        });
    
        // Swiper autoplay stop when image zoom */
        gallery.listen('initialZoomIn', function() {
            if( swiperInstance.autoplay.running){
                swiperInstance.autoplay.stop();
            }
        });
    };
  
    // triggers when user clicks on thumbnail
    onThumbnailsClick(e, self) {
        e = e || window.event;
        e.preventDefault ? e.preventDefault() : (e.returnValue = false);
    
        var eTarget = e.target || e.srcElement;
    
        // find root element of slide
        var closest = function closest(el, fn) {
            return el && (fn(el) ? el : closest(el.parentNode, fn));
        };
    
        var clickedListItem = closest(eTarget, function(el) {
            return el.tagName && el.tagName.toUpperCase() === "LI";
        });
        
        if (!clickedListItem) {
            return;
        }
    
        // find index of clicked item by looping through all child nodes
        // alternatively, you may define index via data- attribute
        var clickedGallery = clickedListItem.parentNode,
            childNodes = clickedListItem.parentNode.childNodes,
            numChildNodes = childNodes.length,
            nodeIndex = 0,
            index;
    
        for (var i = 0; i < numChildNodes; i++) {
            if (childNodes[i].nodeType !== 1) {
            continue;
            }
    
            if (childNodes[i] === clickedListItem) {
            index = nodeIndex;
            break;
            }
            nodeIndex++;
        }
    
        if (index >= 0) {
            // open PhotoSwipe if valid index found
            self.openPhotoSwipe(index, clickedGallery);
        }
        return false;
    }
  
    // parse picture index and gallery index from URL (#&pid=1&gid=2)
    photoswipeParseHash() {
        var hash = window.location.hash.substring(1),
            params = {};
    
        if (hash.length < 5) {
            return params;
        }
    
        var vars = hash.split("&");
        for (var i = 0; i < vars.length; i++) {
            if (!vars[i]) {
            continue;
            }
            var pair = vars[i].split("=");
            if (pair.length < 2) {
            continue;
            }
            params[pair[0]] = pair[1];
        }
    
        if (params.gid) {
            params.gid = parseInt(params.gid, 10);
        }
    
        return params;
    }
}
  
new TainacanMediaGallery(
    '.tainacan-media-component__swiper-thumbs',
    '.tainacan-media-component__swiper-main',
    { autoPlay: true, autoPlayDelay: 7000 }
);