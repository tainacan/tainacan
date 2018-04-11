<template>
    <div>
        <div v-if="filters.length > 0">
            <tainacan-filter-item
                    
                    :query="getPostQuery()"
                    v-for="(filter, index) in filters"
                    :key="index"
                    :filter="filter"/>
        </div>
        <section 
                    v-else
                    class="is-grouped-centered section">
                <div class="content has-text-gray has-text-centered">
                    <p>
                        <b-icon
                                icon="filter-outline"
                                size="is-large"/>
                    </p>
                    <p>{{ $i18n.get('info_there_is_no_filter' ) }}</p>  
                    <router-link
                            id="button-create-filter"
                            :to="isRepositoryLevel ? $routerHelper.getNewFilterPath() : $routerHelper.getNewCollectionFilterPath(collectionId)"
                            tag="button" 
                            class="button is-secondary is-centered">
                        {{ $i18n.getFrom('filters', 'new_item') }}</router-link>
                </div>
            </section>
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
            this.fetchFilters( { collectionId: this.collectionId, isRepositoryLevel: !( this.collectionId ), isContextEdit: true })
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