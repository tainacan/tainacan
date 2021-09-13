<template>
<form
        id="metadatumEditForm"
        @submit.prevent="saveEdition(editForm)"
        autofocus="true"
        tabindex="-1"
        role="dialog"
        aria-modal>
    <div 
            v-if="editForm && Object.keys(editForm).length"
            class="tainacan-modal-content">
        <div class="tainacan-modal-title">
            <h2 v-html="editForm.name ? ($i18n.get('instruction_configure_the_metadatum') + ' <em>' + editForm.name + '</em>') : $i18n.get('instruction_configure_new_metadatum')" />
            <!-- <a 
                    class="back-link" 
                    @click="onEditionCanceled()">
                {{ $i18n.get('back') }}
            </a> -->
            <hr>
        </div>
        <div class="tainacan-form">
            <div class="options-columns">
                <b-field
                        :addons="false"
                        :type="formErrors['name'] != undefined ? 'is-danger' : ''"
                        :message="formErrors['name'] != undefined ? formErrors['name'] : ''">
                    <label class="label is-inline-block">
                        {{ $i18n.get('label_name') }}
                        <span
                                class="required-metadatum-asterisk"
                                :class="formErrors['name'] != undefined ? 'is-danger' : ''">*</span>
                        <help-button
                                :title="$i18n.getHelperTitle('metadata', 'name')"
                                :message="$i18n.getHelperMessage('metadata', 'name')"/>
                    </label>
                    <b-input
                            v-model="editForm.name"
                            name="name"
                            @focus="clearErrors('name')"/>
                </b-field>

                <!-- Hook for extra Form options -->
                <template 
                        v-if="formHooks != undefined && 
                            formHooks['metadatum'] != undefined &&
                            formHooks['metadatum']['begin-left'] != undefined">  
                    <form 
                        id="form-metadatum-begin-left"
                        class="form-hook-region"
                        v-html="formHooks['metadatum']['begin-left'].join('')"/>
                </template>

                <b-field
                        :addons="false"
                        :type="formErrors['description'] != undefined ? 'is-danger' : ''"
                        :message="formErrors['description'] != undefined ? formErrors['description'] : ''">
                    <label class="label is-inline-block">
                        {{ $i18n.get('label_description') }}
                        <help-button
                                :title="$i18n.getHelperTitle('metadata', 'description')"
                                :message="$i18n.getHelperMessage('metadata', 'description')"/>
                    </label>
                    <b-input
                            type="textarea"
                            name="description"
                            rows="3"
                            v-model="editForm.description"
                            @focus="clearErrors('description')"/>
                </b-field>

                <b-field
                        v-if="editForm.parent == 0"
                        :addons="false"
                        :type="formErrors['status'] != undefined ? 'is-danger' : ''"
                        :message="formErrors['status'] != undefined ? formErrors['status'] : ''">
                    <label class="label is-inline-block">
                        {{ $i18n.get('label_status') }}
                        <help-button
                                :title="$i18n.getHelperTitle('metadata', 'status')"
                                :message="$i18n.getHelperMessage('metadata', 'status')"/>
                    </label>
                    <div class="is-flex is-justify-content-space-between">
                        <b-radio
                                @focus="clearErrors('label_status')"
                                id="tainacan-select-status-publish"
                                name="status"
                                v-model="editForm.status"
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
                                v-model="editForm.status"
                                native-value="private">
                            <span class="icon has-text-gray3">
                                <i class="tainacan-icon tainacan-icon-private"/>
                            </span>
                            {{ $i18n.get('status_private') }}
                        </b-radio>
                    </div>
                </b-field>

                <!-- Display on listing -->
                <b-field
                        v-if="editForm.parent == 0"
                        :type="formErrors['display'] != undefined ? 'is-danger' : ''"
                        :message="formErrors['display'] != undefined ? formErrors['display'] : ''" 
                        :addons="false">
                    <label class="label is-inline-block">
                        {{ $i18n.get('label_display') }}
                        <help-button
                                :title="$i18n.getHelperTitle('metadata', 'display')"
                                :message="$i18n.getHelperMessage('metadata', 'display')"/>
                    </label>
                    <b-select 
                            expanded
                            v-model="editForm.display"
                            @input="clearErrors('display')">
                        <option value="yes">
                            {{ $i18n.get('label_display_default') }}
                        </option>
                        <option value="no">
                            {{ $i18n.get('label_not_display') }}
                        </option>
                        <option value="never">
                            {{ $i18n.get('label_display_never') }}
                        </option>
                    </b-select>
                </b-field>

                <b-field 
                        :addons="false">
                    <label class="label is-inline-block">{{ $i18n.get('label_insert_options') }}</label>
                    
                    <b-field
                            v-if="editForm.metadata_type_object.component != 'tainacan-compound' && (editForm.parent == 0 || (editForm.parent != 0 && !isParentMultiple))"
                            :type="formErrors['required'] != undefined ? 'is-danger' : ''"
                            :message="formErrors['required'] != undefined ? formErrors['required'] : ''">
                        <b-checkbox
                                @input="clearErrors('required')"
                                v-model="editForm.required"
                                true-value="yes"
                                false-value="no"
                                class="is-inline-block"
                                name="required">
                            {{ $i18n.get('label_required') }}
                            <help-button
                                    :title="$i18n.getHelperTitle('metadata', 'required')"
                                    :message="$i18n.getHelperMessage('metadata', 'required')"/>
                        </b-checkbox>
                    </b-field>

                    <b-field
                            v-if="editForm.metadata_type_object.component != 'tainacan-compound'"
                            :type="formErrors['collection_key'] != undefined ? 'is-danger' : ''"
                            :message="formErrors['collection_key'] != undefined ? formErrors['collection_key'] : ''">
                        <b-checkbox
                                @input="clearErrors('collection_key')"
                                v-model="editForm.collection_key"
                                true-value="yes"
                                false-value="no"
                                class="is-inline-block"
                                name="collecion_key">
                            {{ $i18n.get('label_unique_value') }}
                            <help-button
                                    :title="$i18n.getHelperTitle('metadata', 'collection_key')"
                                    :message="$i18n.getHelperMessage('metadata', 'collection_key')"/>
                        </b-checkbox>
                    </b-field>

                    <b-field
                            v-if="!originalMetadatum.metadata_type_object.core && editForm.parent == 0"
                            :type="formErrors['multiple'] != undefined ? 'is-danger' : ''"
                            :message="formErrors['multiple'] != undefined ? formErrors['multiple'] : ''">
                        <b-checkbox
                                @input="clearErrors('multiple')"
                                v-model="editForm.multiple"
                                true-value="yes"
                                false-value="no"
                                class="is-inline-block"
                                name="multiple">
                            {{ $i18n.get('label_allow_multiple') }}
                            <help-button
                                    :title="$i18n.getHelperTitle('metadata', 'multiple')"
                                    :message="$i18n.getHelperMessage('metadata', 'multiple')"/>
                        </b-checkbox>
                        
                    </b-field>
                </b-field>

                <b-field
                        v-if="!originalMetadatum.metadata_type_object.core && editForm.parent == 0" 
                        :addons="false"
                        :label="$i18n.get('label_limit_max_values')">
                        &nbsp;
                    <b-switch
                            size="is-small"
                            :disabled="editForm.multiple != 'yes'"
                            v-model="showCardinalityOptions" />
                </b-field>

                <b-field
                        v-if="!originalMetadatum.metadata_type_object.core && editForm.parent == 0"
                        :type="formErrors['cardinality'] != undefined ? 'is-danger' : ''"
                        :message="formErrors['cardinality'] != undefined ? formErrors['cardinality'] : ''"
                        :addons="false">
                    <label class="label is-inline-block">
                        {{ $i18n.getHelperTitle('metadata', 'cardinality') }}
                        <help-button
                                :title="$i18n.getHelperTitle('metadata', 'cardinality')"
                                :message="$i18n.getHelperMessage('metadata', 'cardinality')"/>
                    </label>
                    <b-numberinput
                            :disabled="!showCardinalityOptions && editForm.multiple != 'yes'"
                            name="cardinality"
                            step="1"
                            min="2"
                            v-model="editForm.cardinality"/>
                </b-field>

                <b-field v-if="!isRepositoryLevel && isInsideImporterFlow">
                    <b-checkbox
                            class="is-inline-block"
                            v-model="editForm.repository_level"
                            @input="clearErrors('repository_level')"
                            name="repository_level"
                            true-value="yes"
                            false-value="no">
                        {{ $i18n.get('label_repository_metadata') }}
                        <help-button
                                    :title="$i18n.getHelperTitle('metadata', 'repository_level')"
                                    :message="$i18n.getHelperMessage('metadata', 'repository_level')"/>
                    </b-checkbox>
                </b-field>
            </div>

            <div 
                    v-if="(editForm.metadata_type_object && editForm.metadata_type_object.form_component) || editForm.edit_form != ''"
                    class="metadata-form-section"
                    @click="hideMetadataTypeOptions = !hideMetadataTypeOptions;">
                <span class="icon">
                    <i 
                            class="tainacan-icon"
                            :class="!hideMetadataTypeOptions ? 'tainacan-icon-arrowdown' : 'tainacan-icon-arrowright'" />
                </span>
                <strong>{{ $i18n.getWithVariables('label_options_of_the_%s_metadata_type', [ editForm.metadata_type_object.name ]) }}</strong>
                <hr>
            </div>

            <transition name="filter-item">
                <div 
                        v-show="!hideMetadataTypeOptions"
                        class="options-columns">
                    <component
                            :errors="formErrors['metadata_type_options']"
                            v-if="(editForm.metadata_type_object && editForm.metadata_type_object.form_component) || editForm.edit_form != ''"
                            :is="editForm.metadata_type_object.form_component"
                            :metadatum="editForm"
                            v-model="editForm.metadata_type_options"/>
                    <div
                            v-html="editForm.edit_form"
                            v-else/>

                    <!-- Hook for extra Form options -->
                    <template 
                            v-if="formHooks != undefined && 
                                formHooks['metadatum'] != undefined &&
                                formHooks['metadatum']['end-left'] != undefined">  
                        <form 
                            id="form-metadatum-end-left"
                            class="form-hook-region"
                            v-html="formHooks['metadatum']['end-left'].join('')"/>
                    </template>
                </div>
            </transition>

             <div 
                    @click="showAdvancedOptions = !showAdvancedOptions;"
                    class="metadata-form-section">
                <span class="icon">
                    <i 
                            class="tainacan-icon"
                            :class="showAdvancedOptions ? 'tainacan-icon-arrowdown' : 'tainacan-icon-arrowright'" />
                </span>
                <strong>{{ $i18n.get('label_advanced_metadata_options') }}</strong>
                <hr>

            </div>
            
            <transition name="filter-item">
                <div 
                        v-if="showAdvancedOptions"
                        class="options-columns">
                    <b-field :addons="false">
                        <label class="label is-inline-block">
                            {{ $i18n.get('label_semantic_uri') }}
                            <help-button
                                    :title="$i18n.getHelperTitle('metadata', 'semantic_uri')"
                                    :message="$i18n.getHelperMessage('metadata', 'semantic_uri')"/>
                        </label>
                        <b-input
                                v-model="editForm.semantic_uri"
                                name="semantic_uri"
                                type="url"
                                @focus="clearErrors('semantic_uri')"/>
                    </b-field>
                </div>
            </transition>
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
                    :loading="isLoading"
                    class="button is-success"
                    native-type="submit">
                {{ $i18n.get('save') }}
            </b-button>
        </div>
    </div>
</form>
</template>

<script>
    import {mapActions} from 'vuex';
    import { formHooks } from "../../js/mixins";

    export default {
        name: 'MetadatumEditionForm',
        mixins: [ formHooks ],
        props: {
            index: '',
            originalMetadatum: Object,
            isRepositoryLevel: false,
            collectionId: '',
            isParentMultiple: false,
            isInsideImporterFlow: false
        },
        data() {
            return {
                editForm: {},
                formErrors: {},
                formErrorMessage: '',
                closedByForm: false,
                entityName: 'metadatum',
                isUpdating: false,
                hideMetadataTypeOptions: false,
                showAdvancedOptions: false,
                showCardinalityOptions: false
            }
        },
        watch: {
            showCardinalityOptions() {
                if (!this.showCardinalityOptions)
                    this.editForm.cardinality = 2;
                else
                    this.editForm.cardinality = 1;
            }
        },
        created() {
            this.editForm = JSON.parse(JSON.stringify(this.originalMetadatum));
     
            if (this.editForm.status == 'auto-draft')
                this.editForm.status = 'publish';

            if (this.editForm.cardinality && this.editForm.cardinality > 1)
                this.showCardinalityOptions = true;

            this.formErrors = this.editForm.formErrors != undefined ? this.editForm.formErrors : {};
            this.formErrorMessage = this.editForm.formErrors != undefined ? this.editForm.formErrorMessage : '';
        },
        mounted() {
            // Fills hook forms with it's real values 
            this.$nextTick()
                .then(() => {
                    this.updateExtraFormData(this.editForm);
                });
        },
        methods: {
            ...mapActions('metadata', [
                'updateMetadatum'
            ]),
            saveEdition(metadatum) {

                if ( (metadatum.metadata_type_object && metadatum.metadata_type_object.form_component) || metadatum.edit_form == '') {
                    let repository = this.editForm.repository_level;
                    if (repository && repository === 'yes')
                        this.isRepositoryLevel = true;

                    this.fillExtraFormData(this.editForm);
                    this.isUpdating = true;
                    this.updateMetadatum({
                        collectionId: this.collectionId,
                        metadatumId: metadatum.id,
                        isRepositoryLevel: this.isRepositoryLevel,
                        index: this.index,
                        options: this.editForm,
                        includeOptionsAsHtml: true
                    })
                        .then(() => {
                            this.editForm = {};
                            this.formErrors = {};
                            this.formErrorMessage = '';
                            this.isUpdating = false;
                            this.closedByForm = true;

                            this.$root.$emit('metadatumUpdated', this.isRepositoryLevel);
                            this.$emit('onEditionFinished');
                        })
                        .catch((errors) => {
                            this.isUpdating = false;
                            for (let error of errors.errors) {
                                for (let attribute of Object.keys(error))
                                    this.formErrors[attribute] = error[attribute];
                            }
                            this.formErrorMessage = errors.error_message;

                            this.editForm.formErrors = this.formErrors;
                            this.editForm.formErrorMessage = this.formErrorMessage;
                        });
                } else {
                    let formElement = document.getElementById('metadatumEditForm');
                    let formData = new FormData(formElement);
                    let formObj = {};

                    for (let [key, value] of formData.entries())
                        formObj[key] = value;

                    this.fillExtraFormData(formObj);
                    this.isUpdating = true;
                    this.updateMetadatum({
                        collectionId: this.collectionId,
                        metadatumId: metadatum.id,
                        isRepositoryLevel: this.isRepositoryLevel,
                        index: this.index,
                        options: formObj,
                        includeOptionsAsHtml: true
                    })
                        .then(() => {
                            this.editForm = {};
                            this.formErrors = {};
                            this.formErrorMessage = '';
                            this.isUpdating = false;
                            this.closedByForm = true;

                            this.$root.$emit('metadatumUpdated', this.isRepositoryLevel);
                            this.$emit('onEditionFinished');
                        })
                        .catch((errors) => {
                            this.isUpdating = false;

                            for (let error of errors.errors) {
                                for (let attribute of Object.keys(error))
                                    this.formErrors[attribute] = error[attribute];
                            }
                            this.formErrorMessage = errors.error_message;
                            this.$emit('onErrorFound');

                            this.editForm.formErrors = this.formErrors;
                            this.editForm.formErrorMessage = this.formErrorMessage;
                        });
                }
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

    form#metadatumEditForm {

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
            .help-wrapper {
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
            margin: 0.75em 0 0.5em 0;
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
        .metadata-form-section+.options-columns {
            padding-left: 1.75em;
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


