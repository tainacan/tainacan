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
                secondDateMetadatumId: [Number, String]
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
            this.loading = true;
            this.fetchMetadata();
            console.log(this.filter)
        },
        methods: {
            async fetchMetadata() {
                let endpoint = this.filter.collectionId && this.filter.collectionId !== 'default' ? ( '/collections/' + this.filter.collectionId + '/metadata' ) : '/metadata';
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
                const selectedMetadatumName = selectedMetadatum ? selectedMetadatum.name : '';
                this.$emit('update:model-value', { secondary_filter_metadatum_id: this.secondDateMetadatumId, secondary_filter_metadatum_name: selectedMetadatumName});
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