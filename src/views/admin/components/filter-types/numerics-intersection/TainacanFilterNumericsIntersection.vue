<template>
    <div>
        <b-numberinput
                v-model="valueInit"
                :aria-labelledby="'filter-label-id-' + filter.id"
                :aria-minus-label="$i18n.get('label_decrease')"
                :aria-plus-label="$i18n.get('label_increase')"
                size="is-small"
                :step="filterTypeOptions.step"
                @update:model-value="($event) => { resetPage(); validadeValues($event) }"
            />
        <p 
                v-if="filterTypeOptions.accept_numeric_interval === 'yes'"
                style="font-size: 0.75em; margin-bottom: 0.125em;"
                class="has-text-centered is-marginless">
            {{ $i18n.get('label_until') }}
        </p>
        <b-numberinput
                v-if="filterTypeOptions.accept_numeric_interval === 'yes'"
                v-model="valueEnd"
                :aria-labelledby="'filter-label-id-' + filter.id"
                :aria-minus-label="$i18n.get('label_decrease')"
                :aria-plus-label="$i18n.get('label_increase')"
                size="is-small"
                :step="filterTypeOptions.step"
                @update:model-value="($event) => { resetPage(); validadeValues($event) }" />
        
    </div>
</template>

<script>
    import { filterTypeMixin } from '../../../js/filter-types-mixin';
    export default {
        mixins: [ filterTypeMixin ],
        emits: [
            'input',
        ],
        data(){
            return {
                valueInit: null,
                valueEnd: null
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
            // only validate if the first value is higher than first
            validadeValues: _.debounce( function () {
                if ( this.filterTypeOptions.accept_numeric_interval !== 'yes' )
                    this.valueEnd = this.valueInit;

                if (this.valueInit == null || this.valueEnd == null )
                    return

                if (this.valueInit.constructor == Number)
                    this.valueInit = this.valueInit.valueOf();

                if (this.valueEnd.constructor == Number)
                    this.valueEnd = this.valueEnd.valueOf();
                
                this.valueInit = parseFloat(this.valueInit);
                this.valueEnd = parseFloat(this.valueEnd);

                if ( isNaN(this.valueInit) || isNaN(this.valueEnd) )
                    return

                if ( this.filterTypeOptions.accept_numeric_interval === 'yes' && this.valueInit > this.valueEnd ) {                     
                    this.showErrorMessage();
                    return;
                }

                this.emit();
            }, 600),
            // message for error
            showErrorMessage(){
                this.$buefy.toast.open({
                    duration: 3000,
                    message: this.$i18n.get('info_error_first_value_greater'),
                    position: 'is-bottom',
                    type: 'is-danger'
                })
            },
            // emit the operation for listeners
            emit() {
                let values =  [ this.valueInit, this.valueEnd ];
                let type = ! Number.isInteger( this.valueInit ) || ! Number.isInteger( this.valueEnd ) ? 'DECIMAL(20,3)' : 'NUMERIC';

                if ( this.filterTypeOptions.accept_numeric_interval !== 'yes' ) {
                    this.$emit('input', {
                        filter: 'intersection',
                        type: type,
                        compare: this.filterTypeOptions.first_comparator,
                        metadatum_id: this.metadatumId,
                        collection_id: this.collectionId,
                        value: values[0]
                    });
                    this.$emit('input', {
                        filter: 'intersection',
                        type: type,
                        compare: this.filterTypeOptions.second_comparator,
                        metadatum_id: this.filterTypeOptions.secondary_filter_metadatum_id,
                        collection_id: this.collectionId,
                        value: values[0],
                        secondary: true
                    });
                } else {
                    // Much more complicated logic to be implemented in the future. See #889
                }
            },
            updateSelectedValues(){
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key == this.metadatumId );
                if ( index >= 0 ) {

                    let metaquery = this.query.metaquery[ index ];
                    if ( metaquery.value ) {
                        if ( Array.isArray(metaquery.value) && metaquery.value.length > 1 ) {
                            this.valueInit = new Number(metaquery.value[0]);
                            this.valueEnd = new Number(metaquery.value[1]);
                        } else {
                            this.valueInit = new Number(metaquery.value);
                        }
                    }
                } else {
                    this.valueInit = null;
                    this.valueEnd = null;
                }
            },
        }
    }
</script>

<style scoped>
    .field {
        margin-bottom: 0.125em !important;
    }
</style>
