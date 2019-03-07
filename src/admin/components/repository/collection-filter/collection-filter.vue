<template>
    <b-field class="filter-item-forms">
        <b-collapse
                aria-id="collection-filters-collapse"
                class="show"
                :open="open"
                animation="filter-item">
            <label
                    class="label"
                    slot="trigger"
                    slot-scope="props">
                <span class="icon is-right">
                    <i 
                            :class="{ 'tainacan-icon-arrowdown': props.open, 'tainacan-icon-arrowright': !props.open }"
                            class="tainacan-icon tainacan-icon-20px" />
                </span>
                {{ $i18n.get('collections') }}
            </label>

            <div
                    class="block">
                <div
                        v-for="(collection, key) in collections"
                        :key="key"
                        class="control">
                    <b-checkbox
                            v-model="collectionsIdsToFilter"
                            :native-value="collection.id"
                            @input="apply_filter">
                        {{ collection.name }}
                    </b-checkbox>
                </div>
            </div>
        </b-collapse>
    </b-field>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: 'CollectionFilter',
        props: {
            query: Object,
            open: false,
        },
        created(){
            this.fetchCollections({
                    page: 1, 
                    collectionsPerPage: -1, 
                    status: null,
                    contextEdit: false
            });
        },
        mounted(){
            let routeQueries = this.$route.query;

            if(routeQueries.metaquery &&
                routeQueries.metaquery[0] &&
                Array.isArray(routeQueries.metaquery[0].value)){
                this.collectionsIdsToFilter = routeQueries.metaquery[0].value;

                this.apply_filter();
            }
        },
        data(){
            return {
                inputs: [],
                collectionsIdsToFilter: []
            }
        },
        computed: {
            collections(){
                return this.getCollections();
            },
        },
        methods: {
            ...mapActions('search', [
                'setPage'
            ]),
            ...mapActions('collection', [
                'fetchCollections'
            ]),
            ...mapGetters('collection', [
                'getCollections',
            ]),
            apply_filter(){
                this.$eventBusSearch.$emit( 'input', {
                    filter: 'checkbox',
                    metadatum_id: 'collection_id',
                    value: this.collectionsIdsToFilter,
                    compare: 'IN',
                    collection_id: this.collectionsIdsToFilter,
                });
            },
        }
    }
</script>
