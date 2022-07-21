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

        <!-- Normal item edition -->
        <template v-else>

            <!-- Sequence edition Previous -->
            <button
                    v-if="isOnSequenceEdit && hasPreviousItemOnSequenceEdit"
                    @click="$emit('onPrevInSequence')"
                    type="button"
                    class="button sequence-button">
                <span class="icon is-large">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-previous"/>
                </span>
                <span>{{ $i18n.get('previous') }}</span>
            </button>

            <!-- Item is an autodraft (item creation) -->
            <template v-if="(status == 'auto-draft' || status == undefined)">
                <button
                        @click="$emit('onDiscard')"
                        type="button"
                        class="button is-outlined">{{ $i18n.get('label_discard') }}</button>
                <button
                        @click="openItemCreationStatusDialog"
                        type="button"
                        class="button is-secondary">{{ $i18n.get('label_create_item') }}</button>
            </template>

            <!-- Item is public, draft or private -->
            <template v-else>
                <button 
                        v-if="!isOnSequenceEdit && currentUserCanDelete"
                        @click="$emit('onSubmit', 'trash')"
                        type="button"
                        class="button is-outlined">
                    <span v-if="!isMobileScreen">{{ $i18n.get('label_send_to_trash') }}</span>
                    <span v-else>{{ $i18n.get('status_trash') }}</span>
                </button>

                <b-dropdown
                        ref="item-edition-footer-dropdown"
                        :triggers="['contextmenu']"
                        aria-role="list"
                        animation="item-appear"
                        :mobile-modal="false"
                        position="is-top-left"
                        class="item-edition-footer-dropdown">
                    <template #trigger>
                        <button 
                                :disabled="hasSomeError && (status == 'publish' || status == 'private')"
                                @click="!$adminOptions.mobileAppMode ? $emit(
                                    'onSubmit',
                                    ( currentUserCanPublish && !$adminOptions.hideItemEditionStatusPublishOption ) ? status : 'draft',
                                    ( (isOnSequenceEdit && !isCurrentItemOnSequenceEdit) ? 'next' : null)
                                ) : ($refs && $refs['item-edition-footer-dropdown'] && !$refs['item-edition-footer-dropdown'].isActive ? $refs['item-edition-footer-dropdown'].toggle() : null)"
                                type="button"
                                class="button"
                                :class="{ 
                                    'is-success': status == 'publish' || status == 'private',
                                    'is-secondary': status == 'draft'
                                }">
                            {{ $i18n.get('label_update') }}
                            <span 
                                    v-if="isOnSequenceEdit && !isCurrentItemOnSequenceEdit"
                                    class="icon is-large"
                                    style="margin-left: 0em;">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-next"/>
                            </span>
                            <span 
                                    v-if="!$adminOptions.mobileAppMode"
                                    @mouseenter="$refs && $refs['item-edition-footer-dropdown'] && !$refs['item-edition-footer-dropdown'].isActive ? $refs['item-edition-footer-dropdown'].toggle() : null"
                                    style="margin-left: 0.5em;"
                                    class="icon is-small">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowup" />
                            </span>
                        </button>
                    </template>
                    <b-dropdown-item 
                           @click="$emit(
                                'onSubmit',
                                'draft',
                                ( (isOnSequenceEdit && !isCurrentItemOnSequenceEdit) ? 'next' : null)
                            )"
                            :class="{ 'is-forced-last-option': status == 'draft' }"
                            aria-role="listitem">
                        <span class="icon has-text-gray4">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-draft"/>
                        </span>
                        {{ status == 'draft' ? $i18n.get('label_update_draft') : $i18n.get('label_change_to_draft') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="currentUserCanPublish && !$adminOptions.hideItemEditionStatusPublishOption"
                            @click="$emit(
                                'onSubmit',
                                'private',
                                ( (isOnSequenceEdit && !isCurrentItemOnSequenceEdit) ? 'next' : null)
                            )"
                            :class="{ 'is-forced-last-option': status == 'private' }"
                            aria-role="listitem">
                        <span class="icon has-text-gray4">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-private"/>
                        </span>
                        {{ status == 'private' ? $i18n.get('label_update_as_private') : ( status == 'draft' ? $i18n.get('label_verb_publish_privately') : $i18n.get('label_change_to_private') ) }}
                    </b-dropdown-item>
                    <b-dropdown-item 
                            v-if="currentUserCanPublish && !$adminOptions.hideItemEditionStatusPublishOption"
                            @click="$emit(
                                'onSubmit',
                                'publish',
                                ( (isOnSequenceEdit && !isCurrentItemOnSequenceEdit) ? 'next' : null)
                            )"
                            aria-role="listitem">
                        <span class="icon has-text-gray4">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-public"/>
                        </span>
                        {{ status == 'publish' ? $i18n.get('label_update_as_public') : $i18n.get('label_verb_publish') }}
                    </b-dropdown-item>
                </b-dropdown>

            </template>

            <!-- Sequence edition Next button if user cannot publish (only goes to next, without changing status) -->
            <button 
                    v-if="!currentUserCanPublish && isOnSequenceEdit && hasNextItemOnSequenceEdit"
                    :disabled="(status == 'publish' || status == 'private') && hasSomeError"
                    @click="$emit('onNextInSequence')"
                    type="button"
                    class="button is-success">
                <span>{{ $i18n.get('label_next') }}</span>
                <span class="icon is-large">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-next"/>
                </span>
            </button>

            <!-- Sequence edition Finish -->
            <button 
                    v-if="isOnSequenceEdit && isCurrentItemOnSequenceEdit"
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
import ItemCreationStatusDialog from '../other/item-creation-status-dialog.vue';

export default {
    props: {
        status: String,
        collectionId: Number,
        isOnSequenceEdit: Boolean,
        isCurrentItemOnSequenceEdit: Boolean,
        hasNextItemOnSequenceEdit: Boolean,
        hasPreviousItemOnSequenceEdit: Boolean,
        isMobileScreen: Boolean,
        hasSomeError: Boolean,
        currentUserCanDelete: Boolean,
        currentUserCanPublish: Boolean,
        isEditingItemMetadataInsideIframe: Boolean,
        visibility: String
    },
    mounted() {
        this.$parent.$on('toggleItemEditionFooterDropdown', () => {
            if (this.$refs && this.$refs['item-edition-footer-dropdown'])
                this.$refs['item-edition-footer-dropdown'].toggle();
        });
    },
    beforeDestroy() {
        this.$parent.$off('toggleItemEditionFooterDropdown');
    },
    methods: {
        openItemCreationStatusDialog() {

            this.$buefy.modal.open({
                parent: this,
                component: ItemCreationStatusDialog,
                canCancel: false,
                props: {
                    icon: 'item',
                    onConfirm: (selectedStatus) => {
                        this.$emit('onSubmit', selectedStatus);
                    }
                },
                trapFocus: true,
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close')
            });
        },
    }
}
</script>

<style lang="scss" scoped>
    .form-submission-footer {
        .button {
            margin-left: 16px;
            margin-right: 6px;
        }
        .button:last-of-type {
            margin-right: 0px;
        }

        /deep/ .item-edition-footer-dropdown {
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