// THUMBNAIL PLUGIN - Translates api path of thumbnails src to render placeholders and propoper sizes.
export const ThumbnailHelperPlugin = {};
ThumbnailHelperPlugin.install = function (Vue, options = {}) {
    
    Vue.prototype.$thumbHelper = {
        imagesFolderPath: tainacan_plugin.base_url + '/assets/images/',
        getSrc(thumbnail, tainacanSize, documentType) {
            const wordpressSize = this.getWordpressFallbackSize(tainacanSize);
            return (thumbnail && thumbnail[tainacanSize]) ? thumbnail[tainacanSize][0] : ((thumbnail && thumbnail[wordpressSize]) ? thumbnail[wordpressSize][0] : this.getEmptyThumbnailPlaceholder(documentType, tainacanSize));
        },
        getSrcSet(thumbnail, tainacanSize, documentType) {
            const defaultSrc = this.getSrc(thumbnail, tainacanSize, documentType);
            const retinaSrc = (thumbnail && thumbnail['full']) ? thumbnail['full'][0] : this.getEmptyThumbnailPlaceholder(documentType, 'full');
            return defaultSrc + ' 1x, ' + retinaSrc + ' 2x';
        },
        getWidth(thumbnail, tainacanSize, fallbackSizeValue) {
            const wordpressSize = this.getWordpressFallbackSize(tainacanSize);
            return (thumbnail && thumbnail[tainacanSize]) ? thumbnail[tainacanSize][1] : ((thumbnail && thumbnail[wordpressSize]) ? thumbnail[wordpressSize][1] : (fallbackSizeValue ? fallbackSizeValue : 120));
        },
        getHeight(thumbnail, tainacanSize, fallbackSizeValue) {
            const wordpressSize = this.getWordpressFallbackSize(tainacanSize);
            return (thumbnail && thumbnail[tainacanSize]) ? thumbnail[tainacanSize][2] : ((thumbnail && thumbnail[wordpressSize]) ? thumbnail[wordpressSize][2] : (fallbackSizeValue ? fallbackSizeValue : 120));
        },
        getBlurhashString(thumbnail, tainacanSize) {
            const wordpressSize = this.getWordpressFallbackSize(tainacanSize);
            return (thumbnail && thumbnail[tainacanSize]) ? thumbnail[tainacanSize][4] : ((thumbnail && thumbnail[wordpressSize]) ? thumbnail[wordpressSize][4] : 'V4P?:h00Rj~qM{of%MRjWBRjD%%MRjayofj[%M-;RjRj');
        },
        getEmptyThumbnailPlaceholder(documentType, tainacanSize) {
            
            let imageSrc = '';
            switch(documentType) {
                case 'image/png':
                case 'image/jpeg':
                case 'image/gif':
                case 'image/bmp':
                case 'image/webp':
                case 'image/svg+xml':
                    imageSrc = 'placeholder_image';
                    break;
                case 'audio/midi':
                case 'audio/mpeg':
                case 'audio/webm':
                case 'audio/ogg':
                case 'audio/wav':
                    imageSrc = 'placeholder_audio';
                    break;
                case 'text':
                case 'text/plain':
                case 'text/html':
                case 'text/css':
                case 'text/javascript':
                case 'text/csv':
                    imageSrc = 'placeholder_text';
                    break;
                case 'video/webm':
                case 'video/ogg':
                case 'video/mpeg':
                    imageSrc = 'placeholder_video';
                    break;
                case 'url':
                    imageSrc = 'placeholder_url';
                    break;
                case 'application/pdf':
                    imageSrc = 'placeholder_pdf';
                    break;
                case 'empty':
                default:
                    imageSrc = 'placeholder_square';
            }

            return this.getEmptyThumbnailPlaceholderBySize(imageSrc, tainacanSize);
        },
        getEmptyThumbnailPlaceholderBySize(imageSrc, tainacanSize) {
            switch(tainacanSize) {
                case 'tainacan-medium-full':
                case 'tainacan-medium':
                case 'medium_large':
                case 'medium':
                    return this.imagesFolderPath + imageSrc + '_medium.png';
                case 'tainacan-small':
                case 'thumbnail':
                    return this.imagesFolderPath + imageSrc + '_small.png';
                case 'full':
                default:
                    return this.imagesFolderPath + imageSrc + '.png';
            }
        },
        getWordpressFallbackSize(tainacanSize) {
            switch(tainacanSize) {
                case 'tainacan-medium-full':
                   return 'medium_large';
                case 'tainacan-medium':
                    return 'medium';
                case 'tainacan-small':
                    return 'thumbnail';
                default:
                    return 'thumbnail';
            }
        }
    }
};
