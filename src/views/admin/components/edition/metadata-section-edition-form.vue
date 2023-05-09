<template>
<form
        id="metadataSectionEditForm"
        @submit.prevent="saveEdition(form)"
        autofocus="true"
        tabindex="-1"
        role="dialog"
        aria-modal>
    <div 
            v-if="form && Object.keys(form).length"
            class="tainacan-modal-content">
        <div class="tainacan-modal-title">
            <h2 v-html="form.name ? ($i18n.get('instruction_configure_the_metadata_section') + ' <em>' + form.name + '</em>') : $i18n.get('instruction_configure_new_metadata_section')" />
            <hr>
        </div>
        <div class="tainacan-form">
            <div class="options-columns">
                <b-field
                        :addons="false"
                        :type="formErrors['name'] != undefined ? 'is-danger' : ''"
                        :message="formErrors['name'] != undefined ? formErrors['name'] : ''">
                    <label class="label is-inline">
                        {{ $i18n.get('label_name') }}
                        <span
                                class="required-metadata-section-asterisk"
                                :class="formErrors['name'] != undefined ? 'is-danger' : ''">*</span>
                        <help-button
                                :title="$i18n.getHelperTitle('metadata-sections', 'name')"
                                :message="$i18n.getHelperMessage('metadata-sections', 'name')"
                                :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                    </label>
                    <b-input
                            v-model="form.name"
                            name="name"
                            @focus="clearErrors('name')"/>
                </b-field>

                <!-- Hook for extra Form options -->
                <template 
                        v-if="hasBeginLeftForm">  
                    <form 
                        id="form-metadataSection-begin-left"
                        class="form-hook-region"
                        v-html="getBeginLeftForm"/>
                </template>

                <b-field
                        :addons="false"
                        :type="formErrors['description'] != undefined ? 'is-danger' : ''"
                        :message="formErrors['description'] != undefined ? formErrors['description'] : ''">
                    <label class="label is-inline">
                        {{ $i18n.get('label_description') }}
                        <help-button
                                :title="$i18n.getHelperTitle('metadata-sections', 'description')"
                                :message="$i18n.getHelperMessage('metadata-sections', 'description')"
                                :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                    </label>
                    <b-input
                            type="textarea"
                            name="description"
                            rows="3"
                            v-model="form.description"
                            @focus="clearErrors('description')"/>
                </b-field>

                <b-field 
                        :addons="false"
                        :label="$i18n.getHelperTitle('metadata-sections', 'description_bellow_name')"
                        :type="formErrors['description_bellow_name'] != undefined ? 'is-danger' : ''"
                        :message="formErrors['description_bellow_name'] != undefined ? formErrors['description_bellow_name'] : ''">
                        &nbsp;
                    <b-switch
                            size="is-small"
                            @input="clearErrors('description_bellow_name')"
                            v-model="form.description_bellow_name"
                            true-value="yes"
                            false-value="no"
                            name="description_bellow_name">
                    <help-button
                            :title="$i18n.getHelperTitle('metadata-sections', 'description_bellow_name')"
                            :message="$i18n.getHelperMessage('metadata-sections', 'description_bellow_name')"
                            :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                    </b-switch>
                </b-field>

                <b-field
                        v-if="form.id !== 'default_section'"
                        :addons="false"
                        :type="formErrors['status'] != undefined ? 'is-danger' : ''"
                        :message="formErrors['status'] != undefined ? formErrors['status'] : ''">
                    <label class="label is-inline">
                        {{ $i18n.get('label_status') }}
                        <help-button
                                :title="$i18n.getHelperTitle('metadata-sections', 'status')"
                                :message="$i18n.getHelperMessage('metadata-sections', 'status')"
                                :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                    </label>
                    <div class="is-flex is-justify-content-space-between">
                        <b-radio
                                @focus="clearErrors('label_status')"
                                id="tainacan-select-status-publish"
                                name="status"
                                v-model="form.status"
                                native-value="publish">
                            <span class="icon has-text-gray3">
                                <i class="tainacan-icon tainacan-icon-public"/>
                            </span>
                            {{ $i18n.get('status_public') }}
                        </b-radio>
                        <b-radio
                                @focus="clearErrors('label_status')"
                                id="tainacan-select-status-private"
                                name="status"
                                v-model="form.status"
                                native-value="private">
                            <span class="icon has-text-gray3">
                                <i class="tainacan-icon tainacan-icon-private"/>
                            </span>
                            {{ $i18n.get('status_private') }}
                        </b-radio>
                    </div>
                </b-field>

            </div>

            <div 
                    v-if="form.id !== 'default_section'"
                    @click="hideConditionalSectionSettings = !hideConditionalSectionSettings;"
                    class="metadata-form-section">
                <span class="icon">
                    <i 
                            class="tainacan-icon"
                            :class="!hideConditionalSectionSettings ? 'tainacan-icon-arrowdown' : 'tainacan-icon-arrowright'" />
                </span>
                <strong>{{ $i18n.get('label_advanced_metadata_options') }}</strong>
                <hr>

            </div>

            <transition 
                    v-if="form.id !== 'default_section'"
                    name="filter-item">
                <div 
                        v-show="!hideConditionalSectionSettings"
                        class="options-columns">
                    <b-field 
                            :addons="false"
                            :label="$i18n.getHelperTitle('metadata-sections', 'is_conditional_section')"
                            :type="formErrors['is_conditional_section'] != undefined ? 'is-danger' : ''"
                            :message="formErrors['is_conditional_section'] != undefined ? formErrors['is_conditional_section'] : ''">
                            &nbsp;
                        <b-switch
                                size="is-small"
                                @input="clearErrors('is_conditional_section')"
                                v-model="form.is_conditional_section"
                                true-value="yes"
                                false-value="no"
                                name="is_conditional_section">
                        <help-button
                                :title="$i18n.getHelperTitle('metadata-sections', 'is_conditional_section')"
                                :message="$i18n.getHelperMessage('metadata-sections', 'is_conditional_section')"
                                :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                        </b-switch>
                    </b-field>
                    <div v-if="isConditionalSection && !availableConditionalMetadata.length">
                        <p style="break-inside: avoid;">{{ $i18n.get('info_create_select_metadatum_for_conditional_section') }}</p>
                    </div>
                    <transition name="filter-item">
                        <b-field
                                v-if="isConditionalSection && availableConditionalMetadata.length"
                                :addons="false"
                                :type="formErrors['conditional_section_rules'] != undefined ? 'is-danger' : ''"
                                :message="formErrors['conditional_section_rules'] != undefined ? formErrors['conditional_section_rules'] : ''">
                            <label class="label is-inline">
                                {{ $i18n.getHelperTitle('metadata-sections', 'conditional_section_rules') }}
                                <help-button
                                        :title="$i18n.getHelperTitle('metadata-sections', 'conditional_section_rules')"
                                        :message="$i18n.getHelperMessage('metadata-sections', 'conditional_section_rules')"
                                        :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                            </label>
                            <b-select 
                                    v-model="selectedConditionalMetadatum"
                                    :placeholder="$i18n.get('label_select_metadatum')">
                                <option 
                                        v-for="conditionalMetadatum of availableConditionalMetadata"
                                        :key="conditionalMetadatum.id"
                                        :value="conditionalMetadatum.id">
                                    {{ conditionalMetadatum.name }}
                                </option>
                            </b-select>
                        </b-field>
                    </transition>
                    <transition name="filter-item">
                        <b-field
                                v-if="isConditionalSection && selectedConditionalMetadatum"
                                :addons="false"
                                :type="formErrors['conditional_section_rules'] != undefined ? 'is-danger' : ''"
                                :message="formErrors['conditional_section_rules'] != undefined ? formErrors['conditional_section_rules'] : ''">
                            <label class="label is-inline">
                                {{ availableConditionalMetadata.find((availableMetadatum) => availableMetadatum.id == selectedConditionalMetadatum).name }}
                            </label>
                            <div style="overflow-y: auto; overflow-x: hidden; max-height: 100px;">
                                <b-checkbox
                                        v-model="selectedConditionalValue"
                                        v-for="(conditionalValue, conditionalValueIndex) of availableConditionalMetadata.find((availableMetadatum) => availableMetadatum.id == selectedConditionalMetadatum).metadata_type_object.options.options.split('\n')"
                                        :key="conditionalValueIndex"
                                        :native-value="conditionalValue">
                                    {{ conditionalValue }}
                                </b-checkbox>
                            </div>
                        </b-field>
                    </transition>
                </div>
            </transition>
            
            <!-- Hook for extra Form options -->
            <template v-if="hasEndLeftForm" >  
                <form 
                    id="form-metadataSection-end-left"
                    class="form-hook-region"
                    v-html="getEndLeftForm"/>
            </template>
                
        </div>
    </div>
    <div class="field is-grouped form-submit">
        <div class="control">
            <button
                    type="button"
                    class="button is-outlined"
                    @click.prevent="cancelEdition()"
                    slot="trigger">{{ $i18n.get('cancel') }}
            </button>
        </div>
        <p class="help is-danger">{{ formErrorMessage }}</p>
        <div class="control">
            <b-button
                    :loading="isUpdating"
                    class="button is-success"
                    native-type="submit">
                {{ $i18n.get('save') }}
            </b-button>
        </div>
    </div>
</form>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    import { formHooks } from "../../js/mixins";

    export default {
        name: 'MetadataSectionEditionForm',
        mixins: [ formHooks ],
        props: {
            index: '',
            originalMetadataSection: Object,
            collectionId: '',
            isInsideImporterFlow: false,
        },
        data() {
            return {
                form: {},
                formErrors: {},
                formErrorMessage: '',
                closedByForm: false,
                entityName: 'metadataSection',
                isUpdating: false,
                selectedConditionalMetadatum: undefined,
                selectedConditionalValue: [],
                hideConditionalSectionSettings: false
            }
        },
        computed: {
            ...mapGetters('metadata', [
                'getMetadataSections'
            ]),
            availableConditionalMetadata() {
                if (this.getMetadataSections.length) {
                    const otherMetadataSections = this.getMetadataSections.filter(aMetadataSection => aMetadataSection.id != this.form.id);
                    const availableMetadata = [];
                    for (let aMetadataSection of otherMetadataSections)
                        availableMetadata.push.apply(availableMetadata, aMetadataSection.metadata_object_list);
                    return availableMetadata.filter(aMetadatum => aMetadatum.metadata_type === 'Tainacan\\Metadata_Types\\Selectbox');
                }
                return {};
            },
            isConditionalSection() {
                return this.form.is_conditional_section == 'yes';
            }
        },
        created() {
            this.form = JSON.parse(JSON.stringify(this.originalMetadataSection));

            if (this.form.status == 'auto-draft')
                this.form.status = 'publish';

            if ( this.form.is_conditional_section == 'yes' && Object.keys(this.form.conditional_section_rules).length ) {
                const conditionalMetadatum = Object.keys(this.form.conditional_section_rules)[0];
                this.selectedConditionalMetadatum = conditionalMetadatum;
                this.selectedConditionalValue = this.form.conditional_section_rules[conditionalMetadatum];
            }

            this.formErrors = this.form.formErrors != undefined ? this.form.formErrors : {};
            this.formErrorMessage = this.form.formErrors != undefined ? this.form.formErrorMessage : '';
        },
        mounted() {
            // Fills hook forms with it's real values 
            this.$nextTick()
                .then(() => {
                    this.updateExtraFormData(this.form);
                });
        },
        methods: {
            ...mapActions('metadata', [
                'updateMetadataSection'
            ]),
            saveEdition(metadataSection) {

                if ( this.form.is_conditional_section == 'yes' && this.selectedConditionalMetadatum && this.selectedConditionalValue ) {
                    this.form.conditional_section_rules = {}
                    this.form.conditional_section_rules[this.selectedConditionalMetadatum] = this.selectedConditionalValue;
                } else
                    this.form.conditional_section_rules = null;

                this.fillExtraFormData(this.form);
                this.isUpdating = true;
                this.updateMetadataSection({
                    collectionId: this.collectionId,
                    metadataSectionId: metadataSection.id,
                    index: this.index,
                    options: this.form
                })
                    .then(() => {
                        this.form = {};
                        this.formErrors = {};
                        this.formErrorMessage = '';
                        this.isUpdating = false;
                        this.closedByForm = true;

                        this.$emit('onEditionFinished');
                    })
                    .catch((errors) => {
                        this.isUpdating = false;
                        for (let error of errors.errors) {
                            for (let attribute of Object.keys(error))
                                this.formErrors[attribute] = error[attribute];
                        }
                        this.formErrorMessage = errors.error_message;

                        this.form.formErrors = this.formErrors;
                        this.form.formErrorMessage = this.formErrorMessage;
                    });
            },
            clearErrors(attribute) {
                this.formErrors[attribute] = undefined;
            },
            cancelEdition() {
                this.closedByForm = true;
                this.$emit('onEditionCanceled');
            },
        }
    }
</script>

<style lang="scss" scoped>

    form#metadataSectionEditForm {

        .options-columns {
            -moz-column-count: 2;
            -moz-column-gap: 0;
            -moz-column-rule: 1px solid var(--tainacan-gray1);
            -webkit-column-count: 2;
            -webkit-column-gap: 0;
            -webkit-column-rule: 1px solid var(--tainacan-gray1);
            column-count: 2;
            column-gap: 4em;
            column-rule: 1px solid var(--tainacan-gray1);
            padding-left: 0.25em;
            padding-right: 0.25em;
            padding-bottom: 0.5em;

            &>.field, &>section {
                -webkit-column-break-inside: avoid;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            .field > .field:not(:last-child) {
                margin-bottom: 0em;
            }
            /deep/ .field {
                -webkit-column-break-inside: avoid;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            section {
                display: grid;
            }
            .field:first-child {
                -webkit-column-span: all;
                column-span: all;
            }
            .tainacan-help-tooltip-trigger {
                font-size: 1.25em;
            }
        }
        .tainacan-form .field:not(:last-child) {
            margin-bottom: 1em;
        }
        .tainacan-form /deep/ .control-label {
            white-space: normal;
        }
        .metadata-form-section {
            margin: 1.5em 0 0.5em -1.5em;
            position: relative;
            cursor: pointer;

            .icon {
                background: var(--tainacan-background-color);
                z-index: 1;
                position: relative;
            }
            strong {
                background: var(--tainacan-background-color);
                color: var(--tainacan-gray4);
                font-size: 0.875em;
                z-index: 1;
                position: relative;
                padding-right: 12px;
            }
            hr {
                position: absolute;
                top: -0.75em;
                width: calc(100% - 42px);
                height: 1px;
                background-color: var(--tainacan-gray2);
                margin-left: 42px;
            }
        }

        @media screen and (max-width: 768px) {
            .options-columns {
                -moz-column-count: 1;
                -webkit-column-count: 1;
                column-count: 1;
            }
        }
    }
    .form-submit {
        background-color: var(--tainacan-gray1);
        position: sticky;
        bottom: 0;
        padding: 16px 4.166666667vw;
        display: flex;
        justify-content: space-between;
        z-index: 2;
        font-size: 1.125em;
    }

</style>


