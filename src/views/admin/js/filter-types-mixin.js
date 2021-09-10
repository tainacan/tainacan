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
        currentCollectionId: Boolean
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
    computed: {
        facetsFromItemSearch() {
            return this.getFacets();
        }
    },
    watch: {
        isLoadingItems: {
            handler() {
                if (this.isUsingElasticSearch )
                    this.isLoadingOptions = this.isLoadingItems;
            },
            immediate: true
        }
    },
    methods: {
        ...mapGetters('search', [
            'getFacets'
        ]),
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
                            axios.tainacan.get(url, { cancelToken: source.token })
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
                    for (const facet in this.facetsFromItemSearch) {
                        if (facet == this.filter.id) {
                            this.prepareOptionsForPlainText(this.facetsFromItemSearch[facet], search, valuesToIgnore, isInCheckboxModal);
                            this.$emit('updateParentCollapse', this.facetsFromItemSearch[facet].length > 0 );
                        }
                    }   
                    resolve();
                });
                return new Object ({
                    request: callback
                });
            }
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
                            axios.tainacan.get(url + '&' + qs.stringify(query_items))
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
                    for (const facet in this.facetsFromItemSearch) {
                        if (facet == this.filter.id) {
                            this.prepareOptionsForRelationship(this.facetsFromItemSearch[facet], search, valuesToIgnore, isInCheckboxModal);
                            this.$emit('updateParentCollapse', this.facetsFromItemSearch[facet].length > 0 );
                        }    
                    }
                    resolve();
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
            
            if (this.shouldAddOptions === true && this.searchResults && this.searchResults.length)
                this.searchResults = this.searchResults.concat(sResults);
            else
                this.searchResults = sResults;

            if (opts) {
                if (this.shouldAddOptions === true && this.options && this.options.length)
                    this.options = this.options.concat(opts)
                else 
                    this.options = opts;
            }
            else if(!search)
                this.noMorePage = 1;

            if (this.options.length < this.maxNumOptionsCheckboxList && !search)
                this.noMorePage = 1;

            if (this.filter.max_options && this.options.length >= this.filter.max_options) {
                let showViewAllButton = true;

                if (this.options.length === this.filter.max_options){
                    this.options[this.filter.max_options-1].showViewAllButton = showViewAllButton;
                } else {
                    this.options[this.options.length-1].showViewAllButton = showViewAllButton;
                }
            }
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

            if (this.shouldAddOptions === true && this.searchResults && this.searchResults.length)
                this.searchResults = this.searchResults.concat(sResults);
            else
                this.searchResults = sResults;

            if (opts) {
                if (this.shouldAddOptions === true && this.options && this.options.length)
                     this.options = this.options.concat(opts)
                else 
                    this.options = opts;
            }
            else if(!search)
                this.noMorePage = 1;
        

            if (this.options.length < this.maxNumOptionsCheckboxList)
                this.noMorePage = 1;
            
            if (this.filter.max_options && this.options.length >= this.filter.max_options) {
                let showViewAllButton = true;

                if(this.options.length === this.filter.max_options){
                    this.options[this.filter.max_options-1].showViewAllButton = showViewAllButton;
                } else {
                    this.options[this.options.length-1].showViewAllButton = showViewAllButton;
                }
            }
        },
    },
    beforeDestroy() {
        // Cancels previous Request
        if (this.getOptionsValuesCancel != undefined)
            this.getOptionsValuesCancel.cancel('Facet search Canceled.');
    },
};