<template>
    <div>
        <tainacan-filter-item        
                :query="getPostQuery()"
                v-for="(filter, index) in filters"
                :key="index"
                :filter="filter"/>

    </div>
</template>
<script>
    import { mapGetters } from 'vuex';

    export default {
        props: {
            filters: Array
        },
        methods: {
            ...mapGetters('search',[
                'getPostQuery'
            ])
        }
    }
</script>