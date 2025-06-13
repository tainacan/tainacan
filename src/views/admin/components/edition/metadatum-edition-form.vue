<template>
    <form
            id="metadatumEditForm"
            autofocus="true"
            tabindex="-1"
            role="dialog"
            aria-modal
            @submit.prevent="saveEdition(form)">
        <div 
                v-if="form && Object.keys(form).length"
                class="tainacan-modal-content">
            <div class="tainacan-modal-title">
                <h2 v-if="form.name">
                    {{ $i18n.get('instruction_configure_the_metadatum') }}&nbsp;<em>{{ form.name }}</em>
                </h2>
                <h2 v-else>
                    {{ $i18n.get('instruction_configure_new_metadatum') }}
                </h2>
            </div>
            <div 
                    class="tainacan-form" 
                    :class="'tainacan-metadatum-edition-form--type-' + form.metadata_type_object.component">
                <div class="options-columns">
                    <section>
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
                                        :title="$i18n.getHelperTitle('metadata', 'name')"
                                        :message="$i18n.getHelperMessage('metadata', 'name')"
                                        :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                            </label>
                            <b-input
                                    v-model="form.name"
                                    name="name"
                                    @focus="clearErrors('name')" />
                        </b-field>

                        <!-- Hook for extra Form options -->
                        <template 
                                v-if="hasBeginLeftForm">  
                            <form 
                                    id="form-metadatum-begin-left"
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
                                        :title="$i18n.getHelperTitle('metadata', 'description')"
                                        :message="$i18n.getHelperMessage('metadata', 'description')"
                                        :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                            </label>
                            <b-input
                                    v-model="form.description"
                                    type="textarea"
                                    name="description"
                                    rows="3"
                                    @focus="clearErrors('description')" />
                        </b-field>

                        <b-field 
                                :addons="false"
                                :label="$i18n.getHelperTitle('metadata', 'description_bellow_name')"
                                :type="formErrors['description_bellow_name'] != undefined ? 'is-danger' : ''"
                                :message="formErrors['description_bellow_name'] != undefined ? formErrors['description_bellow_name'] : ''">
                            &nbsp;
                            <b-switch
                                    v-model="form.description_bellow_name"
                                    size="is-small"
                                    true-value="yes"
                                    false-value="no"
                                    name="description_bellow_name"
                                    @update:model-value="clearErrors('description_bellow_name')">
                                <help-button
                                        :title="$i18n.getHelperTitle('metadata', 'description_bellow_name')"
                                        :message="$i18n.getHelperMessage('metadata', 'description_bellow_name')"
                                        :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                            </b-switch>
                        </b-field>

                        <b-field
                                v-if="form.metadata_type_object.component != 'tainacan-geocoordinate' && form.metadata_type_object.component != 'tainacan-compound'"
                                :addons="false"
                                :type="formErrors['placeholder'] != undefined ? 'is-danger' : ''"
                                :message="formErrors['placeholder'] != undefined ? formErrors['placeholder'] : ''">
                            <label class="label is-inline">
                                {{ $i18n.getHelperTitle('metadata', 'placeholder') }}
                                <help-button
                                        :title="$i18n.getHelperTitle('metadata', 'placeholder')"
                                        :message="$i18n.getHelperMessage('metadata', 'placeholder')"
                                        :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                            </label>
                            <b-input
                                    v-model="form.placeholder"
                                    name="placeholder"
                                    @focus="clearErrors('placeholder')" />
                        </b-field>

                        <b-field
                                v-if="form.parent == 0"
                                :addons="false"
                                :type="formErrors['status'] != undefined ? 'is-danger' : ''"
                                :message="formErrors['status'] != undefined ? formErrors['status'] : ''">
                            <label class="label is-inline">
                                {{ $i18n.get('label_status') }}
                                <help-button
                                        :title="$i18n.getHelperTitle('metadata', 'status')"
                                        :message="$i18n.getHelperMessage('metadata', 'status')"
                                        :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                            </label>
                            <div class="is-flex is-justify-content-space-between">
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

                        <!-- Display on listing -->
                        <b-field
                                v-if="form.parent == 0"
                                :type="formErrors['display'] != undefined ? 'is-danger' : ''"
                                :message="formErrors['display'] != undefined ? formErrors['display'] : ''" 
                                :addons="false">
                            <label class="label is-inline">
                                {{ $i18n.get('label_display') }}
                                <help-button
                                        :title="$i18n.getHelperTitle('metadata', 'display')"
                                        :message="$i18n.getHelperMessage('metadata', 'display')"
                                        :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                            </label>
                            <b-select 
                                    v-model="form.display"
                                    expanded
                                    @update:model-value="clearErrors('display')">
                                <option value="yes">
                                    {{ $i18n.get('label_display_default') }}
                                </option>
                                <option value="no">
                                    {{ $i18n.get('label_not_display') }}
                                </option>
                                <option value="never">
                                    {{ $i18n.get('label_display_never') }}
                                </option>
                            </b-select>
                        </b-field>

                        <b-field 
                                :addons="false">
                            <label class="label is-inline">{{ $i18n.get('label_insert_options') }}</label>
                        
                            <b-field
                                    v-if="form.metadata_type_object.component != 'tainacan-compound' && (form.parent == 0 || (form.parent != 0 && !isParentMultiple))"
                                    :type="formErrors['required'] != undefined ? 'is-danger' : ''"
                                    :message="formErrors['required'] != undefined ? formErrors['required'] : ''">
                                <b-checkbox
                                        v-model="form.required"
                                        true-value="yes"
                                        false-value="no"
                                        class="is-inline-block"
                                        name="required"
                                        @update:model-value="clearErrors('required')">
                                    {{ $i18n.get('label_required') }}
                                    <help-button
                                            :title="$i18n.getHelperTitle('metadata', 'required')"
                                            :message="$i18n.getHelperMessage('metadata', 'required')"
                                            :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                                </b-checkbox>
                            </b-field>

                            <b-field
                                    v-if="form.metadata_type_object.component != 'tainacan-compound'"
                                    :type="formErrors['collection_key'] != undefined ? 'is-danger' : ''"
                                    :message="formErrors['collection_key'] != undefined ? formErrors['collection_key'] : ''">
                                <b-checkbox
                                        v-model="form.collection_key"
                                        true-value="yes"
                                        false-value="no"
                                        class="is-inline-block"
                                        name="collection_key"
                                        @update:model-value="clearErrors('collection_key')">
                                    {{ $i18n.get('label_unique_value') }}
                                    <help-button
                                            :title="$i18n.getHelperTitle('metadata', 'collection_key')"
                                            :message="$i18n.getHelperMessage('metadata', 'collection_key')"
                                            :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                                </b-checkbox>
                            </b-field>

                            <b-field
                                    v-if="!originalMetadatum.metadata_type_object.core && form.parent == 0"
                                    :type="formErrors['multiple'] != undefined ? 'is-danger' : ''"
                                    :message="formErrors['multiple'] != undefined ? formErrors['multiple'] : ''">
                                <b-checkbox
                                        v-model="form.multiple"
                                        true-value="yes"
                                        false-value="no"
                                        class="is-inline-block"
                                        name="multiple"
                                        @update:model-value="clearErrors('multiple')">
                                    {{ $i18n.get('label_allow_multiple') }}
                                    <help-button
                                            :title="$i18n.getHelperTitle('metadata', 'multiple')"
                                            :message="$i18n.getHelperMessage('metadata', 'multiple')"
                                            :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                                </b-checkbox>
                            
                            </b-field>
                        </b-field>

                        <b-field
                                v-if="!originalMetadatum.metadata_type_object.core && form.parent == 0" 
                                :addons="false"
                                :label="$i18n.get('label_limit_max_values')">
                            &nbsp;
                            <b-switch
                                    v-model="showCardinalityOptions"
                                    size="is-small"
                                    :disabled="form.multiple != 'yes'" />
                        </b-field>

                        <b-field
                                v-if="!originalMetadatum.metadata_type_object.core && form.parent == 0"
                                :type="formErrors['cardinality'] != undefined ? 'is-danger' : ''"
                                :message="formErrors['cardinality'] != undefined ? formErrors['cardinality'] : ''"
                                :addons="false">
                            <label class="label is-inline">
                                {{ $i18n.getHelperTitle('metadata', 'cardinality') }}
                                <help-button
                                        :title="$i18n.getHelperTitle('metadata', 'cardinality')"
                                        :message="$i18n.getHelperMessage('metadata', 'cardinality')"
                                        :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                            </label>
                            <b-numberinput
                                    :disabled="!showCardinalityOptions || form.multiple != 'yes'"
                                    name="cardinality"
                                    step="1"
                                    min="2"
                                    :model-value="form.cardinality ? Number(form.cardinality) : 2"
                                    @update:model-value="(newCardinalty) => form.cardinality = newCardinalty" />
                        </b-field>

                        <b-field v-if="!isRepositoryLevel && isInsideImporterFlow">
                            <b-checkbox
                                    v-model="form.repository_level"
                                    class="is-inline-block"
                                    name="repository_level"
                                    true-value="yes"
                                    false-value="no"
                                    @update:model-value="clearErrors('repository_level')">
                                {{ $i18n.get('label_repository_metadata') }}
                                <help-button
                                        :title="$i18n.getHelperTitle('metadata', 'repository_level')"
                                        :message="$i18n.getHelperMessage('metadata', 'repository_level')"
                                        :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                            </b-checkbox>
                        </b-field>


                    </section>
                </div>

                <div 
                        v-if="(form.metadata_type_object && form.metadata_type_object.form_component && form.metadata_type_object.component != 'tainacan-compound') || form.edit_form != ''"
                        class="metadata-form-section"
                        @click="hideMetadataTypeOptions = !hideMetadataTypeOptions;">
                    <span class="icon">
                        <i 
                                class="tainacan-icon"
                                :class="!hideMetadataTypeOptions ? 'tainacan-icon-arrowdown' : 'tainacan-icon-arrowright'" />
                    </span>
                    <strong>{{ $i18n.getWithVariables('label_options_of_the_%s_metadata_type', [ form.metadata_type_object.name ]) }}</strong>
                    <hr>
                </div>

                <transition name="filter-item">
                    <div 
                            v-show="!hideMetadataTypeOptions"
                            class="options-columns">
                        <component
                                :is="form.metadata_type_object.form_component"
                                v-if="(form.metadata_type_object && form.metadata_type_object.form_component) || form.edit_form != ''"
                                v-model:value="form.metadata_type_options"
                                :errors="formErrors['metadata_type_options']"
                                :metadatum="form" />
                        <div
                                v-else
                                v-html="form.edit_form" />

                        <!-- Hook for extra Form options -->
                        <template v-if="hasEndLeftForm">  
                            <form 
                                    id="form-metadatum-end-left"
                                    class="form-hook-region"
                                    v-html="getEndLeftForm" />
                        </template>
                    </div>
                </transition>

                <div 
                        class="metadata-form-section"
                        @click="showAdvancedOptions = !showAdvancedOptions;">
                    <span class="icon">
                        <i 
                                class="tainacan-icon"
                                :class="showAdvancedOptions ? 'tainacan-icon-arrowdown' : 'tainacan-icon-arrowright'" />
                    </span>
                    <strong>{{ $i18n.get('label_advanced_metadata_options') }}</strong>
                    <hr>

                </div>
            
                <transition name="filter-item">
                    <div 
                            v-if="showAdvancedOptions"
                            class="options-columns">
                        <section>
                            <b-field :addons="false">
                                <label class="label is-inline">
                                    {{ $i18n.get('label_semantic_uri') }}
                                    <help-button
                                            :title="$i18n.getHelperTitle('metadata', 'semantic_uri')"
                                            :message="$i18n.getHelperMessage('metadata', 'semantic_uri')"
                                            :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
                                </label>
                                <b-input
                                        v-model="form.semantic_uri"
                                        name="semantic_uri"
                                        type="url"
                                        @focus="clearErrors('semantic_uri')" />
                            </b-field>
                        </section>
                    </div>
                </transition>
            </div>
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
            <p class="help is-danger">
                {{ formErrorMessage }}
            </p>
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
    import { nextTick } from 'vue';
    import {mapActions} from 'vuex';
    import { formHooks } from "../../js/mixins";

    import FormText from '../metadata-types/text/FormText.vue';
    import FormTextarea from '../metadata-types/textarea/FormTextarea.vue';
    import FormRelationship from '../metadata-types/relationship/FormRelationship.vue';
    import FormTaxonomy from '../metadata-types/taxonomy/FormTaxonomy.vue';
    import FormSelectbox from '../metadata-types/selectbox/FormSelectbox.vue';
    import FormNumeric from '../metadata-types/numeric/FormNumeric.vue';
    import FormDate from '../metadata-types/date/FormDate.vue';
    import FormUser from '../metadata-types/user/FormUser.vue';
    import FormGeoCoordinate from '../metadata-types/geocoordinate/FormGeoCoordinate.vue';
    import FormUrl from '../metadata-types/url/FormUrl.vue';

    export default {
        name: 'MetadatumEditionForm',
        components: {
            'tainacan-form-text': FormText,
            'tainacan-form-textarea': FormTextarea,
            'tainacan-form-relationship': FormRelationship,
            'tainacan-form-taxonomy': FormTaxonomy,
            'tainacan-form-selectbox': FormSelectbox,
            'tainacan-form-numeric': FormNumeric,
            'tainacan-form-date': FormDate,
            'tainacan-form-user': FormUser,
            'tainacan-form-geocoordinate': FormGeoCoordinate,
            'tainacan-form-url': FormUrl
        },
        mixins: [ formHooks ],
        props: {
            index: '',
            originalMetadatum: Object,
            isRepositoryLevel: false,
            collectionId: '',
            isParentMultiple: false,
            isInsideImporterFlow: false
        },
        emits: [
            'on-edition-finished',
            'on-edition-canceled',
            'on-error-found'
        ],
        data() {
            return {
                form: {},
                formErrors: {},
                formErrorMessage: '',
                closedByForm: false,
                entityName: 'metadatum',
                isUpdating: false,
                hideMetadataTypeOptions: false,
                showAdvancedOptions: false,
                showCardinalityOptions: false
            }
        },
        watch: {
            showCardinalityOptions() {
                this.form.cardinality = !this.showCardinalityOptions ? '' : Number(this.form.cardinality);
            }
        },
        created() {
            this.form = JSON.parse(JSON.stringify(this.originalMetadatum));

            if (this.form.status == 'auto-draft')
                this.form.status = 'publish';

            if (this.form.cardinality && Number(this.form.cardinality) > 1)
                this.showCardinalityOptions = true;

            this.formErrors = this.form.formErrors != undefined ? this.form.formErrors : {};
            this.formErrorMessage = this.form.formErrors != undefined ? this.form.formErrorMessage : '';
        },
        mounted() {
            // Fills hook forms with it's real values 
            nextTick()
                .then(() => {
                    this.updateExtraFormData(this.form);
                });
        },
        methods: {
            ...mapActions('metadata', [
                'updateMetadatum'
            ]),
            saveEdition(metadatum) {
                if ( (metadatum.metadata_type_object && metadatum.metadata_type_object.form_component) || metadatum.edit_form == '') {
                    let repository = this.form.repository_level;

                    this.fillExtraFormData(this.form);
                    this.isUpdating = true;
                    this.updateMetadatum({
                        collectionId: this.collectionId,
                        metadatumId: metadatum.id,
                        isRepositoryLevel: this.isRepositoryLevel || (repository && repository === 'yes'),
                        index: this.index,
                        options: this.form,
                        includeOptionsAsHtml: true,
                        sectionId: metadatum.metadata_section_id
                    })
                        .then(() => {
                            this.form = {};
                            this.formErrors = {};
                            this.formErrorMessage = '';
                            this.isUpdating = false;
                            this.closedByForm = true;

                            this.$emit('on-edition-finished');
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
                    let formElement = document.getElementById('metadatumEditForm');
                    let formData = new FormData(formElement);
                    let formObj = {};

                    for (let [key, value] of formData.entries()) {
                        if (key === 'description_bellow_name')
                            formObj[key] = value ? 'yes' : 'no';
                        else
                            formObj[key] = value;
                    }

                    let repository = formObj['repository_level'];

                    this.fillExtraFormData(formObj);
                    this.isUpdating = true;
                    this.updateMetadatum({
                        collectionId: this.collectionId,
                        metadatumId: metadatum.id,
                        isRepositoryLevel: this.isRepositoryLevel || (repository && repository === 'yes'),
                        index: this.index,
                        options: formObj,
                        includeOptionsAsHtml: true,
                        sectionId: metadatum.metadata_section_id
                    })
                        .then(() => {
                            this.form = {};
                            this.formErrors = {};
                            this.formErrorMessage = '';
                            this.isUpdating = false;
                            this.closedByForm = true;

                            this.$emit('on-edition-finished');
                        })
                        .catch((errors) => {
                            this.isUpdating = false;

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

    form#metadatumEditForm {
        font-size: 1.125em;

        .options-columns>section {
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
            :deep(.field) {
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

            @media screen and (max-width: 600px) {
                -moz-column-count: 1;
                -webkit-column-count: 1;
                column-count: 1;
            }
        }
        .tainacan-form .field:not(:last-child) {
            margin-bottom: 1em;
        }
        .tainacan-form :deep(.control-label) {
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
                transition: background-color 0.2s ease, height 0.2s ease;
            }

            &:hover {
                .icon,
                strong {
                    color: var(--tainacan-secondary);
                }
                hr {
                    background-color: var(--tainacan-primary);
                    height: 2px;
                }
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
        padding: 16px var(--tainacan-one-column);
        display: flex;
        justify-content: space-between;
        z-index: 2;

        &::after,
        &::before {
            height: calc(2 * (var(--tainacan-modal-border-radius, 8px) + 2px ));
            width: calc(2 * (var(--tainacan-modal-border-radius, 8px) + 2px ));
            background: transparent;
            display: block;
            content: '';
            position: absolute;
        }
        &::before {
            left: 0;
            top: calc(-2 * (var(--tainacan-modal-border-radius, 8px) + 2px ));
            border-bottom-left-radius: calc(var(--tainacan-modal-border-radius, 8px) + 2px);
            box-shadow: calc(-1 * (var(--tainacan-modal-border-radius, 8px) + 2px)) 0px 0 0 var(--tainacan-gray1);
        }
        &::after {
            right: 0;
            top: calc(-2 * (var(--tainacan-modal-border-radius, 8px) + 2px ));
            border-bottom-right-radius: calc(var(--tainacan-modal-border-radius, 8px) + 2px);
            box-shadow: calc(var(--tainacan-modal-border-radius, 8px) + 2px) 0px 0 0 var(--tainacan-gray1);
        }
    }

</style>


