<template>
    <div>
        <tainacan-filters-list
                :query="getPostQuery()"
                v-for="(filter, index) in filters"
                :key="index"
                :filter="filter"/>
    </div>
</template>
<script>
    import { mapActions, mapGetters } from 'vuex';

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