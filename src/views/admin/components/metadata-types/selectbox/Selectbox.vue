<template>
    <b-select
            expanded
            :disabled="disabled"
            :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
            :placeholder="$i18n.get('label_selectbox_init')"
            :value="value"
            @input="onSelected($event)">
        <option value="">{{ $i18n.get('label_selectbox_init') }}...</option>
        <option
                v-for="(option, index) in getOptions"
                :key="index"
                :label="option"
                :value="option">
            {{ option }}
        </option>
    </b-select>
</template>

<script>
    export default {
        props: {
            itemMetadatum: Object,
            value: [String, Number, Array],
            disabled: false,
        },
        computed: {
            getOptions(){
                if (this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.options ) {
                    const metadata = this.itemMetadatum.metadatum.metadata_type_options.options;
                    return ( metadata ) ? metadata.split("\n") : [];
                }
                return [];
            }
        },
        methods: {
            onSelected(value) {
                this.$emit('input', value);
            }
        }
    }
</script>