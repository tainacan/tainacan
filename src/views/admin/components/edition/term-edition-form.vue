<template>
    <form
            id="termEditForm"
            autofocus
            role="dialog"
            tabindex="-1"
            aria-modal
            class="tainacan-form tainacan-modal-content"
            @submit.prevent="saveEdition(form)">
        <header
                class="tainacan-page-title tainacan-modal-title">
            <h2 style="width: 60%">
                {{ form & form.id && form.id != 'new' ? $i18n.get("title_term_edit") : $i18n.get("title_term_creation") }}
            </h2>
            <a
                    v-if="form && form.url != undefined && form.url!= ''"
                    target="_blank"
                    :href="form.url">
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-openurl" />
                </span>
                <span class="menu-text">{{ $i18n.get('label_term_page_on_website') }}</span>
            </a>
            <hr>
        </header>
    
        <div class="modal-card-body">
            <b-loading
                    v-model="isLoading"
                    :is-full-page="false" />

            <!-- Name -------------- -->
            <b-field
                    :addons="false"
                    :type="((formErrors.name !== '' || formErrors.repeated !== '') && (formErrors.name !== undefined || formErrors.repeated !== undefined )) ? 'is-danger' : ''"
                    :message="formErrors.name ? formErrors.name : formErrors.repeated">
                <label class="label is-inline">
                    {{ $i18n.get('label_name') }}
                    <span class="required-term-asterisk">*</span>
                    <help-button
                            :title="$i18n.get('label_name')"
                            :message="$i18n.get('info_help_term_name')"
                            extra-classes="tainacan-repository-tooltip" /> 
                </label>
                <b-input
                        v-model="form.name"
                        :placeholder="$i18n.get('label_term_without_name')"
                        name="name"
                        @focus="clearErrors({ name: 'name', repeated: 'repeated' })" />
            </b-field>

            <!-- Hook for extra Form options -->
            <template 
                    v-if="hasBeginLeftForm">  
                <form 
                        id="form-term-begin-left"
                        class="form-hook-region"
                        v-html="getBeginLeftForm" />
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
                                        v-if="form.header_image === undefined || form.header_image === false"
                                        class="image-placeholder">{{ $i18n.get('label_empty_term_image') }}</span>
                                <img
                                        :alt="$i18n.get('label_image')"
                                        :src="(form.header_image === undefined || form.header_image === false) ? $thumbHelper.getEmptyThumbnailPlaceholder() : form.header_image">
                            </figure>
                            <div class="thumbnail-buttons-row">
                                <a
                                        id="button-edit-header"
                                        class="button is-rounded is-secondary"
                                        :aria-label="$i18n.get('label_button_edit_header_image')"
                                        @click="headerImageMediaFrame.openFrame($event)">
                                    <span 
                                            v-tooltip="{
                                                content: $i18n.get('edit'),
                                                autoHide: true,
                                                popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                                placement: 'bottom'
                                            }"
                                            class="icon is-small">
                                        <i class="tainacan-icon tainacan-icon-edit" />
                                    </span>
                                </a>
                                <a
                                        id="button-delete-header"
                                        class="button is-rounded is-secondary"
                                        :aria-label="$i18n.get('label_button_delete_thumb')"
                                        @click="deleteHeaderImage()">
                                    <span 
                                            v-tooltip="{
                                                content: $i18n.get('delete'),
                                                autoHide: true,
                                                popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                                placement: 'bottom'
                                            }"
                                            class="icon is-small">
                                        <i class="tainacan-icon tainacan-icon-delete" />
                                    </span>
                                </a>
                            </div>
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
                                    :message="$i18n.get('info_help_term_description')"
                                    extra-classes="tainacan-repository-tooltip" />
                        </label>
                        <b-input
                                v-model="form.description"
                                type="textarea"
                                name="description"
                                @focus="clearErrors('description')" />
                    </b-field>
                </div>
            </div>

            <!-- Parent -------------- -->
            <b-field
                    v-if="isHierarchical"
                    :addons="false"
                    :type="((formErrors.parent !== '' || formErrors.repeated !== '') && (formErrors.parent !== undefined || formErrors.repeated !== undefined )) ? 'is-danger' : ''"
                    :message="formErrors.parent ? formErrors : formErrors.repeated">
                <label class="label is-inline">
                    {{ $i18n.get('label_parent_term') }}
                    <b-switch
                            id="tainacan-checkbox-has-parent"
                            v-model="hasParent" 
                            size="is-small"
                            @update:model-value="onToggleSwitch()" />
                    <help-button
                            :title="$i18n.get('label_parent_term')"
                            :message="$i18n.get('info_help_parent_term')"
                            extra-classes="tainacan-repository-tooltip" />
                </label>
                <b-autocomplete
                        id="tainacan-add-parent-field"
                        v-model="parentTermName"
                        :placeholder="$i18n.get('instruction_parent_term')"
                        :data="parentTerms"
                        field="name"
                        clearable
                        :loading="isFetchingParentTerms"
                        :disabled="!hasParent"
                        :append-to-body="true"
                        check-infinite-scroll
                        @select="onSelectParentTerm($event)"
                        @update:model-value="fetchParentTerms"
                        @focus="clearErrors('parent');"
                        @infinite-scroll="fetchMoreParentTerms">
                    <template #default="props">
                        <div class="media">
                            <div 
                                    v-if="props.option.header_image"
                                    class="media-left">
                                <img 
                                        width="28"
                                        :src="props.option.header_image">
                            </div>
                            <div class="media-content">
                                {{ props.option.name }}
                            </div>
                        </div>
                    </template>
                    <template #empty>
                        {{ $i18n.get('info_no_parent_term_found') }}
                    </template>
                </b-autocomplete>
                <transition name="fade">
                    <p
                            v-show="isTermInsertionFlow != true && showCheckboxesWarning == true"
                            class="checkboxes-warning">
                        {{ $i18n.get('info_warning_changing_parent_term') }}
                    </p>
                </transition>
            </b-field>

            <!-- Hook for extra Form options -->
            <template v-if="hasEndLeftForm">  
                <form 
                        id="form-term-end-left"
                        class="form-hook-region"
                        v-html="getEndLeftForm" />
            </template>

            <!-- Submit buttons -------------- -->
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
                    <button
                            class="button is-success"
                            type="submit">
                        {{ isTermInsertionFlow ? $i18n.get('label_create_and_select') : $i18n.get('save') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
    import { nextTick } from 'vue';
    import { formHooks } from "../../js/mixins";
    import { mapActions } from 'vuex';
    import wpMediaFrames from '../../js/wp-media-frames';

    export default {
        name: 'TermEditionForm',
        mixins: [ formHooks ],
        props: {
            originalForm: Object,
            taxonomyId: '',
            isHierarchical: Boolean,
            isTermInsertionFlow: false,
            metadatumId: [String, Number],
            itemId: [String, Number]
        },
        emits: [
            'on-edition-finished',
            'close'
        ],
        data() {
            return {
                formErrors: {},
                headerImageMediaFrame: undefined,
                isFetchingParentTerms: false,
                parentTerms: [],
                parentTermName: '',
                showCheckboxesWarning: false,
                hasParent: false,
                hasChangedParent: false,
                initialParentId: undefined,
                entityName: 'term',
                isLoading: false,
                parentTermSearchQuery: '',
                parentTermSearchOffset: 0,
                form: {},
                totalTerms: undefined
            }
        },
        created() {
            this.form = JSON.parse(JSON.stringify(this.originalForm));
        },
        mounted() {

            // Fills hook forms with it's real values 
            nextTick()
                .then(() => {
                    this.updateExtraFormData(this.form);
                    document.getElementById('termEditForm').scrollIntoView({ behavior: 'smooth' });
                });

            this.showCheckboxesWarning = false;
            this.hasParent = this.form.parent != undefined && this.form.parent > 0;
            this.initialParentId = this.form.parent;
            this.initializeMediaFrames();

            if (this.hasParent) {
                this.isFetchingParentTerms = true;
                this.showCheckboxesWarning = false;
                this.fetchParentName({ taxonomyId: this.taxonomyId, parentId: this.form.parent })
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
        },
        methods: {
            ...mapActions('taxonomy', [
                'sendChildTerm',
                'updateTerm',
                'fetchParentName',
                'fetchPossibleParentTerms'
            ]),
            saveEdition(term) {

                if (term.id === 'new') {
                    let data = {
                        name: this.form.name,
                        description: this.form.description,
                        parent: this.hasParent ? this.form.parent : 0,
                        header_image_id: this.form.header_image_id,
                        header_image: this.form.header_image,
                    };
                    this.fillExtraFormData(data);
                    this.isLoading = true;
                    this.sendChildTerm({
                        taxonomyId: this.taxonomyId,
                        term: data,
                        metadatumId: this.metadatumId,
                        itemId: this.itemId
                    })
                        .then((term) => {
                            this.$emit('on-edition-finished', {term: term, hasChangedParent: this.hasChangedParent, initialParent: this.initialParentId });
                            this.form = {};
                            this.formErrors = {};
                            this.isLoading = false;
                            this.$emit('close');
                        })
                        .catch((errors) => {
                            this.isLoading = false;

                            for (let error of errors.errors) {
                                for (let metadatum of Object.keys(error)) {
                                   Object.assign( this.formErrors, { [metadatum]: (this.formErrors[metadatum] !== undefined ? this.formErrors[metadatum] : '') + error[metadatum] + '\n' });
                                }
                            }
                        });

                } else {

                    let data = {
                        id: this.form.id,
                        name: this.form.name,
                        description: this.form.description,
                        parent: this.hasParent ? this.form.parent : 0,
                        header_image_id: this.form.header_image_id,
                        header_image: this.form.header_image,
                        total_children:	this.form.total_children ? this.form.total_children : 0
                    }
                    this.fillExtraFormData(data);
                    this.isLoading = true;
                    this.updateTerm({
                        taxonomyId: this.taxonomyId,
                        term: data,
                        metadatumId: this.metadatumId,
                        itemId: this.itemId
                    })
                        .then((term) => {
                            this.formErrors = {};
                            this.$emit('on-edition-finished', { term: term, hasChangedParent: this.hasChangedParent, initialParent: this.initialParentId });
                            this.$emit('close');
                        })
                        .catch((errors) => {
                            for (let error of errors.errors) {
                                for (let metadatum of Object.keys(error)) {
                                    Object.assign( this.formErrors, { [metadatum]: (this.formErrors[metadatum] !== undefined ? this.formErrors[metadatum] : '') + error[metadatum] + '\n' });
                                }
                            }
                            this.isLoading = false;
                        });
                }
            },
            cancelEdition() {
                this.$emit('close');
            },
            deleteHeaderImage() {
                this.form = Object.assign({},
                    this.form,
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
                        relatedPostId: this.form.id,
                        onSave: (croppedImage) => {

                           this.form = Object.assign({},
                                this.form,
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
            fetchParentTerms: _.debounce(function(search) {

                // String update
                if (search != this.parentTermSearchQuery) {
                    this.parentTermSearchQuery = search;
                    this.parentTerms = [];
                    this.parentTermSearchOffset = 0;
                } 
                
                // String cleared
                if (!search.length) {
                    this.parentTermSearchQuery = search;
                    this.parentTerms = [];
                    this.parentTermSearchOffset = 0;
                }

                // No need to load more
                if (this.parentTermSearchOffset > 0 && this.totalTerms !== undefined && this.parentTerms.length >= this.totalTerms)
                    return;


                this.isFetchingParentTerms = true;
                
                this.fetchPossibleParentTerms({
                        taxonomyId: this.taxonomyId, 
                        termId: this.form.id, 
                        search: this.parentTermSearchQuery,
                        offset: this.parentTermSearchOffset })
                    .then((res) => {
                        for (let term of res.parentTerms)
                            this.parentTerms.push(term);

                        this.parentTermSearchOffset += 12;
                        this.totalTerms = res.totalTerms;
                        this.isFetchingParentTerms = false;
                    })
                    .catch((error) => {
                        this.$console.error(error);
                        this.isFetchingParentTerms = false;
                    });
            }, 500),
            fetchMoreParentTerms: _.debounce(function () {
                this.fetchParentTerms(this.parentTermSearchQuery)
            }, 250),
            onToggleSwitch() {

                if (this.form.parent == 0) {
                    this.hasChangedParent = this.hasParent;
                } else {
                    this.hasChangedParent = !this.hasParent;
                }
                
                this.showCheckboxesWarning = true; 
                this.clearErrors('parent');
            },
            onSelectParentTerm(selectedParentTerm) {
                if ( selectedParentTerm ) {
                    this.hasChangedParent = this.initialParentId != selectedParentTerm.id;
                    this.form.parent = selectedParentTerm.id;
                    this.selectedParentTerm = selectedParentTerm;
                    this.parentTermName = selectedParentTerm.name;
                    this.showCheckboxesWarning = true;
                }
            }
        }
    }
</script>

<style lang="scss" scoped>

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

        &:not(.tainacan-modal-content) {
            padding: 1.7em 0 1.5em 1.5em;
            border-left: 1px solid var(--tainacan-gray2);
            margin-left: 0.75em;
            position: relative;
            animation-name: enter;
            animation-duration: 0.5s;
        }
        &.tainacan-modal-content {
            overflow: hidden;

            .field {
                padding-left: 0;
                margin-left: 0;
            }

            .tainacan-modal-title {
                margin: 0;
                padding: 0 12px;
            }
            .thumbnail-field {
                max-width: 120px;
            }
            .image-placeholder {
                left: 2px;
            }
        }

        .tainacan-page-title {
            margin-bottom: 28px;
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;

            h2 {
                font-size: 1.25em;
                font-weight: 500;
                color: var(--tainacan-blue5);
                display: inline-block;
                margin-right: auto;
            }
            hr {
                margin: 3px 0px 4px 0px; 
                width: 100%;
                height: 1px;
                background-color: var(--tainacan-secondary);
            }
        }

        .image-and-description-area {
            margin-bottom: 0px;

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
                padding: 0 8px;
                font-size: 95%;
                font-weight: bold;
                z-index: 99;
                text-align: center;
                color: var(--tainacan-info-color);
                background-color: transparent;
            }
            #button-delete-header,
            #button-edit-header {

                border-radius: 100px !important;
                max-height: 2.125em !important;
                max-width: 2.125em !important;
                min-height: 2.125em !important;
                min-width: 2.125em !important;
                padding: 0 !important;
                z-index: 99;
                margin-left: 10px !important;
                
                .icon {
                    color: var(--tainacan-white) !important;
                    display: inherit;
                    padding: 0;
                    margin: 0;
                    margin-top: -2px;
                    font-size: 1.125em;
                }
            }
                
            .thumbnail-buttons-row {
                text-align: right;
                top: -0.9375em;
                position: relative;
            }
        }
        .checkboxes-warning {
            color: var(--tainacan-gray5);
            font-style: italic;
            padding: 0.2em 0.75em;
        }
    }

</style>


