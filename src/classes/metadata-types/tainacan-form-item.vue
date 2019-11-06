<template>
    <b-field
            :addons="false"
            :message="getErrorMessage"
            :type="metadatumTypeMessage">
        <span   
                class="collapse-handle"
                @click="$emit('changeCollapse', metadatumTypeMessage != 'is-danger' ? !isCollapsed : true)">
            <span class="icon">
                <i 
                        :class="{ 'tainacan-icon-arrowdown' : isCollapsed || metadatumTypeMessage == 'is-danger', 'tainacan-icon-arrowright' : !(isCollapsed || metadatumTypeMessage == 'is-danger') }"
                        class="has-text-secondary tainacan-icon tainacan-icon-20px"/>
            </span>
            <label 
                    v-tooltip="{
                        delay: {
                            show: 500,
                            hide: 300,
                        },
                        content: metadatum.metadatum.name,
                        autoHide: false,
                        placement: 'auto-end'
                    }" 
                    class="label">
                {{ metadatum.metadatum.name }}
            </label>
            <span
                    v-if="metadatum.metadatum.required == 'yes'"
                    class="required-metadatum-asterisk"
                    :class="metadatumTypeMessage">
                *
            </span>
            <span class="metadata-type">
                ({{ metadatum.metadatum.metadata_type_object.name }})
            </span>
            <help-button 
                    :title="metadatum.metadatum.name"
                    :message="metadatum.metadatum.description"/>
        </span>
        <transition name="filter-item">
            <div   
                    v-show="isCollapsed || metadatumTypeMessage == 'is-danger'"
                    v-if="isTextInputComponent( metadatum.metadatum.metadata_type_object.component )">
                <component 
                        :is="metadatum.metadatum.metadata_type_object.component"
                        v-model="inputs[0]" 
                        :metadatum="metadatum"
                        @input="changeValue()"/>
                <template v-if="metadatum.metadatum.multiple == 'yes'">
                    <transition-group
                            name="filter-item"
                            class="multiple-inputs">
                        <template 
                                v-for="(input, index) in inputs.slice(1)">
                                <component 
                                        :key="index"
                                        :is="metadatum.metadatum.metadata_type_object.component"
                                        v-model="inputs[index]" 
                                        :metadatum="metadatum"
                                        @input="changeValue()"/>
                                <a 
                                        v-if="index > 0" 
                                        @click="removeInput(index)"
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
                    <a 
                            @click="addInput"
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
                    v-if="!isTextInputComponent( metadatum.metadatum.metadata_type_object.component )">
                <component
                        :is="metadatum.metadatum.metadata_type_object.component"
                        v-model="inputs"
                        :metadatum="metadatum"
                        @input="changeValue()"/>
            </div>
        </transition>
    </b-field>
</template>

<script>
    import { eventBus } from '../../js/event-bus-web-components'

    export default {
        name: 'TainacanFormItem',
        props: {
            metadatum: Object,
            isCollapsed: true
        },
        data(){
            return {
                inputs: []
            }
        },
        computed: {
            getErrorMessage() {
                let errorMessage = '';
                let errors = eventBus.getErrors(this.metadatum.metadatum.id);

                if (errors) {
                    for (let error of errors) { 
                        for (let index of Object.keys(error))
                            errorMessage += error[index] + '\n';
                    }
                }

                return errorMessage;
            },
            metadatumTypeMessage() {
                return this.getErrorMessage ? 'is-danger' : ''
            }
        },
        created() {
            this.createInputs();
        },
        methods: {
            changeValue: _.debounce(function() {

                if (this.inputs && this.inputs.length > 0 && this.inputs[0] && this.inputs[0].value) {
                    let terms = this.inputs.map(term => term.value)
                    
                    if (this.metadatum.value instanceof Array){
                        let equal = [];

                        for (let meta of terms) {
                            let foundIndex = this.metadatum.value.findIndex(element => meta == element.id);
                            if (foundIndex >= 0)
                                equal.push(this.metadatum.value[foundIndex]);
                        }

                        if (equal.length == terms.length && this.metadatum.value.length <= equal.length)
                            return;

                    }
                } else if (this.metadatum.value.constructor.name == 'Object') {

                    if (this.metadatum.value.id == this.inputs)
                        return;

                } else if (this.metadatum.value instanceof Array) {  

                    let equal = [];

                    for (let meta of this.inputs) {
                        let foundIndex = this.metadatum.value.findIndex(element => meta == element.id);

                        if (foundIndex >= 0)
                            equal.push(this.metadatum.value[foundIndex]);
                    }

                    if (equal.length == this.inputs.length && this.metadatum.value.length <= equal.length)
                        return;
                }

                eventBus.$emit('input', {
                    itemId: this.metadatum.item.id,
                    metadatumId: this.metadatum.metadatum.id,
                    values: this.inputs ? this.inputs : ''
                });
            
            }, 900),
            createInputs(){
                if (this.metadatum.value instanceof Array)
                    this.inputs = this.metadatum.value.slice(0);
                else
                    this.metadatum.value == null || this.metadatum.value == undefined ? this.inputs = [] : this.inputs.push(this.metadatum.value);
            },
            addInput(){
                this.inputs.push('');
                this.changeValue();
            },
            removeInput(index) {
                this.inputs.splice(index, 1);
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

    @import '../../admin/scss/_variables.scss'; 

    .multiple-inputs {
        display: flex;
        margin: 0.75rem 0;
        flex-direction: column;
        justify-content: space-between;
    }

    .field {
        border-bottom: 1px solid $gray2;
        padding: 10px 25px;

        .label {
            font-size: 0.875rem;
            font-weight: 500;
            margin-left: 15px;
            margin-bottom: 0;
            margin-top: 0.15rem;
            max-width: 50%;
        }
        .metadata-type {
            font-size: 0.8125rem;
            font-weight: 400;
            color: $gray3;
            top: -0.4em;
            position: relative;
        }
        .help-wrapper {
            top: -0.2em;
        }
        .collapse-handle {
            cursor: pointer;
            position: relative;
            margin-left: -42px;
            bottom: 0.1rem;
            white-space: nowrap;
        }
    }
</style>
