<template>
    <div>
        <h1>Fields List and Edition Component</h1>
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
                            :class="{'not-sortable-item': field.id == undefined || isRepositoryLevel }" 
                            v-for="(field, index) in activeFieldList" :key="index">
                            <div>
                                <div class="handle">
                                    {{ field.name }}
                                    <span class="label-details"><span class="loading-spinner" v-if="field.id == undefined"></span> <b-tag v-if="field.status != undefined">{{field.status}}</b-tag></span>
                                    <b-icon type="is-gray" class="is-pulled-right" icon="drag"></b-icon>
                                    <a @click.prevent="removeField(field)" v-if="field.id != undefined"><b-icon icon="delete"></b-icon></a>
                                    <a @click.prevent="editField(field)" v-if="field.id != undefined"><b-icon icon="pencil" v-if="field.id != undefined"></b-icon></a>
                                </div>
                                <div v-if="openedFieldId == field.id">
                                    <form id="fieldEditForm" v-on:submit.prevent="saveEdition($event,field)">    
                                        
                                        <h2 class="is-size-5">{{ $i18n.get('edit') }}</h2>

                                        <b-field :label="$i18n.get('label_name')" message="">
                                            <b-input v-model="editForm.name"></b-input>
                                        </b-field>

                                        <b-field :label="$i18n.get('label_description')" message="">
                                            <b-input type="textarea" name="description" v-model="editForm.description"></b-input>
                                        </b-field>

                                        <div class="field">
                                            <b-switch 
                                                v-model="editForm.required"
                                                true-value="yes" 
                                                false-value="no"
                                                native-value="yes"
                                                name="required">
                                                {{ $i18n.get('label_required') }}
                                            </b-switch>
                                        </div>

                                        <div class="field">
                                            <b-switch 
                                                v-model="editForm.multiple"
                                                true-value="yes" 
                                                false-value="no"
                                                native-value="yes"
                                                name="multiple">
                                                {{ $i18n.get('label_unique_multiple') }}
                                            </b-switch>
                                        </div>

                                        <b-field message="">
                                            <b-switch 
                                                v-model="editForm.unique"
                                                true-value="yes" 
                                                false-value="no"
                                                native-value="yes"
                                                name="collecion_key">
                                                {{ $i18n.get('label_unique_value') }}
                                            </b-switch>
                                        </b-field>

                                        <b-field :label="$i18n.get('label_status')" message="">
                                            <b-select
                                                    id="tainacan-select-status"
                                                    name="status"
                                                    :value="editForm.status"
                                                    :placeholder="$i18n.get('instruction_select_a_status')">
                                                <option value="publish">{{ $i18n.get('publish')}}</option>
                                                <option value="private">{{ $i18n.get('private')}}</option>
                                            </b-select>
                                        </b-field>

                                        <component
                                                v-if="field.field_type_object && field.field_type_object.form_component"
                                                :is="field.field_type_object.form_component"
                                                v-model="editForm.field_type_options">
                                        </component>
                                        <div v-html="field.edit_form" v-else></div>
                                        
                                        <div class="field is-grouped is-grouped-centered">
                                            <div class="control">
                                                <button class="button is-secondary" type="submit">Submit</button>
                                            </div>
                                            <div class="control">
                                                <button class="button is-text" @click.prevent="cancelEdition(field)" slot="trigger">Cancel</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                                                  
                        <!-- <div class="not-sortable-item" slot="footer">{{ $i18n.get('instruction_dragndrop_fields_collection') }}</div> -->
                    </draggable> 
                </b-field>
            </div>
            <div class="column">
                <b-field :label="$i18n.get('label_available_fields')">
                    <div class="columns box available-fields-area" >
                        <draggable class="column" :list="availableFieldList" :options="{ sort: false, group: { name:'fields', pull: 'clone', put: false, revertClone: true }}">
                            <div class="available-field-item" v-if="index % 2 == 0" v-for="(field, index) in availableFieldList" :key="index">
                                {{ field.name }}  <b-icon type="is-gray" class="is-pulled-right" icon="drag"></b-icon>
                            </div>
                        </draggable>
                        <draggable class="column" :list="availableFieldList" :options="{ sort: false, group: { name:'fields', pull: 'clone', put: false, revertClone: true }}">
                            <div class="available-field-item" v-if="index % 2 != 0" v-for="(field, index) in availableFieldList" :key="index">
                                {{ field.name }}  <b-icon type="is-gray" class="is-pulled-right" icon="drag"></b-icon>
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
import qs from 'qs';
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
            editForm: {},
            openedFieldId: '',
        }
    },
    methods: {
        ...mapActions('fields', [
            'fetchFieldTypes',
            'fetchFields',
            'sendField',
            'updateField',
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
        saveEdition($event, field) {

            let formElement = document.getElementById('fieldEditForm');
            let formData = new FormData(formElement);
            this.openedFieldId = field.id;
            
            let formObj = {}
            for (var [key, value] of formData.entries()) { 
                formObj[key] = value;
            }

            this.updateField({collectionId: this.collectionId, fieldId: field.id, isRepositoryLevel: this.isRepositoryLevel, options: formObj})
            .then((field) => {
                 console.log(field);
            })
            .catch((error) => {
                console.log(error);
            });
        },
        cancelEdition(field) {
            this.editForm = {};
            this.openedFieldId = '';
        },
        updateFieldsOrder() {
            let fieldsOrder = [];
            for (let field of this.activeFieldList) {
                fieldsOrder.push({'id': field.id, 'enabled': true});
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
                this.editForm = field;
            }
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

    @import "../scss/_variables.scss";

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
            padding: 0.2em 0.5em;
            margin: 10px;
            border-radius: 5px;
            border: 1px solid gainsboro;
            display: block;
            cursor: grab;
            .icon { float: right }
            .label-details {
                font-weight: normal;
                font-style: italic;
                color: gray;
            }
            form {
                padding: 5px;
            }

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

            &.not-sortable-item {
                color: gray;
                cursor: default;
            }
        }
        .active-field-item:hover {
            box-shadow: 0px 0px 2px #777;
        }

        .sortable-chosen {
            background-color: $primary-lighter;
            padding: 0.2em 0.5em;
            margin: 10px;
            border-radius: 5px;
            border: 2px dashed $primary-light;
            display: block; 
        }
    }

    .available-fields-area {
        padding: 0 10px;
        margin: 0;
        background-color: whitesmoke;

        .available-field-item {
            padding: 0.2em 0.5em;
            margin: 10px 10% 10px 0px;
            border-radius: 5px;
            background-color: white;
            border: 1px solid gainsboro;
            width: 100%;
            cursor: grab;
        }
        .available-field-item:hover {
            border: 1px solid lightgrey;
            box-shadow: 0px 0px 2px #777;
        }
    }

</style>




