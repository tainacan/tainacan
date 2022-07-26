<template>
    <div>
        <!-- Attachments -------------------------------- -->
        <div 
                v-if="!$adminOptions.hideItemEditionAttachments"
                class="section-label">
            <label>
                <span class="icon has-text-gray4">
                    <i class="tainacan-icon tainacan-icon-attachments"/>
                </span>
                {{ $i18n.get('label_attachments') }}&nbsp;
                <span 
                        v-if="totalAttachments"
                        class="has-text-gray has-text-weight-normal"
                        style="font-size: 0.875em;">
                    ({{ totalAttachments }})
                </span>
            </label>
            <help-button
                    :title="$i18n.get('label_attachments')"
                    :message="$i18n.get('info_edit_attachments')"/>
            <button
                    style="float: right; font-size: 0.875em; margin: 2px 5px;"
                    type="button"
                    class="link-style"
                    @click.prevent="($event) => $emit('openAttachmentsMediaFrame', $event)"
                    :disabled="isLoading">
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-edit"/>
                </span>
                {{ $i18n.get('label_add_or_update_attachments') }}
            </button>
        </div>
        <div 
                v-if="item != undefined && item.id != undefined && !$adminOptions.hideItemEditionAttachments"
                class="section-box section-attachments">

            <attachments-list
                    :item="item"
                    :form="form"
                    :is-editable="true"
                    :should-load-attachments="shouldLoadAttachments"
                    @onDeleteAttachment="($event) => $emit('onDeleteAttachment', $event)"/>
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
        totalAttachments: Number,
        isLoading: Boolean,
        shouldLoadAttachments: Boolean
    }
}
</script>

<style lang="scss" scoped>
    .section-attachments {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
</style>