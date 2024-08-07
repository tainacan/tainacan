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
                            :message="$i18n.getHelperMessage('filters', 'name')" />
                </label>
                <b-input
                        v-model="form.name" 
                        name="name" 
                        @focus="clearErrors('name')" />
            </b-field>

            <!-- Hook for extra Form options -->
            <template v-if="hasBeginLeftForm">  
                <form 
                        id="form-filter-begin-left"
                        class="form-hook-region"
                        v-html="getBeginLeftForm" />
            </template>

            <b-field
                    :addons="false" 
                    :type="formErrors['description'] != undefined ? 'is-danger' : ''" 
                    :message="formErrors['description'] != undefined ? formErrors['description'] : ''">
                <label class="label is-inline">
                    {{ $i18n.get('label_description') }} 
                    <help-button 
                            :title="$i18n.getHelperTitle('filters', 'description')" 
                            :message="$i18n.getHelperMessage('filters', 'description')" />    
                </label>
                <b-input
                        v-model="form.description" 
                        type="textarea" 
                        name="description"
                        :rows="3" 
                        @focus="clearErrors('description')" />
            </b-field>

            <b-field 
                    :addons="false"
                    :label="$i18n.getHelperTitle('filters', 'description_bellow_name')"
                    :type="formErrors['description_bellow_name'] != undefined ? 'is-danger' : ''"
                    :message="formErrors['description_bellow_name'] != undefined ? formErrors['description_bellow_name'] : ''">
                &nbsp;
                <b-switch
                        v-model="form.description_bellow_name"
                        size="is-small"
                        true-value="yes"
                        false-value="no"
                        :native-value="form.description_bellow_name == 'yes' ? 'yes' : 'no'"
                        name="description_bellow_name"
                        @update:model-value="clearErrors('description_bellow_name')">
                    <help-button
                            :title="$i18n.getHelperTitle('filters', 'description_bellow_name')"
                            :message="$i18n.getHelperMessage('filters', 'description_bellow_name')"
                            :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                </b-switch>
            </b-field>

            <b-field
                    v-if="form.filter_type_object.use_input_placeholder"
                    :addons="false"
                    :type="formErrors['placeholder'] != undefined ? 'is-danger' : ''"
                    :message="formErrors['placeholder'] != undefined ? formErrors['placeholder'] : ''">
                <label class="label is-inline">
                    {{ $i18n.getHelperTitle('filters', 'placeholder') }}
                    <help-button
                            :title="$i18n.getHelperTitle('filters', 'placeholder')"
                            :message="$i18n.getHelperMessage('filters', 'placeholder')"
                            :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                </label>
                <b-input
                        v-model="form.placeholder"
                        name="placeholder"
                        @focus="clearErrors('placeholder')" />
            </b-field>

            <b-field 
                    :addons="false"
                    :type="formErrors['status'] != undefined ? 'is-danger' : ''" 
                    :message="formErrors['status'] != undefined ? formErrors['status'] : ''">
                <label class="label is-inline">
                    {{ $i18n.get('label_status') }} 
                    <help-button 
                            :title="$i18n.getHelperTitle('filters', 'status')" 
                            :message="$i18n.getHelperMessage('filters', 'status')" />
                </label>
                <div class="inline-block">
                    <b-radio 
                            id="tainacan-select-status-publish"
                            v-model="form.status"
                            name="status" 
                            native-value="publish"
                            @focus="clearErrors('label_status')">
                        <span class="icon has-text-gray3">
                            <i class="tainacan-icon tainacan-icon-public" />
                        </span>
                        {{ $i18n.get('status_public') }}
                    </b-radio>
                    <br>
                    <b-radio
                            id="tainacan-select-status-private"
                            v-model="form.status"
                            name="status" 
                            native-value="private"
                            @focus="clearErrors('label_status')">
                        <span class="icon has-text-gray3">
                            <i class="tainacan-icon tainacan-icon-private" />
                        </span>
                        {{ $i18n.get('status_private') }}
                    </b-radio>
                </div>
            </b-field>

            <b-field
                    v-if="form.filter_type_object && form.filter_type_object.use_max_options"
                    :addons="false">
                <label class="label is-inline">
                    {{ $i18n.get('label_max_options_to_show') }}
                    <help-button
                            :title="$i18n.getHelperTitle('filters', 'max_options')"
                            :message="$i18n.getHelperMessage('filters', 'max_options')" />
                </label>

                <div
                        v-if="!showEditMaxOptions"
                        class="is-flex">
                    <b-select
                            v-model="form.max_options"
                            name="max_options"
                            :placeholder="$i18n.get('instruction_select_max_options_to_show')">
                        <option value="4">
                            4
                        </option>
                        <option value="8">
                            8
                        </option>
                        <option value="12">
                            12
                        </option>
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
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-edit has-text-secondary" />
                        </span>
                    </button>
                </div>
                <div
                        v-if="showEditMaxOptions"
                        class="is-flex">
                    <b-input
                            v-model="form.max_options"
                            name="max_options"
                            type="number"
                            step="1"
                            :max="maxOptionsLimit" />
                    <button
                            class="button is-white is-pulled-right"
                            @click.prevent="showEditMaxOptions = false">
                        <span 
                                v-tooltip="{
                                    content: $i18n.get('close'),
                                    autoHide: true,
                                    placement: 'bottom',
                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-close has-text-secondary" />
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
                        v-model="form.begin_with_filter_collapsed"
                        size="is-small"
                        :true-value="'yes'"
                        :false-value="'no'"
                        :native-value="form.begin_with_filter_collapsed == 'yes' ? 'yes' : 'no'"
                        name="begin_with_filter_collapsed"
                        @update:model-value="clearErrors('begin_with_filter_collapsed')">
                    <help-button
                            :title="$i18n.getHelperTitle('filters', 'begin_with_filter_collapsed')"
                            :message="$i18n.getHelperMessage('filters', 'begin_with_filter_collapsed')"
                            :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                </b-switch>
            </b-field>

            <b-field 
                    v-if="form.collection_id && form.collection_id !== 'default'"
                    :addons="false"
                    :label="$i18n.getHelperTitle('filters', 'display_in_repository_level_lists')"
                    :type="formErrors['display_in_repository_level_lists'] != undefined ? 'is-danger' : ''"
                    :message="formErrors['display_in_repository_level_lists'] != undefined ? formErrors['display_in_repository_level_lists'] : ''">
                    &nbsp;
                <b-switch
                        v-model="form.display_in_repository_level_lists"
                        size="is-small"
                        :true-value="'yes'"
                        :false-value="'no'"
                        :native-value="form.display_in_repository_level_lists == 'yes' ? 'yes' : 'no'"
                        name="display_in_repository_level_lists"
                        @update:model-value="clearErrors('display_in_repository_level_lists')">
                    <help-button
                            :title="$i18n.getHelperTitle('filters', 'display_in_repository_level_lists')"
                            :message="$i18n.getHelperMessage('filters', 'display_in_repository_level_lists')"
                            :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                </b-switch>
            </b-field>

            <component
                    :is="form.filter_type_object.form_component"
                    v-if="(form.filter_type_object && form.filter_type_object.form_component) || form.edit_form == ''"
                    v-model="form.filter_type_options"
                    :errors="formErrors['filter_type_options']"
                    :filter="form" />
            <div 
                    v-else 
                    v-html="form.edit_form" />
        
            <!-- Hook for extra Form options -->
            <template v-if="hasEndLeftForm">  
                <form 
                        id="form-filter-end-left"
                        class="form-hook-region"
                        v-html="getEndLeftForm" />
            </template>
        </div>
        
        <div class="field is-grouped form-submit">
            <div class="control">
                <button 
                        type="button"
                        class="button is-outlined" 
                        @click.prevent="cancelEdition()">
                    {{ $i18n.get('cancel') }}
                </button>
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
        <p class="help is-danger">
            {{ formErrorMessage }}
        </p>
    </form>
</template>

<script>
import { nextTick } from 'vue';
import { mapActions } from 'vuex';
import { formHooks } from "../../js/mixins";

import FormFilterDate from '../filter-types/date/FormDate.vue';
import FormFilterNumeric from '../filter-types/numeric/FormNumeric.vue';
import FormFilterNumericInterval from '../filter-types/numeric-interval/FormNumericInterval.vue';
import FormFilterNumericListInterval from '../filter-types/numeric-list-interval/FormNumericListInterval.vue';
import FormFilterNumericsIntersection from '../filter-types/numerics-intersection/FormNumericsIntersection.vue';
import FormFilterDatesIntersection from '../filter-types/dates-intersection/FormDatesIntersection.vue';

export default {
    name: 'FilterEditionForm',
    components: {
        'tainacan-filter-form-date': FormFilterDate,
        'tainacan-filter-form-numeric': FormFilterNumeric,
        'tainacan-filter-form-numeric-interval': FormFilterNumericInterval,
        'tainacan-filter-form-numeric-list-interval': FormFilterNumericListInterval,
        'tainacan-filter-form-numerics-intersection': FormFilterNumericsIntersection,
        'tainacan-filter-form-dates-intersection': FormFilterDatesIntersection
    },
    mixins: [ formHooks ],
    props: {
        index: '',
        editedFilter: Object,
        originalFilter: Object,
        isRepositoryLevel: Boolean
    }, 
    emits: [
        'on-update-saved-state',
        'on-edition-finished',
        'on-edition-canceled',
        'on-error-found'
    ],
    data(){
        return {
            form: {},
            oldForm: {},
            formErrors: {},
            formErrorMessage: '',
            closedByForm: false,
            showEditMaxOptions: false,
            entityName: 'filter',
            isLoading: false,
            maxOptionsLimit: tainacan_plugin.api_max_items_per_page && !isNaN(tainacan_plugin.api_max_items_per_page) ? Number(tainacan_plugin.api_max_items_per_page) : 96
        }
    },
    created() {
        
        this.form = this.editedFilter;
        this.formErrors = this.form.formErrors != undefined ? this.form.formErrors : {};
        this.formErrorMessage = this.form.formErrors != undefined ? this.form.formErrorMessage : ''; 

        this.oldForm = JSON.parse(JSON.stringify(this.originalFilter));
        
        if ( this.form.metadatum == undefined && this.oldForm.metadatum != undefined )
            this.form.metadatum = this.oldForm.metadatum;
        
    },
    mounted() {
        // Fills hook forms with it's real values 
        nextTick()
            .then(() => {
                this.updateExtraFormData(this.form);
            });
    },
    beforeUnmount() {
        if (this.closedByForm) {
            this.$emit('on-update-saved-state', true);
        } else {
            this.oldForm.saved = this.form.saved;
            if (JSON.stringify(this.form) != JSON.stringify(this.oldForm)) 
                this.$emit('on-update-saved-state', false);
            else    
                this.$emit('on-update-saved-state', true);
        }
    },
    methods: {
        ...mapActions('filter', [
            'updateFilter'
        ]),
        saveEdition(filter) {

            this.isLoading = true;

            if ((filter.filter_type_object && filter.filter_type_object.form_component) || filter.edit_form == '') {

                for (let [key, value] of Object.entries(this.form)) {
                    if (key === 'begin_with_filter_collapsed' || key === 'display_in_repository_level_lists' || key === 'description_bellow_name' )
                        this.form[key] = (value == 'yes' || value == true) ? 'yes' : 'no';
                }
                if (this.form['begin_with_filter_collapsed'] === undefined)
                    this.form['begin_with_filter_collapsed'] = 'no';
                if (this.form['display_in_repository_level_lists'] === undefined)
                    this.form['display_in_repository_level_lists'] = 'no';
                if (this.form['description_bellow_name'] === undefined)
                    this.form['description_bellow_name'] = 'no';

                this.updateFilter({ filterId: filter.id, index: this.index, options: this.form })
                    .then(() => {
                        this.form = {};
                        this.formErrors = {};
                        this.formErrorMessage = '';
                        this.isLoading = false;
                        this.closedByForm = true;
                        this.$emit('on-edition-finished');
                    })
                    .catch((errors) => {
                        this.isLoading = false;
                        for (let error of errors.errors) {     
                            for (let attribute of Object.keys(error))
                                this.formErrors[attribute] = error[attribute];
                        }
                        this.formErrorMessage = errors.error_message;
                        this.$emit('on-error-found');

                        this.form.formErrors = this.formErrors;
                        this.form.formErrorMessage = this.formErrorMessage;
                    });
            } else {
                let formElement = document.getElementById('filterEditForm');
                let formData = new FormData(formElement); 
                let formObj = {};
                
                for (let [key, value] of formData.entries()) {
                    if (key === 'begin_with_filter_collapsed' || key === 'display_in_repository_level_lists' || key === 'description_bellow_name' )
                        formObj[key] = (value == 'yes' || value == true) ? 'yes' : 'no';
                    else
                        formObj[key] = value;
                }
                if (formObj['begin_with_filter_collapsed'] === undefined)
                    formObj['begin_with_filter_collapsed'] = 'no';
                if (formObj['display_in_repository_level_lists'] === undefined)
                    formObj['display_in_repository_level_lists'] = 'no';
                if (formObj['description_bellow_name'] === undefined)
                    formObj['description_bellow_name'] = 'no';
                    
                this.fillExtraFormData(formObj);
                this.updateFilter({ filterId: filter.id, index: this.index, options: formObj })
                    .then(() => {
                        this.form = {};
                        this.formErrors = {};
                        this.formErrorMessage = '';
                        this.isLoading = false;
                        this.closedByForm = true;
                        this.$emit('on-edition-finished');
                    })
                    .catch((errors) => {
                        this.isLoading = false;
                        for (let error of errors.errors) {     
                            for (let attribute of Object.keys(error))
                                this.formErrors[attribute] = error[attribute];
                        }
                        this.formErrorMessage = errors.error_message;
                        this.$emit('on-error-found');

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
            this.$emit('on-edition-canceled');
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
            column-gap: 3em;
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


