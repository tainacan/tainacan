export default {

    install(Vue, options = {}) {

        Vue.prototype.$eventBusMetadataList = new Vue({

            data: {
            },
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