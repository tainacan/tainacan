<template>
    <div class="is-inline-flex">
        <b-tag            
                v-if="filterTags != undefined && filterTags.length > 0"
                v-for="(filterTag, index) of filterTags"
                :key="index"       
                attached
                closable
                @close="removeMetaQuery(filterTag.filterId, filterTag.value, filterTag.singleValue)">
            {{ filterTag.singleValue != undefined ? filterTag.singleValue : filterTag.value }}
        </b-tag>
    </div>
</template>
<script>
    import { mapGetters, mapActions } from 'vuex';

    export default {
        name: 'FiltersTagsList',
        props: {
            filters: Array
        },
        computed: {
            filterTags() {
                let tags = this.getFilterTags();
                let flattenTags = [];
                for (let tag of tags) {
                    if (Array.isArray(tag.value)) {
                        for (let valueTag of tag.value) 
                            flattenTags.push({filterId: tag.filterId, value: tag, singleValue: valueTag}); 
                    } else {
                        flattenTags.push(tag);
                    }
                }
                return flattenTags;
            }
        },
        methods: {
            ...mapGetters('search',[
                'getPostQuery',
                'getFilterTags'
            ]),
            ...mapActions('metadata',[
                'fetchMetadatum'
            ]),
            removeMetaQuery(filterId, value, singleValue) {
                if (singleValue != undefined)
                    this.$eventBusSearch.removeMetaFromFilterTag({ filterId: filterId, singleValue: singleValue });
                else
                    this.$eventBusSearch.removeMetaFromFilterTag({ filterId: filterId, value: value });
            }
        }
    }
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .filter-tags-list {
        width: 100%;
        padding: 1.5rem $page-side-padding 0px $page-side-padding;
        font-size: 0.75rem;
        margin-bottom: -0.375rem;

        &.is-inline-flex {
            flex-wrap: wrap;
            justify-content: flex-start;   
        }     
    }

</style>
