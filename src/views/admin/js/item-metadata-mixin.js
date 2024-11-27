import { mapActions } from 'vuex';

export const itemMetadataMixin = {
    created() {
        this.$emitter.on('removeCompoundGroup', this.removeItemMetadataGroup);
        this.$emitter.on('updateValueFromCompound', this.updateItemMetadataValue);
    },
    beforeDestroy() {
        this.$emitter.off('removeCompoundGroup', this.removeItemMetadataGroup);
        this.$emitter.off('updateValueFromCompound', this.updateItemMetadataValue);
    },
    data () {
        return {
            errors : [],
            conditionalSections: {}
        }
    },
    watch: {
        errors: {
            handler() {
                this.hasErrorsOnForm( this.errors.length > 0 && this.errors[0].errors && this.errors[0].errors.length );
                
                if ( this.errors.length > 0 && this.errors[0].errors && this.errors[0].errors.length ) {
                    for (let error of this.errors) {
                        this.$emitter.emit('updateErrorMessageOf#' + (error.metadatum_id + (error.parent_meta_id ? '-' + error.parent_meta_id : '')), error);
                    }
                }
            },
            deep: true
        }
    },
    methods: {
        ...mapActions('item', [
            'updateItemMetadatum',
            'updateItemSubmissionMetadatum',
            'deleteItemMetadataGroup',
            'deleteGroupFromItemSubmissionMetadatum'
        ]),
        updateItemMetadataValue({ itemId, metadatumId, values, parentMetaId, parentId }) {

            if ( itemId ) {

                this.isUpdatingValues = true;

                if (values.length > 0 && values[0] && values[0].value) {
                    let onlyValues = values.map((aValueObject) => aValueObject.value);
                    values = onlyValues;
                }
                
                this.updateItemMetadatum({ 
                    item_id: itemId, 
                    metadatum_id: metadatumId, 
                    values: Array.isArray(values[0])  ? values[0] : values,
                    parent_meta_id: parentMetaId ? parentMetaId : null
                })
                    .then(() => { 
                        this.isUpdatingValues = false;
                        let index = this.errors.findIndex( errorItem => ( errorItem.metadatum_id == metadatumId && (parentMetaId ? errorItem.parent_meta_id == parentMetaId : true ) ) || ( errorItem.metadatum_id == parentId ) );
                        if ( index >= 0 )
                            this.errors.splice( index, 1);
                        
                        this.$emitter.emit('updateErrorMessageOf#' + (parentMetaId ? metadatumId + '-' + parentMetaId : metadatumId), this.errors[index]);

                        if ( parentId )
                            this.$emitter.emit('updateErrorMessageOf#' + parentId );
                    })
                    .catch(({ error_message, error, item_metadata }) => {
                        this.isUpdatingValues = false;
                        let index = this.errors.findIndex( errorItem => ( errorItem.metadatum_id == metadatumId && (parentMetaId ? errorItem.parent_meta_id == parentMetaId : true ) ) || ( errorItem.metadatum_id == parentId ) );
                        let messages = [];

                        for (let index in error)
                            messages.push(error[index]);
                        
                        if ( index >= 0) {
                            Object.assign( this.errors, { [index]: { metadatum_id: metadatumId, parent_meta_id: parentMetaId, errors: messages } });
                            this.$emitter.emit('updateErrorMessageOf#' + (parentMetaId ? metadatumId + '-' + parentMetaId : metadatumId), this.errors[index]);

                            if ( parentId )
                                this.$emitter.emit('updateErrorMessageOf#' + parentId );
                        } else {
                            this.errors.push( { metadatum_id: metadatumId, parent_meta_id: parentMetaId, errors: messages } );
                            this.$emitter.emit('updateErrorMessageOf#' + (parentMetaId ? metadatumId + '-' + parentMetaId : metadatumId), this.errors[0]);

                            if ( parentId )
                                this.$emitter.emit('updateErrorMessageOf#' + parentId );
                        }
                        
                });

            // If no itemId is provided, we are probably on an item Submission flow
            } else {

                if (values.length > 0 && values[0] != undefined && values[0].value) {
                    const onlyValues = values.map((aValueObject) => aValueObject.value);
                    values = JSON.parse(JSON.stringify(onlyValues));
                }
                
                this.updateItemSubmissionMetadatum({ 
                    metadatum_id: metadatumId, 
                    values: Array.isArray(values[0]) ? values[0] : values,
                    child_group_index: parentMetaId,
                    parent_id: parentId
                });

                // In the item submission, we don't want to block submission or clear errors before a re-submission is performed,
                // as the validation depends on a single server-side request. Thus, we do not update error arary here.

                this.isUpdatingValues = false;
            }

            /** 
             * Updates conditionalSections set values if this is one of the
             * metadata with values that affect the sections visibility.
             */
            let updatedConditionalSections = JSON.parse(JSON.stringify(this.conditionalSections));
            for (let conditionalSectionId in updatedConditionalSections) {
                if ( updatedConditionalSections[conditionalSectionId].metadatumId == metadatumId ) {
                    const conditionalValues = Array.isArray(updatedConditionalSections[conditionalSectionId].metadatumValues) ? updatedConditionalSections[conditionalSectionId].metadatumValues : [ this.conditionalSections[conditionalSectionId].metadatumValues ];
                    updatedConditionalSections[conditionalSectionId].hide = Array.isArray(values) ? values.every(aValue => conditionalValues.indexOf(aValue['id'] ? aValue['id'] : aValue) < 0) : conditionalValues.indexOf(values) < 0 ;
                }
            }
            this.conditionalSections = updatedConditionalSections;
        },
        clearAllErrors() {
            this.errors = [];
        },
        removeItemMetadataGroup({ itemId, metadatumId, parentMetaId, parentMetadatum }) {
            
            this.isUpdatingValues = true;
            
            if (itemId && metadatumId && parentMetaId) {
                
                this.deleteItemMetadataGroup({ 
                    item_id: itemId, 
                    metadatum_id: metadatumId,
                    parent_meta_id: parentMetaId
                })
                    .then((res) => {
                        this.$emitter.emit('hasRemovedItemMetadataGroup', res);
                        this.isUpdatingValues = false;
                    })
                    .catch(() => this.isUpdatingValues = false);
            
            // Item sbmission logic
            } else if (!itemId) {
                
                this.deleteGroupFromItemSubmissionMetadatum({ 
                    metadatum_id: metadatumId,
                    child_group_index: parentMetaId
                });
                
                this.$emitter.emit('hasRemovedItemMetadataGroup', true);
                this.isUpdatingValues = false;
            }
        },
    }
}