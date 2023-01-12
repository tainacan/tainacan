import qs from 'qs';

export const viewModesMixin = {
    data() {
        return {
            isSlideshowViewModeEnabled: false
        }
    },
    props: {
        collectionId: Number,
        displayedMetadata: Array,
        items:  {
            type: Array,
            default: () => [],
            required: true
        },
        isLoading: false,
        totalItems: Number,
        isFiltersMenuCompressed: Boolean,
        enabledViewModes: Array
    },
    computed: {
        queries() {
            let currentQueries = JSON.parse(JSON.stringify(this.$route.query));
            if (currentQueries) {
                delete currentQueries['view_mode'];
                delete currentQueries['fetch_only'];
                delete currentQueries['fetch_only_meta'];
            }
            return currentQueries
        }
    },
    mounted() {
        this.isSlideshowViewModeEnabled = (this.enabledViewModes && Array.isArray(this.enabledViewModes)) ? (this.enabledViewModes.findIndex((viewMode) => viewMode == 'slideshow') >= 0) : false;
    },
    methods: {
        hasBeforeHook() {
            if (wp !== undefined && wp.hooks !== undefined)
                return wp.hooks.hasFilter(`tainacan_faceted_search_item_before`) || wp.hooks.hasFilter(`tainacan_faceted_search_collection_${this.collectionId}_item_before`);

            return false;
        },
        hasAfterHook() {
            if (wp !== undefined && wp.hooks !== undefined)
                return wp.hooks.hasFilter(`tainacan_faceted_search_collection_item_after`) || wp.hooks.hasFilter(`tainacan_faceted_search_collection_${this.collectionId}_item_after`);

            return false;
        },
        getBeforeHook(item) {
            if (wp !== undefined && wp.hooks !== undefined)
                return wp.hooks.applyFilters(`tainacan_faceted_search_collection_${this.collectionId}_item_before`, wp.hooks.applyFilters(`tainacan_faceted_search_item_before`, '', item), item);

            return '';
        },
        getAfterHook(item) {
            if (wp !== undefined && wp.hooks !== undefined)
                return wp.hooks.applyFilters(`tainacan_faceted_search_collection_${this.collectionId}_item_after`, wp.hooks.applyFilters(`tainacan_faceted_search_item_after`, '', item), item);

            return '';
        },
        getItemLink(itemUrl, index) {
            if (this.queries) {
                // Inserts information necessary for item by item navigation on single pages
                this.queries['pos'] = ((this.queries['paged'] - 1) * this.queries['perpage']) + index;
                this.queries['source_list'] = this.$root.termId ? 'term' : (!this.$root.collectionId || this.$root.collectionId == 'default' ? 'repository' : 'collection');
                this.queries['ref'] = this.$route.path;
                return itemUrl + '?' + qs.stringify(this.queries);
            }
            return itemUrl;
        },
        renderMetadata(item, metadatum, multivalueIndex) {
            let metadata = false;
            if (item && item.metadata && item.metadata[metadatum.slug] != undefined)
                metadata = item.metadata[metadatum.slug] 
            else if (metadatum &&
                     metadatum.metadata_type_object &&
                     metadatum.metadata_type_object.core && 
                     metadatum.metadata_type_object.related_mapped_prop &&
                     item[metadatum.metadata_type_object.related_mapped_prop]
            ) {
                return item[metadatum.metadata_type_object.related_mapped_prop];
            }

            if (!metadata)
                return '';
                
            if ( multivalueIndex != undefined && metadata.value[multivalueIndex]) {
            
                if ( !Array.isArray(metadata.value[multivalueIndex]) && metadata.value[multivalueIndex].value_as_html)
                    return metadata.value[multivalueIndex].value_as_html;

                if ( Array.isArray(metadata.value[multivalueIndex]) ) {
                    let sumOfValuesAsHtml = '';

                    metadata.value[multivalueIndex].forEach(aValue => {
                        if (aValue.value_as_html)
                            sumOfValuesAsHtml += aValue.value_as_html + '<br>';
                    })

                    return sumOfValuesAsHtml;
                }
            }

            return metadata.value_as_html;
        },
        starSlideshowFromHere(index) {
            this.$router.replace({ query: {...this.$route.query, ...{'slideshow-from': index } }}).catch((error) => this.$console.log(error));
        },
        getPosInSet(index) {
            if (Number(this.queries.paged) !== NaN && Number(this.queries.perpage) !== NaN)
                return ((Number(this.queries.paged) - 1) * Number(this.queries.perpage)) + index + 1;
        }
    }
}