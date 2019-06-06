<template>
    <form 
            id="filterEditForm" 
            class="tainacan-form" 
            @submit.prevent="saveEdition(editForm)">
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
                            :title="$i18n.getHelperTitle('filters', 'name')" 
                            :message="$i18n.getHelperMessage('filters', 'name')"/>
                </label>
                <b-input
                        v-model="editForm.name" 
                        name="name" 
                        @focus="clearErrors('name')"/>
            </b-field>

            <!-- Hook for extra Form options -->
            <template 
                    v-if="formHooks != undefined && 
                        formHooks['filter'] != undefined &&
                        formHooks['filter']['begin-left'] != undefined">  
                <form 
                    id="form-filter-begin-left"
                    class="form-hook-region"
                    v-html="formHooks['filter']['begin-left'].join('')"/>
            </template>

            <b-field
                    :addons="false" 
                    :type="formErrors['description'] != undefined ? 'is-danger' : ''" 
                    :message="formErrors['description'] != undefined ? formErrors['description'] : ''">
                <label class="label is-inline-block">
                    {{ $i18n.get('label_description') }} 
                    <help-button 
                            :title="$i18n.getHelperTitle('filters', 'description')" 
                            :message="$i18n.getHelperMessage('filters', 'description')"/>    
                </label>
                <b-input
                        type="textarea" 
                        name="description" 
                        v-model="editForm.description" 
                        @focus="clearErrors('description')" />
            </b-field>

            <b-field 
                    :addons="false"
                    :type="formErrors['status'] != undefined ? 'is-danger' : ''" 
                    :message="formErrors['status'] != undefined ? formErrors['status'] : ''">
                <label class="label is-inline-block">
                    {{ $i18n.get('label_status') }} 
                    <help-button 
                            :title="$i18n.getHelperTitle('filters', 'status')" 
                            :message="$i18n.getHelperMessage('filters', 'status')"/>
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

            <b-field
                    :addons="false"
                    v-if="editForm.filter_type_object && editForm.filter_type_object.use_max_options">
                <label class="label is-inline-block">
                    {{ $i18n.get('label_max_options_to_show') }}
                    <help-button
                            :title="$i18n.getHelperTitle('filters', 'max_options')"
                            :message="$i18n.getHelperMessage('filters', 'max_options')"/>
                </label>

                <div
                        v-if="!showEditMaxOptions"
                        class="is-flex">
                    <b-select
                            name="max_options"
                            v-model="editForm.max_options"
                            :placeholder="$i18n.get('instruction_select_max_options_to_show')">
                        <option value="4">4</option>
                        <option value="8">8</option>
                        <option value="12">12</option>
                        <option
                                v-if="editForm.max_options && ![4,8,12].find( (element) => element == editForm.max_options )"
                                :value="editForm.max_options">
                            {{ editForm.max_options }}</option>
                    </b-select>
                    <button
                            class="button is-white is-pulled-right"
                            :aria-label="$i18n.getFrom('items','edit_item')"
                            @click.prevent="showEditMaxOptions = true">
                        <span 
                                v-tooltip="{
                                    content: $i18n.get('edit'),
                                    autoHide: true,
                                    placement: 'bottom'
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-edit has-text-secondary"/>
                        </span>
                    </button>
                </div>
                <div
                        v-if="showEditMaxOptions"
                        class="is-flex">
                    <b-input
                            name="max_options"
                            v-model="editForm.max_options"
                            type="number"
                            step="1" />
                    <button
                            @click.prevent="showEditMaxOptions = false"
                            class="button is-white is-pulled-right">
                        <span 
                                v-tooltip="{
                                    content: $i18n.get('close'),
                                    autoHide: true,
                                    placement: 'bottom'
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-close has-text-secondary"/>
                        </span>
                    </button>
                </div>
            </b-field>

            <component
                    :errors="formErrors['filter_type_options']"
                    v-if="(editForm.filter_type_object && editForm.filter_type_object.form_component) || editForm.edit_form == ''"
                    :is="editForm.filter_type_object.form_component"
                    :filter="editForm"
                    v-model="editForm.filter_type_options"/>
            <div 
                    v-html="editForm.edit_form" 
                    v-else/>
        
            <!-- Hook for extra Form options -->
            <template 
                    v-if="formHooks != undefined && 
                        formHooks['filter'] != undefined &&
                        formHooks['filter']['end-left'] != undefined">  
                <form 
                    id="form-filter-end-left"
                    class="form-hook-region"
                    v-html="formHooks['filter']['end-left'].join('')"/>
            </template>
        </div>
        
        <div class="field is-grouped form-submit">
            <div class="control">
                <button 
                        type="button"
                        class="button is-outlined" 
                        @click.prevent="cancelEdition()" 
                        slot="trigger">{{ $i18n.get('cancel') }}</button>
            </div>
            <div class="control">
                <button 
                        class="button is-success" 
                        type="submit">{{ $i18n.get('save') }}</button>
            </div>
        </div>
        <p class="help is-danger">{{ formErrorMessage }}</p>
    </form>
</template>

<script>
import { mapActions } from 'vuex';
import { formHooks } from "../../js/mixins";

export default {
    name: 'FilterEditionForm',
    mixins: [ formHooks ],
    data(){
        return {
            editForm: {},
            oldForm: {},
            formErrors: {},
            formErrorMessage: '',
            closedByForm: false,
            showEditMaxOptions: false,
            entityName: 'filter'
        }
    }, 
    props: {
        index: '',
        editedFilter: Object,
        originalFilter: Object,
    },
    created() {

        this.editForm = this.editedFilter;
        this.formErrors = this.editForm.formErrors != undefined ? this.editForm.formErrors : {};
        this.formErrorMessage = this.editForm.formErrors != undefined ? this.editForm.formErrorMessage : ''; 

        this.oldForm = JSON.parse(JSON.stringify(this.originalFilter));
    },
    mounted() {
        // Fills hook forms with it's real values 
        this.$nextTick()
            .then(() => {
                this.updateExtraFormData(this.editForm);
            });
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

            if ((filter.filter_type_object && filter.filter_type_object.form_component) || filter.edit_form == '') {

                // this.fillExtraFormData(this.editForm);
                this.updateFilter({ filterId: filter.id, index: this.index, options: this.editForm})
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
                let formElement = document.getElementById('filterEditForm');
                let formData = new FormData(formElement); 
                let formObj = {};

                for (let [key, value] of formData.entries()) {
                    formObj[key] = value;
                }

                this.fillExtraFormData(formObj);
                this.updateFilter({ filterId: filter.id, index: this.index, options: formObj})
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

    form#filterEditForm {
        padding: 1.5rem $page-side-padding 0.5rem $page-side-padding;
        border-top: 1px solid $gray2;
        border-bottom: 1px solid $gray2;
        margin-top: 1.0em;

        .options-columns {
            -moz-column-count: 2;
            -moz-column-gap: 0;
            -moz-column-rule: none;
            -webkit-column-count: 2;
            -webkit-column-gap: 0;
            -webkit-column-rule: none;
            column-count: 2;
            column-gap: 4rem;
            column-rule: none;
            padding-bottom: 1.5rem;

            &>.field, &>section {
                -webkit-column-break-inside: avoid;
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    }

</style>


