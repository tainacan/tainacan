<template>
    <transition name="filter-item">
        <div 
                class="is-inline-flex"
                v-if="filterTags != undefined && filterTags.length > 0">
            <b-tag            
                    v-for="(filterTag, index) of filterTags"
                    :key="index"       
                    attached
                    closable
                    @close="removeMetaQuery(filterTag)">
                {{ filterTag.singleLabel != undefined ? filterTag.singleLabel : filterTag.label }}
            </b-tag>
            <button 
                    @click="clearAllFilters()"
                    id="button-clear-all"
                    class="button is-outlined">
                {{ $i18n.get('label_clear_filters') }}
            </button>
        </div>
    </transition>
</template>
<script>
    import { mapGetters } from 'vuex';

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
                    if (Array.isArray(tag.label)) {
                        for (let i = 0; i < tag.label.length; i++) 
                            flattenTags.push({filterId: tag.filterId, label: tag.label, singleLabel: tag.label[i], value: tag.value[i], taxonomy: tag.taxonomy, metadatumId: tag.metadatumId}); 
                    } else {
                        flattenTags.push(tag);
                    }
                }
                return flattenTags;
            }
        },
        methods: {
            ...mapGetters('search',[
                'getFilterTags'
            ]),
            removeMetaQuery({ filterId, value, singleLabel, label, taxonomy, metadatumId }) {
                this.$eventBusSearch.removeMetaFromFilterTag({ 
                    filterId: filterId,
                    singleLabel: singleLabel,
                    label: label,
                    value: value, 
                    taxonomy: taxonomy,
                    metadatumId: metadatumId 
                });
            },
            clearAllFilters() {
                // this.$eventBusSearch.clearAllFilters();
                for (let tag of this.filterTags) {
                    this.removeMetaQuery(tag);
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
