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
                open-on-focus
                @focus="browseFilterOptions"
                @active="onFilterSuggestionsActive"
                @typing="search"
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
            selected(value) {
                if (!value)
                    this.onSelect(null);
            },
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
                if (!option) {
                    if (this.selected || !this.label)
                        return;

                    this.label = '';
                    this.resetPage();
                    this.$emit('input', {
                        filter: 'autocomplete',
                        metadatum_id: this.metadatumId,
                        collection_id: this.collectionId,
                        value: ''
                    });
                    return;
                }

                this.selected = option.value;
                this.label = option.label;

                this.resetPage();
                this.$emit('input', {
                    filter: 'autocomplete',
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    value: this.selected
                });
                this.updateSelectedValues();
            },
            onFilterSuggestionsActive(isOpen) {
                if (isOpen)
                    return;

                if (this.label && this.selected !== this.label)
                    this.selected = this.label;
            },
            browseFilterOptions() {
                this.searchQuery = '';
                this.searchOffset = 0;
                this.totalFacets = 0;
                this.shouldAddOptions = false;
                this.options = [];
                this.requestFilterOptions('');
            },
            search: _.debounce( function(query) {
                const text = query || '';

                if (this.label && text === this.label)
                    return;

                if (text !== this.searchQuery) {
                    this.searchQuery = text;
                    this.options = [];
                    this.searchOffset = 0;
                    this.totalFacets = 0;
                    this.shouldAddOptions = false;
                }

                if (!text.length)
                    this.label = '';

                if (this.searchOffset > 0 && this.options.length >= Number(this.totalFacets))
                    return;

                this.requestFilterOptions(text);
            }, 500),
            requestFilterOptions(query) {
                if (this.getOptionsValuesCancel)
                    this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                const promise = this.usesRelationshipValues
                    ? this.getValuesRelationship({
                        search: query,
                        isRepositoryLevel: this.isRepositoryLevel,
                        valuesToIgnore: [],
                        offset: this.searchOffset,
                        number: this.searchNumber
                    })
                    : this.getValuesPlainText({
                        metadatumId: this.metadatumId,
                        search: query,
                        isRepositoryLevel: this.isRepositoryLevel,
                        valuesToIgnore: [],
                        offset: this.searchOffset,
                        number: this.searchNumber
                    });

                promise.request
                    .then( res => {
                        if (res && res.fromAggregations) {
                            this.totalFacets = this.options.length;
                            this.searchOffset = this.options.length;
                            return;
                        }

                        this.totalFacets = res.headers['x-wp-total'];
                        this.searchOffset += this.searchNumber;
                    })
                    .catch( error => {
                        const cause = error && error.error ? error.error : error;
                        if (isCancel(cause))
                            this.$console.log('Request canceled: ' + (cause.message || error.message));
                        else
                            this.$console.error( error );
                    });

                this.getOptionsValuesCancel = promise.source;
            },
            searchMore: _.debounce(function () {
                if (this.searchOffset > 0 && this.options.length >= Number(this.totalFacets))
                    return;

                this.shouldAddOptions = true;
                this.requestFilterOptions(this.searchQuery);
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