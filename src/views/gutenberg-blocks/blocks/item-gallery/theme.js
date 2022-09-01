// TAINACAN MEDIA COMPONENT --------------------------------------------------------
//
// Counts on some HMTL markup to make a list of media links be displayed
// as a carousel with a lightbox. Check examples in the end of the file 
import PhotoSwipeLightbox from 'photoswipe/lightbox';
import PhotoSwipe from 'photoswipe';
import 'photoswipe/dist/photoswipe.css';
import Swiper, { Navigation, A11y, Thumbs } from 'swiper';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/a11y';
import 'swiper/css/controller';

const { __ } = wp.i18n;

tainacan_plugin.classes.TainacanMediaGallery = class TainacanMediaGallery {

    /**
     * Constructor initializes the instance. Options are Snake Case because they come from PHP side
     * @param  {String}  thumbs_gallery_selector                html element to be queried containing the thumbnails list
     * @param  {String}  main_gallery_selector                  html element to be queried containing the main list
     * @param  {Object}  options                                several options to be tweaked
     * @param  {Object}  options.swiper_thumbs_options          object with SwiperJS options for the thumbnails list (https://swiperjs.com/swiper-api)
     * @param  {Object}  options.swiper_main_options            object with SwiperJS options for the main list
     * @param  {Boolean} options.disable_lightbox               do not open photoswipes lightbox when clicking the main gallery
     * @param  {Boolean} options.show_share_button              show share button on lightbox
     * @param  {Boolean} options.show_download_button           show share download button on lightbox
     * @param  {Boolean} options.hide_media_name                hide media name on lightbox
     * @param  {Boolean} options.hide_media_caption             hide media caption on lightbox
     * @param  {Boolean} options.hide_media_description         hide media description lightbox
     * @param  {Boolean} options.lightbox_has_light_background  shows a light instead of dark background color scheme on the lightbox
     * 
     * @return {Object}                                   TainacanMediaGallery instance
     */
    constructor(thumbs_gallery_selector, main_gallery_selector, options) {
        this.thumbs_gallery_selector = thumbs_gallery_selector;
        this.main_gallery_selector = main_gallery_selector;
        this.thumbsSwiper = null;
        this.mainSwiper = null;
        this.lightbox = null;
        this.options = options;

        this.initializeSwiper();
        
        if (!this.options.disable_lightbox) {
            if (this.main_gallery_selector)
                this.initializePhotoswipe(this.main_gallery_selector + " .swiper-wrapper");
            else if (this.thumbs_gallery_selector)
                this.initializePhotoswipe(this.thumbs_gallery_selector + " .swiper-wrapper");
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
                // centeredSlides: true,
                // centeredSlidesBounds: true,
                centerInsufficientSlides: true,
                slideToClickedSlide: true,
                watchOverflow: true,
                a11y: {
                    prevSlideMessage: __( 'Previous slide', 'tainacan'),
                    nextSlideMessage: __( 'Next slide', 'tainacan'),
                    firstSlideMessage: __('This is the first slide', 'tainacan'),
                    lastSlideMessage: __('This is the last slide', 'tainacan')
                },
                modules: [Navigation, A11y]
            };
            thumbsSwiperOptions = {...thumbsSwiperOptions, ...this.options.swiper_thumbs_options };
            this.thumbsSwiper = new Swiper(this.thumbs_gallery_selector, thumbsSwiperOptions);
        }

        if (this.main_gallery_selector) {
            let mainSwiperOptions = {
                slidesPerView: 1,
                slidesPerGroup: 1,
                watchOverflow: true,
                a11y: {
                    prevSlideMessage: __( 'Previous slide', 'tainacan'),
                    nextSlideMessage: __( 'Next slide', 'tainacan'),
                    firstSlideMessage: __('This is the first slide', 'tainacan'),
                    lastSlideMessage: __('This is the last slide', 'tainacan')
                },
                modules: [Navigation, A11y]
            };
            mainSwiperOptions = {...mainSwiperOptions, ...this.options.swiper_main_options };
        
            if (this.thumbs_gallery_selector && this.thumbsSwiper) {
                mainSwiperOptions.thumbs = {
                    swiper: this.thumbsSwiper,
                    autoScrollOffset: 3
                }
                mainSwiperOptions.modules = [Navigation, A11y, Thumbs];
            }

            this.mainSwiper = new Swiper(this.main_gallery_selector, mainSwiperOptions);
        }
        
    }
  
    /* Initializes Photoswipe Lightbox */
    initializePhotoswipe (gallerySelector) {

        // Loop through all gallery elements and bind events
        let galleryElement = document.querySelector(gallerySelector);
        galleryElement.setAttribute("data-pswp-uid", this.options.media_id);
        
        const self = this;

        let items = this.parseThumbnailElements(galleryElement);
        let photoswipeOptions = {
            loop: false,
            preloadFirstSlide: false,
            mainClass: 'tainacan-photoswipe-layer' + (this.options.lightbox_has_light_background ? ' has-light-color-scheme' : ''), 
            bgOpacity: 0.85,
            clickToCloseNonZoomable: false,
            closeTitle: __( 'Close lightbox', 'tainacan'),
            zoomTitle: __( 'Zoom', 'tainacan'),
            arrowPrevTitle: __( 'Previous slide', 'tainacan'),
            arrowNextTitle: __( 'Next slide', 'tainacan'),
            errorMsg: __('The image cannot be loaded', 'tainacan'),
            wheelToZoom: true,
            getClickedIndexFn: (clickedElement) => {
                return items.findIndex(anItem => anItem.el.contains(clickedElement.target));
            },
            paddingFn: (viewportSize, itemData, index) => {
                return {
                    // check based on slide index
                    top: (itemData.title && itemData.title.name && !self.options.hide_media_name) ? 60 : 0,
                    bottom: (itemData.title && ((!self.options.hide_media_caption && itemData.title.caption) || (!self.options.hide_media_description && itemData.title.description))) ? 60 : 0,
                    left: 40,
                    right: 40
                };
            }
        };

        // Pass data to PhotoSwipe and initialize it
        this.lightbox = new PhotoSwipeLightbox({
            gallery: galleryElement,
            children: items,
            pswpModule: PhotoSwipe,
            ...photoswipeOptions
        });
        this.lightbox.init();
        
        /* Updates Swiper instance from Photoswipe */
        const swiperInstance = this.mainSwiper ? this.mainSwiper : this.thumbsSwiper;    

        // Parse URL and open gallery from it if contains #&pid=3&gid=1
        const hashData = this.photoswipeParseHash();
        if (hashData.pid && hashData.gid && this.options.media_id == hashData.gid) {
            // in URL indexes start from 1
            photoswipeOptions.index = parseInt(hashData.pid, 10) - 1;

            if (!isNaN(photoswipeOptions.index) && items[photoswipeOptions.index] && items[photoswipeOptions.index].el)
                items[photoswipeOptions.index].el.click();
        }

        // On destroy we make a copy of the inner content to clear it
        // and set again. This stops YouTube player, for example. 
        this.lightbox.on('destroy', () => { 
            let actualGalleryContainer = document.getElementsByClassName("pswp__container")[0];
            if (actualGalleryContainer) {
                let currentData = actualGalleryContainer.innerHTML;
                actualGalleryContainer.innerHTML = '';
                actualGalleryContainer.innerHTML = currentData;           
            } 
        });

        // Swiper autoplay stop when image zoom */
        this.lightbox.on('initialZoomInEnd', () => {
            if (swiperInstance.params && swiperInstance.params.autoplay && swiperInstance.params.autoplay.enabled && swiperInstance.autoplay.running)
                swiperInstance.autoplay.stop();
        });

        // Update position of the slider
        this.lightbox.on("change", () => {
            if (self.lightbox.pswp && !isNaN(self.lightbox.pswp.currIndex) && self.lightbox.pswp.currIndex >= 0) {
                // This is the index of current photoswipe slide
                swiperInstance.slideTo(self.lightbox.pswp.currIndex);

                // Also updates URL for history navigation
                // We only add to the history if it is the first time opening
                let currentURL = window.location.toString();
                if (currentURL.indexOf("#") > 0) {
                    currentURL = currentURL.substring(0, currentURL.indexOf("#"));
                    window.history.replaceState({}, '', currentURL + '#gid=' + this.options.media_id + '&pid=' + (self.lightbox.pswp.currIndex + 1));
                } else {
                    window.history.pushState({}, '', currentURL + '#gid=' + this.options.media_id + '&pid=' + (self.lightbox.pswp.currIndex + 1));  
                } 
            }
        });

        // Re-starts autoplay, if needed
        this.lightbox.on("close", () => {
            // Start swiper autoplay (on close - if swiper autoplay is true)
            if (swiperInstance.params && swiperInstance.params.autoplay && swiperInstance.params.autoplay.enabled)
                swiperInstance.autoplay.start();

            // Clears URL hash as we no longer need history navigation
            let currentURL = window.location.toString();
            if (currentURL.indexOf("#") > 0)
                window.history.replaceState({},'', currentURL.substring(0, currentURL.indexOf("#")));
        });

        // Adds name, caption, description
        this.lightbox.on('uiRegister', () => {
            self.lightbox.pswp.ui.registerElement({
                name: 'name',
                order: 7,
                isButton: false,
                appendTo: 'bar',
                onInit: (el, pswp) => {
                    self.lightbox.pswp.on('change', () => {
                        const item = pswp.currSlide.data;
                        let innerHTML = '';
                        if (
                            item &&
                            item.title &&
                            item.title.name &&
                            !self.options.hide_media_name
                        )
                            innerHTML += item.title.name.innerHTML;
                        el.innerHTML = innerHTML;
                    });
                }
            });
            self.lightbox.pswp.ui.registerElement({
                name: 'caption',
                order: 15,
                isButton: false,
                appendTo: 'root',
                onInit: (el, pswp) => {
                    self.lightbox.pswp.on('change', () => {
                        const item = pswp.currSlide.data;
                        let innerHTML = '';
                        if (
                            item &&
                            item.title &&
                            (
                                (item.title.caption && !self.options.hide_media_caption) ||
                                (item.title.description && !self.options.hide_media_description)
                            )
                        ) {
                            innerHTML += '<div class="pswp__caption-inner">';
                            
                            if (item.title.caption && !self.options.hide_media_caption)
                                innerHTML += '<span class="pswp__figure_caption">' + item.title.caption.innerHTML + '</span>';

                            if (item.title.description && !self.options.hide_media_description)
                                innerHTML += '<span class="pswp__description">' + item.title.description.innerHTML + '</span>';
                            
                            innerHTML += '</div>';
                        }
                        el.innerHTML = innerHTML;
                    });
                }
            });
        });

        /* Stops propagation of download button to avoid opening the gallery on it */
        let carouselDownloadButtons = galleryElement.getElementsByClassName('tainacan-item-file-download');
        if (carouselDownloadButtons && carouselDownloadButtons.length) {
            for (let i = 0; i < carouselDownloadButtons.length; i++) {
                carouselDownloadButtons[i].addEventListener('click',function(e){
                    e.stopPropagation();
                });
            }
        }
    }
  
    // Parse slide data (url, title, size ...) from DOM elements
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

  
    // Parse slide index and gallery index from URL (#&pid=1&gid=2)
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
    
        if (params.pid)
            params.pid = parseInt(params.pid, 10);
    
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