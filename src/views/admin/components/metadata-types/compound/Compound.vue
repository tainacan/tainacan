<template>
<div>
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
            :metadatum="child"
            :is-collapsed="childrenMetadataCollapses[index]"
            @changeCollapse="onChangeCollapse($event, index)"/>
</div>
</template>

<script>
    import { mapActions } from 'vuex';
    export default {
        props: {
            metadatum: Object,
            value: [String, Number, Array],
            disabled: false
        },
        data() {
            return {
                childrenMetadatum: [],
                collapseAllChildren: false,
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
                if (this.metadatum.metadatum &&
                    this.metadatum.metadatum.metadata_type_options &&
                    this.metadatum.metadatum.metadata_type_options.children_objects.length > 0 
                ) {
                    this.fetchChildrenMetadata({
                        itemId: this.metadatum.item.id,
                        parentId: this.metadatum.metadatum.id
                    }).then(childrenMetadata => {
                            this.children = childrenMetadata;
                            this.childrenMetadataCollapses = new Array(childrenMetadata.length).fill(true);
                    })
                }
            },
            toggleCollapseAllChildren() {
                this.collapseAllChildren = !this.collapseAllChildren;

                for (let i = 0; i < this.childreMetadataCollapses.length; i++)
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
</style>