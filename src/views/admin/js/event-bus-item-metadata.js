import Vue from 'vue';
import store from './store/store'

export const eventBusItemMetadata = new Vue({
    store,
    data: {
        errors : [],
        conditionalSections: {}
    },
    watch: {
        errors() {
            this.$emit('hasErrorsOnForm', this.errors.length > 0 && this.errors[0].errors && this.errors[0].errors.length);
            
            if (this.errors.length > 0 && this.errors[0].errors && this.errors[0].errors.length) {
                for (let error of this.errors) 
                    this.$emit('updateErrorMessageOf#' + (error.metadatum_id + (error.parent_meta_id ? '-' + error.parent_meta_id : '')), error);
            }
        }
    },
    created() {
        this.$on('input', this.updateValue);
        this.$on('remove_group', this.removeItemMetadataGroup);
    },
    beforeUpdate() {
        this.$off('input', this.updateValue);
        this.$on('remove_group', this.removeItemMetadataGroup);
    },
    methods : {
        updateValue({ itemId, metadatumId, values, parentMetaId, parentId }){
            
            if (itemId) {

                this.$emit('isUpdatingValue', true);

                if (values.length > 0 && values[0] && values[0].value) {
                    let onlyValues = values.map((aValueObject) => aValueObject.value);
                    values = onlyValues;
                }
                
                this.$store.dispatch('item/updateItemMetadatum', { 
                    item_id: itemId, 
                    metadatum_id: metadatumId, 
                    values: Array.isArray(values[0])  ? values[0] : values,
                    parent_meta_id: parentMetaId ? parentMetaId : null
                })
                    .then(() => { 
                        this.$emit('isUpdatingValue', false);
                        let index = this.errors.findIndex( errorItem => errorItem.metadatum_id == metadatumId && (parentMetaId ? errorItem.parent_meta_id == parentMetaId : true ));
                        if (index >= 0)
                            this.errors.splice( index, 1);
                        
                        this.$emit('updateErrorMessageOf#' + (parentMetaId ? metadatumId + '-' + parentMetaId : metadatumId), this.errors[index]);
                    })
                    .catch(({ error_message, error, item_metadata }) => {
                        this.$emit('isUpdatingValue', false);
                        let index = this.errors.findIndex( errorItem => errorItem.metadatum_id == metadatumId && (parentMetaId ? errorItem.parent_meta_id == parentMetaId : true ));
                        let messages = [];

                        for (let index in error)
                            messages.push(error[index]);

                        if ( index >= 0) {
                            Vue.set( this.errors, index, { metadatum_id: metadatumId, parent_meta_id: parentMetaId, errors: messages });
                            this.$emit('updateErrorMessageOf#' + (parentMetaId ? metadatumId + '-' + parentMetaId : metadatumId), this.errors[index]);
                        } else {
                            this.errors.push( { metadatum_id: metadatumId, parent_meta_id: parentMetaId, errors: messages } );
                            this.$emit('updateErrorMessageOf#' + (parentMetaId ? metadatumId + '-' + parentMetaId : metadatumId), this.errors[0]);
                        }
                        
                });

            // If no itemId is provided, we are probably on an item Submission flow
            } else {

                if (values.length > 0 && values[0] != undefined && values[0].value) {
                    const onlyValues = values.map((aValueObject) => aValueObject.value);
                    values = JSON.parse(JSON.stringify(onlyValues));
                }
                
                this.$store.dispatch('item/updateItemSubmissionMetadatum', { 
                    metadatum_id: metadatumId, 
                    values: Array.isArray(values[0]) ? values[0] : values,
                    child_group_index: parentMetaId,
                    parent_id: parentId
                });

                // In the item submission, we don't want to block submission or clear errors before a re-submission is performed,
                // as the validation depends on a single server-side request. Thus, we do not update error arary here.

                this.$emit('isUpdatingValue', false);
            }

            /** 
             * Updates conditionalSections set values if this is one of the
             * metadata with values that affect the sections visibility.
             */
            let updatedConditionalSections = JSON.parse(JSON.stringify(this.conditionalSections));
            for (let conditionalSectionId in updatedConditionalSections) {
                if ( updatedConditionalSections[conditionalSectionId].metadatumId == metadatumId ) {
                    const conditionalValues = Array.isArray(updatedConditionalSections[conditionalSectionId].metadatumValues) ? updatedConditionalSections[conditionalSectionId].metadatumValues : [ this.conditionalSections[conditionalSectionId].metadatumValues ];
                    updatedConditionalSections[conditionalSectionId].hide = values.every(aValue => conditionalValues.indexOf(aValue) < 0);
                }
            }
            this.conditionalSections = updatedConditionalSections;
        },
        removeItemMetadataGroup({ itemId, metadatumId, parentMetaId, parentMetadatum }) {
            
            this.$emit('isUpdatingValue', true);
            
            if (itemId && metadatumId && parentMetaId) {
                
                this.$store.dispatch('item/deleteItemMetadataGroup', { 
                    item_id: itemId, 
                    metadatum_id: metadatumId,
                    parent_meta_id: parentMetaId
                })
                    .then((res) => {
                        this.$emit('hasRemovedItemMetadataGroup', res);
                        this.$emit('isUpdatingValue', false);
                    })
                    .catch(() => this.$emit('isUpdatingValue', false));
            
            // Item sbmission logic
            } else if (!itemId) {
                
                this.$store.dispatch('item/deleteGroupFromItemSubmissionMetadatum', { 
                    metadatum_id: metadatumId,
                    child_group_index: parentMetaId
                });
                
                this.$emit('hasRemovedItemMetadataGroup', true);
                this.$emit('isUpdatingValue', false);
            }
        },
        clearAllErrors() {
           this.errors = [];
        },
        fetchCompoundFirstParentMetaId({ itemId, metadatumId }) {
            return this.$store.dispatch('item/fetchCompoundFirstParentMetaId', { item_id: itemId, metadatum_id: metadatumId });
        }
    }
});
