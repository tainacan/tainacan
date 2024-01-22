import mitt from 'mitt';

export default {

    install(app) {
        
        const router = app.config.globalProperties.$router;
        const route = app.config.globalProperties.$route;
        const store = app.config.globalProperties.$store;
        const userPrefs = app.config.globalProperties.$userPrefs;

        const emitter = mitt();
        const bus = {
            collectionId: undefined,
            defaultOrder: 'ASC',
            defaultOrderBy: 'date',
            taxonomy: undefined,
            termId: undefined,
            searchCancel: undefined,
            performAdvancedSearch(data) {
                store.dispatch('search/setAdvancedQuery', data);
                this.updateURLQueries();
            },
            addMetaquery( data ){
                if ( data && data.collection_id ){
                    store.dispatch('search/addMetaquery', data );
                }
                this.updateURLQueries();
            },
            addTaxquery( data ){
                if ( data && data.collection_id ){
                    store.dispatch('search/addTaxquery', data );
                }
                this.updateURLQueries();
            },
            removeMetaFromFilterTag(filterTag) {

                if (filterTag.singleLabel != undefined || filterTag.label != undefined) {
                    
                    if (filterTag.argType !== 'postin') {
                        if (filterTag.taxonomy) {
                            store.dispatch('search/removeTaxQuery', {
                                filterId: filterTag.filterId,
                                label: filterTag.singleLabel ? filterTag.singleLabel : filterTag.label,
                                isMultiValue: filterTag.singleLabel ? false : true,
                                taxonomy: filterTag.taxonomy,
                                value: filterTag.value
                            });
                        } else {
                            store.dispatch('search/removeMetaQuery', {
                                filterId: filterTag.filterId,
                                label: filterTag.singleLabel ? filterTag.singleLabel : filterTag.label,
                                isMultiValue: filterTag.singleLabel ? false : true,
                                metadatum_id: filterTag.metadatumId,
                                value: filterTag.value
                            });
                        }
                    } else {
                        store.dispatch('search/removePostIn');
                    }
                    store.dispatch('search/removeFilterTag', filterTag);
                }
                this.updateURLQueries();
            },
            addFetchOnly( metadatum, ignorePrefs, metadatumIDs ) {
                store.dispatch('search/addFetchOnly', metadatum );
                store.dispatch('search/addFetchOnlyMeta', metadatumIDs);
                this.updateURLQueries();  
                
                if (!ignorePrefs) {
                    let prefsFetchOnly = this.collectionId ? `fetch_only_${this.collectionId}` : 'fetch_only';
                    let prefsFetchOnlyMeta = this.collectionId ? `fetch_only_meta_${this.collectionId}` : 'fetch_only_meta';

                    if (userPrefs.get(prefsFetchOnly) != metadatum)
                        userPrefs.set(prefsFetchOnly, metadatum);

                    if (userPrefs.get(prefsFetchOnlyMeta) != metadatumIDs)
                        userPrefs.set(prefsFetchOnlyMeta, metadatumIDs);
                }
            },
            cleanFetchOnly() {
                store.dispatch('search/cleanFetchOnly');
            },
            removeFetchOnlyMeta( metadatum ){
                store.dispatch('search/removeFetchOnlyMeta', metadatum );
                this.updateURLQueries();             
            },
            setPage(page) {
                store.dispatch('search/setPage', page);
                this.updateURLQueries();
            },
            resetPageOnStore() {
                store.dispatch('search/setPage', 1);
            },
            setItemsPerPage(itemsPerPage, shouldNotUpdatePrefs) {
                store.dispatch('search/setItemsPerPage', itemsPerPage);
                this.updateURLQueries();

                if (shouldNotUpdatePrefs == undefined || shouldNotUpdatePrefs == false) {
                    let prefsPerPage = this.collectionId != undefined ? 'items_per_page_' + this.collectionId : 'items_per_page';
                    if (userPrefs.get(prefsPerPage) != itemsPerPage) {
                        userPrefs.set(prefsPerPage, itemsPerPage)
                            .catch(() => {});
                    }
                }
            },
            setOrderBy(orderBy) { 
                let prefsOrderBy = this.collectionId != undefined ? 'order_by_' + this.collectionId : 'order_by';

                if (orderBy.metakey) {
                    if (!userPrefs.get(prefsOrderBy) || orderBy.metakey != userPrefs.get(prefsOrderBy).metakey)
                        userPrefs.set(prefsOrderBy, orderBy).catch(() => {});
                } else {
                    if (orderBy != userPrefs.get(prefsOrderBy))
                        userPrefs.set(prefsOrderBy, orderBy).catch(() => {});
                }
                
                store.dispatch('search/setOrderBy', orderBy);
                this.updateURLQueries();
            },
            setOrder(order) {
                let prefsOrder = this.collectionId != undefined ? 'order_' + this.collectionId : 'order';
                if (userPrefs.get(prefsOrder) != order) {
                    userPrefs.set(prefsOrder, order)
                        .catch(() => {});
                }

                store.dispatch('search/setOrder', order);
                this.updateURLQueries();
            },
            setStatus(status) {
                store.dispatch('search/setStatus', status);
                this.updateURLQueries();
            },
            setTotalItems(totalItems) {
                store.dispatch('search/setTotalItems', totalItems);
            },
            setSentenceMode(sentenceMode) {
                store.dispatch('search/setSentenceMode', sentenceMode);
            },
            setSearchQuery(searchQuery) {
                store.dispatch('search/setSearchQuery', searchQuery);
                this.updateURLQueries();
            },
            setViewMode(viewMode) {
                store.dispatch('search/setViewMode', viewMode);
                this.updateURLQueries(); 
                
                let prefsViewMode = this.collectionId != undefined ? 'view_mode_' + this.collectionId : 'view_mode';
                if(userPrefs.get(prefsViewMode) != viewMode) {
                    userPrefs.set(prefsViewMode, viewMode)
                        .catch(() => {});
                }
            },
            setAdminViewMode(adminViewMode) {
                store.dispatch('search/setAdminViewMode', adminViewMode);
                this.updateURLQueries();  

                let prefsAdminViewMode = this.collectionId != undefined ? 'admin_view_mode_' + this.collectionId : 'admin_view_mode';
                if (userPrefs.get(prefsAdminViewMode) != adminViewMode) {
                    userPrefs.set(prefsAdminViewMode, adminViewMode)
                        .catch(() => {  });
                }
            },
            setInitialViewMode(viewMode) {
                store.dispatch('search/setViewMode', viewMode);
                this.updateURLQueries(); 
            },
            setInitialAdminViewMode(adminViewMode) { 
                store.dispatch('search/setAdminViewMode', adminViewMode);
                this.updateURLQueries();  
            },
            async setSelectedItemsForIframe(selectedItems, singleSelection) {

                if (singleSelection)
                    store.dispatch('search/cleanSelectedItems');

                store.dispatch('search/setSelectedItems', selectedItems);

                let currentSelectedItems = store.getters['search/getSelectedItems'];

                if (window.history.replaceState) {
                    let searchParams = new URLSearchParams(window.location.search);
                    searchParams.delete('selecteditems');
                    for (let selectedItem of currentSelectedItems)
                        searchParams.append('selecteditems', selectedItem);

                    let newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?' + searchParams.toString() + window.location.hash;

                    await router.push(newurl)
                    window.history.replaceState({ ...window.history.state, ...{ path: newurl } }, '')
                }      
            },
            cleanSelectedItems() {
                store.dispatch('search/cleanSelectedItems');
            },
            filterBySelectedItems(selectedItems) {
                if ( route.name ) {
                    router.replace({ query: {} });
                    router.replace({ query: { postin: selectedItems } });
                } else {
                    router.replace({ query: {} });
                    router.replace({ query: { postin: selectedItems } });
                }
            },
            highlightsItem(itemId) {
                store.dispatch('search/highlightsItem', itemId);
                this.updateURLQueries();
            },
            async updateURLQueries() {
                const newQueries = store.getters['search/getPostQuery'];

     
                    await router.replace({ query: {} });
                    await router.replace({ query: newQueries });
                
            },
            updateStoreFromURL() {
                store.dispatch('search/setPostQuery', route.query);
            },
            loadItems() {
                console.log('loadItems')
                console.log(JSON.parse(JSON.stringify(store.getters['search/getPostQuery'])))
                // Forces fetch_only to be filled before any search happens
                if (store.getters['search/getPostQuery']['fetch_only'] != undefined) {  

                    app.config.globalProperties.$eventBusSearchEmitter.emit( 'isLoadingItems', true);
                    // Cancels previous Request
                    if (this.searchCancel != undefined)
                        this.searchCancel.cancel('Item search Canceled.');
                    console.log('fetching')
                    store.dispatch('collection/fetchItems', {
                        'collectionId': this.collectionId,
                        'isOnTheme': route.meta && route.meta.isOnTheme,
                        'termId': this.termId,
                        'taxonomy': this.taxonomy
                    }).then((resp) => {
                        // The actual fetch item request
                        resp.request.then((res) => {
                            app.config.globalProperties.$eventBusSearchEmitter.emit( 'isLoadingItems', false);
                            app.config.globalProperties.$eventBusSearchEmitter.emit( 'hasFiltered', res.hasFiltered);
                        })
                        .catch(() => {
                            app.config.globalProperties.$eventBusSearchEmitter.emit( 'isLoadingItems', false);
                        });

                        // Search Request Token for cancelling
                        this.searchCancel = resp.source;
                    });

                }  
            },
            setCollectionId(collectionId) {
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
                store.dispatch('search/cleanFilterTags');
                store.dispatch('search/cleanMetaQueries', { keepCollections: true });
                store.dispatch('search/cleanTaxQueries');
                this.updateURLQueries();
            }
        }
        app.config.globalProperties.$eventBusSearch = bus;

        // Defines the global $eventBusSearchEmitter for handling events across different search components
        emitter.on('input', data => {
            if (data.taxonomy)
                app.config.globalProperties.$eventBusSearch.addTaxquery(data);
            else
                app.config.globalProperties.$eventBusSearch.addMetaquery(data);
        });

        emitter.on('closeAdvancedSearch', () => {
            app.config.globalProperties.$eventBusSearch.$store.dispatch('search/setPage', 1);
            
            app.config.globalProperties.$eventBusSearch.performAdvancedSearch({});
        });

        emitter.on('performAdvancedSearch', advancedSearchQuery => {
            app.config.globalProperties.$eventBusSearch.$store.dispatch('search/setPage', 1);
            app.config.globalProperties.$eventBusSearch.performAdvancedSearch(advancedSearchQuery);

            app.config.globalProperties.$eventBusSearch.updateURLQueries();
        });

        app.config.globalProperties.$eventBusSearchEmitter = emitter;
    }
}