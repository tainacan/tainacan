<template>
    <div 
            aria-labelledby="alert-dialog-title"
            autofocus
            role="alertdialog"
            tabindex="-1"
            aria-modal
            class="tainacan-form tainacan-dialog dialog"
            ref="itemCreationStatusDialog">
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
                        {{ $i18n.get('label_ready_to_create_item') }}
                    </h1>
                </header>
                {{ $i18n.get('instruction_create_item_select_status') }}

                <!-- Status -------------------------------- --> 
                <div class="status-radios">
                    <b-radio
                            v-model="selectedStatus"
                            v-for="(statusOption, index) of $statusHelper.getStatuses().filter((status) => status.slug != 'trash')"
                            :key="index"
                            :native-value="statusOption.slug">
                        <span class="icon has-text-gray">
                            <i 
                                class="tainacan-icon tainacan-icon-18px"
                                :class="$statusHelper.getIcon(statusOption.slug)"/>
                        </span>
                        {{ statusOption.name }}
                    </b-radio>
                </div>
            </section>
            <footer class="modal-card-foot form-submit">
                <button 
                        type="button"
                        style="margin-right: auto"
                        class="button is-outlined"
                        @click="$parent.close();">
                    {{ $i18n.get('cancel') }}
                </button>
                <button 
                        type="submit"
                        class="button is-success"
                        @click="onConfirm(selectedStatus); $parent.close();">
                    {{ $i18n.get('label_create_item') }}
                </button>
            </footer>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'ItemCreationStatusDialog',
        props: {
            icon: String,
            onConfirm: {
                type: Function,
                default: () => {}
            }
        },
        data() {
            return {
                selectedStatus: 'publish'
            }
        },
        mounted() {
            if (this.$refs.itemCreationStatusDialog)
                this.$refs.itemCreationStatusDialog.focus();
        }
    }
</script>

<style scoped>
    
    .modal-card {
        max-width: 600px;
    }

    .modal-custom-icon i.tainacan-icon,
    .modal-custom-icon i.tainacan-icon::before {
        font-size: 42px;
    }

    button.is-success {
        margin-left: auto;
    }

    .field.is-horizontal {
        margin-top: 8px !important;
        align-items: flex-end;
    }
    .status-radios {
        margin-top: 1rem;
        display: flex;
        font-size: 1.125em;
    }
    .status-radios /deep/ .b-radio {
        margin-bottom: 0px !important;
    }
    .modal-card-foot {
        margin-top: 12px;
    }

</style>

