<template>
    <div>
        <b-numberinput
                :aria-labelledby="'filter-label-id-' + filter.id"
                size="is-small"
                @input="validadeValues()"
                :step="filterTypeOptions.step"
                v-model="valueInit"
                />
        <p class="is-size-7 has-text-centered is-marginless">{{ $i18n.get('label_until') }}</p>
        <b-numberinput
                :aria-labelledby="'filter-label-id-' + filter.id"
                size="is-small"
                @input="validadeValues()"
                :step="filterTypeOptions.step"
                v-model="valueEnd"/>
        
    </div>
</template>

<script>
    import { filterTypeMixin } from '../../../js/filter-types-mixin';
    export default {
        mixins: [ filterTypeMixin ],
        data(){
            return {
                valueInit: null,
                valueEnd: null
            }
        },
        watch: {
            'query.metaquery'() {
                this.updateSelectedValues();
            },
            'query.taxquery'() {
                this.updateSelectedValues();
            }
        },
        mounted() {
            this.updateSelectedValues();
        },
        methods: {
            // only validate if the first value is higher than first
            validadeValues: _.debounce( function () {
                if (this.valueInit == null || this.valueEnd == null)
                    return

                if (this.valueInit.constructor == Number)
                    this.valueInit = this.valueInit.valueOf();

                if (this.valueEnd.constructor == Number)
                    this.valueEnd = this.valueEnd.valueOf();
                
                this.valueInit = parseFloat(this.valueInit);
                this.valueEnd = parseFloat(this.valueEnd);

                if (isNaN(this.valueInit) || isNaN(this.valueEnd))
                    return

                if (this.valueInit > this.valueEnd) {                     
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

                this.$emit('input', {
                    type: type,
                    compare: 'BETWEEN',
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    value: values
                });

                if (values[0] != undefined && values[1] != undefined)
                    this.$emit('sendValuesToTags', { label: values[0] + ' - ' + values[1], value: values });
            },
            updateSelectedValues(){
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key == this.metadatumId );
                if ( index >= 0 ){
                    let metaquery = this.query.metaquery[ index ];
                    if ( metaquery.value && metaquery.value.length > 1 ) {
                        this.valueInit = new Number(metaquery.value[0]);
                        this.valueEnd = new Number(metaquery.value[1]);
                    }

                    if (metaquery.value[0] != undefined && metaquery.value[1] != undefined)
                        this.$emit('sendValuesToTags', { label: this.valueInit + ' - ' + this.valueEnd, value: metaquery.value });

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
        margin-bottom: 0.125rem !important;
    }
    p.is-size-7 {
        margin-bottom: 0.125rem !important;
    }
</style>
