import { createApp } from 'vue';

export default {

    install(app, options = {}) {

        app.config.globalProperties.$eventBusMetadataList = createApp({
            emits: [
                'addMetadatumViaButton',
                'addMetadataSectionViaButton'
            ],
            methods: {
                onAddMetadatumViaButton(metadataType) {
                    this.$emit('addMetadatumViaButton', metadataType);
                },
                onAddMetadataSectionViaButton() {
                    this.$emit('addMetadataSectionViaButton');
                }
            }
        });
    }
}