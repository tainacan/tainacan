<template>
    <div>
        <b-loading :active.sync="isLoadingFieldTypes"/>
        <tainacan-title v-if="!isRepositoryLevel"/>
        <p v-if="isRepositoryLevel">{{ $i18n.get('info_repository_metadata_inheritance') }}</p>
        <br>
        <b-tabs v-model="activeTab">    
            <b-tab-item :label="$i18n.get('fields')">
                <div class="columns">
                    <div class="column"> 
                        <section 
                                v-if="activeFieldList.length <= 0 && !isLoadingFields"
                                class="field is-grouped-centered section">
                            <div class="content has-text-gray has-text-centered">
                                <p>
                                    <b-icon
                                            icon="format-list-bulleted-type"
                                            size="is-large"/>
                                </p>
                                <p>{{ $i18n.get('info_there_is_no_field' ) }}</p>  
                                <p>{{ $i18n.get('info_create_metadata' ) }}</p>
                            </div>
                        </section>             
                        <draggable 
                                v-model="activeFieldList"
                                class="active-fields-area"
                                @change="handleChange"
                                :class="{'fields-area-receive': isDraggingFromAvailable}" 
                                :options="{ 
                                        group: { name:'fields', pull: false, put: true }, 
                                        sort: openedFieldId == '' || openedFieldId == undefined, 
                                        disabled: openedFieldId != '' && openedFieldId != undefined,
                                        handle: '.handle', 
                                        ghostClass: 'sortable-ghost',
                                        filter: 'not-sortable-item', 
                                        animation: '250'}">
                            <div  
                                    class="active-field-item"
                                    :class="{
                                        'not-sortable-item': field.id == undefined || openedFieldId != '' , 
                                        'not-focusable-item': openedFieldId == field.id, 
                                        'disabled-field': field.enabled == false
                                    }" 
                                    v-for="(field, index) in activeFieldList" 
                                    :key="index">
                                <div class="handle">
                                    <grip-icon/>
                                    <span 
                                            class="field-name" 
                                            :class="{'is-danger': formWithErrors == field.id }">
                                            {{ field.name }}
                                    </span>
                                    <span   
                                            v-if="field.id != undefined"
                                            class="label-details">  
                                        ({{ $i18n.get(field.field_type_object.component) }}) <em>{{ (field.collection_id != collectionId) ? $i18n.get('label_inherited') : '' }}</em>   
                                            <span 
                                                class="not-saved" 
                                                v-if="(editForms[field.id] != undefined && editForms[field.id].saved != true) || field.status == 'auto-draft'"> 
                                            {{ $i18n.get('info_not_saved') }}
                                            </span>
                                    </span>
                                    <span 
                                            class="loading-spinner" 
                                            v-if="field.id == undefined"/>
                                    <span 
                                            class="controls" 
                                            v-if="field.id !== undefined">
                                        <b-switch 
                                                size="is-small" 
                                                v-model="field.enabled" 
                                                @input="onChangeEnable($event, index)"/>
                                        <a 
                                                :style="{ visibility: 
                                                        field.collection_id != collectionId 
                                                        ? 'hidden' : 'visible'
                                                    }" 
                                                @click.prevent="editField(field)">
                                            <b-icon 
                                                    type="is-gray" 
                                                    icon="pencil"/>
                                        </a>
                                        <a 
                                                :style="{ visibility: 
                                                        field.collection_id != collectionId || 
                                                        field.field_type == 'Tainacan\\Field_Types\\Core_Title' || 
                                                        field.field_type == 'Tainacan\\Field_Types\\Core_Description' 
                                                        ? 'hidden' : 'visible'
                                                    }" 
                                                @click.prevent="removeField(field)">
                                            <b-icon 
                                                    type="is-gray" 
                                                    icon="delete"/>
                                        </a>
                                    </span>
                                </div>
                                <div v-if="openedFieldId == field.id">
                                    <field-edition-form   
                                            :collection-id="collectionId"
                                            :is-repository-level="isRepositoryLevel"
                                            @onEditionFinished="onEditionFinished()"
                                            @onEditionCanceled="onEditionCanceled()"
                                            @onErrorFound="formWithErrors = field.id"
                                            :index="index"
                                            :original-field="field"
                                            :edited-field="editForms[field.id]"/>
                                </div>
                            </div>
                        </draggable> 
                    </div>
                
                    <div class="column available-fields-area" >
                        <div class="field">
                            <h3 class="label">{{ $i18n.get('label_available_field_types') }}</h3>
                            <draggable 
                                    v-model="availableFieldList" 
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
                                <grip-icon/>  
                                    <span class="field-name">{{ field.name }}</span> 
                                    <span 
                                            class="loading-spinner" 
                                            v-if="hightlightedField == field.name"/>   
                                </div>
                            </draggable>
                        </div>
                    </div> 
                </div>
            </b-tab-item>
            <!-- Exposer -->
            <b-tab-item :label="$i18n.get('mapping')">
                <p>Under construction. You will be able to map your metadata to other metadata standards in this page.</p>
            </b-tab-item>
        </b-tabs>
    </div> 
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import GripIcon from '../other/grip-icon.vue';
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
            formWithErrors: '',
            hightlightedField: '',
            editForms: {}
        }
    },
    components: {
        FieldEditionForm,
        GripIcon
    },
    computed: {
        availableFieldList: {
            get() {
                return this.getFieldTypes();
            },
            set(value) {
                return this.updateFieldTypes(value);
            }
        },
        activeFieldList: {
            get() {
                return this.getFields();
            },
            set(value) {
                this.updateFields(value);
            }
        }
    },
    beforeRouteLeave ( to, from, next ) {
        let hasUnsavedForms = false;
        for (let editForm in this.editForms) {
            if (!this.editForms[editForm].saved) 
                hasUnsavedForms = true;
        }
        if ((this.openedFieldId != '' && this.openedFieldId != undefined) || hasUnsavedForms ) {
            this.$dialog.confirm({
                message: this.$i18n.get('info_warning_fields_not_saved'),
                    onConfirm: () => {
                        this.onEditionCanceled();
                        next();
                    },
                    cancelText: this.$i18n.get('cancel'),
                    confirmText: this.$i18n.get('continue'),
                    type: 'is-secondary'
                });  
        } else {
            next()
        }  
    },
    methods: {
        ...mapActions('fields', [
            'fetchFieldTypes',
            'updateFieldTypes',
            'fetchFields',
            'sendField',
            'deleteField',
            'updateFields',
            'updateCollectionFieldsOrder'
        ]),
        ...mapGetters('fields',[
            'getFieldTypes',
            'getFields'
        ]),
        handleChange(event) {     
            if (event.added) {
                this.addNewField(event.added.element, event.added.newIndex);
            } else if (event.removed) {
                this.removeField(event.removed.element);
            } else if (event.moved) {
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
                this.$console.error(error);
            });
        },
        removeField(removedField) {
            this.deleteField({ collectionId: this.collectionId, fieldId: removedField.id, isRepositoryLevel: this.isRepositoryLevel})
            .then(() => {
                if (!this.isRepositoryLevel)
                    this.updateFieldsOrder(); 
            })
            .catch(() => {
            });
        },
        editField(field) {
            // Closing collapse
            if (this.openedFieldId == field.id) {    
                this.openedFieldId = '';

            // Opening collapse
            } else {

                this.openedFieldId = field.id;
                // First time opening
                if (this.editForms[this.openedFieldId] == undefined) {
                    this.editForms[this.openedFieldId] = JSON.parse(JSON.stringify(field));
                    this.editForms[this.openedFieldId].saved = true;  

                    // Field inserted now
                    if (this.editForms[this.openedFieldId].status == 'auto-draft') {
                        this.editForms[this.openedFieldId].status = 'publish'; 
                        this.editForms[this.openedFieldId].saved = false;
                    }
                }     
            }
        },
        onEditionFinished() {
            this.formWithErrors = '';
            delete this.editForms[this.openedFieldId];
            this.openedFieldId = '';
        },
        onEditionCanceled() {
            this.formWithErrors = '';
            delete this.editForms[this.openedFieldId];
            this.openedFieldId = '';
        }
    },
    created() {
        this.isLoadingFieldTypes = true;
        this.isLoadingFields = true;

        this.fetchFieldTypes()
            .then(() => {
                this.isLoadingFieldTypes = false;
            })
            .catch(() => {
                this.isLoadingFieldTypes = false;
            });

        this.isRepositoryLevel = this.$route.name == 'FieldsPage' ? true : false;
        if (this.isRepositoryLevel)
            this.collectionId = 'default';
        else
            this.collectionId = this.$route.params.collectionId;
        

        this.fetchFields({collectionId: this.collectionId, isRepositoryLevel: this.isRepositoryLevel, isContextEdit: true})
            .then(() => {
                this.isLoadingFields = false;
            })
            .catch(() => {
                this.isLoadingFields = false;
            });
    },
    mounted() {
        if (!this.isRepositoryLevel) {
            document.getElementById('collection-page-container').addEventListener('scroll', ($event) => {
                this.$emit('onShrinkHeader', ($event.originalTarget.scrollTop > 53)); 
            });
        }
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
        margin: 1em 0em 2.0em 0em;
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

        .active-field-item {
            background-color: white;
            padding: 0.7em 0.9em;
            margin: 4px;
            min-height: 40px;
            display: block; 
            position: relative;
            cursor: grab;
            opacity: 1 !important;
            
            .handle {
                padding-right: 6em;
            }
            .grip-icon { 
                fill: $gray; 
                top: 2px;
                position: relative;
            }
            .field-name {
                text-overflow: ellipsis;
                overflow-x: hidden;
                white-space: nowrap;
                font-weight: bold;
                margin-left: 0.4em;
                margin-right: 0.4em;

                &.is-danger {
                    color: $danger !important;
                }
            }
            .label-details {
                font-weight: normal;
                color: $gray;
            }
            .not-saved {
                font-style: italic;
                font-weight: bold;
                color: $danger;
            }
            .controls { 
                position: absolute;
                right: 5px;
                top: 10px;
                .switch {
                    position: relative;
                    bottom: 3px;
                }
                .icon {
                    bottom: 1px;   
                    position: relative;
                    i, i:before { font-size: 20px; }
                }
            }
    
            &.not-sortable-item, &.not-sortable-item:hover {
                cursor: default;
                background-color: white !important;

                .handle .label-details, .handle .icon {
                    color: $gray !important;
                }
            } 
            &.not-focusable-item, &.not-focusable-item:hover {
                cursor: default;
               
                .field-name {
                    color: $secondary;
                }
                .handle .label-details, .handle .icon {
                    color: $gray !important;
                }
            }
            &.disabled-field {
                color: $gray;
            }    
        }
        .active-field-item:hover:not(.not-sortable-item) {
            background-color: $secondary;
            border-color: $secondary;
            color: white !important;

            .label-details, .icon, .not-saved {
                color: white !important;
            }

            .grip-icon { 
                fill: white; 
            }

            .switch.is-small {
                input[type="checkbox"] + .check {
                    background-color: $secondary !important;
                    border: 1.5px solid white !important;
                    &::before { background-color: white !important; }
                } 
                input[type="checkbox"]:checked + .check {
                    border: 1.5px solid white !important;
                    &::before { background-color: white !important; }
                }
                &:hover input[type="checkbox"] + .check {
                    border: 1.5px solid white !important;
                    background-color: $secondary !important;
                }
            }
        }
        .sortable-ghost {
            border: 1px dashed $draggable-border-color;
            display: block;
            padding: 0.7em 0.9em;
            margin: 4px;
            height: 40px;
            position: relative;

            .grip-icon { 
                fill: white; 
            }
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
            line-height: 1.3em;
            height: 40px;
            position: relative;
            border: 1px solid $draggable-border-color;
            border-radius: 1px;
            transition: left 0.2s ease;
            
            .grip-icon { 
                fill: $gray;
                top: -3px;
                position: relative;
                display: inline-block;
            }
            .icon {
                position: relative;
                bottom: 1px;
            }
            .field-name {
                text-overflow: ellipsis;
                overflow-x: hidden;
                white-space: nowrap;
                font-weight: bold;
                margin-left: 0.4em;
                display: inline-block;
                max-width: 200px;
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
                border-top-width: 20px;
                border-bottom-width: 20px;
                left: -19px;
            }
            &:before {
                top: -1px;
                border-color: transparent $draggable-border-color transparent transparent;
                border-right-width: 16px;
                border-top-width: 20px;
                border-bottom-width: 20px;
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
            0%   { fill: #b1b1b1; }
            25%  { fill: white; }
            75%  { fill: white; }
            100% { fill: #b1b1b1; }
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
            
            .grip-icon{
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
          
            .grip-icon { 
                fill: white;
            }
            
        }
    }

</style>