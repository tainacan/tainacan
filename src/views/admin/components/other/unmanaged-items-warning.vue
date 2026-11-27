<template>
    <div
            v-for="warning of warnings"
            :key="warning.collectionId"
            class="unmanaged-status-banner"
            role="status">
        <p>{{ warning.message }}</p>
        <button
                v-if="warning.canRecover && !busyCollectionIds.includes(warning.collectionId)"
                type="button"
                class="button"
                @click="recover(warning)">
            {{ $i18n.get('label_move_unmanaged_items_to_draft') }}
        </button>
    </div>
</template>

<script>
import axios from '../../js/axios/axios';
import { mapGetters } from 'vuex';
import CustomDialog from './custom-dialog.vue';

export default {
    name: 'UnmanagedItemsWarning',
    data() {
        return {
            busyCollectionIds: []
        };
    },
    computed: {
        ...mapGetters('collection', {
            collection: 'getCollection'
        }),
        warnings() {
            return (this.collection ? [this.collection] : [])
                .filter((collection) => {
                    return collection
                        && collection.current_user_can_edit_items
                        && this.$statusHelper.getUnmanagedItemsCount(collection.total_items) > 0;
                })
                .map((collection) => {
                    return {
                        collectionId: collection.id,
                        canRecover: !!collection.current_user_can_bulk_edit,
                        message: this.unmanagedMessage(collection.total_items)
                    };
                });
        }
    },
    methods: {
        recover(warning) {
            if (!warning.canRecover || this.busyCollectionIds.includes(warning.collectionId))
                return;

            const modalTrigger = this.$modalFocusA11y.captureTrigger();
            this.$buefy.modal.open({
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: warning.message + ' ' + this.$i18n.get('info_confirm_move_unmanaged_items_to_draft'),
                    onConfirm: () => {
                        if (this.busyCollectionIds.includes(warning.collectionId))
                            return;

                        this.busyCollectionIds.push(warning.collectionId);
                        axios.tainacanApi.post('/collection/' + warning.collectionId + '/bulk-edit/recover-unmanaged-status')
                            .then((response) => {
                                const data = response && response.data ? response.data : {};
                                if (!data.id) {
                                    this.busyCollectionIds = this.busyCollectionIds.filter((id) => id !== warning.collectionId);
                                    return;
                                }

                                this.$buefy.snackbar.open({
                                    message: this.$i18n.get('info_bulk_edit_process_added'),
                                    type: 'is-primary',
                                    duration: 4000,
                                    actionText: this.$i18n.get('label_view_processes'),
                                    onAction: () => {
                                        this.$router.push(this.$routerHelper.getProcessesPath());
                                    }
                                });
                            })
                            .catch((error) => {
                                this.busyCollectionIds = this.busyCollectionIds.filter((id) => id !== warning.collectionId);
                                const data = error && error.response && error.response.data;
                                if (data && data.error_message && this.$showApiError)
                                    this.$showApiError(error, data.error_message);
                            });
                    }
                },
                trapFocus: true,
                customClass: 'tainacan-modal',
                canCancel: ['escape', 'outside'],
                events: {
                    beforeClose: () => this.$modalFocusA11y.restoreFocus(modalTrigger, this)
                }
            });
        },
        unmanagedMessage(totalItems) {
            const entries = this.$statusHelper.getUnmanagedItemStatuses(totalItems);
            const count = entries.reduce((sum, entry) => sum + entry.count, 0);
            const slugs = entries
                .map((entry) => '`' + this.$htmlSanitizer.sanitize(entry.slug) + '`')
                .join(', ');

            if (count === 1)
                return this.$i18n.getWithVariables('info_unmanaged_item_status_one_%s', [slugs]);

            return this.$i18n.getWithVariables('info_unmanaged_item_status_%1$s_%2$s', [String(count), slugs]);
        }
    }
}
</script>

<style lang="scss" scoped>
    .unmanaged-status-banner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin: 6px var(--tainacan-one-column);
        border-radius: 3px;
        padding: 4px 12px;
        color: var(--tainacan-yellow2);
        background: var(--tainacan-yellow1);

        p {
            margin: 0;
            font-size: 0.885em;
        }

        .button,
        .button:hover,
        .button:active,
        .button:focus {
            background: none !important;
            color: var(--tainacan-yellow2) !important;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
    }
</style>
