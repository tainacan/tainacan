<template>
    <div class="form-submission-footer">

        <!-- Item edition inside iframe -->
        <template v-if="isEditingItemMetadataInsideIframe">
            <button
                    type="button"
                    class="button is-secondary"
                    @click="$emit('on-submit')">
                {{ $i18n.get('label_back_to_related_item') }}
            </button>
        </template>

        <!-- Normal item edition -->
        <template v-else>

            <!-- Sequence edition Previous -->
            <button
                    v-if="isOnSequenceEdit && hasPreviousItemOnSequenceEdit"
                    type="button"
                    class="button sequence-button"
                    @click="$emit('on-prev-in-sequence')">
                <span class="icon is-large">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-previous" />
                </span>
                <span>{{ $i18n.get('previous') }}</span>
            </button>

            <!-- Item is an autodraft (item creation) -->
            <template v-if="(status == 'auto-draft' || status == undefined)">
                <button
                        v-if="!$adminOptions.mobileAppMode"
                        type="button"
                        class="button is-outlined"
                        @click="$emit('on-discard')">{{ $i18n.get('label_discard') }}</button>
                <button
                        type="button"
                        class="button is-secondary"
                        :style="{ marginLeft: $adminOptions.mobileAppMode ? 'auto' : '0.5em' }"
                        @click="openItemCreationStatusDialog">{{ $i18n.get('label_create_item') }}</button>
            </template>

            <!-- Item is public, draft or private -->
            <template v-else>

                <!-- Send items to Trash -->
                <button 
                        v-if="!isOnSequenceEdit && currentUserCanDelete && !$adminOptions.mobileAppMode"
                        type="button"
                        class="button is-outlined"
                        @click="$emit('on-submit', 'trash')">
                    <span v-if="!isMobileScreen">{{ $i18n.get('label_send_to_trash') }}</span>
                    <span v-else>{{ $i18n.get('status_trash') }}</span>
                </button>

                <!-- Update dropdown with -->
                <b-dropdown
                        v-if="!$adminOptions.hideItemEditionStatusOption && $adminOptions.itemEditionStatusOptionOnFooterDropdown"
                        ref="item-edition-footer-dropdown"
                        :triggers="['contextmenu']"
                        aria-role="list"
                        animation="item-appear"
                        :mobile-modal="false"
                        position="is-top-left"
                        class="item-edition-footer-dropdown"
                        :style="{ marginLeft: $adminOptions.mobileAppMode ? 'auto' : '0.5em' }">
                    <template #trigger>
                        <button 
                                :disabled="hasSomeError && (status == 'publish' || status == 'private' || status == 'pending')"
                                type="button"
                                class="button"
                                :class="{ 
                                    'is-success': status == 'publish' || status == 'private' || status == 'pending',
                                    'is-secondary': status == 'draft'
                                }"
                                @click="!$adminOptions.mobileAppMode && !isMobileScreen ? $emit(
                                    'on-submit',
                                    ( !$adminOptions.hideItemEditionStatusPublishOption ) ? status : 'draft',
                                    ( (isOnSequenceEdit && !isLastItemOnSequenceEdit) ? 'next' : null)
                                ) : ($refs && $refs['item-edition-footer-dropdown'] && !$refs['item-edition-footer-dropdown'].isActive ? $refs['item-edition-footer-dropdown'].toggle() : null)">
                            {{ $i18n.get('label_update') }}
                            <span 
                                    v-if="isOnSequenceEdit && !isLastItemOnSequenceEdit"
                                    class="icon is-large"
                                    style="margin-left: 0em;">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-next" />
                            </span>
                            <span 
                                    v-if="!$adminOptions.mobileAppMode"
                                    style="margin-left: 0.5em;"
                                    class="icon is-small"
                                    @mouseenter="$refs && $refs['item-edition-footer-dropdown'] && !$refs['item-edition-footer-dropdown'].isActive ? $refs['item-edition-footer-dropdown'].toggle() : null">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowup" />
                            </span>
                        </button>
                    </template>
                    <b-dropdown-item 
                            :class="{ 'is-forced-last-option': status == 'draft' }"
                            aria-role="listitem"
                            @click="$emit(
                                'on-submit',
                                'draft',
                                ( (isOnSequenceEdit && !isLastItemOnSequenceEdit) ? 'next' : null)
                            )">
                        <span class="icon has-text-gray4">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-draft" />
                        </span>
                        {{ status == 'draft' ? $i18n.get('label_update_draft') : $i18n.get('label_change_to_draft') }}
                        <br>
                        <small 
                                v-if="$statusHelper.hasDescription('draft')"
                                class="is-small">
                            {{ $statusHelper.getDescription('draft') }}
                        </small>
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="!$adminOptions.hideItemEditionStatusPendingOption"
                            :class="{ 'is-forced-last-option': status == 'pending' }"
                            aria-role="listitem"
                            @click="$emit(
                                'on-submit',
                                'pending',
                                ( (isOnSequenceEdit && !isLastItemOnSequenceEdit) ? 'next' : null)
                            )">
                        <span class="icon has-text-gray4">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-waiting" />
                        </span>
                        {{ status == 'pending' ? $i18n.get('label_update_pending') : $i18n.get('label_send_to_review') }}
                        <br>
                        <small 
                                v-if="$statusHelper.hasDescription('pending')"
                                class="is-small">
                            {{ $statusHelper.getDescription('pending') }}
                        </small>
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="currentUserCanPublish"
                            :class="{ 'is-forced-last-option': status == 'private' }"
                            aria-role="listitem"
                            @click="$emit(
                                'on-submit',
                                'private',
                                ( (isOnSequenceEdit && !isLastItemOnSequenceEdit) ? 'next' : null)
                            )">
                        <span class="icon has-text-gray4">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-private" />
                        </span>
                        {{ status == 'private' ? $i18n.get('label_update_as_private') : ( status == 'draft' ? $i18n.get('label_verb_publish_privately') : $i18n.get('label_change_to_private') ) }}
                        <br>
                        <small 
                                v-if="$statusHelper.hasDescription('private')"
                                class="is-small">
                            {{ $statusHelper.getDescription('private') }}
                        </small>
                    </b-dropdown-item>
                    <b-dropdown-item 
                            v-if="currentUserCanPublish && !$adminOptions.hideItemEditionStatusPublishOption"
                            aria-role="listitem"
                            @click="$emit(
                                'on-submit',
                                'publish',
                                ( (isOnSequenceEdit && !isLastItemOnSequenceEdit) ? 'next' : null)
                            )">
                        <span class="icon has-text-gray4">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-public" />
                        </span>
                        {{ status == 'publish' ? $i18n.get('label_update_as_public') : $i18n.get('label_verb_publish') }}
                        <br>
                        <small 
                                v-if="$statusHelper.hasDescription('publish')"
                                class="is-small">
                            {{ $statusHelper.getDescription('publish') }}
                        </small>
                    </b-dropdown-item>
                </b-dropdown>
                
                <!-- In case we do not want to show status, just an update button -->
                <button 
                        v-else-if="!isOnSequenceEdit"
                        :disabled="hasSomeError && (status == 'publish' || status == 'private' || status == 'pending')"
                        type="button"
                        class="button"
                        :class="{ 
                            'is-success': status == 'publish' || status == 'private' || status == 'pending',
                            'is-secondary': status == 'draft'
                        }"
                        @click="$emit('on-submit', status)">
                    {{ $i18n.get('finish') }}
                </button>

            </template>

            <!-- Sequence edition Next button if user cannot publish (only goes to next, without changing status) -->
            <button 
                    v-if="(!currentUserCanPublish || !$adminOptions.itemEditionStatusOptionOnFooterDropdown) && isOnSequenceEdit && hasNextItemOnSequenceEdit"
                    :disabled="(status == 'publish' || status == 'private' || status == 'pending') && hasSomeError"
                    type="button"
                    class="button is-success"
                    @click="$emit('on-next-in-sequence')">
                <span>{{ $i18n.get('next') }}</span>
                <span class="icon is-large">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-next" />
                </span>
            </button>

            <!-- Sequence edition Finish -->
            <button 
                    v-if="isOnSequenceEdit && isLastItemOnSequenceEdit"
                    type="button"
                    class="button sequence-button is-success"
                    @click="$router.push($routerHelper.getCollectionPath(collectionId))">
                <span class="icon is-large">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-approved" />
                </span>
                <span>{{ $i18n.get('finish') }}</span>
            </button>

        </template>

    </div>
</template>

<script>
import ItemCreationStatusDialog from '../other/item-creation-status-dialog.vue';

export default {
    props: {
        status: String,
        collectionId: [Number, String],
        isOnSequenceEdit: Boolean,
        isLastItemOnSequenceEdit: Boolean,
        hasNextItemOnSequenceEdit: Boolean,
        hasPreviousItemOnSequenceEdit: Boolean,
        isMobileScreen: Boolean,
        hasSomeError: Boolean,
        currentUserCanDelete: Boolean,
        currentUserCanPublish: Boolean,
        isEditingItemMetadataInsideIframe: Boolean
    },
    emits: [
        'on-submit',
        'on-next-in-sequence',
        'on-prev-in-sequence',
        'on-discard',
    ],
    mounted() {
        this.$emitter.on('toggleItemEditionFooterDropdown', () => {
            if (this.$refs && this.$refs['item-edition-footer-dropdown'])
                this.$refs['item-edition-footer-dropdown'].toggle();
        });
    },
    beforeUnmount() {
        this.$emitter.off('toggleItemEditionFooterDropdown');
    },
    methods: {
        openItemCreationStatusDialog() {

            this.$buefy.modal.open({
                parent: this,
                component: ItemCreationStatusDialog,
                canCancel: false,
                props: {
                    icon: 'item',
                    currentUserCanPublish: this.currentUserCanPublish,
                    onConfirm: (selectedStatus) => {
                        this.$emit('on-submit', selectedStatus);
                    }
                },
                trapFocus: true,
                customClass: 'tainacan-modal'
            });
        },
    }
}
</script>

<style lang="scss" scoped>
    .form-submission-footer {
        display: flex;
        flex-wrap: nowrap;
        
        .button {
            margin-left: 16px;
            margin-right: 6px;
        }
        .button:last-of-type {
            margin-right: 0px;
        }

        :deep(.item-edition-footer-dropdown) {
            .dropdown-trigger .button>.icon.is-small {
                border-left: 1px solid rgba(255,255,255,0.6);
                margin-left: 0.5em;
            }
            .dropdown-menu>.dropdown-content {
                display: flex;
                flex-direction: column;

                .dropdown-item.is-forced-last-option {
                    order: 99;
                }
            }
        }
    }

    @media screen and (max-width: 782px) {
        .form-submission-footer {
            display: flex;
            justify-content: space-between;
            width: 100%;

            .button {
                margin-left: 6px;
                margin-right: 6px;
            }
            .button:first-of-type {
                margin-left: 0px;
            }
            .button.is-success {
                margin-left: auto;
            }
        }
    }
</style>