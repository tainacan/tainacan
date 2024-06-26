<template>
    <div 
            ref="itemCopyDialog"
            aria-labelledby="alert-dialog-title"
            autofocus
            role="alertdialog"
            tabindex="-1"
            aria-modal
            class="tainacan-form tainacan-dialog dialog">
        <div    
                class="modal-card" 
                style="width: auto">
            <div 
                    v-if="icon != undefined && icon != ''"
                    class="modal-custom-icon">
                <span class="icon is-large">
                    <i 
                            :class="'tainacan-icon-' + icon"
                            class="tainacan-icon" />
                </span>
            </div>
            <section 
                    tabindex="1"
                    class="modal-card-body">
                <header 
                        class="modal-card-head">
                    <h1 
                            id="alert-dialog-title"
                            class="modal-card-title">
                        {{ $i18n.get('label_make_copies_of_item') }}
                    </h1>
                </header>
                {{ message }}
                <div
                        v-if="!hasCopied && !isLoading"
                        class="tainacan-form">
                    <b-field 
                            horizontal
                            :label="$i18n.get('label_number_of_copies') + ':'">
                        <b-numberinput
                                ref="copy-count-numerbinput"
                                :aria-minus-label="$i18n.get('label_decrease')"
                                :aria-plus-label="$i18n.get('label_increase')"
                                min="1" 
                                :model-value="copyCount"
                                step="1"
                                @update:model-value="copyCount = $event" />
                    </b-field>
                </div>
            </section>
            <footer class="modal-card-foot form-submit">
                <button 
                        type="submit"
                        class="button is-outlined"
                        :disabled="isLoading"
                        @click="onConfirm(newItems); $emit('close');">
                    {{ hasCopied ? $i18n.get('label_return_to_list') : $i18n.get('cancel') }}
                </button>
                <button 
                        v-if="!hasCopied"
                        :class="{'is-loading': isLoading, 'is-success': !isLoading }"
                        type="submit"
                        class="button"
                        :disabled="copyCount <= 0 || isNaN(copyCount)"
                        @click="generateCopies();">
                    {{ $i18n.get('run') }}
                </button>
                <button 
                        v-if="copyCount == 1 && hasCopied && newItems.length == 1"
                        class="button is-secondary" 
                        type="submit"
                        @click.prevent="$router.push($routerHelper.getItemEditPath(collectionId, newItems[0].id)); $emit('close');">
                    {{ $i18n.getFrom('items','edit_item') }}
                </button>
                <button 
                        v-if="copyCount > 1 && hasCopied && newItems.length > 1"
                        style="margin-left: 24px;"
                        class="button is-secondary" 
                        :class="{'is-loading': isCreatingSequenceEditGroup }"
                        type="submit"
                        @click.prevent="sequenceEditGroup()">
                    {{ $i18n.get('label_sequence_edit_items') }}
                </button>
                <button 
                        v-if="copyCount > 1 && hasCopied && newItems.length > 1"
                        class="button is-secondary"
                        type="submit"
                        @click.prevent="openBulkEditionModal()">
                    {{ $i18n.get('label_bulk_edit_items') }}
                </button>
            </footer>
        </div>
    </div>
</template>

<script>
    import { mapActions } from 'vuex';
    import BulkEditionModal from '../modals/bulk-edition-modal.vue';

    export default {
        name: 'ItemCopyDialog',
        props: {
            icon: String,
            onConfirm: {
                type: Function,
                default: () => {}
            },
            collectionId: [String, Number],
            itemId: String
        },
        emits: [
            'close'
        ],
        data() {
            return {
                isLoading: Boolean,
                message: String,
                newItems: Array,
                copyCount: Number,
                hasCopied: Boolean,
                isCreatingSequenceEditGroup: Boolean,
            }
        },
        created() {
            this.message = this.$i18n.get('instruction_select_the_amount_of_copies');
            this.isLoading = false;
            this.hasCopied = false;
            this.isCreatingSequenceEditGroup = false;
            this.copyCount = 1;
        },
        mounted() {
            if (this.$refs.itemCopyDialog)
            this.$refs.itemCopyDialog.focus();
        },
        methods: {
            ...mapActions('item', [
                'duplicateItem'
            ]),
            ...mapActions('bulkedition', [
                'createEditGroup',
                'createSequenceEditGroup'
            ]),
            generateCopies() {
                this.isLoading = true;
                this.message = this.copyCount > 1 ? this.$i18n.get('info_await_while_item_copies') : this.$i18n.get('info_await_while_item_copy');

                this.duplicateItem({ 
                        collectionId: this.collectionId, 
                        itemId: this.itemId,
                        copies: this.copyCount })
                    .then((newItems) => {
                        this.isLoading = false;
                        this.hasCopied = true;
                        this.message = newItems.length > 1 ? this.$i18n.getWithVariables('label_%s_items_copy_success', [this.copyCount]) : this.$i18n.get('label_one_item_copy_success');
                        
                        this.newItems = newItems;
                    })
                    .catch((error) => {
                        this.$console.error('Error fetching item for copy ' + error);
                        this.isLoading = false;
                        this.message = this.$i18n.get('label_item_copy_failure'); 
                    });
            },
            sequenceEditGroup() {
                let onlyItemIds = this.newItems.map(item => item.id);

                this.isCreatingSequenceEditGroup = true;
                this.createSequenceEditGroup({
                    object: onlyItemIds,
                    collectionId: this.collectionId
                }).then((group) => {
                    let sequenceId = group.id;
                    this.isCreatingSequenceEditGroup = false;
                    this.$router.push(this.$routerHelper.getCollectionSequenceEditPath(this.collectionId, sequenceId, 1));
                    this.$emit('close');
                });
            },
            openBulkEditionModal() {

                let onlyItemIds = this.newItems.map(item => item.id);

                this.$buefy.modal.open({
                    parent: this,
                    component: BulkEditionModal,
                    props: {
                        modalTitle: this.$i18n.get('info_editing_items_in_bulk'),
                        totalItems: onlyItemIds.length,
                        selectedForBulk: onlyItemIds,
                        objectType: this.$i18n.get('items'),
                        collectionId: this.collectionId
                    },
                    width: 'calc(100% - (2 * var(--tainacan-one-column)))',
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    closeButtonAriaLabel: this.$i18n.get('close')
                }); 

                this.$emit('close');
            },
        }
    }
</script>

<style scoped>
   
    i.tainacan-icon,
    i.tainacan-icon::before {
        font-size: 42px;
    }

    button.is-success {
        margin-left: auto;
    }

    .field.is-horizontal {
        margin-top: 8px !important;
        align-items: flex-end;
    }

    .field.is-horizontal :deep(.label) {
        white-space: nowrap;
    }

    .field.is-horizontal :deep(.has-numberinput) {
        margin-top: 0;
    }

    .modal-card-foot {
        margin-top: 12px;
    }

    @media screen and (min-width: 769px) { 
        .modal-card {
            max-width: 640px;
        }
    }

</style>

