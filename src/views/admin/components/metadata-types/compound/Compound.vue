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
    <component
            :is="'tainacan-form-item'"
            v-for="(child, index) in children"
            :key="index"
            :item-metadatum="child"
            :is-collapsed="childrenMetadataCollapses[index]"
            @changeCollapse="onChangeCollapse($event, index)"/>

</div>
</template>

<script>
    import { mapActions } from 'vuex';
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
                childrenMetadataCollapses: [],
            }
        },
        created() {
            this.createChildInputs();
        },
        methods: {
             ...mapActions('item', [
                'fetchChildrenMetadata'
            ]),
            createChildInputs() {
                if (this.itemMetadatum.metadatum &&
                    this.itemMetadatum.metadatum.metadata_type_options &&
                    this.itemMetadatum.metadatum.metadata_type_options.children_objects.length > 0 
                ) {
                    for (let child of this.itemMetadatum.metadatum.metadata_type_options.children_objects) {
                        this.children.push({
                            parent_meta_id: this.itemMetadatum.value[child.id] ? this.itemMetadatum.value[child.id].parent_meta_id : 0,
                            item: this.itemMetadatum.item,
                            metadatum: child,
                            value: this.itemMetadatum.value[child.id] ? this.itemMetadatum.value[child.id].value : [],
                            value_as_html: this.itemMetadatum.value[child.id] ? this.itemMetadatum.value[child.id].value_as_html : '',
                            value_as_string: this.itemMetadatum.value[child.id] ? this.itemMetadatum.value[child.id].value_as_string : ''
                        });
                        this.childrenMetadataCollapses.push(true);
                    }
                }
            },
            toggleCollapseAllChildren() {
                this.collapseAllChildren = !this.collapseAllChildren;

                for (let i = 0; i < this.childrenMetadataCollapses.length; i++)
                    this.childrenMetadataCollapses[i] = this.collapseAllChildren;
            },
            onChangeCollapse(event, index) {
                this.childrenMetadataCollapses.splice(index, 1, event);
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