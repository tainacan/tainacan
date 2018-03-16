<template>
    <form id="filterEditForm" class="tainacan-form" v-on:submit.prevent="saveEdition(editForm)">    
        
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
                :errors="formErrors['filter_type_options']"
                v-if="(editForm.filter_type_object && filter.filter_type_object.form_component) || filter.edit_form == ''"
                :is="editForm.filter_type_object.form_component"
                :filter="editForm"
                v-model="editForm.filter_type_options">
        </component>
        <div v-html="editForm.edit_form" v-else></div>

        <div class="filter is-grouped is-grouped-centered">
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
    name: 'FilterEditionForm',
    data(){
        return {
            editForm: {},
            formErrors: {},
            formErrorMessage: ''
        }
    }, 
    props: {
        filter: {}
    },
    created() {
        this.editForm = this.filter;
    },
    methods: {
        ...mapActions('filter', [
            'updateFilter'
        ]),
        saveEdition(filter) {

            this.formErrors = {};
            this.formErrorMessage = '';

            if ((filter.filter_type_object && filter.filter_type_object.form_component) || filter.edit_form == '') {
                
                this.updateFilter({ filterId: filter.id, options: this.editForm})
                    .then((filter) => {
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
                let formElement = document.getElementById('filterEditForm');
                let formData = new FormData(formElement); 
                let formObj = {}

                for (let [key, value] of formData.entries())  
                    formObj[key] = value;
                
                this.updateFilter({ filterId: filter.id, options: formObj})
                    .then((filter) => {
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
        padding: 0.5em 1.5em;
        border-top: 1px solid $draggable-border-color;

        .label {
            font-weight: normal;
        }
    }

</style>


