export default {

    install(Vue, options = {}) {

        Vue.prototype.$eventBusSearch = new Vue({
            router: options.router,
            store: options.store,
            data: {
                componentsTag: [],
                errors : [],
                query: {},
                collectionId: undefined,
                taxonomy: undefined,
                termId: undefined,
                searchCancel: undefined
            },
            created() {
                this.$on('input', data => {
                    this.$store.dispatch('search/setPage', 1);

                    if( data.taxonomy ){
                        this.add_taxquery(data);
                    } else {
                        this.add_metaquery(data);
                    }
                    
                    this.updateURLQueries();
                });

                this.$on('sendValuesToTags', data => {
                   this.$store.dispatch('search/addFilterTag', data);
                });

                this.$root.$on('closeAdvancedSearch', () => {
                    this.$store.dispatch('search/setPage', 1);
                    
                    this.searchAdvanced({});
                });

                this.$root.$on('searchAdvanced', advancedSearchQuery => {
                    this.$store.dispatch('search/setPage', 1);

                    this.searchAdvanced(advancedSearchQuery);

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
                            let orderValue = this.$userPrefs.get(orderKey);

                            if (orderValue)
                                this.$route.query.order = orderValue;
                            else {
                                this.$route.query.order = 'DESC';
                                this.$userPrefs.set(orderKey, 'DESC');
                            }
                        }

                        // Order By (required extra work to deal with custom metadata ordering)
                        if (this.$route.query.orderby == undefined || to.params.collectionId != from.params.collectionId) {
                            let orderByKey = (this.collectionId != undefined ? 'order_by_' + this.collectionId : 'order_by');
                            let orderBy = this.$userPrefs.get(orderByKey);

                            if (orderBy) {
                                if (orderBy.slug == 'creation_date') {
                                    this.$route.query.orderby = 'date';
                                } else if (orderBy.slug == 'author_name') {
                                    this.$route.query.orderby = 'author_name';
                                } else if (orderBy.metadata_type_object.primitive_type == 'float' || orderBy.metadata_type_object.primitive_type == 'int') {
                                    this.$route.query.orderby = 'meta_value_num';
                                    this.$route.query.metakey = orderBy.id;
                                } else if (orderBy.metadata_type_object.primitive_type == 'date') {
                                    this.$route.query.orderby = 'meta_value';
                                    this.$route.query.metakey = orderBy.id;
                                    this.$route.query.metatype = 'DATETIME';
                                } else if (orderBy.metadata_type_object.core) {
                                    this.$route.query.orderby =  orderBy.metadata_type_object.related_mapped_prop;
                                } else {
                                    this.$route.query.orderby = 'meta_value';
                                    this.$route.query.metakey = orderBy.id;
                                }
 
                                // Sets orderByName as null so ItemsPage can take care of creating it
                                this.$store.dispatch('search/setOrderByName', null);

                            } else {
                                this.$route.query.orderby = 'date';
                                this.$userPrefs.set(orderByKey, { 
                                    slug: 'creation_date',
                                    name: this.$i18n.get('label_creation_date')
                                }).catch(() => { });

                                // Sets orderByName as null so ItemsPage can take care of creating it
                                this.$store.dispatch('search/setOrderByName', null);
                            }
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

                        if (to.fullPath != from.fullPath) {
                            this.loadItems(to);
                        }
                    }                      
                }
            },
            methods: {
                searchAdvanced(data) {
                    this.$store.dispatch('search/set_advanced_query', data);
                    this.updateURLQueries();
                },
                add_metaquery( data ){
                    if ( data && data.collection_id ){
                        this.$store.dispatch('search/add_metaquery', data );
                    }
                },
                removeMetaQuery(query) {
                    this.$store.dispatch('search/remove_metaquery', query );
                    this.updateURLQueries();
                },
                removeMetaFromFilterTag(filterTag) {
                    this.$emit('removeFromFilterTag', filterTag);
                    if (filterTag.singleValue == undefined)
                        this.$store.dispatch('search/removeFilterTag', filterTag);
                },
                add_taxquery( data ){
                    if ( data && data.collection_id ){
                        this.$store.dispatch('search/add_taxquery', data );
                    }
                },
                addFetchOnly( metadatum, ignorePrefs, metadatumIDs ){
                    
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
                getErrors( filter_id ){
                    let error = this.errors.find( errorItem => errorItem.metadatum_id === filter_id );
                    return ( error ) ? error.errors : false;
                },
                // listener(){
                //     const components = this.getAllComponents();
                //     for (let eventElement of components){
                //         eventElement.addEventListener('input', (event) => {
                //             if( event.detail ) {
                //                 this.add_metaquery( event.detail[0] );
                //             }
                //         });
                //     }
                // },
                setPage(page) {
                    this.$store.dispatch('search/setPage', page);
                    this.updateURLQueries();
                },
                setItemsPerPage(itemsPerPage) {
                    this.$store.dispatch('search/setItemsPerPage', itemsPerPage);
                    this.updateURLQueries();

                    let prefsPerPage = this.collectionId != undefined ? 'items_per_page_' + this.collectionId : 'items_per_page';
                    if (this.$userPrefs.get(prefsPerPage) != itemsPerPage) {
                        this.$userPrefs.set(prefsPerPage, itemsPerPage)
                            .catch(() => {});
                    }
                },
                setOrderBy(orderBy) { 
                    let prefsOrderBy = this.collectionId != undefined ? 'order_by_' + this.collectionId : 'order_by';
                    if (this.$userPrefs.get(prefsOrderBy) != orderBy) {
                        this.$userPrefs.set(prefsOrderBy, orderBy)
                            .catch(() => {});
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
                updateURLQueries() {
                    this.$router.replace({ query: {} });
                    this.$router.replace({ query: this.$store.getters['search/getPostQuery'] });
                },
                updateStoreFromURL() {
                    this.$store.dispatch('search/set_postquery', this.$route.query);
                },
                loadItems(to) {

                    // Forces fetch_only to be filled before any search happens
                    if (this.$store.getters['search/getPostQuery']['fetch_only'] == undefined) {  
                        this.$emit( 'hasToPrepareMetadataAndFilters', to);
                    } else {  
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
                                
                                if(res.advancedSearchResults){
                                    this.$emit('advancedSearchResults', res.advancedSearchResults);
                                }
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
                setTerm(termId, taxonomy) {
                    this.termId = termId;
                    this.taxonomy = taxonomy;
                },
                clearAllFilters() {
                    this.$store.dispatch('search/cleanFilterTags');
                    this.$store.dispatch('search/cleanMetaQueries');
                    this.$store.dispatch('search/cleanTaxQueries');
                    this.updateURLQueries();
                },
                 /* Dev interfaces methods */
        
                registerComponent( name ){
                    if (this.componentsTag.indexOf(name) < 0) {
                        this.componentsTag.push( name );
                    }
                },
                getAllComponents(){
                    const components = [];
                    for( let component of this.componentsTag ){
                        const eventElements = document.getElementsByTagName( component );
                        if( eventElements ) {
                            for (let eventElement of eventElements){
                                components.push( eventElement );
                            }
                        }
                    }
                    return components;
                },
            }
        });
    }
}