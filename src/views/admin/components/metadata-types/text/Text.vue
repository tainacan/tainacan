<template>
    <div>
        <b-input
                v-if="!getDisplayAutocomplete"
                :disabled="disabled"
                :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
                :value="value"
                :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : ''"
                @input="onInput($event)"
                @blur="onBlur"
                @focus="onMobileSpecialFocus" />
        <b-autocomplete
                v-else
                :disabled="disabled"
                :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
                :value="value"
                @blur="onBlur"
                :data="options"
                :loading="isLoadingOptions"
                @input="($event) => { search($event); }"
                field="label"
                @select="onSelect"
                clearable
                :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : ''"
                check-infinite-scroll
                @infinite-scroll="searchMore"
                @focus="onMobileSpecialFocus">
            <template #header>
                <span v-if="!isLoadingOptions && options && options.length">
                    {{ $i18n.get('info_metadata_autocomplete_suggestions') }}
                </span>
            </template>
            <template slot-scope="props">
                <div class="media">
                    <div class="media-content">
                        <span class="ellipsed-text">{{ props.option.label }}</span>
                    </div>
                </div>
            </template>
        </b-autocomplete>
    </div>
</template>

<script>
    import { isCancel } from '../../../js/axios';
    import { dynamicFilterTypeMixin } from '../../../js/filter-types-mixin';

    export default {
        mixins: [dynamicFilterTypeMixin],
        props: {
            itemMetadatum: Object,
            value: [String, Number, Array],
            disabled: false
        },
        data() {
            return {
                selected:'',
                options: [],
                label: '',
                searchQuery: '',
                searchOffset: 0,
                searchNumber: 12,
                totalFacets: 0,
                query: '',
                currentCollectionId: '',
                filter: undefined
            }
        },
        computed: {
            getDisplayAutocomplete() {
                if (this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.display_suggestions)
                    return this.itemMetadatum.metadatum.metadata_type_options.display_suggestions == 'yes';
                else
                    return false;
            }
        },
        created() {
            // These values are set to allow the usage of the getValuesPlainText function from the DynamicFilterTypeMixin
            this.currentCollectionId = this.itemMetadatum && this.itemMetadatum.metadatum && this.itemMetadatum.metadatum.collection_id ? this.itemMetadatum.metadatum.collection_id : null;
            this.filter = { collection_id: this.currentCollectionId };
        },
        methods: {
            onInput(value) {
                this.$emit('input', value);
            },
            onBlur() {
                this.$emit('blur');
            },
            onSelect(option){
                
                if (!option)
                    return;

                this.selected = option.value;
                this.label = option.label;

                this.onInput(this.label);
            },
            search: _.debounce( function(query) {

                // String update
                if (query != this.searchQuery) {
                    this.searchQuery = query;
                    this.options = [];
                    this.searchOffset = 0;
                }
                
                // Updates metadata
                this.onInput(query);
                
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

                    let promise = null;

                    // Cancels previous Request
                    if (this.getOptionsValuesCancel != undefined)
                        this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                    promise = this.getValuesPlainText({
                        metadatumId: this.itemMetadatum.metadatum.id,
                        search: this.searchQuery,
                        isRepositoryLevel: this.currentCollectionId == 'default', 
                        valuesToIgnore: [], 
                        offset: this.searchOffset,
                        number: this.searchNumber,
                        isInCheckboxModal: false,
                        countItems: false
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
            onMobileSpecialFocus() {
                this.$emit('mobileSpecialFocus');
            }
        }
    }
</script>