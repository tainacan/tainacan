<template>
    <checkbox-radio-modal
            :is-modal="false"
            :is-filter="false"
            :parent="0"
            :taxonomy_id="taxonomyId"
            :selected="!value ? [] : value"
            :metadatum-id="itemMetadatum.metadatum.id"
            :taxonomy="taxonomy"
            :collection-id="itemMetadatum.metadatum.collection_id"
            :is-taxonomy="true"
            :query="''"
            :metadatum="itemMetadatum.metadatum"
            :is-checkbox="true"
            @input="(selected) => {
                value = selected;
                $emit('input', value);
            }"
        />
</template>

<script>
    import CheckboxRadioModal from '../../modals/checkbox-radio-modal.vue';

    export default {
        components: {
            CheckboxRadioModal
        },
        props: {
            value: [ Number, String, Array ],
            disabled: false,
            taxonomyId: Number,
            itemMetadatum: Object
        },
        data() {
            return {
            }
        },
        watch: {
            value(val){
                this.$console.log(val)
            }
        },
        created() {
            this.$parent.$on('update-taxonomy-inputs', ($event) => {
                if ($event.taxonomyId == this.taxonomyId && $event.metadatumId == this.itemMetadatum.metadatum.id) {
                    this.$console.log('opaaa')
                }
            });
        }
    }
</script>

<style scoped lang="scss">
    .selected-tags {
        margin-top: 0.75em;
        font-size: 0.75em;
        position: relative;
    }
    .selected-tags .is-loading {
        margin-left: 2em;
        margin-top: -0.4em;
    }
    .selected-tags .is-loading::after {
        border: 2px solid var(--tainacan-gray4) !important;
        border-right-color: var(--tainacan-gray2) !important;
        border-top-color: var(--tainacan-gray2) !important;
    } 
    .metadata-taxonomy-list {
        column-count: 2;
        margin: 10px;

        label {
            break-inside: avoid;
            padding-right: 10px;
        }
    }
    .view-all {
        color: var(--tainacan-gray4);
        margin-bottom: 20px;
        font-size: 0.75em;
    }
</style>
