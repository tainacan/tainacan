<template>
    <b-numberinput
            :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
            :ref="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
            :disabled="disabled"
            :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : ''"
            :model-value="value === 0 || value ? Number(value) : null"
            :data-is-danger="!isInputValid"
            lang="en"
            :min="getMin"
            :max="getMax"
            :step="getStep"
            @update:model-value="$event =>onInput($event)"
            @blur="onBlur"
            @focus="onMobileSpecialFocus" />
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
            'blur',
            'mobile-special-focus'
        ],
        data() {
            return {
                isInputValid: true
            }
        },
        computed: {
            getStep() {
                if (this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.step)
                    return this.itemMetadatum.metadatum.metadata_type_options.step;
                else
                    return 0.01;
            },
            getMin() {
                if (this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.min !== null && this.itemMetadatum.metadatum.metadata_type_options.min !== undefined && this.itemMetadatum.metadatum.metadata_type_options.min !== '')
                    return Number(this.itemMetadatum.metadatum.metadata_type_options.min);
                else
                    return undefined;
            },
            getMax() {
                if (this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.max !== null && this.itemMetadatum.metadatum.metadata_type_options.max !== undefined && this.itemMetadatum.metadatum.metadata_type_options.max !== '')
                    return Number(this.itemMetadatum.metadatum.metadata_type_options.max);
                else
                    return undefined;
            }
        },
        methods: {
            onInput(value) {
                const inputRef = this.$refs['tainacan-item-metadatum_id-' + this.itemMetadatum.metadatum.id + (this.itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + this.itemMetadatum.parent_meta_id) : '')];
                
                if ( inputRef ) {
                    this.isInputValid = inputRef.checkHtml5Validity();

                    if ( !this.isInputValid )
                        return;
                }
                
                // Allowing empty value as a state different of 0
                if ( value === null || value === undefined || value === '' )
                    value = '';
                else
                    value = Number(value);

                this.$emit('update:value', value);
            },
            onBlur() {
                this.$emit('blur');
            },
            onMobileSpecialFocus() {
                this.$emit('mobile-special-focus');
            }
        }
    }
</script>

<style scoped>
    .b-numberinput {
        border-bottom-width: 0px !important;
        margin-left: 0 !important;
    }
</style>
