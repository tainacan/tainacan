<template>
    <div>
        <b-select
                v-model="selectedInterval"
                expanded
                :placeholder="filter.placeholder ? filter.placeholder : $i18n.get('instruction_select_a_interval')"
                @update:model-value="($event) => { resetPage; changeInterval($event) }">
            <option value="">
                {{ $i18n.get('label_selectbox_init') }}...
            </option>
            <option
                    v-for="(interval, index) in filterTypeOptions.intervals"
                    :key="index"
                    :value="index">
                {{ interval.label }}
            </option>
        </b-select>
    </div>
</template>

<script>
    import { filterTypeMixin } from '../../../js/filter-types-mixin';
    export default {
        mixins: [ filterTypeMixin ],
        emits: [
            'input',
        ],
        data() {
            return {
                valueInit: 0,
                valueEnd: 10,
                selectedInterval: ''
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
            changeInterval() {
                if (this.selectedInterval !== '') {
                    this.valueInit = this.filterTypeOptions.intervals[this.selectedInterval].from;
                    this.valueEnd = this.filterTypeOptions.intervals[this.selectedInterval].to;
                    this.emit();
                } else {
                    this.$emit('input', {
                        type: 'DECIMAL',
                        compare: 'BETWEEN',
                        metadatum_id: this.metadatumId,
                        collection_id: this.collectionId,
                        value: [null, null]
                    });
                    this.valueEnd = null;
                    this.valueInit = null;
                }
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
            },
            updateSelectedValues(){
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

                    this.selectedInterval = this.filterTypeOptions.intervals.findIndex(
                        anInterval => anInterval.from == this.valueInit && anInterval.to == this.valueEnd
                    );
                    this.selectedInterval = this.selectedInterval >= 0 ? this.selectedInterval : '';

                } else {
                    this.valueInit = null;
                    this.valueEnd = null;
                    this.selectedInterval = '';
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
