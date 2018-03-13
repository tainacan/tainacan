<template>
    <div>
        <b-loading :active.sync="isLoadingFieldTypes"></b-loading>
        <div class="columns">
            <div class="column">
                <b-field :label="$i18n.get('label_active_fields')" is-grouped>
                    <draggable 
                        class="box active-fields-area"
                        @change="handleChange"
                        :class="{'fields-area-receive': isDraggingFromAvailable}" 
                        :list="activeFieldList" 
                        :options="{group: { name:'fields', pull: false, put: true }, 'handle': '.handle', chosenClass: 'sortable-chosen', filter: '.not-sortable-item'}">
                        <div  
                            class="active-field-item" 
                            :class="{'not-sortable-item': field.id == undefined, 'not-focusable-item': openedFieldId == field.id, 'inherited-field': field.collection_id != collectionId}" 
                            v-for="(field, index) in activeFieldList" :key="index">
                            <div class="handle">
                                <b-icon type="is-gray" class="is-pulled-left" icon="drag"></b-icon>
                                <span class="field-name">{{ field.name }}</span>
                                <span v-if="field.id !== undefined" class="label-details">{{ $i18n.get(field.field_type_object.component)}}</span><span class="loading-spinner" v-if="field.id == undefined"></span>
                                <span class="controls" v-if="field.id !== undefined">
                                    <b-switch size="is-small" v-model="field.disabled" @input="onChangeEnable($event, index)">{{ field.disabled ? $i18n.get('label_disabled') : $i18n.get('label_enabled') }}</b-switch>
                                    <a @click.prevent="removeField(field)">
                                        <b-icon icon="delete"></b-icon>
                                    </a>
                                    <a @click.prevent="editField(field)">
                                        <b-icon icon="pencil"></b-icon>
                                    </a>
                                </span>
                            </div>
                            <b-field v-if="openedFieldId == field.id">
                                <field-edition-form 
                                    :collectionId="collectionId"
                                    :isRepositoryLevel="isRepositoryLevel"
                                    @onEditionFinished="onEditionFinished()"
                                    @onEditionCanceled="onEditionCanceled()"
                                    :field="editForm"></field-edition-form>
                            </b-field>
                        </div>
                    </draggable> 
                </b-field>
            </div>
            <div class="column">
                <b-field :label="$i18n.get('label_available_field_types')">
                    <div class="columns box available-fields-area" >
                        <draggable class="column" :list="availableFieldList" :options="{ sort: false, group: { name:'fields', pull: 'clone', put: false, revertClone: true }}">
                            <div class="available-field-item" v-if="index % 2 == 0" v-for="(field, index) in availableFieldList" :key="index">
                                {{ field.name }}  <b-icon type="is-gray" class="is-pulled-left" icon="drag"></b-icon>
                            </div>
                        </draggable>
                        <draggable class="column" :list="availableFieldList" :options="{ sort: false, group: { name:'fields', pull: 'clone', put: false, revertClone: true }}">
                            <div class="available-field-item" v-if="index % 2 != 0" v-for="(field, index) in availableFieldList" :key="index">
                                {{ field.name }}  <b-icon type="is-gray" class="is-pulled-left" icon="drag"></b-icon>
                            </div>       
                        </draggable> 
                   </div>
                </b-field>
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
            editForm: {}
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
                fieldsOrder.push({'id': field.id, 'enabled': !field.disabled});
            }
            this.updateCollectionFieldsOrder({ collectionId: this.collectionId, fieldsOrder: fieldsOrder });
        },
        onChangeEnable($event, index) {
            this.activeFieldList[index].disabled = $event;
            let fieldsOrder = [];
            for (let field of this.activeFieldList) {
                fieldsOrder.push({'id': field.id, 'enabled': !field.disabled});
            }
            this.updateCollectionFieldsOrder({ collectionId: this.collectionId, fieldsOrder: fieldsOrder });

        },
        addNewField(newField, newIndex) {
            this.sendField({collectionId: this.collectionId, name: newField.name, fieldType: newField.className, status: 'auto-draft', isRepositoryLevel: this.isRepositoryLevel})
            .then((field) => {

                if (newIndex < 0) {
                    this.activeFieldList.pop();
                    this.activeFieldList.push(field);
                } else {
                   this.activeFieldList.splice(newIndex, 1, field);  
                }

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

    .active-fields-area {
        min-height: 40px;
        padding: 10px;

        &.fields-area-receive {
            background-color: whitesmoke;
            border: 1px dashed gray;
        }

        .collapse {
            display: initial;
        }

        .active-field-item {
            background-color: white;
            padding: 0.4em;
            margin: 10px;
            border-radius: 5px;
            border: 1px solid gainsboro;
            display: block; 
            transition: top 0.2s ease;
            cursor: grab;
        
            .field-name {
                text-overflow: ellipsis;
                overflow-x: hidden;
                white-space: nowrap;
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
            }
            &.inherited-field {
                color: gray;
            }
        }
        .active-field-item:hover {
            box-shadow: 0 3px 4px rgba(0,0,0,0.25);
            position: relative;
            top: -2px;
        }

        .sortable-chosen {
            background-color: $primary-lighter;
            margin: 10px;
            border-radius: 5px;
            border: 1px dashed $primary-light;
            display: block; 
        }
    }

    .available-fields-area {
        padding: 0 10px;
        margin: 0;
        background-color: whitesmoke;

        .available-field-item {
            padding: 0.4em;
            margin: 10px 10% 10px 0px;
            border-radius: 5px;
            background-color: white;
            border: 1px solid gainsboro;
            width: 100%;
            cursor: grab;
            top: 0;
            transition: top 0.2s ease;
        }
        .available-field-item:hover {
            border: 1px solid lightgrey;
            box-shadow: 2px 3px 4px rgba(0,0,0,.25);
            position: relative;
            top: -2px;
            left: -2px;
        }
    }

</style>




