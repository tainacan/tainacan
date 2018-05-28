<template>
    <div>
        <collections-filter
                :open="collapsed"
                :query="getQuery"
                v-if="repository"/>
        <tainacan-filter-item
                v-show="!isMenuCompressed"        
                :query="getQuery"
                v-for="(filter, index) in filters"
                :key="index"
                :filter="filter"
                :open="collapsed"
                :repository="repository"/>

    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    import CollectionsFilter from '../repository/collection-filter/collection-filter.vue';

    export default {
        props: {
            filters: Array,
            collapsed: Boolean,
            repository: Boolean,
        },
        methods: {
            ...mapGetters('search',[
                'getPostQuery'
            ])
        },
        computed: {
            getQuery() {
                return this.getPostQuery();
            }
        },
        components: {
            CollectionsFilter
        }
    }
</script>