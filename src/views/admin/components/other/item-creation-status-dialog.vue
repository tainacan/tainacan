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
                            v-for="(statusOption, index) of availableStatus"
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
            currentUserCanPublish: Boolean,
            onConfirm: {
                type: Function,
                default: () => {}
            }
        },
        data() {
            return {
                selectedStatus: !this.$adminOptions.hideItemEditionStatusPublishOption ? 'publish' : 'private'
            }
        },
        computed: {
            availableStatus() {
                return this.$statusHelper.getStatuses().filter((status) => {
                    if (    
                        status.slug != 'trash' &&
                        ( ( this.currentUserCanPublish && !this.$adminOptions.hideItemEditionStatusPublishOption ) || status.slug != 'publish' )
                    )
                        return true;

                    return false;
                });
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

    @media screen and (max-width: 768px) {
        .modal-card {
            padding: 2rem 0.875rem 0.875rem;
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
        }
        .modal-custom-icon {
            display: none !important;
        }
        .modal-card-body {
            padding: 0 1.25rem !important;
        } 
        .status-radios {
            flex-wrap: wrap;
        }  
        .status-radios .b-radio {
            margin-bottom: 0.5rem !important;
            font-size: 1.125rem;
            padding-bottom: 0.5rem;
        }
        .status-radios .b-radio::not(:last-child) {
            border-bottom: 1px solid var(--tainacan-gray2);
        }
    }

</style>

