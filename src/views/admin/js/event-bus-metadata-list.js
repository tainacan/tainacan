import { createApp } from 'vue';
import mitt from 'mitt';

const emitter = mitt();

export default {

    install(app, options = {}) {
        app.config.globalProperties.$emitter = emitter;
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