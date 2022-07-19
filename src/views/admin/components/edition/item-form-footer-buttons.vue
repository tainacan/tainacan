<template>
    <div class="form-submission-footer">

        <!-- Item edition inside iframe -->
        <template v-if="isEditingItemMetadataInsideIframe">
            <button
                    @click="$emit('onSubmit')"
                    type="button"
                    class="button is-secondary">
                {{ $i18n.get('label_back_to_related_item') }}
            </button>
        </template>

        <!-- Item is on trash -->
        <template v-if="status == 'trash' && !isEditingItemMetadataInsideIframe">
            <button 
                    v-if="currentUserCanDelete"
                    @click="$emit('onDeletePermanently')"
                    type="button"
                    class="button is-outlined">{{ $i18n.get('label_delete_permanently') }}</button>
            <button
                    @click="$emit('onSubmit', 'draft')"
                    type="button"
                    class="button is-secondary">
                <span v-if="!isMobileScreen">{{ $i18n.get('label_save_as_draft') }}</span>
                <span v-else>{{ $i18n.get('status_draft') }}</span>
            </button>
            <button 
                    v-if="currentUserCanPublish"
                    @click="$emit('onSubmit', visibility)"
                    type="button"
                    class="button is-success">{{ $i18n.get('label_verb_publish') }}</button>
        </template>

        <!-- Item is an autodraft or draft -->
        <template v-if="(status == 'auto-draft' || status == 'draft' || status == undefined) && !isEditingItemMetadataInsideIframe">
            <button
                    v-if="isOnSequenceEdit && itemPosition > 1"
                    @click="$emit('onPrevInSequence')"
                    type="button"
                    class="button sequence-button">
                <span class="icon is-large">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-previous"/>
                </span>
                <span>{{ $i18n.get('previous') }}</span>
            </button>
            <button 
                    v-if="status == 'draft' && !isOnSequenceEdit && currentUserCanDelete"
                    @click="$emit('onSubmit', 'trash')"
                    type="button"
                    class="button is-outlined">
                <span v-if="!isMobileScreen">{{ $i18n.get('label_send_to_trash') }}</span>
                <span v-else>{{ $i18n.get('status_trash') }}</span>
            </button>
            <button
                    v-if="status == 'auto-draft'"
                    @click="$emit('onDiscard')"
                    type="button"
                    class="button is-outlined">{{ $i18n.get('label_discard') }}</button>
            <button
                    v-if="!isOnSequenceEdit || (group != null && group.items_count != undefined && group.items_count == itemPosition)"
                    @click="$emit('onSubmit', 'draft')"
                    type="button"
                    class="button is-secondary">{{ status == 'draft' ? $i18n.get('label_update') : $i18n.get('label_save_as_draft') }}</button>
            <button
                    v-else
                    @click="$emit('onSubmit', 'draft'); $emit('onNextInSequence');"
                    type="button"
                    class="button is-secondary">
                <span v-if="!isMobileScreen">{{ $i18n.get('label_update_draft') }}</span>
                <span v-else>{{ $i18n.get('status_draft') }}</span>
                <span class="icon is-large">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-next"/>
                </span>
            </button>
            <template v-if="currentUserCanPublish">
                <button 
                        v-if="!isOnSequenceEdit || (group != null && group.items_count != undefined && group.items_count == itemPosition)"
                        @click="$emit('onSubmit', visibility)"
                        type="button"
                        class="button is-success">{{ !$adminOptions.hideItemEditionStatusPublishOption ? $i18n.get('label_verb_publish') : $i18n.get('label_verb_publish_privately') }}</button>
                <button 
                        v-else
                        @click="$emit('onSubmit', visibility, 'next')"
                        type="button"
                        class="button is-success">
                    <span>{{ !$adminOptions.hideItemEditionStatusPublishOption ? $i18n.get('label_verb_publish') : $i18n.get('label_verb_publish_privately') }}</span>
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-next"/>
                    </span>
                </button>
            </template>
            <template v-else>
                <button 
                        v-if="isOnSequenceEdit && (group != null && group.items_count != undefined && group.items_count < itemPosition)"
                        @click="$emit('onNextInSequence')"
                        type="button"
                        class="button is-success">
                    <span>{{ $i18n.get('label_next') }}</span>
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-next"/>
                    </span>
                </button>
            </template>
            <button 
                    v-if="isOnSequenceEdit && (group != null && group.items_count != undefined && group.items_count == itemPosition)"
                    @click="$router.push($routerHelper.getCollectionPath(collectionId))"
                    type="button"
                    class="button sequence-button">
                <span class="icon is-large">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-approved"/>
                </span>
                <span>{{ $i18n.get('finish') }}</span>
            </button>
        </template>

        <!-- Item is published as public or private -->
        <template v-if="(status == 'publish' || status == 'private') && !isEditingItemMetadataInsideIframe">
            <button
                    v-if="isOnSequenceEdit && itemPosition > 1"
                    @click="$emit('onPrevInSequence')"
                    type="button"
                    class="button sequence-button">
                <span class="icon is-large">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-previous"/>
                </span>
                <span>{{ $i18n.get('previous') }}</span>
            </button>
            <button 
                    v-if="!isOnSequenceEdit && currentUserCanDelete"
                    @click="$emit('onSubmit', 'trash')"
                    type="button"
                    class="button is-outlined">
                <span v-if="!isMobileScreen">{{ $i18n.get('label_send_to_trash') }}</span>
                <span v-else>{{ $i18n.get('status_trash') }}</span>
            </button>
            <button
                    v-if="!isOnSequenceEdit || (group != null && group.items_count != undefined && group.items_count == itemPosition)"
                    @click="$emit('onSubmit', 'draft')"
                    type="button"
                    class="button is-secondary">
                <span v-if="!isMobileScreen">{{ isOnSequenceEdit ? $i18n.get('label_save_as_draft') : $i18n.get('label_return_to_draft') }}</span>
                <span v-else>{{ $i18n.get('status_draft') }}</span>
            </button>
            <button
                    v-else
                    @click="$emit('onSubmit', 'draft', 'next')"
                    type="button"
                    class="button is-secondary">
                <span>{{ $i18n.get('label_save_as_draft') }}</span>
                <span class="icon is-large">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-next"/>
                </span>
            </button>
            <template v-if="currentUserCanPublish">
                <button 
                        v-if="!isOnSequenceEdit || (group != null && group.items_count != undefined && group.items_count == itemPosition)"
                        :disabled="hasSomeError"
                        @click="$emit('onSubmit', visibility)"
                        type="button"
                        class="button is-success">{{ $i18n.get('label_update') }}</button>
                <button 
                        v-else
                        :disabled="hasSomeError"
                        @click="$emit('onSubmit', visibility, 'next')"
                        type="button"
                        class="button is-success">
                    <span>{{ $i18n.get('label_update') }}</span>
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-next"/>
                    </span>
                </button>
            </template>
            <template v-else>
                <button 
                        v-if="isOnSequenceEdit && (group != null && group.items_count != undefined && group.items_count < itemPosition)"
                        :disabled="hasSomeError"
                        @click="$emit('onNextInSequence')"
                        type="button"
                        class="button is-success">
                    <span>{{ $i18n.get('label_next') }}</span>
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-next"/>
                    </span>
                </button>
            </template>
            <button 
                    v-if="isOnSequenceEdit && (group != null && group.items_count != undefined && group.items_count == itemPosition)"
                    @click="$router.push($routerHelper.getCollectionPath(collectionId))"
                    type="button"
                    class="button sequence-button">
                <span class="icon is-large">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-approved"/>
                </span>
                <span>{{ $i18n.get('finish') }}</span>
            </button>
        </template>

    </div>
</template>

<script>
export default {
    props: {
        status: String,
        collectionId: Number,
        isOnSequenceEdit: Boolean,
        group: Object,
        itemPosition: Number,
        isMobileScreen: Boolean,
        hasSomeError: Boolean,
        currentUserCanDelete: Boolean,
        currentUserCanPublish: Boolean,
        isEditingItemMetadataInsideIframe: Boolean,
        visibility: String
    }
}
</script>
