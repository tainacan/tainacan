export default {

    install(Vue, options = {}) {

        Vue.prototype.$termsListBus = new Vue({

            data: {
            },
            methods: {
                onEditTerm(term) {
                    this.$emit('editTerm', term);
                },
                onTermEditionSaved(term) {
                    this.$emit('termEditionSaved', term);
                },
                onTermEditionCanceled(term) {
                    this.$emit('termEditionCanceled', term);
                },
                onAddNewChildTerm(parentId) {
                    this.$emit('addNewChildTerm', parentId);
                },
                onDeleteBasicTermItem(term) {
                    this.$emit('deleteBasicTermItem', term);
                }
            }
        });
    }
}