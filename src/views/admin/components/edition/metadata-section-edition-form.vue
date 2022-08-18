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
    import { mapActions } from 'vuex';
    import { formHooks } from "../../js/mixins";

    export default {
        name: 'MetadataSectionEditionForm',
        mixins: [ formHooks ],
        props: {
            index: '',
            originalMetadataSection: Object,
            collectionId: '',
            isInsideImporterFlow: false
        },
        data() {
            return {
                form: {},
                formErrors: {},
                formErrorMessage: '',
                closedByForm: false,
                entityName: 'metadataSection',
                isUpdating: false
            }
        },
        created() {
            this.form = JSON.parse(JSON.stringify(this.originalMetadataSection));

            if (this.form.status == 'auto-draft')
                this.form.status = 'publish';

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

                if ( (metadataSection.metadata_type_object && metadataSection.metadata_type_object.form_component) || metadataSection.edit_form == '') {

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
                } else {
                    let formElement = document.getElementById('metadataSectionEditForm');
                    let formData = new FormData(formElement);
                    let formObj = {};

                    for (let [key, value] of formData.entries()) {
                        if (key === 'description_bellow_name')
                            formObj[key] = value ? 'yes' : 'no';
                        else
                            formObj[key] = value;
                    }

                    this.fillExtraFormData(formObj);
                    this.isUpdating = true;
                    this.updateMetadataSection({
                        collectionId: this.collectionId,
                        metadataSectionId: metadataSection.id,
                        index: this.index,
                        options: formObj
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
                            this.$emit('onErrorFound');

                            this.form.formErrors = this.formErrors;
                            this.form.formErrorMessage = this.formErrorMessage;
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


