// TAINACAN MEDIA COMPONENT --------------------------------------------------------
//
// Counts on some HMTL markup to make a list of media links be displayed
// as a carousel with a lightbox. Check examples in the end of the file 
import PhotoSwipeLightbox from 'photoswipe/lightbox';
import PhotoSwipe from 'photoswipe';
import 'photoswipe/dist/photoswipe.css';
import Swiper from 'swiper';
import { Navigation, A11y, Thumbs, Pagination } from 'swiper/modules';

const { __ } = wp.i18n;

if (typeof window.tainacan_plugin === 'undefined')
    window.tainacan_plugin = {};

if (!window.tainacan_plugin.classes)
    window.tainacan_plugin.classes = {};

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
                this.initializePhotoswipe(this.main_gallery_selector + " .tainacan-media-items");
            else if (this.thumbs_gallery_selector)
                this.initializePhotoswipe(this.thumbs_gallery_selector + " .tainacan-media-items");
        }
    }
  
    /* Initializes Swiper JS instances of carousels */
    initializeSwiper() {
        
        if (this.thumbs_gallery_selector) {
            let thumbsSwiperOptions = {
                spaceBetween: 12,
                slidesPerView: 'auto',
                watchSlidesProgress: true,
                navigation: {
                    addIcons: false,
                    nextEl: '.swiper-navigation-next_' + this.thumbs_gallery_selector,
                    prevEl: '.swiper-navigation-prev_' + this.thumbs_gallery_selector,
                },
                pagination: {
                    el: '.swiper-pagination_' + this.thumbs_gallery_selector
                },
                centeredSlides: true,
                centeredSlidesBounds: true,
                centerInsufficientSlides: true,
                slideToClickedSlide: true,
                watchOverflow: true,
                a11y: {
                    slideRole: '',
                    prevSlideMessage: __( 'Previous slide', 'tainacan'),
                    nextSlideMessage: __( 'Next slide', 'tainacan'),
                    firstSlideMessage: __('This is the first slide', 'tainacan'),
                    lastSlideMessage: __('This is the last slide', 'tainacan'),
                    slideLabelMessage: '' // Screenreaders already know the 1/x slide number due to <ul>/<li> structure
                },
                modules: [Navigation, A11y, Pagination],
                on: {
                    init: function(swiper) {
                        swiper.el.classList.add('swiper-is-beginning');
                    },
                    slideChange: function(swiper) {

                        if (swiper.isBeginning)
                            swiper.el.classList.add('swiper-is-beginning');
                        else
                            swiper.el.classList.remove('swiper-is-beginning');

                        if (swiper.isEnd)
                            swiper.el.classList.add('swiper-is-end');
                        else
                            swiper.el.classList.remove('swiper-is-end');
                    }
                }
            };
            thumbsSwiperOptions = {...thumbsSwiperOptions, ...this.options.swiper_thumbs_options };

            if ( !this.options.disable_thumbs_carousel)
                this.thumbsSwiper = new Swiper(this.thumbs_gallery_selector, thumbsSwiperOptions);
        }

        if (this.main_gallery_selector) {
            let mainSwiperOptions = {
                slidesPerView: 1,
                slidesPerGroup: 1,
                watchOverflow: true,
                a11y: {
                    slideRole: '',
                    prevSlideMessage: __( 'Previous slide', 'tainacan'),
                    nextSlideMessage: __( 'Next slide', 'tainacan'),
                    firstSlideMessage: __('This is the first slide', 'tainacan'),
                    lastSlideMessage: __('This is the last slide', 'tainacan'),
                    slideLabelMessage: '' // Screenreaders already know the 1/x slide number due to <ul>/<li> structure
                },
                pagination: {
                    el: '.swiper-pagination_' + this.main_gallery_selector,
                    clickable: true
                },
                modules: [Navigation, A11y, Pagination]
            };
            mainSwiperOptions = {...mainSwiperOptions, ...this.options.swiper_main_options };
        
            if (this.thumbs_gallery_selector && this.thumbsSwiper) {
                mainSwiperOptions.thumbs = {
                    swiper: this.thumbsSwiper,
                    autoScrollOffset: 3
                }
                mainSwiperOptions.modules = [Navigation, A11y, Thumbs, Pagination];
            }

            if ( !this.options.disable_main_carousel)
                this.mainSwiper = new Swiper(this.main_gallery_selector, mainSwiperOptions);

            if (
                !this.options.disable_thumbs_carousel &&
                !this.options.disable_main_carousel &&
                this.thumbs_gallery_selector &&
                this.thumbsSwiper &&
                this.mainSwiper
            ) {

                const refToMainSwiper = this.mainSwiper;
                const refToThumbSwiper = this.thumbsSwiper;

                
                this.mainSwiper.on('slideChangeTransitionStart', function() {
                    refToThumbSwiper.slideTo(refToMainSwiper.activeIndex);
                });
                
                // When both carousels are present, hide thumbnails from screen readers
                // The main slider already provides complete navigation (arrows, keyboard, pagination)
                // Thumbnails are primarily a visual navigation aid and would be redundant
                const thumbsElement = document.querySelector(this.thumbs_gallery_selector);
                if (thumbsElement) {
                    thumbsElement.setAttribute('aria-hidden', 'true');
                }
            }
        }
        
    }
  
    /* Initializes Photoswipe Lightbox */
    initializePhotoswipe (gallerySelector) {

        // Loop through all gallery elements and bind events
        let galleryElement = document.querySelector(gallerySelector);
        galleryElement.setAttribute("data-pswp-uid", this.options.media_id);
        
        const self = this;

        // Enhance links for accessibility before parsing items
        this.markLightboxClickTargets(galleryElement);
        this.enhanceLinksForAccessibility(galleryElement);
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
            returnFocus: true,
            getClickedIndexFn: (event) => {
                const index = items.findIndex(anItem => anItem.el.contains(event.target));
                if (index < 0)
                    return -1;
                // Video, audio, iframe and file viewers keep the first click.
                // PhotoSwipe still lists every slide so next/prev can land on them.
                if (!self.slideOpensLightboxOnClick(items[index].el))
                    return -1;
                return index;
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
        
        // Setup keyboard accessibility for links
        this.setupKeyboardAccessibility(galleryElement);
        
        /* Updates Swiper instance from Photoswipe */
        let swiperInstance = null;
        if (!this.options.disable_main_carousel && this.mainSwiper)
            swiperInstance = this.mainSwiper;
        else if (!this.options.disable_thumbs_carousel && this.thumbsSwiper)
            swiperInstance = this.thumbsSwiper;

        // Parse URL and open gallery from it if contains #&pid=3&gid=1
        const hashData = this.photoswipeParseHash();
        if (hashData.pid && hashData.gid && this.options.media_id == hashData.gid) {
            // in URL indexes start from 1
            photoswipeOptions.index = parseInt(hashData.pid, 10) - 1;

            if (!isNaN(photoswipeOptions.index) && items[photoswipeOptions.index])
                this.lightbox.loadAndOpen(photoswipeOptions.index);
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
            if (swiperInstance && swiperInstance.params && swiperInstance.params.autoplay && swiperInstance.params.autoplay.enabled && swiperInstance.autoplay.running)
                swiperInstance.autoplay.stop();
        });

        // Update position of the slider
        this.lightbox.on("change", () => {
            if (self.lightbox.pswp && !isNaN(self.lightbox.pswp.currIndex) && self.lightbox.pswp.currIndex >= 0) {
                // This is the index of current photoswipe slide
                if (swiperInstance)
                    swiperInstance.slideTo(self.lightbox.pswp.currIndex);

                // Also updates URL for history navigation
                // We only add to the history if it is the first time opening
                let currentURL = window.location.toString();
                if (currentURL.indexOf("#") > 0) {
                    currentURL = currentURL.substring(0, currentURL.indexOf("#"));
                    window.history.replaceState(window.history.state, '', currentURL + '#gid=' + this.options.media_id + '&pid=' + (self.lightbox.pswp.currIndex + 1));
                } else {
                    window.history.pushState(window.history.state, '', currentURL + '#gid=' + this.options.media_id + '&pid=' + (self.lightbox.pswp.currIndex + 1));  
                } 
            }
        });

        // Re-starts autoplay, if needed
        this.lightbox.on("close", () => {
            // Start swiper autoplay (on close - if swiper autoplay is true)
            if (swiperInstance && swiperInstance.params && swiperInstance.params.autoplay && swiperInstance.params.autoplay.enabled)
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

        /* Stops propagation of slide actions (Expand, Download) to avoid opening the gallery on them */
        let carouselSlideActions = galleryElement.getElementsByClassName('tainacan-media-item-actions');
        if (carouselSlideActions && carouselSlideActions.length) {
            for (let i = 0; i < carouselSlideActions.length; i++) {
                carouselSlideActions[i].addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        }

        /* Stops propagation of download button to avoid opening the gallery on it */
        let carouselDownloadButtons = galleryElement.getElementsByClassName('tainacan-item-file-download');
        if (carouselDownloadButtons && carouselDownloadButtons.length) {
            for (let i = 0; i < carouselDownloadButtons.length; i++) {
                carouselDownloadButtons[i].addEventListener('click',function(e){
                    e.stopPropagation();
                });
            }
        }

        this.setupLightboxExpandControls(galleryElement, items);

        /* Stops propagation inside links that are inside metatada */
        let carouselMetadataLinks = galleryElement.querySelectorAll('.swiper-slide-metadata a');
        if (carouselMetadataLinks && carouselMetadataLinks.length) {
            for (let i = 0; i < carouselMetadataLinks.length; i++) {
                carouselMetadataLinks[i].addEventListener('click',function(e){
                    e.stopPropagation();
                });
            }
        }
    }
  
    // Parse slide data (url, title, size ...) from DOM elements
    // (children of gallerySelector)
    parseThumbnailElements(el) {
        let items = [];

        this.getGallerySlides(el).forEach((liElement) => {
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
                    if (fullContentElement.alt)
                        item.alt = fullContentElement.alt;
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

    /**
     * Direct children of the gallery list that are media items.
     * Uses .tainacan-media-item so Swiper-disabled single items and future
     * grid/list thumbs stay in the same PhotoSwipe index as carousel slides.
     * @param {HTMLElement} galleryElement
     * @return {HTMLElement[]}
     */
    getGallerySlides(galleryElement) {
        const slides = [];
        Array.prototype.forEach.call(galleryElement.childNodes, (node) => {
            if (node.nodeType === 1 && node.classList.contains('tainacan-media-item'))
                slides.push(node);
        });
        return slides;
    }

    /**
     * Whether a node sits outside the visible slide media (lightbox payload, captions, download).
     * @param {Element} node
     * @param {Element} slideContent
     * @return {Boolean}
     */
    isOutsideVisibleSlideMedia(node, slideContent) {
        if (!node || !slideContent.contains(node))
            return true;
        if (node.closest('.media-full-content'))
            return true;
        if (node.closest('.swiper-slide-metadata'))
            return true;
        if (node.closest('.tainacan-media-item-actions'))
            return true;
        if (node.closest('.tainacan-item-file-download'))
            return true;
        return false;
    }

    /**
     * First matching media element in the visible slide, ignoring hidden full content.
     * @param {Element} slideContent
     * @param {String} selector
     * @return {Element|null}
     */
    getVisibleSlideMedia(slideContent, selector) {
        if (!slideContent)
            return null;

        const nodes = slideContent.querySelectorAll(selector);
        for (let i = 0; i < nodes.length; i++) {
            if (!this.isOutsideVisibleSlideMedia(nodes[i], slideContent))
                return nodes[i];
        }
        return null;
    }

    /**
     * Main media link in a slide (not metadata or download).
     * @param {Element} slideContent
     * @return {Element|null}
     */
    getSlideMainLink(slideContent) {
        if (!slideContent)
            return null;

        const allLinks = slideContent.querySelectorAll('a[href]');
        const metadataElement = slideContent.querySelector('.swiper-slide-metadata');

        for (let i = 0; i < allLinks.length; i++) {
            const link = allLinks[i];
            if (metadataElement && metadataElement.contains(link))
                continue;
            if (link.closest('.tainacan-media-item-actions'))
                continue;
            if (link.closest('.tainacan-item-file-download'))
                continue;
            return link;
        }
        return null;
    }

    /**
     * Clicking the slide should open PhotoSwipe only when the visible media is zoomable:
     * an image, or a document cover that is an <img> (see PDF cover option).
     * Video, audio, iframe and file viewers keep the first click.
     * @param {HTMLElement} slide
     * @return {Boolean}
     */
    slideOpensLightboxOnClick(slide) {
        const slideContent = slide && slide.querySelector('.swiper-slide-content');
        if (!slideContent)
            return false;

        const visibleImg = this.getVisibleSlideMedia(slideContent, 'img');
        if (visibleImg && slideContent.classList.contains('has-cover'))
            return true;

        if (this.getVisibleSlideMedia(slideContent, 'iframe, video, audio'))
            return false;

        return !!visibleImg;
    }

    /**
     * Marks slides that open the lightbox on click (cursor via CSS).
     * @param {HTMLElement} galleryElement
     */
    markLightboxClickTargets(galleryElement) {
        this.getGallerySlides(galleryElement).forEach((slide) => {
            slide.classList.toggle(
                'tainacan-media-item--opens-lightbox',
                this.slideOpensLightboxOnClick(slide)
            );
        });
    }

    /**
     * Enhances accessibility for slides that open the lightbox
     * PhotoSwipe works with any direct child of galleryElement, not just links
     * Some slides have links (images), others don't (cover images without a wrapping <a>)
     * Video/audio/iframe slides are left alone so the viewer stays usable.
     * @param {HTMLElement} galleryElement - The gallery container element (.tainacan-media-items)
     */
    enhanceLinksForAccessibility(galleryElement) {
        this.getGallerySlides(galleryElement).forEach((slide) => {
            if (!this.slideOpensLightboxOnClick(slide))
                return;

            const slideContent = slide.querySelector('.swiper-slide-content');
            if (!slideContent) return;

            const mainLink = this.getSlideMainLink(slideContent);
            const img = this.getVisibleSlideMedia(slideContent, 'img');
            const titleElement = slide.querySelector('.swiper-slide-metadata__name');
            const mediaType = slideContent.getAttribute('data-media-type') || '';

            let ariaLabelParts = [];
            let mediaDescription = '';

            if (mediaType === 'application/pdf') {
                mediaDescription = __('PDF document', 'tainacan');
            }

            if (mediaDescription) {
                ariaLabelParts.push(mediaDescription);
            }

            // Add title if no media description is available unless it is an img with alt text (those will be read from the img tag)
            if ( (!img || !img.alt) && ariaLabelParts.length === 0 && titleElement && titleElement.textContent.trim() ) {
                ariaLabelParts.push(titleElement.textContent.trim());
            }

            const ariaLabel = ariaLabelParts.join('. ');

            if (mainLink) {
                mainLink.setAttribute('aria-haspopup', 'dialog');
                mainLink.removeAttribute('target');
                if ( ariaLabel )
                    mainLink.setAttribute('aria-label', ariaLabel);
            } else {
                const figure = slideContent.querySelector('figure');
                const focusableElement = figure || slideContent;

                focusableElement.setAttribute('role', 'button');
                focusableElement.setAttribute('aria-haspopup', 'dialog');
                if ( ariaLabel )
                    focusableElement.setAttribute('aria-label', ariaLabel);
                focusableElement.setAttribute('tabindex', '0');
            }
        });
    }

    /**
     * Sets up keyboard accessibility for slides that open the lightbox (Enter and Space keys)
     * Works with both links and non-link cover/image elements
     * @param {HTMLElement} galleryElement - The gallery container element (.tainacan-media-items)
     */
    setupKeyboardAccessibility(galleryElement) {
        this.getGallerySlides(galleryElement).forEach((slide) => {
            if (!this.slideOpensLightboxOnClick(slide))
                return;

            const slideContent = slide.querySelector('.swiper-slide-content');
            if (!slideContent) return;

            const mainLink = this.getSlideMainLink(slideContent);
            let focusableElement = mainLink;
            if (!focusableElement) {
                const figure = slideContent.querySelector('figure');
                focusableElement = figure || slideContent;
            }

            if (!focusableElement) return;

            focusableElement.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    e.stopPropagation();

                    const clickEvent = new MouseEvent('click', {
                        bubbles: true,
                        cancelable: true,
                        view: window
                    });

                    if (mainLink) {
                        mainLink.dispatchEvent(clickEvent);
                    } else {
                        slide.dispatchEvent(clickEvent);
                    }
                }
            });
        });
    }

    /**
     * Expand opens PhotoSwipe at the slide index without stealing player clicks.
     * @param {HTMLElement} galleryElement
     * @param {Array} items Parsed PhotoSwipe items (same index as Swiper).
     */
    setupLightboxExpandControls(galleryElement, items) {
        const self = this;
        const expandControls = galleryElement.querySelectorAll('.tainacan-media-item-expand');

        Array.prototype.forEach.call(expandControls, (expandControl) => {
            expandControl.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (!self.lightbox)
                    return;

                const slide = expandControl.closest('.tainacan-media-item');
                const index = items.findIndex(anItem => anItem.el === slide);
                if (index >= 0)
                    self.lightbox.loadAndOpen(index);
            });
        });
    }
}

/* Loads and instantiates media components passed to the global variable */
export default (element) => {
    if (!element || !element.id || !tainacan_plugin?.classes?.TainacanMediaGallery)
        return;

    const defaultComponentConfig = {
        media_main_id: null,
        media_thumbs_id: null,
        media_id: element.id,
        has_media_main: false,
        has_media_thumbs: false,
        swiper_main_options: {},
        swiper_thumbs_options: {},
        disable_main_carousel: false,
        disable_thumbs_carousel: false,
        disable_lightbox: false,
        lightbox_has_light_background: false,
        hide_media_name: false,
        hide_media_caption: false,
        hide_media_description: false,
        show_share_button: false,
        show_download_button: false,
    };

    let component = null;

    // Config embedded in markup
    const rawConfig = element?.dataset?.tainacanMediaComponentConfig;
    if (rawConfig) {
        try {
            component = { ...defaultComponentConfig, ...(JSON.parse(rawConfig) || {}) };
        } catch (e) {
            component = null;
        }
    }

    if (component && (Array.isArray(component.swiper_main_options) || !component.swiper_main_options))
        component.swiper_main_options = {};

    if (component && (Array.isArray(component.swiper_thumbs_options) || !component.swiper_thumbs_options))
        component.swiper_thumbs_options = {};

    if (component) {
        new tainacan_plugin.classes.TainacanMediaGallery(
            component.has_media_thumbs ? '#' + component.media_thumbs_id : null,
            component.has_media_main ? '#' + component.media_main_id : null,
            component
        );
    }
};
