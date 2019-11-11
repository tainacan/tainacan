import Vue from 'vue';
import store from './store/store'

export const eventBus = new Vue({
    store,
    data: {
        errors : []
    },
    created() {
        this.$on('input', this.updateValue);
    },
    watch: {
        errors() {
            this.$emit('hasErrorsOnForm', this.errors.length > 0);
        }
    },
    methods : {
        updateValue({ itemId, metadatumId, values}){
            
            this.$emit('isUpdatingValue', true);

            if (itemId) {

                if (values.length > 0 && values[0].value) {
                    let onlyValues = values.map((aValueObject) => aValueObject.value);
                    values = onlyValues;
                }

                this.$store.dispatch('item/updateMetadata', { 
                    item_id: itemId, 
                    metadatum_id: metadatumId, 
                    values: Array.isArray(values[0]) ? values[0] : values
                })
                    .then(() => {
                        this.$emit('isUpdatingValue', false);
                        let index = this.errors.findIndex( errorItem => errorItem.metadatum_id == metadatumId );
                        if (index >= 0)
                            this.errors.splice( index, 1);
                    })
                    .catch((error) => {
                        this.$emit('isUpdatingValue', false);
                        let index = this.errors.findIndex( errorItem => errorItem.metadatum_id == metadatumId );
                        let messages = [];

                        for (let index in error)
                            messages.push(error[index]);

                        if ( index >= 0)
                            Vue.set( this.errors, index, { metadatum_id: metadatumId, errors: messages });
                        else
                            this.errors.push( { metadatum_id: metadatumId, errors: messages } );
                });
            }
        },
        getErrors(metadatum_id) {
            let error = this.errors.find(errorItem => errorItem.metadatum_id == metadatum_id);
            return error ? error.errors : false
        },
        clearAllErrors() {
           this.errors = [];
        }
    },
    beforeUpdate() {
        this.$off('input', this.updateValue);
    }

});
