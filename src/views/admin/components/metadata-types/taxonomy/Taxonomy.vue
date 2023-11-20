<template>
    <div>
        <tainacan-taxonomy-tag-input
                v-if="getComponent == 'tainacan-taxonomy-tag-input'"
                :disabled="disabled || isFetchingTerms"
                :maxtags="maxtags != undefined ? maxtags : (itemMetadatum.metadatum.multiple == 'yes' || allowNew === true ? (maxMultipleValues !== undefined ? maxMultipleValues : null) : '1')"
                v-model="valueComponent"
                :allow-new="allowNewFromOptions"
                :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : $i18n.get('instruction_type_existing_term')"
                :taxonomy-id="taxonomyId"
                :item-metadatum="itemMetadatum"
                @showAddNewTerm="openTermCreationModal"
                :has-counter="false" />
        <checkbox-radio-metadata-input
                v-else
                :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
                :is-modal="false"
                :parent="0"
                :allow-new="allowNewFromOptions"
                @showAddNewTerm="openTermCreationModal"
                :taxonomy_id="taxonomyId"
                :selected="!valueComponent ? [] : valueComponent"
                :metadatum-id="itemMetadatum.metadatum.id"
                :taxonomy="taxonomy"
                :collection-id="itemMetadatum.metadatum.collection_id"
                :is-taxonomy="true"
                :max-multiple-values="maxMultipleValues"
                :metadatum="itemMetadatum.metadatum"
                :amount-selected="Array.isArray(valueComponent) ? valueComponent.length : (valueComponent ? '1' : '0')"
                :is-checkbox="getComponent == 'tainacan-taxonomy-checkbox'"
                @input="(selected) => valueComponent = selected"
                :is-mobile-screen="isMobileScreen"
                @mobileSpecialFocus="onMobileSpecialFocus"
            />
        <div
                v-if="displayCreateNewTerm && !isTermCreationPanelOpen && (maxMultipleValues !== undefined ? (maxMultipleValues > valueComponent.length) : true)"
                class="add-new-term">
            <a
                    @click="openTermCreationModal"
                    class="add-link"
                    :class="{ 'is-loading': isAddingNewTermVaue }">
                <span class="icon is-small">
                    <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
                </span>
                &nbsp;{{ $i18n.get('label_create_new_term') }}
            </a>
        </div>

        <template v-if="allowNew && itemMetadatum.item">
            <!-- Term creation modal, used on admin for a complete term creation -->
            <b-modal
                    v-model="isTermCreationModalOpen"
                    :width="768"
                    trap-focus
                    aria-role="dialog"
                    aria-modal
                    :can-cancel="['outside', 'escape']"
                    custom-class="tainacan-modal"
                    :close-button-aria-label="$i18n.get('close')">
                <term-edition-form
                        :metadatum-id="itemMetadatum.metadatum.id"
                        :item-id="itemMetadatum.item.id"
                        :is-hierarchical="isHierarchical"
                        :taxonomy-id="taxonomyId"
                        :original-form="{ id: 'new', name: newTermName ? newTermName : '' }"
                        :is-term-insertion-flow="true"
                        @onEditionFinished="($event) => addRecentlyCreatedTerm($event.term)"
                        @onEditionCanceled="() => $console.log('Editing canceled')"
                        @onErrorFound="($event) => $console.log('Form with errors: ' + $event)" />
            </b-modal>

            <!-- Term creation panel, used on item submission block for a simpler term creation -->
            <transition name="filter-item">
                <term-creation-panel
                        :metadatum-id="itemMetadatum.metadatum.id"
                        :item-id="itemMetadatum.item.id"
                        :is-hierarchical="isHierarchical"
                        v-if="isTermCreationPanelOpen"
                        :taxonomy-id="taxonomyId"
                        :original-form="{ id: 'new', name: newTermName ? newTermName : '' }"
                        @onEditionFinished="($event) => addTermToBeCreated($event)"
                        @onEditionCanceled="() => isTermCreationPanelOpen = false"
                        @onErrorFound="($event) => $console.log('Form with errors: ' + $event)" />
            </transition>
        </template>
    </div>
</template>

<script>
    import TainacanTaxonomyTagInput from './TaxonomyTaginput.vue';
    import CheckboxRadioMetadataInput from '../../other/checkbox-radio-metadata-input.vue';
    import { tainacan as axios } from '../../../js/axios.js';
    import { mapActions } from 'vuex';

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
            allowNew: false,
            isMobileScreen: false,
        },
        data(){
            return {
                valueComponent: null,
                taxonomyId: '',
                taxonomy: '',
                terms:[],
                isAddingNewTermVaue: false,
                isTermCreationModalOpen: false,
                isTermCreationPanelOpen: false,
                newTermName: '',
                allowNewFromOptions: false,
                isFetchingTerms: false
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
                return this.allowNewFromOptions;
            },
            isOnItemSubmissionForm() {
                return !this.itemMetadatum.item || !this.itemMetadatum.item.id;
            },
            maxMultipleValues() {
                return (
                    this.itemMetadatum &&
                    this.itemMetadatum.metadatum &&
                    this.itemMetadatum.metadatum.cardinality &&
                    !isNaN(this.itemMetadatum.metadatum.cardinality) &&
                    this.itemMetadatum.metadatum.cardinality > 1
                ) ? this.itemMetadatum.metadatum.cardinality : undefined;
            },
            isHierarchical() {
                return (
                    this.itemMetadatum.metadatum &&
                    this.itemMetadatum.metadatum.metadata_type_object && 
                    this.itemMetadatum.metadatum.metadata_type_object.options &&
                    this.itemMetadatum.metadatum.metadata_type_object.options.hierarchical
                ) ? this.itemMetadatum.metadatum.metadata_type_object.options.hierarchical !== 'no' : true;
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
            
            this.allowNewFromOptions = this.allowNew === false ? false : metadata_type_options.allow_new_terms == 'yes' && (!this.$userCaps || this.$userCaps.hasCapability('tnc_rep_edit_taxonomies'));

            this.getTermsId();
        },
        methods: {
            ...mapActions('taxonomy', [
                'fetchTerms'
            ]),
            getTermsId() {
                let values = [];


                if ( this.value && this.itemMetadatum.metadatum && this.getComponent != 'tainacan-taxonomy-tag-input' ) {
                    values = this.value.map(term => term.id).filter(term => term !== undefined);
                    this.valueComponent = (values.length > 0 && this.itemMetadatum.metadatum && this.itemMetadatum.metadatum.multiple === 'no') ? values[0] : values;

                } else if (this.value && this.itemMetadatum.metadatum && this.getComponent == 'tainacan-taxonomy-tag-input') {
                    
                    // This first scenario happens in the item edition form, as the item metadata returns the terms as objects
                    const valuesInArray = Array.isArray(this.value) ? this.value : [this.value];
                    if ( valuesInArray[0] && valuesInArray[0] && valuesInArray[0].id ) {
                        values = valuesInArray.map((term) => { return { label: term.name, value: term.id } });
                        this.valueComponent = values;

                    // If the term is not returned as object, we're in bulk edition modal or in the metadata section condition metadata input, where value is a an array of IDs
                    } else if ( valuesInArray.length > 0 ) {
                        this.isFetchingTerms = true;
                        this.fetchTerms({ 
                            taxonomyId: this.taxonomyId,
                            fetchOnly: { 
                                fetch_only: {
                                    0: 'name',
                                    1: 'id'
                                }
                            },
                            all: true,
                            include: valuesInArray
                        }).then((res) => {
                            values = res.terms.map((term) => { return { label: term.name, value: term.id } });
                            this.valueComponent = values;
                            this.isFetchingTerms = false;    
                        }).catch((error) => {
                            this.isFetchingTerms = false;
                            throw error;
                        });
                    } else {
                        this.valueComponent = [];
                    }
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
            addTermToBeCreated(term) {
                this.isTermCreationPanelOpen = false;

                if (this.itemMetadatum.metadatum.multiple === 'no')
                    this.valueComponent = term.parent ? (term.parent + '>>' + term.name) : term.name;
                else
                    this.valueComponent.push(term.parent ? (term.parent + '>>' + term.name) : term.name);
            },
            openTermCreationModal(newTerm) {
                this.newTermName = newTerm.name;

                if (this.isOnItemSubmissionForm)
                    this.isTermCreationPanelOpen = true;
                else
                    this.isTermCreationModalOpen = true;
            },
            onMobileSpecialFocus() {
                this.$emit('mobileSpecialFocus');
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
