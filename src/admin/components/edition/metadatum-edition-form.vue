<template>
    <form
            id="metadatumEditForm"
            class="tainacan-form"
            :class="{ 'inCollapse': !isOnModal }"
            @submit.prevent="saveEdition(editForm)">

        <!-- Hook for extra Form options -->
        <template 
                v-if="formHooks != undefined && 
                    formHooks['form-metadatum'] != undefined &&
                    formHooks['form-metadatum']['begin'] != undefined">  
            <form 
                id="form-metadatum-begin"
                v-html="formHooks['form-metadatum']['begin']"/>
        </template>

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
                    v-model="editForm.description"
                    @focus="clearErrors('description')"/>
        </b-field>
            
        <b-field
                :addons="false">
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

        <b-field
                :addons="false"
                :type="formErrors['status'] != undefined ? 'is-danger' : ''"
                :message="formErrors['status'] != undefined ? formErrors['status'] : ''">
            <label class="label is-inline-block">
                {{ $i18n.get('label_status') }}
                <help-button
                        :title="$i18n.getHelperTitle('metadata', 'status')"
                        :message="$i18n.getHelperMessage('metadata', 'status')"/>
            </label>
            <b-field>
                <b-radio
                        size="is-small"
                        @focus="clearErrors('label_status')"
                        id="tainacan-select-status-publish"
                        name="status"
                        v-model="editForm.status"
                        native-value="publish">
                    {{ $i18n.get('publish_visibility') }}
                </b-radio>
            </b-field>
            <b-field>
                <b-radio
                        size="is-small"
                        @focus="clearErrors('label_status')"
                        id="tainacan-select-status-private"
                        name="status"
                        v-model="editForm.status"
                        native-value="private">
                    {{ $i18n.get('private_visibility') }}
                </b-radio>
            </b-field>
        </b-field>

        <!-- Display on listing -->
        <b-field
                :type="formErrors['display'] != undefined ? 'is-danger' : ''"
                :message="formErrors['display'] != undefined ? formErrors['display'] : ''" 
                :addons="false">
            <label class="label is-inline-block">
                {{ $i18n.get('label_display') }}
                <help-button
                        :title="$i18n.getHelperTitle('metadata', 'display')"
                        :message="$i18n.getHelperMessage('metadata', 'display')"/>

            </label>

            <b-field>
                <b-radio
                        size="is-small"
                        @input="clearErrors('display')"
                        v-model="editForm.display"
                        native-value="yes"
                        name="display">
                    {{ $i18n.get('label_display_default') }}
                </b-radio>
            </b-field>

            <b-field>
                <b-radio
                        size="is-small"
                        @input="clearErrors('display')"
                        v-model="editForm.display"
                        native-value="no"
                        name="display">
                    {{ $i18n.get('label_not_display') }}
                </b-radio>
            </b-field>

            <b-field>
                <b-radio
                        size="is-small"
                        v-model="editForm.display"
                        @input="clearErrors('display')"
                        native-value="never"
                        name="display">
                    {{ $i18n.get('label_display_never') }}
                </b-radio>
            </b-field>

        </b-field>

        <b-field :addons="false">
            <label class="label is-inline-block">{{ $i18n.get('label_options') }}</label>
            <b-field
                    :type="formErrors['required'] != undefined ? 'is-danger' : ''"
                    :message="formErrors['required'] != undefined ? formErrors['required'] : ''">
                <b-checkbox
                        size="is-small"
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
                    v-if="!originalMetadatum.metadata_type_object.core"
                    :type="formErrors['multiple'] != undefined ? 'is-danger' : ''"
                    :message="formErrors['multiple'] != undefined ? formErrors['multiple'] : ''">
                <b-checkbox
                        size="is-small"
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

            <b-field
                    :type="formErrors['unique'] != undefined ? 'is-danger' : ''"
                    :message="formErrors['unique'] != undefined ? formErrors['unique'] : ''">
                <b-checkbox
                        size="is-small"
                        @input="clearErrors('unique')"
                        v-model="editForm.unique"
                        true-value="yes"
                        false-value="no"
                        class="is-inline-block"
                        name="collecion_key">
                    {{ $i18n.get('label_unique_value') }}
                    <help-button
                            :title="$i18n.getHelperTitle('metadata', 'unique')"
                            :message="$i18n.getHelperMessage('metadata', 'unique')"/>
                </b-checkbox>
            </b-field>
        </b-field>

        <component
                :errors="formErrors['metadata_type_options']"
                v-if="(editForm.metadata_type_object && editForm.metadata_type_object.form_component) || editForm.edit_form == ''"
                :is="editForm.metadata_type_object.form_component"
                :metadatum="editForm"
                v-model="editForm.metadata_type_options"/>
        <div
                v-html="editForm.edit_form"
                v-else/>

        <!-- Hook for extra Form options -->
        <template 
                v-if="formHooks != undefined && 
                    formHooks['form-metadatum'] != undefined &&
                    formHooks['form-metadatum']['end'] != undefined">  
            <form 
                id="form-metadatum-end"
                v-html="formHooks['form-metadatum']['end']"/>
        </template>

        <div class="field is-grouped form-submit">
            <div class="control">
                <button
                        type="button"
                        class="button is-outlined"
                        @click.prevent="cancelEdition()"
                        slot="trigger">{{ $i18n.get('cancel') }}
                </button>
            </div>
            <div class="control">
                <button
                        class="button is-success"
                        type="submit">{{ $i18n.get('save') }}
                </button>
            </div>
        </div>
        <p class="help is-danger">{{ formErrorMessage }}</p>
    </form>
</template>

<script>
    import {mapActions} from 'vuex';
    import { formHooks } from "../../js/mixins";

    export default {
        name: 'MetadatumEditionForm',
        mixins: [ formHooks ],
        data() {
            return {
                editForm: {},
                oldForm: {},
                formErrors: {},
                formErrorMessage: '',
                closedByForm: false
            }
        },
        props: {
            index: '',
            editedMetadatum: Object,
            originalMetadatum: Object,
            isRepositoryLevel: false,
            collectionId: '',
            isOnModal: false
        },
        created() {

            this.editForm = this.editedMetadatum;
            this.formErrors = this.editForm.formErrors != undefined ? this.editForm.formErrors : {};
            this.formErrorMessage = this.editForm.formErrors != undefined ? this.editForm.formErrorMessage : '';

            this.oldForm = JSON.parse(JSON.stringify(this.originalMetadatum));

        },
        beforeDestroy() {
            if (this.closedByForm) {
                this.editedMetadatum.saved = true;
            } else {
                this.oldForm.saved = this.editForm.saved;
                if (JSON.stringify(this.editForm) != JSON.stringify(this.oldForm))
                    this.editedMetadatum.saved = false;
                else
                    this.editedMetadatum.saved = true;
            }
        },
        methods: {
            ...mapActions('metadata', [
                'updateMetadatum'
            ]),
            saveEdition(metadatum) {

                if ((metadatum.metadata_type_object && metadatum.metadata_type_object.form_component) || metadatum.edit_form == '') {
                    
                    this.fillExtraFormData(this.editForm, 'metadatum');
                    this.updateMetadatum({
                        collectionId: this.collectionId,
                        metadatumId: metadatum.id,
                        isRepositoryLevel: this.isRepositoryLevel,
                        index: this.index,
                        options: this.editForm
                    })
                        .then(() => {
                            this.editForm = {};
                            this.formErrors = {};
                            this.formErrorMessage = '';
                            this.closedByForm = true;
                            this.$emit('onEditionFinished');
                        })
                        .catch((errors) => {
                            for (let error of errors.errors) {
                                for (let attribute of Object.keys(error))
                                    this.formErrors[attribute] = error[attribute];
                            }
                            this.formErrorMessage = errors.error_message;
                            this.$emit('onErrorFound');

                            this.editForm.formErrors = this.formErrors;
                            this.editForm.formErrorMessage = this.formErrorMessage;
                        });
                } else {
                    let formElement = document.getElementById('metadatumEditForm');
                    let formData = new FormData(formElement);
                    let formObj = {};

                    for (let [key, value] of formData.entries())
                        formObj[key] = value;

                    this.fillExtraFormData(formObj, 'metadatum');
                    this.updateMetadatum({
                        collectionId: this.collectionId,
                        metadatumId: metadatum.id,
                        isRepositoryLevel: this.isRepositoryLevel,
                        index: this.index,
                        options: formObj
                    })
                        .then(() => {
                            this.editForm = {};
                            this.formErrors = {};
                            this.formErrorMessage = '';
                            this.closedByForm = true;
                            this.$emit('onEditionFinished');
                        })
                        .catch((errors) => {
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

    @import "../../scss/_variables.scss";

    form.inCollapse {
        padding: 1.0em 2.0em;
        border-top: 1px solid $gray2;
        border-bottom: 1px solid $gray2;
        margin-top: 1.0em;
    }

</style>


