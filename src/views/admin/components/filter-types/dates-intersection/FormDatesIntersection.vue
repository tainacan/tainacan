<template>
    <div>
        <b-field 
                :addons="false"
                :type="metadataType"
                :message="metadataMessage">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-filter-dates-intersection', 'secondary_filter_metadatum_id') }}<span :class="metadataType">&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-filter-dates-intersection', 'secondary_filter_metadatum_id')"
                        :message="$i18n.getHelperMessage('tainacan-filter-dates-intersection', 'secondary_filter_metadatum_id')" />
            </label>
            <b-select
                    v-model="secondDateMetadatumId"
                    name="dates_intersect[secondary_filter_metadatum_id]"
                    :placeholder="$i18n.get('instruction_select_second_date_to_compare' )"
                    :loading="loading"
                    expanded
                    @change="onUpdateSecondDateMetadatumId()"
                    @focus="clear()">
                <option :value="''">
                    {{ $i18n.get('instruction_select_second_date_to_compare' ) }}
                </option>
                <option
                        v-for="option in metadata.filter(aMetadatum => aMetadatum.id != filter.metadatumId )"
                        :key="option.id"
                        :value="option.id">
                    {{ option.name }}
                </option>
            </b-select>
        </b-field>
        <div style="column-count: 2;">
            <b-field :addons="false">
                <label 
                        style="line-height: normal;"
                        class="label is-inline">
                    {{ $i18n.getHelperTitle('tainacan-filter-dates-intersection', 'first_comparator') }}<span>&nbsp;*&nbsp;</span>
                    <help-button
                            :title="$i18n.getHelperTitle('tainacan-filter-dates-intersection', 'first_comparator')"
                            :message="$i18n.getHelperMessage('tainacan-filter-dates-intersection', 'first_comparator')" />
                </label>
                <b-select
                        v-model="firstComparator"
                        @update:model-value="emitValues()">
                    <option
                            v-for="(comparatorObject, comparatorKey) in comparatorsObject"
                            :key="comparatorKey" 
                            :value="comparatorKey"
                            v-html="comparatorObject.symbol + '&nbsp;' + comparatorObject.label" />
                </b-select>
            </b-field>
            <b-field :addons="false">
                <label
                        style="line-height: normal;"
                        class="label is-inline">
                    {{ $i18n.getHelperTitle('tainacan-filter-dates-intersection', 'second_comparator') }}<span>&nbsp;*&nbsp;</span>
                    <help-button
                            :title="$i18n.getHelperTitle('tainacan-filter-dates-intersection', 'second_comparator')"
                            :message="$i18n.getHelperMessage('tainacan-filter-dates-intersection', 'second_comparator')" />
                </label>
                <b-select
                        v-model="secondComparator"
                        @update:model-value="emitValues()">
                    <option 
                            v-for="(comparatorObject, comparatorKey) in comparatorsObject"
                            :key="comparatorKey"
                            :value="comparatorKey"
                            v-html="comparatorObject.symbol + '&nbsp;' + comparatorObject.label" />
                </b-select>
            </b-field>
        </div>
    </div>
</template>

<script>
    import { tainacanApi } from '../../../js/axios';

    export default {
        props: {
            filter: Object,
            modelValue: Object,
            errors: Object
        },
        emits: [
            'update:model-value',
        ],
        data() {
            return {
                metadata: [],
                loading: true,
                metadataType: '',
                metadataMessage: '',
                secondDateMetadatumId: [Number, String],
                secondDateMetadatumName: String,
                firstComparator: String,
                secondComparator: String,
                comparatorsObject: {}
            }
        },
        watch: {
            errors(){
                if ( this.errors && this.errors.secondary_filter_metadatum_id !== '' )
                    this.setErrorsAttributes( 'is-danger', this.errors.secondary_filter_metadatum_id );
                else
                    this.setErrorsAttributes( '', '' );
            }
        },
        created() {
            this.secondDateMetadatumId = this.modelValue && this.modelValue.secondary_filter_metadatum_id ? this.modelValue.secondary_filter_metadatum_id : '';
            this.secondDateMetadatumName = this.modelValue && this.modelValue.secondary_filter_metadatum_name ? this.modelValue.secondary_filter_metadatum_name : '';
            this.firstComparator = this.modelValue && this.modelValue.first_comparator ? this.modelValue.first_comparator : '>=';
            this.secondComparator = this.modelValue && this.modelValue.second_comparator ? this.modelValue.second_comparator : '<=';
            
            this.loading = true;
            this.fetchMetadata();

            this.comparatorsObject = {
                '=': {
                    symbol: '&#61;',
                    label: this.$i18n.get('is_equal_to')
                },
                '!=': {
                    symbol: '&#8800;',
                    label: this.$i18n.get('is_not_equal_to')
                },
                '>': {
                    symbol: '&#62;',
                    label: this.$i18n.get('after')
                },
                '>=': {
                    symbol: '&#8805;',
                    label: this.$i18n.get('after_or_on_day')
                },
                '<': {
                    symbol: '&#60;',
                    label: this.$i18n.get('before')
                },
                '<=': {
                    symbol: '&#8804;',
                    label: this.$i18n.get('before_or_on_day')
                }
            };
        },
        methods: {
            async fetchMetadata() {
                
                let endpoint = this.filter.collection_id && this.filter.collection_id !== 'default' ? ( '/collection/' + this.filter.collection_id + '/metadata' ) : '/metadata';
                endpoint += '?metaquery[0][key]=metadata_type&metaquery[0][value]=Tainacan\\Metadata_Types\\Date&nopaging=1&exclude=' + this.filter.metadatum_id;

                return await tainacanApi.get(endpoint)
                    .then(res => {
                        this.loading = false;
                        this.metadata = res.data ? res.data : [];
                    })
                    .catch(error => {
                        this.loading = false;
                        this.$console.log(error);
                    });
            },
            onUpdateSecondDateMetadatumId() {
                const selectedMetadatum = this.metadata.find( aMetadatum => aMetadatum.id == this.secondDateMetadatumId );
                this.selectedMetadatumName = selectedMetadatum ? selectedMetadatum.name : '';
                this.selectedMetadatumId = selectedMetadatum ? selectedMetadatum.id : '';
                this.emitValues();
            },
            emitValues() {
                this.$emit('update:model-value', {
                    first_comparator: this.firstComparator,
                    second_comparator: this.secondComparator,
                    secondary_filter_metadatum_id: this.secondDateMetadatumId,
                    secondary_filter_metadatum_name: this.secondDateMetadatumName
                });
            },
            setErrorsAttributes( type, message ) {
                this.metadataType = type;
                this.metadataMessage = message;
            },
            clear(){
                this.metadataType = '';
                this.metadataMessage = '';
            },
        }
    }
</script>