<template>
    <div>
        <input 
                type="text"
                aria-hidden="true"
                tabindex="-1"
                class="is-special-hidden-for-mobile"
                autocomplete="on"
                @focus="onMobileSpecialFocus">

        <!-- Selectbox (default) -->
        <b-select
                v-if="getComponent === 'tainacan-selectbox'"
                :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
                expanded
                :disabled="disabled"
                :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : $i18n.get('label_selectbox_init')"
                :model-value="getUnescapedLabel(singleValue)"
                :class="{ 'has-placeholder-selected': singleValue === '' }"
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

        <!-- Radio -->
        <div
                v-else-if="getComponent === 'tainacan-selectbox-radio'"
                class="tainacan-selectbox-options-list">
            <b-radio
                    v-for="(option, index) in getOptions"
                    :id="index === 0 ? ('tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')) : undefined"
                    :key="index"
                    v-model="localSingleValue"
                    :native-value="option"
                    :name="'tainacan-selectbox-radio-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('-' + itemMetadatum.parent_meta_id) : '')"
                    :disabled="disabled"
                    @update:model-value="onSelected($event)">
                {{ getUnescapedLabel(option) }}
            </b-radio>
        </div>

        <!-- Checkbox -->
        <div
                v-else-if="getComponent === 'tainacan-selectbox-checkbox'"
                class="tainacan-selectbox-options-list">
            <b-checkbox
                    v-for="(option, index) in getOptions"
                    :id="index === 0 ? ('tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')) : undefined"
                    :key="index"
                    v-model="localMultipleValue"
                    :native-value="option"
                    :disabled="disabled || isCheckboxDisabled(option)"
                    @update:model-value="onMultipleSelected($event)">
                {{ getUnescapedLabel(option) }}
            </b-checkbox>
        </div>

        <!-- Radio Button -->
        <div
                v-else-if="getComponent === 'tainacan-selectbox-radio-button'"
                class="tainacan-selectbox-options-list is-buttons">
            <b-radio-button
                    v-for="(option, index) in getOptions"
                    :id="index === 0 ? ('tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')) : undefined"
                    :key="index"
                    v-model="localSingleValue"
                    type="is-primary"
                    :native-value="option"
                    :name="'tainacan-selectbox-radio-button-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('-' + itemMetadatum.parent_meta_id) : '')"
                    :disabled="disabled"
                    @update:model-value="onSelected($event)">
                <span>{{ getUnescapedLabel(option) }}</span>
            </b-radio-button>
        </div>

        <!-- Checkbox Button -->
        <div
                v-else-if="getComponent === 'tainacan-selectbox-checkbox-button'"
                class="tainacan-selectbox-options-list is-buttons">
            <b-checkbox-button
                    v-for="(option, index) in getOptions"
                    :id="index === 0 ? ('tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')) : undefined"
                    :key="index"
                    v-model="localMultipleValue"
                    type="is-primary"
                    :native-value="option"
                    :disabled="disabled || isCheckboxDisabled(option)"
                    @update:model-value="onMultipleSelected($event)">
                <span>{{ getUnescapedLabel(option) }}</span>
            </b-checkbox-button>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            itemMetadatum: Object,
            value: [String, Number, Array],
            disabled: false,
            forcedComponentType: '',
        },
        emits: [
            'update:value',
            'mobile-special-focus'
        ],
        data() {
            return {
                localSingleValue: '',
                localMultipleValue: []
            }
        },
        computed: {
            getComponent() {
                if (this.forcedComponentType)
                    return this.forcedComponentType;
                else if (
                    this.itemMetadatum &&
                    this.itemMetadatum.metadatum &&
                    this.itemMetadatum.metadatum.metadata_type_options &&
                    this.itemMetadatum.metadatum.metadata_type_options.input_type
                )
                    return this.itemMetadatum.metadatum.metadata_type_options.input_type;

                return 'tainacan-selectbox';
            },
            getOptions() {
                if (this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.options ) {
                    const metadata = this.itemMetadatum.metadatum.metadata_type_options.options;
                    return ( metadata ) ? metadata.split("\n") : [];
                }
                return [];
            },
            isMultipleInput() {
                return this.getComponent === 'tainacan-selectbox-checkbox' || this.getComponent === 'tainacan-selectbox-checkbox-button';
            },
            singleValue() {
                if ( Array.isArray(this.value) )
                    return this.value.length ? this.value[0] : '';
                return this.value !== undefined && this.value !== null ? this.value : '';
            },
            maxMultipleValues() {
                return (
                    this.itemMetadatum &&
                    this.itemMetadatum.metadatum &&
                    this.itemMetadatum.metadatum.cardinality &&
                    !isNaN(this.itemMetadatum.metadatum.cardinality) &&
                    this.itemMetadatum.metadatum.cardinality > 1
                ) ? this.itemMetadatum.metadatum.cardinality : undefined;
            }
        },
        watch: {
            value: {
                handler(val) {
                    this.syncLocalValues(val);
                },
                immediate: true
            }
        },
        methods: {
            syncLocalValues(val) {
                if ( this.isMultipleInput ) {
                    this.localMultipleValue = Array.isArray(val) ? val.slice(0) : ( val ? [val] : [] );
                } else {
                    this.localSingleValue = Array.isArray(val) ? ( val.length ? val[0] : '' ) : ( val !== undefined && val !== null ? val : '' );
                }
            },
            getUnescapedLabel(label) {
                return typeof _.unescape === 'function' ? _.unescape(label) : label;
            },
            isCheckboxDisabled(option) {
                if ( this.maxMultipleValues === undefined )
                    return false;
                const isSelected = Array.isArray(this.localMultipleValue) && this.localMultipleValue.indexOf(option) >= 0;
                return !isSelected && this.localMultipleValue.length >= this.maxMultipleValues;
            },
            onSelected(value) {
                this.$emit('update:value', value);
            },
            onMultipleSelected(value) {
                this.$emit('update:value', Array.isArray(value) ? value : []);
            },
            onMobileSpecialFocus($event) {
                $event.target.blur();
                this.$emit('mobile-special-focus');
            }
        }
    }
</script>

<style lang="scss" scoped>
    .tainacan-selectbox-options-list {

        &:not(.is-buttons) {
            -moz-column-count: 2;
            -moz-column-gap: 0;
            -moz-column-rule: none;
            -webkit-column-count: 2;
            -webkit-column-gap: 0;
            -webkit-column-rule: none;
            column-count: 2;
            -moz-column-gap: 2em;
            column-gap: 2em;
            row-gap: 0.35em;
            column-rule: none;
            
            @media screen and (max-width: 768px) {
                -webkit-column-count: 1;
                -moz-column-count: 1;
                column-count: 1;
            }

            :deep(.b-checkbox), :deep(.b-radio) {
                max-width: 100%;
                min-height: 1.75em;
                margin-inline-start: 0em;
                margin-bottom: 0px !important;
                height: auto;
                padding-inline-start: .5em;
                padding-inline-end: .7em;
                padding-block-start: 2px;
                padding-block-end: 2px;
                -webkit-break-inside: avoid;
                break-inside: avoid;
                border-radius: var(--tainacan-input-border-radius);

                .control-label {
                    white-space: normal;
                    overflow: visible;
                }

                @media screen and (max-width: 768px) {
                    column-span: all;
                    padding-inline-start: 0.8125em;

                    .control-label {
                        padding-top: 0.8125em;
                        padding-bottom: 0.8125em;
                        padding-inline-start: calc(0.875em - 1px);
                        width: 100%;
                        border-bottom: 1px solid var(--tainacan-gray1);
                    }
                }

                &.is-disabled {
                    cursor: not-allowed;
                    opacity: 0.5;
                }

                &:hover {
                    background-color: var(--tainacan-gray1);
                }
            }
        }

        &.is-buttons {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 0.35em;

            .control :deep(.button) {
                transition: border-color 0.2s ease-in-out, background-color 0.2s ease-in-out, color 0.2s ease-in-out;

                &.is-primary {
                    border: 1px solid transparent;

                    &:hover.is-primary,
                    &:focus.is-primary {
                        border-color: var(--tainacan-secondary);
                    }
                    &:focus-visible,
                    &:focus-within {
                        outline-width: 2px;
                        outline-offset: -1px;
                        outline-color: var(--tainacan-secondary);
                        outline-color: color-mix(in srgb, var(--tainacan-secondary) 60%, var(--tainacan-background-color));
                        outline-style: solid;
                        box-shadow: none;
                    }
                }

                &:not(.is-primary) {
                    border: 1px solid var(--tainacan-input-border-color);
                    color: var(--tainacan-info-color);
        
                    &:hover,
                    &:focus {
                        border-color: var(--tainacan-secondary);
                        background-color: color-mix(in srgb, var(--tainacan-secondary) 10%, var(--tainacan-input-background-color));
                        color: var(--tainacan-secondary);
                    }

                    &:focus-visible,
                    &:focus-within {
                        outline-width: 2px;
                        outline-offset: -1px;
                        outline-color: var(--tainacan-input-border-color);
                        outline-style: solid;
                        box-shadow: none;
                    }
                }
            }
        }
    }
</style>
