// THUMBNAIL PLUGIN - Translates api path of thumbnails src to render placeholders and propoper sizes.
export const ThumbnailHelperPlugin = {};

export const ThumbnailHelperFunctions = () => {
    return {
        imagesFolderPath() {
            return typeof tainacan_plugin != 'undefined' ? (tainacan_plugin.base_url + '/assets/images/') : (tainacan_blocks ? (tainacan_blocks.base_url + '/assets/images/') : '/assets/images/');
        },
        getSrc(thumbnail, tainacanSize, documentType) {
            const wordpressSize = this.getWordpressFallbackSize(tainacanSize);
            return (thumbnail && thumbnail[tainacanSize]) ? thumbnail[tainacanSize][0] : ((thumbnail && thumbnail[wordpressSize]) ? thumbnail[wordpressSize][0] : this.getEmptyThumbnailPlaceholder(documentType, tainacanSize));
        },
        getSrcSet(thumbnail, tainacanSize, documentType) {
            const defaultSrc = this.getSrc(thumbnail, tainacanSize, documentType);
            const retinaSrc = this.getSrc(thumbnail, this.getRelativeRetinaSize(tainacanSize), documentType);
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
                case 'audio/mp3':
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
                case 'video/mp4':
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
                case 'tainacan-large-full':
                case 'tainacan-medium-full':
                case 'tainacan-medium':
                case 'medium_large':
                case 'medium':
                    return this.imagesFolderPath() + imageSrc + '_medium.png';
                case 'tainacan-small':
                case 'thumbnail':
                    return this.imagesFolderPath() + imageSrc + '_small.png';
                case 'full':
                default:
                    return this.imagesFolderPath() + imageSrc + '.png';
            }
        },
        getWordpressFallbackSize(tainacanSize) {
            switch(tainacanSize) {
                case 'tainacan-large-full':
                case 'tainacan-medium-full':
                    return 'medium_large';
                case 'tainacan-medium':
                    return 'medium';
                case 'tainacan-small':
                    return 'thumbnail';
                default:
                    return 'thumbnail';
            }
        },
        getRelativeRetinaSize(tainacanSize) {
            switch(tainacanSize) {
                case 'tainacan-medium-full':
                case 'tainacan-medium':
                    return 'large';
                case 'tainacan-small':
                    return 'tainacan-medium';
                default:
                    return 'full';
            }
        }
    }
}

ThumbnailHelperPlugin.install = function (Vue, options = {}) {
    Vue.prototype.$thumbHelper = ThumbnailHelperFunctions();
};



// ORDERBY PLUGIN - Converts a metadatum information into appropriate orderby query for WP Query
export const OrderByHelperPlugin = {};

export const OrderByHelperFunctions = () => {
    return {
        getOrderByForMetadatum(metadatum) {

            // If we are receiving a metadatum object, we can handle different orderby properties
            if (metadatum.id !== undefined) {
                if (metadatum.metadata_type_object && (metadatum.metadata_type_object.primitive_type == 'float' || metadatum.metadata_type_object.primitive_type == 'int')) {
                    return {
                        metakey: metadatum.id,
                        orderby: 'meta_value_num'
                    }
                } else if (metadatum.metadata_type_object && metadatum.metadata_type_object.primitive_type == 'date') {
                    return {
                        orderby: 'meta_value',
                        metakey: metadatum.id,
                        metatype: 'DATETIME'
                    }
                } else if (metadatum.metadata_type_object && metadatum.metadata_type_object.core) {
                    return  metadatum.metadata_type_object.related_mapped_prop
                } else {
                    return {
                        orderby: 'meta_value',
                        metakey: metadatum.id
                    }
                }
            // If it is just a string, we stick to the default
            } else {
                // We do this due to previous metadata that were saved as metadata object instead of orderby objects.
                if (metadatum.slug) {
                    switch(metadatum.slug) {
                        case 'modification_date': return 'modified'
                        case 'creation_date': return 'date'
                        case 'author_name': return 'author_name'
                        case 'created_by': return 'author_name'
                        case 'title': return 'title'
                        case 'description': return 'description'
                        default: return metadatum;
                    }
                } else {
                    switch(metadatum) {
                        case 'modification_date': return { orderby: 'modified' }
                        case 'creation_date': return { orderby: 'date' }
                        case 'author_name': return { orderby: 'author_name' }
                        case 'created_by': return { orderby: 'author_name' }
                        case 'title': return { orderby: 'title' }
                        case 'description': return { orderby: 'description' }
                        default: return metadatum;
                    }
                }
            }
        },
        getOrderByMetadatumName(orderBy, metadata) {

            if (orderBy.metakey) {
                let existingMetadataIndex = metadata.findIndex((aMetadatum) => aMetadatum.id == orderBy.metakey);
                return existingMetadataIndex >= 0 ? metadata[existingMetadataIndex].name : '';
            } else {
                // We do this due to previous metadata that were saved as metadata object instead of orderby objects.
                if (orderBy.slug) {
                    switch(orderBy.slug) {
                        case 'modification_date': return 'label_modification_date'
                        case 'modified': return 'label_modification_date'
                        case 'creation_date': return  'label_creation_date'
                        case 'date': return  'label_creation_date'
                        case 'author_name': return 'label_created_by'
                        case 'created_by': return 'label_created_by'
                        case 'title': return 'label_title'
                        case 'description': return 'label_description'
                        default: return orderBy.slug;
                    }
                } else {
                    switch(orderBy.orderby) {
                        case 'modification_date': return 'label_modification_date'
                        case 'modified': return 'label_modification_date'
                        case 'creation_date': return  'label_creation_date'
                        case 'date': return  'label_creation_date'
                        case 'author_name': return 'label_created_by'
                        case 'created_by': return 'label_created_by'
                        case 'title': return 'label_title'
                        case 'description': return 'label_description'
                        default: return orderBy.orderby;
                    }
                }
            }
        }
    }
}

OrderByHelperPlugin.install = function (Vue, options = {}) {
    Vue.prototype.$orderByHelper = OrderByHelperFunctions();
};
