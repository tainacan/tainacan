<template>
    <form 
            id="filterEditForm" 
            class="tainacan-form" 
            @submit.prevent="saveEdition(form)">
        <div class="options-columns">
            <b-field 
                    :addons="false"
                    :type="formErrors['name'] != undefined ? 'is-danger' : ''" 
                    :message="formErrors['name'] != undefined ? formErrors['name'] : ''">
                <label class="label is-inline">
                    {{ $i18n.get('label_name') }} 
                    <span 
                            class="required-metadatum-asterisk"
                            :class="formErrors['name'] != undefined ? 'is-danger' : ''">*</span> 
                    <help-button 
                            :title="$i18n.getHelperTitle('filters', 'name')" 
                            :message="$i18n.getHelperMessage('filters', 'name')"/>
                </label>
                <b-input
                        v-model="form.name" 
                        name="name" 
                        @focus="clearErrors('name')"/>
            </b-field>

            <!-- Hook for extra Form options -->
            <template v-if="hasBeginLeftForm">  
                <form 
                    id="form-filter-begin-left"
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
                            :title="$i18n.getHelperTitle('filters', 'description')" 
                            :message="$i18n.getHelperMessage('filters', 'description')"/>    
                </label>
                <b-input
                        type="textarea" 
                        name="description" 
                        :rows="3"
                        v-model="form.description" 
                        @focus="clearErrors('description')" />
            </b-field>

            <b-field 
                    :addons="false"
                    :type="formErrors['status'] != undefined ? 'is-danger' : ''" 
                    :message="formErrors['status'] != undefined ? formErrors['status'] : ''">
                <label class="label is-inline">
                    {{ $i18n.get('label_status') }} 
                    <help-button 
                            :title="$i18n.getHelperTitle('filters', 'status')" 
                            :message="$i18n.getHelperMessage('filters', 'status')"/>
                </label>
                <div class="inline-block">
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
                    <br>
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

            <b-field
                    :addons="false"
                    v-if="form.filter_type_object && form.filter_type_object.use_max_options">
                <label class="label is-inline">
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
                            v-model="form.max_options"
                            :placeholder="$i18n.get('instruction_select_max_options_to_show')">
                        <option value="4">4</option>
                        <option value="8">8</option>
                        <option value="12">12</option>
                        <option
                                v-if="form.max_options && ![4,8,12].find( (element) => element == form.max_options )"
                                :value="form.max_options">
                            {{ form.max_options }}</option>
                    </b-select>
                    <button
                            class="button is-white is-pulled-right"
                            :aria-label="$i18n.getFrom('items','edit_item')"
                            @click.prevent="showEditMaxOptions = true">
                        <span 
                                v-tooltip="{
                                    content: $i18n.get('edit'),
                                    autoHide: true,
                                    placement: 'bottom',
                                    popperClass: ['tainacan-tooltip', 'tooltip']
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
                            v-model="form.max_options"
                            type="number"
                            step="1" />
                    <button
                            @click.prevent="showEditMaxOptions = false"
                            class="button is-white is-pulled-right">
                        <span 
                                v-tooltip="{
                                    content: $i18n.get('close'),
                                    autoHide: true,
                                    placement: 'bottom',
                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-close has-text-secondary"/>
                        </span>
                    </button>
                </div>
            </b-field>

             <b-field 
                    :addons="false"
                    :label="$i18n.getHelperTitle('filters', 'begin_with_filter_collapsed')"
                    :type="formErrors['begin_with_filter_collapsed'] != undefined ? 'is-danger' : ''"
                    :message="formErrors['begin_with_filter_collapsed'] != undefined ? formErrors['begin_with_filter_collapsed'] : ''">
                    &nbsp;
                <b-switch
                        size="is-small"
                        @input="clearErrors('begin_with_filter_collapsed')"
                        v-model="form.begin_with_filter_collapsed"
                        :true-value="'yes'"
                        :false-value="'no'"
                        :native-value="form.begin_with_filter_collapsed == 'yes' ? 'yes' : 'no'"
                        name="begin_with_filter_collapsed">
                <help-button
                        :title="$i18n.getHelperTitle('filters', 'begin_with_filter_collapsed')"
                        :message="$i18n.getHelperMessage('filters', 'begin_with_filter_collapsed')"
                        :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                </b-switch>
            </b-field>

            <component
                    :errors="formErrors['filter_type_options']"
                    v-if="(form.filter_type_object && form.filter_type_object.form_component) || form.edit_form == ''"
                    :is="form.filter_type_object.form_component"
                    :filter="form"
                    v-model="form.filter_type_options"/>
            <div 
                    v-html="form.edit_form" 
                    v-else/>
        
            <!-- Hook for extra Form options -->
            <template v-if="hasEndLeftForm">  
                <form 
                    id="form-filter-end-left"
                    class="form-hook-region"
                    v-html="getEndLeftForm"/>
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
                <b-button 
                        :loading="isLoading"
                        class="button is-success" 
                        native-type="submit">
                    {{ $i18n.get('save') }}
                </b-button>
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
    props: {
        index: '',
        editedFilter: Object,
        originalFilter: Object,
    },
    data(){
        return {
            form: {},
            oldForm: {},
            formErrors: {},
            formErrorMessage: '',
            closedByForm: false,
            showEditMaxOptions: false,
            entityName: 'filter',
            isLoading: false
        }
    }, 

    created() {

        this.form = this.editedFilter;
        this.formErrors = this.form.formErrors != undefined ? this.form.formErrors : {};
        this.formErrorMessage = this.form.formErrors != undefined ? this.form.formErrorMessage : ''; 

        this.oldForm = JSON.parse(JSON.stringify(this.originalFilter));
    },
    mounted() {
        // Fills hook forms with it's real values 
        this.$nextTick()
            .then(() => {
                this.updateExtraFormData(this.form);
            });
    },
    beforeDestroy() {
        if (this.closedByForm) {
            this.$emit('onUpdateSavedState', true);
        } else {
            this.oldForm.saved = this.form.saved;
            if (JSON.stringify(this.form) != JSON.stringify(this.oldForm)) 
                this.$emit('onUpdateSavedState', false);
            else    
                this.$emit('onUpdateSavedState', true);
        }
    },
    methods: {
        ...mapActions('filter', [
            'updateFilter'
        ]),
        saveEdition(filter) {
            if ((filter.filter_type_object && filter.filter_type_object.form_component) || filter.edit_form == '') {
                
                this.isLoading = true;
                for (let [key, value] of Object.entries(this.form)) {
                    if (key === 'begin_with_filter_collapsed')
                        this.form[key] = (value == 'yes' || value == true) ? 'yes' : 'no';
                }
                if (this.form['begin_with_filter_collapsed'] === undefined)
                    this.form['begin_with_filter_collapsed'] = 'no';
                
                this.updateFilter({ filterId: filter.id, index: this.index, options: this.form })
                    .then(() => {
                        this.form = {};
                        this.formErrors = {};
                        this.formErrorMessage = '';
                        this.isLoading = false;
                        this.closedByForm = true;
                        this.$emit('onEditionFinished');
                    })
                    .catch((errors) => {
                        this.isLoading = false;
                        for (let error of errors.errors) {     
                            for (let attribute of Object.keys(error))
                                this.formErrors[attribute] = error[attribute];
                        }
                        this.formErrorMessage = errors.error_message;
                        this.$emit('onErrorFound');

                        this.form.formErrors = this.formErrors;
                        this.form.formErrorMessage = this.formErrorMessage;
                    });
            } else {
                let formElement = document.getElementById('filterEditForm');
                let formData = new FormData(formElement); 
                let formObj = {};
                
                for (let [key, value] of formData.entries()) {
                    if (key === 'begin_with_filter_collapsed')
                        formObj[key] = (value == 'yes' || value == true) ? 'yes' : 'no';
                    else
                        formObj[key] = value;
                }
                if (formObj['begin_with_filter_collapsed'] === undefined)
                    formObj['begin_with_filter_collapsed'] = 'no';
                    
                this.fillExtraFormData(formObj);
                this.isLoading = true;
                this.updateFilter({ filterId: filter.id, index: this.index, options: formObj })
                    .then(() => {
                        this.form = {};
                        this.formErrors = {};
                        this.formErrorMessage = '';
                        this.isLoading = false;
                        this.closedByForm = true;
                        this.$emit('onEditionFinished');
                    })
                    .catch((errors) => {
                        this.isLoading = false;
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

    form#filterEditForm {
        padding: 1.5em var(--tainacan-one-column) 0.5em var(--tainacan-one-column);
        border-top: 1px solid var(--tainacan-gray2);
        border-bottom: 1px solid var(--tainacan-gray2);
        margin-top: 1.0em;
        font-size: 1.1em;

        .options-columns {
            -moz-column-count: 2;
            -moz-column-gap: 0;
            -moz-column-rule: none;
            -webkit-column-count: 2;
            -webkit-column-gap: 0;
            -webkit-column-rule: none;
            column-count: 2;
            column-gap: 4em;
            column-rule: none;
            padding-bottom: 0.5em;

            &>.field, &>section {
                -webkit-column-break-inside: avoid;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            .field > .field:not(:last-child) {
                margin-bottom: 0em;
            }
            .tainacan-help-tooltip-trigger {
                font-size: 1.25em;
            }
        }

        .form-submit {
            margin-bottom: 0.75em;
        }
    }

</style>


