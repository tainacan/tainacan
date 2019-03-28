<template>
    <div 
            class="is-inline-flex"
            v-if="filterTags != undefined && filterTags.length > 0">
        <b-tag            
                v-for="(filterTag, index) of filterTags"
                :key="index"       
                attached
                closable
                @close="removeMetaQuery(filterTag.filterId, filterTag.value, filterTag.singleValue)">
            {{ filterTag.singleValue != undefined ? filterTag.singleValue : filterTag.value }}
        </b-tag>
        <button 
                @click="clearAllFilters()"
                id="button-clear-all"
                class="button is-outlined">
            {{ $i18n.get('label_clear_filters') }}
        </button>
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
            },
            clearAllFilters() {
                // this.$eventBusSearch.clearAllFilters();
                for (let tag of this.filterTags) {
                    this.removeMetaQuery(tag.filterId, tag.value, tag.singleValue);
                }
            }
        }
    }
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .filter-tags-list {
        width: 100%;
        padding: 0 $page-side-padding 1.5rem $page-side-padding;
        font-size: 0.75rem;
        margin-bottom: -0.375rem;

        @media only screen and (max-width: 768px) { 
            padding-top: 1rem;
        }   

        &.is-inline-flex {
            flex-wrap: wrap;
            justify-content: flex-start;   
        }  
        
        #button-clear-all {
            margin-left: auto;
        }
    }

</style>
