export default {

    install(Vue, options = {}) {

        Vue.prototype.$eventBusSearch = new Vue({
            router: options.router,
            store: options.store,
            data: {
                errors : [],
                query: {},
                collectionId: undefined,
                defaultOrder: 'ASC',
                defaultOrderBy: 'date',
                taxonomy: undefined,
                termId: undefined,
                searchCancel: undefined
            },
            created() {
                this.$on('input', data => {
                    if (data.taxonomy)
                        this.addTaxquery(data);
                    else
                        this.addMetaquery(data);
                });

                this.$root.$on('closeAdvancedSearch', () => {
                    this.$store.dispatch('search/setPage', 1);
                    
                    this.performAdvancedSearch({});
                });

                this.$root.$on('performAdvancedSearch', advancedSearchQuery => {
                    this.$store.dispatch('search/setPage', 1);
                    this.performAdvancedSearch(advancedSearchQuery);

                    this.updateURLQueries();
                });
            },
            watch: {
                '$route'  (to, from) {
                    
                    // Should set Collection ID from URL only when in admin.
                    if (this.$route.name == 'CollectionItemsPage' || this.$route.name == 'ItemsPage')
                        this.collectionId = !this.$route.params.collectionId ? this.$route.params.collectionId : parseInt(this.$route.params.collectionId);

                    // Fills the URL with appropriate default values in case a query is not passed
                    if (this.$route.name == null || this.$route.name == undefined || this.$route.name == 'CollectionItemsPage' || this.$route.name == 'ItemsPage') {
                        
                        // Items Per Page
                        if (this.$route.query.perpage == undefined || to.params.collectionId != from.params.collectionId) {
                            let perPageKey = (this.collectionId != undefined ? 'items_per_page_' + this.collectionId : 'items_per_page');
                            let perPageValue = this.$userPrefs.get(perPageKey);

                            if (perPageValue)
                                this.$route.query.perpage = perPageValue;
                            else {
                                this.$route.query.perpage = 12;
                                this.$userPrefs.set(perPageKey, 12);
                            }
                        }    
                        
                        // Page
                        if (this.$route.query.paged == undefined || to.params.collectionId != from.params.collectionId)
                            this.$route.query.paged = 1;
                        
                        // Order (ASC, DESC)
                        if (this.$route.query.order == undefined || to.params.collectionId != from.params.collectionId) {
                            let orderKey = (this.collectionId != undefined ? 'order_' + this.collectionId : 'order');
                            let orderValue = this.$userPrefs.get(orderKey) ? this.$userPrefs.get(orderKey) : this.defaultOrder;
                            
                            if (orderValue)
                                this.$route.query.order = orderValue;
                            else {
                                this.$route.query.order = 'DESC';
                                this.$userPrefs.set(orderKey, 'DESC');
                            }
                        }
                        
                        // Order By (required extra work to deal with custom metadata ordering)
                        if (this.$route.query.orderby == undefined || (to.params.collectionId != from.params.collectionId)) {
                            let orderByKey = (this.collectionId != undefined ? 'order_by_' + this.collectionId : 'order_by');
                            let orderBy = this.$userPrefs.get(orderByKey) ? this.$userPrefs.get(orderByKey) : this.defaultOrderBy;
                          
                            if (orderBy && orderBy != 'name') {
                               
                                // Previously was stored as a metadata object, now it is a orderby object
                                if (orderBy.slug || typeof orderBy == 'string')
                                    orderBy = this.$orderByHelper.getOrderByForMetadatum(orderBy);

                                if (orderBy.orderby)
                                    Object.keys(orderBy).forEach((paramKey) => {
                                        this.$route.query[paramKey] = orderBy[paramKey];
                                    });
                                else
                                    this.$route.query.orderby = 'date'
                                
                            } else {
                                this.$route.query.orderby = 'date';
                                this.$userPrefs.set(orderByKey, { 
                                    slug: 'creation_date',
                                    name: this.$i18n.get('label_creation_date')
                                }).catch(() => { });
                            }
                        } else if ( this.$route.query.orderby == 'creation_date' ) { // Fixes old usage of creation_date
                            this.$route.query.orderby = 'date'
                        }

                        // Theme View Modes
                        if ((this.$route.name == null || this.$route.name == undefined ) && 
                            this.$route.name != 'CollectionItemsPage' && this.$route.name != 'ItemsPage' &&
                            (this.$route.query.view_mode == undefined || to.params.collectionId != from.params.collectionId)
                        ) {
                            
                            let viewModeKey = (this.collectionId != undefined ? 'view_mode_' + this.collectionId : 'view_mode');
                            let viewModeValue = this.$userPrefs.get(viewModeKey);

                            if (viewModeValue)
                                this.$route.query.view_mode = viewModeValue;
                            else {
                                this.$route.query.view_mode = 'table';
                                this.$userPrefs.set(viewModeKey, 'table');
                            }
                        }

                        // Emit slideshow-from to start this view mode from index
                        if (this.$route.query.view_mode != 'slideshow' && this.$route.query['slideshow-from'] !== null && this.$route.query['slideshow-from'] !== undefined && this.$route.query['slideshow-from'] !== false)
                            this.$emit('start-slideshow-from-item', this.$route.query['slideshow-from']);

                        // Admin View Modes
                        if (this.$route.name != null && this.$route.name != undefined  && 
                            (this.$route.name == 'CollectionItemsPage' || this.$route.name == 'ItemsPage') &&
                            (this.$route.query.admin_view_mode == undefined || to.params.collectionId != from.params.collectionId)
                        ) {
                            let adminViewModeKey = (this.collectionId != undefined ? 'admin_view_mode_' + this.collectionId : 'admin_view_mode');
                            let adminViewModeValue = this.$userPrefs.get(adminViewModeKey);

                            if (adminViewModeValue)
                                this.$route.query.admin_view_mode = adminViewModeValue;
                            else {
                                this.$route.query.admin_view_mode = 'table';
                                this.$userPrefs.set(adminViewModeKey, 'table');
                            }
                        }
                        
                        // Advanced Search
                        if (this.$route.query && this.$route.query.advancedSearch){
                            this.$store.dispatch('search/set_advanced_query', this.$route.query);
                        } else {
                            this.$store.dispatch('search/set_postquery', this.$route.query);
                        }
                        
                        // Checks current metaqueries and taxqueries to alert filters that should reload
                        // For some reason, this process is not working accessing to.query, so we need to check the path string. 
                        const oldQueryString = from.fullPath.replace(from.path + '?', '');
                        const newQueryString = to.fullPath.replace(from.path + '?', '');
                        
                        const oldQueryArray = oldQueryString.split('&');
                        const newQueryArray = newQueryString.split('&');

                        const oldMetaQueryArray = oldQueryArray.filter(queryItem => queryItem.startsWith('metaquery'));
                        const newMetaQueryArray = newQueryArray.filter(queryItem => queryItem.startsWith('metaquery'));
                        const oldTaxQueryArray  = oldQueryArray.filter(queryItem => queryItem.startsWith('taxquery'));
                        const newTaxQueryArray  = newQueryArray.filter(queryItem => queryItem.startsWith('taxquery'));
                        const oldStatusArray    = oldQueryArray.filter(queryItem => queryItem.startsWith('status'));
                        const newStatusArray    = newQueryArray.filter(queryItem => queryItem.startsWith('status'));
                        const oldSearchQuery    = oldQueryArray.filter(queryItem => queryItem.startsWith('search'));
                        const newSearchQuery    = newQueryArray.filter(queryItem => queryItem.startsWith('search'));

                        if (
                            JSON.stringify(oldMetaQueryArray) != JSON.stringify(newMetaQueryArray) ||
                            JSON.stringify(oldTaxQueryArray)  != JSON.stringify(newTaxQueryArray) ||
                            JSON.stringify(oldStatusArray)    != JSON.stringify(newStatusArray) ||
                            JSON.stringify(oldSearchQuery)    != JSON.stringify(newSearchQuery)
                        ) {
                            this.$emit('has-to-reload-facets', true);
                        }
                        
                        // Finally, loads items
                        if (to.fullPath != from.fullPath)
                            this.loadItems();
                    }
                }
            },
            methods: {
                performAdvancedSearch(data) {
                    this.$store.dispatch('search/set_advanced_query', data);
                    this.updateURLQueries();
                },
                addMetaquery( data ){
                    if ( data && data.collection_id ){
                        this.$store.dispatch('search/add_metaquery', data );
                    }
                    this.updateURLQueries();
                },
                addTaxquery( data ){
                    if ( data && data.collection_id ){
                        this.$store.dispatch('search/add_taxquery', data );
                    }
                    this.updateURLQueries();
                },
                removeMetaFromFilterTag(filterTag) {

                    if (filterTag.singleLabel != undefined || filterTag.label != undefined) {
                        
                        if (filterTag.argType !== 'postin') {
                            if (filterTag.taxonomy) {
                                this.$store.dispatch('search/remove_taxquery', {
                                    filterId: filterTag.filterId,
                                    label: filterTag.singleLabel ? filterTag.singleLabel : filterTag.label,
                                    isMultiValue: filterTag.singleLabel ? false : true,
                                    taxonomy: filterTag.taxonomy,
                                    value: filterTag.value
                                });
                            } else {
                                this.$store.dispatch('search/remove_metaquery', {
                                    filterId: filterTag.filterId,
                                    label: filterTag.singleLabel ? filterTag.singleLabel : filterTag.label,
                                    isMultiValue: filterTag.singleLabel ? false : true,
                                    metadatum_id: filterTag.metadatumId,
                                    value: filterTag.value
                                });
                            }
                        } else {
                            this.$store.dispatch('search/remove_postin');
                        }
                        this.$store.dispatch('search/removeFilterTag', filterTag);
                    }
                    this.updateURLQueries();
                },
                addFetchOnly( metadatum, ignorePrefs, metadatumIDs ) {
                    this.$store.dispatch('search/add_fetch_only', metadatum );
                    this.$store.dispatch('search/add_fetch_only_meta', metadatumIDs);
                    this.updateURLQueries();  
                    
                    if (!ignorePrefs) {
                        let prefsFetchOnly = this.collectionId ? `fetch_only_${this.collectionId}` : 'fetch_only';
                        let prefsFetchOnlyMeta = this.collectionId ? `fetch_only_meta_${this.collectionId}` : 'fetch_only_meta';

                        if (this.$userPrefs.get(prefsFetchOnly) != metadatum)
                            this.$userPrefs.set(prefsFetchOnly, metadatum);

                        if (this.$userPrefs.get(prefsFetchOnlyMeta) != metadatumIDs)
                            this.$userPrefs.set(prefsFetchOnlyMeta, metadatumIDs);
                    }
                },
                cleanFetchOnly() {
                    this.$store.dispatch('search/cleanFetchOnly');
                },
                removeFetchOnlyMeta( metadatum ){
                    this.$store.dispatch('search/remove_fetch_only_meta', metadatum );
                    this.updateURLQueries();             
                },
                setPage(page) {
                    this.$store.dispatch('search/setPage', page);
                    this.updateURLQueries();
                },
                resetPageOnStore() {
                    this.$store.dispatch('search/setPage', 1);
                },
                setItemsPerPage(itemsPerPage, shouldNotUpdatePrefs) {
                    this.$store.dispatch('search/setItemsPerPage', itemsPerPage);
                    this.updateURLQueries();

                    if (shouldNotUpdatePrefs == undefined || shouldNotUpdatePrefs == false) {
                        let prefsPerPage = this.collectionId != undefined ? 'items_per_page_' + this.collectionId : 'items_per_page';
                        if (this.$userPrefs.get(prefsPerPage) != itemsPerPage) {
                            this.$userPrefs.set(prefsPerPage, itemsPerPage)
                                .catch(() => {});
                        }
                    }
                },
                setOrderBy(orderBy) { 
                    let prefsOrderBy = this.collectionId != undefined ? 'order_by_' + this.collectionId : 'order_by';

                    if (orderBy.metakey) {
                        if (!this.$userPrefs.get(prefsOrderBy) || orderBy.metakey != this.$userPrefs.get(prefsOrderBy).metakey)
                            this.$userPrefs.set(prefsOrderBy, orderBy).catch(() => {});
                    } else {
                        if (orderBy != this.$userPrefs.get(prefsOrderBy))
                            this.$userPrefs.set(prefsOrderBy, orderBy).catch(() => {});
                    }
                    
                    this.$store.dispatch('search/setOrderBy', orderBy);
                    this.updateURLQueries();
                },
                setOrder(order) {
                    let prefsOrder = this.collectionId != undefined ? 'order_' + this.collectionId : 'order';
                    if (this.$userPrefs.get(prefsOrder) != order) {
                        this.$userPrefs.set(prefsOrder, order)
                            .catch(() => {});
                    }

                    this.$store.dispatch('search/setOrder', order);
                    this.updateURLQueries();
                },
                setStatus(status) {
                    this.$store.dispatch('search/setStatus', status);
                    this.updateURLQueries();
                },
                setTotalItems(totalItems) {
                    this.$store.dispatch('search/setTotalItems', totalItems);
                },
                setSentenceMode(sentenceMode) {
                    this.$store.dispatch('search/setSentenceMode', sentenceMode);
                },
                setSearchQuery(searchQuery) {
                    this.$store.dispatch('search/setSearchQuery', searchQuery);
                    this.updateURLQueries();
                },
                setViewMode(viewMode) {
                    this.$store.dispatch('search/setViewMode', viewMode);
                    this.updateURLQueries(); 
                    
                    let prefsViewMode = this.collectionId != undefined ? 'view_mode_' + this.collectionId : 'view_mode';
                    if(this.$userPrefs.get(prefsViewMode) != viewMode) {
                        this.$userPrefs.set(prefsViewMode, viewMode)
                            .catch(() => {});
                    }
                },
                setAdminViewMode(adminViewMode) {
                    this.$store.dispatch('search/setAdminViewMode', adminViewMode);
                    this.updateURLQueries();  

                    let prefsAdminViewMode = this.collectionId != undefined ? 'admin_view_mode_' + this.collectionId : 'admin_view_mode';
                    if (this.$userPrefs.get(prefsAdminViewMode) != adminViewMode) {
                        this.$userPrefs.set(prefsAdminViewMode, adminViewMode)
                            .catch(() => {  });
                    }
                },
                setInitialViewMode(viewMode) {
                    this.$store.dispatch('search/setViewMode', viewMode);
                    this.updateURLQueries(); 
                },
                setInitialAdminViewMode(adminViewMode) { 
                    this.$store.dispatch('search/setAdminViewMode', adminViewMode);
                    this.updateURLQueries();  
                },
                setSelectedItemsForIframe(selectedItems, singleSelection) {
 
                    if (singleSelection)
                        this.$store.dispatch('search/cleanSelectedItems');

                    this.$store.dispatch('search/setSelectedItems', selectedItems);
 
                    let currentSelectedItems = this.$store.getters['search/getSelectedItems'];

                    if (window.history.pushState) {
                        let searchParams = new URLSearchParams(window.location.search);
                        searchParams.delete('selecteditems');
                        for (let selectedItem of currentSelectedItems)
                            searchParams.append('selecteditems', selectedItem);

                        let newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?' + searchParams.toString() + window.location.hash;
                        window.history.pushState({path: newurl}, '', newurl);
                    }      
                },
                cleanSelectedItems() {
                    this.$store.dispatch('search/cleanSelectedItems');
                },
                filterBySelectedItems(selectedItems) {
                    this.$router.replace({ query: {} });
                    this.$router.replace({ query: { postin: selectedItems } });
                },
                highlightsItem(itemId) {
                    this.$store.dispatch('search/highlightsItem', itemId);
                    this.updateURLQueries();
                },
                updateURLQueries() {
                    this.$router.replace({ query: {} });
                    this.$router.replace({ query: this.$store.getters['search/getPostQuery'] });
                },
                updateStoreFromURL() {
                    this.$store.dispatch('search/set_postquery', this.$route.query);
                },
                loadItems() {

                    // Forces fetch_only to be filled before any search happens
                    if (this.$store.getters['search/getPostQuery']['fetch_only'] != undefined) {  

                        this.$emit( 'isLoadingItems', true);
                        // Cancels previous Request
                        if (this.searchCancel != undefined)
                            this.searchCancel.cancel('Item search Canceled.');
                        this.$store.dispatch('collection/fetchItems', {
                            'collectionId': this.collectionId,
                            'isOnTheme': (this.$route.name == null),
                            'termId': this.termId,
                            'taxonomy': this.taxonomy
                        }).then((resp) => {
                            // The actual fetch item request
                            resp.request.then((res) => {
                                this.$emit( 'isLoadingItems', false);
                                this.$emit( 'hasFiltered', res.hasFiltered);
                            })
                            .catch(() => {
                                this.$emit( 'isLoadingItems', false);
                            });

                            // Search Request Token for cancelling
                            this.searchCancel = resp.source;
                        });

                    }  
                },
                setCollectionId(collectionId) {
                    this.setTotalItems(null);
                    this.collectionId = collectionId;
                },
                setDefaultOrder(defaultOrder) {
                    this.defaultOrder = defaultOrder;
                },
                setDefaultOrderBy(defaultOrderBy) {
                    this.defaultOrderBy = defaultOrderBy;
                },
                setTerm(termId, taxonomy) {
                    this.termId = termId;
                    this.taxonomy = taxonomy;
                },
                clearAllFilters() {
                    this.$store.dispatch('search/cleanFilterTags');
                    this.$store.dispatch('search/cleanMetaQueries', { keepCollections: true });
                    this.$store.dispatch('search/cleanTaxQueries');
                    this.updateURLQueries();
                }
            }
        });
    }
}