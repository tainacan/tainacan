<template>
    <form
            id="fieldEditForm"
            class="tainacan-form"
            @submit.prevent="saveEdition(editForm)">

        <b-field
                :addons="false"
                :type="formErrors['name'] != undefined ? 'is-danger' : ''"
                :message="formErrors['name'] != undefined ? formErrors['name'] : ''">
            <label class="label is-inline">
                {{ $i18n.get('label_name') }}
                <span
                        class="required-field-asterisk"
                        :class="formErrors['name'] != undefined ? 'is-danger' : ''">*</span>
                <help-button
                        :title="$i18n.getHelperTitle('fields', 'name')"
                        :message="$i18n.getHelperMessage('fields', 'name')"/>
            </label>
            <b-input
                    :class="{'has-content': editForm.name != undefined && editForm.name != ''}"
                    v-model="editForm.name"
                    name="name"
                    @focus="clearErrors('name')"/>
        </b-field>

        <b-field
                :addons="false"
                :type="formErrors['description'] != undefined ? 'is-danger' : ''"
                :message="formErrors['description'] != undefined ? formErrors['description'] : ''">
            <label class="label">
                {{ $i18n.get('label_description') }}
                <help-button
                        :title="$i18n.getHelperTitle('fields', 'description')"
                        :message="$i18n.getHelperMessage('fields', 'description')"/>
            </label>
            <b-input
                    :class="{'has-content': editForm.description != undefined && editForm.description != ''}"
                    type="textarea"
                    name="description"
                    v-model="editForm.description"
                    @focus="clearErrors('description')"/>
        </b-field>

        <b-field
                :addons="false"
                :type="formErrors['status'] != undefined ? 'is-danger' : ''"
                :message="formErrors['status'] != undefined ? formErrors['status'] : ''">
            <label class="label">
                {{ $i18n.get('label_status') }}
                <help-button
                        :title="$i18n.getHelperTitle('fields', 'status')"
                        :message="$i18n.getHelperMessage('fields', 'status')"/>
            </label>
            <div class="inline-block">
                <b-radio
                        size="is-small"
                        @focus="clearErrors('label_status')"
                        id="tainacan-select-status-publish"
                        name="status"
                        v-model="editForm.status"
                        native-value="publish">
                    {{ $i18n.get('publish_visibility') }}
                </b-radio>
                <br>
                <b-radio
                        size="is-small"
                        @focus="clearErrors('label_status')"
                        id="tainacan-select-status-private"
                        name="status"
                        v-model="editForm.status"
                        native-value="private">
                    {{ $i18n.get('private_visibility') }}
                </b-radio>
            </div>
        </b-field>

        <!-- Display on listing -->
        <b-field
                :addons="false">
            <label class="label">
                {{ $i18n.get('label_display') }}
                <help-button
                        :title="$i18n.getHelperTitle('fields', 'display')"
                        :message="$i18n.getHelperMessage('fields', 'display')"/>

            </label>

            <b-field
                    :type="formErrors['display'] != undefined ? 'is-danger' : ''"
                    :message="formErrors['display'] != undefined ? formErrors['display'] : ''">
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

            <b-field
                    :type="formErrors['display'] != undefined ? 'is-danger' : ''"
                    :message="formErrors['display'] != undefined ? formErrors['display'] : ''">
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

        <b-field
                :addons="false"
                :label="$i18n.get('label_options')">
            <b-field
                    :type="formErrors['required'] != undefined ? 'is-danger' : ''"
                    :message="formErrors['required'] != undefined ? formErrors['required'] : ''">
                <b-checkbox
                        size="is-small"
                        @input="clearErrors('required')"
                        v-model="editForm.required"
                        true-value="yes"
                        false-value="no"
                        name="required">
                    {{ $i18n.get('label_required') }}
                    <help-button
                            :title="$i18n.getHelperTitle('fields', 'required')"
                            :message="$i18n.getHelperMessage('fields', 'required')"/>
                </b-checkbox>
            </b-field>

            <b-field
                    :type="formErrors['multiple'] != undefined ? 'is-danger' : ''"
                    :message="formErrors['multiple'] != undefined ? formErrors['multiple'] : ''">
                <b-checkbox
                        size="is-small"
                        @input="clearErrors('multiple')"
                        v-model="editForm.multiple"
                        true-value="yes"
                        false-value="no"
                        name="multiple">
                    {{ $i18n.get('label_allow_multiple') }}
                    <help-button
                            :title="$i18n.getHelperTitle('fields', 'multiple')"
                            :message="$i18n.getHelperMessage('fields', 'multiple')"/>
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
                        name="collecion_key">
                    {{ $i18n.get('label_unique_value') }}
                    <help-button
                            :title="$i18n.getHelperTitle('fields', 'unique')"
                            :message="$i18n.getHelperMessage('fields', 'unique')"/>
                </b-checkbox>
            </b-field>
        </b-field>

        <component
                :errors="formErrors['field_type_options']"
                v-if="(editForm.field_type_object && editForm.field_type_object.form_component) || editForm.edit_form == ''"
                :is="editForm.field_type_object.form_component"
                :field="editForm"
                v-model="editForm.field_type_options"/>
        <div
                v-html="editForm.edit_form"
                v-else/>

        <div class="field is-grouped form-submit">
            <div class="control">
                <button
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

    export default {
        name: 'FieldEditionForm',
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
            editedField: Object,
            originalField: Object,
            isRepositoryLevel: false,
            collectionId: ''
        },
        created() {

            this.editForm = this.editedField;
            this.formErrors = this.editForm.formErrors != undefined ? this.editForm.formErrors : {};
            this.formErrorMessage = this.editForm.formErrors != undefined ? this.editForm.formErrorMessage : '';

            this.oldForm = JSON.parse(JSON.stringify(this.originalField));

        },
        beforeDestroy() {
            if (this.closedByForm) {
                this.editedField.saved = true;
            } else {
                this.oldForm.saved = this.editForm.saved;
                if (JSON.stringify(this.editForm) != JSON.stringify(this.oldForm))
                    this.editedField.saved = false;
                else
                    this.editedField.saved = true;
            }
        },
        methods: {
            ...mapActions('fields', [
                'updateField'
            ]),
            saveEdition(field) {

                if ((field.field_type_object && field.field_type_object.form_component) || field.edit_form == '') {

                    this.updateField({
                        collectionId: this.collectionId,
                        fieldId: field.id,
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
                    let formElement = document.getElementById('fieldEditForm');
                    let formData = new FormData(formElement);
                    let formObj = {};

                    for (let [key, value] of formData.entries())
                        formObj[key] = value;

                    this.updateField({
                        collectionId: this.collectionId,
                        fieldId: field.id,
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

    form {
        padding: 1.0em 2.0em;
        border-top: 1px solid $draggable-border-color;
        border-bottom: 1px solid $draggable-border-color;
        margin-top: 1.0em;
    }

</style>


