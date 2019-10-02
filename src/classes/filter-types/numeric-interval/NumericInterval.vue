<template>
    <div>
        <template v-if="options.inputMode == 'custom'">
            <b-numberinput
                    :aria-labelledby="labelId"
                    size="is-small"
                    @input="validate_values()"
                    :step="options.step"
                    v-model="valueInit"/>
            <p class="is-size-7 has-text-centered is-marginless">{{ $i18n.get('label_until') }}</p>
            <b-numberinput
                    :aria-labelledby="labelId"
                    size="is-small"
                    @input="validate_values()"
                    :step="options.step"
                    v-model="valueEnd"/>
        </template>
        <template v-if="options.inputMode == 'list'">
            <b-select 
                    placeholder="Select a name"
                    @input="changeInterval"
                    v-model="selectedInterval">
                <option
                        v-for="(interval, index) in options.intervals"
                        :value="index"
                        :key="index">
                    {{ interval.label }} 
                </option>
            </b-select>
        </template>
    </div>
</template>

<script>

    export default {
        created() {
            this.collectionId = this.filter.collection_id;
            this.metadatumId = this.filter.metadatum.metadatum_id;
            this.options = this.filter.filter_type_options;

            this.$eventBusSearch.$on('removeFromFilterTag', this.cleanSearchFromTags);
        },
        data(){
            return {
                valueInit: 0,
                valueEnd: 10,
                isValid: false,
                collectionId: '',
                metadatum: '',
                options: [],
                selectedInterval: ''
            }
        },
        props: {
            filter: Object,
            labelId: '',
            query: Object,
            isRepositoryLevel: Boolean
        },
        methods: {
            // only validate if the first value is higher than first
            validate_values: _.debounce( function (){
                if ( parseFloat( this.valueInit ) > parseFloat( this.valueEnd )) {
                    //this.valueEnd = parseFloat( this.valueInit ) + 1;
                    //this.error_message();
                    return;
                }

                this.emit();
            }, 600),
            // message for error
            error_message(){
                if ( !this.isTouched ) return false;

                this.$buefy.toast.open({
                    duration: 3000,
                    message: this.$i18n.get('info_error_first_value_greater'),
                    position: 'is-bottom',
                    type: 'is-danger'
                })
            },
            
            cleanSearchFromTags(filterTag) {
                if (filterTag.filterId == this.filter.id)
                    this.clearSearch();
            },
            changeInterval() {
                if (this.selectedInterval !== '') {
                    this.valueInit = this.options.intervals[this.selectedInterval].from;
                    this.valueEnd = this.options.intervals[this.selectedInterval].to;
                    this.emit();
                } else {
                    this.clearSearch();
                }
                
            },
            clearSearch(){

                this.$emit('input', {
                    filter: 'range',
                    compare: 'BETWEEN',
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    value: ''
                });
                this.valueEnd = null;
                this.valueInit = null;
                
            },
            // emit the operation for listeners
            emit() {
                let values =  [ this.valueInit, this.valueEnd ];
                let type = ! Number.isInteger( this.valueInit ) || ! Number.isInteger( this.valueEnd ) ? 'DECIMAL' : 'NUMERIC';

                this.$emit('input', {
                    type: type,
                    //filter: 'range',
                    compare: 'BETWEEN',
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    value: values
                });

                if (values[0] != undefined && values[1] != undefined) {
                    this.$eventBusSearch.$emit( 'sendValuesToTags', {
                        filterId: this.filter.id,
                        value: values[0] + ' - ' + values[1]
                    });
                }
            },
            selectedValues(){
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key == this.metadatumId );
                if ( index >= 0 ){
                    let metaquery = this.query.metaquery[ index ];
                    if ( metaquery.value && metaquery.value.length > 1 ) {
                        this.valueInit = metaquery.value[0];
                        this.valueEnd = metaquery.value[1];
                    }

                    if (metaquery.value[0] != undefined && metaquery.value[1] != undefined) {
                        this.$eventBusSearch.$emit( 'sendValuesToTags', {
                            filterId: this.filter.id,
                            value: this.valueInit + ' - ' + this.valueEnd
                        });
                    }
                    
                    if (this.options.inputMode == 'list') {
                        this.selectedInterval = this.options.intervals.findIndex(
                            anInterval => anInterval.from == this.valueInit && anInterval.to == this.valueEnd
                        );
                    }

                } else {
                    return false;
                }
            },
        },
        mounted() {
            this.selectedValues();
        },
        beforeDestroy() {
            this.$eventBusSearch.$off('removeFromFilterTag', this.cleanSearchFromTags);
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
