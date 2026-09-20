import qs from 'qs';
import axios from './axios';
import { mapGetters } from 'vuex';

export const filterTypeMixin = {
    data () {
        return {
            collectionId: '',
            metadatumId: '',
            metadatumType: '',
            metadatumName: '',
            filterTypeOptions: [],
            shouldAddOptions: false
        }
    },
    props: {
        filter: Object,
        query: Object,
        isRepositoryLevel: Boolean,
        isUsingElasticSearch: Boolean,
        isLoadingItems: Boolean,
        currentCollectionId: [Number, String]
    },
    created() {
        this.collectionId = this.filter.collection_id ? this.filter.collection_id : this.collectionId;
        this.metadatumId = this.filter.metadatum.metadatum_id ? this.filter.metadatum.metadatum_id : this.metadatumId;
        this.metadatumName = this.filter.metadatum && this.filter.metadatum.metadatum_name ? this.filter.metadatum.metadatum_name : this.metadatumName;
        this.filterTypeOptions = this.filter.filter_type_options ? this.filter.filter_type_options : this.filterTypeOptions;
        this.metadatumType = this.filter.metadatum.metadata_type_object && this.filter.metadatum.metadata_type_object.className ? this.filter.metadatum.metadata_type_object.className : this.metadatumType;
    },
    methods: {
        resetPage() {
            this.$eventBusSearch.resetPageOnStore()
        }
    }
};

export const dynamicFilterTypeMixin = {
    data () {
        return {
            getOptionsValuesCancel: undefined,
            isLoadingOptions: false,
            facetSearchPage: 1
        }
    },
    emits: ['update-parent-collapse'],
    computed: {
        ...mapGetters('search', {
            'facetsFromItemSearch': 'getFacets'
        }),
    },
    watch: {
        isLoadingItems: {
            handler() {
                if (this.isUsingElasticSearch )
                    this.isLoadingOptions = this.isLoadingItems;
            },
            immediate: true
        },
        isLoadingOptions: {
            handler(newVal, oldVal) {
                if (oldVal === true && newVal === false)
                    this.$nextTick(() => this.tryRestoreFocus());
            }
        }
    },
    methods: {
        tryRestoreFocus() {
            if (!this.$eventBusSearch || !this.filter || !this.filter.id)
                return;
            const value = this.$eventBusSearch.getAndClearFocusRestoreRequest(this.filter.id);
            if (value === undefined)
                return;
            this.$nextTick(() => {
                let el = null;
                if (typeof this.getFocusRestoreElement === 'function')
                    el = this.getFocusRestoreElement(value);
                else if (this.$el) {
                    const candidates = this.$el.querySelectorAll('[data-filter-option-value]');
                    for (let i = 0; i < candidates.length; i++) {
                        if (candidates[i].getAttribute('data-filter-option-value') === value) {
                            el = candidates[i];
                            break;
                        }
                    }
                }
                if (el && typeof el.focus === 'function')
                    el.focus();
            });
        },
        getValuesPlainText({ metadatumId, search, isRepositoryLevel, valuesToIgnore, offset, number, isInCheckboxModal, getSelected = '0', countItems = true }) {

            if (isInCheckboxModal || search || !this.isUsingElasticSearch) {
                
                const source = axios.CancelToken.source();
 
                let currentQuery  = JSON.parse(JSON.stringify(this.query));
                if (currentQuery.fetch_only != undefined) {
                    delete currentQuery.fetch_only;
                    // for (let key of Object.keys(currentQuery.fetch_only)) {
                    //     if (currentQuery.fetch_only[key] == null)
                    //         delete currentQuery.fetch_only[key];
                    // }
                }
                let query_items = { 'current_query': currentQuery };

                let url = '';
                if (isRepositoryLevel)
                    url = `/facets/${metadatumId}?getSelected=${getSelected}&`;
                else {
                    if (this.filter.collection_id == 'default' && this.currentCollectionId)
                        url = `/collection/${this.currentCollectionId}/facets/${metadatumId}?getSelected=${getSelected}&`;
                    else
                        url = `/collection/${this.filter.collection_id}/facets/${metadatumId}?getSelected=${getSelected}&`;
                }

                if (offset != undefined && number != undefined) {
                    if (!this.isUsingElasticSearch)
                        url += `offset=${offset}&number=${number}&`;
                    else 
                        url += `last_term=${offset}&number=${number}&`;
                }  

                if (search && offset != undefined && number != undefined)
                    url += `search=${search}&` + qs.stringify(query_items);
                else if (search)
                    url += `search=${search}&` + qs.stringify(query_items);
                else
                    url += qs.stringify(query_items);

                if (countItems != undefined && countItems === false)
                    url += '&count_items=0';
 
                this.isLoadingOptions = true;
                
                return new Object ({
                    request: 
                        new Promise((resolve, reject) => {
                            axios.tainacanApi.get(url, { cancelToken: source.token })
                                .then(res => {
                                    this.isLoadingOptions = false;

                                    if (res.data.values)
                                        this.prepareOptionsForPlainText(res.data.values, search, valuesToIgnore, isInCheckboxModal);
                                    else
                                        this.prepareOptionsForPlainText(res.data, search, valuesToIgnore, isInCheckboxModal);
                                
                                    resolve(res);
                                })
                                .catch((thrown) => {
                                    if (axios.isCancel(thrown)) {
                                        console.log('Request canceled: ', thrown.message);
                                    } else {
                                        this.isLoadingOptions = false;
                                    }
                                    reject(thrown);
                                })
                            }),
                    source: source
                });

            } else {
                let callback = new Promise((resolve) => {
                    let values = [];
                    for (const facet in this.facetsFromItemSearch) {
                        if (facet == this.filter.id) {
                            values = this.facetsFromItemSearch[facet];
                            this.prepareOptionsForPlainText(values, search, valuesToIgnore, isInCheckboxModal);
                            this.$emit('update-parent-collapse', values.length > 0 );
                        }
                    }   
                    resolve({ data: { values }, fromAggregations: true });
                });
                return new Object ({
                    request: callback
                });
            }
        },
        getValuesTaxonomy({ metadatumId, isRepositoryLevel, number, search, offset, valuesToIgnore, parent, getSelected = '1' }) {
            // Taginput/search infinite scroll: offset without parent stays in search mode.
            // Checkbox sidebar progressive load passes parent (usually 0) to keep root terms + getSelected.
            const isSearchMode = ((search !== undefined && search !== null) || offset !== undefined) && parent === undefined;

            if (isSearchMode) {
                const source = axios.CancelToken.source();
                let currentQuery = JSON.parse(JSON.stringify(this.query));
                if (currentQuery.fetch_only != undefined)
                    delete currentQuery.fetch_only;
                const query_items = { 'current_query': currentQuery };
                if (search !== undefined && search !== null)
                    query_items.search = search;
                if (offset !== undefined)
                    query_items.offset = offset;
                if (number !== undefined)
                    query_items.number = number;
                let url = '';
                if (isRepositoryLevel)
                    url = `/facets/${metadatumId}?order=asc&` + qs.stringify(query_items);
                else {
                    if (this.filter.collection_id == 'default' && this.currentCollectionId)
                        url = `/collection/${this.currentCollectionId}/facets/${metadatumId}?order=asc&` + qs.stringify(query_items);
                    else
                        url = `/collection/${this.filter.collection_id}/facets/${metadatumId}?order=asc&` + qs.stringify(query_items);
                }
                this.isLoadingOptions = true;
                return {
                    request: new Promise((resolve, reject) => {
                        axios.tainacanApi.get(url, { cancelToken: source.token })
                            .then(res => {
                                this.isLoadingOptions = false;
                                if (typeof this.prepareOptionsForTaxonomySearch === 'function')
                                    this.prepareOptionsForTaxonomySearch(res, search, valuesToIgnore);
                                resolve(res);
                            })
                            .catch((thrown) => {
                                if (!axios.isCancel(thrown))
                                    this.isLoadingOptions = false;
                                reject(thrown);
                            });
                    }),
                    source: source
                };
            }

            // Sidebar list (and progressive "View more"): dedicated facets request when not using ES aggregations,
            // or when parent is explicitly set to force an API call (e.g. ElasticPress load more).
            if (!this.isUsingElasticSearch || parent !== undefined) {
                const source = axios.CancelToken.source();
                let currentQuery = JSON.parse(JSON.stringify(this.query));
                if (currentQuery.fetch_only != undefined)
                    delete currentQuery.fetch_only;
                const query_items = { 'current_query': currentQuery };
                const parentId = parent !== undefined ? parent : 0;
                const offsetValue = offset !== undefined ? offset : 0;
                let url = '';
                if (isRepositoryLevel)
                    url = `/facets/${metadatumId}?getSelected=${getSelected}&order=asc&parent=${parentId}&number=${number}&offset=${offsetValue}&` + qs.stringify(query_items);
                else {
                    if (this.filter.collection_id == 'default' && this.currentCollectionId)
                        url = `/collection/${this.currentCollectionId}/facets/${metadatumId}?getSelected=${getSelected}&order=asc&parent=${parentId}&number=${number}&offset=${offsetValue}&` + qs.stringify(query_items);
                    else
                        url = `/collection/${this.filter.collection_id}/facets/${metadatumId}?getSelected=${getSelected}&order=asc&parent=${parentId}&number=${number}&offset=${offsetValue}&` + qs.stringify(query_items);
                }
                this.isLoadingOptions = true;
                return {
                    request: new Promise((resolve, reject) => {
                        axios.tainacanApi.get(url, { cancelToken: source.token })
                            .then(res => {
                                this.isLoadingOptions = false;
                                this.prepareOptionsForTaxonomy(res.data.values ? res.data.values : res.data);
                                resolve(res);
                            })
                            .catch((thrown) => {
                                if (!axios.isCancel(thrown))
                                    this.isLoadingOptions = false;
                                reject(thrown);
                            });
                    }),
                    source: source
                };
            }
            const callback = new Promise((resolve) => {
                let values = [];
                for (const facet in this.facetsFromItemSearch) {
                    if (facet == this.filter.id) {
                        const facetData = this.facetsFromItemSearch[facet];
                        if (Array.isArray(facetData)) {
                            values = facetData;
                            this.prepareOptionsForTaxonomy(facetData);
                            this.$emit('update-parent-collapse', facetData.length > 0);
                        } else {
                            const arr = Object.values(facetData);
                            values = arr;
                            this.prepareOptionsForTaxonomy(arr);
                            this.$emit('update-parent-collapse', arr.length > 0);
                        }
                    }
                }
                resolve({ data: { values }, fromAggregations: true });
            });
            return { request: callback };
        },
        getValuesRelationship({ search, isRepositoryLevel, valuesToIgnore, offset, number, isInCheckboxModal, getSelected = '0', countItems = true }) {
            
            if (isInCheckboxModal || search || !this.facetsFromItemSearch || Object.values(this.facetsFromItemSearch).length <= 0) {

                const source = axios.CancelToken.source();

                let currentQuery  = JSON.parse(JSON.stringify(this.query));
                    if (currentQuery.fetch_only != undefined) {
                        delete currentQuery.fetch_only;
                    //     for (let key of Object.keys(currentQuery.fetch_only)) {
                    //     if (currentQuery.fetch_only[key] == null)
                    //         delete currentQuery.fetch_only[key];
                    // }
                }
                let query_items = { 'current_query': currentQuery };

                let url = '';
                
                if (isRepositoryLevel)
                    url = `/facets/${this.filter.metadatum.metadatum_id}?getSelected=${getSelected}&`;
                else {
                    if (this.filter.collection_id == 'default' && this.currentCollectionId)
                        url = `/collection/${this.currentCollectionId}/facets/${this.filter.metadatum.metadatum_id}?getSelected=${getSelected}&`;
                    else
                        url = `/collection/${this.filter.collection_id}/facets/${this.filter.metadatum.metadatum_id}?getSelected=${getSelected}&`;
                }     
                
                if (offset != undefined && number != undefined)
                    url += `offset=${offset}&number=${number}`;
                else
                    url += `nopaging=1`;

                if (search)
                    url += `&search=${search}`;

                if (countItems != undefined && countItems === false)
                    url += '&count_items=0';

                this.isLoadingOptions = true;

                return new Object ({
                    request:
                        new Promise((resolve, reject) => {
                            axios.tainacanApi.get(url + '&' + qs.stringify(query_items))
                                .then(res => {

                                    this.isLoadingOptions = false;
                                    
                                    if (res.data.values)
                                        this.prepareOptionsForRelationship(res.data.values, search, valuesToIgnore, isInCheckboxModal);
                                    else
                                        this.prepareOptionsForRelationship(res.data, search, valuesToIgnore, isInCheckboxModal);
                                
                                    resolve(res);
                                })
                                .catch((thrown) => {
                                    if (axios.isCancel(thrown)) {
                                        console.log('Request canceled: ', thrown.message);
                                    } else {
                                        this.isLoadingOptions = false;
                                    }
                                    reject(thrown);
                                })
                            }),
                    source: source
                });
            } else {
                let callback = new Promise((resolve) => {
                    let values = [];
                    for (const facet in this.facetsFromItemSearch) {
                        if (facet == this.filter.id) {
                            values = this.facetsFromItemSearch[facet];
                            this.prepareOptionsForRelationship(values, search, valuesToIgnore, isInCheckboxModal);
                            this.$emit('update-parent-collapse', values.length > 0 );
                        }    
                    }
                    resolve({ data: { values }, fromAggregations: true });
                });
                return new Object ({
                    request: callback
                });
            }
        },
        prepareOptionsForPlainText(metadata, search, valuesToIgnore, isInCheckboxModal) {

            let sResults = [];
            let opts = [];

            if (!Array.isArray(metadata))
                metadata = Object.values(metadata);

            for (let metadatum of metadata) {
                if (valuesToIgnore != undefined && valuesToIgnore.length > 0) {
                    let indexToIgnore = valuesToIgnore.findIndex(value => value == metadatum.value);

                    if (search && isInCheckboxModal) {
                        sResults.push({
                            label: metadatum.label,
                            value: metadatum.value,
                            total_items: metadatum.total_items
                        });
                    } else if (indexToIgnore < 0) {
                        opts.push({
                            label: metadatum.label,
                            value: metadatum.value,
                            total_items: metadatum.total_items
                        });
                    }
                } else {
                    if (search && isInCheckboxModal) {
                        sResults.push({
                            label: metadatum.label,
                            value: metadatum.value,
                            total_items: metadatum.total_items
                        });
                    } else {
                        opts.push({
                            label: metadatum.label,
                            value: metadatum.value,
                            total_items: metadatum.total_items
                        });
                    }
                }
            }
            
            if ( this.shouldAddOptions === true && this.searchResults && this.searchResults.length )
                this.searchResults = this.searchResults.concat(sResults);
            else
                this.searchResults = sResults;

            if ( opts ) {
                if (this.shouldAddOptions === true && this.options && this.options.length)
                    this.options = this.options.concat(opts)
                else 
                    this.options = opts;
            }
            else if ( !search )
                this.noMorePage = 1;

            if ( this.options.length < this.maxNumOptionsCheckboxList && !search )
                this.noMorePage = 1;
            
        },
        prepareOptionsForRelationship(items, search, valuesToIgnore, isInCheckboxModal) {

            let sResults = [];
            let opts = [];      

            if (items.length > 0) {
                for (let item of items) {
                    if (valuesToIgnore != undefined && valuesToIgnore.length > 0) {
                        let indexToIgnore = valuesToIgnore.findIndex(value => value == item.value);

                        if (search && isInCheckboxModal) {
                            sResults.push({
                                label: item.label,
                                value: item.value,
                                img: item.thumbnail ? this.$thumbHelper.getSrc(item['thumbnail'], 'tainacan-small') : (item.img ? item.img : ''),
                                total_items: item.total_items
                            });
                        } else if (indexToIgnore < 0) {
                            opts.push({
                                label: item.label,
                                value: item.value,
                                img: item.thumbnail ? this.$thumbHelper.getSrc(item['thumbnail'], 'tainacan-small') : (item.img ? item.img : ''),
                                total_items: item.total_items
                            });
                        }
                    } else {
                        if (search && isInCheckboxModal) {
                            sResults.push({
                                label: item.label,
                                value: item.value,
                                img: item.thumbnail ? this.$thumbHelper.getSrc(item['thumbnail'], 'tainacan-small') : (item.img ? item.img : ''),
                                total_items: item.total_items
                            });
                        } else {
                            opts.push({
                                label: item.label,
                                value: item.value,
                                img: item.thumbnail ? this.$thumbHelper.getSrc(item['thumbnail'], 'tainacan-small') : (item.img ? item.img : ''),
                                total_items: item.total_items
                            });
                        }
                    }
                }
            }

            if ( this.shouldAddOptions === true && this.searchResults && this.searchResults.length )
                this.searchResults = this.searchResults.concat(sResults);
            else
                this.searchResults = sResults;

            if ( opts ) {
                if (this.shouldAddOptions === true && this.options && this.options.length)
                     this.options = this.options.concat(opts)
                else 
                    this.options = opts;
            }
            else if ( !search )
                this.noMorePage = 1;
        

            if ( this.options.length < this.maxNumOptionsCheckboxList )
                this.noMorePage = 1;
        
        },
    },
    beforeUnmount() {
        // Cancels previous Request
        if (this.getOptionsValuesCancel != undefined)
            this.getOptionsValuesCancel.cancel('Facet search Canceled.');
    },
};
/**
 * Progressive "View more" / "View all" behavior for checkbox filter sidebars.
 * "View more" until X pages, then replace with View all modal. X=0 keeps modal-only.
 */
export const progressiveCheckboxMixin = {
    data() {
        return {
            nextFacetOffset: 0,
            viewMoreClicks: 0,
            hasMoreOptions: false,
            isLoadingMore: false,
            viewMoreLiveMessage: ''
        }
    },
    computed: {
        maxViewMorePages() {
            const options = this.filterTypeOptions && !Array.isArray(this.filterTypeOptions)
                ? this.filterTypeOptions
                : (this.filter && this.filter.filter_type_options ? this.filter.filter_type_options : {});
            const parsed = parseInt(options.max_view_more_pages, 10);
            return isNaN(parsed) || parsed < 0 ? 0 : parsed;
        },
        facetPageSize() {
            const parsed = parseInt(this.filter && this.filter.max_options, 10);
            return isNaN(parsed) || parsed < 1 ? 4 : parsed;
        },
        forceViewAllModalOnly() {
            return typeof this.shouldForceViewAllModalOnly === 'function' && this.shouldForceViewAllModalOnly();
        },
        showViewMoreButton() {
            if (this.forceViewAllModalOnly || this.maxViewMorePages <= 0 || !this.hasMoreOptions)
                return false;
            return this.viewMoreClicks < this.maxViewMorePages;
        },
        showViewAllButton() {
            if (this.forceViewAllModalOnly)
                return this.hasMoreOptions;
            if (!this.hasMoreOptions)
                return false;
            if (this.maxViewMorePages <= 0)
                return true;
            return this.viewMoreClicks >= this.maxViewMorePages;
        }
    },
    methods: {
        resetProgressiveState() {
            this.nextFacetOffset = 0;
            this.viewMoreClicks = 0;
            this.hasMoreOptions = false;
            this.isLoadingMore = false;
            this.shouldAddOptions = false;
        },
        updateProgressiveStateFromResponse(res, { isInitial = false, fromAggregations = false } = {}) {
            const pageSize = this.facetPageSize;
            const values = res && res.data
                ? (res.data.values ? res.data.values : (Array.isArray(res.data) ? res.data : []))
                : [];

            if (fromAggregations) {
                this.nextFacetOffset = pageSize;
                this.hasMoreOptions = values.length >= pageSize;
            } else if (!this.isUsingElasticSearch && res && res.headers && res.headers['x-wp-total'] !== undefined) {
                const total = Number(res.headers['x-wp-total']);
                this.nextFacetOffset = isInitial
                    ? pageSize
                    : Number(this.nextFacetOffset || 0) + pageSize;
                this.hasMoreOptions = total > this.nextFacetOffset;
            } else if (this.isUsingElasticSearch && res && res.data && res.data.last_term) {
                const lastTerm = res.data.last_term.es_term;
                this.nextFacetOffset = lastTerm || '';
                this.hasMoreOptions = !!lastTerm;
            } else {
                this.nextFacetOffset = isInitial
                    ? pageSize
                    : Number(this.nextFacetOffset || 0) + pageSize;
                this.hasMoreOptions = values.length >= pageSize;
            }

            if (isInitial)
                this.viewMoreClicks = 0;
        },
        focusFilterOptionByValue(value) {
            if (!this.$el || value === undefined || value === null)
                return false;

            const candidates = this.$el.querySelectorAll('[data-filter-option-value]');
            const valueString = String(value);
            for (let i = 0; i < candidates.length; i++) {
                if (candidates[i].getAttribute('data-filter-option-value') === valueString) {
                    candidates[i].focus();
                    return true;
                }
            }
            return false;
        },
        getFirstVisibleOptionFromIndex(startIndex) {
            if (!Array.isArray(this.options) || startIndex >= this.options.length)
                return null;

            for (let i = startIndex; i < this.options.length; i++) {
                if (!this.options[i].isChild)
                    return this.options[i];
            }
            return null;
        },
        focusProgressiveLoadMoreControl() {
            if (this.$refs.viewMoreButton && typeof this.$refs.viewMoreButton.focus === 'function') {
                this.$refs.viewMoreButton.focus();
                return true;
            }
            if (this.$refs.viewAllButton && typeof this.$refs.viewAllButton.focus === 'function') {
                this.$refs.viewAllButton.focus();
                return true;
            }
            return false;
        },
        handleFocusAfterLoadMore(previousOptionsCount) {
            const addedCount = this.options.length - previousOptionsCount;

            if (addedCount > 0) {
                const firstNewOption = this.getFirstVisibleOptionFromIndex(previousOptionsCount);
                if (this.$i18n)
                    this.viewMoreLiveMessage = this.$i18n.getWithVariables('info_%s_filter_options_loaded', [addedCount]);

                this.$nextTick(() => {
                    if (firstNewOption && this.focusFilterOptionByValue(firstNewOption.value))
                        return;
                    this.focusProgressiveLoadMoreControl();
                });
                return;
            }

            this.$nextTick(() => this.focusProgressiveLoadMoreControl());
        }
    }
};
