<template>
    <div>
        <div 
                v-if="!$adminOptions.hideItemEditionThumbnail"
                class="section-label">
            <label>
                <span class="icon has-text-gray4">
                    <i class="tainacan-icon tainacan-icon-image"/>
                </span>
                {{ $i18n.get('label_thumbnail') }}
            </label>
            <help-button
                    :title="$i18n.getHelperTitle('items', '_thumbnail_id')"
                    :message="$i18n.getHelperMessage('items', '_thumbnail_id')"/>

        </div>
        <div 
                v-if="!isLoading && !$adminOptions.hideItemEditionThumbnail"
                class="section-box section-thumbnail">
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
                        }"/>
                <figure
                        v-if="item.thumbnail == undefined || ((item.thumbnail.medium == undefined || item.thumbnail.medium == false) && (item.thumbnail['tainacan-medium'] == undefined || item.thumbnail['tainacan-medium'] == false))"
                        class="image">
                    <span 
                            class="image-placeholder"
                            v-if="item.document_type == 'empty' && item.document_mimetype == 'empty'">
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
                            :message="$i18n.get('info_thumbnail_alt')"/>
                    <textarea
                            id="tainacan-text-description"
                            class="textarea"
                            rows="4"
                            :value="form.thumbnail_alt && form.thumbnail_alt != 'false' ? form.thumbnail_alt : ''"
                            @input="updateThumbnailAlt" />
                </b-field>    
                <div class="thumbnail-buttons-row">
                    <a
                            class="button is-rounded is-secondary"
                            id="button-edit-thumbnail"
                            :aria-label="$i18n.get('label_button_edit_thumb')"
                            @click.prevent="($event) => $emit('openThumbnailMediaFrame', $event)">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('edit'),
                                    autoHide: true,
                                    placement: 'bottom',
                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-edit"/>
                        </span>
                    </a>
                    <a
                            v-if="item.thumbnail && item.thumbnail.thumbnail != undefined && item.thumbnail.thumbnail != false"
                            id="button-delete-thumbnail"
                            class="button is-rounded is-secondary"
                            :aria-label="$i18n.get('label_button_delete_thumb')"
                            @click="$emit('onDeleteThumbnail')">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('delete'),
                                    autoHide: true,
                                    placement: 'bottom',
                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-delete"/>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import FileItem from '../other/file-item.vue';

export default {
    components: {
        FileItem
    },
    props: {
        item: Object,
        form: Object
    },
    methods: {
        updateThumbnailAlt: _.debounce(function($event) {
            this.$emit('onUpdateThumbnailAlt', $event.target.value);
        }, 750)
    }
}
</script>

<style lang="scss" scoped>
    .section-thumbnaill {
        padding-right: 0;
    }
    .thumbnail-buttons-row {
        bottom: -6px;
        left: 0.875em;
        position: absolute;
    }
    .thumbnail-field {
        display: flex;

        .field {
            margin-left: 1em;
            width: 100%;
        }
        .content {
            padding: 10px;
            font-size: 0.8em;
        }
        img {
            height: 120px;
            width: 120px;
            min-width: 120px;
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