<template>
    <div 
            aria-labelledby="alert-dialog-title"
            aria-modal="true"
            role="alertdialog"
            class="tainacan-form dialog">
        <div    
                class="modal-card" 
                style="width: auto">
            <div 
                    v-if="icon != undefined && icon != ''"
                    class="modal-custom-icon">
                <span class="icon is-large">
                    <i 
                            :class="'tainacan-icon-' + icon"
                            class="tainacan-icon"/>
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
                        {{ $i18n.get('label_duplicating_item') }}
                    </h1>
                </header>
                {{ message }}
            </section>
            <footer class="modal-card-foot form-submit">
                <button 
                        type="submit"
                        class="button is-outlined"
                        :disabled="isLoading"
                        @click="onConfirm(duplicatedItemId); $parent.close();">
                    {{ $i18n.get('label_items_list') }}
                </button>
                <button 
                        type="submit"
                        class="button is-success"
                        :disabled="isLoading"
                        @click="$router.push($routerHelper.getItemEditPath(collectionId, duplicatedItemId)); $parent.close();">
                    {{ $i18n.getFrom('items','edit_item') }}
                </button>
            </footer>
        </div>
    </div>
</template>

<script>
    import { mapActions } from 'vuex';
    export default {
        name: 'DuplicationDialog',
        props: {
            icon: String,
            onConfirm: {
                type: Function,
                default: () => {}
            },
            collectionId: String,
            itemId: String
        },
        data() {
            return {
                isLoading: Boolean,
                message: String,
                duplicatedItemId: String
            }
        },
        methods: {
            ...mapActions('item', [
                'fetchItem',
                'duplicateItem'
            ]),
        },
        created() {
            this.isLoading = true;
            this.message = this.$i18n.get('info_await_while_item_duplication');

            this.duplicateItem({ collectionId: this.collectionId, itemId: this.itemId })
                .then((duplicatedItem) => {
                    this.isLoading = false;
                    this.message = this.$i18n.get('label_item_duplication_success');
                    
                    if (duplicatedItem.id)
                        this.duplicatedItemId = duplicatedItem.id; 
                })
                .catch((error) => {
                    this.$console.error('Error fetching item for duplicate ' + error);
                    this.isLoading = false;
                    this.message = this.$i18n.get('label_item_duplication_failure'); 
                });
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

    .modal-card-foot {
        margin-top: 12px;
    }

</style>

