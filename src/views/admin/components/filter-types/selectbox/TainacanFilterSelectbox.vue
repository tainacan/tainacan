<template>
    <div class="block">
        <b-select
                :loading="isLoadingOptions"
                :disabled="!isLoadingOptions && options.length <= 0"
                :model-value="selected"
                :aria-labelledby="'filter-label-id-' + filter.id"
                :placeholder="filter.placeholder ? filter.placeholder : $i18n.get('label_selectbox_init')"
                expanded
                @update:model-value="($event) => { resetPage(); onSelect($event) }">
            <option value="">
                {{ filter.placeholder ? filter.placeholder : $i18n.get('label_selectbox_init') }}
            </option>
            <option
                    v-for="(option, index) in options"
                    :key="index"
                    :label="option.label + ( option.total_items ? (' (' + option.total_items + ')') : '' )"
                    :value="option.value">
                {{ option.label }}
                <span 
                        v-if="option.total_items != undefined"
                        class="has-text-gray">{{ "(" + option.total_items + ")" }}</span>    
            </option>
        </b-select>
    </div>
</template>

<script>
    import { isCancel } from '../../../js/axios';
    import { filterTypeMixin, dynamicFilterTypeMixin } from '../../../js/filter-types-mixin';

    export default {
        mixins: [filterTypeMixin, dynamicFilterTypeMixin],
        emits: [
            'input',
            'update-parent-collapse'
        ],
        data(){
            return {
                options: [],
                selected: ''
            }
        },
        watch: {
            facetsFromItemSearch: {
                handler() {
                    if (this.isUsingElasticSearch)
                        this.loadOptions();
                },
                immediate: true,
                deep: true
            }
        },
        mounted() {
            if (!this.isUsingElasticSearch)
                this.loadOptions(); 
        },
        created() {
            this.$eventBusSearchEmitter.on('hasToReloadFacets', this.reloadOptions);
        },
        beforeUnmount() {
            this.$eventBusSearchEmitter.off('hasToReloadFacets', this.reloadOptions); 
        },
        methods: {
            reloadOptions(shouldReload) {
                if ( !this.isUsingElasticSearch && shouldReload )
                    this.loadOptions();
            },
            loadOptions(){
                // Cancels previous Request
                if (this.getOptionsValuesCancel != undefined)
                    this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                let promise = null;
                promise = this.getValuesPlainText({
                    metadatumId: this.metadatumId,
                    search: null,
                    isRepositoryLevel: this.isRepositoryLevel,
                    number: this.filter.max_options,
                    offset: 0
                });
                promise.request
                    .then((res) => {
                        this.updateSelectedValues();
                        
                        if (res && res.data && res.data.values)
                            this.$emit('update-parent-collapse', res.data.values.length > 0 );
                    })
                    .catch( error => {
                        if (isCancel(error))
                            this.$console.log('Request canceled: ' + error.message);
                        else
                            this.$console.error( error );
                    });

                // Search Request Token for cancelling
                this.getOptionsValuesCancel = promise.source;
            },
            updateSelectedValues() {
                if ( this.query && this.query.metaquery && Array.isArray( this.query.metaquery ) ) {

                    let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key == this.metadatumId );
                    if ( index >= 0) {
                        let metadata = this.query.metaquery[ index ];
                        if (this.selected != metadata.value) {
                            this.selected = metadata.value;
                        }
                    } else {
                        this.selected = '';
                    }
                } else {
                    this.selected = '';
                }
            },
            onSelect(value) {
                this.$emit('input', {
                    filter: 'selectbox',
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    value: value
                });

                this.updateSelectedValues();
            }
        }
    }
</script>

<style scoped>
    .skeleton {
        min-height: 36px;
    }
</style>
