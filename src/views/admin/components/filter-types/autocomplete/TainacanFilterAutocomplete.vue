<template>
    <div class="block">
        <b-autocomplete
                v-model="selected"
                v-a11y-autocomplete
                icon="magnify"
                size="is-small"
                :aria-labelledby="'filter-label-id-' + filter.id"
                :data="options"
                expanded
                :loading="isLoadingOptions"
                field="label"
                clearable
                :placeholder="filter.placeholder ? filter.placeholder : ( (metadatumType === 'Tainacan\\Metadata_Types\\Relationship') ? $i18n.get('info_type_to_search_items') : $i18n.get('info_type_to_search_metadata') )"
                check-infinite-scroll
                @update:model-value="($event) => { resetPage(); search($event); }"
                @select="onSelect"
                @infinite-scroll="searchMore">
            <template #default="props">
                <div class="media">
                    <div
                            v-if="props.option.img"
                            class="media-left">
                        <img
                                width="24"
                                alt=""
                                :src="props.option.img">
                    </div>
                    <div    
                            :style="{ width: props.option.img ? '' : '100%'}"
                            class="media-content">
                        <span class="ellipsed-text">{{ props.option.label }}</span>
                        <span 
                                v-if="props.option.total_items != undefined"
                                class="has-text-dark">{{ "(" + props.option.total_items + ")" }}</span>
                    </div>
                </div>
            </template>
            <template 
                    v-if="!isLoadingOptions" 
                    #empty>
                {{ $i18n.get('info_no_options_found'	) }}
            </template>
        </b-autocomplete>
    </div>
</template>

<script>
    import qs from 'qs';
    import { tainacanApi, isCancel } from '../../../js/axios'
    import { filterTypeMixin, dynamicFilterTypeMixin } from '../../../js/filter-types-mixin';

    export default {
        mixins: [filterTypeMixin, dynamicFilterTypeMixin],
        emits: [
            'input',
        ],
        data(){
            return {
                selected:'',
                options: [],
                label: '',
                searchQuery: '',
                searchOffset: 0,
                searchNumber: 12,
                totalFacets: 0
            }
        },
        computed: {
            usesRelationshipValues() {
                return this.metadatumType === 'Tainacan\\Metadata_Types\\Relationship' ||
                    this.metadatumType === 'Tainacan\\Metadata_Types\\Control';
            }
        },
        watch: {
            'query': {
                handler() {
                    this.updateSelectedValues();
                },
                deep: true
            }
        },
        mounted() {
            this.updateSelectedValues();
        },
        methods: {
            onSelect(option){
                
                if(!option)
                    return;
                this.selected = option.value;
                this.label = option.label;

                this.$emit('input', {
                    filter: 'autocomplete',
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    value: this.selected
                });
                this.updateSelectedValues();
            },
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

                if (this.searchQuery != '') {

                    // Cancels previous Request
                    if (this.getOptionsValuesCancel != undefined)
                        this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                    const promise = this.usesRelationshipValues
                        ? this.getValuesRelationship({
                            search: this.searchQuery,
                            isRepositoryLevel: this.isRepositoryLevel,
                            valuesToIgnore: [],
                            offset: this.searchOffset,
                            number: this.searchNumber
                        })
                        : this.getValuesPlainText({
                            metadatumId: this.metadatumId,
                            search: this.searchQuery,
                            isRepositoryLevel: this.isRepositoryLevel,
                            valuesToIgnore: [],
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
                
                } else {
                    this.label = '';
                    this.selected = '';
                }
            }, 500),
            searchMore: _.debounce(function () {
                this.shouldAddOptions = true;
                this.search(this.searchQuery);
            }, 250),
            updateSelectedValues(){

                if (!this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ))
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key == this.metadatumId);
                if (index >= 0) {
                    let metadata = this.query.metaquery[ index ];

                    if (this.metadatumType === 'Tainacan\\Metadata_Types\\Relationship') {

                        let endpoint = '/items/' + metadata.value + '?fetch_only=title,thumbnail';

                        tainacanApi.get(endpoint)
                            .then( res => {
                                let item = res.data;
                                this.label = item.title;
                                this.selected = item.title;
                            })
                            .catch(error => {
                                this.$console.log(error);
                            });
                    } else if (this.metadatumType === 'Tainacan\\Metadata_Types\\Control') {

                        let endpoint = `/collection/${this.filter.collection_id}/facets/${this.filter.metadatum.metadatum_id}?getSelected=1&offset=0&number=1&count_items=0`;

                        if (this.isRepositoryLevel)
                            endpoint = `/facets/${this.filter.metadatum.metadatum_id}?getSelected=1&offset=0&number=1&count_items=0`;
                        else if (this.filter.collection_id == 'default' && this.currentCollectionId)
                            endpoint = `/collection/${this.currentCollectionId}/facets/${this.filter.metadatum.metadatum_id}?getSelected=1&offset=0&number=1&count_items=0`;

                        let currentQuery = JSON.parse(JSON.stringify(this.query));
                        if (currentQuery.fetch_only != undefined)
                            delete currentQuery.fetch_only;

                        tainacanApi.get(endpoint + '&' + qs.stringify({ 'current_query': currentQuery }))
                            .then( res => {
                                const values = res.data.values || res.data;
                                const match = (Array.isArray(values) ? values : []).find(option => String(option.value) === String(metadata.value));
                                this.label = match ? match.label : metadata.value;
                                this.selected = this.label;
                            })
                            .catch(error => {
                                this.$console.log(error);
                                this.label = metadata.value;
                                this.selected = metadata.value;
                            });
                    } else {
                        this.label = metadata.value;
                        this.selected = metadata.value;
                    }
                } else {
                    this.label = '';
                    this.selected = '';
                }
            }
        }
    }
</script>