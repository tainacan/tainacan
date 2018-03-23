<template>
    <div>
        <tainacan-filters-list
                :query="getPostQuery()"
                v-for="(filter, index) in filters"
                v-bind:key="index"
                :filter="filter"></tainacan-filters-list>
    </div>
</template>
<script>
    import { mapActions, mapGetters } from 'vuex';
    import { eventFilterBus } from '../../../js/event-bus-filters'

    export default {
        data(){
            return {
                collectionId: null,
                filters: []
            }
        },
        created(){
            this.collectionId = ( this.$route.params.collectionId ) ?  this.$route.params.collectionId : null;
            this.fetchFilters( { collectionId: this.collectionId, isRepositoryLevel: !( this.collectionId )  })
                .then( res => {
                    if( res && res.length > 0){
                        this.filters = res;
                    }
                } );
        },
        methods: {
            ...mapActions('filter',[
                'fetchFilters'
            ]),
            ...mapGetters('search',[
                'getPostQuery'
            ])
        }
    }
</script>