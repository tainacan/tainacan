<template>
    <div class="block">
        <b-taginput
                icon="magnify"
                size="is-small"
                :data="options"
                autocomplete
                expanded
                :loading="isLoadingOptions"
                :remove-on-keys="[]"
                field="label"
                attached
                @input="($event) => { resetPage(); onSelect($event) }"
                @typing="search"
                :aria-close-label="$i18n.get('remove_value')"
                :aria-labelledby="'filter-label-id-' + filter.id"
                :placeholder="getInputPlaceholder"
                check-infinite-scroll
                @infinite-scroll="searchMore">
            <template slot-scope="props">
                <div class="media">
                    <div
                            class="media-left"
                            v-if="props.option.img">
                        <img
                                :alt="$i18n.get('label_thumbnail')"
                                width="24"
                                :src="`${props.option.img}`">
                    </div>
                    <div class="media-content">
                        <span class="ellipsed-text">{{ props.option.label }}</span>
                        <span 
                                v-if="props.option.total_items != undefined"
                                class="has-text-gray">{{ "(" + props.option.total_items + ")" }}</span>
                    </div>
                </div>
            </template>
            <template 
                    v-if="!isLoadingOptions" 
                    slot="empty">
                {{ $i18n.get('info_no_options_found'	) }}
            </template>
        </b-taginput>
    </div>
</template>

<script>
    import { isCancel } from '../../../js/axios';
    import { filterTypeMixin, dynamicFilterTypeMixin } from '../../../js/filter-types-mixin';

    export default {
        mixins: [filterTypeMixin, dynamicFilterTypeMixin],
        data() {
            return {
                results:'',
                selected:[], // Simple array of IDs, no more objects and not bound to the taginput
                options: [],
                relatedCollectionId: '',
                searchQuery: '',
                searchOffset: 0,
                searchNumber: 12,
                totalFacets: 0
            }
        },
        computed: {
            getInputPlaceholder() {
                if (this.metadatumType == 'Tainacan\\Metadata_Types\\Relationship') 
                    return this.$i18n.get('info_type_to_add_items');
                else if (this.metadatumType == 'Tainacan\\Metadata_Types\\User')
                    return this.$i18n.get('info_type_to_add_users');  
                else 
                    return this.$i18n.get('info_type_to_add_metadata');
            }
        },
        watch: {
            'query'() {
                this.updateSelectedValues();
            }
        },
        created() {
            if (this.metadatumType === 'Tainacan\\Metadata_Types\\Relationship' && 
                this.filter.metadatum && 
                this.filter.metadatum.metadata_type_object && 
                this.filter.metadatum.metadata_type_object.options &&
                this.filter.metadatum.metadata_type_object.options.collection_id) {
                    this.relatedCollectionId = this.filter.metadatum.metadata_type_object.options.collection_id;
                }
        },
        mounted() {
            this.updateSelectedValues();
        },
        methods: {
            search: _.debounce( function(query) {

                // String update
                if (query != this.searchQuery) {
                    this.searchQuery = query;
                    this.options = [];
                    this.searchOffset = 0;
                } 
                
                // String cleared
                if (!query.length) {
                    this.searchQuery = query;
                    this.options = [];
                    this.searchOffset = 0;
                }

                // No need to load more
                if (this.searchOffset > 0 && this.options.length >= this.totalFacets)
                    return;

                let promise = null;

                // Cancels previous Request
                if (this.getOptionsValuesCancel != undefined)
                    this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                if ( this.metadatumType === 'Tainacan\\Metadata_Types\\Relationship' || this.metadatumType === 'Tainacan\\Metadata_Types\\User' )
                    promise = this.getValuesRelationship({
                        search: this.searchQuery,
                        isRepositoryLevel: this.isRepositoryLevel,
                        valuesToIgnore: this.selected,
                        offset: this.searchOffset,
                        number: this.searchNumber
                    });
                else
                    promise = this.getValuesPlainText({
                        metadatumId: this.metadatumId,
                        search: this.searchQuery,
                        isRepositoryLevel: this.isRepositoryLevel,
                        valuesToIgnore: this.selected,
                        offset: this.searchOffset,
                        number: this.searchNumber
                    });

                promise.request
                    .then( res => {
                        this.totalFacets = res.headers['x-wp-total'];
                        this.searchOffset += this.searchNumber;
                    })
                    .catch( error => {
                        if (isCancel(error))
                            this.$console.log('Request canceled: ' + error.message);
                        else
                            this.$console.error( error );
                    });

                // Search Request Token for cancelling
                this.getOptionsValuesCancel = promise.source;
                
            }, 500),
            searchMore: _.debounce(function () {
                this.search(this.searchQuery);
            }, 250),
            updateSelectedValues() {

                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                // Cleared either way, we might be coming from a situation where all the filters were removed.
                this.selected = [];

                const index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key == this.metadatumId );
                if (index >= 0) {
                    const metadata = this.query.metaquery[ index ];
                    for (let item of metadata.value)
                        this.selected.push(item);
                }
            },
            onSelect(selection) {
                this.$emit('input', {
                    filter: 'taginput',
                    compare: 'IN',
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    value: _.union(this.selected, selection.map(anOption => anOption.value))
                });
            }
        }
    }
</script>