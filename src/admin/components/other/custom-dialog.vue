<template>
    <div class="tainacan-form dialog">
        <div    
                class="modal-card" 
                style="width: auto">
            <div 
                    v-if="icon != undefined && icon != ''"
                    class="modal-custom-icon">
                <component :is="icon + '-icon'"/>
            </div>
            <section 
                    class="modal-card-body">
                <header 
                        class="modal-card-head">
                    <h1 class="modal-card-title">{{ title }}</h1>
                </header>
                {{ message }}
            </section>
            <footer class="modal-card-foot form-submit">
                <button 
                        class="button is-outline" 
                        type="button"
                        @click="$parent.close()">
                    {{ $i18n.get('cancel') }}
                </button>
                <button 
                        class="button is-success"
                        @click="onConfirm(); $parent.close();">
                    {{ $i18n.get('continue') }}
                </button>
            </footer>
        </div>
    </div>
</template>
<script>
    import AlertIcon from './alert-icon.vue';
    import CheckIcon from './check-icon.vue';
    import QuestionIcon from './question-icon.vue';

    export default {
        name: 'CustomDialog',
        props: {
            title: String,
            message: String,
            icon: String,
            onConfirm: {
                type: Function,
                default: () => {}
            }
        },
        components: {
            AlertIcon,
            CheckIcon,
            QuestionIcon
        }
    }
</script>