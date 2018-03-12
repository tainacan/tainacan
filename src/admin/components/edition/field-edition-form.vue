<template>
    <form id="fieldEditForm" class="tainacan-form" v-on:submit.prevent="saveEdition(editForm)">    
        
        <b-field 
            :label="$i18n.get('label_name')" 
            :type="formErrors['name'] != undefined ? 'is-danger' : ''" 
            :message="formErrors['name'] != undefined ? formErrors['name'] : ''">
            <b-input v-model="editForm.name" name="name" @focus="clearErrors('name')"></b-input>
        </b-field>

        <b-field
            :label="$i18n.get('label_description')" 
            :type="formErrors['description'] != undefined ? 'is-danger' : ''" 
            :message="formErrors['description'] != undefined ? formErrors['description'] : ''">
            <b-input type="textarea" name="description" v-model="editForm.description" @focus="clearErrors('description')" ></b-input>
        </b-field>

        <b-field
            :type="formErrors['required'] != undefined ? 'is-danger' : ''" 
            :message="formErrors['required'] != undefined ? formErrors['required'] : ''">
            <b-switch 
                @input="clearErrors('required')"
                v-model="editForm.required"
                true-value="yes" 
                false-value="no"
                name="required">
                {{ $i18n.get('label_required') }}
            </b-switch>
        </b-field>

        <b-field
            :type="formErrors['multiple'] != undefined ? 'is-danger' : ''" 
            :message="formErrors['multiple'] != undefined ? formErrors['multiple'] : ''">
            <b-switch 
                @input="clearErrors('multiple')"
                v-model="editForm.multiple"
                true-value="yes" 
                false-value="no"
                name="multiple">
                {{ $i18n.get('label_allow_multiple') }}
            </b-switch>
        </b-field>

        <b-field 
            :type="formErrors['unique'] != undefined ? 'is-danger' : ''" 
            :message="formErrors['unique'] != undefined ? formErrors['unique'] : ''">
            <b-switch 
                @input="clearErrors('unique')"
                v-model="editForm.unique"
                true-value="yes" 
                false-value="no"
                name="collecion_key">
                {{ $i18n.get('label_unique_value') }}
            </b-switch>
        </b-field>

        <b-field 
            :label="$i18n.get('label_status')"
            :type="formErrors['status'] != undefined ? 'is-danger' : ''" 
            :message="formErrors['status'] != undefined ? formErrors['status'] : ''">
            <b-select
                    @focus="clearErrors('label_status')"
                    id="tainacan-select-status"
                    name="status"
                    v-model="editForm.status"
                    :placeholder="$i18n.get('instruction_select_a_status')">
                <option value="publish" selected>{{ $i18n.get('publish')}}</option>
                <option value="private">{{ $i18n.get('private')}}</option>
            </b-select>
        </b-field>

        <component
                :errors="formErrors['field_type_options']"
                v-if="(editForm.field_type_object && field.field_type_object.form_component) || field.edit_form == ''"
                :is="editForm.field_type_object.form_component"
                :field="editForm"
                v-model="editForm.field_type_options">
        </component>
        <div v-html="editForm.edit_form" v-else></div>

        <div class="field is-grouped is-grouped-centered">
            <div class="control">
                <button class="button is-secondary" type="submit">Submit</button>
            </div>
            <div class="control">
                <button class="button is-text" @click.prevent="cancelEdition()" slot="trigger">Cancel</button>
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
            'fetchFields',
            'updateField',
        ]),
        saveEdition(field) {

            this.openedFieldId = field.id;
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
                        this.openedFieldId = '';
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

<style scoped>

    form {
        padding: 5px;
    }

</style>


