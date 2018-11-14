<template>
    <div class="extra-margin">
        <template v-if="taxonomyFilters != undefined">
            <div 
                    :key="index"
                    v-for="(taxonomyFilter, key, index) of taxonomyFilters">
                <div 
                        v-tooltip="{
                            content: $i18n.get('label_filters_from') + ' ' + taxonomyFiltersCollectionNames[key] + ': ',
                            autoHide: false,
                            placement: 'auto-start'
                        }" 
                        v-if="taxonomyFiltersCollectionNames[key] != undefined != undefined && taxonomyFiltersCollectionNames[key] != undefined"
                        class="collection-name">
                    {{ $i18n.get('label_filters_from') + " " + taxonomyFiltersCollectionNames[key] + ": " }}
                </div>
                <tainacan-filter-item
                        v-show="!isMenuCompressed"        
                        :query="getQuery"
                        v-for="(filter, filterIndex) in taxonomyFilter"
                        :key="filterIndex"
                        :filter="filter"
                        :open="collapsed"
                        :is-repository-level="isRepositoryLevel"/>
                <hr>
            </div>
        </template>
        <template v-else>
             <collections-filter
                    :open="collapsed"
                    :query="getQuery"
                    v-if="isRepositoryLevel"/>
            <tainacan-filter-item
                    v-show="!isMenuCompressed"        
                    :query="getQuery"
                    v-for="(filter, index) in filters"
                    :key="index"
                    :filter="filter"
                    :open="collapsed"
                    :is-repository-level="isRepositoryLevel"/>
        </template>

    </div>
</template> 

<script>
    import { mapGetters, mapActions } from 'vuex';
    import CollectionsFilter from '../repository/collection-filter/collection-filter.vue';

    export default {
        data() {
            return {
                taxonomyFiltersCollectionNames: Object
            }
        },
        props: {
            filters: Array,
            collapsed: Boolean,
            isRepositoryLevel: Boolean,
            taxonomyFilters: Object,
            taxonomy: String
        },
        watch: {
            taxonomyFilters() {
                if (this.taxonomyFilters != undefined) {
                    this.$set(this.taxonomyFiltersCollectionNames, 'repository-filters', this.$i18n.get('repository'));
                    for (let taxonomyFilter of Object.keys(this.taxonomyFilters)) {
                        if (taxonomyFilter != 'repository-filters') {
                            this.fetchCollectionName(taxonomyFilter)
                                .then((collectionName) => {
                                    this.$set(this.taxonomyFiltersCollectionNames, taxonomyFilter, collectionName);
                                });
                        }
                    }
                }
            }
        },
        methods: {
            ...mapGetters('search',[
                'getPostQuery'
            ]),
            ...mapActions('collection',[
                'fetchCollectionName'
            ]),
        },
        computed: {
            getQuery() {
                return this.getPostQuery();
            },
            taxonomyId () {
                let taxonomyArray = this.taxonomy.split("_");
                return taxonomyArray[taxonomyArray.length - 1];
            }
        },
        components: {
            CollectionsFilter
        }
    }
</script>

<style>
    .extra-margin {
        margin-bottom: 40px;
    }
    .collection-name {
        color: #454647;
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.875rem;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
        width: 100%;
    }
</style>
