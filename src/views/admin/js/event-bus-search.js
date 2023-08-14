import mitt from 'mitt';

const emitter = mitt();

export default {

    install(app, options = {}) {

        app.config.globalProperties.$eventBusSearch = {
            $store: app.config.globalProperties.$store,
            $router: app.config.globalProperties.$router,
            $route: app.config.globalProperties.$route,
            errors : [],
            query: {},
            collectionId: undefined,
            defaultOrder: 'ASC',
            defaultOrderBy: 'date',
            taxonomy: undefined,
            termId: undefined,
            searchCancel: undefined,
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

                    this.$eventBusSearchEmitter.emit( 'isLoadingItems', true);
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
                            this.$eventBusSearchEmitter.emit( 'isLoadingItems', false);
                            this.$eventBusSearchEmitter.emit( 'hasFiltered', res.hasFiltered);
                        })
                        .catch(() => {
                            this.$eventBusSearchEmitter.emit( 'isLoadingItems', false);
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

        // Defines the global $eventBusSearchEmitter for handling events across different search components
        emitter.on('input', data => {
            if (data.taxonomy)
                $eventBusSearch.addTaxquery(data);
            else
                $eventBusSearch.addMetaquery(data);
        });

        emitter.on('closeAdvancedSearch', () => {
            $eventBusSearch.$store.dispatch('search/setPage', 1);
            
            $eventBusSearch.performAdvancedSearch({});
        });

        emitter.on('performAdvancedSearch', advancedSearchQuery => {
            $eventBusSearch.$store.dispatch('search/setPage', 1);
            $eventBusSearch.performAdvancedSearch(advancedSearchQuery);

            $eventBusSearch.updateURLQueries();
        });

        app.config.globalProperties.$eventBusSearchEmitter = emitter;
    }
}