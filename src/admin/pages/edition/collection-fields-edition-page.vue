<template>
    <div>
        <h1>Collection Fields Edition Page</h1>
        <b-loading :active.sync="isLoadingFieldTypes"></b-loading>
        <div class="columns">
            <div class="column">
                <b-field :label="$i18n.get('label_active_fields')" is-grouped>
                    <draggable class="active-fields-area" :class="{'fields-receive': isDraggingFromAvailable}" :list="activeFieldList" :options="{group:'fields'}">
                        <div class="field-item active" v-for="(field, index) in activeFieldList" :key="index">
                            <label class="label">{{ field.name }} <b-icon is-small icon="pencil"></b-icon><b-icon is-small icon="delete"></b-icon></label>
                            <b-field>
                                <component :is="field.component"></component>
                            </b-field> 
                        </div>
                        <div slot="footer">Drag and drop Fields here to add them to Collection.</div>
                    </draggable> 
                </b-field>
            </div>
            <div class="column">
                <b-field :label="$i18n.get('label_available_fields')" is-grouped>
                    <draggable class="available-fields-area" :list="availableFieldList" @start="isDraggingFromAvailable=true" @end="isDraggingFromAvailable=false" :options="{ group: { name:'fields', pull: 'clone', put: 'false' }}">
                        <div class="field-item" v-for="(field, index) in availableFieldList" :key="index">
                            <b-field :label="field.name">
                                <component :is="field.component"></component>
                            </b-field>
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
            //availableFieldList: ['text', 'textarea', 'numeric', 'select', 'radio', 'checkbox', 'relationship', 'categories'],
            activeFieldList: [],
            isDraggingFromAvailable: false,
            isLoadingFieldTypes: true
        }
    },
    methods: {
        ...mapActions('collection', [
            'fetchFieldTypes'
        ]),
        ...mapGetters('collection',[
            'getFieldTypes'
        ]),
    },
    computed: {
        availableFieldList() {
            return this.getFieldTypes();
        }
    },
    created() {
        this.isLoadingFieldTypes = true;
        this.fetchFieldTypes()
            .then((res) => {
                //console.log(res);
                this.isLoadingFieldTypes = false;
            })
            .catch((error) => {
                this.isLoadingFieldTypes = false;
            });
    }
}
</script>

<style lang="scss" scoped>
    .available-fields-area {
        padding: 10px;
        border: 1px dashed gray;
        border-radius: 5px;
        background-color: ghostwhite;
    }
    .active-fields-area {
        min-height: 40px;
        padding: 10px;
        border: 1px solid gray;
        border-radius: 5px;
    }
    .fields-receive {
        background-color: whitesmoke;
        border: 1px dashed gray;
    }
    .field-item {
        padding: 0.2em 0.5em;
        margin: 10px;
        border-radius: 5px;
        display: inline-block;
        border: 1px solid gray;
    }
    .field-item:hover {
        border: 1px dashed gray;
    }

    .active {
        display: block;
        .icon { float: right }
    }
</style>


