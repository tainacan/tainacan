<template>
    <b-field
            :class="metadatumFormClasses"
            :ref="isHighlightedMetadatum ? 'hightlighted-metadatum': 'null'"
            :addons="false"
            :message="errorMessage"
            :type="errorMessage ? 'is-danger' : ''">
        <span   
                class="collapse-handle"
                @click="(!hideCollapses && !isMetadataNavigation) ? $emit('changeCollapse', errorMessage ? true : !isCollapsed ) : ''">
            <span 
                    v-if="!hideCollapses"
                    class="icon"
                    @click="(!hideCollapses && isMetadataNavigation) ? $emit('changeCollapse', errorMessage ? true : !isCollapsed ) : ''">
                <i 
                        :class="{
                            'tainacan-icon-arrowdown' : isCollapsed || errorMessage,
                            'tainacan-icon-arrowright' : !(isCollapsed || errorMessage)
                        }"
                        class="has-text-secondary tainacan-icon tainacan-icon-1-25em"/>
            </span>
            <label class="label">
                {{ itemMetadatum.metadatum.name }}
            </label>
            <span
                    v-if="itemMetadatum.metadatum.required == 'yes'"
                    class="required-metadatum-asterisk"
                    :class="errorMessage ? 'is-danger' : ''">
                *
            </span>
            <span 
                    v-if="!hideMetadataTypes"
                    class="metadata-type">
                ({{ itemMetadatum.metadatum.metadata_type_object.name }})
            </span>
            <help-button
                    v-if="!hideHelpButtons &&
                        !helpInfoBellowLabel &&
                        itemMetadatum.metadatum &&
                        itemMetadatum.metadatum.description_bellow_name !== 'yes' &&
                        itemMetadatum.metadatum.description" 
                    :title="itemMetadatum.metadatum.name"
                    :message="itemMetadatum.metadatum.description" />
        </span>
        <transition name="filter-item">
            <div   
                    v-show="hideCollapses || isCollapsed || !!errorMessage"
                    v-if="isTextInputComponent">
                <p
                        class="metadatum-description-help-info"
                        v-if="itemMetadatum.metadatum &&
                            itemMetadatum.metadatum.description &&
                            (
                                (!hideHelpButtons && helpInfoBellowLabel) ||
                                (itemMetadatum.metadatum.description_bellow_name === 'yes')
                            )">
                    {{ itemMetadatum.metadatum.description }}
                </p>
                <component 
                        :is="metadatumComponent"
                        v-model="values[0]" 
                        :item-metadatum="itemMetadatum"
                        @input="changeValue"
                        @blur="performValueChange"
                        :metadata-name-filter-string="metadataNameFilterString"
                        :hide-collapses="hideCollapses"
                        :hide-metadata-types="hideMetadataTypes"
                        :hide-help-buttons="hideHelpButtons"
                        :help-info-bellow-label="helpInfoBellowLabel"
                        :is-mobile-screen="isMobileScreen"
                        @mobileSpecialFocus="onMobileSpecialFocus"
                        :is-focused="isFocused"
                        :is-metadata-navigation="isMetadataNavigation" />
                <template v-if="isMultiple && values.length > 1">
                    <transition-group
                            name="filter-item"
                            class="multiple-inputs">
                        <template v-for="(value, index) of values">
                            <component 
                                    v-if="index > 0"
                                    :key="index"
                                    :is="metadatumComponent"
                                    v-model="values[index]" 
                                    :item-metadatum="itemMetadatum"
                                    @input="changeValue"
                                    @blur="performValueChange"
                                    :metadata-name-filter-string="metadataNameFilterString"
                                    :hide-collapses="hideCollapses"
                                    :hide-metadata-types="hideMetadataTypes"
                                    :hide-help-buttons="hideHelpButtons"
                                    :help-info-bellow-label="helpInfoBellowLabel"
                                    :is-mobile-screen="isMobileScreen"
                                    @mobileSpecialFocus="onMobileSpecialFocus"
                                    :is-focused="isFocused"
                                    :is-metadata-navigation="isMetadataNavigation" />
                            <a 
                                    v-if="index > 0" 
                                    @click="removeValue(index)"
                                    class="add-link"
                                    :key="index">
                                <span class="icon is-small">
                                    <i class="tainacan-icon has-text-secondary tainacan-icon-remove"/>
                                </span>
                                &nbsp;{{ $i18n.get('label_remove_value') }}
                            </a>
                        </template>
                    </transition-group>
                </template>
                <template v-if="isMultiple && (maxMultipleValues === undefined || maxMultipleValues === 0 || (maxMultipleValues !== 1 && maxMultipleValues > values.length))">
                    <a 
                            @click="addValue"
                            class="is-inline-block add-link">
                        <span class="icon is-small">
                            <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
                        </span>
                        &nbsp;{{ $i18n.get('label_add_value') }}
                    </a>
                </template>
            </div>

            <!-- Non-textual metadata such as taxonomy, relationship, geocoordinate and compound manage multiple state in different ways -->
            <div 
                    v-show="hideCollapses || isCollapsed"
                    v-else>
                <p
                        class="metadatum-description-help-info"
                        v-if="itemMetadatum.metadatum &&
                            itemMetadatum.metadatum.description &&
                            (
                                (!hideHelpButtons && helpInfoBellowLabel) ||
                                (itemMetadatum.metadatum.description_bellow_name === 'yes')
                            )">
                    {{ itemMetadatum.metadatum.description }}
                </p>
                <component
                        :is="metadatumComponent"
                        v-model="values"
                        :item-metadatum="itemMetadatum"
                        @input="changeValue"
                        @blur="performValueChange"
                        :is-last-metadatum="isLastMetadatum"
                        :hide-collapses="hideCollapses"
                        :hide-metadata-types="hideMetadataTypes"
                        :hide-help-buttons="hideHelpButtons"
                        :help-info-bellow-label="helpInfoBellowLabel"
                        :is-mobile-screen="isMobileScreen"
                        :metadata-name-filter-string="metadataNameFilterString"
                        @mobileSpecialFocus="onMobileSpecialFocus"
                        :is-focused="isFocused"
                        :is-metadata-navigation="isMetadataNavigation" />
            </div>
        </transition>
    </b-field>
</template>

<script>
    import { eventBusItemMetadata } from '../../js/event-bus-item-metadata';

    export default {
        name: 'TainacanFormItem',
        props: {
            itemMetadatum: Object,
            isCollapsed: true,
            hideCollapses: false,
            hideMetadataTypes: Boolean,
            hideHelpButtons: Boolean,
            helpInfoBellowLabel: Boolean,
            isLastMetadatum: false,
            metadataNameFilterString: '',
            isMobileScreen: false,
            isFocused: false,
            isMetadataNavigation: false
        },
        data(){
            return {
                values: [],
                errorMessage: '',
                isHighlightedMetadatum: false
            }
        },
        computed: {
            metadatumComponent() {
                return (this.itemMetadatum && this.itemMetadatum.metadatum && this.itemMetadatum.metadatum.metadata_type_object.component) ? this.itemMetadatum.metadatum.metadata_type_object.component : '';
            },
            isMultiple() {
                return (this.itemMetadatum && this.itemMetadatum.metadatum && this.itemMetadatum.metadatum.multiple == 'yes') ? this.itemMetadatum.metadatum.multiple == 'yes' : false;
            },
            maxMultipleValues() {
                return (
                    this.itemMetadatum &&
                    this.itemMetadatum.metadatum &&
                    this.itemMetadatum.metadatum.cardinality &&
                    !isNaN(this.itemMetadatum.metadatum.cardinality) &&
                    this.itemMetadatum.metadatum.cardinality > 1
                ) ? this.itemMetadatum.metadatum.cardinality : undefined;
            },
            isTextInputComponent() {
                const array = ['tainacan-relationship','tainacan-taxonomy', 'tainacan-compound', 'tainacan-user', 'tainacan-geocoordinate'];
                return !(array.indexOf(this.metadatumComponent) >= 0 );
            },
            metadatumFormClasses() {
                return '' + 
                    (this.hideCollapses ? ' has-collapses-hidden' : '') + 
                    (this.isHighlightedMetadatum ? ' hightlighted-metadatum' : '') + 
                    (this.metadatumComponent ? ' tainacan-metadatum-component--' + this.metadatumComponent : '') +
                    (this.itemMetadatum && this.itemMetadatum.metadatum && this.itemMetadatum.metadatum.placeholder ? ' has-placeholder' : '') +  
                    (this.itemMetadatum && this.itemMetadatum.metadatum && this.itemMetadatum.metadatum.description ? ' has-description' : '') +  
                    (this.itemMetadatum && this.itemMetadatum.metadatum && this.itemMetadatum.metadatum.id ? ' tainacan-metadatum-id--' + this.itemMetadatum.metadatum.id : '') +
                    (this.isFocused ? ' is-focused' : '');  
            }
        },
        created() {
            this.setInitialValues();
            eventBusItemMetadata.$on('updateErrorMessageOf#' + (this.itemMetadatum.parent_meta_id ? this.itemMetadatum.metadatum.id + '-' + this.itemMetadatum.parent_meta_id : this.itemMetadatum.metadatum.id), (errors) => {    
                let updatedErrorMessage = '';
                if (errors && errors.errors && this.itemMetadatum && this.itemMetadatum.metadatum && (this.itemMetadatum.parent_meta_id ? (this.itemMetadatum.parent_meta_id == errors.parent_meta_id && this.itemMetadatum.metadatum.id == errors.metadatum_id) : this.itemMetadatum.metadatum.id == errors.metadatum_id)) {
                    for (let error of errors.errors) { 
                        for (let index of Object.keys(error))
                            updatedErrorMessage += error[index] + '\n';
                    }
                }
                this.errorMessage = updatedErrorMessage;
            }); 
        },
        beforeDestroy() {
            if (this.itemMetadatum && this.itemMetadatum.metadatum) {
                eventBusItemMetadata.$off('updateErrorMessageOf#' + (this.itemMetadatum.parent_meta_id ? this.itemMetadatum.metadatum.id + '-' + this.itemMetadatum.parent_meta_id : this.itemMetadatum.metadatum.id));
            }
        },
        mounted () {
            if (this.$route && this.$route.query && this.$route.query.editingmetadata) {
                this.isHighlightedMetadatum = this.$route.query.editingmetadata == (this.itemMetadatum.parent_meta_id ? this.itemMetadatum.metadatum.id + '-' + this.itemMetadatum.parent_meta_id : this.itemMetadatum.metadatum.id);

                if (this.isHighlightedMetadatum) {
                    
                    this.$nextTick(() => {
                        let highlightedMetadatum = this.$refs['hightlighted-metadatum'];
                        if (highlightedMetadatum && highlightedMetadatum.$el && highlightedMetadatum.$el.scrollIntoView)
                            setTimeout(() => highlightedMetadatum.$el.scrollIntoView(), 500);
                    });
                }
            }
        },
        methods: {
            // 'this.values' is always an array for this component, even if it is single valued.
            setInitialValues() {
                if (this.itemMetadatum) {
                    if (this.itemMetadatum.value instanceof Array)
                        this.values = this.itemMetadatum.value.slice(0); // This way we garantee this.values is a copy of the contents of this.itemMetadatum.value, instead of a reference to it
                    else
                        this.itemMetadatum.value == null || this.itemMetadatum.value == undefined ? this.values = [] : this.values.push(this.itemMetadatum.value);
                }
            },
            changeValue: _.debounce(function() {
                this.performValueChange();
            }, 800),
            performValueChange() {

                // Compound metadata do not emit values, only their children.
                if (this.metadatumComponent == 'tainacan-compound')
                    return;

                if (this.itemMetadatum.value !== null && this.itemMetadatum.value !== false) {

                    // This routine avoids calling the API if the value did not changed
                    switch(this.itemMetadatum.value.constructor.name) {
                        // Multivalored Metadata requires checking the whole array
                        case 'Array': {
                            
                            let equal = [];
                            let currentValues = [];
                            
                            // An array of terms
                            if (this.values.length && this.values[0] && this.values[0].constructor.name == 'Object')
                                currentValues = this.values.map(term => term.value)
                            else
                                currentValues = this.values;
                                
                            if (Array.isArray(currentValues)) {
                                for (let value of currentValues) {
                                    let foundIndex = this.itemMetadatum.value.findIndex(element => value == element.id);
                                    if (foundIndex >= 0)
                                        equal.push(this.itemMetadatum.value[foundIndex]);
                                }

                                if (equal.length == currentValues.length && this.itemMetadatum.value.length <= equal.length)
                                    return;
                            } else { // This will happen in taxonomy single valued on item submission, as there all term values appear as array.
                                if (this.itemMetadatum.value == currentValues)
                                    return;
                            }

                            break;
                        }
                        
                        // A single term value
                        case 'Object':
                            if (this.values.length && this.values[0] == this.itemMetadatum.value.id)
                                return;
                            break;

                        // Any single metadatum value that is not a term
                        default:
                            if (!this.errorMessage && this.values.length && this.values[0] == this.itemMetadatum.value)
                                return;
                    }
                }
                
                // If none is the case, the value is update request is sent to the API
                eventBusItemMetadata.$emit('input', {
                    itemId: this.itemMetadatum.item.id,
                    metadatumId: this.itemMetadatum.metadatum.id,
                    values: this.values ? this.values : '',
                    parentMetaId: this.itemMetadatum.parent_meta_id,
                    parentId: this.itemMetadatum.metadatum.parent != undefined ? this.itemMetadatum.metadatum.parent : 0
                });
            },
            addValue(){
                this.values.push('');
                this.changeValue();
            },
            removeValue(index) {
                this.values.splice(index, 1);
                this.changeValue();
            },
            onMobileSpecialFocus() {
                if (this.isMobileScreen)
                    this.$emit('mobileSpecialFocus');
            }
        }
    }
</script>

<style lang="scss" scoped>

    .multiple-inputs {
        display: flex;
        margin: 0.75em 0;
        flex-direction: column;
        justify-content: space-between;
    }

    /deep/ .is-special-hidden-for-mobile,
    /deep/ .is-special-hidden-for-mobile:focus,
    /deep/ .is-special-hidden-for-mobile:focus-visible {
        opacity: 0;
        width: 0;
        height: 0 !important;
        min-height: 0;
        min-width: 0;
        padding: 0 !important;
        line-height: 0px !important;
        border: none !important;
        border-color: transparent !important;
        border-width: 0px !important;
        font-size: 0px !important;
        display: block !important;
    }

    .field {
        border-bottom: 1px solid var(--tainacan-input-border-color);
        padding: 10px var(--tainacan-container-padding);

        &.hightlighted-metadatum {
            background-color: var(--tainacan-white);
            transition: background-color 0.8s; 
            animation-name: metadatum-highlight;
            animation-duration: 3s;
            animation-iteration-count: 2; 
        }

        &.is-metadata-navigation-active {
            transition: filter 0.2s ease, opacity 0.2s ease;
        }
        &:not(.is-focused).is-metadata-navigation-active {
            opacity: 0.3;
            filter: grayscale(1);
        }
        &.is-focused.is-metadata-navigation-active {
            opacity: 1.0;
            filter: none;
        }

        &.has-collapses-hidden {
            border-bottom: none;
            padding: 10px !important;

            .child-metadata-inputs {
                margin-left: 0.25em;
            }
            @media screen and (min-width: 770px) {
                .collapse-handle {
                    margin-left: -15px;
                }
            }
        }

        .label {
            font-size: 0.875em;
            font-weight: 500;
            margin-left: 15px;
            margin-bottom: 0;
            margin-top: 0.15em;
        }
        .metadatum-description-help-info {
            font-size: 0.75em;
            color: var(--tainacan-info-color);
            margin-bottom: 0.5em;
        }
        .metadata-type {
            font-size: 0.8125em;
            font-weight: 400;
            color: var(--tainacan-info-color);
            opacity: 0.75;
            position: relative;
        }
        .collapse-handle {
            cursor: pointer;
            margin-left: -42px;
            line-height: 1.5em;

            .tainacan-help-tooltip-trigger {
                margin-right: auto;
            }
        }
        .collapse-handle+div {
            margin-top: 0.5em;
        }
        .add-link {
            font-size: 0.75em;
        }

        @media screen and (max-width: 769px) {
            .collapse-handle {
                font-size: 1em;
                margin-left: 0;
                margin-right: 22px;
                width: 100%;
                display: flex;
                position: relative;
                justify-content: space-between;
                align-items: center;

                .label {
                    margin-left: 2px;
                    margin-right: 0.5em;
                }
                .icon {
                    margin-left: auto;
                    order: 3;
                    width: 2em;
                    justify-content: flex-end;
                }
            }
        }
    }
</style>
