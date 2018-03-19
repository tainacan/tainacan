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
                            filter: 'not-sortable-item', 
                            animation: '250'}">
                    <div  
                        class="active-field-item"
                        :class="{'not-sortable-item': field.id == undefined || openedFieldId == field.id, 'not-focusable-item': openedFieldId == field.id, 'disabled-field': field.enabled == false}" 
                        v-for="(field, index) in activeFieldList" :key="index">
                        <div class="handle">
                            <b-icon type="is-gray" class="is-pulled-left" icon="drag"></b-icon>
                            <span class="field-name">{{ field.name }}</span>
                            <span v-if="field.id !== undefined" class="label-details">({{ $i18n.get(field.field_type_object.component)}})</span><span class="loading-spinner" v-if="field.id == undefined"></span>
                            <span class="controls" v-if="field.id !== undefined">
                                <b-switch size="is-small" v-model="field.enabled" @input="onChangeEnable($event, index)"></b-switch>
                                <a  :style="{ visibility: 
                                                field.collection_id != collectionId 
                                                ? 'hidden' : 'visible'
                                            }" 
                                    @click.prevent="editField(field)">
                                    <b-icon type="is-gray" icon="pencil"></b-icon>
                                </a>
                                <a  :style="{ visibility: 
                                                field.collection_id != collectionId || 
                                                field.field_type == 'Tainacan\\Field_Types\\Core_Title' || 
                                                field.field_type == 'Tainacan\\Field_Types\\Core_Description' 
                                                ? 'hidden' : 'visible'
                                            }" 
                                    @click.prevent="removeField(field)">
                                    <b-icon type="is-gray" icon="delete"></b-icon>
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
                            :class="{ 'hightlighted-field' : hightlightedField == field.name }" 
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
            hightlightedField: '',
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
            
            // Higlights the clicker field
            this.hightlightedField = fieldType.name;

        },
        addNewField(newField, newIndex) {
            this.sendField({collectionId: this.collectionId, name: newField.name, fieldType: newField.className, status: 'auto-draft', isRepositoryLevel: this.isRepositoryLevel, newIndex: newIndex})
            .then((field) => {

                if (!this.isRepositoryLevel)
                    this.updateFieldsOrder();

                this.editField(field);
                this.hightlightedField = '';
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

<style lang="scss">

    @import "../../scss/_variables.scss";

    .page-title {
        border-bottom: 1px solid $secondary;
        h2 {
            color: $tertiary;
            font-weight: 500;
        }
        margin: 1em 0em 2.0em -1em;

        @media screen and (max-width: 769px) {
            margin: 1em 0em 2.0em 0em;
        }
    }

    .active-fields-area {
        font-size: 14px;
        margin-right: 0.8em;
        margin-left: -0.8em;
        padding-right: 6em;
        min-height: 330px;

        @media screen and (max-width: 769px) {
            min-height: 45px;
            margin: 0; 
            padding-right: 0em;
        }
        @media screen and (max-width: 1216px) {
            padding-right: 1em;
        }

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
            padding: 0.8em;
            margin: 4px;
            min-height: 42px;
            display: block; 
            top: 0;
            position: relative;
            transition: top 0.1s linear;
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
                color: $gray;
            }
            .controls { 
                float: right;
                .switch {
                    position: relative;
                    bottom: 3px;
                }
            }
            .icon {
                bottom: 1px;   
                position: relative;
                font-size: 18px;
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
            &.not-sortable-item, &.not-sortable-item:hover, &.not-focusable-item, &.not-focusable-item:hover {
                box-shadow: none !important;
                top: 0px !important;
                cursor: default;
                background-color: white !important;
                
                .field-name {
                    color: $primary !important;
                }
                .label-details, .icon {
                    color: $gray !important;
                }
            }
            &.disabled-field {
                color: $gray;
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

            .switch.is-small {
                input[type="checkbox"] + .check {
                    border: 2px solid white !important;
                    &::before { background-color: white !important; }
                } 

                input[type="checkbox"]:checked + .check {
                    border: 2px solid white !important;
                    &::before { background-color: white !important; }
                }
                &:hover input[type="checkbox"] + .check {
                    border: 2px solid white !important;
                    background-color: $secondary !important;
                }
            }

        }
        .sortable-ghost {
            border: 1px dashed $draggable-border-color;
            display: block;
            padding: 0.8em;
            margin: 4px;
            min-height: 42px;
            position: relative;
        }
    }

    .available-fields-area {
        padding: 10px 0px 10px 10px;
        margin: 0;
        max-width: 280px;
        font-size: 14px;

        @media screen and (max-width: 769px) {
            max-width: 100%;
            padding: 10px;
            h3 {
                margin: 1em 0em 1em 0em !important;
            }
            .available-field-item::before, 
            .available-field-item::after {
                display: none !important;
            }
        }

        h3 {
            color: $secondary;
            margin: 0.2em 0em 1em -1.2em;
            font-weight: 500;
        }

        .available-field-item {
            padding: 0.7em;
            margin: 4px;
            background-color: white;
            cursor: pointer;
            left: 0;
            height: 42px;
            position: relative;
            border: 1px solid $draggable-border-color;
            border-radius: 1px;
            transition: left 0.2s ease;
            
            .icon {
                position: relative;
                bottom: 3px;
            }
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
                top: -1px;
                border-color: transparent white transparent transparent;
                border-right-width: 16px;
                border-top-width: 21px;
                border-bottom-width: 21px;
                left: -19px;
            }
            &:before {
                top: -1px;
                border-color: transparent $draggable-border-color transparent transparent;
                border-right-width: 16px;
                border-top-width: 21px;
                border-bottom-width: 21px;
                left: -20px;
            }
        }

        .sortable-drag {
            opacity: 1 !important;
        }

        @keyframes hightlighten {
            0%   {
                color: #222;             
                background-color: white;
                border-color: white;
            }
            25%  {
                color: white;            
                background-color: #2cb4c1; 
                border-color: #2cb4c1;
            }
            75%  {
                color: white;            
                background-color: #2cb4c1; 
                border-color: #2cb4c1;
            }
            100% {
                color: #222;             
                background-color: white;
                border-color: white;
            }
        }
        @keyframes hightlighten-icon {
            0%   { color: #b1b1b1; }
            25%  { color: white; }
            75%  { color: white; }
            100% { color: #b1b1b1; }
        }
        @keyframes hightlighten-arrow {
            0%   {
                border-color: transparent white transparent transparent;
                border-color: transparent white transparent transparent; 
            }
            25%  {
                border-color: transparent #2cb4c1 transparent transparent;
                border-color: transparent #2cb4c1 transparent transparent; 
            }
            75%  {
                border-color: transparent #2cb4c1 transparent transparent;
                border-color: transparent #2cb4c1 transparent transparent; 
            }
            100% {
                border-color: transparent white transparent transparent;
                border-color: transparent white transparent transparent;  
            }
        }
        .hightlighted-field {
            background-color: white;
            position: relative;
            left: 0px;
            animation-name: hightlighten;
            animation-duration: 1.0s;
            animation-iteration-count: 2;
            
            .icon{
                animation-name: hightlighten-icon;
                animation-duration: 1.0s;
                animation-iteration-count: 2; 
            }

            &::before,
            &::after {
                animation-name: hightlighten-arrow;
                animation-duration: 1.0s;
                animation-iteration-count: 2;
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