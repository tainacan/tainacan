export default {

    install(Vue, options = {}) {

        Vue.prototype.$termsListBus = new Vue({

            data: {
            },
            methods: {
                onEditTerm(term) {
                    this.$emit('editTerm', term);
                },
                onTermEditionSaved({term, hasChangedParent}) {
                    this.$emit('termEditionSaved', { term: term, hasChangedParent: hasChangedParent });
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