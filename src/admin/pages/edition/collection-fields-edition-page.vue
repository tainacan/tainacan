<template>
    <div>
        <h1>Collection Fields Edition Page</h1>
        <b-loading :active.sync="isLoadingFieldTypes"></b-loading>
        <div class="columns">
            <div class="column">
                <b-field :label="$i18n.get('label_active_fields')" is-grouped>
                    <draggable class="box active-fields-area" @change="handleChange" :class="{'fields-area-receive': isDraggingFromAvailable}" :list="activeFieldList" :options="{group:'fields', chosenClass: 'sortable-chosen'}">
                        <div class="active-field-item" v-for="(field, index) in activeFieldList" :key="index">
                            {{ field.name }}<span class="label-details"> (not configured)</span><a @click.prevent="removeField(field)"><b-icon is-small icon="delete"></b-icon></a><b-icon is-small icon="pencil"></b-icon>
                        </div>
                        <div slot="footer">Drag and drop Fields here to add them to Collection.</div>
                    </draggable> 
                </b-field>
            </div>
            <div class="column">
                <b-field :label="$i18n.get('label_available_fields')" is-grouped>
                    <draggable class="box available-fields-area" :list="availableFieldList" :options="{ group: { name:'fields', pull: 'clone', put: 'false', revertClone: 'true' }}">
                        <div class="available-field-item" v-for="(field, index) in availableFieldList" :key="index">
                            {{ field.name }}
                        </div>
                    </draggable> 
                </b-field>
            </div>
        </div>
    </div> 
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'CollectionFieldsEditionPage',
    data(){           
        return {
            collectionId: '',
            isDraggingFromAvailable: false,
            isLoadingFieldTypes: true,
            isLoadingFields: false
        }
    },
    methods: {
        ...mapActions('collection', [
            'fetchFieldTypes',
            'fetchFields',
            'sendField',
            'deleteField'
        ]),
        ...mapGetters('collection',[
            'getFieldTypes',
            'getFields'
        ]),
        handleChange($event) {
            if ($event.added) {
                this.addNewField($event.added.element);
            } else if ($event.removed) {
                this.removeField($event.removed.element);
            } else if ($event.moved) {
                console.log($event.moved.element);
            } 
        },
        addNewField(newField) {
            this.sendField({collectionId: this.collectionId, name: newField.name, fieldType: newField.className, status: 'publish'})
            .then((res) => {

            })
            .catch((error) => {
            });
        },
        removeField(removedField) {
            this.deleteField({ collectionId: this.collectionId, fieldId: removedField.id })
            .then((field) => {
                let index = this.activeFieldList.findIndex(deletedItem => deletedItem.id === field.id);
                if (index >= 0) {  
                    this.activeFieldList.splice(index, 1);
                }
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

        this.collectionId = this.$route.params.id;
        
        this.fetchFieldTypes()
            .then((res) => {
                this.isLoadingFieldTypes = false;
            })
            .catch((error) => {
                this.isLoadingFieldTypes = false;
            });
        this.fetchFields(this.collectionId)
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

            &.is-loading:after {
                animation: spinAround 500ms infinite linear;
                border: 2px solid #dbdbdb;
                border-radius: 290486px;
                border-right-color: transparent;
                border-top-color: transparent;
                content: "";
                display: block;
                height: 1em;
                width: 1em;
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
            border: 3px dashed $primary-light;
            display: block; 
        }
    }

    .available-fields-area {
        padding: 10px;
        border: 1px dashed gray;
        border-radius: 5px;
        background-color: whitesmoke;

        .available-field-item {
            padding: 0.2em 0.5em;
            margin: 10px;
            border-radius: 5px;
            background-color: white;
            border: 1px solid gainsboro;
            display: inline-flex;
            cursor: grab;
        }
        .available-field-item:hover {
            border: 1px solid lightgrey;
            box-shadow: 0px 0px 2px #777;
        }
    }
</style>


