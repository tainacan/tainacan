import Vue from 'vue';
import store from './store/store'
import router from './../admin/js/router.js';

export const eventSearchBus = new Vue({
    router,
    store,
    data: {
        componentsTag: [],
        errors : [],
        query: {}
    },
    created(){
        this.$on('input', data => {
            store.dispatch('search/setPage', 1);
            this.add_metaquery(data) 
            this.updateURLQueries();
        });
    },
    watch: {
        '$route.query' () {     
            if (this.$route.name == 'CollectionItemsPage' || this.$route.name == 'ItemsPage') {
                if (this.$route.query.perpage == undefined)
                    this.$route.query.perpage = 12;
                if (this.$route.query.paged == undefined)
                    this.$route.query.paged = 1;

                store.dispatch('search/set_postquery', this.$route.query);
                //console.log(this.$route.query);
                this.loadItems();
            } 
        }
    },
    methods: {
        add_metaquery( data ){
            if ( data && data.collection_id ){
                store.dispatch('search/add_metaquery', data );
            }
        },
        getErrors( filter_id ){
            let error = this.errors.find( errorItem => errorItem.field_id === filter_id );
            return ( error ) ? error.errors : false
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
            store.dispatch('search/setPage', page);
            this.updateURLQueries();
        },
        setItemsPerPage(itemsPerPage) {
            store.dispatch('search/setItemsPerPage', itemsPerPage);
            this.updateURLQueries();
        },
        setOrderBy(newOrderBy) {
            store.dispatch('search/setOrderBy', newOrderBy);
            this.updateURLQueries();
        },
        setOrderAsc(newOrderAsc) {
            store.dispatch('search/setOrderBy', newOrderAsc);
            this.updateURLQueries();
        },
        updateURLQueries() {
            router.push({ query: {} });
            router.push({ query: store.getters['search/getPostQuery'] });
        },
        updateStoreFromURL() {
            store.dispatch('search/set_postquery', this.$route.query);
        },
        loadItems() {
            this.$emit( 'isLoadingItems', true);        
            store.dispatch('collection/fetchItems', this.$route.params.collectionId).then(() => {
                this.$emit( 'isLoadingItems', false);
            })
            .catch(() => {
                this.$emit( 'isLoadingItems', false);
            });
            
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