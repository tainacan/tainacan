export default {

    install(Vue, options = {}) {

        Vue.prototype.$eventBusSearch = new Vue({
            router: options.router,
            store: options.store,
            data: {
                componentsTag: [],
                errors : [],
                query: {},
                collectionId: undefined
            },
            created(){
                
                this.$on('input', data => {
                    this.$store.dispatch('search/setPage', 1);
        
                    if( data.taxonomy ){
                        this.add_taxquery(data);
                    } else {
                        this.add_metaquery(data);
                    }
        
                    this.updateURLQueries();
                });

                this.$root.$on('searchAdvanced', advancedSearchQuery => {
                    this.$store.dispatch('search/setPage', 1);

                    this.searchAdvanced(advancedSearchQuery);

                    this.updateURLQueries();
                });
            },
            watch: {
                '$route' (to, from) {
                    if (this.$route.params.collectionId)
                        this.collectionId = parseInt(this.$route.params.collectionId);

                    if (this.$route.name == null || this.$route.name == undefined || this.$route.name == 'CollectionItemsPage' || this.$route.name == 'ItemsPage') {
                        
                        if (this.$route.query.perpage == undefined || to.params.collectionId == from.params.collectionId) {
                            let perPageKey = (this.collectionId != undefined ? 'items_per_page_' + this.collectionId : 'items_per_page');
                            let perPageValue = this.$userPrefs.get(perPageKey);

                            if (perPageValue)
                                this.$route.query.perpage = perPageValue;
                            else {
                                this.$route.query.perpage = 12;
                                this.$userPrefs.set(perPageKey, 12);
                            }
                        }    
                        if (this.$route.query.paged == undefined || to.params.collectionId == from.params.collectionId)
                            this.$route.query.paged = 1;
                        if (this.$route.query.order == undefined || to.params.collectionId == from.params.collectionId)
                            this.$route.query.order = 'DESC';
                        if (this.$route.query.orderby == undefined || to.params.collectionId == from.params.collectionId) {
                            let orderByKey = (this.collectionId != undefined ? 'order_by_' + this.collectionId : 'order_by');
                            let orderByValue = this.$userPrefs.get(orderByKey);

                            if (orderByValue)
                                this.$route.query.orderby = orderByValue;
                            else {
                                this.$route.query.orderby = 'date';
                                this.$userPrefs.set(orderByKey, 'date');
                            }
                        }
                        
                        if(this.$route.query.metaquery && this.$route.query.metaquery.advancedSearch){
                            this.$store.dispatch('search/set_advanced_query', this.$route.query.metaquery);
                        } else {
                            this.$store.dispatch('search/set_postquery', this.$route.query);
                        }

                        this.loadItems(to);
                    }  
                }
            },
            methods: {
                searchAdvanced(data) {
                    this.$store.dispatch('search/set_advanced_query', data);
                    this.updateURLQueries(true);
                },
                add_metaquery( data ){
                    if ( data && data.collection_id ){
                        this.$store.dispatch('search/add_metaquery', data );
                    }
                },
                add_taxquery( data ){
                    if ( data && data.collection_id ){
                        this.$store.dispatch('search/add_taxquery', data );
                    }
                },
                addFetchOnlyMeta( metadatum ){
                    this.$store.dispatch('search/add_fetchonly_meta', metadatum );
                    this.updateURLQueries();             
                },
                addFetchOnly( metadatum ){
                    this.$store.dispatch('search/add_fetchonly', metadatum );
                    this.updateURLQueries();             
                },
                removeFetchOnlyMeta( metadatum ){
                    this.$store.dispatch('search/remove_fetchonly_meta', metadatum );
                    this.updateURLQueries();             
                },
                getErrors( filter_id ){
                    let error = this.errors.find( errorItem => errorItem.metadatum_id === filter_id );
                    return ( error ) ? error.errors : false;
                },
                listener(){
                    const components = this.getAllComponents();
                    for (let eventElement of components){
                        eventElement.addEventListener('input', (event) => {
                            if( event.detail ) {
                                this.add_metaquery( event.detail[0] );
                            }
                        });
                    }
                },
                setPage(page) {
                    this.$store.dispatch('search/setPage', page);
                    this.updateURLQueries();
                },
                setItemsPerPage(itemsPerPage) {
                    let prefsPerPage = this.collectionId != undefined ? 'items_per_page_' + this.collectionId : 'items_per_page';
                    this.$userPrefs.set(prefsPerPage, itemsPerPage)
                    .catch(() => { this.$console.log("Error settings user prefs for items per page.") });

                    this.$store.dispatch('search/setItemsPerPage', itemsPerPage);
                    this.updateURLQueries();
                },
                setOrderBy(newOrderBy) {
                    let prefsOrderBy = this.collectionId != undefined ? 'order_by_' + this.collectionId : 'order_by';
                    this.$userPrefs.set(prefsOrderBy, newOrderBy.slug)
                    .catch(() => { this.$console.log("Error settings user prefs for order by.") });

                    this.$store.dispatch('search/setOrderBy', newOrderBy);
                    this.updateURLQueries();
                },
                setOrder(newOrder) {
                    this.$store.dispatch('search/setOrder', newOrder);
                    this.updateURLQueries();
                },
                setStatus(status) {
                    this.$store.dispatch('search/setStatus', status);
                    this.updateURLQueries();
                },
                setSearchQuery(searchQuery) {
                    this.$store.dispatch('search/setSearchQuery', searchQuery);
                    this.updateURLQueries();
                },
                setViewMode(viewMode) {
                    this.$store.dispatch('search/setViewMode', viewMode);
                    this.updateURLQueries();  
                },
                updateURLQueries(isAdvancedSearch) {
                    this.$router.push({query: {}});
                    this.$route.meta['advancedSearch'] = isAdvancedSearch;
                    this.$router.push({query: this.$store.getters['search/getPostQuery']});
                },
                updateStoreFromURL() {
                    this.$store.dispatch('search/set_postquery', this.$route.query);
                },
                loadItems(to) {

                    // Forces fetch_only to be filled before any search happens
                    if (this.$store.getters['search/getFetchOnly'] == undefined) {
                        this.$emit( 'hasToPrepareMetadataAndFilters', to);
                    } else {
                        this.$emit( 'isLoadingItems', true);

                        this.$store.dispatch('collection/fetchItems', {
                            'collectionId': this.collectionId,
                            'isOnTheme': (this.$route.name == null)
                        })
                        .then((res) => {
                            this.$emit( 'isLoadingItems', false);
                            this.$emit( 'hasFiltered', res.hasFiltered);

                            if(res.advancedSearchResults){
                                this.$emit('advancedSearchResults', res.advancedSearchResults);
                            }
                        })
                        .catch(() => {
                            this.$emit( 'isLoadingItems', false);
                        });
                    }
                },
                setCollectionId(collectionId) {
                    this.collectionId = collectionId;
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