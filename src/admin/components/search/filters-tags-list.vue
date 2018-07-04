<template>
    <div>
        <div
                class="is-inline-block"
                v-if="filterTags != undefined && filterTags.length > 0"
                v-for="(filterTag, index) of filterTags"
                :key="index">
            <b-tag
                    v-if="!Array.isArray(filterTag.value)"
                    attached
                    closable
                    @close="removeMetaQuery(filterTag.filterId, filterTag.value)">
                {{ filterTag.value }}
            </b-tag>
            <div 
                    class="is-flex"
                    style="align-items: flex-start"
                    v-if="Array.isArray(filterTag.value)">
                <b-tag
                        v-for="(value, otherIndex) of filterTag.value"
                        :key="otherIndex"
                        attached
                        closable
                        @close="removeMetaQuery(filterTag.filterId, filterTag.value, value)">
                    {{ value }}
                </b-tag>
            </div>
        </div>
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
                this.$console.log(this.getFilterTags());
                return this.getFilterTags();
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
            getFilterValue(query, value) {
                let filterIndex = this.filters.findIndex(filter => filter.metadatum.metadatum_id == query.key);
                if (filterIndex >= 0) {

                    this.fetchMetadatum({ collectionId: this.filters[filterIndex].collection_id, metadatumId: query.key })
                    .then((metadatum) => {
                        if (metadatum.metadatum_type == 'Tainacan\\Metadata_Types\\Relationship') {
                            // Fetch CollectionItem where, collection if == metadataum.metadatum_type_options.collection_id,
                            // item_id == value
                        }
                    })
                    .catch((error) => {
                        this.$console.log(error);
                    });
                    
                }
                return value;
            }
        },
        created() {
            
        }
    }
</script>