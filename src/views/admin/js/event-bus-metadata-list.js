import { createApp } from 'vue';

export default {

    install(app, options = {}) {

        app.config.globalProperties.$eventBusMetadataList = new createApp({
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