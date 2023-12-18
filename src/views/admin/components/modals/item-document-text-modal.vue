<template>
    <form class="tainacan-modal-content tainacan-form">
        <div class="tainacan-modal-title">
            <h2 id="item-document-text-modal-title">{{ $i18n.get('instruction_write_text') }}</h2>
            <hr>
        </div>
        <b-input
                ref="item-document-text-input"
                v-model="localTextContent"
                aria-labelledby="item-document-text-modal-title"
                type="textarea"
                :autofocus="true" />

        <div class="field is-grouped form-submit">
            <div class="control">
                <button
                        id="button-cancel-text-content-writing"
                        class="button is-outlined"
                        type="button"
                        @click="$emit('cancelTextWriting');$emit('close');">
                    {{ $i18n.get('cancel') }}</button>
            </div>
            <div class="control">
                <button
                        id="button-submit-text-content-writing"
                        type="submit"
                        class="button is-success"
                        @click.prevent="confirmTextWriting();$emit('close');">
                    {{ $i18n.get('save') }}</button>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    props: {
        textContent: ''
    },
    emits: [
        'confirmTextWriting',
        'cancelTextWriting'
    ],
    data(){
        return {
            localTextContent: ''
        }
    },
    mounted() {
        this.localTextContent = this.textContent;

        if (
            this.$refs && 
            this.$refs['item-document-text-input'] &&
            this.$refs['item-document-text-input']['$el'] &&
            this.$refs['item-document-text-input']['$el'].children &&
            this.$refs['item-document-text-input']['$el'].children[0]
        ) {
            this.$refs['item-document-text-input']['$el'].children[0].focus();
            this.$refs['item-document-text-input']['$el'].children[0].click();
        }
    },
    methods: {
        confirmTextWriting() {
            this.$emit('confirmTextWriting', this.localTextContent);
        }
    }
}
</script>