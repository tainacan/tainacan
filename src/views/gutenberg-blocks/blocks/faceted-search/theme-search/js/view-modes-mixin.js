import qs from 'qs';

export const viewModesMixin = {
    data() {
        return {
            isSlideshowViewModeEnabled: false
        }
    },
    props: {
        collectionId: [String, Number],
        termId: [String, Number],
        displayedMetadata: Array,
        shouldHideItemsThumbnail: Boolean,
        items:  {
            type: Array,
            default: () => [],
            required: true
        },
        isLoading: false,
        totalItems: Number,
        isFiltersMenuCompressed: Boolean,
        enabledViewModes: Array,
        containerId: String
    },
    computed: {
        queries() {
            let currentQueries = (this.$route && this.$route.query) ? JSON.parse(JSON.stringify(this.$route.query)) : {};
            if (currentQueries) {
                delete currentQueries['view_mode'];
                delete currentQueries['fetch_only'];
                delete currentQueries['fetch_only_meta'];
            }
            return currentQueries
        },
         /* 
         * This computed property only returns the metadatum object where the title is. 
         * In repository level, there is not "title metadatum", this information comes directly from the item.title.
         */
         titleItemMetadatum() {
            const possibleTitleItemMetadatum = this.displayedMetadata.find(aMetadatum => aMetadatum.display && aMetadatum.metadata_type_object && aMetadatum.metadata_type_object.related_mapped_prop == 'title');
            return possibleTitleItemMetadatum ? possibleTitleItemMetadatum : false;
        },
        /* 
         * This computed property only returns the metadatum object where the description is. 
         * In repository level, there is not "description metadatum", this information comes directly from the item.description.
         */
        descriptionItemMetadatum() {
            const possibleDescriptionItemMetadatum = this.displayedMetadata.find(aMetadatum => aMetadatum.display && aMetadatum.metadata_type_object && aMetadatum.metadata_type_object.related_mapped_prop == 'description');
            return possibleDescriptionItemMetadatum ? possibleDescriptionItemMetadatum : false;
        },
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
                this.queries['source_list'] = this.termId ? 'term' : (!this.collectionId || this.collectionId == 'default' ? 'repository' : 'collection');
                if ( this.$route && this.$route.href && this.$route.href.split('?') && this.$route.href.split('?').length )
                    this.queries['ref'] = this.$route.href;
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
            if ( this.$router && this.$route && this.$route.query )
                this.$router.replace({ query: {...this.$route.query, ...{'slideshow-from': index } }}).catch((error) => this.$console.log(error));
        },
        getPosInSet(index) {
            if ( !isNaN(Number(this.queries.paged)) && !isNaN(Number(this.queries.perpage)) )
                return ((Number(this.queries.paged) - 1) * Number(this.queries.perpage)) + index + 1;
        }
    }
}