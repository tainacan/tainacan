import mitt from 'mitt';
import { isIframeSelectionMode, broadcastSelectionState, toPlainItemIds } from './selection-bridge.js';

export default {

    install(app) {

        const emitter = mitt();

        const bus = {
            collectionId: undefined,
            defaultOrder: 'ASC',
            defaultOrderBy: 'date',
            orderBeforeRelevance: null,
            taxonomy: undefined,
            termId: undefined,
            searchCancel: undefined,
            pendingFocusRestore: null,
            setFocusRestoreRequest(filterId, value) {
                this.pendingFocusRestore = { filterId, value: value != null ? String(value) : undefined };
            },
            getAndClearFocusRestoreRequest(filterId) {
                if (this.pendingFocusRestore && this.pendingFocusRestore.filterId === filterId) {
                    const value = this.pendingFocusRestore.value;
                    this.pendingFocusRestore = null;
                    return value;
                }
                return undefined;
            },
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
                    // get() returns undefined for an empty string, so compare against '' before saving.
                    let currentFetchOnly = app.config.globalProperties.$userPrefs.get(prefsFetchOnly) || '';
                    let currentFetchOnlyMeta = app.config.globalProperties.$userPrefs.get(prefsFetchOnlyMeta) || '';

                    if (currentFetchOnly != metadatum)
                        app.config.globalProperties.$userPrefs.set(prefsFetchOnly, metadatum);

                    if (currentFetchOnlyMeta != metadatumIDs)
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
                this.rememberOrderByPreference(orderBy);
                
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
            setOrderAndOrderBy(order, orderBy) {
                this.rememberOrderByPreference(orderBy);

                let prefsOrder = this.collectionId != undefined ? 'order_' + this.collectionId : 'order';
                if (app.config.globalProperties.$userPrefs.get(prefsOrder) != order) {
                    app.config.globalProperties.$userPrefs.set(prefsOrder, order)
                        .catch(() => {});
                }

                app.config.globalProperties.$store.dispatch('search/setOrderBy', orderBy);
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
            setPerWordSearchMode(perWordSearchMode) {
                app.config.globalProperties.$store.dispatch('search/setPerWordSearchMode', perWordSearchMode);
                this.updateURLQueries();
            },
            applyPerWordSearchDefaultIfNeeded() {
                const postquery = app.config.globalProperties.$store.getters['search/getPostQuery'];
                const isPerWordSearchByDefault = app.config.globalProperties.$store.getters['search/getIsPerWordSearchByDefault'];

                if ( isPerWordSearchByDefault && (postquery.search || postquery.s) && postquery.sentence === undefined ) {
                    app.config.globalProperties.$store.dispatch('search/setPerWordSearchMode', true);
                }
            },
            setSearchQuery(searchQuery) {
                const store = app.config.globalProperties.$store;
                const previous = store.getters['search/getSearchQuery'];
                const hadSearch = previous != undefined && String(previous).trim() !== '';
                const hasSearch = searchQuery != undefined && String(searchQuery).trim() !== '';

                store.dispatch('search/setSearchQuery', searchQuery);

                if (!hadSearch && hasSearch)
                    this.switchToSearchRelevance(true);
                else if (hadSearch && !hasSearch)
                    this.restoreOrderAfterSearch();

                this.updateURLQueries();
            },
            /**
             * Relevance is the sort while a textual search is active and the visitor
             * has not already chosen another one. The previous sort is kept aside
             * and is not written to the saved preference.
             */
            switchToSearchRelevance(shouldSwitch) {
                if (!shouldSwitch)
                    return false;

                const store = app.config.globalProperties.$store;
                const query = store.getters['search/getPostQuery'] || {};
                if (query.orderby === 'relevance')
                    return false;

                const snapshot = { orderby: query.orderby || this.defaultOrderBy || 'date' };
                if (query.metakey)
                    snapshot.metakey = query.metakey;
                if (query.metatype)
                    snapshot.metatype = query.metatype;
                this.orderBeforeRelevance = snapshot;

                store.dispatch('search/setOrderBy', 'relevance');
                return true;
            },
            restoreOrderAfterSearch() {
                const store = app.config.globalProperties.$store;
                const query = store.getters['search/getPostQuery'] || {};
                if (query.orderby !== 'relevance') {
                    this.orderBeforeRelevance = null;
                    return;
                }

                const remembered = this.orderBeforeRelevance;
                this.orderBeforeRelevance = null;
                if (remembered && remembered.orderby && remembered.orderby !== 'relevance')
                    store.dispatch('search/setOrderBy', remembered.metakey ? remembered : remembered.orderby);
                else
                    store.dispatch('search/clearOrderBy');
            },
            rememberOrderByPreference(orderBy) {
                if (orderBy == undefined || orderBy === '' || orderBy === 'relevance')
                    return;
                if (orderBy.orderby === 'relevance' || orderBy.slug === 'relevance')
                    return;

                let prefsOrderBy = this.collectionId != undefined ? 'order_by_' + this.collectionId : 'order_by';
                const stored = app.config.globalProperties.$userPrefs.get(prefsOrderBy);

                if (orderBy.metakey) {
                    if (!stored || orderBy.metakey != stored.metakey)
                        app.config.globalProperties.$userPrefs.set(prefsOrderBy, orderBy).catch(() => {});
                } else if (orderBy != stored) {
                    app.config.globalProperties.$userPrefs.set(prefsOrderBy, orderBy).catch(() => {});
                }
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
            publishSelectionState(selectedItems) {
                if (!isIframeSelectionMode(app))
                    return;

                let items = toPlainItemIds(selectedItems);
                if (!items.length) {
                    try {
                        items = toPlainItemIds(
                            app.config.globalProperties.$store.getters['search/getSelectedItems']
                        );
                    } catch {
                        items = [];
                    }
                }

                let query;
                try {
                    query = JSON.parse(JSON.stringify(
                        app.config.globalProperties.$store.getters['search/getPostQuery'] || {}
                    ));
                } catch {
                    query = {};
                }

                broadcastSelectionState({
                    selectedItems: items,
                    query: query,
                    href: window.location.href,
                    collectionId: this.collectionId
                });
            },
            async setSelectedItemsForIframe(selectedItems, singleSelection) {

                if (singleSelection)
                    app.config.globalProperties.$store.dispatch('search/cleanSelectedItems');

                app.config.globalProperties.$store.dispatch('search/setSelectedItems', selectedItems);
                this.publishSelectionState(
                    toPlainItemIds(app.config.globalProperties.$store.getters['search/getSelectedItems'])
                );
            },
            cleanSelectedItems() {
                app.config.globalProperties.$store.dispatch('search/cleanSelectedItems');
            },
            exitViewModeWithoutPagination() {
                app.config.globalProperties.$eventBusSearchEmitter.emit( 'exitViewModeWithoutPagination', true);
            },
            updateURLQueries() {
                const newQueries = JSON.parse(JSON.stringify(app.config.globalProperties.$store.getters['search/getPostQuery']));
                const navigation = app.config.globalProperties.$router.replace({ path: app.config.globalProperties.$route.path, query: newQueries });
                if (navigation && typeof navigation.finally === 'function') {
                    navigation.finally(() => this.publishSelectionState());
                } else {
                    this.publishSelectionState();
                }
            },
            updateStoreFromURL() {
                app.config.globalProperties.$store.dispatch('search/setPostQuery', JSON.parse(JSON.stringify(app.config.globalProperties.$route.query)));
                this.applyPerWordSearchDefaultIfNeeded();
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

        if (isIframeSelectionMode(app))
            bus.publishSelectionState();

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