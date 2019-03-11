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
                            :style="{ color: icon == 'alert' ? '#a23939' : ( icon == 'approved' ? '#25a189' : 'inherit' ) }"
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
                        {{ title }}
                    </h1>
                </header>
                {{ message }}
                <div v-if="showNeverShowAgainOption">
                    <b-checkbox
                            @input="changeNeverShowMessageAgain($event)"
                            :native-value="neverShowAgain">
                        {{ $i18n.get('instruction_never_show_message_again') }}
                    </b-checkbox>
                </div>
            </section>
            <footer class="modal-card-foot form-submit">
                <button 
                        v-if="!hideCancel"
                        class="button is-outlined" 
                        type="button"
                        @click="$parent.close()">
                    {{ $i18n.get('cancel') }}
                </button>
                <button 
                        type="submit"
                        class="button is-success"
                        @click="onConfirm(); $parent.close();">
                    {{ $i18n.get('continue') }}
                </button>
            </footer>
        </div>
    </div>
</template>

<script>

    export default {
        name: 'CustomDialog',
        props: {
            title: String,
            message: String,
            icon: String,
            onConfirm: {
                type: Function,
                default: () => {}
            },
            hideCancel: {
                type: Boolean,
                default: false,
            },
            showNeverShowAgainOption: {
                type: Boolean,
                default: false
            },
            messageKeyForUserPrefs: ''
        },
        data() {
            return {
                neverShowAgain: false
            }
        },
        methods: {
            changeNeverShowMessageAgain($event) {
                this.$userPrefs.set('neverShow' + this.messageKeyForUserPrefs + 'Dialog', $event);
            }
        }
    }
</script>

<style scoped>
   
    i.tainacan-icon,
    i.tainacan-icon::before {
        font-size: 56px;
    }

    button.is-success {
        margin-left: auto;
    }

    .b-checkbox.checkbox {
        margin-top: 12px;
    }

</style>

