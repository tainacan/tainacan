<template>
    <form
            id="termEditForm"
            class="tainacan-form"
            @submit.prevent="saveEdition(editForm)">
        <div class="tainacan-page-title">
            <h2>{{ $i18n.get("title_term_edition") }}</h2>
            <hr>
        </div>
    
        <!-- Name -------------- -->
        <b-field
                :addons="false"
                :type="((formErrors.name !== '' || formErrors.repeated !== '') && (formErrors.name !== undefined || formErrors.repeated !== undefined )) ? 'is-danger' : ''"
                :message="formErrors.name ? formErrors : formErrors.repeated">
            <label class="label is-inline">
                {{ $i18n.get('label_name') }}
                <span class="required-term-asterisk">*</span>
                <help-button
                        :title="$i18n.get('label_name')"
                        :message="$i18n.get('info_help_term_name')"/>
            </label>
            <b-input
                    v-model="editForm.name"
                    name="name"
                    @focus="clearErrors({ name: 'name', repeated: 'repeated' })"/>
        </b-field>

        <!-- Hook for extra Form options -->
        <template 
                v-if="formHooks != undefined && 
                    formHooks['term'] != undefined &&
                    formHooks['term']['begin-left'] != undefined">  
            <form 
                id="form-term-begin-left"
                class="form-hook-region"
                v-html="formHooks['term']['begin-left'].join('')"/>
        </template>

        <div class="columns is-gapless image-and-description-area">
            <div class="column">

                <!-- Header Image -------------------------------- -->
                <b-field
                        :addons="false"
                        :label="$i18n.get('label_image')">
                    <div class="thumbnail-field">
                        <figure class="image">
                            <span
                                    v-if="editForm.header_image === undefined || editForm.header_image === false"
                                    class="image-placeholder">{{ $i18n.get('label_empty_term_image') }}</span>
                            <img
                                    :alt="$i18n.get('label_image')"
                                    :src="(editForm.header_image === undefined || editForm.header_image === false) ? headerPlaceholderPath : editForm.header_image">
                        </figure>
                        <div class="thumbnail-buttons-row">
                            <a
                                    class="button is-rounded is-secondary"
                                    id="button-edit-header"
                                    :aria-label="$i18n.get('label_button_edit_header_image')"
                                    @click="headerImageMediaFrame.openFrame($event)">
                                <span 
                                        v-tooltip="{
                                            content: $i18n.get('edit'),
                                            autoHide: true,
                                            classes: ['tooltip', 'repository-tooltip'],
                                            placement: 'bottom'
                                        }"
                                        class="icon is-small">
                                    <i class="tainacan-icon tainacan-icon-edit"/>
                                </span>
                            </a>
                            <a
                                    class="button is-rounded is-secondary"
                                    id="button-delete-header"
                                    :aria-label="$i18n.get('label_button_delete_thumb')"
                                    @click="deleteHeaderImage()">
                                <span 
                                        v-tooltip="{
                                            content: $i18n.get('delete'),
                                            autoHide: true,
                                            classes: ['tooltip', 'repository-tooltip'],
                                            placement: 'bottom'
                                        }"
                                        class="icon is-small">
                                    <i class="tainacan-icon tainacan-icon-delete"/>
                                </span>
                            </a>
                        </div>
                        <br>
                    </div>
                </b-field>
            </div>

            <div class="column">
                <!-- Description -------------- -->
                <b-field
                        :addons="false"
                        :type="formErrors['description'] !== '' && formErrors['description'] !== undefined ? 'is-danger' : ''"
                        :message="formErrors['description']">
                    <label class="label">
                        {{ $i18n.get('label_description') }}
                        <help-button
                                :title="$i18n.get('label_description')"
                                :message="$i18n.get('info_help_term_description')"/>
                    </label>
                    <b-input
                            type="textarea"
                            name="description"
                            v-model="editForm.description"
                            @focus="clearErrors('description')"/>
                </b-field>
            </div>
        </div>

        <!-- Parent -------------- -->
        <b-field
                :addons="false"
                :type="((formErrors.parent !== '' || formErrors.repeated !== '') && (formErrors.parent !== undefined || formErrors.repeated !== undefined )) ? 'is-danger' : ''"
                :message="formErrors.parent ? formErrors : formErrors.repeated">
           <label class="label is-inline">
                {{ $i18n.get('label_parent_term') }}
                 <b-switch
                        @input="onToggleSwitch()"
                        id="tainacan-checkbox-has-parent" 
                        size="is-small"
                        v-model="hasParent" />
                <help-button
                        :title="$i18n.get('label_parent_term')"
                        :message="$i18n.get('info_help_parent_term')"/>
            </label>
            <b-autocomplete
                    id="tainacan-text-cover-page"
                    :placeholder="$i18n.get('instruction_parent_term')"
                    :data="parentTerms"
                    field="name"
                    v-model="parentTermName"
                    @select="onSelectParentTerm($event)"
                    :loading="isFetchingParentTerms"
                    @input="fecthParentTerms($event)"
                    @focus="clearErrors('parent');"
                    :disabled="!hasParent">
                <template slot-scope="props">
                    {{ props.option.name }}
                </template>
                <template slot="empty">{{ $i18n.get('info_no_parent_term_found') }}</template>
            </b-autocomplete>
            <transition name="fade">
                <p
                        class="checkboxes-warning"
                        v-show="showCheckboxesWarning == true">
                    {{ $i18n.get('info_warning_changing_parent_term') }}
                </p>
            </transition>
        </b-field>

        <!-- Hook for extra Form options -->
        <template 
                v-if="formHooks != undefined && 
                    formHooks['term'] != undefined &&
                    formHooks['term']['end-left'] != undefined">  
            <form 
                id="form-term-end-left"
                class="form-hook-region"
                v-html="formHooks['term']['end-left'].join('')"/>
        </template>

        <!-- Submit buttons -------------- -->
        <div class="field is-grouped form-submit">
            <div class="control">
                <button
                        type="button"
                        class="button is-outlined"
                        @click.prevent="cancelEdition()"
                        slot="trigger">
                    {{ $i18n.get('cancel') }}
                </button>
            </div>
            <div class="control">
                <a
                        type="button"
                        v-if="editForm.url != undefined && editForm.url!= ''"
                        class="button is-secondary"
                        target="_blank"
                        :href="editForm.url">
                    {{ $i18n.get('label_view_term') }}
                </a>
            </div>
            <div class="control">
                <button
                        class="button is-success"
                        type="submit">
                    {{ $i18n.get('save') }}
                </button>
            </div>
        </div>
    </form>
</template>

<script>
    import { formHooks } from "../../js/mixins";
    import {mapActions, mapGetters} from 'vuex';
    import wpMediaFrames from '../../js/wp-media-frames';

    export default {
        name: 'TermEditionForm',
        mixins: [ formHooks ],
        data() {
            return {
                formErrors: {},
                headerPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png',
                headerImageMediaFrame: undefined,
                isFetchingParentTerms: false,
                parentTerms: [],
                parentTermName: '',
                showCheckboxesWarning: false,
                hasParent: false,
                hasChangedParent: false,
                initialParentId: undefined,
                entityName: 'term'
            }
        },
        props: {
            editForm: Object,
            taxonomyId: ''
        },
        methods: {
            ...mapActions('taxonomy', [
                'sendChildTerm',
                'updateChildTerm',
                'fetchParentName',
                'fetchPossibleParentTerms'
            ]),
            ...mapGetters('taxonomy', [
                'getTerms'
            ]),
            saveEdition(term) {

                if (term.id === 'new') {
                    let data = {
                        name: this.editForm.name,
                        description: this.editForm.description,
                        parent: this.hasParent ? this.editForm.parent : 0,
                        header_image_id: this.editForm.header_image_id,
                        header_image: this.editForm.header_image,
                    };
                    this.fillExtraFormData(data);
                    this.sendChildTerm({
                        taxonomyId: this.taxonomyId,
                        term: data
                    })
                        .then((term) => {
                            this.$emit('onEditionFinished', {term: term, hasChangedParent: this.hasChangedParent });
                            this.editForm = {};
                            this.formErrors = {};
                        })
                        .catch((errors) => {
                            for (let error of errors.errors) {
                                for (let metadatum of Object.keys(error)) {
                                    this.$set(this.formErrors, metadatum, (this.formErrors[metadatum] !== undefined ? this.formErrors[metadatum] : '') + error[metadatum] + '\n');
                                }
                            }
                            this.$emit('onErrorFound');
                        });

                } else {

                    let data = {
                        id: this.editForm.id,
                        name: this.editForm.name,
                        description: this.editForm.description,
                        parent: this.hasParent ? this.editForm.parent : 0,
                        header_image_id: this.editForm.header_image_id,
                        header_image: this.editForm.header_image,
                    }
                    this.fillExtraFormData(data);
                    this.updateChildTerm({
                        taxonomyId: this.taxonomyId,
                        term: data
                    })
                        .then((term) => {
                            this.formErrors = {};
                            this.$emit('onEditionFinished', { term: term, hasChangedParent: this.hasChangedParent });
                        })
                        .catch((errors) => {
                            for (let error of errors.errors) {
                                for (let metadatum of Object.keys(error)) {
                                    this.$set(this.formErrors, metadatum, (this.formErrors[metadatum] !== undefined ? this.formErrors[metadatum] : '') + error[metadatum] + '\n');
                                }
                            }
                            this.$emit('onErrorFound');
                        });
                }
            },
            cancelEdition() {
                this.$emit('onEditionCanceled', this.editForm);
            },
            deleteHeaderImage() {
                this.editForm = Object.assign({},
                    this.editForm,
                    {
                        header_image_id: "0",
                        header_image: false
                    }
                );
            },
            initializeMediaFrames() {
                this.headerImageMediaFrame = new wpMediaFrames.thumbnailControl(
                    'my-thumbnail-image-media-frame', {
                        button_labels: {
                            frame_title: this.$i18n.get('instruction_select_term_header_image'),
                            frame_button: this.$i18n.get('label_select_file')
                        },
                        relatedPostId: this.editForm.id,
                        onSave: (croppedImage) => {

                           this.editForm = Object.assign({},
                                this.editForm,
                                {
                                    header_image_id: croppedImage.id.toString(),
                                    header_image: croppedImage.url
                                }
                            );
                        }
                    }
                );
            },
            clearErrors(attributes) {
                if(attributes instanceof Object){
                    for(let attribute in attributes){
                        this.formErrors[attribute] = undefined;
                    }
                } else {
                    this.formErrors[attributes] = undefined;
                }
            },
            fecthParentTerms(search) {
                this.isFetchingParentTerms = true;
                
                this.fetchPossibleParentTerms({
                        taxonomyId: this.taxonomyId, 
                        termId: this.editForm.id, 
                        search: search })
                    .then((parentTerms) => {
                        this.parentTerms = parentTerms;
                        this.isFetchingParentTerms = false;
                    })
                    .catch((error) => {
                        this.$console.error(error);
                        this.isFetchingParentTerms = false;
                    });
            },
            onToggleSwitch() {

                if (this.editForm.parent == 0) {
                    this.hasChangedParent = this.hasParent;
                } else {
                    this.hasChangedParent = !this.hasParent;
                }
                
                this.showCheckboxesWarning = true; 
                this.clearErrors('parent');
            },
            onSelectParentTerm(selectedParentTerm) {
                this.hasChangedParent = this.initialParentId != selectedParentTerm.id;
                this.editForm.parent = selectedParentTerm.id;
                this.selectedParentTerm = selectedParentTerm;
                this.parentTermName = selectedParentTerm.name;
                this.showCheckboxesWarning = true;
            }
        },
        mounted() {

            // Fills hook forms with it's real values 
            this.$nextTick()
                .then(() => {
                    this.updateExtraFormData(this.editForm);
                });

            this.showCheckboxesWarning = false;
            this.hasParent = this.editForm.parent != undefined && this.editForm.parent > 0;
            this.initialParentId = this.editForm.parent;
            this.initializeMediaFrames();

            if (this.hasParent) {
                this.isFetchingParentTerms = true;
                this.showCheckboxesWarning = false;
                this.fetchParentName({ taxonomyId: this.taxonomyId, parentId: this.editForm.parent })
                    .then((parentName) => {
                        this.parentTermName = parentName;
                        this.isFetchingParentTerms = false;
                        this.showCheckboxesWarning = false;
                    })
                    .catch((error) => {
                        this.$console.error(error);
                        this.isFetchingParentTerms = false;
                        this.showCheckboxesWarning = false;
                    });
            }
        }
    }
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .column {
        padding: 0;
        &.is-narrow {
            padding-right: 42px;
        }
    }    

    @keyframes enter {
        from {
            opacity: 0;
            transform: translate(-40px,0);
        }
        to {
            opacity: 1;
            transform: translate(0px,0);
        }
    }

    form#termEditForm {
        padding: 1.7rem 0 1.5rem 1.5rem;
        border-left: 1px solid $gray2;
        margin-left: 0.75rem;
        position: relative;
        animation-name: enter;
        animation-duration: 0.5s;

        .tainacan-page-title {
            margin-bottom: 35px;

            h2 {
                font-size: 20px;
                font-weight: 500;
                color: $blue5;
                display: inline-block;
            }
            hr{
                margin: 3px 0px 4px 0px; 
                height: 1px;
                background-color: $secondary;
            }
        }

        .image-and-description-area {
            margin-bottom: 0px;
            margin-top: 24px;

            .column:first-of-type {
                margin-right: 24px;
            }
            .column:last-of-type {
                flex-grow: 2;
            }
        }

        .thumbnail-field {

            .content {
                padding: 10px;
                font-size: 0.8em;
            }
            img {
                position: relative;
            }
            .image-placeholder {
                position: absolute;
                margin-left: auto;
                margin-right: auto;
                width: 100%;
                top: 35%;
                font-size: 1.25rem;
                font-weight: bold;
                z-index: 99;
                text-align: center;
                color: $gray4;
            }
            #button-delete-header,
            #button-edit-header {

                border-radius: 100px !important;
                max-height: 30px !important;
                max-width: 30px !important;
                min-height: 30px !important;
                min-width: 30px !important;
                padding: 0 !important;
                z-index: 99;
                margin-left: 10px !important;
                
                .icon {
                    color: white !important;
                    display: inherit;
                    padding: 0;
                    margin: 0;
                    margin-top: -2px;
                    font-size: 18px;
                }
            }
                
            .thumbnail-buttons-row {
                text-align: right;
                top: -15px;
                position: relative;
            }
        }
        .checkboxes-warning {
            color: $gray5;
            font-style: italic;
            padding: 0.2rem 0.75rem;
        }
    }

</style>


