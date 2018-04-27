<template>
    <form
            id="termEditForm"
            class="tainacan-form"
            @submit.prevent="saveEdition(editForm)">

        <div class="columns">
            <div class="column is-narrow">
                <!-- Header Image -------------------------------- -->
                <b-field
                        :addons="false"
                        :label="$i18n.get('label_header_image')">
                    <div class="thumbnail-field">
                        <a
                                class="button is-rounred is-secondary"
                                id="button-edit-thumbnail"
                                :aria-label="$i18n.get('label_button_edit_header_image')"
                                @click="editImage($event, true)">
                            <b-icon icon="pencil"/>
                        </a>
                        <figure class="image is-128x128">
                                <span
                                        v-if="editForm.header_image === undefined || editForm.header_image === false"
                                        class="image-placeholder">{{ $i18n.get('label_empty_header_image') }}</span>
                            <img
                                    :alt="$i18n.get('label_header_image')"
                                    :src="(editForm.header_image === undefined || editForm.header_image === false) ? headerPlaceholderPath : editForm.header_image">
                        </figure>
                        <div class="thumbnail-buttons-row">
                            <a
                                    id="button-delete"
                                    :aria-label="$i18n.get('label_button_delete_header_image')"
                                    @click="deleteHeaderImage()">
                                <b-icon icon="delete"/>
                            </a>
                        </div>
                    </div>
                </b-field>
            </div>

            <div class="column">
                <b-field
                        :addons="false"
                        :type="((formErrors.name !== '' || formErrors.repeated !== '') && (formErrors.name !== undefined || formErrors.repeated !== undefined )) ? 'is-danger' : ''"
                        :message="formErrors.name ? formErrors : formErrors.repeated">
                    <label class="label">
                        {{ $i18n.get('label_name') }}
                        <span class="required-term-asterisk">*</span>
                        <help-button
                                :title="$i18n.getHelperTitle('terms', 'name')"
                                :message="$i18n.getHelperMessage('terms', 'name')"/>
                    </label>
                    <b-input
                            v-model="editForm.name"
                            name="name"
                            @focus="clearErrors({ name: 'name', repeated: 'repeated' })"/>
                </b-field>

                <b-field
                        :addons="false"
                        :type="formErrors['description'] !== '' && formErrors['description'] !== undefined ? 'is-danger' : ''"
                        :message="formErrors['description']">
                    <label class="label">
                        {{ $i18n.get('label_description') }}
                        <help-button
                                :title="$i18n.getHelperTitle('terms', 'description')"
                                :message="$i18n.getHelperMessage('terms', 'description')"/>
                    </label>
                    <b-input
                            type="textarea"
                            name="description"
                            v-model="editForm.description"
                            @focus="clearErrors('description')"/>
                </b-field>
            </div>
        </div>

        <div class="field is-grouped form-submit">
            <div class="control">
                <button
                        class="button is-outlined"
                        @click.prevent="cancelEdition()"
                        slot="trigger">
                    {{ $i18n.get('cancel') }}
                </button>
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
    import {mapActions, mapGetters} from 'vuex';

    export default {
        name: 'TermEditionForm',
        data() {
            return {
                formErrors: {},
                headerPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_rectangle.png',
                frameUploader: undefined,
            }
        },
        props: {
            editForm: Object,
            categoryId: ''
        },
        methods: {
            ...mapActions('category', [
                'sendTerm',
                'updateTerm',
            ]),
            ...mapGetters('category', [
                'getTerms'
            ]),
            saveEdition(term) {

                if (term.id === 'new') {
                    this.sendTerm({
                        categoryId: this.categoryId,
                        name: this.editForm.name,
                        description: this.editForm.description,
                        parent: this.editForm.parent,
                        headerImageId: this.editForm.header_image_id,
                    })
                        .then(() => {
                            this.editForm = {};
                            this.formErrors = {};
                            this.$emit('onEditionFinished');
                        })
                        .catch((errors) => {
                            for (let error of errors.errors) {
                                for (let field of Object.keys(error)) {
                                    this.$set(this.formErrors, field, (this.formErrors[field] !== undefined ? this.formErrors[field] : '') + error[field] + '\n');
                                }
                            }
                            this.$emit('onErrorFound');
                        });

                } else {
                    this.updateTerm({
                        categoryId: this.categoryId,
                        termId: this.editForm.id,
                        name: this.editForm.name,
                        description: this.editForm.description,
                        parent: this.editForm.parent,
                        headerImageId: this.editForm.header_image_id,
                    })
                        .then(() => {
                            this.editForm.saved = true;
                            this.formErrors = {};
                            this.$emit('onEditionFinished');
                        })
                        .catch((errors) => {
                            for (let error of errors.errors) {
                                for (let field of Object.keys(error)) {
                                    this.$set(this.formErrors, field, (this.formErrors[field] !== undefined ? this.formErrors[field] : '') + error[field] + '\n');
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
            editImage(event) {
                'use strict';
                event.preventDefault();

                // Create a new media frame
                this.frameUploader = wp.media.frames.frame_uploader = wp.media({
                    frame: 'select',
                    title: 'Select or Upload and Image.',
                    button: {
                        text: 'Select and Crop',
                        close: false
                    },
                    multiple: false,
                    library: {
                        type: 'image',
                    },
                    uploader: true,
                    states: [
                        new wp.media.controller.Library({
                            title: 'Corta pra mim!',
                            library: wp.media.query({type: 'image'}),
                            multiple: false,
                            date: false,
                            priority: 20,
                            suggestedWidth: 1000,
                            suggestedHeight: 200
                        }),
                        new wp.media.controller.Cropper({
                            imgSelectOptions: {
                                enable: true,
                                handles: true,
                                imageHeight: 200,
                                imageWidth: 1000,
                                instance: true,
                                keys: true,
                                maxWidth: 1000,
                                persistent: true,
                                x1: 0,
                                x2: 250,
                                y1: 0,
                                y2: 50
                            }
                        })
                    ]

                });

                this.frameUploader.on('select', () => {
                    this.frameUploader.state('cropper').set('canSkipCrop', true);
                    this.frameUploader.setState('cropper');
                });

                this.frameUploader.on('skippedcrop', () => {
                    let media = this.frameUploader.state().get('selection').first().toJSON();

                    this.editForm = Object.assign({},
                        this.editForm,
                        {
                            header_image_id: media.id.toString(),
                            header_image: media.url
                        }
                    );
                });

                this.frameUploader.on('cropped', (croppedImage) => {

                    this.editForm = Object.assign({},
                        this.editForm,
                        {
                            header_image_id: croppedImage.attachment_id.toString(),
                            header_image: croppedImage.url
                        }
                    );
                });

                this.frameUploader.open();
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

        .thumbnail-field {
            max-height: 128px;
            margin-bottom: 96px;
            margin-top: -20px;

            .content {
                padding: 10px;
                font-size: 0.8em;
            }
            img {
                position: absolute;
            }
            .image-placeholder {
                position: absolute;
                margin-left: 10px;
                margin-right: 10px;
                bottom: 50%;
                font-size: 0.8rem;
                font-weight: bold;
                z-index: 99;
                text-align: center;
                color: gray;
            }
            #button-edit-thumbnail, #button-edit-header-image {

                border-radius: 100px !important;
                height: 40px !important;
                width: 40px !important;
                bottom: -20px;
                left: -20px;
                z-index: 99;

                .icon {
                    display: inherit;
                    padding: 0;
                    margin: 3px 0 0 -8px;
                }
            }
            .thumbnail-buttons-row {
                display: none;
            }
            &:hover {
                .thumbnail-buttons-row {
                    display: inline-block;
                    position: relative;
                    top: -128px;
                    background-color: rgba(255, 255, 255, 0.9);
                    padding: 2px 8px;
                    border-radius: 0px 0px 0px 4px;
                    left: 88px;
                }
            }
        }
    }

</style>


