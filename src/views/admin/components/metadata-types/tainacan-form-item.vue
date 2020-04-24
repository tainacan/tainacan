<template>
    <b-field
            :addons="false"
            :message="errorMessage"
            :type="errorMessage ? 'is-danger' : ''">
        <span   
                class="collapse-handle"
                @click="$emit('changeCollapse', errorMessage ? true : !isCollapsed )">
            <span class="icon">
                <i 
                        :class="{
                            'tainacan-icon-arrowdown' : isCollapsed || errorMessage,
                            'tainacan-icon-arrowright' : !(isCollapsed || errorMessage)
                        }"
                        class="has-text-secondary tainacan-icon tainacan-icon-1-25em"/>
            </span>
            <label 
                    v-tooltip="{
                        delay: {
                            show: 500,
                            hide: 300,
                        },
                        content: itemMetadatum.metadatum.name,
                        autoHide: false,
                        placement: 'auto-end'
                    }" 
                    class="label">
                {{ itemMetadatum.metadatum.name }}
            </label>
            <span
                    v-if="itemMetadatum.metadatum.required == 'yes'"
                    class="required-metadatum-asterisk"
                    :class="errorMessage ? 'is-danger' : ''">
                *
            </span>
            <span class="metadata-type">
                ({{ itemMetadatum.metadatum.metadata_type_object.name }})
            </span>
            <help-button 
                    :title="itemMetadatum.metadatum.name"
                    :message="itemMetadatum.metadatum.description"/>
        </span>
        <transition name="filter-item">
            <div   
                    v-show="isCollapsed || errorMessage"
                    v-if="isTextInputComponent">
                <component 
                        :is="metadatumComponent"
                        v-model="values[0]" 
                        :item-metadatum="itemMetadatum"
                        @input="changeValue"
                        @blur="performValueChange"/>
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
                                    @blur="performValueChange"/>
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
        <!-- Non-textual metadata such as taxonomy, relationship and compound manage multiple state in different ways -->
        <transition name="filter-item">
            <div 
                    v-show="isCollapsed"
                    v-if="!isTextInputComponent">
                <component
                        :is="metadatumComponent"
                        v-model="values"
                        :item-metadatum="itemMetadatum"
                        @input="changeValue"
                        @blur="performValueChange"/>
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
            isCollapsed: true
        },
        data(){
            return {
                values: [],
                errorMessage: ''
            }
        },
        computed: {
            metadatumComponent() {
                return (this.itemMetadatum && this.itemMetadatum.metadatum && this.itemMetadatum.metadatum.metadata_type_object.component) ? this.itemMetadatum.metadatum.metadata_type_object.component : '';
            },
            isMultiple() {
                return (this.itemMetadatum && this.itemMetadatum.metadatum && this.itemMetadatum.metadatum.multiple == 'yes') ? this.itemMetadatum.metadatum.multiple == 'yes' : false;
            },
            isTextInputComponent() {
                const array = ['tainacan-relationship','tainacan-taxonomy', 'tainacan-compound', 'tainacan-user'];
                return !(array.indexOf(this.metadatumComponent) >= 0 );
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
            if (this.itemMetadatum && this.itemMetadatum.metadatum)
                eventBusItemMetadata.$off('updateErrorMessageOf#' + (this.itemMetadatum.parent_meta_id ? this.itemMetadatum.metadatum.id + '-' + this.itemMetadatum.parent_meta_id : this.itemMetadatum.metadatum.id));
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

                // This routine avoids calling the API if the value did not changed
                switch(this.itemMetadatum.value.constructor.name) {

                    // Multivalored Metadata requires checking the whole array
                    case 'Array': {
                        
                        let equal = [];
                        let currentValues = [];

                        // An array of terms
                        if (this.values.length && this.values[0].constructor.name == 'Object')
                            currentValues = this.values.map(term => term.value)
                        else
                            currentValues = this.values;
                            
                        for (let value of currentValues) {
                            let foundIndex = this.itemMetadatum.value.findIndex(element => value == element.id);
                            if (foundIndex >= 0)
                                equal.push(this.itemMetadatum.value[foundIndex]);
                        }

                        if (equal.length == currentValues.length && this.itemMetadatum.value.length <= equal.length)
                            return;

                        break;
                    }
                    
                    // A single term value
                    case 'Object':
                        if (this.values.length && this.values[0] == this.itemMetadatum.value.id)
                            return;
                        break;

                    // Any single metadatum value that is not a term
                    default:
                        if (this.values.length && this.values[0] == this.itemMetadatum.value)
                            return;
                }
                
                // If none is the case, the value is update request is sent to the API
                eventBusItemMetadata.$emit('input', {
                    itemId: this.itemMetadatum.item.id,
                    metadatumId: this.itemMetadatum.metadatum.id,
                    values: this.values ? this.values : '',
                    parentMetaId: this.itemMetadatum.parent_meta_id
                });
            },
            addValue(){
                this.values.push('');
                this.changeValue();
            },
            removeValue(index) {
                this.values.splice(index, 1);
                this.changeValue();
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

    .field {
        border-bottom: 1px solid var(--tainacan-gray2);
        padding: 10px var(--tainacan-container-padding);

        .label {
            font-size: 0.875em;
            font-weight: 500;
            margin-left: 15px;
            margin-bottom: 0;
            margin-top: 0.15em;
            max-width: 50%;
        }
        .metadata-type {
            font-size: 0.8125em;
            font-weight: 400;
            color: var(--tainacan-gray3);
            top: -0.1em;
            position: relative;
        }
        .help-wrapper {
            top: -0.2em;
        }
        .collapse-handle {
            cursor: pointer;
            position: relative;
            margin-left: -42px;
            bottom: 0.1em;
            white-space: nowrap;
        }
        .add-link {
            font-size: 0.75em;
        }
    }
</style>
