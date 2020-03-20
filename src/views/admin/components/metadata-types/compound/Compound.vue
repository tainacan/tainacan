<template>
<div class="child-metadata-inputs">
    <a
            class="collapse-all"
            @click="toggleCollapseAllChildren()">
        {{ collapseAllChildren ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
        <span class="icon">
            <i
                    :class="{ 'tainacan-icon-arrowdown' : collapseAllChildren, 'tainacan-icon-arrowright' : !collapseAllChildren }"
                    class="tainacan-icon tainacan-icon-1-25em"/>
        </span>
    </a>
    
    <transition name="filter-item">
        <div v-if="childItemMetadataGroups.length > 0">
            <tainacan-form-item
                    v-for="(childItemMetadatum, index) of childItemMetadataGroups[0]"
                    :key="index"
                    :item-metadatum="childItemMetadatum"
                    :is-collapsed="true"
                    @changeCollapse="onChangeCollapse($event, index)"/>
            <template v-if="isMultiple && childItemMetadataGroups.length > 1">
                <transition-group
                        name="filter-item"
                        class="multiple-inputs">
                    <template v-for="(childItemMetadata, groupIndex) of childItemMetadataGroups">
                        <tainacan-form-item
                                v-if="groupIndex > 0"
                                v-for="(childItemMetadatum, index) of childItemMetadata"
                                :key="groupIndex + '-' + index"
                                :item-metadatum="childItemMetadatum"
                                :is-collapsed="true"
                                @changeCollapse="onChangeCollapse($event, index)"/>
                        <a 
                                v-if="index > 0" 
                                @click="removeValue(index)"
                                class="add-link"
                                :key="groupIndex + '-' + index">
                            <b-icon
                                    icon="minus-circle"
                                    size="is-small"
                                    type="is-secondary"/>
                            &nbsp;{{ $i18n.get('label_remove_value') }}
                        </a>
                    </template>
                </transition-group>
            </template>
            <template v-if="isMultiple">
                <a 
                        @click="addValue"
                        class="is-block add-link">
                    <span class="icon is-small">
                        <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
                    </span>
                    &nbsp;{{ $i18n.get('label_add_value') }}
                </a>
            </template>
        </div>
    </transition>

</div>
</template>

<script>
    export default {
        props: {
            itemMetadatum: Object,
            value: [String, Number, Array],
            disabled: false
        },
        data() {
            return {
                children: [],
                collapseAllChildren: true,
                childItemMetadataGroups: [],
                childrenMetadataCollapses: [],
            }
        },
        computed: {
            isMultiple() {
                return (this.itemMetadatum.metadatum && this.itemMetadatum.metadatum.multiple == 'yes') ? this.itemMetadatum.metadatum.multiple == 'yes' : false;
            }
        },
        watch: {
            /*  This will create the input object structure for 
             *   <tainacan-item-form :item-metadatum="childItemMetadatum" />
             *   looking at the values from the parent (this.itemMetadatum)   
             */  
            'itemMetadatum.value': {
                handler() {
                    let currentChildItemMetadataGroups = [];

                    const parentValues = this.isMultiple ? this.itemMetadatum.value : [ this.itemMetadatum.value ];

                    if (this.itemMetadatum.metadatum &&
                        this.itemMetadatum.metadatum.metadata_type_options &&
                        this.itemMetadatum.metadatum.metadata_type_options.children_objects.length > 0 
                    ) {

                        // Here we load the values from the object, but must also create some
                        if (parentValues && this.itemMetadatum.value.length) {
                            
                            for (let childItemMetadata of parentValues) {
                                let existingChildItemMetadata = [];

                                // Loads the existing values
                                for (let childItemMetadatum of childItemMetadata) {
                                    const childMetadatum = this.itemMetadatum.metadatum.metadata_type_options.children_objects.find((aMetadatum) => aMetadatum.id == childItemMetadatum.metadatum_id);
                                    
                                    existingChildItemMetadata.push({
                                        item: this.itemMetadatum.item,
                                        metadatum: childMetadatum,
                                        parent_meta_id: childItemMetadatum.parent_meta_id,
                                        value: childItemMetadatum.value,
                                        value_as_html: childItemMetadatum.value_as_html,
                                        value_as_string: childItemMetadatum.value_as_string,
                                    })
                                }
                                // If some have empty childs, we need to creat their input
                                if (childItemMetadata.length < this.itemMetadatum.metadatum.metadata_type_options.children_objects.length) {
                                    for (let child of this.itemMetadatum.metadatum.metadata_type_options.children_objects) {
                                        const existingValueIndex = childItemMetadata.findIndex((anItemMetadatum) => anItemMetadatum.metadatum_id == child.id);
                                        if (existingValueIndex < 0) {
                                            const existintParentMetaId = childItemMetadata.findIndex((anItemMetadatum) => anItemMetadatum.parent_meta_id > 0);
                                            existingChildItemMetadata.push({
                                                item: this.itemMetadatum.item,
                                                metadatum: child,
                                                parent_meta_id: existintParentMetaId ? existintParentMetaId : 0,
                                                value: '',
                                                value_as_html: '',
                                                value_as_string: ''
                                            });
                                        }
                                    }
                                }
                                currentChildItemMetadataGroups.push(existingChildItemMetadata)
                            }

                        } else {
                            
                            // In this situation, we simply create empty forms
                            let currentChildItemMetadata = [];

                            // A new input for each type of child metadatum
                            for (let child of this.itemMetadatum.metadatum.metadata_type_options.children_objects) {
                                let childObject = {
                                    item: this.itemMetadatum.item,
                                    metadatum: child,
                                    parent_meta_id: '0',
                                    value: '',
                                    value_as_html: '',
                                    value_as_string: ''
                                };
                                currentChildItemMetadata.push(childObject)
                            }
                            currentChildItemMetadataGroups.push(currentChildItemMetadata);
                        }
                    }
                    
                    this.childItemMetadataGroups = currentChildItemMetadataGroups;
                },
                immediate: true
            }
        },
        methods: {
            toggleCollapseAllChildren() {
                this.collapseAllChildren = !this.collapseAllChildren;

                for (let i = 0; i < this.childrenMetadataCollapses.length; i++)
                    this.childrenMetadataCollapses[i] = this.collapseAllChildren;
            },
            onChangeCollapse(event, index) {
                this.childrenMetadataCollapses.splice(index, 1, event);
            },
            addValue(){
                // Create a new placeholder parent_meta_id group here.
                let newEmptyGroup = [];

                if (this.itemMetadatum.metadatum &&
                    this.itemMetadatum.metadatum.metadata_type_options &&
                    this.itemMetadatum.metadatum.metadata_type_options.children_objects.length > 0 
                ) {
                    for (let child of this.itemMetadatum.metadatum.metadata_type_options.children_objects) {
                        let childObject = {
                            item: this.itemMetadatum.item,
                            metadatum: child,
                            parent_meta_id: 0,
                            value: '',
                            value_as_html: '',
                            value_as_string: ''
                        };
                        newEmptyGroup.push(childObject)
                    }
                }
                this.childItemMetadataGroups.push(newEmptyGroup);
            },
            removeValue(index) {
                // Remove the whole parent_meta_id group here.
            }
        }
    }
</script>

<style lang="scss" scoped>
    @import '../../../admin/scss/_variables.scss';

    .child-metadata-inputs {
        margin-left: -30px;
        padding-left: 38px;
        padding-top: 5px;
        border-left: 1px solid var(--tainacan-gray2);

        .collapse-all {
            margin-left: -8px;
            font-size: 0.75em;
        }
        .field {
            padding-right: 0;
            margin-left: 3px;
            margin-bottom: 0.875em;
        }
        .field:last-child {
            border-bottom: none;
        }
    }
</style>