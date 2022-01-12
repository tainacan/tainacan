// TAINACAN MEDIA COMPONENT --------------------------------------------------------
//
// Counts on some HMTL markup to make a list of media links be displayed
// as a carousel with a lightbox. Check examples in the end of the file 
import PhotoSwipe from 'photoswipe/dist/photoswipe.min.js';
import PhotoSwipeUI_Default from 'photoswipe/dist/photoswipe-ui-default.min.js';
import Swiper from 'swiper';
import 'swiper/css/swiper.min.css';

const { __ } = wp.i18n;

tainacan_plugin.classes.TainacanMediaGallery = class TainacanMediaGallery {

    /**
     * Constructor initializes the instance. Options are Snake Case because they come from PHP side
     * @param  {String}  thumbs_gallery_selector          html element to be queried containing the thumbnails list
     * @param  {String}  main_gallery_selector            html element to be queried containing the main list
     * @param  {Object}  options                          several options to be tweaked
     * @param  {Object}  options.swiper_thumbs_options    object with SwiperJS options for the thumbnails list (https://swiperjs.com/swiper-api)
     * @param  {Object}  options.swiper_main_options      object with SwiperJS options for the main list
     * @param  {Boolean} options.disable_lightbox         do not open photoswipes lightbox when clicking the main gallery
     * @param  {Boolean} options.show_share_button        show share button on lightbox
     * 
     * @return {Object}                                   TainacanMediaGallery instance
     */
    constructor(thumbs_gallery_selector, main_gallery_selector, options) {
        this.thumbs_gallery_selector = thumbs_gallery_selector;
        this.main_gallery_selector = main_gallery_selector;
        this.thumbsSwiper = null;
        this.mainSwiper = null;
        this.options = options;
        this.initializeSwiper();
        
        if (!this.options.disable_lightbox) {
            if (this.main_gallery_selector)
                this.initPhotoSwipeFromDOM(this.main_gallery_selector + " .swiper-wrapper");
            else if (this.thumbs_gallery_selector)
                this.initPhotoSwipeFromDOM(this.thumbs_gallery_selector + " .swiper-wrapper");
        }
    }
  
    /* Initializes Swiper JS instances of carousels */
    initializeSwiper() {
        
        if (this.thumbs_gallery_selector) {
            let thumbsSwiperOptions = {
                spaceBetween: 12,
                slidesPerView: 'auto',
                navigation: {
                    nextEl: '.swiper-navigation-next_' + this.thumbs_gallery_selector,
                    prevEl: '.swiper-navigation-prev_' + this.thumbs_gallery_selector,
                },
                pagination: {
                    el: '.swiper-pagination_' + this.thumbs_gallery_selector
                },
                centerInsufficientSlides: true,
                watchOverflow: true,
                a11y: {
                    prevSlideMessage: __( 'Previous slide', 'tainacan'),
                    nextSlideMessage: __( 'Next slide', 'tainacan'),
                    firstSlideMessage: __('This is the first slide', 'tainacan'),
                    lastSlideMessage: __('This is the last slide', 'tainacan')
                },
            };
            thumbsSwiperOptions = {...thumbsSwiperOptions, ...this.options.swiper_thumbs_options };
            this.thumbsSwiper = new Swiper(this.thumbs_gallery_selector, thumbsSwiperOptions);
        }
    
        if (this.main_gallery_selector) {
            
            let mainSwiperOptions = {
                slidesPerView: 1,
                slidesPerGroup: 1,
                // navigation: {
                //     nextEl: '.swiper-navigation-next_' + this.main_gallery_selector,
                //     prevEl: '.swiper-navigation-prev_' + this.main_gallery_selector,
                // },
                // pagination: {
                //     el: '.swiper-pagination_' + this.main_gallery_selector
                // },
                watchOverflow: true,
                a11y: {
                    prevSlideMessage: __( 'Previous slide', 'tainacan'),
                    nextSlideMessage: __( 'Next slide', 'tainacan'),
                    firstSlideMessage: __('This is the first slide', 'tainacan'),
                    lastSlideMessage: __('This is the last slide', 'tainacan')
                },
            };
            if (this.thumbsSwiper) {
                mainSwiperOptions.thumbs = {
                    swiper: this.thumbsSwiper,
                    autoScrollOffset: 3
                }
            }
            mainSwiperOptions = {...mainSwiperOptions, ...this.options.swiper_main_options };
            this.mainSwiper = new Swiper(this.main_gallery_selector, mainSwiperOptions);
            
        }

        if (this.thumbsMain && this.mainSwiper) {
            this.mainSwiper.controller = {
                control: this.thumbsSwiper,
                by: 'slide'
            }
        }
    }
  
    initPhotoSwipeFromDOM (gallerySelector) {
        // loop through all gallery elements and bind events
        let galleryElement = document.querySelector(gallerySelector);
        
        galleryElement.setAttribute("data-pswp-uid", this.options.media_id);
        galleryElement.onclick = (event) => this.onThumbnailsClick(event, this);
        
        // Parse URL and open gallery if it contains #&pid=3&gid=1
        let hashData = this.photoswipeParseHash();
        
        if (hashData.pid && hashData.gid)
            this.openPhotoSwipe(hashData.pid, galleryElement, true, true);
    }
  
    // parse slide data (url, title, size ...) from DOM elements
    // (children of gallerySelector)
    parseThumbnailElements(el) {
        let items = [];
        const galleryElements = el.childNodes;

        // Crossbrowser safe way to traverse nodeList
        Array.prototype.forEach.call(galleryElements, (liElement) => {

            // Include only element nodes
            if (liElement.nodeType === 1) {

                let item = {};
                let fullContentElement = liElement.querySelectorAll('.media-full-content *');

                if ( !fullContentElement.length ) {
                    item = {
                        html: fullContentElement.outerHTML ? fullContentElement.outerHTML : fullContentElement
                    }
                } else {
                    if (fullContentElement[fullContentElement.length - 1].nodeName === 'IMG') {
                        fullContentElement = fullContentElement[fullContentElement.length - 1];
                        item = {
                            src: fullContentElement.src,
                            w: parseInt(fullContentElement.width),
                            h: parseInt(fullContentElement.height)
                        };
                    } else {
                        fullContentElement = fullContentElement[0];
                        item = {
                            html: fullContentElement.outerHTML ? fullContentElement.outerHTML : fullContentElement
                        }
                    }
                }

                let metadataElement = liElement.querySelector('.swiper-slide-metadata');
                if (metadataElement) {
                    const name = metadataElement.querySelector('.swiper-slide-metadata__name');
                    const caption = metadataElement.querySelector('.swiper-slide-metadata__caption');
                    const description = metadataElement.querySelector('.swiper-slide-metadata__description');

                    item.title = {
                        name,
                        caption,
                        description
                    }
                } else {
                    item.title = false;
                }
                
                item.el = liElement; // save link to element for getThumbBoundsFn
                items.push(item);
            }
        });
        
        return items;
    };
  
    openPhotoSwipe(
      index,
      galleryElement,
      disableAnimation,
      fromURL
    ) {
        let pswpElement = document.querySelectorAll(".pswp")[0],
            gallery,
            options,
            items;
        items = this.parseThumbnailElements(galleryElement);

        // Photoswipe options
        // https://photoswipe.com/documentation/options.html //
        options = {
            showHideOpacity: true,
            loop: false,
            timeToIdle: 6000,
            timeToIdleOutside: 3000,
            closeEl: true,
            captionEl: true,
            fullscreenEl: true,
            zoomEl: true,
            counterEl: true,
            arrowEl: true,
            preloaderEl: true,
            shareEl: this.options.show_share_button ? this.options.show_share_button : false,
            bgOpacity: 1,
            // define gallery index (for URL)
            galleryUID: galleryElement.getAttribute("data-pswp-uid"),
            getThumbBoundsFn: (index) => {
                let thumbnail = items[index].el,
                    pageYScroll = window.pageYOffset || document.documentElement.scrollTop,
                    rect = thumbnail.getBoundingClientRect();
        
                return { x: rect.left, y: rect.top + pageYScroll, w: rect.width };
            },
            // Function builds caption markup
            addCaptionHTMLFn: (item, captionEl, isFake) => {
                // item      - slide object
                // captionEl - caption DOM element
                // isFake    - true when content is added to fake caption container
                //             (used to get size of next or previous caption)

                captionEl.children[0].innerHTML = '';
                
                if(!item.title)
                    return false;
                    
                if (item.title.caption)
                    captionEl.children[0].innerHTML += '<span class="pswp__figure_caption">' + item.title.caption.innerHTML + '</span>';

                if (item.title.name && item.title.caption || (!item.title.name && item.title.caption && item.title.description) )
                    captionEl.children[0].innerHTML += '<br>';

                if (item.title.name)
                    captionEl.children[0].innerHTML += '<span class="pswp__name">' + item.title.name.innerHTML + '</span>';
                 
                if (item.title.description && item.title.name)
                    captionEl.children[0].innerHTML += '<br>';

                if (item.title.description)
                    captionEl.children[0].innerHTML += '<span class="pswp__description">' + item.title.description.innerHTML + '</span>';
                    
                return true;
            },

        };
    
        // PhotoSwipe opened from URL
        if (fromURL) {
            if (options.galleryPIDs) {
                // parse real index when custom PIDs are used
                // http://photoswipe.com/documentation/faq.html#custom-pid-in-url
                for (let j = 0; j < items.length; j++) {
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
        if (isNaN(options.index))
            return;
    
        if (disableAnimation)
            options.showAnimationDuration = 0;
    
        // Pass data to PhotoSwipe and initialize it
        gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, items, options);
        gallery.init();
        
        /* Updates PhotoSwiper instance from Swiper */
        let swiperInstance = this.mainSwiper ? this.mainSwiper : this.thumbsSwiper;
    
        gallery.listen("unbindEvents", () => {
            // This is index of current photoswipe slide
            let getCurrentIndex = gallery.getCurrentIndex();
            // Update position of the slider
            swiperInstance.slideTo(getCurrentIndex, 0, false);
            // Start swiper autoplay (on close - if swiper autoplay is true)
            if (swiperInstance.params && swiperInstance.params.autoplay && swiperInstance.params.autoplay.enabled)
                swiperInstance.autoplay.start();
        });
    
        // Swiper autoplay stop when image zoom */
        gallery.listen('initialZoomIn', () => {
            if (swiperInstance.params && swiperInstance.params.autoplay && swiperInstance.params.autoplay.enabled && swiperInstance.autoplay.running)
                swiperInstance.autoplay.stop();
        });

        // On destroy we make a copy of the inner content to clear it
        // and set again. This stops YouTube player, for example. 
        gallery.listen('destroy', () => { 
            let actualGalleryContainer = document.getElementsByClassName("pswp__container")[0];
            if (actualGalleryContainer) {
                let currentData = actualGalleryContainer.innerHTML;
                actualGalleryContainer.innerHTML = '';
                actualGalleryContainer.innerHTML = currentData;           
            } 
        });

    };
  
    // triggers when user clicks on thumbnail
    onThumbnailsClick(e, self) {
        e = e || window.event;
        e.preventDefault ? e.preventDefault() : (e.returnValue = false);
    
        let eTarget = e.target || e.srcElement;

        // find root element of slide
        let closest = function closest(el, fn) {
            return el && (fn(el) ? el : closest(el.parentNode, fn));
        };
        
        let clickedListItem = closest(eTarget, function(el) {
            return el.tagName && el.tagName.toUpperCase() === "LI";
        });
        
        if (!clickedListItem)
            return;
    
        // find index of clicked item by looping through all child nodes
        // alternatively, you may define index via data- attribute
        let clickedGallery = clickedListItem.parentNode,
            childNodes = clickedListItem.parentNode.childNodes,
            numChildNodes = childNodes.length,
            nodeIndex = 0,
            index;

        for (let i = 0; i < numChildNodes; i++) {
            if (childNodes[i].nodeType !== 1)
                continue;
    
            if (childNodes[i] === clickedListItem) {
                index = nodeIndex;
                break;
            }
            nodeIndex++;
        }
        
        // open PhotoSwipe if valid index found
        if (index >= 0)
            self.openPhotoSwipe(index, clickedGallery);

        return false;
    }
  
    // parse picture index and gallery index from URL (#&pid=1&gid=2)
    photoswipeParseHash() {
        const hash = window.location.hash.substring(1),
            params = {};
    
        if (hash.length < 5)
            return params;
    
        const vars = hash.split("&");
        for (let i = 0; i < vars.length; i++) {
            if (!vars[i])
                continue;
            
            const pair = vars[i].split("=");
            if (pair.length < 2) 
                continue;
            
            params[pair[0]] = pair[1];
        }
    
        if (params.gid)
            params.gid = parseInt(params.gid, 10);
    
        return params;
    }
}

/* Loads and instantiates media components passed to the global variable */
export default (element) => {
    if (element && element.id && tainacan_plugin?.classes?.TainacanMediaGallery && tainacan_plugin?.tainacan_media_components && tainacan_plugin.tainacan_media_components[element.id]) {
        const component = tainacan_plugin.tainacan_media_components[element.id];
        new tainacan_plugin.classes.TainacanMediaGallery(
            component.has_media_thumbs ? '#' + component.media_thumbs_id : null,
            component.has_media_main ? '#' + component.media_main_id : null,
            component
        );
    }
};


/*

---- Carousel of thumbnails only ----------------------------------------

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


---- Carousel of thumbnails with main slider ----------------------------

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