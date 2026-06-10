<template>
    <div>
        <div 
                v-if="!$adminOptions.hideItemEditionThumbnail"
                class="section-label">
            <label>
                <span 
                        class="icon has-text-dark"
                        aria-hidden="true">
                    <i class="tainacan-icon tainacan-icon-image" />
                </span>
                {{ collection && collection.item_thumbnail_label ? collection.item_thumbnail_label : $i18n.get('label_thumbnail') }}
            </label>
            <help-button
                    :title="collection && collection.item_thumbnail_label ? collection.item_thumbnail_label: $i18n.getHelperTitle('items', '_thumbnail_id')"
                    :message="$i18n.getHelperMessage('items', '_thumbnail_id')" />

        </div>
        <div 
                v-if="!$adminOptions.hideItemEditionThumbnail"
                class="section-box section-thumbnail"
                :style="{ 'margin-bottom': aiAltTextAvailable ? '1rem' : '' }">
            <div class="thumbnail-field">
                <file-item
                        v-if="item.thumbnail != undefined && ((item.thumbnail['tainacan-medium'] != undefined && item.thumbnail['tainacan-medium'] != false) || (item.thumbnail.medium != undefined && item.thumbnail.medium != false))"
                        :show-name="false"
                        :modal-on-click="false"
                        :size="120"
                        :file="{
                            media_type: 'image',
                            thumbnails: { 'tainacan-medium': [ $thumbHelper.getSrc(item['thumbnail'], 'tainacan-medium', item.document_mimetype) ] },
                            title: $i18n.get('label_thumbnail'),
                            description: `<img alt='` + $i18n.get('label_thumbnail') + `' src='` + $thumbHelper.getSrc(item['thumbnail'], 'full', item.document_mimetype) + `'/>` 
                        }" />
                <figure
                        v-if="item.thumbnail == undefined || ((item.thumbnail.medium == undefined || item.thumbnail.medium == false) && (item.thumbnail['tainacan-medium'] == undefined || item.thumbnail['tainacan-medium'] == false))"
                        class="image">
                    <span 
                            v-if="item.document_type == 'empty' && item.document_mimetype == 'empty'"
                            class="image-placeholder">
                        {{ $i18n.get('label_empty_thumbnail') }}
                    </span>
                    <img
                            :alt="$i18n.get('label_thumbnail')"
                            :src="$thumbHelper.getEmptyThumbnailPlaceholder(item.document_mimetype)">
                </figure>
                <b-field
                        v-if="item.thumbnail_id"
                        :addons="false" 
                        :label="$i18n.get('label_thumbnail_alt')">
                    <help-button 
                            :title="$i18n.get('label_thumbnail_alt')" 
                            :message="$i18n.get('info_thumbnail_alt')" />
                    <textarea
                            id="tainacan-text-description"
                            class="textarea"
                            rows="4"
                            :value="form.thumbnail_alt && form.thumbnail_alt != 'false' ? form.thumbnail_alt : ''"
                            @input="updateThumbnailAlt" />
                    <div
                            v-if="aiAltTextAvailable"
                            class="thumbnail-alt-ai-actions">
                        <a
                                role="button"
                                tabindex="0"
                                :loading="isGeneratingAiAlt"
                                :disabled="isGeneratingAiAlt"
                                class="link-style"
                                @click.prevent="onGenerateThumbnailAltAi">
                            <span class="icon is-small">
                                <i 
                                        class="tainacan-icon has-text-secondary tainacan-icon-updating"
                                        :class="isGeneratingAiAlt ? 'tainacan-icon-spin' : ''" />
                            </span>
                            {{ hasThumbnailAltText ? $i18n.get('label_regenerate_ai') : $i18n.get('label_generate_ai') }}
                        </a>
                    </div>
                </b-field>    
                <div class="thumbnail-buttons-row">
                    <a
                            id="button-edit-thumbnail"
                            class="button is-rounded is-secondary"
                            role="button"
                            tabindex="0"
                            :aria-label="$i18n.get('label_button_edit_thumb')"
                            @click.prevent="($event) => $emit('open-thumbnail-media-frame', $event)"
                            @keydown.enter.prevent="($event) => $emit('open-thumbnail-media-frame', $event)"
                            @keydown.space.prevent="($event) => $emit('open-thumbnail-media-frame', $event)">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('edit'),
                                    autoHide: true,
                                    placement: 'bottom',
                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                }"
                                class="icon"
                                aria-hidden="true">
                            <i class="tainacan-icon tainacan-icon-edit" />
                        </span>
                    </a>
                    <a
                            v-if="item.thumbnail && item.thumbnail.thumbnail != undefined && item.thumbnail.thumbnail != false"
                            id="button-delete-thumbnail"
                            class="button is-rounded is-secondary"
                            role="button"
                            tabindex="0"
                            :aria-label="$i18n.get('label_button_delete_thumb')"
                            @click="$emit('on-delete-thumbnail')"
                            @keydown.enter.prevent="$emit('on-delete-thumbnail')"
                            @keydown.space.prevent="$emit('on-delete-thumbnail')">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('delete'),
                                    autoHide: true,
                                    placement: 'bottom',
                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                }"
                                class="icon"
                                aria-hidden="true">
                            <i class="tainacan-icon tainacan-icon-delete" />
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapActions } from 'vuex';
import FileItem from '../other/file-item.vue';

export default {
    components: {
        FileItem
    },
    props: {
        item: Object,
        collection: Object,
        form: Object
    },
    emits: [
        'open-thumbnail-media-frame',
        'on-delete-thumbnail',
        'on-update-thumbnail-alt'
    ],
    data() {
        return {
            isGeneratingAiAlt: false
        };
    },
    computed: {
        aiAltTextAvailable() {
            return typeof tainacan_plugin !== 'undefined' && !!tainacan_plugin.ai_alt_text_generation_available;
        },
        hasThumbnailAltText() {
            const altText = this.form && this.form.thumbnail_alt;
            return !!(altText && altText !== 'false' && String(altText).trim() !== '');
        }
    },
    methods: {
        ...mapActions('item', [
            'generateThumbnailAltWithAi'
        ]),
        onGenerateThumbnailAltAi() {
            if (!this.item || !this.item.thumbnail_id) {
                return;
            }
            this.isGeneratingAiAlt = true;
            this.generateThumbnailAltWithAi({ thumbnailId: this.item.thumbnail_id })
                .then((result) => {
                    this.isGeneratingAiAlt = false;
                    this.$emit('on-update-thumbnail-alt', result.alt_text);
                    this.$buefy.snackbar.open({
                        message: result.is_decorative
                            ? this.$i18n.get('info_thumbnail_alt_ai_decorative')
                            : this.$i18n.get('info_thumbnail_alt_ai_success'),
                        type: result.is_decorative ? 'is-info' : 'is-success',
                        position: 'is-bottom-right',
                        duration: 5000,
                        queue: false
                    });
                })
                .catch((err) => {
                    this.isGeneratingAiAlt = false;
                    let msg = this.$i18n.get('error_thumbnail_alt_ai_failed');
                    if (err && err.message === 'empty_alt_response') {
                        msg = this.$i18n.get('error_thumbnail_alt_ai_empty_response');
                    } else if (err && err.message === 'abilities_unavailable') {
                        msg = this.$i18n.get('error_thumbnail_alt_ai_unavailable');
                    } else if (err?.error?.response?.data?.message) {
                        msg = err.error.response.data.message;
                    } else if (err && err.errorMessage) {
                        msg = err.errorMessage;
                    }
                    this.$buefy.snackbar.open({
                        message: msg,
                        type: 'is-danger',
                        position: 'is-bottom-right',
                        duration: 6000,
                        queue: false
                    });
                    this.$console.error(err);
                });
        },
        updateThumbnailAlt: _.debounce(function($event) {
            this.$emit('on-update-thumbnail-alt', $event.target.value);
        }, 750)
    }
}
</script>

<style lang="scss" scoped>
    .section-thumbnaill {
        padding-inline-end: 0;
    }
    .thumbnail-buttons-row {
        bottom: -6px;
        inset-inline-start: 0.875em;
        position: absolute;
    }
    .thumbnail-field {
        display: flex;
        min-height: 110px;

        @supports (contain: inline-size) {
            container-type: inline-size;
            container-name: thumbnailfield; 
        }

        @container thumbnailfield (max-width: 300px) {
            :deep(img),
            :deep(.image-wrapper) {
                height: 58px !important;
                width: 58px !important;
                min-width: 58px !important;
            }
            :deep(.image-placeholder) {
                top: 12px !important;
                font-size: 0.75em !important;
                margin-left: 3px !important;
                margin-right: 3px !important;
            }
            .thumbnail-buttons-row {
                bottom: 42px !important;
            }
        }

        .field {
            margin-inline-start: 1em;
            width: 100%;
        }
        .thumbnail-alt-ai-actions  {
            position: relative;
            a {
                margin-top: 0.125rem;
                font-size: 0.875em;
                position: absolute;
                inset-inline-end: 0;
            }
        }
        .content {
            padding: 10px;
            font-size: 0.8em;
        }
        img {
            height: 110px;
            width: 110px;
            min-width: 110px;
            border-radius: var(--tainacan-item-border-radius, 3px);
        }
        .image-placeholder {
            position: absolute;
            margin-left: 20px;
            margin-right: 20px;
            font-size: 0.8em;
            font-weight: bold;
            z-index: 99;
            text-align: center;
            color: var(--tainacan-info-color);
            top: 34px;
            max-width: 84px;

            & + img {
                opacity: 0.5;
                border: 1px dashed var(--tainacan-info-color);
            }
        }

        .thumbnail-alt-input {
            .label {
                font-size: 0.875em;
                font-weight: 500;
                margin-left: 15px;
                margin-bottom: 0;
                margin-top: 0.15em;
            }
        }
    }
</style>