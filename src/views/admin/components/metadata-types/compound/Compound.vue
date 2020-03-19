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
        <div>
            <tainacan-form-item
                    v-for="(childItemMetadatum, index) of Object.values(childItemMetadata)[0]"
                    :key="index"
                    :item-metadatum="childItemMetadatum"
                    :is-collapsed="true"
                    @changeCollapse="onChangeCollapse($event, index)"/>
            <template v-if="isMultiple && childItemMetadata.length > 1">
                <transition-group
                        name="filter-item"
                        class="multiple-inputs">
                    <template v-for="(parentMetaIdGroup, groupIndex) of Object.values(childItemMetadata)">
                        <tainacan-form-item
                                v-if="groupIndex > 0"
                                v-for="(childItemMetadatum, index) of parentMetaIdGroup"
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
                childItemMetadata: [],
                childrenMetadataCollapses: [],
            }
        },
        computed: {
            isMultiple() {
                return (this.itemMetadatum.metadatum && this.itemMetadatum.metadatum.multiple == 'yes') ? this.itemMetadatum.metadatum.multiple == 'yes' : false;
            }
        },
        watch: {
            'itemMetadatum.value': {
                handler() {
                    let currentValue = [];
                    
                    if (this.itemMetadatum.value && this.itemMetadatum.value.length) {
                        // Here we load the values from the object, but must also create
                        // empty forms for those not created
                        if (this.itemMetadatum.metadatum &&
                            this.itemMetadatum.metadatum.metadata_type_options &&
                            this.itemMetadatum.metadatum.metadata_type_options.children_objects.length > 0 
                        ) {
                            let lastParentMetaId = 0;
                            
                            for (let i = this.itemMetadatum.value.length - 1; i >= 0; i--) {
                                if (this.itemMetadatum.value[i].parent_meta_id && this.itemMetadatum.value[i].parent_meta_id > 0) {
                                    lastParentMetaId = this.itemMetadatum.value[i].parent_meta_id;
                                    break;
                                }
                            }
                            
                            for (let child of this.itemMetadatum.metadatum.metadata_type_options.children_objects) {
                                const existingValueIndex = this.itemMetadatum.value.findIndex((anItemMetadatum) => anItemMetadatum.metadatum_id == child.id)
                                
                                if (existingValueIndex >= 0)
                                    currentValue.splice(existingValueIndex, 0, {
                                        item: this.itemMetadatum.item,
                                        metadatum: child,
                                        parent_meta_id: this.itemMetadatum.value[existingValueIndex].parent_meta_id,
                                        value: this.itemMetadatum.value[existingValueIndex].value,
                                        value_as_html: this.itemMetadatum.value[existingValueIndex].value_as_html,
                                        value_as_string: this.itemMetadatum.value[existingValueIndex].value_as_string,
                                    });
                                else
                                    currentValue.push({
                                        item: this.itemMetadatum.item,
                                        metadatum: child,
                                        parent_meta_id: lastParentMetaId,
                                        value: '',
                                        value_as_html: '',
                                        value_as_string: ''
                                    })
                            }
                        }

                    } else {
                        // In this situation, we simply create empty forms
                        if (this.itemMetadatum.metadatum &&
                            this.itemMetadatum.metadatum.metadata_type_options &&
                            this.itemMetadatum.metadatum.metadata_type_options.children_objects.length > 0 
                        ) {
                            for (let child of this.itemMetadatum.metadatum.metadata_type_options.children_objects) {
                                let childObject = {
                                    item: this.itemMetadatum.item,
                                    metadatum: child,
                                    parent_meta_id: '0',
                                    value: '',
                                    value_as_html: '',
                                    value_as_string: ''
                                };
                                currentValue.push(childObject)
                            }
                        }
                    }
                    this.childItemMetadata = _.groupBy(currentValue, 'parent_meta_id');
                },
                immediate: true
            },
            childItemMetadata: {
                handler(value) {
                    console.log(value)
                    if (this.itemMetadatum.metadatum &&
                        this.itemMetadatum.metadatum.metadata_type_options &&
                        this.itemMetadatum.metadatum.metadata_type_options.children_objects.length > 0 
                    ) {
                        for (let child of this.itemMetadatum.metadatum.metadata_type_options.children_objects)
                            this.childrenMetadataCollapses.push(true);
                    }
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
                            parent_meta_id: '0',
                            value: '',
                            value_as_html: '',
                            value_as_string: ''
                        };
                        newEmptyGroup.push(childObject)
                    }
                }
                this.childItemMetadata['0'] = newEmptyGroup;
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