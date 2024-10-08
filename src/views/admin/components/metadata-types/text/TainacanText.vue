<template>
    <div>
        <div 
                v-if="!getDisplayAutocomplete"
                class="control is-clearfix">
            <input  
                    :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
                    v-imask="getMask"
                    class="input"
                    :disabled="disabled"
                    :value="localValue"
                    :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : ''"
                    :maxlength="getMaxlength"
                    @focus="onMobileSpecialFocus"
                    @complete="($event) => getMask ? onInput($event.detail.value) : null"
                    @input="($event) => getMask ? null : onInput($event.target.value)"
                    @blur="onBlur">
            <small
                    v-if="localValue && getMaxlength"
                    class="help counter"
                    :class="{ 'is-invisible': !isInputFocused }">
                {{ localValue.length }} / {{ getMaxlength }}
            </small>
        </div>
        <b-autocomplete
                v-else
                :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
                :disabled="disabled"
                :model-value="localValue"
                :data="options"
                :loading="isLoadingOptions"
                field="label"
                clearable
                :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : ''"
                check-infinite-scroll
                :maxlength="getMaxlength"
                @blur="onBlur"
                @update:model-value="($event) => { search($event); }"
                @select="onSelect"
                @infinite-scroll="searchMore"
                @focus="onMobileSpecialFocus">
            <template #header>
                <span v-if="!isLoadingOptions && options && options.length">
                    {{ $i18n.get('info_metadata_autocomplete_suggestions') }}
                </span>
            </template>
            <template #default="props">
                <div class="media">
                    <div class="media-content">
                        <span class="ellipsed-text">{{ props.option.label }}</span>
                    </div>
                </div>
            </template>
            <template #empty>
                {{ $i18n.get('info_nothing_like_this_so_far') }}
            </template>
        </b-autocomplete>
    </div>
</template>

<script>
    import { isCancel } from '../../../js/axios';
    import { dynamicFilterTypeMixin } from '../../../js/filter-types-mixin';
    import { IMaskDirective } from 'vue-imask';

    export default {
        directives: {
            imask: IMaskDirective
        },
        mixins: [dynamicFilterTypeMixin],
        props: {
            itemMetadatum: Object,
            value: [String, Number, Array],
            disabled: false
        },
        emits: [
            'update:value',
            'blur',
            'mobile-special-focus'
        ],
        data() {
            return {
                localValue: '',
                selected:'',
                options: [],
                label: '',
                searchQuery: '',
                searchOffset: 0,
                searchNumber: 12,
                totalFacets: 0,
                query: '',
                currentCollectionId: '',
                filter: undefined,
                isInputFocused: false,
            }
        },
        computed: {
            getDisplayAutocomplete() {
                if (this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.display_suggestions)
                    return this.itemMetadatum.metadatum.metadata_type_options.display_suggestions == 'yes';
                else
                    return false;
            },
            getMask() {
                if (this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.mask)
                    return {
                        mask: this.itemMetadatum.metadatum.metadata_type_options.mask,
                        lazy: false
                    };
                else
                    return false;
            },
            getMaxlength() {
                if ( this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.maxlength !== null && this.itemMetadatum.metadatum.metadata_type_options.maxlength !== undefined && this.itemMetadatum.metadatum.metadata_type_options.maxlength !== '' )
                    return Number(this.itemMetadatum.metadatum.metadata_type_options.maxlength);
                else
                    return undefined;
            }
        },
        created() {
            // These values are set to allow the usage of the getValuesPlainText function from the DynamicFilterTypeMixin
            this.currentCollectionId = this.itemMetadatum && this.itemMetadatum.metadatum && this.itemMetadatum.metadatum.collection_id ? this.itemMetadatum.metadatum.collection_id : null;
            this.filter = { collection_id: this.currentCollectionId };
            this.localValue = this.value ? JSON.parse(JSON.stringify(this.value)) : '';
        },
        methods: {
            onInput(value) {
                const inputRef = this.$refs['tainacan-item-metadatum_id-' + this.itemMetadatum.metadatum.id + (this.itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + this.itemMetadatum.parent_meta_id) : '')];
                if ( inputRef && this.getMaxlength && !inputRef.checkHtml5Validity() )
                    return;

                this.localValue = value;
                this.changeValue(value);
            },
            changeValue: _.debounce(function(value) {
                this.$emit('update:value', value);
            }, 750),
            onBlur() {
                this.isInputFocused = false;
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
                this.isInputFocused = true;
                this.$emit('mobile-special-focus');
            }
        }
    }
</script>