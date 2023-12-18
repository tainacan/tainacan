<template>
    <div>
        <!-- Attachments -------------------------------- -->
        <div 
                v-if="!$adminOptions.hideItemEditionAttachments"
                class="section-label">
            <label>
                <span class="icon has-text-gray4">
                    <i class="tainacan-icon tainacan-icon-attachments" />
                </span>
                {{ collection && collection.item_attachment_label ? collection.item_attachment_label : $i18n.get('label_attachments') }}&nbsp;
                <span 
                        v-if="totalAttachments"
                        class="has-text-gray has-text-weight-normal"
                        style="font-size: 0.875em;">
                    ({{ totalAttachments }})
                </span>
            </label>
            <help-button
                    :title="collection && collection.item_attachment_label ? collection.item_attachment_label : $i18n.get('label_attachments')"
                    :message="$i18n.get('info_edit_attachments')" />
            <button
                    style="float: right; font-size: 0.875em; margin: 2px 5px;"
                    type="button"
                    class="link-style"
                    :disabled="isLoading"
                    @click.prevent="($event) => $emit('open-attachments-media-frame', $event)">
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-edit" />
                </span>
                {{ $i18n.get('label_add_or_update') }}
            </button>
        </div>
        <div 
                v-if="item != undefined && item.id != undefined && !$adminOptions.hideItemEditionAttachments"
                class="section-box section-attachments">

            <attachments-list
                    :item="item"
                    :form="form"
                    :collection="collection"
                    :is-editable="true"
                    :should-load-attachments="shouldLoadAttachments"
                    @on-delete-attachment="($event) => $emit('on-delete-attachment', $event)" />
        </div>
    </div>
</template>

<script>
import AttachmentsList from '../lists/attachments-list.vue';

export default {
    components: {
        AttachmentsList
    },
    props: {
        item: Object,
        form: Object,
        collection: Object,
        totalAttachments: Number,
        isLoading: Boolean,
        shouldLoadAttachments: Boolean
    },
    emits: [
        'open-attachments-media-frame',
        'on-delete-attachment'
    ]
}
</script>

<style lang="scss" scoped>
    .section-attachments {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
</style>