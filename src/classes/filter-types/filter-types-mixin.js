import qs from 'qs';
import axios from '../../js/axios/axios';
import { mapGetters } from 'vuex';

export const filter_type_mixin = {
    data () {
        return {
            thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png',
            getOptionsValuesCancel: undefined,
            isUsingElasticSearch: tainacan_plugin.wp_elasticpress == "1" ? true : false,
            isLoadingOptions: false
        }
    },
    props: {
        filter: {
            type: Object // concentrate all attributes metadatum id and type
        },
        metadatum_id: [Number], // not required, but overrides the filter metadatum id if is set
        collection_id: [Number], // not required, but overrides the filter metadatum id if is set
        id: '',
        query: {}
    },
    created() {
        // We listen to event, but reload event if hasFiltered is negative, as 
        // an empty query also demands filters reloading.
        this.$eventBusSearch.$on('hasFiltered', this.reloadOptionsDueToFiltering);
    },
    computed: {
        facetsFromItemSearch() {
            return this.getFacets();
        }
    },
    methods: {
        ...mapGetters('search', [
            'getFacets'
        ]),
        getValuesPlainText(metadatumId, search, isRepositoryLevel, valuesToIgnore, offset, number, isInCheckboxModal, getSelected = '0') {

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
                if (isRepositoryLevel  || this.filter.collection_id == 'filter_in_repository')
                    url = `/facets/${metadatumId}?getSelected=${getSelected}&`;
                else
                    url = `/collection/${this.collection}/facets/${metadatumId}?getSelected=${getSelected}&`;

                if (offset != undefined && number != undefined) {
                    if (!this.isUsingElasticSearch)
                        url += `offset=${offset}&number=${number}&`;
                    else 
                        url += `last_term=${offset}&number=${number}&`;
                }  

                if(search && offset != undefined && number != undefined){
                    url += `search=${search}&` + qs.stringify(query_items);
                } else if(search){
                    url += `search=${search}&` + qs.stringify(query_items);
                } else {
                    url += qs.stringify(query_items);
                }
                
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
                                
                                    resolve(res.data);
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
                        if (facet == this.filter.id)
                            this.prepareOptionsForPlainText(this.facetsFromItemSearch[facet], search, valuesToIgnore, isInCheckboxModal);
                        }   
                    resolve();
                });
                return new Object ({
                    request: callback
                });
            }
        },
        getValuesRelationship(search, isRepositoryLevel, valuesToIgnore, offset, number, isInCheckboxModal, getSelected = '0') {
            
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
                if (isRepositoryLevel  || this.filter.collection_id == 'filter_in_repository')
                    url =  '/facets/' + this.filter.metadatum.metadatum_id + `?getSelected=${getSelected}&`; 
                else
                    url =  '/collection/' + this.filter.collection_id + '/facets/' + this.filter.metadatum.metadatum_id + `?getSelected=${getSelected}&`;
                    
                if(offset != undefined && number != undefined){
                    url += `offset=${offset}&number=${number}`;
                } else {
                    url += `nopaging=1`
                }

                if(search){
                    url += `&search=${search}`;
                }

                this.isLoadingOptions = true;

                return new Object ({
                    request:
                        new Promise((resolve, reject) => {
                            axios.tainacan.get(url + '&fetch_only=thumbnail,title,id&' + qs.stringify(query_items))
                                .then(res => {
                                    this.isLoadingOptions = false;
                                    
                                    if (res.data.values)
                                        this.prepareOptionsForRelationship(res.data.values, search, valuesToIgnore, isInCheckboxModal);
                                    else
                                        this.prepareOptionsForRelationship(res.data, search, valuesToIgnore, isInCheckboxModal);
                                
                                    resolve(res.data);
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

            this.searchResults = sResults;

            if (opts.length)
                this.options = opts;
            else if(!search)
                this.noMorePage = 1;

            if(this.options.length < this.maxNumOptionsCheckboxList && !search)
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
                                total_items: item.total_items
                            });
                        } else if (indexToIgnore < 0) {
                            opts.push({
                                label: item.label,
                                value: item.value,
                                img: (item.img ? item.img : this.thumbPlaceholderPath),
                                total_items: item.total_items
                            });
                        }
                    } else {
                        if (search && isInCheckboxModal) {
                            sResults.push({
                                label: item.label,
                                value: item.value,
                                img: (item.img ? item.img : this.thumbPlaceholderPath),
                                total_items: item.total_items
                            });
                        } else {
                            opts.push({
                                label: item.label,
                                value: item.value,
                                img: (item.img ? item.img : this.thumbPlaceholderPath),
                                total_items: item.total_items
                            });
                        }
                    }
                }
            }

            this.searchResults = sResults;

            if (opts.length)
                this.options = opts;
            else
                this.noMorePage = 1;
            

            if(this.options.length < this.maxNumOptionsCheckboxList)
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
        reloadOptionsDueToFiltering() {
            if (typeof this.loadOptions == "function")
                this.loadOptions(true);
        }
    },
    beforeDestroy() {
        // Cancels previous Request
        if (this.getOptionsValuesCancel != undefined)
            this.getOptionsValuesCancel.cancel('Facet search Canceled.');
    
        this.$eventBusSearch.$off('hasFiltered', this.reloadOptionsDueToFiltering);
    },
};