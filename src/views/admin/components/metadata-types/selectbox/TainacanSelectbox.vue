<template>
    <div>
        <input 
                type="text"
                aria-hidden="true"
                class="is-special-hidden-for-mobile"
                autocomplete="on"
                @focus="onMobileSpecialFocus">
        <b-select
                :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
                expanded
                :disabled="disabled"
                :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : $i18n.get('label_selectbox_init')"
                :model-value="getUnescapedLabel(value)"
                :class="{ 'has-placeholder-selected': value === '' }"
                @update:model-value="onSelected($event)">
            <option value="">
                {{ itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : ($i18n.get('label_selectbox_init') + '...') }}
            </option>
            <option
                    v-for="(option, index) in getOptions"
                    :key="index"
                    :label="option"
                    :value="option">
                {{ getUnescapedLabel(option) }}
            </option>
        </b-select>
    </div>
</template>

<script>
    export default {
        props: {
            itemMetadatum: Object,
            value: [String, Number, Array],
            disabled: false,
        },
        emits: [
            'update:value',
            'mobile-special-focus'
        ],
        computed: {
            getOptions() {
                if (this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.options ) {
                    const metadata = this.itemMetadatum.metadatum.metadata_type_options.options;
                    return ( metadata ) ? metadata.split("\n") : [];
                }
                return [];
            }
        },
        methods: {
            getUnescapedLabel(label) {
                return typeof _.unescape === 'function' ? _.unescape(label) : label;
            },
            onSelected(value) {
                this.$emit('update:value', value);
            },
            onMobileSpecialFocus($event) {
                $event.target.blur();
                this.$emit('mobile-special-focus');
            }
        }
    }
</script>