import mitt from 'mitt';

export default {

    install(app) {

        const emitter = mitt();
        const bus = {
            collectionId: undefined,
            defaultOrder: 'ASC',
            defaultOrderBy: 'date',
            taxonomy: undefined,
            termId: undefined,
            searchCancel: undefined,
            performAdvancedSearch(data) {
                app.config.globalProperties.$store.dispatch('search/setAdvancedQuery', data);
                this.updateURLQueries();
            },
            addMetaquery( data ){
                if ( data && data.collection_id ){
                    app.config.globalProperties.$store.dispatch('search/addMetaquery', data );
                }
                this.updateURLQueries();
            },
            addTaxquery( data ){
                if ( data && data.collection_id ){
                    app.config.globalProperties.$store.dispatch('search/addTaxquery', data );
                }
                this.updateURLQueries();
            },
            removeMetaFromFilterTag(filterTag) {
                
                if (filterTag.singleLabel != undefined || filterTag.label != undefined) {
                    
                    if (filterTag.argType !== 'postin') {
                        if (filterTag.taxonomy) {
                            app.config.globalProperties.$store.dispatch('search/removeTaxQuery', {
                                filterId: filterTag.filterId,
                                label: filterTag.singleLabel ? filterTag.singleLabel : filterTag.label,
                                isMultiValue: filterTag.singleLabel ? false : true,
                                taxonomy: filterTag.taxonomy,
                                value: filterTag.value,
                                secondaryMetadatumId: filterTag.secondaryMetadatumId
                            });
                        } else {
                            app.config.globalProperties.$store.dispatch('search/removeMetaQuery', {
                                filterId: filterTag.filterId,
                                label: filterTag.singleLabel ? filterTag.singleLabel : filterTag.label,
                                isMultiValue: filterTag.singleLabel ? false : true,
                                metadatum_id: filterTag.metadatumId,
                                value: filterTag.value,
                                secondaryMetadatumId: filterTag.secondaryMetadatumId
                            });
                        }
                    } else {
                        app.config.globalProperties.$store.dispatch('search/removePostIn');
                    }
                    app.config.globalProperties.$store.dispatch('search/removeFilterTag', filterTag);
                }
                this.updateURLQueries();
            },
            addFetchOnly( metadatum, ignorePrefs, metadatumIDs ) {
                app.config.globalProperties.$store.dispatch('search/addFetchOnly', metadatum );
                app.config.globalProperties.$store.dispatch('search/addFetchOnlyMeta', metadatumIDs);
                this.updateURLQueries();  
                
                if (!ignorePrefs) {
                    let prefsFetchOnly = this.collectionId ? `fetch_only_${this.collectionId}` : 'fetch_only';
                    let prefsFetchOnlyMeta = this.collectionId ? `fetch_only_meta_${this.collectionId}` : 'fetch_only_meta';

                    if (app.config.globalProperties.$userPrefs.get(prefsFetchOnly) != metadatum)
                        app.config.globalProperties.$userPrefs.set(prefsFetchOnly, metadatum);

                    if (app.config.globalProperties.$userPrefs.get(prefsFetchOnlyMeta) != metadatumIDs)
                        app.config.globalProperties.$userPrefs.set(prefsFetchOnlyMeta, metadatumIDs);
                }
            },
            cleanFetchOnly() {
                app.config.globalProperties.$store.dispatch('search/cleanFetchOnly');
            },
            removeFetchOnlyMeta( metadatum ){
                app.config.globalProperties.$store.dispatch('search/removeFetchOnlyMeta', metadatum );
                this.updateURLQueries();             
            },
            setPage(page) {
                app.config.globalProperties.$store.dispatch('search/setPage', page);
                this.updateURLQueries();
            },
            resetPageOnStore() {
                app.config.globalProperties.$store.dispatch('search/setPage', 1);
            },
            setItemsPerPage(itemsPerPage, shouldNotUpdatePrefs) {
                app.config.globalProperties.$store.dispatch('search/setItemsPerPage', itemsPerPage);
                this.updateURLQueries();

                if (shouldNotUpdatePrefs == undefined || shouldNotUpdatePrefs == false) {
                    let prefsPerPage = this.collectionId != undefined ? 'items_per_page_' + this.collectionId : 'items_per_page';
                    if (app.config.globalProperties.$userPrefs.get(prefsPerPage) != itemsPerPage) {
                        app.config.globalProperties.$userPrefs.set(prefsPerPage, itemsPerPage)
                            .catch(() => {});
                    }
                }
            },
            setOrderBy(orderBy) { 
                let prefsOrderBy = this.collectionId != undefined ? 'order_by_' + this.collectionId : 'order_by';

                if (orderBy.metakey) {
                    if (!app.config.globalProperties.$userPrefs.get(prefsOrderBy) || orderBy.metakey != app.config.globalProperties.$userPrefs.get(prefsOrderBy).metakey)
                        app.config.globalProperties.$userPrefs.set(prefsOrderBy, orderBy).catch(() => {});
                } else {
                    if (orderBy != app.config.globalProperties.$userPrefs.get(prefsOrderBy))
                        app.config.globalProperties.$userPrefs.set(prefsOrderBy, orderBy).catch(() => {});
                }
                
                app.config.globalProperties.$store.dispatch('search/setOrderBy', orderBy);
                this.updateURLQueries();
            },
            setOrder(order) {
                let prefsOrder = this.collectionId != undefined ? 'order_' + this.collectionId : 'order';
                if (app.config.globalProperties.$userPrefs.get(prefsOrder) != order) {
                    app.config.globalProperties.$userPrefs.set(prefsOrder, order)
                        .catch(() => {});
                }

                app.config.globalProperties.$store.dispatch('search/setOrder', order);
                this.updateURLQueries();
            },
            setStatus(status) {
                app.config.globalProperties.$store.dispatch('search/setStatus', status);
                this.updateURLQueries();
            },
            setTotalItems(totalItems) {
                app.config.globalProperties.$store.dispatch('search/setTotalItems', totalItems);
            },
            setSentenceMode(sentenceMode) {
                app.config.globalProperties.$store.dispatch('search/setSentenceMode', sentenceMode);
            },
            setSearchQuery(searchQuery) {
                app.config.globalProperties.$store.dispatch('search/setSearchQuery', searchQuery);
                this.updateURLQueries();
            },
            setViewMode(viewMode) {
                app.config.globalProperties.$store.dispatch('search/setViewMode', viewMode);
                this.updateURLQueries(); 
                
                let prefsViewMode = this.collectionId != undefined ? 'view_mode_' + this.collectionId : 'view_mode';
                if(app.config.globalProperties.$userPrefs.get(prefsViewMode) != viewMode) {
                    app.config.globalProperties.$userPrefs.set(prefsViewMode, viewMode)
                        .catch(() => {});
                }
            },
            setAdminViewMode(adminViewMode) {
                app.config.globalProperties.$store.dispatch('search/setAdminViewMode', adminViewMode);
                this.updateURLQueries();  

                let prefsAdminViewMode = this.collectionId != undefined ? 'admin_view_mode_' + this.collectionId : 'admin_view_mode';
                if (app.config.globalProperties.$userPrefs.get(prefsAdminViewMode) != adminViewMode) {
                    app.config.globalProperties.$userPrefs.set(prefsAdminViewMode, adminViewMode)
                        .catch(() => {  });
                }
            },
            setInitialViewMode(viewMode) {
                app.config.globalProperties.$store.dispatch('search/setViewMode', viewMode);
                this.updateURLQueries(); 
            },
            setInitialAdminViewMode(adminViewMode) { 
                app.config.globalProperties.$store.dispatch('search/setAdminViewMode', adminViewMode);
                this.updateURLQueries();  
            },
            async setSelectedItemsForIframe(selectedItems, singleSelection) {

                if (singleSelection)
                    app.config.globalProperties.$store.dispatch('search/cleanSelectedItems');

                app.config.globalProperties.$store.dispatch('search/setSelectedItems', selectedItems);

                let currentSelectedItems = app.config.globalProperties.$store.getters['search/getSelectedItems'];

                if (window.history.replaceState) {
                    let searchParams = new URLSearchParams(window.location.search);
                    searchParams.delete('selecteditems');
                    for (let selectedItem of currentSelectedItems)
                        searchParams.append('selecteditems', selectedItem);

                    let newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?' + searchParams.toString() + window.location.hash;
                    window.history.pushState({path: newurl}, '', newurl);
                }      
            },
            cleanSelectedItems() {
                app.config.globalProperties.$store.dispatch('search/cleanSelectedItems');
            },
            exitViewModeWithoutPagination() {
                app.config.globalProperties.$eventBusSearchEmitter.emit( 'exitViewModeWithoutPagination', true);
            },
            updateURLQueries() {
                const newQueries = JSON.parse(JSON.stringify(app.config.globalProperties.$store.getters['search/getPostQuery']));
                app.config.globalProperties.$router.replace({ path: app.config.globalProperties.$route.path, query: newQueries });
            },
            updateStoreFromURL() {
                app.config.globalProperties.$store.dispatch('search/setPostQuery', JSON.parse(JSON.stringify(app.config.globalProperties.$route.query)));
            },
            loadItems() {
                // Forces fetch_only to be filled before any search happens
                if (app.config.globalProperties.$store.getters['search/getPostQuery']['fetch_only'] != undefined) {  

                    app.config.globalProperties.$eventBusSearchEmitter.emit( 'isLoadingItems', true);
                    // Cancels previous Request
                    if (this.searchCancel != undefined)
                        this.searchCancel.cancel('Item search Canceled.');
                    
                    app.config.globalProperties.$store.dispatch('collection/fetchItems', {
                        'collectionId': this.collectionId,
                        'isOnTheme': app.config.globalProperties.$route.meta && app.config.globalProperties.$route.meta.isOnTheme,
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
                app.config.globalProperties.$store.dispatch('search/cleanFilterTags');
                app.config.globalProperties.$store.dispatch('search/cleanMetaQueries', { keepCollections: true });
                app.config.globalProperties.$store.dispatch('search/cleanTaxQueries');
                app.config.globalProperties.$store.dispatch('search/removePostIn');
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
            app.config.globalProperties.$store.dispatch('search/setPage', 1);
            app.config.globalProperties.$eventBusSearch.performAdvancedSearch({});
        });

        app.config.globalProperties.$eventBusSearchEmitter = emitter;
    }
}