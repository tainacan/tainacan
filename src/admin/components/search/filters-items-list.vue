<template>
    <div>
        <tainacan-filter-item
                v-show="!isMenuCompressed"        
                :query="getQuery"
                v-for="(filter, index) in filters"
                :key="index"
                :filter="filter"
                :opened="collapsed"/>

    </div>
</template>
<script>
    import { mapGetters } from 'vuex';

    export default {
        props: {
            filters: Array,
            collapsed: Boolean,
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
        }
    }
</script>