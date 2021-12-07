<template>
    <div 
            :class="{ 'skeleton': isLoadingOptions }"
            class="block">
        <b-select
                v-if="!isLoadingOptions"
                :value="selected"
                :aria-labelledby="'filter-label-id-' + filter.id"
                @input="($event) => { resetPage(); onSelect($event) }"
                :placeholder="$i18n.get('label_selectbox_init')"
                expanded>
            <option value="">{{ $i18n.get('label_selectbox_init') }}...</option>
            <option
                    v-for="(option, index) in options"
                    :key="index"
                    :label="option.label"
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
                immediate: true
            }
        },
        mounted() {
            if (!this.isUsingElasticSearch)
                this.loadOptions(); 
        },
        created() {
            this.$eventBusSearch.$on('has-to-reload-facets', this.reloadOptions);
        },
        beforeDestroy() {
            this.$eventBusSearch.$off('has-to-reload-facets', this.reloadOptions); 
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
                    isRepositoryLevel: this.isRepositoryLevel
                });
                promise.request
                    .then((res) => {
                        this.updateSelectedValues();
                        
                        if (res && res.data && res.data.values)
                            this.$emit('updateParentCollapse', res.data.values.length > 0 );
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
