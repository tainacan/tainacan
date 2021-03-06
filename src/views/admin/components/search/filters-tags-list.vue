<template>
    <transition name="filter-item">
        <div 
                class="is-inline-flex"
                v-if="filterTags != undefined && filterTags.length > 0">
            <p style="margin-bottom: 0;">
                <strong>{{ totalItems }}</strong>
                {{ ' ' + ( totalItems == 1 ? $i18n.get('info_item_found') : $i18n.get('info_items_found') ) + ', ' }}
                <strong>{{ filterTags.length }}</strong>
                {{ ' ' + ( filterTags.length == 1 ? $i18n.get('info_applied_filter') : $i18n.get('info_applied_filters') ) + ': ' }}
                &nbsp;&nbsp;
            </p>
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
            },
            totalItems() {
                return this.getTotalItems()
            }
        },
        methods: {
            ...mapGetters('search',[
                'getFilterTags',
                'getTotalItems'
            ]),
            removeMetaQuery({ filterId, value, singleLabel, label, taxonomy, metadatumId }) {
                this.$eventBusSearch.resetPageOnStore();
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
                this.$eventBusSearch.resetPageOnStore();
                for (let tag of this.filterTags) {
                    this.removeMetaQuery(tag);
                }
            }
        }
    }
</script>

<style lang="scss" scoped>

    .filter-tags-list {
        width: 100%;
        padding: 1em var(--tainacan-one-column) 1em var(--tainacan-one-column);
        font-size: 0.75em;

        @media only screen and (max-width: 768px) { 
            padding-top: 1em;
        }   

        &.is-inline-flex {
            flex-wrap: wrap;
            justify-content: flex-start;   
            align-items: baseline;
        }  
        
        #button-clear-all {
            margin-left: auto;
            font-size: 1em !important;
        }
    }

</style>
