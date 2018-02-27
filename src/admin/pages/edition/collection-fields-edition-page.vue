<template>
    <div>
        <h1>Collection Fields Edition Page</h1>
        <div class="columns">
            <div class="column">
                <b-field :label="$i18n.get('label_active_fields')" is-grouped>
                    <draggable class="active-fields-area" :class="{'fields-receive': isDraggingFromAvailable}" :list="activeFieldList" :options="{group:'fields'}">
                        <div class="field-item active" v-for="(field, index) in activeFieldList" :key="index">{{field}} <b-icon is-small icon="pencil"></b-icon></div>
                        <div slot="footer">Drag and drop Fields here to add them to Collection.</div>
                    </draggable> 
                </b-field>
            </div>
            <div class="column">
                <b-field :label="$i18n.get('label_available_fields')" is-grouped>
                    <draggable class="available-fields-area" :list="availableFieldList" @start="isDraggingFromAvailable=true" @end="isDraggingFromAvailable=false" :options="{ group: { name:'fields', pull: 'clone', put: 'false' }}">
                        <div class="field-item" v-for="(field, index) in availableFieldList" :key="index">{{field}}</div>
                    </draggable> 
                </b-field>
            </div>
        </div>
    </div> 
</template>

<script>
export default {
    name: 'CollectionFieldsEditionPage',
    data(){
        return {
            availableFieldList: ['text', 'textarea', 'numeric', 'select', 'radio', 'checkbox', 'relationship', 'categories'],
            activeFieldList: [],
            isDraggingFromAvailable: false
        }
    }
}
</script>

<style lang="scss" scoped>
    .available-fields-area {
        padding: 10px;
        border: 1px dashed grey;
        border-radius: 5px;
        background-color: ghostwhite;
    }
    .active-fields-area {
        min-height: 40px;
        padding: 10px;
        border: 1px solid grey;
        border-radius: 5px;
    }
    .fields-receive {
        background-color: whitesmoke;
        border: 1px dashed grey;
    }
    .field-item {
        padding: 0.2em 0.5em;
        margin: 10px;
        border-radius: 5px;
        color: white;
        display: inline-block;
        background-color: darkslategrey;
    }

    .active {
        display: block;
        background-color: lightslategray;

        .icon { float: right }
    }
</style>


