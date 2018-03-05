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
                        :options="{group: { name:'fields', pull: false, put: true }, chosenClass: 'sortable-chosen', filter: '.not-sortable-item'}">
                        <div 
                            class="active-field-item" 
                            :class="{'not-sortable-item': field.id == undefined || isRepositoryLevel}" 
                            v-for="(field, index) in activeFieldList" :key="index">
                            {{ field.name }}
                            <span class="label-details"><span class="loading-spinner" v-if="field.id == undefined"></span> <b-tag v-if="field.status != undefined">{{field.status}}</b-tag></span>
                            <a @click.prevent="removeField(field)" v-if="field.id != undefined"><b-icon icon="delete"></b-icon></a>
                            <b-icon icon="pencil" v-if="field.id != undefined"></b-icon>
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

export default {
    name: 'FieldsList',
    data(){           
        return {
            collectionId: '',
            isRepositoryLevel: false,
            isDraggingFromAvailable: false,
            isLoadingFieldTypes: true,
            isLoadingFields: false
        }
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
                cursor: wait;
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




