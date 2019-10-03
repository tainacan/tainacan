<template>
    <div>
        <b-select
                :placeholder="$i18n.get('instruction_select_a_interval')"
                @input="changeInterval"
                v-model="selectedInterval">
            <option value="">
                {{ $i18n.get('label_clean') }}
            </option>
            <option
                    v-for="(interval, index) in options.intervals"
                    :value="index"
                    :key="index">
                {{ interval.label }}
            </option>
        </b-select>
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
        data() {
            return {
                valueInit: 0,
                valueEnd: 10,
                isValid: false,
                collectionId: '',
                metadatumId: '',
                options: [],
                selectedInterval: ''
            }
        },
        props: {
            filter: {
                type: Object // concentrate all attributes metadatum id and type
            },
            labelId: '',
            query: Object,
            isRepositoryLevel: Boolean,
        },
        methods: {
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
                    compare: 'BETWEEN',
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    value: values
                });

                if (values[0] != undefined && values[1] != undefined) {
                    let labelValue = this.options.intervals[this.selectedInterval].label + (this.options.showIntervalOnTag ? `(${values[0]}-${values[1]})` : '');
                    this.$eventBusSearch.$emit( 'sendValuesToTags', {
                        filterId: this.filter.id,
                        value: labelValue
                    });
                }
            },
            selectedValues(){
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key == this.metadatumId );
                if ( index >= 0 ) {
                    let metaquery = this.query.metaquery[ index ];

                    if ( !metaquery.value || 
                         !metaquery.value.length > 1 ||
                         metaquery.value[0] == undefined ||
                         metaquery.value[1] == undefined )
                         return false

                    this.valueInit = metaquery.value[0];
                    this.valueEnd = metaquery.value[1];

                    this.selectedInterval = this.options.intervals.findIndex(
                        anInterval => anInterval.from == this.valueInit && anInterval.to == this.valueEnd
                    );

                    let labelValue = this.options.intervals[this.selectedInterval].label + (this.options.showIntervalOnTag ? `(${this.valueInit}-${this.valueEnd})` : '');
                    this.$eventBusSearch.$emit( 'sendValuesToTags', {
                        filterId: this.filter.id,
                        value: labelValue
                    });
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
