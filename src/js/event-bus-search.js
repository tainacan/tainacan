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
            },
            watch: {
                '$route' () {
                    if (this.$route.params.collectionId) 
                        this.collectionId = parseInt(this.$route.params.collectionId);

                    if (this.$route.name == null || this.$route.name == undefined || this.$route.name == 'CollectionItemsPage' || this.$route.name == 'ItemsPage') {
                        if (this.$route.query.perpage == undefined)
                            this.$route.query.perpage = 12;
                        if (this.$route.query.paged == undefined)
                            this.$route.query.paged = 1;
                        if (this.$route.query.order == undefined)
                            this.$route.query.order = 'DESC';
                        if (this.$route.query.orderby == undefined)
                            this.$route.query.orderby = 'date';
                        
                        this.$store.dispatch('search/set_postquery', this.$route.query); 
                        this.loadItems();
                    }
                    
                }
            },
            methods: {
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
                addFetchOnlyMeta( field ){
                    this.$store.dispatch('search/add_fetchonly_meta', field );
                    this.updateURLQueries();             
                },
                removeFetchOnlyMeta( field ){
                    this.$store.dispatch('search/remove_fetchonly_meta', field );
                    this.updateURLQueries();             
                },
                getErrors( filter_id ){
                    let error = this.errors.find( errorItem => errorItem.field_id === filter_id );
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
                    this.$store.dispatch('search/setItemsPerPage', itemsPerPage);
                    this.updateURLQueries();
                },
                setOrderBy(newOrderBy) {
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
                updateURLQueries() {
                    this.$router.push({ query: {}});
                    this.$router.push({ query: this.$store.getters['search/getPostQuery'] });
                },
                updateStoreFromURL() {
                    this.$store.dispatch('search/set_postquery', this.$route.query);
                },
                loadItems() {
       
                    this.$emit( 'isLoadingItems', true);
                    this.$store.dispatch('collection/fetchItems', this.collectionId).then((res) => {
                        this.$emit( 'isLoadingItems', false);
                        this.$emit( 'hasFiltered', res.hasFiltered);
                        //var event = new Event('tainacan-items-change')
                        //document.dispatchEvent(event);
                    })
                    .catch(() => {
                        this.$emit( 'isLoadingItems', false);
                    });
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