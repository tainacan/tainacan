<template>
    <b-field
            :addons="false"
            :message="errorMessage"
            :type="errorMessage ? 'is-danger' : ''">
        <span   
                class="collapse-handle"
                @click="$emit('changeCollapse', errorMessage ? true : isCollapsed)">
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
                    v-if="isTextInputComponent( itemMetadatum.metadatum.metadata_type_object.component )">
                <component 
                        :is="itemMetadatum.metadatum.metadata_type_object.component"
                        v-model="values[0]" 
                        :item-metadatum="itemMetadatum"
                        @input="changeValue"
                        @blur="performValueChange"/>
                <template v-if="itemMetadatum.metadatum.multiple == 'yes' && values.length > 1">
                    <transition-group
                            name="filter-item"
                            class="multiple-inputs">
                        <template v-for="(value, index) of values">
                            <component 
                                    v-if="index > 0"
                                    :key="index"
                                    :is="itemMetadatum.metadatum.metadata_type_object.component"
                                    v-model="values[index]" 
                                    :item-metadatum="itemMetadatum"
                                    @input="changeValue"
                                    @blur="performValueChange"/>
                            <a 
                                    v-if="index > 0" 
                                    @click="removeValue(index)"
                                    class="add-link"
                                    :key="index">
                                <b-icon
                                        icon="minus-circle"
                                        size="is-small"
                                        type="is-secondary"/>
                                &nbsp;{{ $i18n.get('label_remove_value') }}
                            </a>
                        </template>
                    </transition-group>
                </template>
                <template v-if="itemMetadatum.metadatum.multiple == 'yes'">
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
        <transition name="filter-item">
            <div 
                    v-show="isCollapsed"
                    v-if="!isTextInputComponent( itemMetadatum.metadatum.metadata_type_object.component )">
                <component
                        :is="itemMetadatum.metadatum.metadata_type_object.component"
                        v-model="values"
                        :item-metadatum="itemMetadatum"
                        @input="changeValue()"
                        @blur="performValueChange()"/>
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
        created() {
            this.setInitialValues();
            eventBusItemMetadata.$on('updateErrorMessageOf#' + this.itemMetadatum.metadatum.id, (errors) => {
                let updatedErrorMessage = '';
                if (errors && this.itemMetadatum.metadatum.id == errors.metadatum_id && errors.errors) {
                    for (let error of errors.errors) { 
                        for (let index of Object.keys(error))
                            updatedErrorMessage += error[index] + '\n';
                    }
                }
                this.errorMessage = updatedErrorMessage;
            })
        },
        beforeDestroy() {
            eventBusItemMetadata.$off('updateErrorMessageOf#' + this.itemMetadatum.metadatum.id);
        },
        methods: {
            changeValue: _.debounce(function() {
                this.performValueChange();
            }, 800),
            performValueChange() {

                if (this.values && this.values.length > 0 && this.values[0] && this.values[0].value) {
                    let terms = this.values.map(term => term.value)
                    
                    if (this.itemMetadatum.value instanceof Array) {
                        let equal = [];

                        for (let meta of terms) {
                            let foundIndex = this.itemMetadatum.value.findIndex(element => meta == element.id);
                            if (foundIndex >= 0)
                                equal.push(this.itemMetadatum.value[foundIndex]);
                        }

                        if (equal.length == terms.length && this.itemMetadatum.value.length <= equal.length)
                            return;
                    }
                } else if (this.itemMetadatum.value.constructor.name == 'Object') {

                    if (this.itemMetadatum.value.id == this.values)
                        return;

                } else if (this.itemMetadatum.value instanceof Array) {  

                    let equal = [];

                    for (let meta of this.values) {
                        let foundIndex = this.itemMetadatum.value.findIndex(element => meta == element.id);

                        if (foundIndex >= 0)
                            equal.push(this.itemMetadatum.value[foundIndex]);
                    }

                    if (equal.length == this.values.length && this.itemMetadatum.value.length <= equal.length)
                        return;
                        
                } else if (this.values && this.values.length == 1 && this.values[0] == this.itemMetadatum.value) {
                    return
                }

                eventBusItemMetadata.$emit('input', {
                    itemId: this.itemMetadatum.item.id,
                    metadatumId: this.itemMetadatum.metadatum.id,
                    values: this.values ? this.values : '',
                    parentMetaId: this.itemMetadatum.parent_meta_id
                });
            },
            setInitialValues() {
                if (this.itemMetadatum.value instanceof Array)
                    this.values = this.itemMetadatum.value.slice(0); // This way we garantee this.values is a copy of the contents of this.itemMetadatum.value, instead of a reference to it
                else
                    this.itemMetadatum.value == null || this.itemMetadatum.value == undefined ? this.values = [] : this.values.push(this.itemMetadatum.value);
            },
            addValue(){
                this.values.push('');
                this.changeValue();
            },
            removeValue(index) {
                this.values.splice(index, 1);
                this.changeValue();
            },
            isTextInputComponent(component) {
                const array = ['tainacan-relationship','tainacan-taxonomy'];
                return !(array.indexOf(component) >= 0 );
            }
        }
    }
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss'; 

    .multiple-inputs {
        display: flex;
        margin: 0.75em 0;
        flex-direction: column;
        justify-content: space-between;
    }

    .field {
        border-bottom: 1px solid var(--tainacan-gray2);
        padding: 10px 25px;

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
    }
</style>
