<template>
    <div>
        <b-loading :active.sync="isLoadingFieldTypes"></b-loading>
        <div class="page-title">
            <h2>{{ $i18n.get('instruction_dragndrop_fields_collection')}}</h2>
        </div>
        <div class="columns">
            <div class="column">        
                <draggable 
                    class="active-fields-area"
                    @change="handleChange"
                    :class="{'fields-area-receive': isDraggingFromAvailable}" 
                    :list="activeFieldList" 
                    :options="{ 
                            group: { name:'fields', pull: false, put: true }, 
                            handle: '.handle', 
                            ghostClass: 'sortable-ghost', 
                            filter: '.not-sortable-item', 
                            animation: '250'}">
                    <div  
                        class="active-field-item"
                        :class="{'not-sortable-item': field.id == undefined, 'not-focusable-item': openedFieldId == field.id, 'inherited-field': field.collection_id != collectionId}" 
                        v-for="(field, index) in activeFieldList" :key="index">
                        <div class="handle">
                            <b-icon type="is-gray" class="is-pulled-left" icon="drag"></b-icon>
                            <span class="field-name">{{ field.name }}</span>
                            <span v-if="field.id !== undefined" class="label-details">{{ $i18n.get(field.field_type_object.component)}}</span><span class="loading-spinner" v-if="field.id == undefined"></span>
                            <span class="controls" v-if="field.id !== undefined">
                                <b-switch size="is-small" v-model="field.enabled" @input="onChangeEnable($event, index)"></b-switch>
                                <a @click.prevent="removeField(field)">
                                    <b-icon icon="delete"></b-icon>
                                </a>
                                <a @click.prevent="editField(field)">
                                    <b-icon icon="pencil"></b-icon>
                                </a>
                            </span>
                        </div>
                        <div v-if="openedFieldId == field.id">
                            <field-edition-form   
                                :collectionId="collectionId"
                                :isRepositoryLevel="isRepositoryLevel"
                                @onEditionFinished="onEditionFinished()"
                                @onEditionCanceled="onEditionCanceled()"
                                :field="editForm"></field-edition-form>
                        </div>
                    </div>
                </draggable> 
            </div>
          
            <div class="column available-fields-area" >
                <div class="field">
                    <h3 class="label">{{ $i18n.get('label_available_field_types')}}</h3>
                    <draggable 
                        :list="availableFieldList" 
                        :options="{ 
                            sort: false, 
                            group: { name:'fields', pull: 'clone', put: false, revertClone: true },
                            dragClass: 'sortable-drag'
                        }">
                        <div 
                            @click.prevent="addFieldViaButton(field)" 
                            class="available-field-item" 
                            v-for="(field, index) in availableFieldList" 
                            :key="index">
                            <b-icon type="is-gray" class="is-pulled-left"  icon="drag"></b-icon> <span class="field-name">{{ field.name }}</span>
                        </div>
                    </draggable>
                </div>
            </div> 
        </div>
    </div> 
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import FieldEditionForm from './../edition/field-edition-form.vue';

export default {
    name: 'FieldsList',
    data(){           
        return {
            collectionId: '',
            isRepositoryLevel: false,
            isDraggingFromAvailable: false,
            isLoadingFieldTypes: true,
            isLoadingFields: false,
            isLoadingField: false,
            openedFieldId: '',
            editForm: {},
        }
    },
    components: {
        FieldEditionForm
    },
    methods: {
        ...mapActions('fields', [
            'fetchFieldTypes',
            'fetchFields',
            'sendField',
            'deleteField',
            'updateCollectionFieldsOrder'
        ]),
        ...mapGetters('fields',[
            'getFieldTypes',
            'getFields'
        ]),
        handleChange($event) {     
            if ($event.added) {
                this.addNewField($event.added.element, $event.added.newIndex);
            } else if ($event.removed) {
                this.removeField($event.removed.element);
            } else if ($event.moved) {
                if (!this.isRepositoryLevel)
                    this.updateFieldsOrder(); 
            }
        },
        updateFieldsOrder() {
            let fieldsOrder = [];
            for (let field of this.activeFieldList) {
                fieldsOrder.push({'id': field.id, 'enabled': field.enabled});
            }
            this.updateCollectionFieldsOrder({ collectionId: this.collectionId, fieldsOrder: fieldsOrder });
        },
        onChangeEnable($event, index) {
            this.activeFieldList[index].enabled = $event;
            let fieldsOrder = [];
            for (let field of this.activeFieldList) {
                fieldsOrder.push({'id': field.id, 'enabled': field.enabled});
            }
            this.updateCollectionFieldsOrder({ collectionId: this.collectionId, fieldsOrder: fieldsOrder });

        },
        addFieldViaButton(fieldType) {
            let lastIndex = this.activeFieldList.length;
            this.activeFieldList.push(fieldType);
            this.addNewField(fieldType, lastIndex);
        },
        addNewField(newField, newIndex) {
            this.sendField({collectionId: this.collectionId, name: newField.name, fieldType: newField.className, status: 'auto-draft', isRepositoryLevel: this.isRepositoryLevel, newIndex: newIndex})
            .then((field) => {

                if (!this.isRepositoryLevel)
                    this.updateFieldsOrder();

                this.editField(field);
            })
            .catch((error) => {
                console.log(error);
            });
        },
        removeField(removedField) {
            this.deleteField({ collectionId: this.collectionId, fieldId: removedField.id, isRepositoryLevel: this.isRepositoryLevel})
            .then((field) => {
                let index = this.activeFieldList.findIndex(deletedField => deletedField.id === field.id);
                if (index >= 0) 
                    this.activeFieldList.splice(index, 1);
                
                if (!this.isRepositoryLevel)
                    this.updateFieldsOrder(); 
            })
            .catch((error) => {
            });
        },
        editField(field) {
            if (this.openedFieldId == field.id) {
                this.openedFieldId = '';
                this.editForm = {};
            } else {
                this.openedFieldId = field.id;
                this.editForm = JSON.parse(JSON.stringify(field));
                this.editForm.status = 'publish';
            }            
        },
        onEditionFinished() {
            this.openedFieldId = '';
            this.fetchFields({collectionId: this.collectionId, isRepositoryLevel: this.isRepositoryLevel});
        },
        onEditionCanceled() {
            this.openedFieldId = '';
        }

    },
    computed: {
        availableFieldList() {
            return this.getFieldTypes();
        },
        activeFieldList() {
            return this.getFields();
        }
    },
    created() {
        this.isLoadingFieldTypes = true;
        this.isLoadingFields = true;

        this.fetchFieldTypes()
            .then((res) => {
                this.isLoadingFieldTypes = false;
            })
            .catch((error) => {
                this.isLoadingFieldTypes = false;
            });

        this.isRepositoryLevel = this.$route.name == 'FieldsPage' ? true : false;
        if (this.isRepositoryLevel)
            this.collectionId = 'default';
        else
            this.collectionId = this.$route.params.collectionId;
        

        this.fetchFields({collectionId: this.collectionId, isRepositoryLevel: this.isRepositoryLevel})
            .then((res) => {
                this.isLoadingFields = false;
            })
            .catch((error) => {
                this.isLoadingFields = false;
            });
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .page-title {
        border-bottom: 1px solid $secondary;
        h2 {
            color: $tertiary;
        }
        margin: 1em 0em 1.6em -1.2em;
    }

    .active-fields-area {
        font-size: 14px;
        min-height: 40px;
        padding: 10px;
        margin-right: 6em; 

        &.fields-area-receive {
            border: 1px dashed gray;
        }

        .collapse {
            display: initial;
        }
        .list-item {
            visibility: visible;
            opacity: 1;
            transition: all 5s;
        }
        .active-field-item {
            background-color: white;
            padding: 0.6em;
            margin: 4px;
            min-height: 42px;
            display: block; 
            transition: top 0.2s ease;
            cursor: grab;

            .field-name {
                text-overflow: ellipsis;
                overflow-x: hidden;
                white-space: nowrap;
                font-weight: bold;
                margin-left: 0.4em;
                margin-right: 0.4em;
            }
            .label-details {
                font-weight: normal;
                font-style: italic;
                color: gray;
            }
            .controls { float: right }
            .loading-spinner {
                animation: spinAround 500ms infinite linear;
                border: 2px solid #dbdbdb;
                border-radius: 290486px;
                border-right-color: transparent;
                border-top-color: transparent;
                content: "";
                display: inline-block;
                height: 1em; 
                width: 1em;
            }
            &.not-sortable-item, &.not-sortable-item:hover, &.not-focusable-item, &.not-focusable-item:hover {
                box-shadow: none !important;
                top: 0px !important;
                cursor: default;
                background-color: white !important;
                
                .field-name {
                    color: $primary !important;
                }
                .label-details, .icon {
                    color: gray !important;
                }
            }
            &.inherited-field {
                color: gray;
            }           
        }
        .active-field-item:hover {
            background-color: $secondary;
            border-color: $secondary;
            color: white !important;
            top: -2px;

            .label-details, .icon {
                color: white !important;
            }
        }
        .sortable-ghost {
            border-left: 1px solid $draggable-border-color;
            border-right: 1px dashed $draggable-border-color;
            border-top: 1px dashed $draggable-border-color;
            border-bottom: 1px dashed $draggable-border-color;
            display: block;
            position: relative;
            height: 42px;

            // &:after,
            // &:before {
            //     content: '';
            //     display: block;
            //     position: absolute;
            //     left: 0%;
            //     width: 0;
            //     height: 0;
            //     border-style: solid;
            // }
            // &:after {
            //     top: 0px;
            //     border-color: $secondary transparent $secondary $secondary;
            //     border-width: 20px;
            // }
            // &:before {
            //     top: -1px;
            //     border-color: $secondary transparent $secondary $secondary;
            //     border-width: 21px;
            // }
        }
    }

    .available-fields-area {
        padding: 10px 0px 10px 10px;
        margin: 0;
        max-width: 280px;
        font-size: 14px;

        h3 {
            color: $secondary;
            margin: 1.0em 0em;
        }

        .available-field-item {
            padding: 0.6em;
            margin: 4px;
            background-color: white;
            cursor: pointer;
            left: 0;
            height: 42px;
            position: relative;
            border: 1px solid $draggable-border-color;
            transition: left 0.2s ease;
            
            .field-name {
                text-overflow: ellipsis;
                overflow-x: hidden;
                white-space: nowrap;
                font-weight: bold;
                margin-left: 0.4em;
            }
            &:after,
            &:before {
                content: '';
                display: block;
                position: absolute;
                right: 100%;
                width: 0;
                height: 0;
                border-style: solid;
            }
            &:after {
                top: 0px;
                border-color: transparent white transparent transparent;
                border-width: 20px;
            }
            &:before {
                top: -1px;
                border-color: transparent $draggable-border-color transparent transparent;
                border-width: 21px;
            }

            .sortable-drag {
                opacity: 1 !important;
            }
        }

        .available-field-item:hover {
            background-color: $secondary;
            border-color: $secondary;
            color: white;
            position: relative;
            left: -4px;

            &:after {
                border-color: transparent $secondary transparent transparent;
            }
            &:before {
                border-color: transparent $secondary transparent transparent;
            }
            .icon {
                color: white !important;
            }
        }
    }

</style>




