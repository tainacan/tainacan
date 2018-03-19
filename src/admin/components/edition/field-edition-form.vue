<template>
    <form id="fieldEditForm" class="tainacan-form" v-on:submit.prevent="saveEdition(editForm)">    
        
        <b-field 
            :addons="false"
            :type="formErrors['name'] != undefined ? 'is-danger' : ''" 
            :message="formErrors['name'] != undefined ? formErrors['name'] : ''"> 
            <label class="label">{{$i18n.get('label_name')}} <span class="required-field-asterisk" :class="formErrors['name'] != undefined ? 'is-danger' : ''">*</span> <a class="help-button"><b-icon size="is-small" icon="help-circle-outline"></b-icon></a></label>
            <b-input v-model="editForm.name" name="name" @focus="clearErrors('name')"></b-input>
        </b-field>

        <b-field
            :addons="false" 
            :type="formErrors['description'] != undefined ? 'is-danger' : ''" 
            :message="formErrors['description'] != undefined ? formErrors['description'] : ''">
            <label class="label">{{$i18n.get('label_description')}} <a class="help-button"><b-icon size="is-small" icon="help-circle-outline"></b-icon></a></label>
            <b-input type="textarea" name="description" v-model="editForm.description" @focus="clearErrors('description')" ></b-input>
        </b-field>

        <b-field 
            :addons="false"
            :type="formErrors['status'] != undefined ? 'is-danger' : ''" 
            :message="formErrors['status'] != undefined ? formErrors['status'] : ''">
            <label class="label">{{$i18n.get('label_status')}} <a class="help-button"><b-icon size="is-small" icon="help-circle-outline"></b-icon></a></label>
            <div class="inline-block">
                <b-radio 
                    @focus="clearErrors('label_status')"
                    id="tainacan-select-status-publish"
                    name="status" 
                    v-model="editForm.status"
                    native-value="publish">
                    {{ $i18n.get('publish_visibility') }}
                </b-radio>
                <br>
                <b-radio 
                    @focus="clearErrors('label_status')"
                    id="tainacan-select-status-private"
                    name="status" 
                    v-model="editForm.status"
                    native-value="private">
                    {{ $i18n.get('private_visibility') }}
                </b-radio>
            </div>
        </b-field>
        <br>
        <b-field
            :type="formErrors['required'] != undefined ? 'is-danger' : ''" 
            :message="formErrors['required'] != undefined ? formErrors['required'] : ''">
            <b-checkbox
                @input="clearErrors('required')"
                v-model="editForm.required"
                true-value="yes" 
                false-value="no"
                name="required">
                {{ $i18n.get('label_required') }}
            </b-checkbox>
            <a class="help-button"><b-icon size="is-small" icon="help-circle-outline"></b-icon></a>
        </b-field>

        <b-field
            :type="formErrors['multiple'] != undefined ? 'is-danger' : ''" 
            :message="formErrors['multiple'] != undefined ? formErrors['multiple'] : ''">
            <b-checkbox 
                @input="clearErrors('multiple')"
                v-model="editForm.multiple"
                true-value="yes" 
                false-value="no"
                name="multiple">
                {{ $i18n.get('label_allow_multiple') }}
            </b-checkbox>
            <a class="help-button"><b-icon size="is-small" icon="help-circle-outline"></b-icon></a>
        </b-field>

        <b-field 
            :type="formErrors['unique'] != undefined ? 'is-danger' : ''" 
            :message="formErrors['unique'] != undefined ? formErrors['unique'] : ''">
            <b-checkbox 
                @input="clearErrors('unique')"
                v-model="editForm.unique"
                true-value="yes" 
                false-value="no"
                name="collecion_key">
                {{ $i18n.get('label_unique_value') }}
            </b-checkbox>
            <a class="help-button"><b-icon size="is-small" icon="help-circle-outline"></b-icon></a>
        </b-field>

        <div class="separator"></div>

        <component
                :errors="formErrors['field_type_options']"
                v-if="(editForm.field_type_object && field.field_type_object.form_component) || field.edit_form == ''"
                :is="editForm.field_type_object.form_component"
                :field="editForm"
                v-model="editForm.field_type_options">
        </component>
        <div v-html="editForm.edit_form" v-else></div>

        <div class="field is-grouped form-submit">  
            <div class="control">
                <button class="button is-outlined" @click.prevent="cancelEdition()" slot="trigger">Cancel</button>
            </div>
            <div class="control">
                <button class="button is-success" type="submit">Submit</button>
            </div>
        </div>
        <p class="help is-danger">{{formErrorMessage}}</p>
    </form>
</template>

<script>
import { mapActions } from 'vuex';

export default {
    name: 'FieldEditionForm',
    data(){
        return {
            editForm: {},
            formErrors: {},
            formErrorMessage: ''
        }
    }, 
    props: {
        field: {}, 
        isRepositoryLevel: false,
        collectionId: ''
    },
    created() {
        this.editForm = this.field;
    },
    methods: {
        ...mapActions('fields', [
            'updateField'
        ]),
        saveEdition(field) {

            this.formErrors = {};
            this.formErrorMessage = '';

            if ((field.field_type_object && field.field_type_object.form_component) || field.edit_form == '') {
                
                this.updateField({collectionId: this.collectionId, fieldId: field.id, isRepositoryLevel: this.isRepositoryLevel, options: this.editForm})
                    .then((field) => {
                        this.editForm = {};
                        this.formErrors = {};
                        this.formErrorMessage = '';
                        this.$emit('onEditionFinished');
                    })
                    .catch((errors) => {
                        for (let error of errors.errors) {     
                            for (let attribute of Object.keys(error))
                                this.formErrors[attribute] = error[attribute];
                        }
                        this.formErrorMessage = errors.error_message;
                        this.$emit('onErrorFound');
                    });
            } else {
                let formElement = document.getElementById('fieldEditForm');
                let formData = new FormData(formElement); 
                let formObj = {}

                for (let [key, value] of formData.entries())  
                    formObj[key] = value;
                
                this.updateField({collectionId: this.collectionId, fieldId: field.id, isRepositoryLevel: this.isRepositoryLevel, options: formObj})
                    .then((field) => {
                        this.editForm = {};
                        this.formErrors = {};
                        this.formErrorMessage = '';
                        this.$emit('onEditionFinished');
                    })
                    .catch((errors) => {
                        for (let error of errors.errors) {     
                            for (let attribute of Object.keys(error))
                                this.formErrors[attribute] = error[attribute];
                        }
                        this.formErrorMessage = errors.error_message;
                        this.$emit('onErrorFound');
                    });
            }           
        },
        clearErrors(attribute) {
            this.formErrors[attribute] = undefined;
        },
        cancelEdition() {
            this.editForm = {};
            this.formErrors = {};
            this.formErrors = {};
            this.formErrorMessage = '';
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

        .separator {
            color: $draggable-border-color;
            height: 1px;
            width: 60%;
        }
    }

</style>


