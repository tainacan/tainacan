<template>
    <div>
        <component
                :disabled="disabled"
                :is="getComponent"
                :maxtags="maxtags"
                v-model="valueComponent"
                :allow-select-to-create="allowSelectToCreate"
                :allow-new="allowNew"
                :taxonomy-id="taxonomyId"
                :item-metadatum="itemMetadatum"/>
        <add-new-term
                v-if="allowNew"
                :component-type="getComponent"
                :taxonomy-id="taxonomyId"
                :item-metadatum="itemMetadatum"
                :value="valueComponent"
                @newTerm="reload"/>
    </div>
</template>
<script>
    import TainacanTaxonomyRadio from './TaxonomyRadio.vue'
    import TainacanTaxonomyCheckbox from './TaxonomyCheckbox.vue'
    import TainacanTaxonomyTagInput from './TaxonomyTaginput.vue'
    import AddNewTerm from  './AddNewTerm.vue'

    export default {
        components: {
            TainacanTaxonomyRadio,
            TainacanTaxonomyCheckbox,
            TainacanTaxonomyTagInput,
            AddNewTerm
        },
        props: {
            itemMetadatum: Object,
            value: [ Number, String, Array, Object ],
            disabled: false,
            forcedComponentType: '',
            maxtags: '',
            allowSelectToCreate: false,
        },
        data(){
            return {
                valueComponent: null,
                taxonomyId: '',
                taxonomy: '',
                terms:[],
                allowNew: false
            }
        },
        computed: {
            getComponent() {
                if (this.forcedComponentType)
                   return this.forcedComponentType;
                else if(this.itemMetadatum.metadatum &&
                        this.itemMetadatum.metadatum.metadata_type_options &&
                        this.itemMetadatum.metadatum.metadata_type_options.input_type
                        )
                    return this.itemMetadatum.metadatum.metadata_type_options.input_type;
                else
                    return '';
            }
        },
        watch: {
            valueComponent( val ){
                this.$emit('input', val);
            }
        },
        created() {
            const metadata_type_options = this.itemMetadatum.metadatum.metadata_type_options;

            this.taxonomyId = metadata_type_options.taxonomy_id;
            this.taxonomy = metadata_type_options.taxonomy;

            if (metadata_type_options && metadata_type_options.allow_new_terms && this.itemMetadatum.item) 
                this.allowNew = metadata_type_options.allow_new_terms == 'yes';

            this.getTermsId();
        },
        methods: {
            getTermsId() {
                let values = [];
                if (this.value && this.itemMetadatum.metadatum && this.getComponent != 'tainacan-taxonomy-tag-input') {
                    values = this.value.map(term => term.id) 
                    this.valueComponent = (values.length >= 0 && this.itemMetadatum.metadatum && this.itemMetadatum.metadatum.multiple === 'no') ? values[0] : values;
                } else if (this.value && this.itemMetadatum.metadatum && this.getComponent == 'tainacan-taxonomy-tag-input') {
                    values = this.value.map((term) => { return { label: term.name, value: term.id } });
                    this.valueComponent = values;
                }
            },
            reload($event) {
                if ($event.taxonomyId == this.taxonomyId && $event.metadatumId == this.itemMetadatum.metadatum.id) {
                    this.valueComponent = $event.values;
                    this.$emit('update-taxonomy-inputs', $event)
                }
            }
        }
    }
</script>
