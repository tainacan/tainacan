<template>
    <div>
        <taxonomy-tag-input
                v-if="getComponent == 'tainacan-taxonomy-tag-input'"
                :disabled="disabled"
                :is="getComponent"
                :maxtags="maxtags"
                v-model="valueComponent"
                :allow-select-to-create="allowSelectToCreate"
                :allow-new="allowNew"
                :taxonomy-id="taxonomyId"
                :item-metadatum="itemMetadatum"
                @showAddNewTerm="openTermCreationModal"
                :has-counter="false" />
        <checkbox-radio-metadata-input
                v-else
                :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
                :is-modal="false"
                :parent="0"
                :allow-new="allowNew"
                @showAddNewTerm="openTermCreationModal"
                :taxonomy_id="taxonomyId"
                :selected="!valueComponent ? [] : valueComponent"
                :metadatum-id="itemMetadatum.metadatum.id"
                :taxonomy="taxonomy"
                :collection-id="itemMetadatum.metadatum.collection_id"
                :is-taxonomy="true"
                :metadatum="itemMetadatum.metadatum"
                :amount-selected="Array.isArray(valueComponent) ? valueComponent.length : (valueComponent ? '1' : '0')"
                :is-checkbox="getComponent == 'tainacan-taxonomy-checkbox'"
                @input="(selected) => valueComponent = selected"
            />
            
        <div 
                v-if="displayCreateNewTerm"
                class="add-new-term">
            <a
                    @click="openTermCreationModal"
                    class="add-link"
                    :class="{ 'is-loading': isAddingNewTermVaue }">
                <span class="icon is-small">
                    <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
                </span>
                &nbsp;{{ $i18n.get('label_new_term') }}
            </a>
        </div>

        <b-modal 
                v-model="isTermCreationModalOpen"
                trap-focus
                aria-role="dialog"
                aria-modal
                :can-cancel="['outside', 'escape']"
                custom-class="tainacan-modal">
            <term-edition-form 
                    :taxonomy-id="taxonomyId"
                    :edit-form="{ id: 'new', name: newTermName ? newTermName : '' }"
                    :is-modal="true"
                    @onEditionFinished="($event) => addRecentlyCreatedTerm($event.term)"
                    @onEditionCanceled="() => $console.log('Edition canceled')"
                    @onErrorFound="($event) => $console.log('Form with errors: ' + $event)" />
        </b-modal>
    </div>
</template>

<script>
    import TainacanTaxonomyTagInput from './TaxonomyTaginput.vue';
    import CheckboxRadioMetadataInput from '../../other/checkbox-radio-metadata-input.vue';
    import { tainacan as axios } from '../../../js/axios.js';

    export default {
        components: {
            TainacanTaxonomyTagInput,
            CheckboxRadioMetadataInput
        },
        props: {
            itemMetadatum: Object,
            value: [ Number, String, Array, Object ],
            disabled: false,
            forcedComponentType: '',
            maxtags: '',
            allowSelectToCreate: false,
            isTermCreationModalOpen: false,
            newTermName: ''
        },
        data(){
            return {
                valueComponent: null,
                taxonomyId: '',
                taxonomy: '',
                terms:[],
                allowNew: false,
                isAddingNewTermVaue: false
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
            },
            displayCreateNewTerm() {
                return this.allowNew;
            }
        },
        watch: {
            valueComponent( val ) {
                this.$emit('input', val);
            }
        },
        created() {
            const metadata_type_options = this.itemMetadatum.metadatum.metadata_type_options;

            this.taxonomyId = metadata_type_options.taxonomy_id;
            this.taxonomy = metadata_type_options.taxonomy;
            
            if (this.itemMetadatum.item && this.itemMetadatum.item.id && metadata_type_options && metadata_type_options.allow_new_terms && this.itemMetadatum.item) 
                this.allowNew = metadata_type_options.allow_new_terms == 'yes';

            this.getTermsId();
        },
        methods: {
            getTermsId() {
                let values = [];
                if (this.value && this.itemMetadatum.metadatum && this.getComponent != 'tainacan-taxonomy-tag-input') {
                    values = this.value.map(term => term.id).filter(term => term !== undefined);
                    this.valueComponent = (values.length >= 0 && this.itemMetadatum.metadatum && this.itemMetadatum.metadatum.multiple === 'no') ? values[0] : values;
                } else if (this.value && this.itemMetadatum.metadatum && this.getComponent == 'tainacan-taxonomy-tag-input') {
                    values = this.value.map((term) => { return { label: term.name, value: term.id } });
                    this.valueComponent = values;
                }
            },
            addRecentlyCreatedTerm(term) {
                if (term && term.id) {

                    this.isAddingNewTermVaue = true;

                    let val = this.valueComponent;

                    if ((!Array.isArray(val) || val.length == 0) && this.itemMetadatum.metadatum.multiple === 'no') {
                        axios.patch(`/item/${this.itemMetadatum.item.id}/metadata/${this.itemMetadatum.metadatum.id}`, {
                            values: term.id,
                        }).then(() => {
                            this.isAddingNewTermVaue = false;
                            this.valueComponent = term.id;
                            this.$emit('update-taxonomy-inputs', { taxonomyId: this.taxonomyId, metadatumId: this.itemMetadatum.metadatum.id });
                        })
                    } else {
                        val = val ? val : [];
                        val.push( this.getComponent == ('tainacan-taxonomy-checkbox' || 'tainacan-taxonomy-radio') ? term.id : {'label': term.name, 'value': term.id} );
                        axios.patch(`/item/${this.itemMetadatum.item.id}/metadata/${this.itemMetadatum.metadatum.id}`, {
                            values: val,
                        }).then(() => {
                            this.isAddingNewTermVaue = false;
                            this.valueComponent = val;
                            this.$emit('update-taxonomy-inputs', { taxonomyId: this.taxonomyId, metadatumId: this.itemMetadatum.metadatum.id });
                        })
                    }
                }
            },
            openTermCreationModal(newTerm) {
                this.newTermName = newTerm.name;
                this.isTermCreationModalOpen = true;
            }
        }
    }
</script>

<style scoped>
    .add-new-term {
        margin: 3px 0;
        font-size: 0.75em;
    }
</style>
