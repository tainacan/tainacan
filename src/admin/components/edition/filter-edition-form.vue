<template>
    <form id="filterEditForm" class="tainacan-form" v-on:submit.prevent="saveEdition(editForm)">    
        
        <b-field 
            :addons="false"
            :type="formErrors['name'] != undefined ? 'is-danger' : ''" 
            :message="formErrors['name'] != undefined ? formErrors['name'] : ''">
            <label class="label">
                {{$i18n.get('label_name')}} 
                  <span class="required-field-asterisk" :class="formErrors['name'] != undefined ? 'is-danger' : ''">*</span> 
                  <a class="help-button"><b-icon size="is-small" icon="help-circle-outline"></b-icon></a>
            </label>
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

        <component
                :errors="formErrors['filter_type_options']"
                v-if="(editForm.filter_type_object && editForm.filter_type_object.form_component) || editForm.edit_form == ''"
                :is="editForm.filter_type_object.form_component"
                :filter="editForm"
                v-model="editForm.filter_type_options">
        </component>
        <div v-html="editForm.edit_form" v-else></div>

        <div class="field is-grouped form-submit">
            <div class="control">
                <button class="button is-outlined" @click.prevent="cancelEdition()" slot="trigger">{{ $i18n.get('cancel')}}</button>
            </div>
            <div class="control">
                <button class="button is-success" type="submit">{{ $i18n.get('save')}}</button>
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
            oldForm: {},
            formErrors: {},
            formErrorMessage: '',
            closedByForm: false
        }
    }, 
    props: {
        index: '',
        editedFilter: {},
        originalFilter: {},
    },
    created() {
        this.editForm = this.editedFilter;
        this.oldForm = JSON.parse(JSON.stringify(this.originalFilter));
    },
    beforeDestroy() {
        if (this.closedByForm) {
            this.editedFilter.saved = true;
        } else {
            this.oldForm.saved = this.editForm.saved;
            if (JSON.stringify(this.editForm) != JSON.stringify(this.oldForm)) 
                this.editedFilter.saved = false;
            else    
                this.editedFilter.saved = true;
        }
    },
    methods: {
        ...mapActions('filter', [
            'updateFilter'
        ]),
        saveEdition(filter) {

            this.formErrors = {};
            this.formErrorMessage = '';

            if ((filter.filter_type_object && filter.filter_type_object.form_component) || filter.edit_form == '') {
                
                this.updateFilter({ filterId: filter.id, index: this.index, options: this.editForm})
                    .then((filter) => {
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
                    });
            } else {
                let formElement = document.getElementById('filterEditForm');
                let formData = new FormData(formElement); 
                let formObj = {}

                for (let [key, value] of formData.entries())  
                    formObj[key] = value;
                
                this.updateFilter({ filterId: filter.id, index: this.index, options: formObj})
                    .then((filter) => {
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

        .separator {
            color: $draggable-border-color;
            height: 1px;
            width: 60%;
        }
    }

</style>


