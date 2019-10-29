<template>
    <div 
            :class="{ 'skeleton': isLoadingOptions }"
            class="block">
        <b-select
                v-if="!isLoadingOptions"
                :value="selected"
                :aria-labelledby="'filter-label-id-' + filter.id"
                @input="onSelect($event)"
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
    import { isCancel } from '../../../js/axios/axios';
    import { filterTypeMixin, dynamicFilterTypeMixin } from '../filter-types-mixin';

    export default {
        mixins: [filterTypeMixin, dynamicFilterTypeMixin],
        data(){
            return {
                options: [],
                selected: ''
            }
        },
        watch: {
            'query.metaquery'() {
                if (!this.isUsingElasticSearch)
                    this.loadOptions();
            }
        },
        mounted(){           
            if (!this.isUsingElasticSearch)
                this.loadOptions();
        },
        methods: {
            loadOptions(){
                // Cancels previous Request
                if (this.getOptionsValuesCancel != undefined)
                    this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                let promise = null;
                promise = this.getValuesPlainText( this.metadatumId, null, this.isRepositoryLevel );
                promise.request
                    .then(() => {
                        this.updateSelectedValues();
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

                this.$emit('sendValuesToTags', { label: this.selected, value: this.selected })
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
